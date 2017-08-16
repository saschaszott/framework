SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='STRICT_TRANS_TABLES,NO_AUTO_VALUE_ON_ZERO';
SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, AUTOCOMMIT=0;
SET @OLD_TIME_ZONE=@@TIME_ZONE, TIME_ZONE = "+00:00";

-- Schema changes

START TRANSACTION;

-- Add table "opus_version" OPUSVIER-3577

CREATE TABLE IF NOT EXISTS `opus_version` (
    `version` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Internal version number of OPUS.'
)
ENGINE = InnoDB
COMMENT = 'Holds internal OPUS version for controlling update steps.';

-- Update database version

TRUNCATE TABLE `schema_version`;
INSERT INTO `schema_version` (`version`) VALUES (4);

COMMIT;

-- Reset settings

SET TIME_ZONE=@OLD_TIME_ZONE;
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;