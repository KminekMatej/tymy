
-- SQL Migration file
-- Author: Matěj Kmínek <m@kminet.eu>
-- Created: 03.01.2022 16:09:11


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

DELETE * FROM `usr_mails` WHERE `user_id` NOT IN (SELECT `id` FROM `users` WHERE 1);

ALTER TABLE `usr_mails` ADD FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-01-03T16-09-11, in file 2022-01-03T16-09-11.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
