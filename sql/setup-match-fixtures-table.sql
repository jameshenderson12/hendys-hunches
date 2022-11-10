CREATE TABLE live_match_schedule (
	id SMALLINT(6) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	hometeamimg CHAR(50) NOT NULL,
	hometeam CHAR(50) NOT NULL,
    homescore SMALLINT(6) NULL,
    awayscore SMALLINT(6) NULL,
	awayteam CHAR(50) NOT NULL,
	awayteamimg CHAR(50) NOT NULL,
	venue CHAR(70) NOT NULL,
	kotime CHAR(5) NOT NULL,
	date DATE NULL
);
