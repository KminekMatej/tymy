
-- SQL Migration file
-- Author: Matěj Kmínek <m@kminet.eu>
-- Created: 14.11.2021 15:39:04


/*
    Comment for this migration: (not neccessary, but can be handy)


*/


-- UP:
-- commands that updates database shall be written here:

ALTER TABLE `captions` ENGINE = INNODB;
ALTER TABLE `discussions` ENGINE = INNODB;
ALTER TABLE `download` ENGINE = INNODB;
ALTER TABLE `download_log` ENGINE = INNODB;
ALTER TABLE `ds_read` ENGINE = INNODB;
ALTER TABLE `ds_watcher` ENGINE = INNODB;
ALTER TABLE `export` ENGINE = INNODB;
ALTER TABLE `export_settings` ENGINE = INNODB;
ALTER TABLE `live` ENGINE = INNODB;
ALTER TABLE `mail_log` ENGINE = INNODB;
ALTER TABLE `pages_log` ENGINE = INNODB;
ALTER TABLE `pwd_reset` ENGINE = INNODB;
ALTER TABLE `reports` ENGINE = INNODB;
ALTER TABLE `rep_columns` ENGINE = INNODB;
ALTER TABLE `rights` ENGINE = INNODB;
ALTER TABLE `rights_cache` ENGINE = INNODB;
ALTER TABLE `settings` ENGINE = INNODB;
ALTER TABLE `statuses` ENGINE = INNODB;
ALTER TABLE `status_sets` ENGINE = INNODB;
ALTER TABLE `texts` ENGINE = INNODB;
ALTER TABLE `usr_mails` ENGINE = INNODB;

-- DOWN:
-- commands that reverts updates from UP section shall be written here:

CALL 'Migrations DOWN are for security reasons temporarily DISABLED. Impossible to migrate down from 2021-11-14T15-39-04, in file 2021-11-14T15-39-04.sql';

-- DOWN migrations are disabled for security reasons.
-- These migrations might often include removal of some existent database columns, which is very dangerous to do on production servers.
-- Whenever we would migrate DOWN, then it would be impossible to migrate UP again, without loss of data.
