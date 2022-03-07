
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 07.03.2022 21:56:49


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

DELETE FROM `attendance` WHERE `user_id` NOT IN (SELECT `id` FROM `users` WHERE 1);
DELETE FROM `attendance` WHERE `event_id` NOT IN (SELECT `id` FROM `events` WHERE 1);

ALTER TABLE `attendance` ADD FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-03-07T21-56-49, in file 2022-03-07T21-56-49.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
