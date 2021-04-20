<?php
/*
 -------------------------------------------------------------------------
 GappEssentials plugin for GLPI
 Copyright (C) 2019 by the TICgal
 https://tic.gal
 https://github.com/pluginsGLPI/gappessentials
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GappEssentials.

 GappEssentials is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GappEssentials is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GappEssentials. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_GAPPESSENTIALS_VERSION', '1.2.0');
// Minimal GLPI version, inclusive
define("PLUGIN_GAPPESSENTIALS_MIN_GLPI", "9.5.0");
define("PLUGIN_GAPPESSENTIALS_MAX_GLPI", "9.6.0");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_gappessentials() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['gappessentials'] = true;
}

/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_gappessentials() {
   return [
      'name'           => 'Gapp Essentials',
      'version'        => PLUGIN_GAPPESSENTIALS_VERSION,
      'author'         => '<a href="https://tic.gal">TICgal</a>',
      'license'        => 'AGPLv3+',
      'homepage'       => 'https://tic.gal/en/gappessentials/',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_GAPPESSENTIALS_MIN_GLPI,
            'max' => PLUGIN_GAPPESSENTIALS_MAX_GLPI,
         ]
      ]
   ];
}

/**
 * Check pre-requisites before install
 * OPTIONAL, but recommanded
 *
 * @return boolean
 */
function plugin_gappessentials_check_prerequisites() {
   $path=substr(Plugin::getPhpDir('gappessentials',false),1,(strpos(Plugin::getPhpDir('gappessentials',false),"/",1)-1));
   if($path=="plugins"){
      return true;
   }
   return false;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_gappessentials_check_config($verbose = false) {
   return true;
}
