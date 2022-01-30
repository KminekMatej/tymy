
-- SQL Migration file
-- Author: Matěj Kmínek <m@kminet.eu>
-- Created: 30.01.2022 09:46:25


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `event_types` ADD FOREIGN KEY (`pre_status_set`) REFERENCES `status_sets`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `event_types` ADD FOREIGN KEY (`post_status_set`) REFERENCES `status_sets`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `events` 
ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `id`, 
ADD `created_user_id` INT NULL DEFAULT NULL AFTER `created`, 
ADD `event_type_id` INT NOT NULL AFTER `created_user_id`;

UPDATE `events` SET `event_type_id`=(SELECT `id` FROM `event_types` WHERE `event_types`.`code`=`events`.`type`) WHERE 1;

ALTER TABLE `events` ADD FOREIGN KEY (`event_type_id`) REFERENCES `event_types`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `events` DROP `type`;

ALTER TABLE `events` 
CHANGE `caption` `caption` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL, 
CHANGE `close_time` `close_time` DATETIME NOT NULL, 
CHANGE `start_time` `start_time` DATETIME NOT NULL, 
CHANGE `end_time` `end_time` DATETIME NOT NULL;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-01-30T09-46-25, in file 2022-01-30T09-46-25.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
