<?php
/**
 * QuickMart IOMS - Persistent login attempt limiter.
 *
 * The key is a one-way digest of the normalized identifier and the direct
 * client address. Forwarded headers are intentionally ignored unless a
 * deployment-specific trusted-proxy boundary rewrites REMOTE_ADDR safely.
 */

require_once __DIR__ . '/../shared/transaction-retry.php';

function getLoginRateLimitClientKey()
{
    $remoteAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '');

    return filter_var($remoteAddress, FILTER_VALIDATE_IP) !== false
        ? $remoteAddress
        : 'unknown';
}

function getLoginRateLimitKey($identifier)
{
    $normalizedIdentifier = strtolower(trim((string) $identifier));
    $clientKey = getLoginRateLimitClientKey();
    $secret = function_exists('quickmartEnv')
        ? quickmartEnv('QUICKMART_RATE_LIMIT_SECRET', '')
        : '';
    $material = $normalizedIdentifier . "\0" . $clientKey;

    return $secret !== ''
        ? hash_hmac('sha256', $material, $secret)
        : hash('sha256', $material);
}

function runLoginRateLimitTransaction(PDO $pdo, callable $operation)
{
    if ($pdo->inTransaction()) {
        throw new RuntimeException('Login rate-limit transaction cannot be nested.');
    }

    $maxAttempts = 3;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        try {
            if (!$pdo->beginTransaction()) {
                throw new RuntimeException('Login rate-limit transaction could not start.');
            }

            $result = $operation();

            if (!$pdo->commit()) {
                throw new RuntimeException('Login rate-limit transaction could not commit.');
            }

            return $result;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if ($attempt < $maxAttempts && isRetryableTransactionFailure($exception)) {
                backoffBeforeTransactionRetry($attempt);
                continue;
            }

            throw $exception;
        }
    }

    throw new RuntimeException('Login rate-limit transaction failed.');
}

/**
 * Reserve one valid-shaped login attempt atomically. The first five attempts
 * in a window are allowed to reach password verification; the next attempt
 * receives 429. A successful login clears the row.
 */
function reserveLoginAttempt(PDO $pdo, $identifier)
{
    $rateKey = getLoginRateLimitKey($identifier);
    $maxAttempts = (int) LOGIN_RATE_LIMIT_MAX_ATTEMPTS;
    $blockedAttemptCount = $maxAttempts + 1;
    $windowSeconds = (int) LOGIN_RATE_LIMIT_WINDOW_SECONDS;

    return runLoginRateLimitTransaction($pdo, function () use (
        $pdo,
        $rateKey,
        $maxAttempts,
        $blockedAttemptCount,
        $windowSeconds
    ) {
        // Keep stale buckets bounded without a separate scheduler. The limit
        // makes this cleanup deterministic and inexpensive.
        $pdo->exec(
            'DELETE FROM Login_Rate_Limits '
            . 'WHERE Window_Started_At < DATE_SUB(NOW(), INTERVAL '
            . $windowSeconds . ' SECOND) LIMIT 100'
        );

        $statement = $pdo->prepare(
            'INSERT INTO Login_Rate_Limits '
            . '(Rate_Key, Window_Started_At, Attempt_Count, Last_Attempt_At) '
            . 'VALUES (:rate_key, NOW(), 1, NOW()) '
            . 'ON DUPLICATE KEY UPDATE '
            . 'Attempt_Count = IF('
            . 'TIMESTAMPDIFF(SECOND, Window_Started_At, NOW()) >= ' . $windowSeconds
            . ', 1, LEAST(Attempt_Count + 1, ' . $blockedAttemptCount . ')), '
            . 'Window_Started_At = IF('
            . 'TIMESTAMPDIFF(SECOND, Window_Started_At, NOW()) >= ' . $windowSeconds
            . ', NOW(), Window_Started_At), '
            . 'Last_Attempt_At = NOW()'
        );
        $statement->execute(['rate_key' => $rateKey]);

        $stateStatement = $pdo->prepare(
            'SELECT Attempt_Count, '
            . 'TIMESTAMPDIFF(SECOND, Window_Started_At, NOW()) AS Elapsed_Seconds '
            . 'FROM Login_Rate_Limits WHERE Rate_Key = :rate_key FOR UPDATE'
        );
        $stateStatement->execute(['rate_key' => $rateKey]);
        $state = $stateStatement->fetch();

        if (!is_array($state)) {
            throw new RuntimeException('Login rate-limit state was not persisted.');
        }

        $attemptCount = (int) $state['Attempt_Count'];
        $elapsedSeconds = max(0, (int) $state['Elapsed_Seconds']);

        return [
            'allowed' => $attemptCount <= $maxAttempts,
            'retry_after' => max(1, $windowSeconds - $elapsedSeconds),
            'rate_key' => $rateKey
        ];
    });
}

function clearLoginRateLimit(PDO $pdo, $rateKey)
{
    if (!is_string($rateKey) || !preg_match('/\A[0-9a-f]{64}\z/D', $rateKey)) {
        throw new InvalidArgumentException('Invalid login rate-limit key.');
    }

    return runLoginRateLimitTransaction($pdo, function () use ($pdo, $rateKey) {
        $statement = $pdo->prepare('DELETE FROM Login_Rate_Limits WHERE Rate_Key = :rate_key');
        $statement->execute(['rate_key' => $rateKey]);

        return $statement->rowCount();
    });
}
