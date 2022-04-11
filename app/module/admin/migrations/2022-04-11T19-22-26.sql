
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 11.04.2022 19:22:26


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `ask_quests` CHANGE `changeable_votes` `changeable_votes` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1';
UPDATE `ask_quests` SET `changeable_votes` = IF(`changeable_votes` = "NO",0,1) WHERE 1;
ALTER TABLE `ask_quests` CHANGE `changeable_votes` `changeable_votes` BOOLEAN NOT NULL DEFAULT TRUE;

ALTER TABLE `ask_quests` CHANGE `anonymous_results` `anonymous_results` VARCHAR(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '1';
UPDATE `ask_quests` SET `anonymous_results` = IF(`anonymous_results` = "YES",1,0) WHERE 1;
ALTER TABLE `ask_quests` CHANGE `anonymous_results` `anonymous_results` BOOLEAN NOT NULL DEFAULT FALSE;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-04-11T19-22-26, in file 2022-04-11T19-22-26.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
