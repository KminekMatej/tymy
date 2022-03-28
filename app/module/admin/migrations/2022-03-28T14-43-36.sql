
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 28.03.2022 14:43:36


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

DELETE * FROM `discussions` WHERE `status` = 'DELETED';
ALTER TABLE `discussions` DROP `status`;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-03-28T14-43-36, in file 2022-03-28T14-43-36.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
