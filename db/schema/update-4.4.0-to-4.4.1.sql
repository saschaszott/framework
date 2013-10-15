SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES';
SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, AUTOCOMMIT=0;

START TRANSACTION;

-- Add field department to dnb_institutes to distinguish institutes from departments
ALTER TABLE `dnb_institutes` ADD `department` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `name`;
ALTER TABLE `dnb_institutes` DROP INDEX `name` , ADD UNIQUE `name` ( `name` , `department` );

COMMIT;

START TRANSACTION;

-- Fix: Schreibfehler in DDC-Klassifikation 620
UPDATE `collections` SET `name` = 'Ingenieurwissenschaften und zugeordnete Tätigkeiten' WHERE `id` = 661 AND `name` = 'Ingenieurwissenschaften und zugeordnete Tätigkeitenn';

COMMIT;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

