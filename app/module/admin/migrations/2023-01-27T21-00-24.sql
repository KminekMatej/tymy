
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 27.01.2023 21:00:24


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `status` ADD `order` INT NULL DEFAULT NULL AFTER `updated_user_id`;
UPDATE `status` SET `order`=`id` WHERE 1;
ALTER TABLE `status_set` ADD `order` INT NULL DEFAULT NULL AFTER `name`;
UPDATE `status_set` SET `order`=`id` WHERE 1;


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2023-01-27T21-00-24, in file 2023-01-27T21-00-24.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
