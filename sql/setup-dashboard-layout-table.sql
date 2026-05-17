CREATE TABLE IF NOT EXISTS live_dashboard_layout (
    id SMALLINT(6) NOT NULL AUTO_INCREMENT,
    layout_key VARCHAR(50) NOT NULL DEFAULT 'main',
    card_key VARCHAR(60) NOT NULL,
    sort_order TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
    card_width VARCHAR(12) NOT NULL DEFAULT 'normal',
    is_visible TINYINT(1) NOT NULL DEFAULT 1,
    updated_by SMALLINT(6) DEFAULT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_live_dashboard_layout (layout_key, card_key),
    KEY idx_live_dashboard_layout_updated_by (updated_by),
    CONSTRAINT fk_live_dashboard_layout_updated_by
        FOREIGN KEY (updated_by) REFERENCES live_user_information(id)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
