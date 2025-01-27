
CREATE DATABASE IF NOT EXISTS `rest-api` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `rest-api`;

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_name` VARCHAR(50) NOT NULL DEFAULT '',
	`profile_image` VARCHAR(50) NULL DEFAULT '',
	`gender` CHAR(10) NOT NULL DEFAULT 'male',
	`first_name` VARCHAR(50) NULL DEFAULT '',
	`last_name` VARCHAR(50) NULL DEFAULT '',
	`email` VARCHAR(100) NOT NULL DEFAULT '',
	`password` VARCHAR(100) NOT NULL DEFAULT '',
	`personal_info` TEXT NULL,
	`latitude` VARCHAR(50) NULL DEFAULT NULL,
	`longitude` VARCHAR(50) NULL DEFAULT NULL,
	`last_api_time` DATETIME NULL DEFAULT NULL,
	`device_token` VARCHAR(50) NULL DEFAULT '1',
	`device_model` VARCHAR(50) NULL DEFAULT '1',
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`),
	UNIQUE INDEX `email` (`email`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

INSERT INTO `users` (`id`, `user_name`, `profile_image`, `gender`, `first_name`, `last_name`, `email`, `password`, `personal_info`, `latitude`, `longitude`, `last_api_time`, `device_token`, `device_model`, `created_at`, `updated_at`) VALUES
	(1, 'mx', '', 'male', 'Mr.', 'X', 'x@gmail.com', '$2y$12$fBIXhUMvf3xhvz937bzpiOsNJ3rEgGlKHzOoEftzVssDAbwVgDwx.', 'I am a tester', '23.750580', '90.38872', '2018-11-08 18:54:35', 'dummy_token', 'Sony SO-04K', '2018-07-14 01:29:08', '2018-11-08 18:54:35'),
	(2, 'my', '', 'male', 'Mr.', 'Y', 'y@gmail.com', '$2y$12$fBIXhUMvf3xhvz937bzpiOsNJ3rEgGlKHzOoEftzVssDAbwVgDwx.', 'I am a tester', '23.750380', '90.38872', '2018-11-08 18:54:52', 'dummy_token', 'iPhone SE', '2018-07-14 03:17:18', '2018-11-08 18:54:52'),
	(3, 'mz', '', 'male', 'Mr.', 'Z', 'z@gmail.com', '$2y$12$fBIXhUMvf3xhvz937bzpiOsNJ3rEgGlKHzOoEftzVssDAbwVgDwx.', 'I am a tester', '23.755480', '90.38872', '2018-09-24 08:13:01', 'dummy_token', 'iPhone X', '2018-07-14 03:21:25', '2018-09-24 08:13:01'),
	(4, 'ma', '', 'male', 'Mr.', 'A', 'a@gmail.com', '$2y$12$fBIXhUMvf3xhvz937bzpiOsNJ3rEgGlKHzOoEftzVssDAbwVgDwx.', 'I am a tester', '23.75126', '90.38872', '2018-12-03 12:30:35', '1', '1', '2018-07-14 03:42:16', '2018-12-03 12:30:35'),
	(5, 'mb', '', 'male', 'Mr.', 'B', 'b@gmail.com', '$2y$12$fBIXhUMvf3xhvz937bzpiOsNJ3rEgGlKHzOoEftzVssDAbwVgDwx.', 'I am a tester', '23.750480', '90.38872', '2018-08-04 05:59:28', '1', '1', '2018-07-14 04:53:39', '2018-10-17 12:28:48'),
	(6, 'mc', '', 'male', 'Ms.', 'C', 'c@gmail.com', '$2y$12$fBIXhUMvf3xhvz937bzpiOsNJ3rEgGlKHzOoEftzVssDAbwVgDwx.', 'I am a tester', '23.75144', '90.38872', '2018-09-24 04:39:19', 'dummy_token', 'iPhone X', '2018-07-14 05:19:21', '2018-09-24 04:39:19'),
	(7, 'md', '', 'male', 'Ms.', 'D', 'd@gmail.com', '$2y$12$fBIXhUMvf3xhvz937bzpiOsNJ3rEgGlKHzOoEftzVssDAbwVgDwx.', 'I am a tester', '23.750480', '135.3217', '2018-09-24 06:55:45', 'dummy_token', 'iPhone X', '2018-07-14 12:39:47', '2018-09-24 06:55:45'),
	(9, 'me', '', 'male', 'Mrs.', 'E', 'e@gmail.com', '$2y$12$fBIXhUMvf3xhvz937bzpiOsNJ3rEgGlKHzOoEftzVssDAbwVgDwx.', 'I am a tester', '23.75048', '90.38872', '2018-11-12 17:27:45', 'dummy_token', 'MacBookPro12,1', '2018-07-16 05:14:45', '2018-11-12 17:27:45'),
	(10, 'mf', '', 'male', 'Mrs.', 'F', 'f@gmail.com', '$2y$12$fBIXhUMvf3xhvz937bzpiOsNJ3rEgGlKHzOoEftzVssDAbwVgDwx.', 'I am a tester', '23.750480', '90.388720', '2018-11-08 18:46:40', '1', '1', '2018-07-16 08:45:42', '2018-11-08 18:46:40');

DROP TABLE IF EXISTS `user_login_sessions`;
CREATE TABLE `user_login_sessions` (
	`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) UNSIGNED NOT NULL COMMENT 'userID',
	`session_id` VARCHAR(64) NOT NULL COMMENT 'sessionID',
	`login_type` TINYINT(1) NOT NULL DEFAULT '1' COMMENT 'Type of login (if any)  1 = email-pass',
	`login_count` SMALLINT(6) NOT NULL DEFAULT '1',
	`time` INT(11) NOT NULL DEFAULT '0',
	`created_at` DATETIME NOT NULL COMMENT 'Row create time',
	`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Update time',
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;

DROP TABLE IF EXISTS `user_items`;
CREATE TABLE `user_items` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NULL DEFAULT NULL,
	`item_name` VARCHAR(100) NULL DEFAULT NULL,
	`created_at` DATETIME NULL DEFAULT NULL,
	`updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	PRIMARY KEY (`id`)
)
ENGINE=InnoDB;

CREATE TABLE `api_exec_time` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`api_name` VARCHAR(50) NOT NULL,
	`exec_time` FLOAT NOT NULL,
	PRIMARY KEY (`id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
