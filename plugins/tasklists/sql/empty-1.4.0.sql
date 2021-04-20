DROP TABLE IF EXISTS `glpi_plugin_tasklists_tasks`;
CREATE TABLE `glpi_plugin_tasklists_tasks`
(
    `id`                             int(11)                              NOT NULL auto_increment,
    `entities_id`                    int(11)                              NOT NULL default '0',
    `is_recursive`                   tinyint(1)                           NOT NULL default '0',
    `name`                           varchar(255) collate utf8_unicode_ci          default NULL,
    `plugin_tasklists_tasktypes_id`  int(11)                              NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_tasklists_tasktypes (id)',
    `plugin_tasklists_taskstates_id` int(11)                              NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_tasklists_taskstates (id)',
    `priority`                       int(11)                              NOT NULL DEFAULT '1',
    `visibility`                     int(11)                              NOT NULL DEFAULT '1',
    `actiontime`                     int(11)                              NOT NULL DEFAULT '0',
    `percent_done`                   int(11)                              NOT NULL DEFAULT '0',
    `state`                          int(11)                              NOT NULL DEFAULT '1',
    `due_date`                       date                                          default NULL,
    `users_id`                       int(11)                              NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
    `groups_id`                      int(11)                              NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
    `client`                         varchar(255) COLLATE utf8_unicode_ci          DEFAULT NULL,
    `comment`                        text collate utf8_unicode_ci,
    `notepad`                        longtext collate utf8_unicode_ci,
    `date_mod`                       datetime                                      default NULL,
    `date_creation`                  datetime                                      default NULL,
    `is_template`                    smallint(6)                          NOT NULL default '0',
    `template_name`                  varchar(200) collate utf8_unicode_ci NOT NULL default '',
    `is_deleted`                     tinyint(1)                           NOT NULL default '0',
    `is_archived`                    tinyint(1)                           NOT NULL default '0',
    PRIMARY KEY (`id`),
    KEY `name` (`name`),
    KEY `entities_id` (`entities_id`),
    KEY `plugin_tasklists_tasktypes_id` (`plugin_tasklists_tasktypes_id`),
    KEY `is_template` (`is_template`),
    KEY `users_id` (`users_id`),
    KEY `groups_id` (`groups_id`),
    KEY `date_mod` (`date_mod`),
    KEY `is_deleted` (`is_deleted`),
    KEY `is_archived` (`is_archived`)
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
    KEY `entities_id` (`entities_id`),
    KEY `unicity` (`plugin_tasklists_tasktypes_id`, `name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_tasklists_taskstates`;
CREATE TABLE `glpi_plugin_tasklists_taskstates`
(
    `id`           int(11)    NOT NULL AUTO_INCREMENT,
    `name`         varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `entities_id`  int(11)    NOT NULL                  DEFAULT '0',
    `is_recursive` tinyint(1) NOT NULL                  DEFAULT '0',
    `is_finished`  tinyint(1) NOT NULL                  DEFAULT '0',
    `tasktypes`    varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `color`        varchar(200)                         DEFAULT '#CCC' NOT NULL,
    `comment`      text COLLATE utf8_unicode_ci,
    PRIMARY KEY (`id`),
    KEY `name` (`name`),
    KEY `entities_id` (`entities_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_tasklists_stateorders`;
CREATE TABLE `glpi_plugin_tasklists_stateorders`
(
    `id`                             int(11) NOT NULL auto_increment, -- id
    `plugin_tasklists_taskstates_id` int(11) NOT NULL DEFAULT 0,
    `plugin_tasklists_tasktypes_id`  int(11) NOT NULL DEFAULT 0,
    `ranking`                        int(11) NULL,
    PRIMARY KEY (`id`),
    KEY `plugin_tasklists_tasktypes_id` (`plugin_tasklists_tasktypes_id`),
    KEY `plugin_tasklists_taskstates_id` (`plugin_tasklists_taskstates_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_tasklists_typevisibilities`;
CREATE TABLE `glpi_plugin_tasklists_typevisibilities`
(
    `id`                            int(11) NOT NULL AUTO_INCREMENT,
    `groups_id`                     int(11) NOT NULL default '0',
    `plugin_tasklists_tasktypes_id` int(11) NOT NULL default '0',
    PRIMARY KEY (`id`),
    KEY `plugin_tasklists_tasktypes_id` (`plugin_tasklists_tasktypes_id`),
    KEY `groups_id` (`groups_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_tasklists_preferences`;
CREATE TABLE `glpi_plugin_tasklists_preferences`
(
    `id`           int(11) NOT NULL COMMENT 'RELATION to glpi_users(id)',
    `default_type` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_tasklists_tickets`;
CREATE TABLE `glpi_plugin_tasklists_tickets`
(
    `id`                        int(11) NOT NULL AUTO_INCREMENT,
    `tickets_id`                int(11) NOT NULL DEFAULT '0',
    `plugin_tasklists_tasks_id` int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `plugin_tasklists_tasks_id` (`plugin_tasklists_tasks_id`),
    KEY `tickets_id` (`tickets_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_tasklists_tasks_comments`;
CREATE TABLE `glpi_plugin_tasklists_tasks_comments`
(
    `id`                        int(11)                      NOT NULL AUTO_INCREMENT,
    `plugin_tasklists_tasks_id` int(11)                      NOT NULL,
    `users_id`                  int(11)                      NOT NULL DEFAULT '0',
    `language`                  varchar(5) COLLATE utf8_unicode_ci    DEFAULT NULL,
    `comment`                   text COLLATE utf8_unicode_ci NOT NULL,
    `parent_comment_id`         int(11)                               DEFAULT NULL,
    `date_creation`             datetime                              DEFAULT NULL,
    `date_mod`                  datetime                              DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;