CREATE TABLE live_user_predictions_ro16 (
	id SMALLINT(6) NOT NULL,
	username CHAR(50) UNIQUE NOT NULL,
	firstname CHAR(50) NOT NULL,
	surname CHAR(50) NOT NULL,
	score97_p TINYINT(4) NOT NULL,
	score98_p TINYINT(4) NOT NULL,
	score99_p TINYINT(4) NOT NULL,
	score100_p TINYINT(4) NOT NULL,
	score101_p TINYINT(4) NOT NULL,
	score102_p TINYINT(4) NOT NULL,
	score103_p TINYINT(4) NOT NULL,
	score104_p TINYINT(4) NOT NULL,
	score105_p TINYINT(4) NOT NULL,
	score106_p TINYINT(4) NOT NULL,
	score107_p TINYINT(4) NOT NULL,
	score108_p TINYINT(4) NOT NULL,
	score109_p TINYINT(4) NOT NULL,
	score110_p TINYINT(4) NOT NULL,
	score111_p TINYINT(4) NOT NULL,
	score112_p TINYINT(4) NOT NULL,
	lastupdate TIMESTAMP NULL,
	points_total SMALLINT(6) DEFAULT '0'
);
