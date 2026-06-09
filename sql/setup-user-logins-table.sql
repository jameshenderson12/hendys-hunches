CREATE TABLE IF NOT EXISTS live_user_logins (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id SMALLINT(6) NOT NULL,
    logged_in_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_live_user_logins_user (user_id),
    KEY idx_live_user_logins_logged_in_at (logged_in_at),
    CONSTRAINT fk_live_user_logins_user
        FOREIGN KEY (user_id) REFERENCES live_user_information(id)
        ON DELETE CASCADE
);
