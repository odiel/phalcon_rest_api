/* Inserting Change in the versioning table */
DELETE FROM DB_VERSIONING WHERE `version` = '0002';

DROP TABLE IF EXISTS `nonce`;