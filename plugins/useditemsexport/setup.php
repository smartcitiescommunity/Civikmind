<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of useditemsexport.
 *
 * useditemsexport is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * useditemsexport is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright © 2015 - 2018 Teclib'
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/useditemsexport
 * @link      https://pluginsglpi.github.io/useditemsexport/
 * -------------------------------------------------------------------------
 */

// Plugin version
define("PLUGIN_USEDITEMSEXPORT_VERSION", "2.4.0");

// Minimal GLPI version, inclusive
define("PLUGIN_USEDITEMSEXPORT_MIN_GLPI", "9.5");
// Maximum GLPI version, exclusive
define("PLUGIN_USEDITEMSEXPORT_MAX_GLPI", "9.6");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_useditemsexport() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $plugin = new Plugin();

   $PLUGIN_HOOKS['csrf_compliant']['useditemsexport'] = true;

   if (Session::getLoginUserID() && $plugin->isActivated('useditemsexport')) {

      PluginUseditemsexportConfig::loadInSession();

      if (Session::haveRight('config', UPDATE)) {
         $PLUGIN_HOOKS['config_page']['useditemsexport'] = 'front/config.form.php';
      }

      if (Session::haveRight('profile', UPDATE)) {
         Plugin::registerClass('PluginUseditemsexportProfile',
                                 ['addtabon' => 'Profile']);
      }

      if (isset($_SESSION['plugins']['useditemsexport']['config'])) {

         $useditemsexport_config = $_SESSION['plugins']['useditemsexport']['config'];

         if (Session::haveRightsOr('plugin_useditemsexport_export', [READ, CREATE, PURGE])
              && $useditemsexport_config['is_active']) {

            Plugin::registerClass('PluginUseditemsexportExport',
                                    ['addtabon' => 'User']);
         }
      }
   }
}

/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_useditemsexport() {

   return  [
      'name' => __('Used items export', 'useditemsexport'),
      'version' => PLUGIN_USEDITEMSEXPORT_VERSION,
      'oldname' => '',
      'license' => 'GPLv2+',
      'author'  => "TECLIB",
      'homepage'=>'https://github.com/pluginsGLPI/useditemsexport',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_USEDITEMSEXPORT_MIN_GLPI,
            'max' => PLUGIN_USEDITEMSEXPORT_MAX_GLPI,
         ]
      ]
   ];
}
