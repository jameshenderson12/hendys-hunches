CREATE TABLE live_user_predictions_groups (
	id SMALLINT(6) NOT NULL,
	username CHAR(50) UNIQUE NOT NULL,
	firstname CHAR(50) NOT NULL,
	surname CHAR(50) NOT NULL,
    score1_p TINYINT(4) NOT NULL,
    score2_p TINYINT(4) NOT NULL,
    score3_p TINYINT(4) NOT NULL,
	score4_p TINYINT(4) NOT NULL,
	score5_p TINYINT(4) NOT NULL,
    score6_p TINYINT(4) NOT NULL,
    score7_p TINYINT(4) NOT NULL,
    score8_p TINYINT(4) NOT NULL,
    score9_p TINYINT(4) NOT NULL,
    score10_p TINYINT(4) NOT NULL,
    score11_p TINYINT(4) NOT NULL,
	score12_p TINYINT(4) NOT NULL,
	score13_p TINYINT(4) NOT NULL,
    score14_p TINYINT(4) NOT NULL,
    score15_p TINYINT(4) NOT NULL,
    score16_p TINYINT(4) NOT NULL,
    score17_p TINYINT(4) NOT NULL,
    score18_p TINYINT(4) NOT NULL,
    score19_p TINYINT(4) NOT NULL,
	score20_p TINYINT(4) NOT NULL,
	score21_p TINYINT(4) NOT NULL,
    score22_p TINYINT(4) NOT NULL,
    score23_p TINYINT(4) NOT NULL,
    score24_p TINYINT(4) NOT NULL,
    score25_p TINYINT(4) NOT NULL,
    score26_p TINYINT(4) NOT NULL,
	score27_p TINYINT(4) NOT NULL,
	score28_p TINYINT(4) NOT NULL,
    score29_p TINYINT(4) NOT NULL,
    score30_p TINYINT(4) NOT NULL,
    score31_p TINYINT(4) NOT NULL,
    score32_p TINYINT(4) NOT NULL,
    score33_p TINYINT(4) NOT NULL,
    score34_p TINYINT(4) NOT NULL,
	score35_p TINYINT(4) NOT NULL,
	score36_p TINYINT(4) NOT NULL,
    score37_p TINYINT(4) NOT NULL,
    score38_p TINYINT(4) NOT NULL,
    score39_p TINYINT(4) NOT NULL,
    score40_p TINYINT(4) NOT NULL,
    score41_p TINYINT(4) NOT NULL,
    score42_p TINYINT(4) NOT NULL,
	score43_p TINYINT(4) NOT NULL,
	score44_p TINYINT(4) NOT NULL,
    score45_p TINYINT(4) NOT NULL,
    score46_p TINYINT(4) NOT NULL,
    score47_p TINYINT(4) NOT NULL,
    score48_p TINYINT(4) NOT NULL,
    score49_p TINYINT(4) NOT NULL,
    score50_p TINYINT(4) NOT NULL,
	score51_p TINYINT(4) NOT NULL,
	score52_p TINYINT(4) NOT NULL,
    score53_p TINYINT(4) NOT NULL,
    score54_p TINYINT(4) NOT NULL,
    score55_p TINYINT(4) NOT NULL,
    score56_p TINYINT(4) NOT NULL,
    score57_p TINYINT(4) NOT NULL,
    score58_p TINYINT(4) NOT NULL,
	score59_p TINYINT(4) NOT NULL,
	score60_p TINYINT(4) NOT NULL,
    score61_p TINYINT(4) NOT NULL,
    score62_p TINYINT(4) NOT NULL,
    score63_p TINYINT(4) NOT NULL,
    score64_p TINYINT(4) NOT NULL,
    score65_p TINYINT(4) NOT NULL,
	score66_p TINYINT(4) NOT NULL,
	score67_p TINYINT(4) NOT NULL,
    score68_p TINYINT(4) NOT NULL,
    score69_p TINYINT(4) NOT NULL,
    score70_p TINYINT(4) NOT NULL,
    score71_p TINYINT(4) NOT NULL,
    score72_p TINYINT(4) NOT NULL,
	lastupdate TIMESTAMP NULL,
	points_total SMALLINT(6) DEFAULT '0'
);
