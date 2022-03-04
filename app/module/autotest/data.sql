
/**
 * This script fills created empty database with testing data - in order to have exact IDs setup
 *
 * Author:  Matěj Kmínek
 * Created: 25. 9. 2020
 */

/** PURGE SECTION */
SET FOREIGN_KEY_CHECKS=0;
DELETE FROM `users` WHERE `id`>1;
ALTER TABLE `users` auto_increment = 2; #keep just admin there
DELETE FROM `usr_mails` WHERE `id`>1;
ALTER TABLE `usr_mails` auto_increment = 2; #keep just admin there

DELETE FROM `statuses` WHERE `id`>11;
ALTER TABLE `statuses` auto_increment = 12; #keep factory deault statuses there

TRUNCATE `ask_items`;
TRUNCATE `ask_quests`;
TRUNCATE `ask_votes`;
TRUNCATE `attendance`;
TRUNCATE `attendance_history`;
TRUNCATE `debt`;
TRUNCATE `discussions`;
TRUNCATE `download`;
TRUNCATE `download_log`;
TRUNCATE `ds_items`;
TRUNCATE `ds_read`;
TRUNCATE `ds_watcher`;
TRUNCATE `events`;
TRUNCATE `event_types`;
TRUNCATE `export`;
TRUNCATE `export_settings`;
TRUNCATE `live`;
TRUNCATE `mail_log`;
TRUNCATE `notes`;
TRUNCATE `notes_history`;
TRUNCATE `pages_log`;
TRUNCATE `pwd_reset`;
TRUNCATE `reports`;
TRUNCATE `rep_columns`;
TRUNCATE `rights`;
TRUNCATE `settings`;
TRUNCATE `pwd_reset`;
TRUNCATE `pwd_reset`;

SET FOREIGN_KEY_CHECKS=1;

/** IMPORT SECTION */

INSERT INTO `users` (`user_name`, `password`, `can_login`, `status`, `roles`, `first_name`, `last_name`, `call_name`, `editable_call_name`, `email_name`,`language`, `sex`, `gdpr_accepted_at`, `last_read_news`) VALUES
('autotest_admin',  'f4ad5b4e691802fca51711dede771a36', 'YES', 'PLAYER', 'SUPER,USR,ATT', 'Autotest', 'admin', 'autotest-admin', 'NO', 'autotest-admin', 'CZ', 'MALE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('autotest_user',   '58d26e9a3381ace5e682dc26bf780dd4', 'YES', 'PLAYER', '', 'Autotest', 'user', 'autotest-user', 'NO', 'autotest-user', 'CZ', 'MALE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('autotest_member',  '58d26e9a3381ace5e682dc26bf780dd4', 'YES', 'MEMBER', '', 'Autotest', 'member', 'autotest-member', 'NO', 'autotest-member', 'CZ', 'MALE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('autotest_init',  '58d26e9a3381ace5e682dc26bf780dd4', 'YES', 'INIT', '', 'Autotest', 'init', 'autotest-init', 'NO', 'autotest-init', 'CZ', 'MALE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP),
('autotest_sick',  '58d26e9a3381ace5e682dc26bf780dd4', 'YES', 'SICK', '', 'Autotest', 'sick', 'autotest-sick', 'NO', 'autotest-sick', 'CZ', 'MALE', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO `usr_mails`(`user_id`, `email`, `type`) VALUES 
(2,'admin@autotest.tymy.cz','DEF'),
(3,'user@autotest.tymy.cz','DEF'),
(4,'member@autotest.tymy.cz','DEF'),
(5,'init@autotest.tymy.cz','DEF'),
(6,'sick@autotest.tymy.cz','DEF');

INSERT INTO `rights` (`id`, `right_type`, `name`, `caption`, `a_roles`, `r_roles`, `a_statuses`, `r_statuses`, `a_users`, `r_users`, `dat_mod`, `usr_mod`) VALUES
(1, 'SYS', 'EVE_CREATE', 'Create event', 'ATT', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(2, 'SYS', 'EVE_UPDATE', 'Modify event', 'ATT', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(3, 'SYS', 'EVE_DELETE', 'Delete event', 'ATT', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(4, 'SYS', 'ATT_UPDATE', 'Update attendance plans of other players', 'ATT', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(5, 'SYS', 'EVE_ATT_UPDATE', 'Enter real attendance', 'ATT', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(6, 'SYS', 'USR_CREATE', 'Insert new user', 'USR', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(7, 'SYS', 'USR_UPDATE', 'Edit user', 'USR', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(8, 'SYS', 'USR_HDEL', 'Physical user delete', 'SUPER', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(9, 'SYS', 'TEAM_UPDATE', 'Modify team settings', 'SUPER', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(10, 'SYS', 'REP_SETUP', 'Spravovat reporty', 'SUPER', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(11, 'SYS', 'DSSETUP', 'Discussions management', 'SUPER', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(12, 'SYS', 'ASK.VOTE_UPDATE', 'Modify vote definitions', NULL, NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(13, 'SYS', 'ASK.VOTE_DELETE', 'Delete votes', NULL, NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(14, 'SYS', 'ASK.VOTE_CREATE', 'Create new votes', NULL, NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(15, 'SYS', 'ASK.VOTE_RESET', 'Reset users votes', NULL, NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(16, 'SYS', 'FILE_CREATE', 'Upload files', NULL, NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(17, 'SYS', 'FILE_UPDATE', 'Modify uploaded files', NULL, NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(18, 'SYS', 'FILE_DELETE', 'Delete uploaded files', NULL, NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(19, 'SYS', 'DEBTS_TEAM', 'Správa týmového dlužníčku', 'SUPER', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, 1),
(20, 'USR', 'ADMINONLY', 'SUPER roles only', 'SUPER', NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP, NULL),
(21, 'USR', 'MAINADMIN', 'Only user id 1 - superadmin', NULL, NULL, NULL, NULL, '1', NULL, CURRENT_TIMESTAMP, NULL);

INSERT INTO `event_types` (`id`, `code`, `caption`, `pre_status_set`, `post_status_set`, `mandatory`, `dat_mod`, `usr_mod`) VALUES
(1, 'TRA', 'Trénink', 1, 3, 'FREE', CURRENT_TIMESTAMP, 0),
(2, 'RUN', 'Běhání', 1, 3, 'FREE', CURRENT_TIMESTAMP, 0),
(3, 'MEE', 'Schůze', 1, 3, 'WARN', CURRENT_TIMESTAMP, 0),
(4, 'TOU', 'Turnaj', 2, 4, 'WARN', CURRENT_TIMESTAMP, 0),
(5, 'CMP', 'Soustředění', 2, 4, 'WARN', CURRENT_TIMESTAMP, 0);