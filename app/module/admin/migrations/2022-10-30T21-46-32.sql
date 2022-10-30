
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 30.10.2022 21:46:32


/*
    Comment for this migration: (not neccessary, but can be handy)
Need to do it again becauseI rewrote it in previous migration

*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `pwd_reset` CHANGE `from_host` `from_host` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-10-30T21-46-32, in file 2022-10-30T21-46-32.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
