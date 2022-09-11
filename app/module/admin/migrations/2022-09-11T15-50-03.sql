
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 11.09.2022 15:50:03


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

RENAME TABLE `export` TO `ical`;
RENAME TABLE `export_settings` TO `ical_items`;

ALTER TABLE `ical` CHANGE `allowed` `enabled` TINYINT(1) NULL DEFAULT NULL;

ALTER TABLE `ical_items` DROP INDEX `export_settings_unique`;
ALTER TABLE `ical_items` CHANGE `export_id` `ical_id` INT(11) NOT NULL;
ALTER TABLE `ical_items` ADD `status_id` INT NOT NULL AFTER `pre_status`;

ALTER TABLE `ical` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ical_items` CHANGE `ical_id` `ical_id` INT(11) NOT NULL;
ALTER TABLE `ical_items` CHANGE `id` `id` INT(11) NOT NULL AUTO_INCREMENT;

UPDATE `ical_items` SET `ical_items`.`status_id`=
(SELECT `status`.`id`
FROM `status`
LEFT JOIN `status_set` ON `status_set`.`id` = `status`.`status_set_id`
LEFT JOIN `event_types` ON `event_types`.`pre_status_set_id`=`status_set`.`id`
WHERE `event_types`.`code`=`ical_items`.`event_type` AND `status`.`code`=`ical_items`.`pre_status`
);

DELETE FROM `ical` WHERE `hash` = '';
DELETE FROM `ical` WHERE `user_id` NOT IN (SELECT `id` FROM `user`);
DELETE FROM `ical_items` WHERE `status_id` = 0 OR `status_id` IS NULL;
DELETE FROM `ical_items` WHERE `ical_id` NOT IN (SELECT `id` FROM `ical`);

ALTER TABLE `ical` ADD FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ical_items` ADD FOREIGN KEY (`ical_id`) REFERENCES `ical`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ical_items` ADD FOREIGN KEY (`status_id`) REFERENCES `status`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `ical_items`
  DROP `event_type`,
  DROP `pre_status`;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-09-11T15-50-03, in file 2022-09-11T15-50-03.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
