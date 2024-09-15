
-- SQL Migration file
-- Author: Matěj Kmínek <m@kminet.eu>
-- Created: 15.09.2024 17:16:04


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `user` ADD `email` VARCHAR(1023) NULL DEFAULT NULL AFTER `password`; 
UPDATE `user` SET `email` = (SELECT `email` FROM `usr_mails` WHERE `user_id` = `user`.`id` LIMIT 1);
DROP TABLE `usr_mails`;
DROP VIEW IF EXISTS `v_users`;


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2024-09-15T17-16-04, in file 2024-09-15T17-16-04.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
