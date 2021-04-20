<?php
/*
 -------------------------------------------------------------------------
 metabase plugin for GLPI
 Copyright (C) 2017 by the metabase Development Team.

 https://github.com/pluginsGLPI/metabase
 -------------------------------------------------------------------------

 LICENSE

 This file is part of metabase.

 metabase is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 metabase is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with metabase. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_METABASE_VERSION', '1.2.2');

// Minimal GLPI version, inclusive
define("PLUGIN_METABASE_MIN_GLPI", "9.5");
// Maximum GLPI version, exclusive
define("PLUGIN_METABASE_MAX_GLPI", "9.6");

if (!defined("PLUGINMETABASE_DIR")) {
   define("PLUGINMETABASE_DIR", __DIR__);
}
if (!defined("PLUGINMETABASE_REPORTS_DIR")) {
   define("PLUGINMETABASE_REPORTS_DIR", PLUGINMETABASE_DIR."/reports");
}
if (!defined("PLUGINMETABASE_DASHBOARDS_DIR")) {
   define("PLUGINMETABASE_DASHBOARDS_DIR", PLUGINMETABASE_DIR."/dashboards");
}

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_metabase() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['metabase'] = true;

   // add autoload for vendor
   include_once(PLUGINMETABASE_DIR . "/vendor/autoload.php");

   // don't load hooks if plugin not enabled (or glpi not logged)
   $plugin = new Plugin();
   if (!$plugin->isInstalled('metabase')
       || !$plugin->isActivated('metabase')
       || !Session::getLoginUserID()) {
      return true;
   }

   // config page
   Plugin::registerClass('PluginMetabaseConfig', ['addtabon' => 'Config']);
   $PLUGIN_HOOKS['config_page']['metabase'] = 'front/config.form.php';

   // add dashboards
   Plugin::registerClass('PluginMetabaseDashboard', ['addtabon' => 'Central']);
   $PLUGIN_HOOKS['helpdesk_menu_entry']['metabase'] = '/front/selfservice.php';

   // profile rights management
   Plugin::registerClass('PluginMetabaseProfileright', ['addtabon' => 'Profile']);

   // css & js
   $PLUGIN_HOOKS['add_css']['metabase'] = 'metabase.css';
   $PLUGIN_HOOKS['add_javascript']['metabase'] = 'metabase.js';

   // Encryption
   $PLUGIN_HOOKS['secured_configs']['metabase'] = ['password'];
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_metabase() {
   return [
      'name'           => 'metabase',
      'version'        => PLUGIN_METABASE_VERSION,
      'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
      'license'        => 'GPLv2+',
      'homepage'       => 'https://github.com/pluginsGLPI/metabase',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_METABASE_MIN_GLPI,
            'max' => PLUGIN_METABASE_MAX_GLPI,
         ]
      ]
   ];
}

function plugin_metabase_recursive_remove_empty($haystack) {
   foreach ($haystack as $key => $value) {
      if (is_array($value)) {
         if (count($value) == 0) {
            unset($haystack[$key]);
         } else {
            $haystack[$key] = plugin_metabase_recursive_remove_empty($haystack[$key]);
         }
      } else if ($haystack[$key] === "") {
         unset($haystack[$key]);
      }
   }

   return $haystack;
}

function metabaseGetIdByField($itemtype = "", $field = "", $value = "") {
   global $DB;

   $query = "SELECT `id`
             FROM `".$itemtype::getTable()."`
             WHERE `$field` = '".addslashes($value)."'";
   $result = $DB->query($query);

   if ($DB->numrows($result) == 1) {
      return $DB->result($result, 0, 'id');
   }
   return false;
}
