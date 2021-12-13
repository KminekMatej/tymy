
-- SQL Migration file
-- Author: Matěj Kmínek <m@kminet.eu>
-- Created: 13.12.2021 22:13:40


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `users` ADD `skin` VARCHAR(32) NULL DEFAULT NULL AFTER `last_read_news`;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2021-12-13T22-13-40, in file 2021-12-13T22-13-40.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
