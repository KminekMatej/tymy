
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 04.02.2023 16:32:00


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

DELETE FROM `status` WHERE `status_set_id` NOT IN (SELECT `id` FROM `status_set` WHERE 1);
ALTER TABLE `status` ADD FOREIGN KEY (`status_set_id`) REFERENCES `status_set`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `attendance` ADD FOREIGN KEY (`pre_usr_mod`) REFERENCES `user`(`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
ALTER TABLE `attendance` ADD FOREIGN KEY (`post_usr_mod`) REFERENCES `user`(`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2023-02-04T16-32-00, in file 2023-02-04T16-32-00.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
