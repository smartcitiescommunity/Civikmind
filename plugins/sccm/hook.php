<?php
/**
 * ------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of SCCM plugin.
 *
 * SCCM plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * SCCM plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * ------------------------------------------------------------------------
 * @author    François Legastelois <flegastelois@teclib.com>
 * @copyright Copyright (C) 2014-2018 by Teclib' and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/sccm
 * @link      https://pluginsglpi.github.io/sccm/
 * ------------------------------------------------------------------------
 */

function plugin_sccm_install() {
   global $DB;

   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/sccm')) {
      mkdir(GLPI_PLUGIN_DOC_DIR.'/sccm');
   }
   if (!is_dir(GLPI_PLUGIN_DOC_DIR.'/sccm/xml')) {
      mkdir(GLPI_PLUGIN_DOC_DIR.'/sccm/xml');
   }

   $migration = new Migration(100);

   require 'inc/config.class.php';
   require 'inc/sccm.class.php';
   PluginSccmConfig::install($migration);
   PluginSccmSccm::install($migration);

   $migration->executeMigration();

   return true;
}

function plugin_sccm_uninstall() {
   global $DB;

   if (is_dir(GLPI_PLUGIN_DOC_DIR.'/sccm')) {
      rrmdir(GLPI_PLUGIN_DOC_DIR.'/sccm');
   }

   require 'inc/config.class.php';
   require 'inc/sccm.class.php';
   return PluginSccmConfig::uninstall();
   return PluginSccmSccm::uninstall();

   return true;
}

function rrmdir($dir) {

   if (is_dir($dir)) {
      $objects = scandir($dir);
      foreach ($objects as $object) {
         if ($object != "." && $object != "..") {
            if (filetype($dir."/".$object) == "dir") {
               rrmdir($dir."/".$object);
            } else {
               unlink($dir."/".$object);
            }
         }
      }
      reset($objects);
      rmdir($dir);
   }
}