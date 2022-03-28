
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 28.03.2022 11:21:37


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `attendance` DROP FOREIGN KEY `attendance_ibfk_4`;
ALTER TABLE `attendance` DROP FOREIGN KEY `attendance_ibfk_3`;

ALTER TABLE `attendance` DROP PRIMARY KEY;
ALTER TABLE `attendance` ADD UNIQUE (`user_id`, `event_id`);
ALTER TABLE `attendance` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `id`, ADD PRIMARY KEY (`id`);

ALTER TABLE `attendance` ADD FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-03-28T11-21-37, in file 2022-03-28T11-21-37.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
