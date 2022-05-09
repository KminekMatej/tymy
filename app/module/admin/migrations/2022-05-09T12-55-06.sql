
-- SQL Migration file
-- Author: Matěj Kmínek <matej.kminek@attendees.eu>
-- Created: 09.05.2022 12:55:06


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

CREATE TABLE `discussion_post_reaction` (
  `id` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(11) NOT NULL,
  `discussion_post_id` int(11) NOT NULL,
  `reaction` varchar(4) CHARACTER SET utf8mb4 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE `discussion_post_reaction`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `discussion_post_id` (`discussion_post_id`);


ALTER TABLE `discussion_post_reaction`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `discussion_post_reaction`
  ADD CONSTRAINT `discussion_post_reaction_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `discussion_post_reaction_ibfk_2` FOREIGN KEY (`discussion_post_id`) REFERENCES `discussion_post` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;


-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2022-05-09T12-55-06, in file 2022-05-09T12-55-06.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
