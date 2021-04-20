<?php
/*
 *
 -------------------------------------------------------------------------
 Plugin GLPI News
 Copyright (C) 2015 by teclib.
 http://www.teclib.com
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Plugin GLPI News.
 Plugin GLPI News is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Plugin GLPI News is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Plugin GLPI News. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

define ('PLUGIN_NEWS_VERSION', '1.9.0');

// Minimal GLPI version, inclusive
define("PLUGIN_NEWS_MIN_GLPI", "9.5");
// Maximum GLPI version, exclusive
define("PLUGIN_NEWS_MAX_GLPI", "9.6");

function plugin_init_news() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['news'] = true;

   $plugin = new Plugin();
   if ($plugin->isInstalled('news')
       && $plugin->isActivated('news')) {
      Plugin::registerClass('PluginNewsProfile', ['addtabon' => 'Profile']);

      $PLUGIN_HOOKS['add_css']['news'] = 'css/styles.css';
      $PLUGIN_HOOKS['add_javascript']['news'][] = "js/news.js";
      $PLUGIN_HOOKS['display_login']['news'] = [
         "PluginNewsAlert", "displayOnLogin"
      ];
      $PLUGIN_HOOKS['display_central']['news'] = [
         "PluginNewsAlert", "displayOnCentral"
      ];

      $PLUGIN_HOOKS['pre_item_form']['news'] = ['PluginNewsAlert', 'preItemForm'];

      if (Session::haveRight('reminder_public', READ)) {
         $PLUGIN_HOOKS['menu_toadd']['news'] = [
            'tools' => 'PluginNewsAlert',
         ];
         $PLUGIN_HOOKS['config_page']['news'] = 'front/alert.php';

         // require tinymce (for glpi >= 9.2)
         $CFG_GLPI['javascript']['tools']['pluginnewsalert'] = ['tinymce'];
      }
   }
}

function plugin_version_news() {
   return [
      'name'           => __('Alerts', 'news'),
      'version'        => PLUGIN_NEWS_VERSION,
      'author'         => "<a href='mailto:contact@teclib.com'>TECLIB'</a>",
      'license'        => "GPLv2+",
      'homepage'       => 'https://github.com/pluginsGLPI/news',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_NEWS_MIN_GLPI,
            'max' => PLUGIN_NEWS_MAX_GLPI,
         ]
      ]
   ];
}
