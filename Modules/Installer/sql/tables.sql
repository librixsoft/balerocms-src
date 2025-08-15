SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `page` (
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `virtual_title` VARCHAR(250) CHARACTER SET latin1 NOT NULL,
    `static_url` VARCHAR(255) CHARACTER SET latin1 NOT NULL,
    `virtual_content` MEDIUMTEXT NOT NULL,
    `created_at` DATE NOT NULL,
    `visible` INT(10) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=11;

INSERT INTO `page` (`id`, `virtual_title`, `static_url`, `virtual_content`, `created_at`, `visible`)
VALUES
(1, 'Welcome Title', 'welcome-title', 'Welcome content ...', '2025-07-04', 1),
(2, 'Another Example', 'another-example-page-demo', 'Hello there ...', '2025-07-04', 1);
