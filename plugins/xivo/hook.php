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

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_xivo_install() {
   global $DB;

   $version   = plugin_version_xivo();
   $migration = new Migration($version['version']);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php$/", $filepath, $matches)) {
         $classname = 'PluginXivo' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'install')) {
            $classname::install($migration);
         }
      }
   }

   $migration->executeMigration();

   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_xivo_uninstall() {
   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginXivo' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'uninstall')) {
            $classname::uninstall();
         }
      }
   }
   return true;
}

function plugin_xivo_getAddSearchOptions($itemtype) {
   if (isset($_SESSION['glpiactiveentities'])
      && is_array($_SESSION['glpiactiveentities'])
      && count($_SESSION['glpiactiveentities']) > 0) {

      switch ($itemtype) {
         case 'Phone':
         case 'Line':
            return PluginXivoLine::getAddSearchOptions($itemtype);
      }
   }

   return null;
}