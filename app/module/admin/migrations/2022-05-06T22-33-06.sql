
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 06.05.2022 22:33:06


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

RENAME TABLE `discussions` TO `discussion`;
RENAME TABLE `ds_items` TO `discussion_post`;
RENAME TABLE `ds_read` TO `discussion_read`;

ALTER TABLE `discussion_post` CHANGE `ds_id` `discussion_id` INT(11) NOT NULL;
ALTER TABLE `discussion_read` CHANGE `ds_id` `discussion_id` INT(11) NOT NULL;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-05-06T22-33-06, in file 2022-05-06T22-33-06.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
