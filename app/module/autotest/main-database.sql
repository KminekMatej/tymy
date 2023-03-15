SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `api_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `remote_host` varchar(255) DEFAULT NULL,
  `request_url` varchar(1024) DEFAULT NULL,
  `response_status` int(11) DEFAULT NULL,
  `time_in_ms` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `languages` (
  `code` char(3) NOT NULL DEFAULT '',
  `name` varchar(30) DEFAULT NULL,
  `gui` enum('YES','NO') DEFAULT 'YES',
  `content_type_header` varchar(100) DEFAULT NULL,
  `content_language_header` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `multi_accounts` (
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `account_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transfer_key` varchar(50) DEFAULT NULL,
  `tk_dtm` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `caption` varchar(100) DEFAULT NULL,
  `descr` mediumtext DEFAULT NULL,
  `lc` char(3) DEFAULT NULL,
  `inserted` timestamp NULL DEFAULT current_timestamp(),
  `team` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `file_name` varchar(30) DEFAULT NULL,
  `exe_file_name` varchar(30) DEFAULT NULL,
  `cached` enum('yes','no') DEFAULT 'yes',
  `public` enum('yes','no') DEFAULT 'no',
  `output_type` varchar(20) DEFAULT 'HTML'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `strings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `domain` varchar(50) NOT NULL,
  `code` varchar(100) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `language` char(2) DEFAULT 'en'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `sys_name` varchar(30) DEFAULT NULL,
  `db_name` varchar(50) DEFAULT NULL,
  `languages` varchar(30) DEFAULT NULL,
  `default_lc` char(3) DEFAULT NULL,
  `sport` varchar(30) DEFAULT NULL,
  `account_number` varchar(32) DEFAULT NULL,
  `web` varchar(50) DEFAULT NULL,
  `country_id` int(11) DEFAULT 0,
  `attend_email` varchar(30) DEFAULT NULL COMMENT 'DEPRECATED',
  `excuse_email` varchar(30) DEFAULT NULL COMMENT 'DEPRECATED',
  `modules` varchar(100) DEFAULT NULL COMMENT 'DEPRECATED',
  `max_users` int(11) DEFAULT 100,
  `max_events_month` int(11) DEFAULT 100,
  `advertisement` enum('YES','NO') DEFAULT 'YES',
  `active` enum('YES','NO') DEFAULT 'NO',
  `retranslate` enum('YES','NO') DEFAULT 'NO',
  `insert_date` datetime DEFAULT current_timestamp(),
  `time_zone` smallint(6) DEFAULT 1,
  `dst_flag` enum('YES','NO','AUTO') DEFAULT 'AUTO',
  `app_version` varchar(10) DEFAULT '0.2' COMMENT 'DEPRECATED',
  `use_namedays` enum('YES','NO') DEFAULT 'YES' COMMENT 'DEPRECATED',
  `att_check` enum('NO','MESSAGE','FW') DEFAULT 'MESSAGE',
  `att_check_days` int(11) DEFAULT 7,
  `create_step` int(11) DEFAULT 0,
  `activation_key` varchar(50) DEFAULT NULL,
  `host` varchar(100) DEFAULT 'FREE',
  `tariff` enum('FREE','LITE','FULL','FREE-LITE','FREE-FULL') NOT NULL DEFAULT 'FREE-FULL',
  `tariff_until` date DEFAULT NULL,
  `tariff_payment` enum('MONTHLY','QUARTERLY','YEARLY','OTHER') DEFAULT NULL,
  `skin` varchar(32) DEFAULT NULL,
  `required_fields` varchar(255) NOT NULL DEFAULT 'gender,firstName,lastName,phone,email,birthDate,callName,status,jerseyNumber,street,city,zipCode'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


ALTER TABLE `api_log`
  ADD UNIQUE KEY `id` (`id`);

ALTER TABLE `languages`
  ADD PRIMARY KEY (`code`);

ALTER TABLE `multi_accounts`
  ADD PRIMARY KEY (`team_id`,`user_id`);

ALTER TABLE `news`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `strings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `domain_code` (`domain`,`code`,`language`);

ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `api_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `strings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `multi_accounts`
  ADD CONSTRAINT `multi_accounts_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
