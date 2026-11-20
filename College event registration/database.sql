CREATE DATABASE IF NOT EXISTS `technovate_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `technovate_db`;

CREATE TABLE IF NOT EXISTS `registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reg_id` VARCHAR(30) NOT NULL UNIQUE,
  `student_name` VARCHAR(120) NOT NULL,
  `reg_number` VARCHAR(50) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `phone` VARCHAR(25) NOT NULL,
  `college_name` VARCHAR(160) NOT NULL,
  `department` VARCHAR(100) NOT NULL,
  `year_of_study` VARCHAR(25) NOT NULL,
  `event_categories` TEXT NOT NULL,
  `participation_date` DATE NOT NULL,
  `gender` VARCHAR(25) NOT NULL,
  `message` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_reg_id` (`reg_id`),
  INDEX `idx_reg_number` (`reg_number`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `registrations` 
  (`reg_id`, `student_name`, `reg_number`, `email`, `phone`, `college_name`, `department`, `year_of_study`, `event_categories`, `participation_date`, `gender`, `message`)
VALUES 
  ('TEC2026-0001', 'Alex Vance', 'RA2111003010450', 'alex.vance@apex.edu', '9876543210', 'Apex Institute of Technology', 'Computer Science & Engineering', '3rd Year', 'Coding Challenge, Web Development, Photography Challenge', '2026-12-26', 'Male', 'Ready for the technical and creative hackathons!')
ON DUPLICATE KEY UPDATE `reg_id`=`reg_id`;
