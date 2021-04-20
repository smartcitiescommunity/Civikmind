<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Tasklists plugin for GLPI
 Copyright (C) 2003-2016 by the Tasklists Development Team.

 https://github.com/InfotelGLPI/tasklists
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Tasklists.

 Tasklists is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Tasklists is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Tasklists. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_TASKLISTS_VERSION', '1.6.2');

// Init the hooks of the plugins -Needed
function plugin_init_tasklists() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['tasklists'] = true;
   $PLUGIN_HOOKS['change_profile']['tasklists'] = ['PluginTasklistsProfile', 'initProfile'];
   $PLUGIN_HOOKS['use_rules']['tasklists'] = ['RuleMailCollector'];

   if (Session::getLoginUserID()) {

      Plugin::registerClass('PluginTasklistsTask', [
         'linkuser_types'              => true,
         'linkgroup_types'             => true,
         'document_types'              => true,
         'notificationtemplates_types' => true
      ]);

      Plugin::registerClass('PluginTasklistsTicket',
                            ['addtabon' => 'Ticket']);

      $PLUGIN_HOOKS['item_purge']['tasklists']['Ticket'] = ['PluginTasklistsTaskTicket', 'cleanForTicket'];

      Plugin::registerClass('PluginTasklistsProfile',
                            ['addtabon' => 'Profile']);

      Plugin::registerClass('PluginTasklistsPreference',
                            ['addtabon' => 'Preference']);

      if (Session::haveRight("plugin_tasklists", READ)) {
         $PLUGIN_HOOKS['menu_toadd']['tasklists'] = ['helpdesk' => 'PluginTasklistsMenu'];
      }

      if (class_exists('PluginMydashboardMenu')) {
         $PLUGIN_HOOKS['mydashboard']['tasklists'] = ["PluginTasklistsDashboard"];
      }

      if (Session::haveRight("plugin_tasklists", CREATE)) {
         $PLUGIN_HOOKS['use_massive_action']['tasklists'] = 1;
      }
      // require spectrum (for glpi >= 9.2)
//      $CFG_GLPI['javascript']['config']['commondropdown']['PluginTasklistsTaskState'] = ['colorpicker'];
      $PLUGIN_HOOKS['javascript']['tasklists'][]                                      = "/plugins/tasklists/lib/redips/redips-drag-min.js";
      $PLUGIN_HOOKS['javascript']['tasklists'][]                                      = "/plugins/tasklists/scripts/plugin_tasklists_drag-field-row.js";
//      $PLUGIN_HOOKS['javascript']['tasklists'][]                                      = "/plugins/tasklists/lib/kanban/js/kanban.js";
//      $CFG_GLPI['javascript']['helpdesk']['plugintasklistsmenu']                      = ['colorpicker'];


   }
}

// Get the name and the version of the plugin - Needed
/**
 * @return array
 */
function plugin_version_tasklists() {

   return [
      'name'         => __('Tasks list', 'tasklists'),
      'version'      => PLUGIN_TASKLISTS_VERSION,
      'license'      => 'GPLv2+',
      'author'       => "<a href='http://blogglpi.infotel.com'>Infotel</a>",
      'homepage'     => 'https://github.com/InfotelGLPI/tasklists',
      'requirements' => [
         'glpi' => [
            'min' => '9.5',
            'dev' => false
         ]
      ]
   ];

}

// Optional : check prerequisites before install : may print errors or add to message after redirect
/**
 * @return bool
 */
function plugin_tasklists_check_prerequisites() {
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
/**
 * @return bool
 */
function plugin_tasklists_check_config() {
   return true;
}
