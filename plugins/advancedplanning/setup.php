<?php
/*
 -------------------------------------------------------------------------
 advancedplanning plugin for GLPI
 Copyright (C) 2019 by the advancedplanning Development Team.

 https://github.com/pluginsGLPI/advancedplanning
 -------------------------------------------------------------------------

 LICENSE

 This file is part of advancedplanning.

 advancedplanning is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 advancedplanning is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with advancedplanning. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_ADVANCEDPLANNING_VERSION', '0.2.0');

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_advancedplanning() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   $PLUGIN_HOOKS['csrf_compliant']['advancedplanning'] = true;

   // manage list of authorized url where to load js/css
   $calendar_urls = [
      "front/planning.php",
      "front/reservation.php",
   ];
   foreach ($CFG_GLPI['reservation_types'] as $reservation_type) {
      $calendar_urls[] = $reservation_type::getFormUrl(false);
   }

   $found_url = false;
   foreach ($calendar_urls as $url) {
      if (strpos($_SERVER['REQUEST_URI'], $url) !==false ) {
         $found_url = true;
         break;
      }
   }

   if ($found_url) {
      $sc_lib = "lib/fullcalendar-scheduler-4.4.0/packages-premium";

      $PLUGIN_HOOKS['add_javascript']['advancedplanning'] = [
         "$sc_lib/resource-common/main.js",
         "$sc_lib/timeline/main.js",
         "$sc_lib/resource-timeline/main.js"
      ];

      $PLUGIN_HOOKS['add_css']['advancedplanning'] = [
         "$sc_lib/timeline/main.css",
         "$sc_lib/resource-timeline/main.css"
      ];

      $PLUGIN_HOOKS['planning_scheduler_key']['advancedplanning'] = function() {
         return "GPL-My-Project-Is-Open-Source";
      };
   }
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_advancedplanning() {
   return [
      'name'           => 'advancedplanning',
      'version'        => PLUGIN_ADVANCEDPLANNING_VERSION,
      'author'         => '<a href="http://www.teclib.com">Teclib\'</a>',
      'license'        => 'GPLV3',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => '9.2',
         ]
      ]
   ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_advancedplanning_check_prerequisites() {

   //Version check is not done by core in GLPI < 9.2 but has to be delegated to core in GLPI >= 9.2.
   $version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
   if (version_compare($version, '9.2', '<')) {
      echo "This plugin requires GLPI >= 9.2";
      return false;
   }
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_advancedplanning_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo __('Installed / not configured', 'advancedplanning');
   }
   return false;
}
