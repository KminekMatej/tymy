
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 28.10.2022 09:45:38


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

DROP TABLE IF EXISTS `download`;
DROP TABLE IF EXISTS `download_log`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `rep_columns`;
DROP TABLE IF EXISTS `pages_log`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `rights_cache`;
DROP TABLE IF EXISTS `web_data`;
DROP TABLE IF EXISTS `web_news`;

ALTER TABLE `rights` 
CHANGE `right_type` `right_type` ENUM('SYS','PAGE','USR') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'USR', 
CHANGE `name` `name` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
CHANGE `caption` `caption` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `a_roles` `a_roles` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `r_roles` `r_roles` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `a_statuses` `a_statuses` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `r_statuses` `r_statuses` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `a_users` `a_users` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `r_users` `r_users` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


ALTER TABLE `ask_quests` 
CHANGE `caption` `caption` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `descr` `descr` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `show_results` `show_results` ENUM('NEVER','ALWAYS','AFTER_VOTE','WHEN_CLOSED') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'ALWAYS', 
CHANGE `public_web_results` `public_web_results` ENUM('NEVER','ALWAYS','WHEN_CLOSED') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'NEVER', 
CHANGE `type` `type` ENUM('SORT','FILL') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'FILL', 
CHANGE `result_rights` `result_rights` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `vote_rights` `vote_rights` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `alien_vote_rights` `alien_vote_rights` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `texts` 
CHANGE `table_name` `table_name` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
CHANGE `table_column` `table_column` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'DESCR', 
CHANGE `lc` `lc` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
CHANGE `descr` `descr` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


ALTER TABLE `status` 
CHANGE `code` `code` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
CHANGE `caption` `caption` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `color` `color` VARCHAR(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `icon` `icon` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `captions` 
CHANGE `table_name` `table_name` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
CHANGE `table_column` `table_column` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'CAPTION', 
CHANGE `lc` `lc` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
CHANGE `caption` `caption` VARCHAR(250) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `pwd_reset` 
CHANGE `from_host` `from_host` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `reset_code` `reset_code` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `pwd_reset` DROP IF EXISTS `reset_url`;

ALTER TABLE `status_set` CHANGE `name` `name` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `ask_votes` CHANGE `text_value` `text_value` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `mail_log` 
CHANGE `mail_to` `mail_to` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `mail_from` `mail_from` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `subject` `subject` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `command` `command` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `events` 
CHANGE `descr` `descr` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `link` `link` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `place` `place` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `view_rights` `view_rights` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `plan_rights` `plan_rights` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `result_rights` `result_rights` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


ALTER TABLE `user` 
CHANGE `user_name` `user_name` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
CHANGE `password` `password` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `status` `status` VARCHAR(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `roles` `roles` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `first_name` `first_name` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `last_name` `last_name` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `call_name` `call_name` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `email_name` `email_name` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `street` `street` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `city` `city` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `zipcode` `zipcode` VARCHAR(12) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `phone` `phone` VARCHAR(25) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `phone2` `phone2` VARCHAR(35) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `account_number` `account_number` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `birth_code` `birth_code` VARCHAR(16) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `language` `language` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'CZ', 
CHANGE `sex` `sex` ENUM('male','female','unknown') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'unknown', 
CHANGE `password2` `password2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `skin` `skin` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


ALTER TABLE `ask_items` 
CHANGE `caption` `caption` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `item_type` `item_type` ENUM('NUMBER','TEXT','BOOLEAN') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'BOOLEAN';


ALTER TABLE `attendance_history` 
CHANGE `entry_type` `entry_type` CHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'UAE', 
CHANGE `pre_desc_from` `pre_desc_from` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `pre_desc_to` `pre_desc_to` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


ALTER TABLE `live` CHANGE `php_session` `php_session` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

ALTER TABLE `discussion` 
CHANGE `caption` `caption` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `descr` `descr` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `read_rights` `read_rights` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `write_rights` `write_rights` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `del_rights` `del_rights` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `sticky_rights` `sticky_rights` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


ALTER TABLE `discussion_post` 
CHANGE `user_name` `user_name` VARCHAR(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `item` `item` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `priority` `priority` VARCHAR(5) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


ALTER TABLE `event_types` 
CHANGE `code` `code` CHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
CHANGE `caption` `caption` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `color` `color` VARCHAR(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL, 
CHANGE `mandatory` `mandatory` ENUM('FREE','WARN','MUST') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'FREE';


ALTER TABLE `attendance` 
CHANGE `pre_desc` `pre_desc` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL, 
CHANGE `post_desc` `post_desc` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

ALTER TABLE `ask_items` CHARACTER SET utf8;
ALTER TABLE `ask_quests` CHARACTER SET utf8;
ALTER TABLE `ask_votes` CHARACTER SET utf8;
ALTER TABLE `attendance` CHARACTER SET utf8;
ALTER TABLE `attendance_history` CHARACTER SET utf8;
ALTER TABLE `captions` CHARACTER SET utf8;
ALTER TABLE `debt` CHARACTER SET utf8;
ALTER TABLE `discussion` CHARACTER SET utf8;
ALTER TABLE `discussion_post` CHARACTER SET utf8;
ALTER TABLE `discussion_post_reaction` CHARACTER SET utf8;
ALTER TABLE `discussion_read` CHARACTER SET utf8;
ALTER TABLE `ds_watcher` CHARACTER SET utf8;
ALTER TABLE `events` CHARACTER SET utf8;
ALTER TABLE `event_types` CHARACTER SET utf8;
ALTER TABLE `ical` CHARACTER SET utf8;
ALTER TABLE `ical_item` CHARACTER SET utf8;
ALTER TABLE `live` CHARACTER SET utf8;
ALTER TABLE `mail_log` CHARACTER SET utf8;
ALTER TABLE `migration` CHARACTER SET utf8;
ALTER TABLE `notes` CHARACTER SET utf8;
ALTER TABLE `notes_history` CHARACTER SET utf8;
ALTER TABLE `push_notification` CHARACTER SET utf8;
ALTER TABLE `pwd_reset` CHARACTER SET utf8;
ALTER TABLE `rights` CHARACTER SET utf8;
ALTER TABLE `status` CHARACTER SET utf8;
ALTER TABLE `status_set` CHARACTER SET utf8;
ALTER TABLE `texts` CHARACTER SET utf8;
ALTER TABLE `user` CHARACTER SET utf8;
ALTER TABLE `user_invitation` CHARACTER SET utf8;
ALTER TABLE `usr_mails` CHARACTER SET utf8;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-10-28T09-45-38, in file 2022-10-28T09-45-38.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
