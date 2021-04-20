<?php
/*
 -------------------------------------------------------------------------
 Screenshot
 Copyright (C) 2020-2021 by Curtis Conard
 https://github.com/cconard96/glpi-screenshot-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Screenshot.
 Screenshot is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Screenshot is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Screenshot. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_SCREENSHOT_VERSION', '1.1.3');
define('PLUGIN_SCREENSHOT_MIN_GLPI', '9.5.0');
define('PLUGIN_SCREENSHOT_MAX_GLPI', '9.6.0');

function plugin_init_screenshot()
{
	global $PLUGIN_HOOKS;
	$PLUGIN_HOOKS['csrf_compliant']['screenshot'] = true;
   $PLUGIN_HOOKS['timeline_actions']['screenshot'] = [PluginScreenshotScreenshot::class, 'timelineActions'];
   $PLUGIN_HOOKS['add_javascript']['screenshot'][] = 'js/screenshot.js';
   Plugin::registerClass('PluginScreenshotConfig', ['addtabon' => 'Config']);
   Plugin::registerClass('PluginScreenshotProfile', ['addtabon' => 'Profile']);
}

function plugin_version_screenshot()
{
	return [
      'name'         => __('Screenshot', 'screenshot'),
      'version'      => PLUGIN_SCREENSHOT_VERSION,
      'author'       => 'Curtis Conard',
      'license'      => 'GPLv2+',
      'homepage'     =>'https://github.com/cconard96/glpi-screenshot-plugin',
      'requirements' => [
         'glpi'   => [
            'min' => PLUGIN_SCREENSHOT_MIN_GLPI,
            'max' => PLUGIN_SCREENSHOT_MAX_GLPI
         ]
      ]
   ];
}

function plugin_screenshot_check_prerequisites()
{
	if (!method_exists('Plugin', 'checkGlpiVersion')) {
      $version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
      $matchMinGlpiReq = version_compare($version, PLUGIN_SCREENSHOT_MIN_GLPI, '>=');
      $matchMaxGlpiReq = version_compare($version, PLUGIN_SCREENSHOT_MAX_GLPI, '<');
      if (!$matchMinGlpiReq || !$matchMaxGlpiReq) {
         echo vsprintf(
            'This plugin requires GLPI >= %1$s and < %2$s.',
            [
               PLUGIN_SCREENSHOT_MIN_GLPI,
               PLUGIN_SCREENSHOT_MAX_GLPI,
            ]
         );
         return false;
      }
   }
   return true;
}

function plugin_screenshot_check_config()
{
	return true;
}

