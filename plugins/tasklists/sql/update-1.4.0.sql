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

INSERT INTO `glpi_plugin_tasklists_taskstates` (`id`, `name`, `entities_id`, `is_recursive`, `comment`, `color`,
                                                `tasktypes`)
VALUES (1, 'To do', '0', '1', NULL, '#CCC', NULL);
INSERT INTO `glpi_plugin_tasklists_taskstates` (`id`, `name`, `entities_id`, `is_recursive`, `comment`, `color`,
                                                `tasktypes`)
VALUES (2, 'Done', '0', '1', NULL, '#CCC', NULL);

ALTER TABLE `glpi_plugin_tasklists_tasks`
    CHANGE `state` `plugin_tasklists_taskstates_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'RELATION to glpi_plugin_tasklists_taskstates (id)';
ALTER TABLE `glpi_plugin_tasklists_tasks`
    ADD `is_archived` tinyint(1) NOT NULL default '0';
ALTER TABLE `glpi_plugin_tasklists_tasks`
    ADD `client` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `glpi_plugin_tasklists_tasks`
    ADD `date_creation` datetime default NULL;
ALTER TABLE `glpi_plugin_tasklists_tasks`
    ADD `due_date` date default NULL;
ALTER TABLE `glpi_plugin_tasklists_tasks`
    ADD `is_template` smallint(6) NOT NULL default '0';
ALTER TABLE `glpi_plugin_tasklists_tasks`
    ADD `template_name` varchar(200) collate utf8_unicode_ci NOT NULL default '';

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

CREATE TABLE `glpi_plugin_tasklists_preferences`
(
    `id`           int(11) NOT NULL COMMENT 'RELATION to glpi_users(id)',
    `default_type` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

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