CREATE TABLE live_poll_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255),
    answer VARCHAR(255),
    count INT DEFAULT 0
);