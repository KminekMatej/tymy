
-- SQL Migration file
-- Author: Matěj Kmínek <m@kminet.eu>
-- Created: 30.01.2022 09:46:25


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `event_types` ADD FOREIGN KEY (`pre_status_set`) REFERENCES `status_sets`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `event_types` ADD FOREIGN KEY (`post_status_set`) REFERENCES `status_sets`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE;


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-01-30T09-46-25, in file 2022-01-30T09-46-25.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
