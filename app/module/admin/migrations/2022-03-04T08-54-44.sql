
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 04.03.2022 08:54:44


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

RENAME TABLE `attend_history` TO `attendance_history`;
ALTER TABLE `attendance_history` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);
ALTER TABLE `attendance_history` ADD `status_id_from` INT NULL DEFAULT NULL AFTER `entry_type`;
ALTER TABLE `attendance_history` ADD `status_id_to` INT NOT NULL AFTER `pre_desc_from`;

ALTER TABLE `attendance_history` CHANGE `dat_mod` `dat_mod` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

UPDATE `attendance_history` SET `attendance_history`.`status_id_from`=
(SELECT `status`.`id`
FROM `status`
LEFT JOIN `status_set` ON `status_set`.`id` = `status`.`status_set_id`
LEFT JOIN `event_types` ON `event_types`.`pre_status_set_id`=`status_set`.`id`
LEFT JOIN `events` ON `events`.`event_type_id`=`event_types`.`id`
WHERE `events`.`id`=`attendance_history`.`event_id` AND `status`.`code`=`attendance_history`.`pre_status_from`
);

UPDATE `attendance_history` SET `attendance_history`.`status_id_to`=
(SELECT `status`.`id`
FROM `status`
LEFT JOIN `status_set` ON `status_set`.`id` = `status`.`status_set_id`
LEFT JOIN `event_types` ON `event_types`.`pre_status_set_id`=`status_set`.`id`
LEFT JOIN `events` ON `events`.`event_type_id`=`event_types`.`id`
WHERE `events`.`id`=`attendance_history`.`event_id` AND `status`.`code`=`attendance_history`.`pre_status_to`
);

DELETE FROM `attendance_history` WHERE `status_id_to` = 0;
UPDATE `attendance_history` SET `usr_mod` = NULL WHERE `usr_mod` NOT IN (SELECT `id` FROM `users` WHERE 1);

ALTER TABLE `attendance_history` 
ADD FOREIGN KEY (`status_id_from`) REFERENCES `status`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY (`status_id_to`) REFERENCES `status`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY (`usr_mod`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `attendance_history`
  DROP `pre_status_from`,
  DROP `pre_status_to`;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-03-04T08-54-44, in file 2022-03-04T08-54-44.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
