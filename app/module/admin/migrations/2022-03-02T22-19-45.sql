
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 02.03.2022 22:19:45


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

RENAME TABLE `statuses` TO `status`;
RENAME TABLE `status_sets` TO `status_set`;
ALTER TABLE `status` CHANGE `set_id` `status_set_id` INT(11) NOT NULL;
ALTER TABLE `event_types` CHANGE `pre_status_set` `pre_status_set_id` INT(11) NULL DEFAULT NULL, CHANGE `post_status_set` `post_status_set_id` INT(11) NULL DEFAULT NULL;


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-03-02T22-19-45, in file 2022-03-02T22-19-45.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
