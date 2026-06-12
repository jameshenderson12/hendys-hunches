CREATE TABLE IF NOT EXISTS live_prediction_access_overrides (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id SMALLINT(6) NOT NULL,
    stage_key VARCHAR(20) NOT NULL,
    granted_until DATETIME NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    granted_by SMALLINT(6) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_prediction_override_user_stage (user_id, stage_key),
    KEY idx_prediction_override_until (granted_until)
);
