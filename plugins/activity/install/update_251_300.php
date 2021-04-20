<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

/**
 * Update from 2.5.1 to 2.6.0
 * Glpi upgrade to 9.5
 * @return bool for success (will die for most error)
 * */

ini_set("memory_limit", "-1");
ini_set("max_execution_time", 0);
chdir(dirname($_SERVER["SCRIPT_FILENAME"]));

if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', realpath('..'));
}

include_once (GLPI_ROOT."/inc/autoload.function.php");
include_once (GLPI_ROOT."/inc/db.function.php");
include_once (GLPI_ROOT."/inc/based_config.php");
include_once (GLPI_CONFIG_DIR."/config_db.php");
include_once (GLPI_ROOT."/inc/define.php");

$GLPI = new GLPI();
$GLPI->initLogger();
Config::detectRootDoc();

if (is_writable(GLPI_SESSION_DIR)) {
   Session::setPath();
} else {
   die("Can't write in ".GLPI_SESSION_DIR."\r\n");
}
Session::start();
$_SESSION['glpi_use_mode'] = 0;
Session::loadLanguage();

if (!$DB->connected) {
   die("No DB connection\r\n");
}
$CFG_GLPI['notifications_ajax']    = 0;
$CFG_GLPI['notifications_mailing'] = 0;
$CFG_GLPI['use_notifications']     = 0;

function update251to300() {
   global $DB;
   $dbu = new DbUtils();
   $migration = new Migration("3.0.0");

   $add_temporary_column_query = "ALTER TABLE `glpi_planningeventcategories` ADD `old_id` int(11) NOT NULL DEFAULT 0;";
   $DB->queryOrDie($add_temporary_column_query);

   $cats = $dbu->getAllDataFromTable('glpi_plugin_activity_activitytypes');

   foreach ($cats as $cat) {
      $migrate_cat_activities_query = 'INSERT INTO `glpi_planningeventcategories` (`name`, `comment`, `old_id`) 
           VALUES("' . $cat['completename'] . '","' . addslashes($cat['comment']) . '","' . $cat['id'] . '")';

      $DB->query($migrate_cat_activities_query);

   }


   $activities = $dbu->getAllDataFromTable('glpi_plugin_activity_activities');
   $add_temporary_column_query = "ALTER TABLE `glpi_planningexternalevents` ADD `old_id` int(11) NOT NULL DEFAULT 0;";
   $DB->queryOrDie($add_temporary_column_query);

   foreach ($activities as $activity) {

      if (strstr($activity['name'], '>')) {
         list($parent, $child) = explode('>', $activity['name']);
         $name = $child;
      } else {
         $name = $activity['name'];
      }
      $migrate_activities_query = 'INSERT INTO `glpi_planningexternalevents`(`entities_id`,`users_id`,`name`,`text`,`begin`,`end`,`state`,`planningeventcategories_id`, `old_id`)
                                    VALUES("' . $activity['entities_id'] . '", "' . $activity['users_id'] . '", "' . addslashes($name) . '", "' . addslashes($activity['comment']) . '",
                                     "' . $activity['begin'] . '", "' . $activity['end'] . '", "' . $activity['is_planned'] . '","' . $activity['plugin_activity_activitytypes_id'] . '", "' . $activity['id'] . '")';


      $DB->query($migrate_activities_query);

      $migrate_cra_activities_query = 'INSERT INTO `glpi_plugin_activity_planningexternalevents` (`is_oncra`, `planningexternalevents_id`, `actiontime`) VALUES
                                           ("' . $activity['is_usedbycra'] . '", "' . $activity['id'] . '", "' . $activity['actiontime'] . '")';


      $DB->query($migrate_cra_activities_query);
   }

   $new_cats = $dbu->getAllDataFromTable('glpi_planningeventcategories', ['old_id'  => ['>', 0]]);

   foreach ($new_cats as $new_cat) {

      if (strstr($new_cat['name'], '>')) {
         list($parent, $child) = explode('>', $new_cat['name']);
         $name = $child;
      } else {
         $name = $new_cat['name'];
      }
      $query = "UPDATE `glpi_planningexternalevents` 
                         SET `planningeventcategories_id`='" . $new_cat['id'] . "'
                         WHERE `planningeventcategories_id`= " . $new_cat['old_id'] . ";";
      $DB->query($query);

      $query_create_eventtemplates = 'INSERT INTO `glpi_planningexternaleventtemplates` (`name`, `state`, `planningeventcategories_id`) VALUES
                                           ("' .  addslashes($name) . '", "' . 1 . '", "' . $new_cat['id'] . '")';

      $DB->query($query_create_eventtemplates);
   }

   $remove_temporary_column_query = "ALTER TABLE `glpi_planningeventcategories` DROP `old_id`;";
   $DB->queryOrDie($remove_temporary_column_query);

   $new_events = $dbu->getAllDataFromTable('glpi_planningexternalevents', ['old_id'  => ['>', 0]]);

   foreach ($new_events as $new_event) {

      $query = "UPDATE `glpi_plugin_activity_planningexternalevents` 
                         SET `planningexternalevents_id`='" . $new_event['id'] . "'
                         WHERE `planningexternalevents_id`= " . $new_event['old_id'] . ";";
      $DB->query($query);


   }

   $remove_temporary_column_query = "ALTER TABLE `glpi_planningexternalevents` DROP `old_id`;";
   $DB->queryOrDie($remove_temporary_column_query);

   $migration->dropTable('glpi_plugin_activity_activitytypes');
   $migration->dropTable('glpi_plugin_activity_activities');
}
