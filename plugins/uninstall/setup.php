<?php
/*
 * @version $Id: setup.php 154 2013-07-11 09:26:04Z yllen $
 LICENSE

 This file is part of the uninstall plugin.

 Uninstall plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Uninstall plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with uninstall. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   uninstall
 @author    the uninstall plugin team
 @copyright Copyright (c) 2010-2013 Uninstall plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/uninstall
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

define ('PLUGIN_UNINSTALL_VERSION', '2.7.0');

// Minimal GLPI version, inclusive
define("PLUGIN_UNINSTALL_MIN_GLPI", "9.5");
// Maximum GLPI version, exclusive
define("PLUGIN_UNINSTALL_MAX_GLPI", "9.6");

/**
 * Function Init
 */
function plugin_init_uninstall() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $UNINSTALL_TYPES,
          $UNINSTALL_DIRECT_CONNECTIONS_TYPE;

   $PLUGIN_HOOKS['csrf_compliant']['uninstall'] = true;

   Plugin::registerClass('PluginUninstallPreference', ['addtabon' => ['Preference']]);
   Plugin::registerClass('PluginUninstallProfile', ['addtabon' => ['Profile']]);

   $plugin = new Plugin();
   if ($plugin->isActivated('uninstall')) {
      $UNINSTALL_TYPES                    = ['Computer', 'Monitor',
                                             'NetworkEquipment',
                                             'Peripheral', 'Phone', 'Printer'];
      $UNINSTALL_DIRECT_CONNECTIONS_TYPE  = ['Monitor', 'Peripheral', 'Phone',
                                             'Printer'];

      if (Session::getLoginUserID()) {
         // config page
         Plugin::registerClass('PluginUninstallConfig', [
            'addtabon' => 'Config'
         ]);
         $PLUGIN_HOOKS['config_page']['uninstall'] = 'front/config.form.php';
         $uninstallconfig = PluginUninstallConfig::getConfig();

         $PLUGIN_HOOKS['add_css']['uninstall'] = [
            'css/uninstall.css',
         ];

         if ($uninstallconfig['replace_status_dropdown']) {
            // replace item state by uninstall list
            $PLUGIN_HOOKS['post_item_form']['uninstall'] = [
               'PluginUninstallState', 'replaceState'
            ];
         } else {
            // add tabs to items
            foreach ($UNINSTALL_TYPES as $type) {
               Plugin::registerClass('PluginUninstallUninstall', [
                  'addtabon' => $type
               ]);
            }
         }

         if (Session::haveRight('uninstall:profile', READ)) {
            $PLUGIN_HOOKS['use_massive_action']['uninstall'] = true;

            if (Session::haveRight('uninstall:profile', UPDATE)) {
               // Add link in GLPI plugins list :
               $PLUGIN_HOOKS["menu_toadd"]['uninstall'] = ['admin' => 'PluginUninstallModel'];
            }

            //Item actions
            $PLUGIN_HOOKS['item_update']['uninstall']
               = ['PluginUninstallModel'
                  => ['PluginUninstallPreference', 'afterUpdateModel']];
            $PLUGIN_HOOKS['item_delete']['uninstall']
               = ['PluginUninstallModel'
                  => ['PluginUninstallPreference', 'beforeItemPurge']];

            $PLUGIN_HOOKS['pre_item_purge']['uninstall']
               = ['User' => ['PluginUninstallPreference', 'beforeItemPurge']];
         }

      }
      $PLUGIN_HOOKS['post_init']['uninstall'] = 'plugin_uninstall_postinit';
   }
}

function plugin_version_uninstall() {
   return [
      'name'           => __("Item's Lifecycle (uninstall)", 'uninstall'),
      'author'         => 'Walid Nouh, François Legastelois, Remi Collet',
      'license'        => "GPLv2+",
      'homepage'       => 'https://github.com/pluginsGLPI/uninstall',
      'version'        => PLUGIN_UNINSTALL_VERSION,
      'license'        => 'GPLv2+',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_UNINSTALL_MIN_GLPI,
            'max' => PLUGIN_UNINSTALL_MAX_GLPI,
            'dev' => true, //Required to allow 9.2-dev
         ]
      ]
   ];
}
