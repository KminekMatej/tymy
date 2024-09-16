
-- SQL Migration file
-- Author: Matěj Kmínek <m@kminet.eu>
-- Created: 15.09.2024 17:03:48


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

DELETE FROM `user` WHERE `status` = 'DELETED';
ALTER TABLE `user` CHANGE `status` `status` ENUM('INIT','PLAYER','MEMBER','SICK') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NULL DEFAULT NULL; 

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2024-09-15T17-03-48, in file 2024-09-15T17-03-48.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
