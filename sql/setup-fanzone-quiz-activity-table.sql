CREATE TABLE IF NOT EXISTS live_fanzone_quiz_activity (
    id INT NOT NULL AUTO_INCREMENT,
    user_id SMALLINT(6) NOT NULL,
    username VARCHAR(120) NOT NULL,
    session_key CHAR(36) NOT NULL,
    event_type VARCHAR(40) NOT NULL,
    question_number SMALLINT(6) DEFAULT NULL,
    question_text VARCHAR(255) DEFAULT NULL,
    selected_answer VARCHAR(255) DEFAULT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,
    score_total SMALLINT(6) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_live_fanzone_quiz_activity_user (user_id),
    KEY idx_live_fanzone_quiz_activity_session (session_key),
    KEY idx_live_fanzone_quiz_activity_event (event_type),
    CONSTRAINT fk_live_fanzone_quiz_activity_user
        FOREIGN KEY (user_id) REFERENCES live_user_information(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
