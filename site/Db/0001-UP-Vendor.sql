
/* Inserting Change in the versioning table */
INSERT INTO DB_VERSIONING (`version`, `created`, `changes`) VALUES ('0001', NOW(), 'Creating Vendor table');

CREATE TABLE `vendor`
(
  id INT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
  public_key VARCHAR(255) NOT NULL,
  private_key VARCHAR(255) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  last_name VARCHAR (100) NULL,
  email VARCHAR(200) NOT NULL,
  role VARCHAR(100) NOT NULL DEFAULT 'Vendor',
  created DATETIME NOT NULL,
  deleted TINYINT(1) UNSIGNED NOT NULL DEFAULT 0
);

CREATE UNIQUE INDEX unique_id ON `vendor` ( id );
