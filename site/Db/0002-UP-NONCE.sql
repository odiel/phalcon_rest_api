
/* Inserting Change in the versioning table */
INSERT INTO DB_VERSIONING (`version`, `created`, `changes`) VALUES ('0002', NOW(), 'Creating Nonce table');

CREATE TABLE `nonce`
(
  id VARCHAR(40) PRIMARY KEY NOT NULL,
  vendor_public_key VARCHAR(32) NOT NULL,
  created DATETIME NOT NULL
);

CREATE UNIQUE INDEX unique_id ON `nonce` ( id );

ALTER TABLE `nonce`
ADD INDEX (`vendor_public_key`) USING BTREE ;
