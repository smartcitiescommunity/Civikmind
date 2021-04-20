<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 databases plugin for GLPI
 Copyright (C) 2009-2016 by the databases Development Team.

 https://github.com/InfotelGLPI/databases
 -------------------------------------------------------------------------

 LICENSE

 This file is part of databases.

 databases is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 databases is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with databases. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_DATABASES_VERSION', '2.3.2');

if (!defined("PLUGIN_DATABASES_DIR")) {
   define("PLUGIN_DATABASES_DIR", Plugin::getPhpDir("databases"));
   define("PLUGIN_DATABASES_DIR_NOFULL", Plugin::getPhpDir("databases",false));
}
// Init the hooks of the plugins -Needed
function plugin_init_databases() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['databases']   = true;
   $PLUGIN_HOOKS['change_profile']['databases']   = ['PluginDatabasesProfile', 'initProfile'];
   $PLUGIN_HOOKS['assign_to_ticket']['databases'] = true;

   //$PLUGIN_HOOKS['assign_to_ticket_dropdown']['databases'] = true;
   //$PLUGIN_HOOKS['assign_to_ticket_itemtype']['databases'] = array('PluginDatabasesDatabase_Item');

   Plugin::registerClass('PluginDatabasesDatabase', [
      'linkgroup_types'        => true,
      'linkgroup_tech_types'   => true,
      'linkuser_tech_types'    => true,
      'document_types'         => true,
      'ticket_types'           => true,
      'helpdesk_visible_types' => true,
      'addtabon'               => 'Supplier'
   ]);

   $CFG_GLPI['impact_asset_types']['PluginDatabasesDatabase'] = "plugins/databases/databases.png";

   Plugin::registerClass('PluginDatabasesProfile',
                         ['addtabon' => 'Profile']);

   //Plugin::registerClass('PluginDatabasesDatabase_Item',
   //                      array('ticket_types' => true));

   if (class_exists('PluginAccountsAccount')) {
      PluginAccountsAccount::registerType('PluginDatabasesDatabase');
   }

   if (Session::getLoginUserID()) {

      $plugin = new Plugin();
      if (!$plugin->isActivated('environment')
          && Session::haveRight("plugin_databases", READ)
      ) {

         $PLUGIN_HOOKS['menu_toadd']['databases'] = ['assets' => 'PluginDatabasesMenu'];
      }

      if (Session::haveRight("plugin_databases", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['databases'] = 1;
      }

      if (class_exists('PluginDatabasesDatabase_Item')) { // only if plugin activated
         $PLUGIN_HOOKS['plugin_datainjection_populate']['databases'] = 'plugin_datainjection_populate_databases';
      }

      // End init, when all types are registered
      $PLUGIN_HOOKS['post_init']['databases'] = 'plugin_databases_postinit';

      // Import from Data_Injection plugin
      $PLUGIN_HOOKS['migratetypes']['databases'] = 'plugin_datainjection_migratetypes_databases';
   }
}

// Get the name and the version of the plugin - Needed
/**
 * @return array
 */
function plugin_version_databases() {

   return [
      'name'         => _n('Database', 'Databases', 2, 'databases'),
      'version'      => PLUGIN_DATABASES_VERSION,
      'author'       => "<a href='http://blogglpi.infotel.com'>Infotel</a>",
      'oldname'      => 'sgbd',
      'license'      => 'GPLv2+',
      'homepage'     => 'https://github.com/InfotelGLPI/databases',
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
function plugin_databases_check_prerequisites() {
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
function plugin_databases_check_config() {
   return true;
}

/**
 * @param $types
 *
 * @return mixed
 */
function plugin_datainjection_migratetypes_databases($types) {
   $types[2400] = 'PluginDatabasesDatabase';
   return $types;
}
