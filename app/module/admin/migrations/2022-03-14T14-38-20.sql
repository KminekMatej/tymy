
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 14.03.2022 14:38:20


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `status` ADD `icon` VARCHAR(32) NULL DEFAULT NULL AFTER `color`;
UPDATE `status` SET `icon` = 'far fa-check-circle' WHERE `code` = 'YES';
UPDATE `status` SET `icon` = 'far fa-times-circle' WHERE `code` = 'NO';
UPDATE `status` SET `icon` = 'fas fa-running' WHERE `code` = 'LAT';
UPDATE `status` SET `icon` = 'far fa-question-circle' WHERE `code` = 'DKY';

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-03-14T14-38-20, in file 2022-03-14T14-38-20.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
