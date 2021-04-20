ALTER TABLE `glpi_plugin_metademands_fields`
    ADD `display_type` INT(11) NOT NULL DEFAULT '0' AFTER `date_mod`;
ALTER TABLE `glpi_plugin_metademands_fields`
    ADD `used_by_ticket` INT(11) NOT NULL DEFAULT '0' AFTER `display_type`;
ALTER TABLE `glpi_plugin_metademands_fields`
    ADD `hide_title` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_metademands_fields`
    ADD `used_by_child` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_metademands_fields`
    ADD `link_to_user` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_metademands_metademands`
    ADD `validation_subticket` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `glpi_plugin_metademands_tickets_metademands`
    ADD `status` TINYINT(1) NOT NULL DEFAULT '1' AFTER `tickettemplates_id`;
ALTER TABLE `glpi_plugin_metademands_fields`
    ADD `default_use_id_requester` TINYINT(1) NOT NULL DEFAULT '1';
ALTER TABLE `glpi_plugin_metademands_tasks`
    ADD `block_use` VARCHAR (255) NOT NULL DEFAULT '[]';

DROP TABLE IF EXISTS `glpi_plugin_metademands_metademandvalidations`;
CREATE TABLE `glpi_plugin_metademands_metademandvalidations`
(
    `id`                                int(11) NOT NULL AUTO_INCREMENT,
    `tickets_id`                        int(11) NOT NULL    DEFAULT '0',
    `plugin_metademands_metademands_id` int(11) NOT NULL    DEFAULT '0',
    `users_id`                          int(11) NOT NULL    DEFAULT '0',
    `validate`                          tinyint(1) NOT NULL DEFAULT '0',
    `date`                              timestamp NOT NULL,
    `tickets_to_create`                 text NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci
  AUTO_INCREMENT = 1;

ALTER TABLE `glpi_plugin_metademands_fields` CHANGE `comment` `comment` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_metademands_fields` CHANGE `label2` `label2` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `glpi_plugin_metademands_fields` ADD `additional_number_day` INT(11) NOT NULL DEFAULT '0' AFTER `default_use_id_requester`;
ALTER TABLE `glpi_plugin_metademands_fields` ADD `use_date_now` TINYINT(1) NOT NULL DEFAULT '0' AFTER `additional_number_day`;


