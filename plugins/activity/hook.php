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

function plugin_activity_install() {
   global $DB;

   include_once (GLPI_ROOT . "/plugins/activity/inc/profile.class.php");

   $install = false;
   $update200 = false;

   // version 3.0.0
   if (!$DB->tableExists("glpi_plugin_activity_activities")) {
      $install  = true;
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/empty-3.0.0.sql");
   }

   if (!$DB->tableExists('glpi_plugin_activity_holidays')) {
      $DB->runFile(GLPI_ROOT."/plugins/activity/install/sql/update-2.0.0.sql");
      $update200 = true;
   }

   //TODO Update ?
   //TODO update tech_num & realtime (to `actiontime` int(11) NOT NULL DEFAULT '0') & use_planning -> is_planned (tinyint(1) NOT NULL DEFAULT '0',)

   if ($install || $update200) {

      $query_id = "SELECT `id` FROM `glpi_notificationtemplates` WHERE `itemtype`='PluginActivityHoliday' AND `name` = 'Holidays validation'";
      $result = $DB->query($query_id) or die ($DB->error());
      $itemtype = $DB->result($result, 0, 'id');

      $query="INSERT INTO `glpi_notificationtemplatetranslations`
                                 VALUES(NULL, ".$itemtype.", '','##lang.activity.title##',
'##lang.activity.title##
##lang.activity.url## : ##activity.url##
##lang.holiday.status## : ##holiday.status##
##lang.holiday.applicant.name## : ##holiday.applicant.name##
##lang.holiday.name## : ##holiday.name##
##lang.holiday.begin.date## : ##holiday.begin.date##
##lang.holiday.end.date## : ##holiday.end.date##
##lang.holiday.nbdays## : ##holiday.nbdays##
##lang.holiday.date.submission## : ##holiday.date.submission##
##IFlang.holiday.date.validation####lang.holiday.date.validation## : ##holiday.date.validation####ENDIFlang.holiday.date.validation##
##IFlang.holiday.commentvalidation####lang.holiday.commentvalidation## : ##holiday.commentvalidation####endiflang.holiday.commentvalidation##
##lang.holiday.holidaytype## : ##holiday.holidaytype##
##lang.activity.url## : ##activity.url##',
      '&lt;strong&gt;##lang.activity.title## &lt;/strong&gt;&lt;ul&gt;&lt;li&gt;&lt;strong&gt;##lang.holiday.status## :&lt;/strong&gt; ##holiday.status##&lt;/li&gt;&lt;li&gt;&lt;strong&gt;##lang.activity.url## :&lt;/strong&gt; ##activity.url##&lt;/li&gt;&lt;li&gt;&lt;strong&gt;##lang.holiday.applicant.name## :&lt;/strong&gt; ##holiday.applicant.name##&lt;/li&gt;&lt;li&gt;&lt;strong&gt;##lang.holiday.begin.date## :&lt;/strong&gt; ##holiday.begin.date##&lt;/li&gt;&lt;li&gt;&lt;strong&gt;##lang.holiday.end.date## :&lt;/strong&gt; ##holiday.end.date##&lt;/li&gt;&lt;li&gt;&lt;strong&gt;##lang.holiday.nbdays## :&lt;/strong&gt; ##holiday.nbdays##&lt;/li&gt;&lt;li&gt;&lt;strong&gt;##lang.holiday.date.submission## :&lt;/strong&gt; ##holiday.date.submission##&lt;/li&gt;##IFlang.holiday.date.validation##&lt;li&gt;&lt;strong&gt;##lang.holiday.date.validation## :&lt;/strong&gt; ##holiday.date.validation##&lt;/li&gt;##ENDIFlang.holiday.date.validation####IFlang.holiday.commentvalidation##&lt;li&gt;&lt;strong&gt;##lang.holiday.commentvalidation## :&lt;/strong&gt; ##holiday.commentvalidation##&lt;/li&gt;##ENDIFlang.holiday.commentvalidation##&lt;li&gt;&lt;strong&gt;##lang.holiday.holidaytype## :&lt;/strong&gt; ##holiday.holidaytype##&lt;/li&gt;&lt;/ul&gt;');";

      $DB->query($query);

      $query = "INSERT INTO `glpi_notifications` (`name`, `entities_id`, `itemtype`, `event`, `is_recursive`, `is_active`) 
                   VALUES ('New validation', 0, 'PluginActivityHoliday', 'newvalidation', 1, 1);";
      $DB->query($query);

      $query_id = "SELECT `id` FROM `glpi_notifications`
               WHERE `name` = 'New validation' AND `itemtype` = 'PluginActivityHoliday' AND `event` = 'newvalidation'";
      $result = $DB->query($query_id) or die ($DB->error());
      $notification = $DB->result($result, 0, 'id');

      $query = "INSERT INTO `glpi_notifications_notificationtemplates` (`notifications_id`, `mode`, `notificationtemplates_id`) 
               VALUES (" . $notification . ", 'mailing', " . $itemtype . ");";
      $DB->query($query);

      $query = "INSERT INTO `glpi_notifications` (`name`, `entities_id`, `itemtype`, `event`, `is_recursive`, `is_active`) 
                   VALUES ('Answer validation', 0, 'PluginActivityHoliday', 'answervalidation', 1, 1);";
      $DB->query($query);

      $query_id = "SELECT `id` FROM `glpi_notifications`
               WHERE `name` = 'Answer validation' AND `itemtype` = 'PluginActivityHoliday' AND `event` = 'answervalidation'";
      $result = $DB->query($query_id) or die ($DB->error());
      $notification = $DB->result($result, 0, 'id');

      $query = "INSERT INTO `glpi_notifications_notificationtemplates` (`notifications_id`, `mode`, `notificationtemplates_id`) 
               VALUES (" . $notification . ", 'mailing', " . $itemtype . ");";
      $DB->query($query);
   }

   if (!$DB->tableExists("glpi_plugin_activity_snapshots")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.0.1.sql");
   }
   if (!$DB->fieldExists("glpi_plugin_activity_holidaytypes", "is_holiday")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.0.2.sql");
   }
   if (!$DB->fieldExists("glpi_plugin_activity_holidaytypes", "auto_validated")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.0.3.sql");
   }
   if (!$DB->fieldExists("glpi_plugin_activity_holidays", "date_mod")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.0.4.sql");
   }
   if (!$DB->fieldExists("glpi_plugin_activity_options", "use_groupmanager")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.1.4.sql");
   }
   if (!$DB->tableExists('glpi_plugin_activity_holidaycounts')) {
      $DB->runFile(GLPI_ROOT."/plugins/activity/install/sql/update-2.2.1.sql");
   }
   if (!$DB->fieldExists("glpi_plugin_activity_options", "use_type_as_name")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.2.2.sql");
   }

   //version 2.2.4
   if (!$DB->fieldExists("glpi_plugin_activity_holidaytypes", "is_holiday_counter")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.2.4.sql");

      include(GLPI_ROOT."/plugins/activity/install/update_223_224.php");
      update223to224();
   }
   if (!$DB->fieldExists("glpi_plugin_activity_holidayperiods", "archived")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.2.5.sql");
   }
   //version 2.2.6
   if (!$DB->fieldExists("glpi_plugin_activity_options", "is_cra_default")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.2.6.sql");
   }
   //version 2.2.7
   if (!$DB->fieldExists("glpi_plugin_activity_options", "use_mandaydisplay")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.2.7.sql");
   }

   //version 2.3.0
   if (!$DB->fieldExists("glpi_plugin_activity_options", "use_project")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.3.0.sql");
   }
   
   //version 2.5.1
   if (!$DB->fieldExists("glpi_plugin_activity_options", "use_weekend")) {
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-2.5.1.sql");
   }

   //version 3.0.0
   if ($DB->tableExists("glpi_planningexternalevents") && $DB->tableExists('glpi_plugin_activity_activities')) {
      include(GLPI_ROOT."/plugins/activity/install/update_251_300.php");
      $DB->runFile(GLPI_ROOT . "/plugins/activity/install/sql/update-3.0.0.sql");
      update251to300();
   }

   PluginActivityProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   PluginActivityProfile::initProfile();
   $DB->query("DROP TABLE IF EXISTS `glpi_plugin_activity_profiles`;");

   $migration = new Migration("2.0.0");
   $migration->dropTable('glpi_plugin_activity_profiles');

   return true;
}

