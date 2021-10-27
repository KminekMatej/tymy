
-- SQL Migration file
-- Author: Matěj Kmínek <>
-- Created: 27.10.2021 09:51:07


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `attend_history` ENGINE = INNODB;
ALTER TABLE `attendance` ENGINE = INNODB;
ALTER TABLE `events` ENGINE = INNODB;
ALTER TABLE `event_types` ENGINE = INNODB;

DELETE FROM `attend_history` WHERE `attend_history`.`user_id` NOT IN (SELECT `users`.`id` FROM `users`);
DELETE FROM `attend_history` WHERE `attend_history`.`event_id` NOT IN (SELECT `events`.`id` FROM `events`);
ALTER TABLE `attend_history` ADD FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `attend_history` ADD FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

UPDATE `rights` SET `a_roles`='SUPER' WHERE `a_roles` IS NULL AND `r_roles` IS NULL AND `a_statuses` IS NULL AND `r_statuses` IS NULL AND `a_users` IS NULL AND `r_users` IS NULL;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2021-10-27T09-51-07, in file 2021-10-27T09-51-07.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
