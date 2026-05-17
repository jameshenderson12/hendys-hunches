CREATE TABLE IF NOT EXISTS live_user_predictions_final (
    id SMALLINT(6) PRIMARY KEY NOT NULL,
    username CHAR(50) UNIQUE NOT NULL,
    firstname CHAR(50) NOT NULL,
    surname CHAR(50) NOT NULL,
    score205_p TINYINT(4) DEFAULT NULL,
    score206_p TINYINT(4) DEFAULT NULL,
    score207_p TINYINT(4) DEFAULT NULL,
    score208_p TINYINT(4) DEFAULT NULL,
    lastupdate TIMESTAMP NULL,
    points_total SMALLINT(6) DEFAULT '0'
);
