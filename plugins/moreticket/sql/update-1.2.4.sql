ALTER TABLE glpi_plugin_moreticket_configs
  ADD `urgency_justification` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE glpi_plugin_moreticket_configs
  ADD `urgency_ids` TEXT COLLATE utf8_unicode_ci DEFAULT NULL;

CREATE TABLE `glpi_plugin_moreticket_urgencytickets` (
  `id`            INT(11) NOT NULL AUTO_INCREMENT, -- id ...
  `tickets_id`    INT(11) NOT NULL, -- id du ticket GLPI
  `justification` VARCHAR(255)     DEFAULT NULL, -- justification
  PRIMARY KEY (`id`), -- index
  FOREIGN KEY (`tickets_id`) REFERENCES glpi_tickets (id)
)
  ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;
