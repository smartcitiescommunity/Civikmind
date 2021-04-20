<?php
/*
 * @version $Id: hook.php 157 2013-07-31 06:56:26Z yllen $
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

// ** Massive actions **

function plugin_uninstall_MassiveActions($type) {
   global $UNINSTALL_TYPES;

   // Like GLPI 0.84, this plugin don't support massive actions in Global item page.
   if (isset($_REQUEST['container']) && $_REQUEST['container'] == 'massformAllAssets') {
      return [];
   }

   if (in_array($type, $UNINSTALL_TYPES)) {
      return ["PluginUninstallUninstall:uninstall" => __("Uninstall", 'uninstall')];
   }
   return [];
}

// ** Search **

function plugin_uninstall_addDefaultWhere($itemtype) {

   switch ($itemtype) {
      case 'PluginUninstallModel' :
         if (!PluginUninstallModel::canReplace()) {
            return "`glpi_plugin_uninstall_models`.`types_id` = '1'";
         }
         break;
   }
}

// ** Install / Uninstall plugin **

function plugin_uninstall_install() {
   $dir = Plugin::getPhpDir('uninstall');

   $plugin_infos = plugin_version_uninstall();
   $migration    = new Migration($plugin_infos['version']);

   //Plugin classes are not loaded when plugin is not activated : force class loading
   require_once ($dir . "/inc/uninstall.class.php");
   require_once ($dir . "/inc/profile.class.php");
   require_once ($dir . "/inc/preference.class.php");
   require_once ($dir . "/inc/model.class.php");
   require_once ($dir . "/inc/replace.class.php");
   require_once ($dir . "/inc/config.class.php");

   PluginUninstallProfile::install($migration);
   PluginUninstallModel::install($migration);
   PluginUninstallPreference::install($migration);
   PluginUninstallConfig::install($migration);
   return true;
}


function plugin_uninstall_uninstall() {
   $dir = Plugin::getPhpDir('uninstall');

   require_once ($dir . "/inc/uninstall.class.php");
   require_once ($dir . "/inc/profile.class.php");
   require_once ($dir . "/inc/preference.class.php");
   require_once ($dir . "/inc/model.class.php");
   require_once ($dir . "/inc/replace.class.php");
   require_once ($dir . "/inc/config.class.php");

   PluginUninstallProfile::uninstall();
   PluginUninstallModel::uninstall();
   PluginUninstallPreference::uninstall();
   PluginUninstallConfig::uninstall();
   return true;
}
