<?php
/*
 -------------------------------------------------------------------------
 Costs plugin for GLPI
 Copyright (C) 2018 by the TICgal Team.

 https://github.com/ticgal/costs
 -------------------------------------------------------------------------

 LICENSE

 This file is part of the Costs plugin.

 Costs plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 Costs plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Costs. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   Costs
 @author    the TICgal team
 @copyright Copyright (c) 2018 TICgal team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://tic.gal
 @since     2018
 ---------------------------------------------------------------------- */
define ('PLUGIN_COSTS_VERSION', '1.3.0');
// Minimal GLPI version, inclusive
define("PLUGIN_COSTS_MIN_GLPI", "9.5");
// Maximum GLPI version, exclusive
define("PLUGIN_COSTS_MAX_GLPI", "9.6");

global $CFG_GLPI;
if (!defined('PLUGIN_COSTS_NUMBER_STEP')) {
   define("PLUGIN_COSTS_NUMBER_STEP", 1 / pow(1, $CFG_GLPI["decimal_number"]));
}

function plugin_version_costs() {
   return ['name'       => 'Costs',
      'version'        => PLUGIN_COSTS_VERSION,
      'author'         => '<a href="https://tic.gal">TICgal</a>',
      'homepage'       => 'https://tic.gal/en/project/costs-control-plugin-glpi/',
      'license'        => 'GPLv3+',
      'requirements'   => [
         'glpi'   => [
            'min' => PLUGIN_COSTS_MIN_GLPI,
            'max' => PLUGIN_COSTS_MAX_GLPI,
         ]
      ]];
}

function plugin_init_costs() {
   global $PLUGIN_HOOKS;

   if (Session::haveRight('entity', UPDATE)) {
       Plugin::registerClass('PluginCostsEntity', ['addtabon' => 'Entity']);
   }
   if (Session::haveRightsOr("config", [READ, UPDATE])) {
      Plugin::registerClass('PluginCostsConfig', ['addtabon' => 'Config']);
      $PLUGIN_HOOKS['config_page']['costs'] = 'front/config.form.php';
   }

   $PLUGIN_HOOKS['csrf_compliant']['costs'] = true;
   $PLUGIN_HOOKS['pre_item_update']['costs'] = ['Ticket'  => ['PluginCostsTicket','generateCosts']];

}
