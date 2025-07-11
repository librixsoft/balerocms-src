SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `custom_settings` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `theme` varchar(100) CHARACTER SET latin1 NOT NULL,
  `url_friendly` int(10) NOT NULL,
  `pagination` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

INSERT INTO `custom_settings` (`id`, `theme`, `url_friendly`, `pagination`) VALUES
(1, 'tundra', 1, 5);

CREATE TABLE IF NOT EXISTS `page` (
    `id` int(10) NOT NULL AUTO_INCREMENT,
    `virtual_title` varchar(250) CHARACTER SET latin1 NOT NULL,
    `static_url` varchar(255) CHARACTER SET latin1 NOT NULL,
    `virtual_content` mediumtext NOT NULL,
    `date` varchar(250) CHARACTER SET latin1 NOT NULL,
    `active` int(10) NOT NULL,
    `visible` int(10) NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=11;

INSERT INTO `page` (`id`, `virtual_title`, `static_url`, `virtual_content`, `date`, `active`, `visible`)
VALUES
(1, 'Welcome Title', 'welcome-title', 'Welcome content ...', '2025-07-04', 1, 1);

