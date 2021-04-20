<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 badges plugin for GLPI
 Copyright (C) 2009-2016 by the badges Development Team.

 https://github.com/InfotelGLPI/badges
 -------------------------------------------------------------------------

 LICENSE

 This file is part of badges.

 badges is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 badges is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with badges. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_BADGES_VERSION', '2.6.0');
if (!defined("PLUGINBADGES_DIR")) {
   define("PLUGINBADGES_DIR", Plugin::getPhpDir("badges"));
}
// Init the hooks of the plugins -Needed
function plugin_init_badges() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['badges']   = true;
   $PLUGIN_HOOKS['assign_to_ticket']['badges'] = true;
   $PLUGIN_HOOKS['change_profile']['badges']   = ['PluginBadgesProfile', 'initProfile'];
   $PLUGIN_HOOKS['add_css']['badges']          = ['badges.css'];

   $PLUGIN_HOOKS['javascript']['badges'][]   = PLUGINBADGES_DIR . '/badges.js';
   //   $PLUGIN_HOOKS['add_javascript']['badges'][] = 'badges.js';

   if (Session::getLoginUserID()) {

      Plugin::registerClass('PluginBadgesBadge', [
         'linkuser_types'              => true,
         'document_types'              => true,
         'helpdesk_visible_types'      => true,
         'ticket_types'                => true,
         'notificationtemplates_types' => true
      ]);

      Plugin::registerClass('PluginBadgesProfile', ['addtabon' => 'Profile']);
      Plugin::registerClass('PluginBadgesConfig', ['addtabon' => 'CronTask']);
      Plugin::registerClass('PluginBadgesReturn', ['addtabon' => 'CronTask']);
      Plugin::registerClass('PluginBadgesRequest', ['addtabon' => 'User']);

      if (class_exists('PluginResourcesResource')) {
         PluginResourcesResource::registerType('PluginBadgesBadge');
      }

      $plugin = new Plugin();
      if (!$plugin->isActivated('environment')
          && Session::haveRight("plugin_badges", READ)) {
         $PLUGIN_HOOKS['menu_toadd']['badges'] = ['assets' => 'PluginBadgesMenu'];

      }

      if (Session::haveRight("plugin_badges", READ)
          && !$plugin->isActivated('servicecatalog')) {
         $PLUGIN_HOOKS['helpdesk_menu_entry']['badges'] = '/front/wizard.php';
      }

      if ($plugin->isActivated('servicecatalog')) {
         $PLUGIN_HOOKS['servicecatalog']['badges'] = ['PluginBadgesServicecatalog'];
      }

      if (Session::haveRight("plugin_badges", UPDATE)) {
         $PLUGIN_HOOKS['use_massive_action']['badges'] = 1;
      }

      if ($plugin->isActivated('badges')) { // only if plugin activated
         $PLUGIN_HOOKS['plugin_datainjection_populate']['badges'] = 'plugin_datainjection_populate_badges';
      }

      // Import from Data_Injection plugin
      $PLUGIN_HOOKS['migratetypes']['badges']  = 'plugin_datainjection_migratetypes_badges';
      $PLUGIN_HOOKS['redirect_page']['badges'] = 'front/wizard.php';
   }
}

// Get the name and the version of the plugin - Needed

/**
 * @return array
 */
function plugin_version_badges() {

   return [
      'name'           => _n('Badge', 'Badges', 2, 'badges'),
      'version'        => PLUGIN_BADGES_VERSION,
      'author'         => "<a href='http://blogglpi.infotel.com'>Infotel</a>",
      'license'        => 'GPLv2+',
      'homepage'       => 'https://github.com/InfotelGLPI/badges',
      'requirements'   => [
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
function plugin_badges_check_prerequisites() {
   if (version_compare(GLPI_VERSION, '9.5', 'lt')
         || version_compare(GLPI_VERSION, '9.6', 'ge')) {
      if (method_exists('Plugin', 'messageIncompatible')) {
         echo Plugin::messageIncompatible('core', '9.5');
      }
      return false;
   }
   return true;
}

// Uninstall process for plugin : need to return true if succeeded
//may display messages or add to message after redirect
/**
 * @return bool
 */
function plugin_badges_check_config() {
   return true;
}

/**
 * @param $types
 *
 * @return mixed
 */
function plugin_datainjection_migratetypes_badges($types) {
   $types[1600] = 'PluginBadgesBadge';
   return $types;
}
