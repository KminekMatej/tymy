
-- SQL Migration file
-- Author: Matěj Kmínek <m@kminet.eu>
-- Created: 30.01.2022 13:02:19


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `event_types` ADD `color` VARCHAR(6) NULL DEFAULT NULL AFTER `caption`;

UPDATE `event_types` SET `color` = '5cb85c' WHERE `event_types`.`code` = 'TRA';
UPDATE `event_types` SET `color` = '0275d8' WHERE `event_types`.`code` = 'RUN';
UPDATE `event_types` SET `color` = '795548' WHERE `event_types`.`code` = 'MEE';
UPDATE `event_types` SET `color` = 'f0ad4e' WHERE `event_types`.`code` = 'TOU';
UPDATE `event_types` SET `color` = '5bc0de' WHERE `event_types`.`code` = 'CMP';
UPDATE `event_types` SET `color` = '827f76' WHERE `event_types`.`color` IS NULL;

ALTER TABLE `statuses` ADD `color` VARCHAR(6) NULL DEFAULT NULL AFTER `caption`;
UPDATE `statuses` SET `color` = '5cb85c' WHERE `statuses`.`code` = 'YES';
UPDATE `statuses` SET `color` = 'e63333' WHERE `statuses`.`code` = 'NO';
UPDATE `statuses` SET `color` = 'ffbf01' WHERE `statuses`.`code` = 'LAT';
UPDATE `statuses` SET `color` = 'f2ab7e' WHERE `statuses`.`code` = 'DKY';
UPDATE `statuses` SET `color` = 'd9ff00' WHERE `statuses`.`color` IS NULL;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-01-30T13-02-19, in file 2022-01-30T13-02-19.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
