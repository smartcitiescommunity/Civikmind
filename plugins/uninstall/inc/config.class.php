<?php
/*
 * @version $Id: profile.class.php 154 2013-07-11 09:26:04Z yllen $
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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginUninstallConfig extends Config {
   const CFG_CTXT = 'plugin:uninstall';

   static function getTypeName($nb = 0) {
      return __("Item's Lifecycle", 'uninstall');
   }

   /**
    * Return the current config of the plugin store in the glpi config table
    *
    * @return array config with keys => values
    */
   static function getConfig() {
      return Config::getConfigurationValues(self::CFG_CTXT);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item->getType()) {
         case "Config":
            return self::createTabEntry(self::getTypeName());
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case "Config":
            return self::showForConfig($item, $withtemplate);
      }

      return true;
   }

   static function showForConfig(Config $config, $withtemplate = 0) {
      global $CFG_GLPI;

      if (!self::canView()) {
         return false;
      }

      $cfg     = self::getConfig();
      $canedit = Session::haveRight(self::$rightname, UPDATE);
      echo "<div class='uninstall_config'>";
      if ($canedit) {
         echo "<form name='form' action='".Toolbox::getItemTypeFormURL("Config")."' method='post'>";
      }
      echo "<h2 class='header'>".__("Shortcuts", 'uninstall')."</h2>";

      echo "<ul class='shortcuts'>";
      echo "<li><a href='".PluginUninstallModel::getSearchURL()."' class='vsubmit'>".
            PluginUninstallModel::getTypeName(Session::getPluralNumber())."</a><li>";
      echo "<li><a href='preference.php?forcetab=PluginUninstallPreference$1' class='vsubmit'>".
            __("Location preferences", 'uninstall')."</a><li>";
      echo "</ul>";

      echo "<h2 class='header'>".__("Configuration")."</h2>";

      $rand = mt_rand();
      echo "<div class='field'>";
      echo "<label for='dropdown_replace_status_dropdown$rand'>".
           __("Replace status dropdown by plugin actions", 'uninstall').
           "</label>";
      Dropdown::showYesNo("replace_status_dropdown", $cfg['replace_status_dropdown'], -1, [
         'rand' => $rand,
      ]);
      echo "</div>";

      if ($canedit) {
         echo Html::hidden('config_class', ['value' => __CLASS__]);
         echo Html::hidden('config_context', ['value' => self::CFG_CTXT]);
         echo Html::submit(_sx('button', 'Save'), [
            'name' => 'update'
         ]);
      }

      Html::closeForm();
      echo "</div>"; //.uninstall_config
   }


   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   static function install(Migration $migration) {
      $current_config = self::getConfig();

      // fill config table with default values if missing
      foreach ([
         'replace_status_dropdown' => 0,
      ] as $key => $value) {
         if (!isset($current_config[$key])) {
            Config::setConfigurationValues(self::CFG_CTXT, [$key => $value]);
         }
      }
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   static function uninstall() {
      $config = new Config();
      $config->deleteByCriteria(['context' => self::CFG_CTXT]);

      return true;
   }
}