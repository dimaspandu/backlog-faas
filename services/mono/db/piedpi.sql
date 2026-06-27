SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

START TRANSACTION;

CREATE TABLE IF NOT EXISTS `items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_general_ci;

INSERT INTO `items` (`id`, `name`, `created_at`) VALUES
  (1, 'Apple',  '2025-12-30 07:34:28'),
  (2, 'Orange', '2025-12-30 07:34:28'),
  (3, 'Banana', '2025-12-30 07:34:28');

COMMIT;
