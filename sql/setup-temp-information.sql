CREATE TABLE live_temp_information (
	id SMALLINT(6) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username CHAR(50) UNIQUE NOT NULL,
    temp_pass CHAR(50) NOT NULL	
);
