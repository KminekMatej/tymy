
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 28.03.2022 22:08:10


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `user` ADD `hide_disc_desc` BOOLEAN NULL DEFAULT NULL AFTER `skin`;
UPDATE `user` SET `hide_disc_desc`=1 WHERE `user`.`id` IN (SELECT `user_id` FROM `settings` WHERE `name` LIKE 'MENU_TYPE' AND `varchar_value` LIKE 'SHORT');


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-03-28T22-08-10, in file 2022-03-28T22-08-10.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
