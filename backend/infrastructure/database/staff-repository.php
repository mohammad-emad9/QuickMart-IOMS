<?php
/**
 * QuickMart IOMS - Staff Repository
 * Staff persistence operations
 */

/**
 * Check whether a database role is one of the roles supported by the
 * application contract.
 */
function isSupportedStaffRole($role)
{
    return is_string($role) && in_array($role, ['Admin', 'Manager', 'Staff'], true);
}

/**
 * Find the current staff authentication context without exposing passwords.
 * This is used to refresh session identity and role data at protected
 * backend boundaries.
 *
 * @return array|false
 */
function findStaffAuthContext(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare(
        "SELECT Staff_ID, Full_Name, Email, Role
         FROM Staff
         WHERE Staff_ID = ?"
    );
    $statement->execute([$staffId]);

    return $statement->fetch();
}

/**
 * Find one staff record without exposing password fields.
 *
 * @return array|null
 */
function findStaffById(PDO $pdo, $requestedStaffId, $ownerStaffId = null)
{
    $sql = "
        SELECT Staff_ID, Full_Name, Email, Role, Phone_Number
        FROM Staff
        WHERE Staff_ID = ?";
    $params = [$requestedStaffId];

    if ($ownerStaffId !== null) {
        $sql .= " AND Staff_ID = ?";
        $params[] = $ownerStaffId;
    }

    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $staff = $statement->fetch();

    return $staff === false ? null : $staff;
}

/**
 * Find all staff records without exposing password fields.
 *
 * @return array
 */
function findStaffList(PDO $pdo)
{
    $statement = $pdo->query("
        SELECT Staff_ID, Full_Name, Email, Phone_Number, Role
        FROM Staff
        ORDER BY Staff_ID ASC
    ");

    return $statement->fetchAll();
}

/**
 * Find the authenticated staff record needed for a profile update.
 *
 * @return array|false
 */
function findStaffForProfileUpdate(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare("SELECT Staff_ID FROM Staff WHERE Staff_ID = ?");
    $statement->execute([$staffId]);

    return $statement->fetch();
}

/**
 * Find an email owned by another staff member.
 *
 * @return array|false
 */
function findOtherStaffByEmail(PDO $pdo, $email, $staffId)
{
    $statement = $pdo->prepare("SELECT Staff_ID FROM Staff WHERE Email = ? AND Staff_ID != ?");
    $statement->execute([$email, $staffId]);

    return $statement->fetch();
}

/**
 * Find any staff member that owns an email address.
 *
 * This is used after an INSERT duplicate-key race to distinguish a unique
 * email conflict from an extremely unlikely Staff_ID collision.
 *
 * @return array|false
 */
function findStaffByEmail(PDO $pdo, $email)
{
    // FOR UPDATE makes the duplicate-email check a current read inside the
    // creation transaction, including when another insert just committed.
    $statement = $pdo->prepare("SELECT Staff_ID FROM Staff WHERE Email = ? FOR UPDATE");
    $statement->execute([$email]);

    return $statement->fetch();
}

/**
 * Insert a staff account using only database-owned authentication defaults.
 * Auth_Revision is intentionally omitted so its DEFAULT 1 remains authoritative.
 */
function insertStaffRecord(
    PDO $pdo,
    $staffId,
    $fullName,
    $email,
    $phoneNumber,
    $role,
    $passwordHash
)
{
    $statement = $pdo->prepare(
        "INSERT INTO Staff (
            Staff_ID,
            Full_Name,
            Email,
            Password,
            Phone_Number,
            Role
        ) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $statement->execute([
        $staffId,
        $fullName,
        $email,
        $passwordHash,
        $phoneNumber,
        $role
    ]);
}

/**
 * Update only the allowlisted public profile fields.
 */
function updateStaffProfileFields(PDO $pdo, $staffId, array $updates)
{
    $columns = [
        'full_name' => 'Full_Name',
        'email' => 'Email',
        'phone_number' => 'Phone_Number'
    ];
    $fieldOrder = ['full_name', 'email', 'phone_number'];
    $setClauses = [];
    $params = [];

    foreach ($fieldOrder as $field) {
        if (array_key_exists($field, $updates)) {
            $setClauses[] = $columns[$field] . ' = ?';
            $params[] = $updates[$field];
        }
    }

    $params[] = $staffId;
    $statement = $pdo->prepare(
        "UPDATE Staff SET " . implode(', ', $setClauses) . " WHERE Staff_ID = ?"
    );
    $statement->execute($params);
}

/**
 * Fetch the public staff profile without password fields.
 *
 * @return array|false
 */
function findPublicStaffProfile(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare("SELECT Staff_ID, Full_Name, Email, Phone_Number, Role FROM Staff WHERE Staff_ID = ?");
    $statement->execute([$staffId]);

    return $statement->fetch();
}

/**
 * Find only the stored password for an authenticated staff member.
 *
 * @return array|false
 */
function findStaffPasswordById(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare("SELECT Password FROM Staff WHERE Staff_ID = ?");
    $statement->execute([$staffId]);

    return $statement->fetch();
}

/**
 * Persist a staff password by its identifier.
 */
function updateStaffPassword(PDO $pdo, $staffId, $passwordHash)
{
    $statement = $pdo->prepare("UPDATE Staff SET Password = ? WHERE Staff_ID = ?");
    $statement->execute([$passwordHash, $staffId]);
}

/**
 * Find the target staff record needed for an Admin update.
 *
 * @return array|false
 */
function findStaffForAdminUpdate(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare("SELECT Staff_ID FROM Staff WHERE Staff_ID = ?");
    $statement->execute([$staffId]);

    return $statement->fetch();
}

/**
 * Update only the allowlisted Admin staff fields in the existing field order.
 */
function updateStaffFieldsByAdmin(PDO $pdo, $staffId, array $updates)
{
    $columns = [
        'full_name' => 'Full_Name',
        'email' => 'Email',
        'phone_number' => 'Phone_Number',
        'role' => 'Role',
        'password' => 'Password'
    ];
    $fieldOrder = ['full_name', 'email', 'phone_number', 'role', 'password'];
    $setClauses = [];
    $params = [];

    foreach ($fieldOrder as $field) {
        if (array_key_exists($field, $updates)) {
            $setClauses[] = $columns[$field] . ' = ?';
            $params[] = $updates[$field];
        }
    }

    $params[] = $staffId;
    $statement = $pdo->prepare(
        "UPDATE Staff SET " . implode(', ', $setClauses) . " WHERE Staff_ID = ?"
    );
    $statement->execute($params);
}

/**
 * Find the staff fields needed before deletion.
 *
 * @return array|false
 */
function findStaffForDeletion(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare(
        "SELECT Staff_ID, Full_Name, Role
         FROM Staff
         WHERE Staff_ID = ?
         FOR UPDATE"
    );
    $statement->execute([$staffId]);

    return $statement->fetch();
}

/**
 * Count orders that reference a staff member.
 */
function countStaffOrderReferences(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare("SELECT COUNT(*) FROM Orders WHERE Staff_ID = ?");
    $statement->execute([$staffId]);

    return $statement->fetchColumn();
}

/**
 * Delete a staff record by its identifier.
 */
function deleteStaffRecord(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare("DELETE FROM Staff WHERE Staff_ID = ?");
    $statement->execute([$staffId]);
}
