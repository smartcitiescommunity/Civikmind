DROP TABLE IF EXISTS `glpi_plugin_tasklists_tasks`;
CREATE TABLE `glpi_plugin_tasklists_tasks`
(
    `id`                            int(11)    NOT NULL auto_increment,
    `entities_id`                   int(11)    NOT NULL                  default '0',
    `is_recursive`                  tinyint(1) NOT NULL                  default '0',
    `name`                          varchar(255) collate utf8_unicode_ci default NULL,
    `plugin_tasklists_tasktypes_id` int(11)    NOT NULL                  default '0' COMMENT 'RELATION to glpi_plugin_tasklists_tasktypes (id)',
    `priority`                      int(11)    NOT NULL                  DEFAULT '1',
    `visibility`                    int(11)    NOT NULL                  DEFAULT '1',
    `actiontime`                    int(11)    NOT NULL                  DEFAULT '0',
    `percent_done`                  int(11)    NOT NULL                  DEFAULT '0',
    `state`                         int(11)    NOT NULL                  DEFAULT '1',
    `due_date`                      date                                 default NULL,
    `users_id`                      int(11)    NOT NULL                  default '0' COMMENT 'RELATION to glpi_users (id)',
    `groups_id`                     int(11)    NOT NULL                  default '0' COMMENT 'RELATION to glpi_groups (id)',
    `comment`                       text collate utf8_unicode_ci,
    `notepad`                       longtext collate utf8_unicode_ci,
    `date_mod`                      datetime                             default NULL,
    `is_deleted`                    tinyint(1) NOT NULL                  default '0',
    PRIMARY KEY (`id`),
    KEY `name` (`name`),
    KEY `entities_id` (`entities_id`),
    KEY `plugin_tasklists_tasktypes_id` (`plugin_tasklists_tasktypes_id`),
    KEY `users_id` (`users_id`),
    KEY `groups_id` (`groups_id`),
    KEY `date_mod` (`date_mod`),
    KEY `is_deleted` (`is_deleted`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_tasklists_tasktypes`;
CREATE TABLE `glpi_plugin_tasklists_tasktypes`
(
    `id`                            int(11)    NOT NULL AUTO_INCREMENT,
    `name`                          varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `entities_id`                   int(11)    NOT NULL                  DEFAULT '0',
    `is_recursive`                  tinyint(1) NOT NULL                  DEFAULT '0',
    `comment`                       text COLLATE utf8_unicode_ci,
    `plugin_tasklists_tasktypes_id` int(11)    NOT NULL                  DEFAULT '0',
    `completename`                  text COLLATE utf8_unicode_ci,
    `level`                         int(11)    NOT NULL                  DEFAULT '0',
    `ancestors_cache`               longtext COLLATE utf8_unicode_ci,
    `sons_cache`                    longtext COLLATE utf8_unicode_ci,
    PRIMARY KEY (`id`),
    KEY `name` (`name`),
    KEY `unicity` (`plugin_tasklists_tasktypes_id`, `name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;