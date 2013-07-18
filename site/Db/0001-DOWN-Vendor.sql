/* Inserting Change in the versioning table */
DELETE FROM DB_VERSIONING WHERE `version` = '0001';

DROP TABLE IF EXISTS `vendor`;