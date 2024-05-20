CREATE TABLE live_user_predictions_qf (
	id SMALLINT(6) NOT NULL,
	username CHAR(50) UNIQUE NOT NULL,
	firstname CHAR(50) NOT NULL,
	surname CHAR(50) NOT NULL,
	score89_p TINYINT(4) NOT NULL,
	score90_p TINYINT(4) NOT NULL,
	score91_p TINYINT(4) NOT NULL,
	score92_p TINYINT(4) NOT NULL,
	score93_p TINYINT(4) NOT NULL,
	score94_p TINYINT(4) NOT NULL,
	score95_p TINYINT(4) NOT NULL,
	score96_p TINYINT(4) NOT NULL,
	lastupdate TIMESTAMP NULL,
	points_total SMALLINT(6) DEFAULT '0'
);
