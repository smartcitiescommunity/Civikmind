<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_METADEMANDS_VERSION', '2.7.5');

// Init the hooks of the plugins -Needed
function plugin_init_metademands() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['metademands'] = true;
   $PLUGIN_HOOKS['change_profile']['metademands'] = ['PluginMetademandsProfile', 'changeProfile'];
   $PLUGIN_HOOKS['add_javascript']['metademands'] = ['scripts/metademands.js'];
   $PLUGIN_HOOKS["javascript"]['metademands']     = ["/plugins/metademands/scripts/metademands.js"];
   $PLUGIN_HOOKS['add_css']['metademands']        = ['/css/metademands.css'];

   // add minidashboard
   $PLUGIN_HOOKS['dashboard_cards']['metademands'] = ['PluginMetademandsMetademand', 'getMetademandDashboards'];

//   if ((strpos($_SERVER['REQUEST_URI'], "ticket.form.php") !== false)
//       || strpos($_SERVER['REQUEST_URI'], "helpdesk.public.php?create_ticket=1") !== false
//       || strpos($_SERVER['REQUEST_URI'], "tracking.injector.php") !== false) {
//      $PLUGIN_HOOKS['add_javascript']['metademands'][] = 'scripts/metademands_load_scripts.js';
//   }
   $PLUGIN_HOOKS['use_massive_action']['metademands'] = 1;
   $plugin = new Plugin();

   if (Session::getLoginUserID()) {
      Plugin::registerClass('PluginMetademandsMetademand', ['addtabon' => 'Ticket']);
      Plugin::registerClass('PluginMetademandsProfile', ['addtabon' => 'Profile']);
      Plugin::registerClass('PluginMetademandsMetademand_Resource', ['addtabon' => 'PluginResourcesContractType']);

      $PLUGIN_HOOKS['item_show']['metademands']  = ['PluginResourcesResource' =>
                                                       ['PluginMetademandsMetademand_Resource', 'redirectFormForResource']];
      $PLUGIN_HOOKS['item_empty']['metademands'] = ['Ticket' =>
                                                       ['PluginMetademandsTicket', 'emptyTicket']];

      $PLUGIN_HOOKS['pre_item_purge']['metademands'] = ['Profile'                       =>
                                                           ['PluginMetademandsProfile', 'purgeProfiles'],
                                                        'PluginMetademandsMetademand'   => 'plugin_pre_item_purge_metademands',
                                                        'PluginMetademandsTask'         => 'plugin_pre_item_purge_metademands',
                                                        'Group'                         => 'plugin_pre_item_purge_metademands',
                                                        'Ticket'                        => 'plugin_pre_item_purge_metademands',
                                                        'PluginMetademandsField'        => 'plugin_pre_item_purge_metademands',
                                                        'PluginResourcesContractType'   => 'plugin_pre_item_purge_metademands',
                                                        'TicketTemplateMandatoryField'  =>
                                                           ['PluginMetademandsTicketField', 'post_delete_mandatoryField'],
                                                        'TicketTemplatePredefinedField' =>
                                                           ['PluginMetademandsTicketField', 'post_delete_predefinedField']];

      $PLUGIN_HOOKS['item_update']['metademands'] = ['Ticket'       =>
                                                        ['PluginMetademandsTicket', 'post_update_ticket'],
                                                     'ITILCategory' =>
                                                        ['PluginMetademandsTicketField', 'update_category_mandatoryFields'],
                                                     'ITILCategory' =>
                                                        ['PluginMetademandsTicketField', 'update_category_predefinedFields']];

      $PLUGIN_HOOKS['pre_item_update']['metademands'] = ['Ticket' =>
                                                            ['PluginMetademandsTicket', 'pre_update_ticket']];

      $PLUGIN_HOOKS['item_add']['metademands'] = ['TicketTemplateMandatoryField'  =>
                                                     ['PluginMetademandsTicketField', 'post_add_mandatoryField'],
                                                  'TicketTemplatePredefinedField' =>
                                                     ['PluginMetademandsTicketField', 'post_add_predefinedField'],
                                                  'ITILCategory'                  =>
                                                     ['PluginMetademandsTicketField', 'update_category_mandatoryFields'],
                                                  'ITILCategory'                  =>
                                                     ['PluginMetademandsTicketField', 'update_category_predefinedFields'],
                                                  'Ticket'                        =>
                                                     ['PluginMetademandsTicket', 'post_add_ticket']];

      $PLUGIN_HOOKS['pre_item_add']['metademands'] = ['Ticket' =>
                                                         ['PluginMetademandsTicket', 'pre_add_ticket']];

      if (Session::haveRight("plugin_metademands", READ)) {
         $PLUGIN_HOOKS['menu_toadd']['metademands'] = ['helpdesk' => 'PluginMetademandsMetademand'];
      }

      if (Session::haveRight("plugin_metademands", READ)
          && !$plugin->isActivated('servicecatalog')) {
         $PLUGIN_HOOKS['helpdesk_menu_entry']['metademands'] = '/front/wizard.form.php';
      }

      if (Session::haveRight("config", UPDATE)) {
         $PLUGIN_HOOKS['config_page']['metademands'] = 'front/config.form.php';
      }

      if (Session::haveRight("metademands", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['metademands'] = 1;
      }

      // Template
      $PLUGIN_HOOKS['tickettemplate']['metademands'] = ['PluginMetademandsTicket', 'getAllowedFields'];

      // Rule
      $PLUGIN_HOOKS['use_rules']['metademands'] = ['RuleTicket'];

      // Notifications
      $PLUGIN_HOOKS['item_get_datas']['metademands'] = ['NotificationTargetTicket' =>
                                                           ['PluginMetademandsTicket', 'addNotificationDatas']];

      if ($plugin->isActivated('servicecatalog')) {
         $PLUGIN_HOOKS['servicecatalog']['metademands'] = ['PluginMetademandsServicecatalog'];
      }
   }

   // Import webservice
   $PLUGIN_HOOKS['webservices']['metademands']   = 'plugin_metademands_registerMethods';
   $PLUGIN_HOOKS['timeline_actions']['metademands']   = 'plugin_metademands_timeline_actions';
   $PLUGIN_HOOKS['plugin_datainjection_populate']['metademands'] = 'plugin_datainjection_populate_metademands';

}

/**
 * Get the name and the version of the plugin - Needed
 *e
 * @return array
 */
function plugin_version_metademands() {

   return [
      'name'           => _n('Meta-Demand', 'Meta-Demands', 2, 'metademands'),
      'version'        => PLUGIN_METADEMANDS_VERSION,
      'author'         => "<a href='http://blogglpi.infotel.com'>Infotel</a>",
      'license'        => 'GPLv2+',
      'homepage'       => 'https://github.com/InfotelGLPI/metademands',
      'requirements'   => [
         'glpi' => [
            'min' => '9.5',
            'dev' => false
         ]
      ]];
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
/**
 * @return bool
 */
function plugin_metademands_check_prerequisites() {
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
function plugin_metademands_check_config() {
   return true;
}
