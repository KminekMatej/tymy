
-- SQL Migration file
-- Author: Matej Kminek <m@kminet.eu>
-- Created: 25.10.2021 12:02:44


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

DELETE FROM `ask_items` WHERE `quest_id` NOT IN (SELECT `id` FROM `ask_quests` WHERE 1);
ALTER TABLE `ask_items` CHANGE `quest_id` `quest_id` INT(11) NOT NULL;

ALTER TABLE `ask_items` ENGINE = INNODB;
ALTER TABLE `ask_quests` ENGINE = INNODB;
ALTER TABLE `ask_votes` ENGINE = INNODB;

ALTER TABLE `ask_items` ADD FOREIGN KEY (`quest_id`) REFERENCES `ask_quests`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ask_votes` ADD FOREIGN KEY (`quest_id`) REFERENCES `ask_quests`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ask_votes` ADD FOREIGN KEY (`item_id`) REFERENCES `ask_items`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2021-10-25T12-02-44, in file 2021-10-25T12-02-44.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