// Uninstall process for plugin : need to return true if succeeded
function plugin_activity_uninstall() {
   global $DB;

   include_once (GLPI_ROOT."/plugins/activity/inc/profile.class.php");
   include_once (GLPI_ROOT."/plugins/activity/inc/menu.class.php");

   // Plugin tables deletion
   $tables = ["glpi_plugin_activity_holidaytypes",
                    "glpi_plugin_activity_holidays",
                    "glpi_plugin_activity_configs",
                    "glpi_plugin_activity_tickettasks",
                    "glpi_plugin_activity_options",
                    "glpi_plugin_activity_preferences",
                    "glpi_plugin_activity_holidayvalidations",
                    "glpi_plugin_activity_holidaycounts",
                    "glpi_plugin_activity_holidayperiods",
                    "glpi_plugin_activity_snapshots",
                    "glpi_plugin_activity_projecttasks",
                    "glpi_plugin_activity_planningexternalevents"];

   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   $tables_glpi = ["glpi_displaypreferences",
                        "glpi_savedsearches",
                        "glpi_logs",
                        "glpi_dropdowntranslations"];

   foreach ($tables_glpi as $table_glpi) {
      $DB->query("DELETE
                  FROM `$table_glpi`
                  WHERE `itemtype` LIKE 'PluginActivity%'");
   }

   // Delete notifications
   $notif   = new Notification();
   $options = ['itemtype' => 'PluginActivityHoliday',
                    'event'    => 'newvalidation',
                    'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }

   $options = ['itemtype' => 'PluginActivityHoliday',
                    'event'    => 'answervalidation',
                    'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notifications', $options) as $data) {
      $notif->delete($data);
   }

   //templates
   $template = new NotificationTemplate();
   $translation = new NotificationTemplateTranslation();
   $notif_template = new Notification_NotificationTemplate();
   $options = ['itemtype' => 'PluginActivityHoliday',
                    'FIELDS'   => 'id'];
   foreach ($DB->request('glpi_notificationtemplates', $options) as $data) {
      $options_template = ['notificationtemplates_id' => $data['id'],
                    'FIELDS'   => 'id'];

      foreach ($DB->request('glpi_notificationtemplatetranslations', $options_template) as $data_template) {
         $translation->delete($data_template);
      }
      $template->delete($data);

      foreach ($DB->request('glpi_notifications_notificationtemplates', $options_template) as $data_template) {
         $notif_template->delete($data_template);
      }
   }

   //Delete rights associated with the plugin
   $profileRight = new ProfileRight();
   foreach (PluginActivityProfile::getAllRights() as $right) {
      $profileRight->deleteByCriteria(['name' => $right['field']]);
   }
   PluginActivityProfile::removeRightsFromSession();
   PluginActivityProfile::removeRightsFromDB();

   PluginActivityMenu::removeRightsFromSession();

   return true;
}
//TODO add relation between holiday & holiday validation ?
// Define database relations
function plugin_activity_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("activity")) {
      return ["glpi_tickettasks" => ["glpi_plugin_activity_tickettasks" => "tickettasks_id"],
                  "glpi_planningexternalevents" => ["glpi_plugin_activity_planningexternalevents" => "planningexternalevents_id"],
                     "glpi_plugin_activity_holidaytypes" => ["glpi_plugin_activity_holidays"    => "plugin_activity_holidaytypes_id",
                                                                  "glpi_plugin_activity_holidaycounts"    => "plugin_activity_holidaytypes_id"],
                           "glpi_plugin_activity_holidayperiods" => ["glpi_plugin_activity_holidaycounts"   => "plugin_activity_holidayperiods_id"]];
   }
   return  [];
}

