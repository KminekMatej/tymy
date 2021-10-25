
-- SQL Migration file
-- Author: Matej Kminek <m@kminet.eu>
-- Created: 25.10.2021 10:32:50


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `ask_items` (
  `id` int(11) NOT NULL,
  `quest_id` int(11) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `item_type` enum('NUMBER','TEXT','BOOLEAN') DEFAULT 'BOOLEAN'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ask_quests` (
  `id` int(11) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `descr` text,
  `min_items` int(11) DEFAULT '-1',
  `max_items` int(11) DEFAULT '-1',
  `changeable_votes` enum('YES','NO') DEFAULT 'YES',
  `main_menu` enum('YES','NO') DEFAULT 'YES',
  `anonymous_results` enum('YES','NO') DEFAULT 'NO',
  `show_results` enum('NEVER','ALWAYS','AFTER_VOTE','WHEN_CLOSED') DEFAULT 'ALWAYS',
  `public_web_results` enum('NEVER','ALWAYS','WHEN_CLOSED') DEFAULT 'NEVER',
  `status` enum('DESIGN','OPENED','CLOSED','DELETED') DEFAULT 'DESIGN',
  `type` enum('SORT','FILL') DEFAULT 'FILL',
  `result_rights` varchar(20) DEFAULT NULL,
  `vote_rights` varchar(20) DEFAULT NULL,
  `alien_vote_rights` varchar(20) DEFAULT NULL,
  `dat_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usr_mod` int(11) DEFAULT NULL,
  `usr_cre` int(11) DEFAULT NULL,
  `dat_cre` datetime DEFAULT NULL,
  `order_flag` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ask_votes` (
  `quest_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `text_value` varchar(500) DEFAULT NULL,
  `numeric_value` double DEFAULT NULL,
  `boolean_value` enum('FALSE','TRUE') DEFAULT NULL,
  `usr_mod` int(11) DEFAULT NULL,
  `dat_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `attendance` (
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `pre_status` char(3) DEFAULT NULL,
  `pre_desc` varchar(255) DEFAULT NULL,
  `pre_usr_mod` int(11) DEFAULT NULL,
  `pre_dat_mod` datetime DEFAULT NULL,
  `post_status` char(3) DEFAULT NULL,
  `post_desc` varchar(255) DEFAULT NULL,
  `post_usr_mod` int(11) DEFAULT NULL,
  `post_dat_mod` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `attend_history` (
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `dat_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usr_mod` int(11) DEFAULT NULL,
  `entry_type` char(3) DEFAULT 'UAE',
  `pre_status_from` char(3) DEFAULT NULL,
  `pre_desc_from` varchar(255) DEFAULT NULL,
  `pre_status_to` char(3) DEFAULT NULL,
  `pre_desc_to` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `captions` (
  `table_name` varchar(20) NOT NULL,
  `id` int(11) NOT NULL,
  `table_column` varchar(20) NOT NULL DEFAULT 'CAPTION',
  `lc` varchar(3) NOT NULL,
  `caption` varchar(250) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `captions` (`table_name`, `id`, `table_column`, `lc`, `caption`) VALUES
('PAGES', 1, 'CAPTION', 'CZ', 'Hlavní strana'),
('PAGES', 1, 'CAPTION', 'EN', 'Main page'),
('PAGES', 2, 'CAPTION', 'CZ', 'Zadání docházky'),
('PAGES', 2, 'CAPTION', 'EN', 'Attendance insert'),
('PAGES', 3, 'CAPTION', 'CZ', 'Přehled docházky'),
('PAGES', 3, 'CAPTION', 'EN', 'Attendance view'),
('PAGES', 4, 'CAPTION', 'CZ', 'Detail události'),
('PAGES', 4, 'CAPTION', 'EN', 'Event detail'),
('PAGES', 5, 'CAPTION', 'CZ', 'Osobní údaje'),
('PAGES', 5, 'CAPTION', 'EN', 'Personal data'),
('PAGES', 6, 'CAPTION', 'CZ', 'Seznam členů'),
('PAGES', 6, 'CAPTION', 'EN', 'Member list'),
('PAGES', 7, 'CAPTION', 'CZ', 'Poslat mail'),
('PAGES', 7, 'CAPTION', 'EN', 'Send mail'),
('PAGES', 8, 'CAPTION', 'CZ', 'Správa událostí'),
('PAGES', 8, 'CAPTION', 'EN', 'Events management'),
('PAGES', 9, 'CAPTION', 'CZ', 'Soubory'),
('PAGES', 9, 'CAPTION', 'EN', 'Files'),
('PAGES', 10, 'CAPTION', 'CZ', 'Správa souborů'),
('PAGES', 10, 'CAPTION', 'EN', 'Files management'),
('PAGES', 11, 'CAPTION', 'CZ', 'Report docházky'),
('PAGES', 11, 'CAPTION', 'EN', 'Attendance report'),
('PAGES', 12, 'CAPTION', 'CZ', 'Diskuze'),
('PAGES', 12, 'CAPTION', 'EN', 'Discussion'),
('PAGES', 13, 'CAPTION', 'CZ', 'Nápověda'),
('PAGES', 13, 'CAPTION', 'EN', 'Help'),
('PAGES', 14, 'CAPTION', 'CZ', 'Správa uživatelů'),
('PAGES', 14, 'CAPTION', 'EN', 'Users management'),
('PAGES', 15, 'CAPTION', 'CZ', 'Nastavení týmu'),
('PAGES', 15, 'CAPTION', 'EN', 'Team setup'),
('PAGES', 16, 'CAPTION', 'CZ', 'Výber události...'),
('PAGES', 16, 'CAPTION', 'EN', 'Select event...'),
('PAGES', 17, 'CAPTION', 'CZ', 'Detail události'),
('PAGES', 17, 'CAPTION', 'EN', 'Event detail'),
('PAGES', 18, 'CAPTION', 'CZ', 'Docházka'),
('PAGES', 18, 'CAPTION', 'EN', 'Attendance'),
('PAGES', 19, 'CAPTION', 'CZ', 'Seznam členů'),
('PAGES', 19, 'CAPTION', 'EN', 'Team list'),
('PAGES', 20, 'CAPTION', 'CZ', 'Detail uživatele'),
('PAGES', 20, 'CAPTION', 'EN', 'User detail'),
('PAGES', 21, 'CAPTION', 'CZ', 'Nastavení'),
('PAGES', 21, 'CAPTION', 'EN', 'Settings'),
('PAGES', 22, 'CAPTION', 'CZ', 'Reporty'),
('PAGES', 22, 'CAPTION', 'EN', 'Reports'),
('PAGES', 24, 'CAPTION', 'CZ', 'Registrace'),
('PAGES', 24, 'CAPTION', 'EN', 'Register'),
('PAGES', 25, 'CAPTION', 'CZ', 'Oprávnění'),
('PAGES', 25, 'CAPTION', 'EN', 'Rights management'),
('PAGES', 27, 'CAPTION', 'CZ', 'Správa diskuzí'),
('PAGES', 27, 'CAPTION', 'EN', 'Discussions management'),
('PAGES', 28, 'CAPTION', 'CZ', 'Zapomenuté heslo'),
('PAGES', 28, 'CAPTION', 'EN', 'Password reset'),
('PAGES', 29, 'CAPTION', 'CZ', 'Správa typů událostí'),
('PAGES', 29, 'CAPTION', 'EN', 'Event types management'),
('PAGES', 30, 'CAPTION', 'CZ', 'Správa možností účasti'),
('PAGES', 30, 'CAPTION', 'EN', 'Attendance options management'),
('PAGES', 31, 'CAPTION', 'CZ', 'Správa reportů'),
('PAGES', 31, 'CAPTION', 'EN', 'Reports management'),
('PAGES', 34, 'CAPTION', 'CZ', 'Správa multi-účtu'),
('PAGES', 34, 'CAPTION', 'EN', 'Multi-account setup'),
('PAGES', 35, 'CAPTION', 'CZ', 'Historie docházky'),
('PAGES', 35, 'CAPTION', 'EN', 'Attendance history'),
('PAGES', 48, 'CAPTION', 'CZ', 'Souhlas se zpracováním osobních údajů'),
('PAGES', 48, 'CAPTION', 'EN', 'Private info declaration'),
('EVENT_TYPES', 1, 'CAPTION', 'CZ', 'Trénink'),
('EVENT_TYPES', 1, 'CAPTION', 'EN', 'Training'),
('EVENT_TYPES', 2, 'CAPTION', 'CZ', 'Běhání'),
('EVENT_TYPES', 2, 'CAPTION', 'EN', 'Running'),
('EVENT_TYPES', 3, 'CAPTION', 'CZ', 'Schůze'),
('EVENT_TYPES', 3, 'CAPTION', 'EN', 'Meeting'),
('EVENT_TYPES', 4, 'CAPTION', 'CZ', 'Turnaj'),
('EVENT_TYPES', 4, 'CAPTION', 'EN', 'Tournament'),
('EVENT_TYPES', 5, 'CAPTION', 'CZ', 'Soustředění'),
('EVENT_TYPES', 5, 'CAPTION', 'EN', 'Camp'),
('STATUSES', 1, 'CAPTION', 'CZ', 'Přijdu'),
('STATUSES', 1, 'CAPTION', 'EN', "I\'ll come"),
('STATUSES', 2, 'CAPTION', 'CZ', 'Omlouvám se'),
('STATUSES', 2, 'CAPTION', 'EN', "I\'m sorry"),
('STATUSES', 3, 'CAPTION', 'CZ', 'Přijdu později'),
('STATUSES', 3, 'CAPTION', 'EN', "I\'ll be late"),
('STATUSES', 4, 'CAPTION', 'CZ', 'Chci'),
('STATUSES', 4, 'CAPTION', 'EN', "I\'ll go"),
('STATUSES', 5, 'CAPTION', 'CZ', 'Nechci'),
('STATUSES', 5, 'CAPTION', 'EN', "I won\'t go"),
('STATUSES', 6, 'CAPTION', 'CZ', 'Nevím'),
('STATUSES', 6, 'CAPTION', 'EN', "I\'m not sure"),
('STATUSES', 7, 'CAPTION', 'CZ', 'Účast'),
('STATUSES', 7, 'CAPTION', 'EN', 'Was there'),
('STATUSES', 8, 'CAPTION', 'CZ', 'Neúčast'),
('STATUSES', 8, 'CAPTION', 'EN', 'Was not there'),
('STATUSES', 9, 'CAPTION', 'CZ', 'Pozdní příchod'),
('STATUSES', 9, 'CAPTION', 'EN', 'Was late'),
('STATUSES', 10, 'CAPTION', 'CZ', 'Účast'),
('STATUSES', 10, 'CAPTION', 'EN', 'Was there'),
('STATUSES', 11, 'CAPTION', 'CZ', 'Neúčast'),
('STATUSES', 11, 'CAPTION', 'EN', 'Was not there'),
('DISCUSSIONS', 1, 'CAPTION', 'CZ', 'Týmová diskuze'),
('DISCUSSIONS', 1, 'CAPTION', 'EN', 'Team discussion'),
('RIGHTS', 1, 'CAPTION', 'CZ', 'Vložit událost'),
('RIGHTS', 1, 'CAPTION', 'EN', 'Create event'),
('RIGHTS', 2, 'CAPTION', 'CZ', 'Editovat událost'),
('RIGHTS', 2, 'CAPTION', 'EN', 'Modify event'),
('RIGHTS', 3, 'CAPTION', 'CZ', 'Smazat událost'),
('RIGHTS', 3, 'CAPTION', 'EN', 'Delete event'),
('RIGHTS', 4, 'CAPTION', 'CZ', 'Vložit plány jiného člena'),
('RIGHTS', 4, 'CAPTION', 'EN', 'Update attendance plans of other players'),
('RIGHTS', 5, 'CAPTION', 'CZ', 'Zadat skutečnou docházku'),
('RIGHTS', 5, 'CAPTION', 'EN', 'Enter real attendance'),
('RIGHTS', 6, 'CAPTION', 'CZ', 'Založit nového uživatele'),
('RIGHTS', 6, 'CAPTION', 'EN', 'Insert new user'),
('RIGHTS', 7, 'CAPTION', 'CZ', 'Editovat údaje uživatele'),
('RIGHTS', 7, 'CAPTION', 'EN', 'Edit user'),
('RIGHTS', 8, 'CAPTION', 'CZ', 'Fyzicky smazat uživatele'),
('RIGHTS', 8, 'CAPTION', 'EN', 'Physical user delete'),
('RIGHTS', 9, 'CAPTION', 'CZ', 'Měnit týmové nastavení'),
('RIGHTS', 9, 'CAPTION', 'EN', 'Modify team settings'),
('RIGHTS', 45, 'CAPTION', 'CZ', 'Spravovat diskuze'),
('RIGHTS', 45, 'CAPTION', 'EN', 'Discussions management'),
('REPORTS', 1, 'CAPTION', 'CZ', 'Docházka'),
('REPORTS', 1, 'CAPTION', 'EN', 'Attendance'),
('REP_COLUMNS', 1, 'CAPTION', 'CZ', 'Účast'),
('REP_COLUMNS', 1, 'CAPTION', 'EN', 'Presence'),
('PAGES', 26, 'CAPTION', 'CZ', 'Hlasování'),
('PAGES', 26, 'CAPTION', 'EN', 'Votes'),
('REP_SETUP', 10, 'CAPTION', 'CZ', 'Spravovat reporty'),
('REP_SETUP', 10, 'CAPTION', 'EN', 'Manage reports'),
('PAGES', 23, 'CAPTION', 'CZ', 'Spravovat reporty'),
('PAGES', 23, 'CAPTION', 'EN', 'Public web news'),
('RIGHTS', 12, 'CAPTION', 'CZ', 'Modifikovat ankety'),
('RIGHTS', 12, 'CAPTION', 'EN', 'Modify polls'),
('RIGHTS', 13, 'CAPTION', 'CZ', 'Mazat ankety'),
('RIGHTS', 13, 'CAPTION', 'EN', 'Delete polls'),
('RIGHTS', 14, 'CAPTION', 'CZ', 'Vytvářet ankety'),
('RIGHTS', 14, 'CAPTION', 'EN', 'Create polls'),
('RIGHTS', 15, 'CAPTION', 'CZ', 'Smazat hlasování uživatelů'),
('RIGHTS', 15, 'CAPTION', 'EN', 'Delete user votes'),
('RIGHTS', 16, 'CAPTION', 'CZ', 'Nahrávat soubory ke stažení'),
('RIGHTS', 16, 'CAPTION', 'EN', 'Upload files'),
('RIGHTS', 17, 'CAPTION', 'CZ', 'Měnit soubory ke stažení'),
('RIGHTS', 17, 'CAPTION', 'EN', 'Modify uploaded files'),
('RIGHTS', 18, 'CAPTION', 'CZ', 'Mazat soubory ke stažení'),
('RIGHTS', 18, 'CAPTION', 'EN', 'Delete uploaded files');

CREATE TABLE `debt` (
  `id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_user_id` int(11) NOT NULL,
  `amount` float NOT NULL,
  `currency_iso` varchar(3) NOT NULL DEFAULT 'CZK',
  `country_iso` varchar(3) NOT NULL DEFAULT 'CZ',
  `debtor_id` int(11) DEFAULT NULL,
  `debtor_type` enum('user','team','other') NOT NULL DEFAULT 'user',
  `payee_id` int(11) DEFAULT NULL,
  `payee_type` enum('user','team','other') NOT NULL DEFAULT 'user',
  `payee_account_number` varchar(32) DEFAULT NULL,
  `varcode` int(11) DEFAULT NULL,
  `debt_date` timestamp NULL DEFAULT NULL,
  `caption` varchar(255) NOT NULL,
  `payment_sent` timestamp NULL DEFAULT NULL,
  `payment_received` timestamp NULL DEFAULT NULL,
  `note` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `discussions` (
  `id` int(11) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `descr` text,
  `read_rights` varchar(20) DEFAULT NULL,
  `write_rights` varchar(20) DEFAULT NULL,
  `del_rights` varchar(20) DEFAULT NULL,
  `dat_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usr_mod` int(11) DEFAULT NULL,
  `public_read` enum('YES','NO') DEFAULT 'NO',
  `status` enum('DELETED','ACTIVE') DEFAULT 'ACTIVE',
  `can_modify` enum('YES','NO') DEFAULT 'YES',
  `usr_cre` int(11) DEFAULT NULL,
  `dat_cre` datetime DEFAULT NULL,
  `order_flag` int(11) DEFAULT NULL,
  `sticky_rights` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `discussions` (`id`, `caption`, `descr`, `read_rights`, `write_rights`, `del_rights`, `dat_mod`, `usr_mod`, `public_read`, `status`, `can_modify`, `usr_cre`, `dat_cre`, `order_flag`, `sticky_rights`) VALUES
(1, 'Default discussion', 'All members can write here about team matters.', NULL, NULL, NULL, '2021-10-25 08:12:41', NULL, 'NO', 'ACTIVE', 'YES', 1, '2021-10-25 10:12:41', NULL, NULL);

CREATE TABLE `download` (
  `id` int(11) NOT NULL,
  `caption` varchar(30) DEFAULT NULL,
  `file_name` varchar(30) DEFAULT NULL,
  `file_path` varchar(50) DEFAULT NULL,
  `content_type` varchar(100) DEFAULT NULL,
  `descr` text,
  `protection` enum('PUBLIC','LOGIN','PASSWORD') DEFAULT NULL,
  `password` varchar(30) DEFAULT NULL,
  `download_count` int(11) DEFAULT '0',
  `menu_code` varchar(10) DEFAULT NULL,
  `author` int(11) DEFAULT NULL,
  `insert_date` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `download_log` (
  `file_id` int(11) NOT NULL,
  `dl_time` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `remote_host` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ds_items` (
  `id` int(11) NOT NULL,
  `ds_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item` text,
  `priority` varchar(5) DEFAULT NULL,
  `insert_date` datetime DEFAULT NULL,
  `usr_mod` int(11) DEFAULT NULL,
  `dat_mod` datetime DEFAULT NULL,
  `sticky` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `ds_read` (
  `user_id` int(11) NOT NULL,
  `ds_id` int(11) NOT NULL,
  `items_read` int(11) DEFAULT '0',
  `last_id` int(11) DEFAULT NULL,
  `last_date` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `ds_watcher` (
  `ds_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `enabled` enum('YES','NO') DEFAULT NULL,
  `user_email` enum('YES','NO') DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `item_limit` int(11) DEFAULT '10'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `type` varchar(3) DEFAULT NULL,
  `descr` text,
  `close_time` datetime DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `place` varchar(255) DEFAULT NULL,
  `attended_by` int(11) DEFAULT NULL,
  `attended_at` datetime DEFAULT NULL,
  `closed_by` int(11) DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `view_rights` varchar(20) DEFAULT NULL,
  `plan_rights` varchar(20) DEFAULT NULL,
  `result_rights` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `event_types` (
  `id` int(11) NOT NULL,
  `code` varchar(3) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `pre_status_set` int(11) DEFAULT NULL,
  `post_status_set` int(11) DEFAULT NULL,
  `mandatory` enum('FREE','WARN','MUST') NOT NULL DEFAULT 'FREE',
  `dat_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usr_mod` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `event_types` (`id`, `code`, `caption`, `pre_status_set`, `post_status_set`, `mandatory`, `dat_mod`, `usr_mod`) VALUES
(1, 'TRA', 'Training', 1, 3, 'FREE', '2021-10-25 08:12:41', 0),
(2, 'RUN', 'Running', 1, 3, 'FREE', '2021-10-25 08:12:41', 0),
(3, 'MEE', 'Meeting', 1, 3, 'WARN', '2021-10-25 08:12:41', 0),
(4, 'TOU', 'Tournament', 2, 4, 'WARN', '2021-10-25 08:12:41', 0),
(5, 'CMP', 'Camp', 2, 4, 'WARN', '2021-10-25 08:12:41', 0);

CREATE TABLE `export` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `hash` varchar(32) DEFAULT NULL,
  `allowed` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `export_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `export_id` int(11) NOT NULL,
  `event_type` varchar(10) DEFAULT NULL,
  `pre_status` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `live` (
  `user_id` int(11) DEFAULT NULL,
  `page_id` int(11) DEFAULT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `php_session` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `mail_log` (
  `mail_to` varchar(100) DEFAULT NULL,
  `mail_from` varchar(100) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `command` varchar(100) DEFAULT NULL,
  `received` datetime DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `migration` (
  `id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `migration_from` varchar(16) NOT NULL,
  `migration` varchar(16) NOT NULL,
  `time` double NOT NULL,
  `result` enum('OK','ERROR') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `caption` varchar(150) DEFAULT NULL,
  `descr` text,
  `source` text,
  `access` enum('PRIVATE','PUBLIC','USERS') DEFAULT NULL,
  `in_menu` enum('APP','PUBLIC','BOTH','NO') DEFAULT NULL,
  `special_page` varchar(20) DEFAULT NULL,
  `menu_order` int(11) DEFAULT NULL,
  `read_rights` varchar(20) DEFAULT NULL,
  `write_rights` varchar(20) DEFAULT NULL,
  `update_counter` int(11) DEFAULT '0',
  `read_counter` int(11) DEFAULT '0',
  `version` int(11) DEFAULT '1',
  `usr_cre` int(11) DEFAULT NULL,
  `dat_cre` datetime DEFAULT NULL,
  `usr_mod` int(11) DEFAULT NULL,
  `dat_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `notes_history` (
  `id` int(11) NOT NULL,
  `caption` varchar(150) DEFAULT NULL,
  `descr` text,
  `source` text,
  `access` enum('PRIVATE','PUBLIC','USERS') DEFAULT NULL,
  `in_menu` enum('APP','PUBLIC','BOTH','NO') DEFAULT NULL,
  `special_page` varchar(20) DEFAULT NULL,
  `menu_order` int(11) DEFAULT NULL,
  `read_rights` varchar(20) DEFAULT NULL,
  `write_rights` varchar(20) DEFAULT NULL,
  `update_counter` int(11) DEFAULT '0',
  `read_counter` int(11) DEFAULT '0',
  `version` int(11) NOT NULL,
  `usr_cre` int(11) DEFAULT NULL,
  `dat_cre` datetime DEFAULT NULL,
  `usr_mod` int(11) DEFAULT NULL,
  `dat_mod` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `pages_log` (
  `id` bigint(20) NOT NULL,
  `page_id` int(11) NOT NULL,
  `access_time` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `remote_host` varchar(100) DEFAULT NULL,
  `accessed` enum('YES','NO','GH') DEFAULT 'YES'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `pwd_reset` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `requested` datetime DEFAULT NULL,
  `reseted` datetime DEFAULT NULL,
  `from_host` varchar(50) DEFAULT NULL,
  `reset_code` varchar(20) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `descr` text,
  `days_back` int(11) DEFAULT NULL,
  `months_back` int(11) DEFAULT NULL,
  `events_back` int(11) DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `menu` enum('YES','NO','FAST') DEFAULT NULL,
  `public` enum('YES','NO') DEFAULT NULL,
  `right_name` varchar(20) DEFAULT NULL,
  `web_code` varchar(20) DEFAULT NULL,
  `show_uncomplete` tinyint(1) DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `reports` (`id`, `caption`, `descr`, `days_back`, `months_back`, `events_back`, `event_type`, `menu`, `public`, `right_name`, `web_code`, `show_uncomplete`) VALUES
(1, 'Attendance', 'Results of attendance per given period of time.', NULL, NULL, NULL, '^TRA$|^RUN$', 'YES', 'NO', NULL, NULL, 0);

CREATE TABLE `rep_columns` (
  `id` int(11) NOT NULL,
  `rep_id` int(11) NOT NULL,
  `col_type` set('ABS','REL') DEFAULT NULL,
  `col_order` int(11) DEFAULT NULL,
  `pre_mask` varchar(50) NOT NULL,
  `post_mask` varchar(50) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `dat_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usr_mod` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `rep_columns` (`id`, `rep_id`, `col_type`, `col_order`, `pre_mask`, `post_mask`, `caption`, `dat_mod`, `usr_mod`) VALUES
(1, 1, 'ABS,REL', -1, '.*', '^YES$|^LAT$', 'Účast', '2021-10-25 08:12:41', NULL);

CREATE TABLE `rights` (
  `id` int(11) NOT NULL,
  `right_type` enum('SYS','PAGE','USR') NOT NULL DEFAULT 'USR',
  `name` varchar(20) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `a_roles` varchar(100) DEFAULT NULL,
  `r_roles` varchar(100) DEFAULT NULL,
  `a_statuses` varchar(100) DEFAULT NULL,
  `r_statuses` varchar(100) DEFAULT NULL,
  `a_users` varchar(1000) DEFAULT NULL,
  `r_users` varchar(1000) DEFAULT NULL,
  `dat_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usr_mod` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `rights` (`id`, `right_type`, `name`, `caption`, `a_roles`, `r_roles`, `a_statuses`, `r_statuses`, `a_users`, `r_users`, `dat_mod`, `usr_mod`) VALUES
(1, 'SYS', 'EVE_CREATE', 'Create event', 'ATT', NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(2, 'SYS', 'EVE_UPDATE', 'Modify event', 'ATT', NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(3, 'SYS', 'EVE_DELETE', 'Delete event', 'ATT', NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(4, 'SYS', 'ATT_UPDATE', 'Update attendance plans of other players', 'ATT', NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(5, 'SYS', 'EVE_ATT_UPDATE', 'Enter real attendance', 'ATT', NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(6, 'SYS', 'USR_CREATE', 'Insert new user', 'USR', NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(7, 'SYS', 'USR_UPDATE', 'Edit user', 'USR', NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(8, 'SYS', 'USR_HDEL', 'Physical user delete', 'SUPER', NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(9, 'SYS', 'TEAM_UPDATE', 'Modify team settings', 'SUPER', NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(10, 'SYS', 'REP_SETUP', 'Spravovat reporty', 'SUPER', NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(11, 'SYS', 'DSSETUP', 'Discussions management', 'SUPER', NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(12, 'SYS', 'ASK.VOTE_UPDATE', 'Modify vote definitions', NULL, NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(13, 'SYS', 'ASK.VOTE_DELETE', 'Delete votes', NULL, NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(14, 'SYS', 'ASK.VOTE_CREATE', 'Create new votes', NULL, NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(15, 'SYS', 'ASK.VOTE_RESET', 'Reset users votes', NULL, NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(16, 'SYS', 'FILE_CREATE', 'Upload files', NULL, NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(17, 'SYS', 'FILE_UPDATE', 'Modify uploaded files', NULL, NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1),
(18, 'SYS', 'FILE_DELETE', 'Delete uploaded files', NULL, NULL, NULL, NULL, NULL, NULL, '2021-10-25 08:12:41', 1);

CREATE TABLE `rights_cache` (
  `user_id` int(11) NOT NULL DEFAULT '0',
  `right_type` varchar(5) NOT NULL DEFAULT 'USR',
  `right_name` varchar(20) NOT NULL DEFAULT '',
  `allowed` enum('YES','NO') NOT NULL DEFAULT 'YES'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `module` varchar(30) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `int_value` int(11) DEFAULT NULL,
  `varchar_value` varchar(100) DEFAULT NULL,
  `text_value` text,
  `double_value` double DEFAULT NULL,
  `dat_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usr_mod` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `statuses` (
  `id` int(11) NOT NULL,
  `set_id` int(11) NOT NULL,
  `code` varchar(3) NOT NULL,
  `caption` varchar(50) DEFAULT NULL,
  `dat_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `usr_mod` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `statuses` (`id`, `set_id`, `code`, `caption`, `dat_mod`, `usr_mod`) VALUES
(1, 1, 'YES', "I\'ll come", '2021-10-25 08:12:41', NULL),
(2, 1, 'NO', "I\'m sorry", '2021-10-25 08:12:41', NULL),
(3, 1, 'LAT', "I\'ll be late", '2021-10-25 08:12:41', NULL),
(4, 2, 'YES', "I\'ll go", '2021-10-25 08:12:41', NULL),
(5, 2, 'NO', "I won\'t go", '2021-10-25 08:12:41', NULL),
(6, 2, 'DKY', "I\'m not sure", '2021-10-25 08:12:41', NULL),
(7, 3, 'YES', 'Was there', '2021-10-25 08:12:41', NULL),
(8, 3, 'NO', 'Was not there', '2021-10-25 08:12:41', NULL),
(9, 3, 'LAT', 'Was late', '2021-10-25 08:12:41', NULL),
(10, 4, 'YES', 'Was there', '2021-10-25 08:12:41', NULL),
(11, 4, 'NO', 'Was not there', '2021-10-25 08:12:41', NULL);

CREATE TABLE `status_sets` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `status_sets` (`id`, `name`) VALUES
(1, 'Training'),
(2, 'Tournament'),
(3, 'Result with late'),
(4, 'Result without late');

CREATE TABLE `texts` (
  `table_name` varchar(20) NOT NULL,
  `id` int(11) NOT NULL,
  `table_column` varchar(20) NOT NULL DEFAULT 'DESCR',
  `lc` varchar(3) NOT NULL,
  `descr` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `texts` (`table_name`, `id`, `table_column`, `lc`, `descr`) VALUES
('DISCUSSIONS', 1, 'DESCR', 'EN', 'All members can write here about team matters.'),
('DISCUSSIONS', 1, 'DESCR', 'CZ', 'Zde mohou všichni členové debatovat o týmových záležitostech.'),
('REPORTS', 1, 'DESCR', 'EN', 'Results of attendance per given period of time.'),
('REPORTS', 1, 'DESCR', 'CZ', 'Výsledky docházky za určité časové období.');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_name` varchar(20) NOT NULL,
  `password` varchar(40) DEFAULT NULL,
  `can_login` enum('YES','NO') DEFAULT 'NO',
  `last_login` datetime DEFAULT NULL,
  `status` varchar(15) DEFAULT NULL,
  `roles` varchar(40) DEFAULT NULL,
  `first_name` varchar(20) DEFAULT NULL,
  `last_name` varchar(20) DEFAULT NULL,
  `call_name` varchar(30) DEFAULT NULL,
  `editable_call_name` enum('YES','NO') DEFAULT 'NO',
  `email_name` varchar(20) DEFAULT NULL,
  `street` varchar(40) DEFAULT NULL,
  `city` varchar(40) DEFAULT NULL,
  `zipcode` varchar(12) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `phone2` varchar(35) DEFAULT NULL,
  `icq` varchar(25) DEFAULT NULL,
  `account_number` varchar(32) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `birth_code` varchar(16) DEFAULT NULL,
  `nameday_month` int(11) DEFAULT NULL,
  `nameday_day` int(11) DEFAULT NULL,
  `language` varchar(3) DEFAULT 'CZ',
  `sex` enum('male','female','unknown') NOT NULL DEFAULT 'unknown',
  `jersey_number` varchar(255) NOT NULL DEFAULT '',
  `password2` varchar(255) DEFAULT NULL,
  `gdpr_accepted_at` datetime DEFAULT NULL,
  `gdpr_revoked_at` datetime DEFAULT NULL,
  `last_read_news` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `usr_mails` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `email` varchar(50) DEFAULT NULL,
  `type` enum('DEF','SMS','WEB','USR') DEFAULT 'USR'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `v_users` (
`id` int(11)
,`created_at` datetime
,`user_name` varchar(20)
,`password` varchar(40)
,`can_login` enum('YES','NO')
,`last_login` datetime
,`status` varchar(15)
,`roles` varchar(40)
,`first_name` varchar(20)
,`last_name` varchar(20)
,`call_name` varchar(30)
,`editable_call_name` enum('YES','NO')
,`email_name` varchar(20)
,`street` varchar(40)
,`city` varchar(40)
,`zipcode` varchar(12)
,`phone` varchar(25)
,`phone2` varchar(35)
,`icq` varchar(25)
,`account_number` varchar(32)
,`birth_date` date
,`birth_code` varchar(16)
,`nameday_month` int(11)
,`nameday_day` int(11)
,`language` varchar(3)
,`sex` enum('male','female','unknown')
,`jersey_number` varchar(255)
,`password2` varchar(255)
,`gdpr_accepted_at` datetime
,`gdpr_revoked_at` datetime
,`last_read_news` timestamp
,`email` varchar(50)
);
DROP TABLE IF EXISTS `v_users`;

CREATE VIEW `v_users`  AS 
SELECT `u`.`id` AS `id`, `u`.`created_at` AS `created_at`, `u`.`user_name` AS `user_name`, `u`.`password` AS `password`, `u`.`can_login` AS `can_login`, `u`.`last_login` AS `last_login`, `u`.`status` AS `status`, `u`.`roles` AS `roles`, `u`.`first_name` AS `first_name`, `u`.`last_name` AS `last_name`, `u`.`call_name` AS `call_name`, `u`.`editable_call_name` AS `editable_call_name`, `u`.`email_name` AS `email_name`, `u`.`street` AS `street`, `u`.`city` AS `city`, `u`.`zipcode` AS `zipcode`, `u`.`phone` AS `phone`, `u`.`phone2` AS `phone2`, `u`.`icq` AS `icq`, `u`.`account_number` AS `account_number`, `u`.`birth_date` AS `birth_date`, `u`.`birth_code` AS `birth_code`, `u`.`nameday_month` AS `nameday_month`, `u`.`nameday_day` AS `nameday_day`, `u`.`language` AS `language`, `u`.`sex` AS `sex`, `u`.`jersey_number` AS `jersey_number`, `u`.`password2` AS `password2`, `u`.`gdpr_accepted_at` AS `gdpr_accepted_at`, `u`.`gdpr_revoked_at` AS `gdpr_revoked_at`, `u`.`last_read_news` AS `last_read_news`, `e`.`email` AS `email` FROM (`users` `u` left join `usr_mails` `e` on(((`e`.`user_id` = `u`.`id`) and (`e`.`type` = 'DEF')))) ;

ALTER TABLE `ask_items`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ask_quests`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ask_votes`
  ADD PRIMARY KEY (`quest_id`,`user_id`,`item_id`);

ALTER TABLE `attendance`
  ADD PRIMARY KEY (`user_id`,`event_id`);

ALTER TABLE `attend_history`
  ADD KEY `idx_dat_mod` (`dat_mod`),
  ADD KEY `idx_event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `captions`
  ADD PRIMARY KEY (`table_name`,`id`,`table_column`,`lc`);

ALTER TABLE `debt`
  ADD PRIMARY KEY (`id`),
  ADD KEY `debtor_id` (`debtor_id`),
  ADD KEY `payee_id` (`payee_id`),
  ADD KEY `created_user_id` (`created_user_id`);

ALTER TABLE `discussions`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `download`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ds_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ds_id_insert_date` (`ds_id`,`insert_date`),
  ADD KEY `ds_items_created` (`user_id`);
ALTER TABLE `ds_items` ADD FULLTEXT KEY `idx_item` (`item`);

ALTER TABLE `ds_read`
  ADD PRIMARY KEY (`user_id`,`ds_id`);

ALTER TABLE `ds_watcher`
  ADD PRIMARY KEY (`ds_id`,`user_id`);

ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_start_time` (`start_time`);

ALTER TABLE `event_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_code` (`code`);

ALTER TABLE `export`
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `export_user_id` (`user_id`);

ALTER TABLE `export_settings`
  ADD UNIQUE KEY `id` (`id`),
  ADD UNIQUE KEY `export_settings_unique` (`export_id`,`event_type`,`pre_status`);

ALTER TABLE `migration`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `notes_history`
  ADD PRIMARY KEY (`id`,`version`);

ALTER TABLE `pages_log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `pwd_reset`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id_requested` (`user_id`,`requested`);

ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rep_columns`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rights`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `right_key` (`right_type`,`name`);

ALTER TABLE `rights_cache`
  ADD PRIMARY KEY (`right_type`,`right_name`,`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_module_name` (`module`,`name`,`user_id`);

ALTER TABLE `statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `statuses_key` (`set_id`,`code`);

ALTER TABLE `status_sets`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `texts`
  ADD PRIMARY KEY (`table_name`,`id`,`table_column`,`lc`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_name` (`user_name`);

ALTER TABLE `usr_mails`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`user_id`,`email`);


ALTER TABLE `ask_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ask_quests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `debt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `discussions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `download`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ds_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `event_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `export`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `export_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `migration`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pages_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pwd_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `rep_columns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `rights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `statuses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

ALTER TABLE `status_sets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `usr_mails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `attend_history`
  ADD CONSTRAINT `attend_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `attend_history_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `ds_items`
  ADD CONSTRAINT `ds_items_created` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2021-10-25T10-32-51, in file 2021-10-25T10-32-51.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
