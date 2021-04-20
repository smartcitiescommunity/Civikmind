<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of mantis.
 *
 * mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2018 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/mantis
 * @link      https://pluginsglpi.github.io/mantis/
 * -------------------------------------------------------------------------
 */

/**
 * Install all necessary elements for the plugin
 *
 * @return boolean True if success
 */
function plugin_mantis_install() {

   $migration = new Migration(PLUGIN_MANTIS_VERSION);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginMantis' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'install')) {
            $classname::install($migration);
         }
      }
   }
   return true;
}

/**
 * Uninstall previously installed elements of the plugin
 *
 * @return boolean True if success
 */
function plugin_mantis_uninstall() {

   $migration = new Migration(PLUGIN_MANTIS_VERSION);

   // Parse inc directory
   foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
      // Load *.class.php files and get the class name
      if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
         $classname = 'PluginMantis' . ucfirst($matches[1]);
         include_once($filepath);
         // If the install method exists, load it
         if (method_exists($classname, 'uninstall')) {
            $classname::uninstall($migration);
         }
      }
   }
   return true;
}

function plugin_mantis_getAddSearchOptions($itemtype) {
   $sopt = [];

   if (in_array($itemtype, ['Ticket','Problem','Change'])) {

      $sopt[78963]['table']            = 'glpi_plugin_mantis_mantis';
      $sopt[78963]['field']            = 'id';
      $sopt[78963]['forcegroupby']     = true;
      $sopt[78963]['usehaving']        = true;
      $sopt[78963]['datatype']         = 'count';
      $sopt[78963]['massiveaction']    = false;
      $sopt[78963]['name']             = __('Number of linked MantisBT issues', 'mantis');
      $sopt[78963]['joinparams']       = ['jointype' => "itemtype_item"];

   }

   return $sopt;
}