// Define Dropdown tables to be manage in GLPI
function plugin_activity_getDropdown() {

   $plugin = new Plugin();

   if ($plugin->isActivated("activity")) {
      return ['PluginActivityHolidayType' => PluginActivityHolidayType::getTypeName(2),
                 'PluginActivityHolidayPeriod' => PluginActivityHolidayPeriod::getTypeName(2)];
   } else {
      return [];
   }
}


function plugin_activity_postinit() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['item_purge']['activity']['TicketTask']
         = ['PluginActivityTicketTask','cleanForItem'];

   $PLUGIN_HOOKS['item_purge']['activity']['User']
         = ['PluginActivityActivity','cleanForUser'];

}

function plugin_activity_addDefaultWhere($type) {

   switch ($type) {
/*      case "PluginActivityActivity" :
         $who = Session::getLoginUserID();
         if (!Session::haveRight("plugin_activity_all_users", 1)) {
            return " `glpi_plugin_activity_activities`.`users_id` = '$who' ";
         }
         break;*/

      case "PluginActivityHoliday" :
         $who = Session::getLoginUserID();
         if (!Session::haveRight("plugin_activity_all_users", 1)) {
            return " `glpi_plugin_activity_holidays`.`users_id` = '$who' ";
         }
         break;
      case "PluginActivityHolidaycount" :
         $who = Session::getLoginUserID();
         return " `glpi_plugin_activity_holidaycounts`.`users_id` = '$who' ";
         break;

   }
   return "";
}


function plugin_activity_addWhere($link, $nott, $itemtype, $ID, $val, $searchtype) {

   switch ($itemtype) {
      case "PluginActivityHoliday" :
         $searchoptions = Search::getOptions($itemtype);
         if ($searchoptions[$ID]['table'] == 'glpi_plugin_activity_holidayvalidations') {
            $who = Session::getLoginUserID();
            if ($nott) {
               $nott = 'NOT';
            } else {
               $nott = '';
            }
            return " $link $nott `glpi_plugin_activity_holidayvalidations`.`users_id_validate` = '$val' ";
         }
   }
   return "";
}
