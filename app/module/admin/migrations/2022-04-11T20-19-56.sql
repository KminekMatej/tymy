
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 11.04.2022 20:19:56


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:


ALTER TABLE `discussions` CHANGE `public_read` `public_read` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1';
UPDATE `discussions` SET `public_read` = IF(`public_read` = "YES",1,0) WHERE 1;
ALTER TABLE `discussions` CHANGE `public_read` `public_read` BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE `discussions` CHANGE `can_modify` `can_modify` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1';
UPDATE `discussions` SET `can_modify` = IF(`can_modify` = "NO",0,1) WHERE 1;
ALTER TABLE `discussions` CHANGE `can_modify` `can_modify` BOOLEAN NOT NULL DEFAULT TRUE;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-04-11T20-19-56, in file 2022-04-11T20-19-56.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
