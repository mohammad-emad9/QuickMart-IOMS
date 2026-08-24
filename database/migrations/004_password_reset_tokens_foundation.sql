-- DB-05: Password reset token database foundation
-- DDL only. Raw reset tokens must never be stored in this table.

-- UP
CREATE TABLE Password_Reset_Tokens (
    Reset_ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    Staff_ID VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    Token_Hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    Expires_At DATETIME NOT NULL,
    Used_At DATETIME NULL DEFAULT NULL,
    Revoked_At DATETIME NULL DEFAULT NULL,
    Created_At DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Reset_ID),
    UNIQUE KEY uq_password_reset_tokens_token_hash (Token_Hash),
    KEY idx_password_reset_tokens_staff_created (Staff_ID, Created_At),
    KEY idx_password_reset_tokens_expires_at (Expires_At),
    CONSTRAINT chk_password_reset_tokens_hash_format
        CHECK (BINARY Token_Hash REGEXP '^[0-9A-Fa-f]{64}$'),
    CONSTRAINT fk_password_reset_tokens_staff
        FOREIGN KEY (Staff_ID) REFERENCES Staff(Staff_ID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- DOWN (manual, executable statement):
-- DROP TABLE IF EXISTS Password_Reset_Tokens;
-- This removes only ephemeral reset-token records and leaves Staff and order history intact.
