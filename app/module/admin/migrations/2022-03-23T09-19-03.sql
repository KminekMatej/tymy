
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 23.03.2022 09:19:03


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `ask_votes` CHANGE `boolean_value` `boolean_value` VARCHAR(6) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;

UPDATE `ask_votes` SET `boolean_value`=1 WHERE `boolean_value`='TRUE';
UPDATE `ask_votes` SET `boolean_value`=0 WHERE `boolean_value`='FALSE';

ALTER TABLE `ask_votes` CHANGE `boolean_value` `boolean_value` BOOLEAN NULL DEFAULT NULL;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-03-23T09-19-03, in file 2022-03-23T09-19-03.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
