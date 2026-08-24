<?php
/**
 * QuickMart IOMS - Staff Service
 * Staff application operations
 */

require_once __DIR__ . '/../../infrastructure/database/staff-repository.php';
require_once __DIR__ . '/../auth/auth-service.php';

/**
 * Retrieve one staff record while enforcing Admin/Staff ownership rules.
 *
 * @return array|null
 */
function getStaffById(PDO $pdo, $authenticatedStaffId, $role, $requestedStaffId)
{
    if (!is_string($role) || !in_array($role, ['Admin', 'Manager', 'Staff'], true)) {
        return null;
    }

    if ($role !== 'Admin' && $requestedStaffId !== $authenticatedStaffId) {
        return null;
    }

    $ownerStaffId = $role === 'Admin' ? null : $authenticatedStaffId;

    return findStaffById($pdo, $requestedStaffId, $ownerStaffId);
}

/**
 * Retrieve the complete staff list for an already-authorized Admin route.
 *
 * @return array
 */
function getStaffList(PDO $pdo)
{
    $staff = findStaffList($pdo);

    return [
        'staff' => $staff,
        'total' => count($staff)
    ];
}

/**
 * Update the authenticated staff member's public profile.
 *
 * @return array
 */
function updateOwnStaffProfile(PDO $pdo, $authenticatedStaffId, array $updates)
{
    if (!findStaffForProfileUpdate($pdo, $authenticatedStaffId)) {
        return ['status' => 'not_found'];
    }

    if (array_key_exists('email', $updates)
        && findOtherStaffByEmail($pdo, $updates['email'], $authenticatedStaffId)) {
        return ['status' => 'duplicate_email'];
    }

    if (empty($updates)) {
        return ['status' => 'no_fields'];
    }

    updateStaffProfileFields($pdo, $authenticatedStaffId, $updates);

    return [
        'status' => 'updated',
        'profile' => findPublicStaffProfile($pdo, $authenticatedStaffId)
    ];
}

/**
 * Change the authenticated staff member's password.
 *
 * @return array
 */
function changeOwnStaffPassword(PDO $pdo, $authenticatedStaffId, $currentPassword, $newPassword)
{
    if (!is_string($currentPassword) || !is_string($newPassword)) {
        return ['status' => 'invalid_password'];
    }

    return runAuthenticationRevisionTransaction($pdo, function () use (
        $pdo,
        $authenticatedStaffId,
        $currentPassword,
        $newPassword
    ) {
        $staff = lockStaffForAuthenticationUpdate($pdo, $authenticatedStaffId);

        if ($staff === false) {
            return ['status' => 'not_found'];
        }

        if (!password_verify($currentPassword, $staff['Password'])) {
            return ['status' => 'incorrect_current_password'];
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!is_string($newPasswordHash)) {
            throw new RuntimeException('Password hashing failed.');
        }

        $newRevision = updateStaffPasswordAndIncrementAuthRevision(
            $pdo,
            $authenticatedStaffId,
            $newPasswordHash
        );

        return [
            'status' => 'updated',
            'auth_revision' => $newRevision
        ];
    });
}

/**
 * Update a target staff member through the Admin staff-management flow.
 *
 * @return array
 */
function updateStaffByAdmin(PDO $pdo, $targetStaffId, array $updates)
{
    if (!findStaffForAdminUpdate($pdo, $targetStaffId)) {
        return ['status' => 'not_found'];
    }

    if (array_key_exists('email', $updates)
        && findOtherStaffByEmail($pdo, $updates['email'], $targetStaffId)) {
        return ['status' => 'duplicate_email'];
    }

    if (empty($updates)) {
        return ['status' => 'no_fields'];
    }

    if (!array_key_exists('password', $updates)) {
        updateStaffFieldsByAdmin($pdo, $targetStaffId, $updates);
    } else {
        $newPasswordHash = password_hash($updates['password'], PASSWORD_DEFAULT);
        if (!is_string($newPasswordHash)) {
            throw new RuntimeException('Password hashing failed.');
        }

        $profileUpdates = $updates;
        unset($profileUpdates['password']);

        $passwordUpdateResult = runAuthenticationRevisionTransaction($pdo, function () use (
            $pdo,
            $targetStaffId,
            $profileUpdates,
            $newPasswordHash
        ) {
            $staff = lockStaffForAuthenticationUpdate($pdo, $targetStaffId);

            if ($staff === false) {
                return false;
            }

            if (!empty($profileUpdates)) {
                updateStaffFieldsByAdmin($pdo, $targetStaffId, $profileUpdates);
            }

            updateStaffPasswordAndIncrementAuthRevision(
                $pdo,
                $targetStaffId,
                $newPasswordHash
            );

            return true;
        });

        if ($passwordUpdateResult !== true) {
            return ['status' => 'not_found'];
        }
    }

    return [
        'status' => 'updated',
        'staff' => findPublicStaffProfile($pdo, $targetStaffId)
    ];
}

/**
 * Delete a staff member when no orders reference the account.
 *
 * @return array
 */
function deleteStaffByAdmin(PDO $pdo, $targetStaffId)
{
    $ownsTransaction = !$pdo->inTransaction();

    try {
        if ($ownsTransaction) {
            $pdo->beginTransaction();
        }

        // Lock the parent row while checking references. The RESTRICT
        // foreign key remains the final integrity boundary for races.
        $staff = findStaffForDeletion($pdo, $targetStaffId);

        if (!$staff) {
            if ($ownsTransaction) {
                $pdo->commit();
            }

            return ['status' => 'not_found'];
        }

        $orderCount = (int) countStaffOrderReferences($pdo, $targetStaffId);

        if ($orderCount > 0) {
            if ($ownsTransaction) {
                $pdo->commit();
            }

            return [
                'status' => 'referenced',
                'order_count' => $orderCount
            ];
        }

        deleteStaffRecord($pdo, $targetStaffId);

        if ($ownsTransaction) {
            $pdo->commit();
        }

        return [
            'status' => 'deleted',
            'staff_id' => $staff['Staff_ID'],
            'name' => $staff['Full_Name']
        ];
    } catch (Throwable $e) {
        if ($ownsTransaction && $pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
