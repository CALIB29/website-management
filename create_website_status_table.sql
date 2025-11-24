-- Create website_status table for uptime monitoring
CREATE TABLE IF NOT EXISTS `website_status` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `website_id` INT NOT NULL,
  `status` ENUM('up', 'down', 'unknown') DEFAULT 'unknown',
  `last_checked` DATETIME,
  `response_time` INT,
  `last_error` VARCHAR(255),
  FOREIGN KEY (`website_id`) REFERENCES `websites`(`id`) ON DELETE CASCADE
);
