
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 03.03.2022 09:10:04


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `attendance` ADD `pre_status_id` INT NULL DEFAULT NULL AFTER `pre_status`;
ALTER TABLE `attendance` ADD `post_status_id` INT NULL DEFAULT NULL AFTER `post_status`;

UPDATE `attendance` SET `attendance`.`pre_status_id`=
(SELECT `status`.`id`
FROM `status`
LEFT JOIN `status_set` ON `status_set`.`id` = `status`.`status_set_id`
LEFT JOIN `event_types` ON `event_types`.`pre_status_set_id`=`status_set`.`id`
LEFT JOIN `events` ON `events`.`event_type_id`=`event_types`.`id`
WHERE `events`.`id`=`attendance`.`event_id` AND `status`.`code`=`attendance`.`pre_status`
);

UPDATE `attendance` SET `attendance`.`post_status_id`=
(SELECT `status`.`id`
FROM `status`
LEFT JOIN `status_set` ON `status_set`.`id` = `status`.`status_set_id`
LEFT JOIN `event_types` ON `event_types`.`post_status_set_id`=`status_set`.`id`
LEFT JOIN `events` ON `events`.`event_type_id`=`event_types`.`id`
WHERE `events`.`id`=`attendance`.`event_id` AND `status`.`code`=`attendance`.`post_status`
);

ALTER TABLE `attendance` 
ADD FOREIGN KEY (`pre_status_id`) REFERENCES `status`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD FOREIGN KEY (`post_status_id`) REFERENCES `status`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `attendance` DROP `pre_status`;
ALTER TABLE `attendance` DROP `post_status`;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-03-03T09-10-04, in file 2022-03-03T09-10-04.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
