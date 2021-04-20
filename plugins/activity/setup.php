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

define('PLUGIN_ACTIVITY_VERSION', '3.0.0');

// Init the hooks of the plugins -Needed
function plugin_init_activity() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['activity'] = true;
   $PLUGIN_HOOKS['change_profile']['activity'] = ['PluginActivityProfile', 'initProfile'];
   if (isset($_SESSION["glpiactiveprofile"]["interface"])
       && $_SESSION["glpiactiveprofile"]["interface"] != "helpdesk") {
      $PLUGIN_HOOKS['add_css']['activity']      = ['activity.css'];
      $PLUGIN_HOOKS['javascript']['activity'][] = '/plugins/activity/lib/sdashboard/lib/flotr2/flotr2.js';
      $PLUGIN_HOOKS['add_javascript']['activity'] = ['/lib/jquery/js/jquery.ui.touch-punch.min.js'];

   }

   // Lateral menu
   if (Session::haveRight("plugin_activity", UPDATE)
       || Session::haveRight("plugin_activity_can_requestholiday", 1)
       || Session::haveRight("plugin_activity_can_validate", 1)
       || Session::haveRight("plugin_activity_all_users", 1)) {

      $PLUGIN_HOOKS['add_javascript']['activity'] = ['scripts/scripts-activityholidays.js',
                                                     'scripts/activity_load_scripts.js'];
      $PLUGIN_HOOKS['javascript']['activity']     = ["/plugins/activity/scripts/scripts-activitydate.js",
                                                     "/plugins/activity/scripts/scripts-activityholidays.js",
                                                     "/plugins/activity/scripts/activity_load_scripts.js"];
   }

   if (Session::haveRight("plugin_activity_statistics", 1)) {
      /* Show Stats in standard stats page */
      if (class_exists('PluginActivityStats')) {
         $common = new PluginActivityStats();
         $stats  = $common->getAllStats();

         if ($stats !== false) {
            foreach ($stats as $stat) {
               foreach ($stat['funct'] as $func) {
                  $PLUGIN_HOOKS['stats']['activity'] = ['front/stats.php?stat_id=' . $func['id'] => $func['title']];
               }
            }
         }
      }
   }
   $PLUGIN_HOOKS['post_init']['activity'] = 'plugin_activity_postinit';

   $plugin = new Plugin();

   if ($plugin->isActivated("activity")) {

      Plugin::registerClass('PluginActivityProfile', ['addtabon' => 'Profile']);
      Plugin::registerClass('PluginActivityPublicHoliday', ['planning_types' => true]);

      $opt = new PluginActivityOption();
      $opt->getFromDB(1);

      if ($plugin->isActivated("manageentities")
          || $opt->fields['use_timerepartition']) {
         unset($CFG_GLPI['planning_types'][3]);
         unset($_SESSION['glpi_plannings']['filters']['TicketTask']);
      }

      Plugin::registerClass('PluginActivityHoliday', ['planning_types'              => true,
                                                      'notificationtemplates_types' => true]);
      Plugin::registerClass('PluginActivityHolidayValidation', []);

      if (Session::getLoginUserID()) {
         if (Session::haveRight("plugin_activity", READ)
             || Session::haveRight("plugin_activity_can_requestholiday", 1)
             || Session::haveRight("plugin_activity_can_validate", 1)) {

            Plugin::registerClass('PluginActivityPreference',
                                  ['addtabon' => 'Preference']);

            if (Session::haveRight('plugin_activity', READ)) {
               $PLUGIN_HOOKS["menu_toadd"]['activity']          = ['tools' => 'PluginActivityMenu'];
               $PLUGIN_HOOKS['helpdesk_menu_entry']['activity'] = '/front/menu.php';
            }

            $PLUGIN_HOOKS['redirect_page']['activity'] = 'front/holiday.form.php';
         }
         if (class_exists('PluginMydashboardMenu')) {
            $PLUGIN_HOOKS['mydashboard']['activity'] = ["PluginActivityDashboard"];
         }

         if (Session::haveRight("plugin_activity", UPDATE) && class_exists('PluginActivityProfile')) {

            $PLUGIN_HOOKS['config_page']['activity']        = 'front/config.form.php';
            $PLUGIN_HOOKS['use_massive_action']['activity'] = false;
         }

         $PLUGIN_HOOKS['pre_item_add']['activity'] = ['Ticket_User' => ['PluginActivityTicket',
                                                                        'afterAddUser']];

         $PLUGIN_HOOKS['item_add']['activity']     = ['TicketTask' => ['PluginActivityTicketTask',
                                                                       'taskAdd'],
                                                      'PlanningExternalEvent' => ['PluginActivityPlanningExternalEvent',
                                                                        'activityAdd']];

         $PLUGIN_HOOKS['pre_item_update']['activity'] = ['TicketTask' => ['PluginActivityTicketTask',
                                                                          'taskUpdate'],
                                                         'PlanningExternalEvent'  => ['PluginActivityPlanningExternalEvent',
                                                            'activityUpdate']];

         $PLUGIN_HOOKS['post_prepareadd']['activity'] = ['PlanningExternalEvent' => ['PluginActivityPlanningExternalEvent',
               'prepareInputToAddWithPluginOptions']];


         $PLUGIN_HOOKS['pre_item_purge']['activity'] = ['Document' => ['PluginActivitySnapshot',
                                                                       'purgeSnapshots']];

         if ($opt->getUseProject() && strpos($_SERVER['REQUEST_URI'],"projecttask")) {
            $PLUGIN_HOOKS['item_add']['activity']       = ['ProjectTask' => ['PluginActivityProjectTask',
                                                                             'taskAdd']];

            $PLUGIN_HOOKS['pre_item_update']['activity'] = ['ProjectTask' => ['PluginActivityProjectTask',
                                                                              'taskUpdate']];
            $PLUGIN_HOOKS['post_item_form']['activity'] = ['PluginActivityProjectTask', 'addField'];
         }

      }

      if ( Session::haveRight("plugin_activity", READ) && strpos($_SERVER['REQUEST_URI'],"planningexternalevent")
               || isset($_POST['action']) && $_POST['action'] == 'add_event_fromselect') {
         $PLUGIN_HOOKS['post_item_form']['activity'] = ['PluginActivityPlanningExternalEvent', 'addCra'];
      }

      // Ticket task cra
      if (Session::haveRight("plugin_activity", READ) && strpos($_SERVER['REQUEST_URI'],"TicketTask")
               || strpos($_SERVER['REQUEST_URI'],"timeline")) {
         $PLUGIN_HOOKS['post_item_form']['activity'] = ['PluginActivityTicketTask', 'postForm'];
      }

   }
   //Planning hook
   $PLUGIN_HOOKS['display_planning']['activity']  = ['PluginActivityPublicHoliday' => "displayPlanningItem",
                                                     'PluginActivityHoliday'       => "displayPlanningItem"];
   $PLUGIN_HOOKS['planning_populate']['activity'] = ['PluginActivityPublicHoliday' => "populatePlanning",
                                                     'PluginActivityHoliday'       => "populatePlanning"];
}

// Get the name and the version of the plugin - Needed
function plugin_version_activity() {

   return [
      'name'           => _n('Activity', 'Activities', 2, 'activity'),
      'version'        => PLUGIN_ACTIVITY_VERSION,
      'author'         => "Xavier Caillaud / Infotel",
      'license'        => 'GPLv2+',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => '9.5',
            'dev' => false
         ]
      ]];
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_activity_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.5', 'lt')
         || version_compare(GLPI_VERSION, '9.6', 'ge')) {
      if (method_exists('Plugin', 'messageIncompatible')) {
         echo Plugin::messageIncompatible('core', '9.5');
      }
      return false;
   }

   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_activity_check_config() {
   return true;
}