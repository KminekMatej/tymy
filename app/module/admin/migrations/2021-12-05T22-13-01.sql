
-- SQL Migration file
-- Author: Matěj Kmínek <m@kminet.eu>
-- Created: 05.12.2021 22:13:01


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

UPDATE `ask_quests` SET `alien_vote_rights`=NULL WHERE `alien_vote_rights`=-1;

ALTER TABLE `ask_votes` 
DROP FOREIGN KEY `ask_votes_ibfk_1`,
DROP FOREIGN KEY `ask_votes_ibfk_2`;

ALTER TABLE `ask_votes` DROP PRIMARY KEY;

ALTER TABLE `ask_votes` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);

ALTER TABLE `ask_votes` ADD UNIQUE (`quest_id`, `user_id`, `item_id`);

ALTER TABLE `ask_votes` ADD FOREIGN KEY (`quest_id`) REFERENCES `ask_quests`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ask_votes` ADD FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ask_votes` ADD FOREIGN KEY (`usr_mod`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2021-12-05T22-13-01, in file 2021-12-05T22-13-01.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
