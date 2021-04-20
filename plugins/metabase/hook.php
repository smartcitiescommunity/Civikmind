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

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_metabase_install() {
   $version   = plugin_version_metabase();
   $migration = new Migration($version['version']);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php$/", $filepath, $matches)) {
         $classname = 'PluginMetabase' . ucfirst($matches[1]);
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
function plugin_metabase_uninstall() {
   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginMetabase' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'uninstall')) {
            $classname::uninstall();
         }
      }
   }
   return true;
}
