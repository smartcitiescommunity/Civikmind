<?php
/*
* @version $Id: HEADER 14684 2011-06-11 06:32:40Z remi $
LICENSE

This file is part of the purgelogs plugin.

Purgelogs plugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Purgelogs plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with datainjection. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
 @package   purgelogs
 @author    TECLIB
 @copyright Copyright (c) 2009-2017 purgelogs plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/purgelogs
 @link      http://www.glpi-project.org/
 @link      http://www.teclib-edition.com/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginPurgelogsPurge extends CommonDBTM {

   static function cronPurgeLogs($task) {
      $config = new PluginPurgelogsConfig();
      $config->getFromDB(1);
      $logs_before = self::getLogsCount();
      if (self::canLaunchPurge() && $logs_before) {
         self::purgeSoftware($config);
         self::purgeInfocom($config);
         self::purgeUserInfos($config);
         self::purgeWebserviceslogs($config);
         self::purgeOcsInfos($config);
         self::purgeDevices($config);
         self::purgeRelations($config);
         self::purgeItems($config);
         self::purgeOthers($config);
         self::purgeGenericobject($config);
         self::purgeAll($config);
         $logs_after = self::getLogsCount();
         $task->addVolume($logs_before - $logs_after);
      } else {
         $task->addVolume(0);
      }
      return true;
   }

   static function cronInfo($name) {
      return array('description' => __("Purge history", "purgelogs"));
   }

   static function purgeSoftware($config) {
      global $DB;
      $month = self::getDateModRestriction($config->fields['purge_computer_software_install']);
      if ($month) {
         $query = "DELETE FROM `glpi_logs`
                   WHERE `itemtype` = 'Computer'
                     AND `linked_action` IN (4,5)
                     $month";
         $DB->query($query);
      }

      $month = self::getDateModRestriction($config->fields['purge_software_computer_install']);
      if ($month) {
         $query = "DELETE FROM `glpi_logs`
                   WHERE `itemtype` = 'SoftwareVersion'
                     AND `linked_action` IN (4,5)
                     $month";
         $DB->query($query);
      }

      $month = self::getDateModRestriction($config->fields['purge_software_version_install']);
      if ($month) {
         //Delete software version association
         $query = "DELETE FROM `glpi_logs`
                   WHERE `itemtype`='Software'
                      AND `itemtype_link`='SoftwareVersion' $month
                         AND `linked_action` IN (17, 18, 19)";
         $DB->query($query);
      }
   }

   static function purgeInfocom($config) {
      global $DB;

      $month = self::getDateModRestriction($config->fields['purge_infocom_creation']);
      if ($month) {
         //Delete add infocom
         $query = "DELETE FROM `glpi_logs`
                   WHERE (`itemtype`='Software'
                          AND `itemtype_link`='Infocom'
                          AND `linked_action` = 17

                          OR `itemtype` = 'Infocom'
                          AND `linked_action` = 20
                         )
                     $month";
         $DB->query($query);
      }
   }

   static function purgeUserinfos($config) {
      global $DB;

      $month = self::getDateModRestriction($config->fields['purge_profile_user']);
      if ($month) {
         //Delete software version association
         $query = "DELETE FROM `glpi_logs`
                   WHERE `itemtype`='User'
                      AND `itemtype_link`='Profile_User'
                         $month
                            AND `linked_action` IN (17, 18, 19)";
         $DB->query($query);
      }

      $month = self::getDateModRestriction($config->fields['purge_group_user']);
      if ($month) {
         //Delete software version association
         $query = "DELETE FROM `glpi_logs`
                   WHERE `itemtype`='User'
                      AND `itemtype_link`='Group_User'
                         $month
                            AND `linked_action` IN (17, 18, 19)";
         $DB->query($query);
      }

      $month = self::getDateModRestriction($config->fields['purge_userdeletedfromldap']);
      if ($month) {
         //Delete software version association
         $query = "DELETE FROM `glpi_logs`
                   WHERE `itemtype`='User' AND `linked_action` IN (12) $month";
         $DB->query($query);
      }

      $month = self::getDateModRestriction($config->fields['purge_user_auth_changes']);
      if ($month) {
         //Delete software version association
         $query = "DELETE FROM `glpi_logs`
                   WHERE `itemtype`='User' AND `linked_action` IN (15) $month";
         $DB->query($query);
      }

   }

   static function purgeWebserviceslogs($config) {
      global $DB;
      $month = self::getDateModRestriction($config->fields['purge_webservices_logs']);
      if ($month) {
         //Delete software version association
         $query = "DELETE FROM `glpi_logs`
                   WHERE `itemtype`='PluginWebservicesClient' $month";
         $DB->query($query);
      }
   }

   static function purgeOcsInfos($config) {
      global $DB;
      foreach (array(10 => 'ocsid_changes', 8 => 'ocsimport', 9 => 'ocsdelete',
                       11 => 'ocslink') as $key => $value) {
         $month = self::getDateModRestriction($config->fields['purge_'.$value]);
         if ($month) {
            //Delete software version association
            $query = "DELETE FROM `glpi_logs`
            WHERE `linked_action`='$key' $month";
            $DB->query($query);
         }
      }
   }

   static function purgeDevices($config) {
      global $DB;
      foreach (array(1 => "adddevice", 2 => "updatedevice", 3 => "deletedevice",
                       7 => "connectdevice", 6 => "disconnectdevice") as $key => $value) {
         $month = self::getDateModRestriction($config->fields['purge_'.$value]);
         if ($month) {
            //Delete software version association
            $query = "DELETE FROM `glpi_logs`
                      WHERE `linked_action`='$key' $month";
            $DB->query($query);
         }

      }
   }

   static function purgeRelations($config) {
      global $DB;

      foreach (array(15 => "addrelation", 16 => "deleterelation") as $key => $value) {
         $month = self::getDateModRestriction($config->fields['purge_'.$value]);
         if ($month) {
            //Delete software version association
            $query = "DELETE FROM `glpi_logs`
                      WHERE `linked_action`='$key' $month";
            $DB->query($query);
         }
      }
   }

   static function purgeItems($config) {
      global $DB;

      foreach (array(20 => "createitem", 17 => "createitem",
                       13 => "deleteitem", 19 => "deleteitem",
                       18 => "updateitem", 14 => "restoreitem") as $key => $value) {
         $month = self::getDateModRestriction($config->fields['purge_'.$value]);
         if ($month) {
            //Delete software version association
            $query = "DELETE FROM `glpi_logs`
                      WHERE `linked_action`='$key' $month";
            $DB->query($query);
         }
      }

   }

   static function purgeOthers($config) {
      global $DB;
      foreach (array(16 => 'comments', 19 => 'datemod') as $key => $value) {
         $month = self::getDateModRestriction($config->fields['purge_'.$value]);
         if ($month) {
            $query = "DELETE FROM `glpi_logs`
            WHERE `id_search_option`='$key' $month";
            $DB->query($query);
         }

      }
   }

   static function purgeGenericobject($config) {
      global $DB;
      $month = self::getDateModRestriction($config->fields['purge_genericobject_unusedtypes']);
      if ($month) {
         $query = "SELECT DISTINCT `itemtype`
                   FROM `glpi_logs`
                   WHERE `itemtype` LIKE '%PluginGenericobject%'
                   GROUP BY `itemtype`";
         $types = array();
         foreach ($DB->request($query) as $type) {
            if (!class_exists($type['itemtype'])) {
               $types[] = "'".$type['itemtype']."'";
            }
         }
         if (!empty($types)) {
            $types_string = implode(',', $types);
            $query = "DELETE FROM `glpi_logs` WHERE `itemtype` IN ($types_string) $month";
            $DB->query($query);
         }
      }
   }

   static function purgeAll($config) {
      global $DB;
      $month = self::getDateModRestriction($config->fields['purge_all']);
      if ($month) {
         $query = "DELETE FROM `glpi_logs`
                   WHERE 1 $month";
         $DB->query($query);
      }
   }

   static function getDateModRestriction($month) {
      if ($month > 0) {
         return "AND `date_mod` <= DATE_ADD(NOW(), INTERVAL -$month MONTH) ";
      } else if ($month == PluginPurgelogsConfig::DELETE_ALL) {
         return "AND 1 ";
      } else if ($month == PluginPurgelogsConfig::KEEP_ALL) {
         return false;
      }
   }

   /**
   * since GLPi 0.90.2
   * Check if there's no crashed tables. If there're some, skip log purge
   */
   static function canLaunchPurge() {
      if (method_exists('DBmysql', 'checkForCrashedTables')) {

         //Check for potential crashed tables
         $crashed_tables = DBmysql::checkForCrashedTables();
         if (empty($crashed_tables)) {
            //No crashed tables, good !
            return true;
         } else {
            //Some crashed tables has been detected : stop cron execution
            Toolbox::logDebug("Cannot launch automatic action : crashed tables detected");
            return false;
         }
      } else {
         //The check function is unavailable (GLPi < 0.90.2)
         return true;
      }
   }

   static function getLogsCount() {
      return countElementsInTable('glpi_logs');
   }

   //----------------- Install & uninstall -------------------//
   static function install(Migration $migration) {
      $cron = new CronTask;
      if (!$cron->getFromDBbyName(__CLASS__, 'purgeLogs')) {
         CronTask::Register(__CLASS__, 'purgeLogs', 7 * DAY_TIMESTAMP,
                            ['param' => 24, 'mode' => CronTask::MODE_EXTERNAL]);
      }
   }

   static function uninstall() {
      CronTask::Unregister(__CLASS__);
   }
}
