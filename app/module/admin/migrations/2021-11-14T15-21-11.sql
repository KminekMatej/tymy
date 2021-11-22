
-- SQL Migration file
-- Author: Matěj Kmínek <m@kminet.eu>
-- Created: 14.11.2021 15:21:11


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

CREATE TABLE `push_notification` (
  `id` int(11) NOT NULL,
  `type` ENUM('WEB','APNS','FCM') NOT NULL DEFAULT 'WEB',
  `user_id` int(11) NOT NULL,
  `subscription` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


ALTER TABLE `push_notification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_push_notification_user_id` (`user_id`);


ALTER TABLE `push_notification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `push_notification` ADD FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2021-11-14T15-21-11, in file 2021-11-14T15-21-11.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
