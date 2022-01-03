
-- SQL Migration file
-- Author: Matej Kminek <m@kminet.eu>
-- Created: 25.10.2021 12:02:44


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

CREATE TABLE IF NOT EXISTS `debt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_user_id` int(11) NOT NULL,
  `amount` float NOT NULL,
  `currency_iso` varchar(3) NOT NULL DEFAULT 'CZK',
  `country_iso` varchar(3) NOT NULL DEFAULT 'CZ',
  `debtor_id` int(11) DEFAULT NULL,
  `debtor_type` enum('user','team','other') NOT NULL DEFAULT 'user',
  `payee_id` int(11) DEFAULT NULL,
  `payee_type` enum('user','team','other') NOT NULL DEFAULT 'user',
  `payee_account_number` varchar(32) DEFAULT NULL,
  `varcode` int(11) DEFAULT NULL,
  `debt_date` timestamp NULL DEFAULT NULL,
  `caption` varchar(255) NOT NULL,
  `payment_sent` timestamp NULL DEFAULT NULL,
  `payment_received` timestamp NULL DEFAULT NULL,
  `note` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `debtor_id` (`debtor_id`),
  KEY `payee_id` (`payee_id`),
  KEY `created_user_id` (`created_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT IGNORE INTO captions (table_name,id,table_column,lc,caption) VALUES ('PAGES', 49,'CAPTION','CZ','Dlužníček');
INSERT IGNORE INTO captions (table_name,id,table_column,lc,caption) VALUES ('PAGES', 49,'CAPTION','EN','Debts');

INSERT IGNORE INTO rights (right_type,name,caption,a_roles,dat_mod,usr_mod) 
VALUES ('SYS', 'DEBTS_TEAM', 'Správa týmového dlužníčku','SUPER',NOW(),1);

CREATE OR REPLACE VIEW v_users AS SELECT u.*, e.email FROM users u LEFT JOIN usr_mails e ON e.user_id = u.id AND e.type='DEF';

DELETE FROM `ask_items` WHERE `quest_id` NOT IN (SELECT `id` FROM `ask_quests` WHERE 1);
ALTER TABLE `ask_items` CHANGE `quest_id` `quest_id` INT(11) NOT NULL;

ALTER TABLE `ask_items` ENGINE = INNODB;
ALTER TABLE `ask_quests` ENGINE = INNODB;
ALTER TABLE `ask_votes` ENGINE = INNODB;

ALTER TABLE `ask_items` ADD FOREIGN KEY (`quest_id`) REFERENCES `ask_quests`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ask_votes` ADD FOREIGN KEY (`quest_id`) REFERENCES `ask_quests`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `ask_votes` ADD FOREIGN KEY (`item_id`) REFERENCES `ask_items`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2021-10-25T12-02-44, in file 2021-10-25T12-02-44.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
