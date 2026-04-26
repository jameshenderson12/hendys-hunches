CREATE TABLE IF NOT EXISTS live_fanzone_posts (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    parent_id INT UNSIGNED NULL,
    username VARCHAR(50) NOT NULL,
    display_name VARCHAR(120) NOT NULL,
    message_body TEXT NOT NULL,
    is_deleted TINYINT(1) NOT NULL DEFAULT 0,
    is_pinned TINYINT(1) NOT NULL DEFAULT 0,
    is_announcement TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_fanzone_parent (parent_id),
    KEY idx_fanzone_created (created_at),
    KEY idx_fanzone_pinned (is_pinned),
    CONSTRAINT fk_fanzone_parent
        FOREIGN KEY (parent_id) REFERENCES live_fanzone_posts(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE live_fanzone_posts
    ADD COLUMN IF NOT EXISTS is_pinned TINYINT(1) NOT NULL DEFAULT 0 AFTER is_deleted,
    ADD COLUMN IF NOT EXISTS is_announcement TINYINT(1) NOT NULL DEFAULT 0 AFTER is_pinned;
