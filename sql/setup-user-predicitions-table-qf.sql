CREATE TABLE live_user_predictions_qf (
	id SMALLINT(6) NOT NULL,
	username CHAR(50) UNIQUE NOT NULL,
	firstname CHAR(50) NOT NULL,
	surname CHAR(50) NOT NULL,
	score113_p TINYINT(4) NOT NULL,
	score114_p TINYINT(4) NOT NULL,
	score115_p TINYINT(4) NOT NULL,
	score116_p TINYINT(4) NOT NULL,
	score117_p TINYINT(4) NOT NULL,
	score118_p TINYINT(4) NOT NULL,
	score119_p TINYINT(4) NOT NULL,
	score120_p TINYINT(4) NOT NULL,
	lastupdate TIMESTAMP NULL,
	points_total SMALLINT(6) DEFAULT '0'
);
