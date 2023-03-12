
/**
 * This script fills created empty database with testing data - in order to have exact IDs setup
 *
 * Author:  Matěj Kmínek
 * Created: 25. 9. 2020
 */

/** PURGE SECTION */
SET FOREIGN_KEY_CHECKS=0;

DELETE FROM `status` WHERE `id`>11;
ALTER TABLE `status` auto_increment = 12; #keep factory deault statuses there

TRUNCATE `ask_items`;
TRUNCATE `ask_votes`;
TRUNCATE `ask_quests`;
TRUNCATE `attendance`;
TRUNCATE `attendance_history`;
TRUNCATE `debt`;
TRUNCATE `discussion`;
TRUNCATE `discussion_post`;
TRUNCATE `discussion_post_reaction`;
TRUNCATE `discussion_read`;
TRUNCATE `ds_watcher`;
TRUNCATE `events`;
TRUNCATE `event_types`;
TRUNCATE `live`;
TRUNCATE `mail_log`;
TRUNCATE `notes`;
TRUNCATE `notes_history`;
TRUNCATE `push_notification`;
TRUNCATE `pwd_reset`;
TRUNCATE `rights`;
TRUNCATE `usr_mails`;
TRUNCATE `user`;

SET FOREIGN_KEY_CHECKS=1;

/** IMPORT SECTION */

INSERT INTO `user` (`user_name`, `password`, `can_login`, `status`, `roles`, `first_name`, `last_name`, `call_name`, `editable_call_name`, `email_name`,`language`, `sex`, `gdpr_accepted_at`, `last_read_news`) VALUES
('autotest_admin',  'f4ad5b4e691802fca51711dede771a36', 1, 'PLAYER', 'SUPER,USR,ATT', 'Autotest', 'admin', 'autotest-admin', 0, 'autotest-admin', 'CZ', 'MALE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('autotest_user',   '58d26e9a3381ace5e682dc26bf780dd4', 1, 'PLAYER', '', 'Autotest', 'user', 'autotest-user', 0, 'autotest-user', 'CZ', 'MALE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('autotest_member',  '58d26e9a3381ace5e682dc26bf780dd4', 1, 'MEMBER', '', 'Autotest', 'member', 'autotest-member', 0, 'autotest-member', 'CZ', 'MALE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('autotest_init',  '58d26e9a3381ace5e682dc26bf780dd4', 1, 'INIT', '', 'Autotest', 'init', 'autotest-init', 0, 'autotest-init', 'CZ', 'MALE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('autotest_sick',  '58d26e9a3381ace5e682dc26bf780dd4', 1, 'SICK', '', 'Autotest', 'sick', 'autotest-sick', 0, 'autotest-sick', 'CZ', 'MALE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO `usr_mails`(`user_id`, `email`, `type`) VALUES 
(1,'admin@autotest.tymy.cz','DEF'),
(2,'user@autotest.tymy.cz','DEF'),
(3,'member@autotest.tymy.cz','DEF'),
(4,'init@autotest.tymy.cz','DEF'),
(5,'sick@autotest.tymy.cz','DEF');

INSERT INTO `rights` (`id`, `right_type`, `name`, `caption`, `a_roles`, `r_roles`, `a_statuses`, `r_statuses`, `a_users`, `r_users`, `updated`, `updated_user_id`) VALUES
(1, 'SYS', 'EVE_CREATE', 'Create event', 'ATT', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'SYS', 'EVE_UPDATE', 'Modify event', 'ATT', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'SYS', 'EVE_DELETE', 'Delete event', 'ATT', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'SYS', 'ATT_UPDATE', 'Update attendance plans of other players', 'ATT', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'SYS', 'EVE_ATT_UPDATE', 'Enter real attendance', 'ATT', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'SYS', 'USR_CREATE', 'Insert new user', 'USR', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 'SYS', 'USR_UPDATE', 'Edit user', 'USR', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'SYS', 'USR_HDEL', 'Physical user delete', 'SUPER', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'SYS', 'TEAM_UPDATE', 'Modify team settings', 'SUPER', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 'SYS', 'REP_SETUP', 'Spravovat reporty', 'SUPER', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'SYS', 'DSSETUP', 'Discussions management', 'SUPER', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 'SYS', 'ASK.VOTE_UPDATE', 'Modify vote definitions', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'SYS', 'ASK.VOTE_DELETE', 'Delete votes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 'SYS', 'ASK.VOTE_CREATE', 'Create new votes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'SYS', 'ASK.VOTE_RESET', 'Reset users votes', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'SYS', 'FILE_CREATE', 'Upload files', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'SYS', 'FILE_UPDATE', 'Modify uploaded files', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'SYS', 'FILE_DELETE', 'Delete uploaded files', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'SYS', 'DEBTS_TEAM', 'Správa týmového dlužníčku', 'SUPER', NULL, NULL, NULL, 3, NULL, NULL, NULL),
(20, 'USR', 'ADMINONLY', 'SUPER roles only', 'SUPER', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, NULL),
(21, 'USR', 'ADMINMEMBER', 'ADMINs and user id 3 - member', 'SUPER', NULL, NULL, NULL, 3, NULL, CURRENT_TIMESTAMP, NULL);

INSERT INTO `event_types` (`id`, `code`, `caption`, `pre_status_set_id`, `post_status_set_id`, `mandatory`, `updated`, `updated_user_id`) VALUES
(1, 'TRA', 'Trénink', 1, 3, 'FREE', NULL, NULL),
(2, 'RUN', 'Běhání', 1, 3, 'FREE', NULL, NULL),
(3, 'MEE', 'Schůze', 1, 3, 'WARN', NULL, NULL),
(4, 'TOU', 'Turnaj', 2, 4, 'WARN', NULL, NULL),
(5, 'CMP', 'Soustředění', 2, 4, 'WARN', NULL, NULL);

DELETE FROM `tymy_cz`.`teams` WHERE `sys_name` = 'autotest';
INSERT INTO `tymy_cz`.`teams` (`name`, `sys_name`, `db_name`, `languages`, `default_lc`, `sport`, `account_number`, `web`, `country_id`, `attend_email`, `excuse_email`, `modules`, `max_users`, `max_events_month`, `advertisement`, `active`, `retranslate`, `insert_date`, `time_zone`, `dst_flag`, `app_version`, `use_namedays`, `att_check`, `att_check_days`, `create_step`, `activation_key`, `host`, `tariff`, `tariff_until`, `tariff_payment`, `skin`, `required_fields`) VALUES 
('Autotest Team', 'autotest', NULL, 'CZ,EN', 'CZ', 'Autotest ultimate', NULL, NULL, '0', NULL, NULL, 'WEB,DS_WATCHER,DWNLD,ASK', '500', '100', 'YES', 'YES', 'NO', CURRENT_TIMESTAMP, '1', 'AUTO', '0.2', 'YES', 'FW', '7', '0', NULL, 'localhost', 'FULL', '2022-11-13', 'YEARLY', NULL, 'gender,firstName,lastName,phone,email,birthDate,callName,status,jerseyNumber,street,city,zipCode');
