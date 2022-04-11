
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 11.04.2022 20:31:39


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `user` CHANGE `can_login` `can_login` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1';
UPDATE `user` SET `can_login` = IF(`can_login` = "YES",1,0) WHERE 1;
ALTER TABLE `user` CHANGE `can_login` `can_login` BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE `user` CHANGE `editable_call_name` `editable_call_name` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1';
UPDATE `user` SET `editable_call_name` = IF(`editable_call_name` = "YES",1,0) WHERE 1;
ALTER TABLE `user` CHANGE `editable_call_name` `editable_call_name` BOOLEAN NOT NULL DEFAULT FALSE;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-04-11T20-31-39, in file 2022-04-11T20-31-39.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
