-- Run this on the DB VM's MySQL (testdb database) to create the reviews table.
-- Example:  mysql -u testUser -p12345 testdb < create_reviews_table.sql

CREATE TABLE IF NOT EXISTS reviews (
    review_id   INT AUTO_INCREMENT PRIMARY KEY,
    userid      INT NOT NULL,
    username    VARCHAR(255) NOT NULL,
    subject     VARCHAR(255) NOT NULL COMMENT 'Artist or track being reviewed',
    rating      TINYINT NOT NULL,
    review_text TEXT NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reviews_userid (userid),
    INDEX idx_reviews_subject (subject)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
