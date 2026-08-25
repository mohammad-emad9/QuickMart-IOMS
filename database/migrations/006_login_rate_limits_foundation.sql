-- HARDEN-01: persistent, bounded login-attempt rate limiting.
-- Apply after migrations 001 through 005.

-- UP
CREATE TABLE Login_Rate_Limits (
    Rate_Key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    Window_Started_At DATETIME NOT NULL,
    Attempt_Count SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    Last_Attempt_At DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (Rate_Key),
    CONSTRAINT chk_login_rate_limits_attempt_count CHECK (Attempt_Count >= 0),
    KEY idx_login_rate_limits_window (Window_Started_At)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- DOWN (execute manually after traffic is stopped and the limiter is disabled)
-- DROP TABLE IF EXISTS Login_Rate_Limits;
