CREATE TABLE IF NOT EXISTS live_registration_invites (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    invite_token CHAR(64) NOT NULL,
    email_hint VARCHAR(255) DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    used_at DATETIME DEFAULT NULL,
    used_by_user_id SMALLINT(6) DEFAULT NULL,
    created_by SMALLINT(6) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_invite_token (invite_token),
    KEY idx_registration_invites_expires_at (expires_at),
    KEY idx_registration_invites_used_at (used_at)
);
