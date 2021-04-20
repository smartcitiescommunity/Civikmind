CREATE TABLE `glpi_plugin_tasklists_items_kanbans`
(
    `id`                             INT(11)                              NOT NULL AUTO_INCREMENT, -- id
    `itemtype`                       varchar(100) COLLATE utf8_unicode_ci NOT NULL,
    `items_id`                       int(11)                                       DEFAULT NULL,
    `users_id`                       int(11)                              NOT NULL,
    `plugin_tasklists_taskstates_id` int(11)                              NOT NULL,
    `state`                          int(1)                               NOT NULL DEFAULT 0,
    `date_mod`                       timestamp                            NULL     DEFAULT NULL,
    `date_creation`                  timestamp                            NULL     DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unicity` (`itemtype`, `items_id`, `users_id`, `plugin_tasklists_taskstates_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

ALTER TABLE `glpi_plugin_tasklists_preferences`
    ADD `automatic_refresh`       TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_tasklists_preferences`
    ADD `automatic_refresh_delay` INT(11)    NOT NULL DEFAULT '10';

