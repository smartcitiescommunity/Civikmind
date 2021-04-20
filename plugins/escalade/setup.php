<?php
/*
 -------------------------------------------------------------------------
 Escalade plugin for GLPI
 Copyright (C) 2016-207 by the Escalade Development Team.

 https://github.com/pluginsGLPI/escalade
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Escalade.

 Escalade is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Escalade is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Escalade. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define ('PLUGIN_ESCALADE_VERSION', '2.6.2');

// Minimal GLPI version, inclusive
define("PLUGIN_ESCALADE_MIN_GLPI", "9.5");
// Maximum GLPI version, exclusive
define("PLUGIN_ESCALADE_MAX_GLPI", "9.6");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_escalade() {
   global $PLUGIN_HOOKS, $DB;

   $PLUGIN_HOOKS['csrf_compliant']['escalade'] = true;

   $plugin = new Plugin();
   if ((isset($_SESSION['glpiID']) || isCommandLine())
      && $plugin->isInstalled('escalade')
      && $plugin->isActivated('escalade')) {

      //load config in session
      if ($DB->tableExists("glpi_plugin_escalade_configs")) {
         PluginEscaladeConfig::loadInSession();

         // == Load js scripts ==
         if (isset($_SESSION['plugins']['escalade']['config'])) {
            $escalade_config = $_SESSION['plugins']['escalade']['config'];

            $PLUGIN_HOOKS['add_javascript']['escalade'][] = 'js/function.js';

            // on central page
            if (strpos($_SERVER['REQUEST_URI'], "central.php") !== false) {
               //history and climb feature
               if ($escalade_config['show_history']) {
                  $PLUGIN_HOOKS['add_javascript']['escalade'][] = 'js/central.js.php';
               }
            }

            // on ticket page (in edition)
            if ((strpos($_SERVER['REQUEST_URI'], "ticket.form.php") !== false
               || strpos($_SERVER['REQUEST_URI'], "problem.form.php") !== false
               || strpos($_SERVER['REQUEST_URI'], "change.form.php") !== false) && isset($_GET['id'])) {

               if (!$escalade_config['remove_delete_requester_user_btn']
                  || !$escalade_config['remove_delete_watcher_user_btn']
                  || !$escalade_config['remove_delete_assign_user_btn']
                  || !$escalade_config['remove_delete_requester_group_btn']
                  || !$escalade_config['remove_delete_watcher_group_btn']
                  || !$escalade_config['remove_delete_assign_group_btn']
                  || !$escalade_config['remove_delete_assign_supplier_btn']) {
                  //remove btn feature
                  $PLUGIN_HOOKS['add_javascript']['escalade'][] = 'js/remove_btn.js.php';
               }
            }

            // on ticket page (in edition)
            if (strpos($_SERVER['REQUEST_URI'], "ticket.form.php") !== false
                && isset($_GET['id'])) {

               //history and climb feature
               if ($escalade_config['show_history']) {
                  $PLUGIN_HOOKS['add_javascript']['escalade'][] = 'js/escalade.js.php';
               }

               //clone ticket feature
               if ($escalade_config['cloneandlink_ticket']) {
                  $PLUGIN_HOOKS['add_javascript']['escalade'][] = 'js/cloneandlink_ticket.js.php';
               }

               //filter group feature
               if ($escalade_config['use_filter_assign_group']) {
                  $PLUGIN_HOOKS['add_javascript']['escalade'][] = 'js/filtergroup.js.php';
               }
            }

            Plugin::registerClass('PluginEscaladeGroup_Group', ['addtabon' => 'Group']);
            Plugin::registerClass('PluginEscaladeUser', ['addtabon' => ['User']]);
         }
      }

      $PLUGIN_HOOKS['add_css']['escalade'][]= 'css/escalade.css';

      // == Ticket modifications
      $PLUGIN_HOOKS['item_update']['escalade']= [
         'Ticket'       => 'plugin_escalade_item_update',
      ];
      $PLUGIN_HOOKS['item_add']['escalade'] = [
         'Group_Ticket' => 'plugin_escalade_item_add_group_ticket',
         'Ticket_User'  => 'plugin_escalade_item_add_user',
         'Ticket'       => 'plugin_escalade_item_add_ticket',
      ];
      $PLUGIN_HOOKS['pre_item_add']['escalade'] = [
         'Group_Ticket' => 'plugin_escalade_pre_item_add_group_ticket',
         'Ticket'       => 'plugin_escalade_pre_item_add_ticket',
      ];
      $PLUGIN_HOOKS['post_prepareadd']['escalade'] = [
         'Ticket'       => 'plugin_escalade_post_prepareadd_ticket',
      ];

      $PLUGIN_HOOKS['item_purge']['escalade']= [
         'User'         => 'plugin_escalade_item_purge',
         'Ticket'       => 'plugin_escalade_item_purge',
      ];
      $PLUGIN_HOOKS['item_add']['escalade']['User'] = 'plugin_escalade_item_add_user';

      // == Interface links ==
      if (Session::haveRight('config', UPDATE)) {
         $PLUGIN_HOOKS['config_page']['escalade'] = 'front/config.form.php';
      }

      $PLUGIN_HOOKS['use_massive_action']['escalade'] = 1;

      // add more target to some notifications
      $PLUGIN_HOOKS['item_add_targets']['escalade']['NotificationTargetPlanningRecall']
         = ['PluginEscaladeNotification', 'addTargets'];
      $PLUGIN_HOOKS['item_action_targets']['escalade']['NotificationTargetPlanningRecall']
         = ['PluginEscaladeNotification', 'getActionTargets'];

      // Add additional events for Ticket notifications
      $PLUGIN_HOOKS['item_get_events']['escalade'] = [
         'NotificationTargetTicket' =>  ['PluginEscaladeNotification', 'getEvents']
      ];
   }
}

/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_escalade() {
   return [
      'name'           => __("Escalation", "escalade"),
      'version'        => PLUGIN_ESCALADE_VERSION,
      'author'         => "<a href='http://www.teclib.com'>Teclib'</a>",
      'homepage'       => "https://github.com/pluginsGLPI/escalade",
      'license'        => 'GPLv2+',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_ESCALADE_MIN_GLPI,
            'max' => PLUGIN_ESCALADE_MAX_GLPI,
         ]
      ]
   ];
}

