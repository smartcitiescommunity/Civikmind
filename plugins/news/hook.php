<?php
/*
 *
 -------------------------------------------------------------------------
 Plugin GLPI News
 Copyright (C) 2015 by teclib.
 http://www.teclib.com
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Plugin GLPI News.
 Plugin GLPI News is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Plugin GLPI News is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Plugin GLPI News. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

function plugin_news_install() {
   global $DB;

   $migration = new Migration(Plugin::getInfo('news', 'version'));

   if (! $DB->tableExists('glpi_plugin_news_alerts')) {
      $DB->query("
         CREATE TABLE IF NOT EXISTS `glpi_plugin_news_alerts` (
         `id`                       INT NOT NULL AUTO_INCREMENT,
         `date_mod`                 TIMESTAMP NOT NULL,
         `name`                     VARCHAR(255) NOT NULL,
         `message`                  TEXT NOT NULL,
         `date_start`               TIMESTAMP NULL DEFAULT NULL,
         `date_end`                 TIMESTAMP NULL DEFAULT NULL,
         `type`                     INT NOT NULL,
         `is_deleted`               TINYINT(1) NOT NULL DEFAULT 0,
         `is_displayed_onlogin`     TINYINT(1) NOT NULL,
         `is_displayed_oncentral`   TINYINT(1) NOT NULL,
         `entities_id`              INT NOT NULL,
         `is_recursive`             TINYINT(1) NOT NULL DEFAULT 1,
         PRIMARY KEY (`id`)
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
      ");
   }

   if (! $DB->tableExists('glpi_plugin_news_alerts_users')) {
      $DB->query("
         CREATE TABLE IF NOT EXISTS `glpi_plugin_news_alerts_users` (
         `id`                    INT NOT NULL AUTO_INCREMENT,
         `plugin_news_alerts_id` INT NOT NULL,
         `users_id`              INT NOT NULL,
         `state`                 TINYINT(1) NOT NULL,
         PRIMARY KEY (`id`),
         UNIQUE KEY `state_for_user`
            (`plugin_news_alerts_id`,`users_id`,`state`)
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
      ");
   }

   if (! $DB->tableExists('glpi_plugin_news_alerts_targets')) {
      $DB->query("
         CREATE TABLE IF NOT EXISTS `glpi_plugin_news_alerts_targets` (
         `id`                    INT NOT NULL AUTO_INCREMENT,
         `plugin_news_alerts_id` INT NOT NULL,
         `itemtype`              VARCHAR(255) NOT NULL,
         `items_id`              INT NOT NULL,
         PRIMARY KEY (`id`),
         UNIQUE KEY `alert_itemtype_items_id`
            (`plugin_news_alerts_id`, `itemtype`,`items_id`)
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
      ");
   }

   /* Remove old table */
   if ($DB->tableExists('glpi_plugin_news_profiles')) {
      $DB->query("DROP TABLE IF EXISTS `glpi_plugin_news_profiles`;");
   }

   // add displayed on login flag
   if (!$DB->fieldExists("glpi_plugin_news_alerts", "is_displayed_onlogin")) {
      $migration->addField("glpi_plugin_news_alerts", "is_displayed_onlogin", 'bool');
   }

   // add displayed on helpdesk flag
   if (!$DB->fieldExists("glpi_plugin_news_alerts", "is_displayed_onhelpdesk")) {
      $migration->addField("glpi_plugin_news_alerts", "is_displayed_onhelpdesk", 'bool');
   }

   if (!$DB->fieldExists("glpi_plugin_news_alerts", "date_creation")) {
      if ($migration->addField("glpi_plugin_news_alerts", "date_creation", 'date')) {
         $migration->addKey("glpi_plugin_news_alerts", "date_creation");
      }
   }

   // add close allowed flag
   if (!$DB->fieldExists("glpi_plugin_news_alerts", "is_close_allowed")) {
      $migration->addField("glpi_plugin_news_alerts", "is_close_allowed", 'bool');
   }

   // add type field on alert (to display icons)
   if (!$DB->fieldExists("glpi_plugin_news_alerts", "type")) {
      $migration->addField("glpi_plugin_news_alerts", "type", 'integer');
   }

   // add activity flag
   if (!$DB->fieldExists("glpi_plugin_news_alerts", "is_active")) {
      if ($migration->addField("glpi_plugin_news_alerts", "is_active", 'bool')) {
         $migration->addKey("glpi_plugin_news_alerts", "is_active");
      }
   }

   // fix is_default default value
   $alert_fields = $DB->listFields('glpi_plugin_news_alerts');
   if ($alert_fields['is_deleted']['Default'] !== '0') {
      $migration->changeField("glpi_plugin_news_alerts",
                           "is_deleted", "is_deleted",
                           "TINYINT(1) NOT NULL DEFAULT 0");
   }

   // end/start dates can be null
   $migration->changeField("glpi_plugin_news_alerts",
                           "date_end", "date_end",
                           "TIMESTAMP NULL DEFAULT NULL");
   $migration->changeField("glpi_plugin_news_alerts",
                           "date_start", "date_start",
                           "TIMESTAMP NULL DEFAULT NULL");

   if ($DB->fieldExists("glpi_plugin_news_alerts", "profiles_id")) {
      // migration of direct profiles into targets table
      $query_targets = "INSERT INTO glpi_plugin_news_alerts_targets
                           (plugin_news_alerts_id, itemtype, items_id)
                           SELECT id, 'Profile', profiles_id
                           FROM glpi_plugin_news_alerts";
      $res_targets = $DB->query($query_targets) or die("fail to migration targets");

      //drop old field
      $migration->dropField("glpi_plugin_news_alerts", "profiles_id");
   }

   // install default display preferences
   $dpreferences = new DisplayPreference;
   $found_dpref = $dpreferences->find(['itemtype' => ['LIKE', '%PluginNews%']]);
   if (count($found_dpref) == 0) {
      $DB->query("INSERT INTO `glpi_displaypreferences`
                     (`itemtype`, `num`, `rank`, `users_id`)
                  VALUES
                     ('PluginNewsAlert', 2, 1, 0),
                     ('PluginNewsAlert', 3, 2, 0),
                     ('PluginNewsAlert', 6, 4, 0)");
   }

   // add displayed on central flag
   if (!$DB->fieldExists("glpi_plugin_news_alerts", "is_displayed_oncentral")) {
      $migration->addField("glpi_plugin_news_alerts", "is_displayed_oncentral", 'bool', ['value' => true]);
   }

   $migration->migrationOneTable("glpi_plugin_news_alerts");
   return true;
}

function plugin_news_uninstall() {
   global $DB;

   $DB->query("DROP TABLE IF EXISTS `glpi_plugin_news_alerts`;");
   $DB->query("DROP TABLE IF EXISTS `glpi_plugin_news_profiles`;");
   $DB->query("DROP TABLE IF EXISTS `glpi_plugin_news_alerts_users`;");
   $DB->query("DROP TABLE IF EXISTS `glpi_plugin_news_alerts_targets`;");
   $DB->query("DELETE FROM `glpi_profiles` WHERE `name` LIKE '%plugin_news%';");
   $DB->query("DELETE FROM `glpi_displaypreferences` WHERE `itemtype` LIKE '%PluginNews%';");

   return true;
}
