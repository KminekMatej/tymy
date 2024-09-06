
-- SQL Migration file
-- Author: Matěj Kmínek <m@kminet.eu>
-- Created: 06.09.2024 22:44:33


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `attendance` DROP FOREIGN KEY `attendance_ibfk_5`; 
ALTER TABLE `attendance` ADD CONSTRAINT `attendance_ibfk_5` FOREIGN KEY (`pre_usr_mod`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE; 
ALTER TABLE `attendance` DROP FOREIGN KEY `attendance_ibfk_6`; 
ALTER TABLE `attendance` ADD CONSTRAINT `attendance_ibfk_6` FOREIGN KEY (`post_usr_mod`) REFERENCES `user`(`id`) ON DELETE SET NULL ON UPDATE CASCADE; 

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2024-09-06T22-44-33, in file 2024-09-06T22-44-33.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
