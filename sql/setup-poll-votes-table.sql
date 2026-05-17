CREATE TABLE IF NOT EXISTS live_poll_votes (
    id INT NOT NULL AUTO_INCREMENT,
    poll_id SMALLINT(6) NOT NULL,
    option_id SMALLINT(6) NOT NULL,
    user_id SMALLINT(6) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_live_poll_votes_poll_user (poll_id, user_id),
    KEY idx_live_poll_votes_option (option_id),
    KEY idx_live_poll_votes_user (user_id),
    CONSTRAINT fk_live_poll_votes_poll
        FOREIGN KEY (poll_id) REFERENCES live_polls(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_live_poll_votes_option
        FOREIGN KEY (option_id) REFERENCES live_poll_options(id)
        ON DELETE CASCADE,
    CONSTRAINT fk_live_poll_votes_user
        FOREIGN KEY (user_id) REFERENCES live_user_information(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
