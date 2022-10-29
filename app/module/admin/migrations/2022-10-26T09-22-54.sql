
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 26.10.2022 09:22:54


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `user` CHANGE `jersey_number` `jersey_number` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;
UPDATE `user` SET `jersey_number` = NULL WHERE `jersey_number` = '';

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-10-26T09-22-54, in file 2022-10-26T09-22-54.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
