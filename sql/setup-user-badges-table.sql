CREATE TABLE IF NOT EXISTS live_user_badges (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id SMALLINT(6) NOT NULL,
    badge_token VARCHAR(12) NOT NULL,
    awarded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    notified_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_live_user_badges_user_token (user_id, badge_token),
    KEY idx_live_user_badges_token (badge_token),
    KEY idx_live_user_badges_awarded_at (awarded_at),
    CONSTRAINT fk_live_user_badges_user
        FOREIGN KEY (user_id) REFERENCES live_user_information(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
