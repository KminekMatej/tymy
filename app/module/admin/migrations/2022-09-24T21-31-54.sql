
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 24.09.2022 21:31:54


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `debt` CHANGE `created_user_id` `created_user_id` INT(11) NULL DEFAULT NULL;
UPDATE `debt` SET `created_user_id` = NULL WHERE `created_user_id` NOT IN (SELECT `id` FROM `user` WHERE 1);
ALTER TABLE `debt` ADD FOREIGN KEY (`debtor_id`) REFERENCES `user`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `debt` ADD FOREIGN KEY (`payee_id`) REFERENCES `user`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

/* attendance_history */

UPDATE `attendance_history` SET `usr_mod` = NULL WHERE `usr_mod` NOT IN (SELECT `id` FROM `user` WHERE 1);
ALTER TABLE `attendance_history` CHANGE `dat_mod` `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `attendance_history` CHANGE `usr_mod` `updated_user_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `attendance_history` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

/* ask_quests */

UPDATE `ask_quests` SET `usr_cre` = NULL WHERE `usr_cre` NOT IN (SELECT `id` FROM `user` WHERE 1);
UPDATE `ask_quests` SET `usr_mod` = NULL WHERE `usr_mod` NOT IN (SELECT `id` FROM `user` WHERE 1);

ALTER TABLE `ask_quests` CHANGE `usr_cre` `created_user_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `ask_quests` CHANGE `dat_cre` `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `ask_quests` ADD FOREIGN KEY (`created_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `ask_quests` CHANGE `dat_mod` `updated` TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `ask_quests` CHANGE `usr_mod` `updated_user_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `ask_quests` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

/* discussion */

UPDATE `discussion` SET `usr_cre` = NULL WHERE `usr_cre` NOT IN (SELECT `id` FROM `user` WHERE 1);
UPDATE `discussion` SET `usr_mod` = NULL WHERE `usr_mod` NOT IN (SELECT `id` FROM `user` WHERE 1);

ALTER TABLE `discussion` CHANGE `usr_cre` `created_user_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `discussion` CHANGE `dat_cre` `created` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `discussion` ADD FOREIGN KEY (`created_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `discussion` CHANGE `dat_mod` `updated` TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `discussion` CHANGE `usr_mod` `updated_user_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `discussion` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

/* discussion_post - store username first to keep at least a bit of history */

ALTER TABLE `discussion_post` ADD `user_name` VARCHAR(64) NULL DEFAULT NULL AFTER `user_id`;
UPDATE `discussion_post` SET `user_name` = (SELECT `call_name` FROM `user` WHERE `user`.`id` = `discussion_post`.`user_id`);

ALTER TABLE `discussion_post` CHANGE `user_id` `user_id` INT(11) NULL DEFAULT NULL;
UPDATE `discussion_post` SET `user_id` = NULL WHERE `user_id` NOT IN (SELECT `id` FROM `user` WHERE 1) OR `user_id` = 0;
ALTER TABLE `discussion_post` ADD FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

UPDATE `discussion_post` SET `usr_mod` = NULL WHERE `usr_mod` NOT IN (SELECT `id` FROM `user` WHERE 1);
ALTER TABLE `discussion_post` CHANGE `usr_mod` `updated_user_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `discussion_post` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `discussion_post` CHANGE `dat_mod` `updated` TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `discussion_post` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

/* discussion_read */

DELETE FROM `discussion_read` WHERE `user_id` NOT IN (SELECT `id` FROM `user` WHERE 1);
DELETE FROM `discussion_read` WHERE `discussion_id` NOT IN (SELECT `id` FROM `discussion` WHERE 1);

ALTER TABLE `discussion_read` ADD FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE; 
ALTER TABLE `discussion_read` ADD FOREIGN KEY (`discussion_id`) REFERENCES `discussion`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/* status */

UPDATE `status` SET `usr_mod` = NULL WHERE `usr_mod` NOT IN (SELECT `id` FROM `user` WHERE 1);
ALTER TABLE `status` CHANGE `usr_mod` `updated_user_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `status` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `status` CHANGE `dat_mod` `updated` TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `status` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

/* event_types */

UPDATE `event_types` SET `usr_mod` = NULL WHERE `usr_mod` NOT IN (SELECT `id` FROM `user` WHERE 1);
ALTER TABLE `event_types` CHANGE `usr_mod` `updated_user_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `event_types` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `event_types` CHANGE `dat_mod` `updated` TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `event_types` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

/* pwd_reset */

DELETE FROM `pwd_reset` WHERE `user_id` NOT IN (SELECT `id` FROM `user` WHERE 1);
ALTER TABLE `pwd_reset` CHANGE `user_id` `user_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `pwd_reset` ADD FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/* rights */

UPDATE `rights` SET `usr_mod` = NULL WHERE `usr_mod` NOT IN (SELECT `id` FROM `user` WHERE 1);
ALTER TABLE `rights` CHANGE `usr_mod` `updated_user_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `rights` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `rights` CHANGE `dat_mod` `updated` TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `rights` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

/* ask_votes */

UPDATE `ask_votes` SET `usr_mod` = NULL WHERE `usr_mod` NOT IN (SELECT `id` FROM `user` WHERE 1);
ALTER TABLE `ask_votes` CHANGE `usr_mod` `updated_user_id` INT(11) NULL DEFAULT NULL;
ALTER TABLE `ask_votes` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE `ask_votes` CHANGE `dat_mod` `updated` TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT NULL;
ALTER TABLE `ask_votes` ADD FOREIGN KEY (`updated_user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

/* delete deleted users */

DELETE FROM `user` WHERE `status`='DELETED';




-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-09-24T21-31-54, in file 2022-09-24T21-31-54.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
