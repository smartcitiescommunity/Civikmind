<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Mreporting plugin for GLPI
 Copyright (C) 2003-2017 by the mreporting Development Team.

 https://github.com/pluginsGLPI/mreporting
 -------------------------------------------------------------------------

 LICENSE

 This file is part of mreporting.

 mreporting is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 mreporting is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with mreporting. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_mreporting_install() {
   global $DB;

   $version   = plugin_version_mreporting();
   $migration = new Migration($version['version']);

   include_once(Plugin::getPhpDir('mreporting')."/inc/profile.class.php");

   //create profiles table
   $queries = [];
   $queries[] = "CREATE TABLE IF NOT EXISTS `glpi_plugin_mreporting_profiles` (
      `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
      `profiles_id` VARCHAR(45) NOT NULL,
      `reports` CHAR(1),
      PRIMARY KEY (`id`),
      UNIQUE `profiles_id_reports` (`profiles_id`, `reports`)
      )
      ENGINE = InnoDB;";

    //create configuration table
    $queries[] = "CREATE TABLE IF NOT EXISTS `glpi_plugin_mreporting_configs` (
   `id` int(11) NOT NULL auto_increment,
   `name` varchar(255) collate utf8_unicode_ci default NULL,
   `classname` varchar(255) collate utf8_unicode_ci default NULL,
   `is_active` tinyint(1) NOT NULL default '0',
   `is_notified` tinyint(1) NOT NULL default '1',
   `show_graph` tinyint(1) NOT NULL default '0',
   `show_area` tinyint(1) NOT NULL default '0',
   `spline` tinyint(1) NOT NULL default '0',
   `show_label` VARCHAR(10) default NULL,
   `flip_data` tinyint(1) NOT NULL default '0',
   `unit` VARCHAR(10) default NULL,
   `default_delay` VARCHAR(10) default NULL,
   `condition` VARCHAR(255) default NULL,
   `graphtype` VARCHAR(255) default 'SVG',
   PRIMARY KEY  (`id`),
   KEY `is_active` (`is_active`)
   ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

    //create configuration table
    $queries[] = "CREATE TABLE IF NOT EXISTS `glpi_plugin_mreporting_dashboards` (
   `id` int(11) NOT NULL auto_increment,
   `users_id` int(11) NOT NULL,
   `reports_id`int(11) NOT NULL,
   `configuration` VARCHAR(500) default NULL,
   PRIMARY KEY  (`id`)
   ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

   $queries[] = "CREATE TABLE  IF NOT EXISTS `glpi_plugin_mreporting_preferences` (
   `id` int(11) NOT NULL auto_increment,
   `users_id` int(11) NOT NULL default 0,
   `template` varchar(255) collate utf8_unicode_ci default NULL,
   PRIMARY KEY  (`id`),
   KEY `users_id` (`users_id`)
   ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

   // add display preferences
   $query_display_pref = "SELECT id
      FROM glpi_displaypreferences
      WHERE itemtype = 'PluginMreportingConfig'";
   $res_display_pref = $DB->query($query_display_pref);
   if ($DB->numrows($res_display_pref) == 0) {
      $queries[] = "INSERT INTO `glpi_displaypreferences`
         VALUES (NULL,'PluginMreportingConfig','2','2','0');";
      $queries[] = "INSERT INTO `glpi_displaypreferences`
         VALUES (NULL,'PluginMreportingConfig','3','3','0');";
      $queries[] = "INSERT INTO `glpi_displaypreferences`
         VALUES (NULL,'PluginMreportingConfig','4','4','0');";
      $queries[] = "INSERT INTO `glpi_displaypreferences`
         VALUES (NULL,'PluginMreportingConfig','5','5','0');";
      $queries[] = "INSERT INTO `glpi_displaypreferences`
         VALUES (NULL,'PluginMreportingConfig','6','6','0');";
      $queries[] = "INSERT INTO `glpi_displaypreferences`
         VALUES (NULL,'PluginMreportingConfig','8','8','0');";
   }

    $queries[] = "CREATE TABLE IF NOT EXISTS `glpi_plugin_mreporting_notifications` (
      `id` int(11) NOT NULL auto_increment,
      `entities_id` int(11) NOT NULL default '0',
      `is_recursive` tinyint(1) NOT NULL default '0',
      `name` varchar(255) collate utf8_unicode_ci default NULL,
      `notepad` longtext collate utf8_unicode_ci,
      `date_envoie` DATE DEFAULT NULL,
      `notice`INT(11) NOT NULL DEFAULT 0,
      `alert` INT(11) NOT NULL DEFAULT 0,
      `comment` text collate utf8_unicode_ci,
      `date_mod` timestamp NULL default NULL,
      `is_deleted` tinyint(1) NOT NULL default '0',
      PRIMARY KEY  (`id`)
      ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";

   foreach ($queries as $query) {
      $DB->query($query);
   }

   // == Update to 2.1 ==
   $migration->addField('glpi_plugin_mreporting_configs', 'is_notified',
                        'tinyint(1) NOT NULL default "1"', ['after' => 'is_active']);
   $migration->migrationOneTable('glpi_plugin_mreporting_configs');

   // == Update to 2.3 ==
   if (!$DB->fieldExists('glpi_plugin_mreporting_profiles', 'right')
       && $DB->fieldExists('glpi_plugin_mreporting_profiles', 'reports')) {
      //save all profile with right READ
      $right = PluginMreportingProfile::getRight();

      //truncate profile table
      $query = "TRUNCATE TABLE `glpi_plugin_mreporting_profiles`";
      $DB->query($query);

       //migration of field
      $migration->addField('glpi_plugin_mreporting_profiles', 'right', 'char');
      $migration->changeField('glpi_plugin_mreporting_profiles', 'reports',
                             'reports', 'integer');
      $migration->changeField('glpi_plugin_mreporting_profiles', 'profiles_id',
                             'profiles_id', 'integer');
      $migration->dropField('glpi_plugin_mreporting_profiles', 'config');

      $migration->migrationOneTable('glpi_plugin_mreporting_profiles');
   }

   // == UPDATE to 0.84+1.0 ==
   $query = "UPDATE `glpi_plugin_mreporting_profiles` pr SET pr.right = ".READ." WHERE pr.right = 'r'";
   $DB->query($query);
   if (!isIndex('glpi_plugin_mreporting_profiles', 'profiles_id_reports')) {
      $query = "ALTER TABLE glpi_plugin_mreporting_profiles
                ADD UNIQUE INDEX `profiles_id_reports` (`profiles_id`, `reports`)";
      $DB->query($query);
   }

   // Remove GLPI graphtype to fix compatibility with GLPI 9.2.2+
   $query = "UPDATE `glpi_plugin_mreporting_configs` SET `graphtype` = 'SVG' WHERE `graphtype` = 'GLPI'";
   $DB->query($query);

   //== Create directories
   $rep_files_mreporting = GLPI_PLUGIN_DOC_DIR."/mreporting";
   if (!is_dir($rep_files_mreporting)) {
      mkdir($rep_files_mreporting);
   }
   $notifications_folder = GLPI_PLUGIN_DOC_DIR."/mreporting/notifications";
   if (!is_dir($notifications_folder)) {
      mkdir($notifications_folder);
   }

   // == Install notifications
   require_once "inc/notification.class.php";
   PluginMreportingNotification::install($migration);
   CronTask::Register('PluginMreportingNotification', 'SendNotifications', MONTH_TIMESTAMP);

   $migration->addField("glpi_plugin_mreporting_preferences", "selectors", "text");
   $migration->migrationOneTable('glpi_plugin_mreporting_preferences');

   // == Init available reports
   require_once "inc/baseclass.class.php";
   require_once "inc/common.class.php";
   require_once "inc/config.class.php";
   $config = new PluginMreportingConfig();
   $config->createFirstConfig();

   PluginMreportingProfile::addRightToAllProfiles();
   PluginMreportingProfile::addRightToProfile($_SESSION['glpiactiveprofile']['id']);

   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_mreporting_uninstall() {
   global $DB;

   $migration = new Migration("2.3.0");
   $tables = ["glpi_plugin_mreporting_profiles",
              "glpi_plugin_mreporting_configs",
              "glpi_plugin_mreporting_preferences",
              "glpi_plugin_mreporting_notifications",
              "glpi_plugin_mreporting_dashboards"
   ];

   foreach ($tables as $table) {
      $migration->dropTable($table);
   }

   Toolbox::deleteDir(GLPI_PLUGIN_DOC_DIR."/mreporting/notifications");
   Toolbox::deleteDir(GLPI_PLUGIN_DOC_DIR."/mreporting");

   $objects = ["DisplayPreference", "SavedSearch"];

   foreach ($objects as $object) {
      $obj = new $object();
      $obj->deleteByCriteria(['itemtype' => 'PluginMreportingConfig']);
   }

   require_once "inc/notification.class.php";
   PluginMreportingNotification::uninstall();

   return true;
}

// Define dropdown relations
function plugin_mreporting_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("mreporting")) {
      return ["glpi_profiles" =>  ["glpi_plugin_mreporting_profiles" => "profiles_id"]];
   } else {
      return [];
   }
}

function plugin_mreporting_giveItem($type, $ID, $data, $num) {
   global $LANG;

   $searchopt=&Search::getOptions($type);
   $table=$searchopt[$ID]["table"];
   $field=$searchopt[$ID]["field"];

   $output_type=Search::HTML_OUTPUT;
   if (isset($_GET['display_type'])) {
      $output_type=$_GET['display_type'];
   }

   switch ($type) {

      case 'PluginMreportingConfig':

         switch ($table.'.'.$field) {
            case "glpi_plugin_mreporting_configs.show_label":
               $out = ' ';
               if (!empty($data['raw']["ITEM_$num"])) {
                  $out=PluginMreportingConfig::getLabelTypeName($data['raw']["ITEM_$num"]);
               }
               return $out;
               break;
            case "glpi_plugin_mreporting_configs.name":
               $out = ' ';
               if (!empty($data['raw']["ITEM_$num"])) {
                  $title_func = '';
                  $short_classname = '';
                  $f_name = '';

                  $inc_dir = Plugin::getPhpDir('mreporting')."/inc";
                  //parse inc dir to search report classes
                  $classes = PluginMreportingCommon::parseAllClasses($inc_dir);

                  foreach ($classes as $classname) {
                     if (!class_exists($classname)) {
                        continue;
                     }
                     $functions = get_class_methods($classname);

                     foreach ($functions as $funct_name) {
                        $ex_func = preg_split('/(?<=\\w)(?=[A-Z])/', $funct_name);
                        if ($ex_func[0] != 'report') {
                           continue;
                        }

                        $gtype = strtolower($ex_func[1]);

                        if ($data['raw']["ITEM_$num"] == $funct_name) {
                           if (!empty($classname) && !empty($funct_name)) {
                              $short_classname = str_replace('PluginMreporting', '', $classname);
                              if (isset($LANG['plugin_mreporting'][$short_classname][$funct_name]['title'])) {
                                 $title_func = $LANG['plugin_mreporting'][$short_classname][$funct_name]['title'];
                              }
                           }
                        }
                     }
                  }
                  $out="<a href='config.form.php?id=".$data["id"]."'>".
                        $data['raw']["ITEM_$num"]."</a> (".$title_func.")";
               }
               return $out;
               break;
         }
         return "";
         break;

   }
   return "";
}

function plugin_mreporting_MassiveActionsFieldsDisplay($options = []) {

   $table = $options['options']['table'];
   $field = $options['options']['field'];
   $linkfield = $options['options']['linkfield'];
   if ($table == getTableForItemType($options['itemtype'])) {

      // Table fields
      switch ($table.".".$field) {

         case "glpi_plugin_mreporting_configs.show_label":
            PluginMreportingConfig::dropdownLabel('show_label');
            return true;
            break;

         case "glpi_plugin_mreporting_configs.graphtype":
            Dropdown::showFromArray("graphtype",
               ['PNG'=>'PNG', 'SVG'=>'SVG']);
            return true;
            break;
      }

   }
   // Need to return false on non display item
   return false;
}


function plugin_mreporting_searchOptionsValues($options = []) {

   $table = $options['searchoption']['table'];
   $field = $options['searchoption']['field'];

   switch ($table.".".$field) {
      case "glpi_plugin_mreporting_configs.graphtype":
         Dropdown::showFromArray("graphtype",
            ['PNG'=>'PNG', 'SVG'=>'SVG']);
         return true;
   }
   return false;
}
