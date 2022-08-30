
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 30.08.2022 21:24:24


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `ask_quests` CHANGE `status` `status` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'DESIGN';
UPDATE `ask_quests` SET `status` = 'HIDDEN' WHERE `status` = 'DELETED';
ALTER TABLE `ask_quests` CHANGE `status` `status` ENUM('DESIGN','OPENED','CLOSED','HIDDEN') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT 'DESIGN';

UPDATE `ask_quests` SET `result_rights` = NULL WHERE `result_rights` = '';
UPDATE `ask_quests` SET `vote_rights` = NULL WHERE `vote_rights` = '';
UPDATE `ask_quests` SET `alien_vote_rights` = NULL WHERE `alien_vote_rights` = '';


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-08-30T21-24-24, in file 2022-08-30T21-24-24.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
