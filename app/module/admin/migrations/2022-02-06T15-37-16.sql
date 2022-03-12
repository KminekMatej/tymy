
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 06.02.2022 15:37:16


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

DELETE FROM `ds_items` WHERE `ds_id` NOT IN (SELECT `id` FROM `discussions` WHERE 1 );

ALTER TABLE `ds_items` ADD FOREIGN KEY (`ds_id`) REFERENCES `discussions`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-02-06T15-37-16, in file 2022-02-06T15-37-16.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
