CREATE TABLE IF NOT EXISTS live_polls (
    id SMALLINT(6) NOT NULL AUTO_INCREMENT,
    question VARCHAR(255) NOT NULL,
    created_by SMALLINT(6) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_live_polls_active (is_active),
    KEY idx_live_polls_created_by (created_by),
    CONSTRAINT fk_live_polls_created_by
        FOREIGN KEY (created_by) REFERENCES live_user_information(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
