CREATE TABLE IF NOT EXISTS live_user_minileague (
    id SMALLINT(6) NOT NULL AUTO_INCREMENT,
    owner_id SMALLINT(6) NOT NULL,
    member_id SMALLINT(6) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_live_user_minileague_owner_member (owner_id, member_id),
    KEY idx_live_user_minileague_owner (owner_id),
    KEY idx_live_user_minileague_member (member_id),
    CONSTRAINT fk_live_user_minileague_owner
        FOREIGN KEY (owner_id) REFERENCES live_user_information(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_live_user_minileague_member
        FOREIGN KEY (member_id) REFERENCES live_user_information(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
