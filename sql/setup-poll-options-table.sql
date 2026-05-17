CREATE TABLE IF NOT EXISTS live_poll_options (
    id SMALLINT(6) NOT NULL AUTO_INCREMENT,
    poll_id SMALLINT(6) NOT NULL,
    option_label VARCHAR(120) NOT NULL,
    sort_order TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_live_poll_options_poll (poll_id),
    CONSTRAINT fk_live_poll_options_poll
        FOREIGN KEY (poll_id) REFERENCES live_polls(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
