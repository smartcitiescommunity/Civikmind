<?php
/*
 -------------------------------------------------------------------------
 xivo plugin for GLPI
 Copyright (C) 2017 by the xivo Development Team.

 https://github.com/pluginsGLPI/xivo
 -------------------------------------------------------------------------

 LICENSE

 This file is part of xivo.

 xivo is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 xivo is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with xivo. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_XIVO_VERSION', '1.0.0');

// Minimal GLPI version, inclusive
define('PLUGIN_XIVO_MIN_GLPI', '9.5');
// Maximum GLPI version, exclusive
define('PLUGIN_XIVO_MAX_GLPI', '9.6');

// disable some feature as they are considered as experimental or deprecated by the editor
define('PLUGIN_XIVO_ENABLE_PRESENCE', '1');
define('PLUGIN_XIVO_ENABLE_CALLCENTER', '0');

if (!defined("PLUGINXIVO_DIR")) {
   define("PLUGINXIVO_DIR", __DIR__);
}

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_xivo() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['xivo'] = true;

   // add autoload for vendor
   include_once(PLUGINXIVO_DIR . "/vendor/autoload.php");

   // don't load hooks if plugin not enabled (or glpi not logged)
   $plugin = new Plugin();
   if (!$plugin->isInstalled('xivo')
       || !$plugin->isActivated('xivo')
       || !Session::getLoginUserID()) {
      return true;
   }

   //get plugin config
   $xivoconfig = PluginXivoConfig::getConfig();

   // config page
   Plugin::registerClass('PluginXivoConfig', ['addtabon' => 'Config']);
   $PLUGIN_HOOKS['config_page']['xivo'] = 'front/config.form.php';

   // additional tabs
   Plugin::registerClass('PluginXivoPhone_Line',
                         ['addtabon' => ['Phone', 'Line']]);

   // add Line to GLPI types
   Plugin::registerClass('PluginXivoLine',
                         ['addtabon' => 'Line']);

   // css & js
   $PLUGIN_HOOKS['add_css']['xivo'] = [
      'css/animation.css',
      'css/main.css'
   ];

   $PLUGIN_HOOKS['add_javascript']['xivo'] = [
      'js/common.js',
   ];
   if ($xivoconfig['enable_xuc']
       && ($_SESSION['glpiactiveprofile']['interface'] == "central"
           || $xivoconfig['enable_xuc_selfservice'])) {
      $PLUGIN_HOOKS['add_javascript']['xivo'][] = 'js/xivo/callback.js';
      $PLUGIN_HOOKS['add_javascript']['xivo'][] = 'js/xivo/membership.js';
      $PLUGIN_HOOKS['add_javascript']['xivo'][] = 'js/xivo/cti.js';
      $PLUGIN_HOOKS['add_javascript']['xivo'][] = 'js/store2.min.js';
      $PLUGIN_HOOKS['add_javascript']['xivo'][] = 'js/sessionStorageTabs.js';
      $PLUGIN_HOOKS['add_javascript']['xivo'][] = 'js/xuc.js';
      $PLUGIN_HOOKS['add_javascript']['xivo'][] = 'js/app.js.php';
   }

   // standard hooks
   $PLUGIN_HOOKS['item_purge']['xivo'] = [
      'Phone' => ['PluginXivoPhone', 'phonePurged']
   ];

   // display autoinventory in phones
   $PLUGIN_HOOKS['autoinventory_information']['xivo'] = [
      'Phone' =>  ['PluginXivoPhone', 'displayAutoInventory'],
   ];
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_xivo() {

   return [
      'name'           => 'xivo',
      'version'        => PLUGIN_XIVO_VERSION,
      'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
      'license'        => '',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_XIVO_MIN_GLPI,
            'max' => PLUGIN_XIVO_MAX_GLPI,
         ]
      ]
   ];
}

function plugin_xivo_recursive_remove_empty($haystack) {
   foreach ($haystack as $key => $value) {
      if (is_array($value)) {
         if (count($value) == 0) {
            unset($haystack[$key]);
         } else {
            $haystack[$key] = plugin_xivo_recursive_remove_empty($haystack[$key]);
         }
      } else if ($haystack[$key] === "") {
         unset($haystack[$key]);
      }
   }

   return $haystack;
}
