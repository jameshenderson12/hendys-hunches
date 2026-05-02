CREATE TABLE IF NOT EXISTS live_user_predictions_sf (
    id SMALLINT(6) NOT NULL,
    username CHAR(50) UNIQUE NOT NULL,
    firstname CHAR(50) NOT NULL,
    surname CHAR(50) NOT NULL,
    score193_p TINYINT(4) NOT NULL,
    score194_p TINYINT(4) NOT NULL,
    score195_p TINYINT(4) NOT NULL,
    score196_p TINYINT(4) NOT NULL,
    score197_p TINYINT(4) NOT NULL,
    score198_p TINYINT(4) NOT NULL,
    score199_p TINYINT(4) NOT NULL,
    score200_p TINYINT(4) NOT NULL,
    lastupdate TIMESTAMP NULL,
    points_total SMALLINT(6) DEFAULT '0'
);
