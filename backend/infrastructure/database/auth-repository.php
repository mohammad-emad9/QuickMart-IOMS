<?php
/*
 * Authentication repository operations.
 */

/**
 * Find the staff record needed for login authentication and response data.
 *
 * @return array|false
 */
function findStaffForLogin(PDO $pdo, $identifier)
{
    $statement = $pdo->prepare("
        SELECT Staff_ID, Full_Name, Email, Password, Role, Auth_Revision
        FROM Staff
        WHERE Email = ? OR Staff_ID = ?
    ");
    $statement->execute([$identifier, $identifier]);

    $staff = $statement->fetch();

    if ($staff !== false) {
        $staff['Auth_Revision'] = (int) $staff['Auth_Revision'];
    }

    return $staff;
}

/**
 * Find the current authentication context, including the database-backed
 * session revision, without exposing the password.
 *
 * @return array|false
 */
function findStaffAuthContextWithRevision(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare(
        "SELECT Staff_ID, Full_Name, Email, Role, Auth_Revision
         FROM Staff
         WHERE Staff_ID = ?
         LIMIT 1"
    );
    $statement->execute([$staffId]);

    $staff = $statement->fetch();

    if ($staff !== false) {
        $staff['Auth_Revision'] = (int) $staff['Auth_Revision'];
    }

    return $staff;
}

/**
 * Find the Staff_ID used by a future password-reset request.
 *
 * This query intentionally returns no password or contact data. The public
 * forgot-password route remains disabled until a secure delivery adapter is
 * available, so callers must keep any response generic.
 *
 * @return string|false
 */
function findStaffIdForPasswordReset(PDO $pdo, $identifier)
{
    $statement = $pdo->prepare("
        SELECT Staff_ID
        FROM Staff
        WHERE Email = ? OR Staff_ID = ?
        LIMIT 1
    ");
    $statement->execute([$identifier, $identifier]);

    $staffId = $statement->fetchColumn();

    return $staffId === false ? false : (string) $staffId;
}

/**
 * Lock the owning staff row while issuing or consuming a reset token.
 *
 * Serializing on Staff prevents two issuances for an account with no existing
 * token rows from both creating an active token concurrently.
 *
 * @return string|false
 */
function lockStaffForPasswordReset(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare('
        SELECT Staff_ID
        FROM Staff
        WHERE Staff_ID = ?
        FOR UPDATE
    ');
    $statement->execute([$staffId]);

    $lockedStaffId = $statement->fetchColumn();

    return $lockedStaffId === false ? false : (string) $lockedStaffId;
}

/**
 * Lock a Staff row before any password or authentication-revision mutation.
 * Reset-token operations acquire this same parent-row lock before token-row
 * locks so all password-sensitive paths use a consistent lock order.
 *
 * @return array|false
 */
function lockStaffForAuthenticationUpdate(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare('
        SELECT Staff_ID, Password, Auth_Revision
        FROM Staff
        WHERE Staff_ID = ?
        FOR UPDATE
    ');
    $statement->execute([$staffId]);

    $staff = $statement->fetch();

    if ($staff !== false) {
        $staff['Auth_Revision'] = (int) $staff['Auth_Revision'];
    }

    return $staff;
}

/**
 * Revoke all currently usable reset tokens for one staff account.
 */
function revokeActivePasswordResetTokens(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare('
        UPDATE Password_Reset_Tokens
        SET Revoked_At = CURRENT_TIMESTAMP
        WHERE Staff_ID = ?
          AND Used_At IS NULL
          AND Revoked_At IS NULL
          AND Expires_At > CURRENT_TIMESTAMP
    ');
    $statement->execute([$staffId]);

    return $statement->rowCount();
}

/**
 * Insert a reset token using a database-clock expiry.
 */
function insertPasswordResetToken(PDO $pdo, $staffId, $tokenHash, $ttlSeconds)
{
    $statement = $pdo->prepare('
        INSERT INTO Password_Reset_Tokens (Staff_ID, Token_Hash, Expires_At)
        VALUES (?, ?, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL ? SECOND))
    ');
    $statement->execute([$staffId, $tokenHash, $ttlSeconds]);

    return (int) $pdo->lastInsertId();
}

/**
 * Find a token owner without locking it. The consuming transaction obtains
 * locks in Staff-then-token order, matching issuance and avoiding avoidable
 * deadlocks between issuance and consumption.
 *
 * @return array|false
 */
function findPasswordResetTokenOwner(PDO $pdo, $tokenHash)
{
    $statement = $pdo->prepare('
        SELECT Reset_ID, Staff_ID
        FROM Password_Reset_Tokens
        WHERE Token_Hash = ?
        LIMIT 1
    ');
    $statement->execute([$tokenHash]);

    return $statement->fetch();
}

/**
 * Lock and validate a reset token for one-time consumption.
 *
 * @return array|false
 */
function findUsablePasswordResetTokenForUpdate(PDO $pdo, $tokenHash)
{
    $statement = $pdo->prepare('
        SELECT Reset_ID, Staff_ID
        FROM Password_Reset_Tokens
        WHERE Token_Hash = ?
          AND Used_At IS NULL
          AND Revoked_At IS NULL
          AND Expires_At > CURRENT_TIMESTAMP
        FOR UPDATE
    ');
    $statement->execute([$tokenHash]);

    $token = $statement->fetch();

    return $token === false ? false : $token;
}

/**
 * Mark a validated token as used, retaining the conditional guard as a final
 * one-time-consumption boundary.
 */
function markPasswordResetTokenUsed(PDO $pdo, $resetId)
{
    $statement = $pdo->prepare('
        UPDATE Password_Reset_Tokens
        SET Used_At = CURRENT_TIMESTAMP
        WHERE Reset_ID = ?
          AND Used_At IS NULL
          AND Revoked_At IS NULL
          AND Expires_At > CURRENT_TIMESTAMP
    ');
    $statement->execute([$resetId]);

    return $statement->rowCount();
}

/**
 * Revoke other currently usable tokens after a successful consumption.
 */
function revokeOtherActivePasswordResetTokens(PDO $pdo, $staffId, $resetId)
{
    $statement = $pdo->prepare('
        UPDATE Password_Reset_Tokens
        SET Revoked_At = CURRENT_TIMESTAMP
        WHERE Staff_ID = ?
          AND Reset_ID <> ?
          AND Used_At IS NULL
          AND Revoked_At IS NULL
          AND Expires_At > CURRENT_TIMESTAMP
    ');
    $statement->execute([$staffId, $resetId]);

    return $statement->rowCount();
}

/**
 * Update a password and increment Auth_Revision in one atomic statement.
 * The caller must hold the Staff row lock from
 * lockStaffForAuthenticationUpdate().
 *
 * @return int The new Auth_Revision value.
 */
function updateStaffPasswordAndIncrementAuthRevision(PDO $pdo, $staffId, $passwordHash)
{
    $statement = $pdo->prepare('
        UPDATE Staff
        SET Password = ?,
            Auth_Revision = Auth_Revision + 1
        WHERE Staff_ID = ?
          AND Auth_Revision >= 1
    ');
    $statement->execute([$passwordHash, $staffId]);

    if ($statement->rowCount() !== 1) {
        throw new RuntimeException('Authentication revision update failed.');
    }

    $revisionStatement = $pdo->prepare('
        SELECT Auth_Revision
        FROM Staff
        WHERE Staff_ID = ?
    ');
    $revisionStatement->execute([$staffId]);

    $revision = $revisionStatement->fetchColumn();

    if ($revision === false) {
        throw new RuntimeException('Authentication revision could not be read.');
    }

    return (int) $revision;
}

/**
 * Remove expired reset-token rows without touching active lifecycle rows.
 */
function deleteExpiredPasswordResetTokens(PDO $pdo)
{
    $statement = $pdo->prepare('
        DELETE FROM Password_Reset_Tokens
        WHERE Expires_At <= CURRENT_TIMESTAMP
    ');
    $statement->execute();

    return $statement->rowCount();
}
