CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NOT NULL,
  `action` VARCHAR(64) NOT NULL,
  `details` TEXT,
  `ip_address` VARCHAR(45),
  `timestamp` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`admin_id`),
  INDEX (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
