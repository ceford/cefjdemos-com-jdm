CREATE TABLE IF NOT EXISTS `#__jdm_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_url` varchar(512) NOT NULL,
  `manual` varchar(128) NOT NULL,
  `language` char(7) NOT NULL,
  `heading` varchar(256) NOT NULL,
  `filename` varchar(256) NOT NULL,
  `display_title` varchar(512) NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT NULL,
  `html` mediumtext COLLATE utf8mb4_unicode_ci,
  `order_next` text COLLATE utf8mb4_unicode_ci,
  `order_previous` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `manual` (`manual`),
  KEY `language` (`language`),
  KEY `heading` (`heading`),
  KEY `filename` (`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `#__jdm_article_stashes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `eid` INT(11) NOT NULL,
  `source_url` varchar(512) NOT NULL,
  `manual` varchar(128) NOT NULL,
  `language` char(7) NOT NULL,
  `heading` varchar(256) NOT NULL,
  `filename` varchar(256) NOT NULL,
  `display_title` varchar(512) NOT NULL,
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
  `code` varchar(8) NOT NULL,
  `locale` varchar(8) NOT NULL,
  `title` varchar(255) NOT NULL,
  `index_language` int(11) NOT NULL DEFAULT '0',
  `page_language` int(11) NOT NULL DEFAULT '0',
  `lft` int(11) NOT NULL DEFAULT '0',
  `rgt` int(11) NOT NULL DEFAULT '0',
  `state` tinyint(3) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `#__jdm_languages` (`id`, `code`, `locale`, `title`, `index_language`, `page_language`, `lft`, `rgt`, `state`) VALUES
(1, 'en', 'en-GB', 'English', 1, 1, 0, 0, 1),
(2, 'nl', 'nl-NL', 'Dutch', 1, 1, 0, 0, 1),
(3, 'fr', 'fr-FR',  'French', 1, 1, 0, 0, 1),
(4, 'de', 'de-DE', 'German', 1, 1, 0, 0, 1),
(5, 'es', 'es-ES', 'Spanish', 1, 1, 0, 0, 1),
(6, 'pt', 'pt-PT', 'Portuguese', 1, 1, 0, 0, 1),
(7, 'ptbr', 'pt-BR', 'Portuguese-Brazil', 1, 1, 0, 0, 1),
(8, 'ru', 'ru-RU', 'Russian', 1, 1, 0, 0, 1),
(9, 'it', 'it-IT', 'Italian', 1, 1, 0, 0, 1);

CREATE TABLE IF NOT EXISTS `#__jdm_manuals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manual` varchar(128) NOT NULL,
  `home` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `heading_ini` VARCHAR(128) NULL DEFAULT NULL,
  `filename_ini` VARCHAR(128) NULL DEFAULT NULL,
  `state` tinyint(3) NOT NULL DEFAULT '1',
  `ordering` INT NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `#__jdm_manuals` (`id`, `manual`, `home`, `title`, `heading_ini`, `filename_ini`, `state`) VALUES
(1, 'user', 1, 'Joomla User Manual', 'getting-started', 'introduction-to-joomla.md', 0),
(2, 'help', 0, 'Joomla Help Screens', 'help-screens', 'start-here.md', 0),
(3, 'developer', 0, 'Joomla Developer Manual', 'getting-started', 'developer-required-software.md', 0),
(4, 'docs', 0, 'Joomla Documenter Manual', 'jdocmanual', 'introduction-to-jdocmanual.md', 0);

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
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `manual` varchar(128) NOT NULL,
  `pr` int(11) NOT NULL DEFAULT '0',
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `commit_message` varchar(256) DEFAULT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci,
  `menu_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `#__jdm_menu_stashes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manual` (`manual`),
  ADD KEY `user_id` (`user_id`);

ALTER TABLE `#__jdm_menu_stashes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE IF NOT EXISTS `#__jdm_menu_headings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manual` varchar(128) NOT NULL,
  `language` char(8) NOT NULL,
  `heading` varchar(128) NOT NULL,
  `display_title` varchar(512) NOT NULL,
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
    `heading` VARCHAR(128) NOT NULL ,
    `filename` VARCHAR(256) NOT NULL ,
    `likeitornot` VARCHAR(8) NULL,
    `comment` VARCHAR(1024) NULL ,
    `date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `session` (`session_id`),
    KEY `manual` (`manual`),
    KEY `language` (`language`),
    KEY `heading` (`heading`),
    KEY `filename` (`filename`),
    KEY `likeitornot` (`likeitornot`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
