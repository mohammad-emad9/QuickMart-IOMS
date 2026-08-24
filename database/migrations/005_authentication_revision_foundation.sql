-- DB-06: Authentication revision database foundation
-- DDL only. Existing Staff rows receive the safe default revision of 1.

-- UP
ALTER TABLE Staff
    ADD COLUMN Auth_Revision INT UNSIGNED NOT NULL DEFAULT 1 AFTER Role,
    ADD CONSTRAINT chk_staff_auth_revision
        CHECK (Auth_Revision >= 1);

-- DOWN (execute manually when removing only the DB-06 schema change)
-- ALTER TABLE Staff
--     DROP CONSTRAINT chk_staff_auth_revision,
--     DROP COLUMN Auth_Revision;
