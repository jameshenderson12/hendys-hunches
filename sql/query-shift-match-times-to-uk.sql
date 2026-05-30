UPDATE live_match_schedule
SET
    date = DATE(DATE_ADD(TIMESTAMP(date, kotime), INTERVAL 1 HOUR)),
    kotime = DATE_FORMAT(DATE_ADD(TIMESTAMP(date, kotime), INTERVAL 1 HOUR), '%H:%i');
