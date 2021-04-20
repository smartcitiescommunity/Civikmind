CREATE TABLE `glpi_plugin_tasklists_taskstates`
(
    `id`           INT(11)    NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `entities_id`  INT(11)    NOT NULL                  DEFAULT '0',
    `is_recursive` TINYINT(1) NOT NULL                  DEFAULT '0',
    `is_finished`  TINYINT(1) NOT NULL                  DEFAULT '0',
    `tasktypes`    VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL,
    `color`        VARCHAR(200)                         DEFAULT '#CCC' NOT NULL,
    `comment`      TEXT COLLATE utf8_unicode_ci,
    PRIMARY KEY (`id`),
    KEY `name` (`name`),
    KEY `entities_id` (`entities_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

INSERT INTO `glpi_plugin_tasklists_taskstates` (`id`, `name`, `entities_id`, `is_recursive`, `comment`, `color`,
                                                `tasktypes`)
VALUES (1, 'To do', '0', '1', NULL, '#CCC', NULL);
INSERT INTO `glpi_plugin_tasklists_taskstates` (`id`, `name`, `entities_id`, `is_recursive`, `comment`, `color`,
                                                `tasktypes`)
VALUES (2, 'Done', '0', '1', NULL, '#CCC', NULL);

ALTER TABLE `glpi_plugin_tasklists_tasks`
    CHANGE `state` `plugin_tasklists_taskstates_id` INT(11) NOT NULL DEFAULT '0'
        COMMENT 'RELATION to glpi_plugin_tasklists_taskstates (id)';
ALTER TABLE `glpi_plugin_tasklists_tasks`
    ADD `is_archived` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_tasklists_tasks`
    ADD `client` VARCHAR(255) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `glpi_plugin_tasklists_tasks`
    ADD `date_creation` DATETIME DEFAULT NULL;
ALTER TABLE `glpi_plugin_tasklists_tasks`
    ADD `is_template` SMALLINT(6) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_tasklists_tasks`
    ADD `template_name` VARCHAR(200) COLLATE utf8_unicode_ci NOT NULL DEFAULT '';

CREATE TABLE `glpi_plugin_tasklists_stateorders`
(
    `id`                             INT(11) NOT NULL AUTO_INCREMENT, -- id
    `plugin_tasklists_taskstates_id` INT(11) NOT NULL DEFAULT 0,
    `plugin_tasklists_tasktypes_id`  INT(11) NOT NULL DEFAULT 0,
    `ranking`                        INT(11) NULL,
    PRIMARY KEY (`id`),
    KEY `plugin_tasklists_tasktypes_id` (`plugin_tasklists_tasktypes_id`),
    KEY `plugin_tasklists_taskstates_id` (`plugin_tasklists_taskstates_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `glpi_plugin_tasklists_typevisibilities`
(
    `id`                            INT(11) NOT NULL AUTO_INCREMENT,
    `groups_id`                     INT(11) NOT NULL DEFAULT '0',
    `plugin_tasklists_tasktypes_id` INT(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `plugin_tasklists_tasktypes_id` (`plugin_tasklists_tasktypes_id`),
    KEY `groups_id` (`groups_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `glpi_plugin_tasklists_preferences`
(
    `id`           INT(11) NOT NULL
        COMMENT 'RELATION to glpi_users(id)',
    `default_type` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `glpi_plugin_tasklists_tickets`
(
    `id`                        INT(11) NOT NULL AUTO_INCREMENT,
    `tickets_id`                INT(11) NOT NULL DEFAULT '0',
    `plugin_tasklists_tasks_id` INT(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    KEY `plugin_tasklists_tasks_id` (`plugin_tasklists_tasks_id`),
    KEY `tickets_id` (`tickets_id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;

CREATE TABLE `glpi_plugin_tasklists_tasks_comments`
(
    `id`                        INT(11)                      NOT NULL AUTO_INCREMENT,
    `plugin_tasklists_tasks_id` INT(11)                      NOT NULL,
    `users_id`                  INT(11)                      NOT NULL DEFAULT '0',
    `language`                  VARCHAR(5) COLLATE utf8_unicode_ci    DEFAULT NULL,
    `comment`                   TEXT COLLATE utf8_unicode_ci NOT NULL,
    `parent_comment_id`         INT(11)                               DEFAULT NULL,
    `date_creation`             DATETIME                              DEFAULT NULL,
    `date_mod`                  DATETIME                              DEFAULT NULL,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB
    DEFAULT CHARSET = utf8
    COLLATE = utf8_unicode_ci;