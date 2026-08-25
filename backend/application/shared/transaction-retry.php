<?php
/*
 * Database transaction retry and backoff helpers.
 */

/**
 * Determine whether a database exception represents retryable contention.
 */
function isRetryableTransactionFailure(Throwable $exception)
{
    $exceptionCode = (string) $exception->getCode();

    if ($exceptionCode === '40001' || $exceptionCode === '1213' || $exceptionCode === '1205') {
        return true;
    }

    if ($exception instanceof PDOException && is_array($exception->errorInfo)) {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $driverCode = (string) ($exception->errorInfo[1] ?? '');

        return $sqlState === '40001' || $driverCode === '1213' || $driverCode === '1205';
    }

    return false;
}

/**
 * Apply a short bounded delay before the next transaction attempt.
 */
function backoffBeforeTransactionRetry($failedAttempt)
{
    $delayMicroseconds = min(50000 * max(1, (int) $failedAttempt), 200000);
    usleep($delayMicroseconds);
}
