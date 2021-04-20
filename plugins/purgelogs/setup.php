<?php
/*
* @version $Id: HEADER 14684 2011-06-11 06:32:40Z remi $
LICENSE

This file is part of the purgelogs plugin.

Purgelogs plugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

Purgelogs plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with datainjection. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
 @package   purgelogs
 @author    TECLIB
 @copyright Copyright (c) 2009-2017 purgelogs plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/purgelogs
 @link      http://www.glpi-project.org/
 @link      http://www.teclib-edition.com/
 @since     2009
 ---------------------------------------------------------------------- */

define ('PLUGIN_PURGELOGS_VERSION', '1.3.0');

// Init the hooks of the plugins -Needed
function plugin_init_purgelogs() {
   global $PLUGIN_HOOKS,$CFG_GLPI;
   $PLUGIN_HOOKS['csrf_compliant']['purgelogs'] = true;

   $plugin = new Plugin();
   if ($plugin->isInstalled('purgelogs') && $plugin->isActivated('purgelogs')) {

      //if glpi is loaded
      if (Session::getLoginUserID() && Session::haveRight("config", UPDATE)) {
         $PLUGIN_HOOKS['config_page']['purgelogs'] = 'front/config.form.php';
      }
   }
}

// Get the name and the version of the plugin - Needed
function plugin_version_purgelogs() {
   return [
      'name'           => __("Purge history", "purgelogs"),
      'version'        => PLUGIN_PURGELOGS_VERSION,
      'author'         => "<a href='www.teclib.com'>TECLIB'</a>",
      'homepage'       => 'https://github.com/pluginsGLPI/purgelogs',
      'requirements'   => [
         'glpi' => [
            'min' => '9.2',
            'dev' => true
         ]
      ]
   ];
}

// Optional : check prerequisites before install : may print errors or add to message after redirect
function plugin_purgelogs_check_prerequisites() {
   $version = rtrim(GLPI_VERSION, '-dev');
   if (version_compare($version, '9.2', 'lt')) {
      echo "This plugin requires GLPI 9.2";
      return false;
   }

   return true;
}

// Uninstall process for plugin : need to return true if succeeded : may display messages or add to message after redirect
function plugin_purgelogs_check_config() {
   return true;
}
