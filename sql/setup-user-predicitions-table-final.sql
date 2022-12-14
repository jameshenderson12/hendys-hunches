CREATE TABLE live_user_predictions_final (
	id SMALLINT(6) NOT NULL,
	username CHAR(50) UNIQUE NOT NULL,
	firstname CHAR(50) NOT NULL,
	surname CHAR(50) NOT NULL,
	score125_p TINYINT(4) NOT NULL,
	score126_p TINYINT(4) NOT NULL,
	score127_p TINYINT(4) NOT NULL,
	score128_p TINYINT(4) NOT NULL,
	lastupdate TIMESTAMP NULL,
	points_total SMALLINT(6) DEFAULT '0'
);
