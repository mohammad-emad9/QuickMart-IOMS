-- DB-03: enforce Staff role and required text integrity.
-- Apply only after verifying existing Staff rows contain canonical roles and
-- nonblank Full_Name and Email values. This migration is DDL-only; it does not
-- backfill or alter seed data.

-- UP
ALTER TABLE Staff
    MODIFY COLUMN Role VARCHAR(20) NOT NULL DEFAULT 'Staff',
    ADD CONSTRAINT chk_staff_full_name_nonempty
        CHECK (Full_Name REGEXP '[^[:space:]]'),
    ADD CONSTRAINT chk_staff_email_nonempty
        CHECK (Email REGEXP '[^[:space:]]'),
    ADD CONSTRAINT chk_staff_role
        CHECK (BINARY Role IN ('Admin', 'Manager', 'Staff'));

-- The inline UNIQUE constraint on Staff.Email already provides the complete
-- lookup and uniqueness index. The previous non-unique duplicate is removed.
ALTER TABLE Staff
    DROP INDEX idx_staff_email;

-- DOWN (execute manually, in this order, only when restoring the pre-DB-03 schema)
-- ALTER TABLE Staff
--     DROP CONSTRAINT chk_staff_role,
--     DROP CONSTRAINT chk_staff_email_nonempty,
--     DROP CONSTRAINT chk_staff_full_name_nonempty;
-- ALTER TABLE Staff
--     MODIFY COLUMN Role VARCHAR(20) NULL DEFAULT 'Staff';
-- CREATE INDEX idx_staff_email ON Staff(Email);

-- The DOWN plan restores the prior nullable/unconstrained Staff.Role and
-- permits blank names/emails again. It also restores the redundant index for
-- schema compatibility; it is not needed for the current query patterns.
