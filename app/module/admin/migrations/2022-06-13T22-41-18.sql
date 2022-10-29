
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 13.06.2022 22:41:18


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `ask_quests` DROP `main_menu`;
ALTER TABLE `ask_quests` 
CHANGE `min_items` `min_items` INT(11) NULL DEFAULT NULL, 
CHANGE `max_items` `max_items` INT(11) NULL DEFAULT NULL;

UPDATE `ask_quests` SET `min_items`= NULL WHERE `min_items`= -1;
UPDATE `ask_quests` SET `max_items`= NULL WHERE `max_items`= -1;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-06-13T22-41-18, in file 2022-06-13T22-41-18.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
