CREATE TABLE IF NOT EXISTS live_group_standings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(10) NOT NULL,
    team_name VARCHAR(255) NOT NULL,
    team_img VARCHAR(255),
    played INT DEFAULT 0,
    won INT DEFAULT 0,
    drawn INT DEFAULT 0,
    lost INT DEFAULT 0,
    goals_for INT DEFAULT 0,
    goals_against INT DEFAULT 0,
    points INT DEFAULT 0,
    goal_difference INT DEFAULT 0
);