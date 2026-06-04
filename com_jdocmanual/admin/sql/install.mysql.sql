CREATE TABLE IF NOT EXISTS `#__jdm_manuals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manual` varchar(128) NOT NULL,
  `language` varchar(32) NOT NULL,
  `home` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `path` VARCHAR(128) NULL DEFAULT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `ordering` INT NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jdm_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manual` varchar(128) NOT NULL,
  `language` char(7) NOT NULL,
  `path` varchar(2048) NOT NULL,
  `title` varchar(512) NOT NULL,
  `source_url` varchar(512) NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  `html` mediumtext COLLATE utf8mb4_unicode_ci,
  `order_next` text COLLATE utf8mb4_unicode_ci,
  `order_previous` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `manual` (`manual`),
  KEY `language` (`language`),
  KEY `path` (`path`),
  KEY `state` (`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jdm_article_stashes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `eid` INT(11) NOT NULL,
  `manual` varchar(128) NOT NULL,
  `language` char(7) NOT NULL,
  `path` varchar(2048) NOT NULL,
  `title` varchar(512) NOT NULL,
  `source_url` varchar(512) NOT NULL,
  `pr` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `commit_message` varchar(256) DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `markdown_text` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jdm_languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lang_id` int(11) NOT NULL,
  `state` int(11) NOT NULL
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jdm_menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manual` varchar(256) NOT NULL,
  `language` char(8) NOT NULL DEFAULT 'en',
  `menu` mediumtext COLLATE utf8mb4_unicode_ci,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  `last_update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS`#__jdm_menu_stashes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `manual` varchar(128) NOT NULL,
  `pr` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `commit_message` varchar(256) DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `menu_text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `manual` (`manual`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jdm_menu_headings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manual` varchar(128) NOT NULL,
  `language` char(8) NOT NULL,
  `heading` varchar(128) NOT NULL,
  `title` varchar(512) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jdm_git_updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manual` char(16) NOT NULL,
  `language` char(8) NOT NULL,
  `last_update` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jdm_feedback` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `session_id` VARCHAR(32) NOT NULL ,
    `manual` VARCHAR(16) NOT NULL ,
    `language` VARCHAR(8) NOT NULL ,
    `path` VARCHAR(2048) NOT NULL ,
    `likeitornot` VARCHAR(8) NULL,
    `comment` VARCHAR(1024) NULL ,
    `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `session` (`session_id`),
    KEY `manual` (`manual`),
    KEY `language` (`language`),
    KEY `path` (`path`),
    KEY `likeitornot` (`likeitornot`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
