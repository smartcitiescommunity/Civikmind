<?php
/*
 -------------------------------------------------------------------------
 Camera Input
 Copyright (C) 2020-2021 by Curtis Conard
 https://github.com/cconard96/glpi-camerainput-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Camera Input.
 Camera Input is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Camera Input is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Camera Input. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

define('PLUGIN_CAMERAINPUT_VERSION', '1.0.0');
define('PLUGIN_CAMERAINPUT_MIN_GLPI', '9.5.0');
define('PLUGIN_CAMERAINPUT_MAX_GLPI', '9.6.0');

function plugin_init_camerainput()
{
	global $PLUGIN_HOOKS;
	$PLUGIN_HOOKS['csrf_compliant']['camerainput'] = true;

	if (Plugin::isPluginActive('camerainput')) {
      $PLUGIN_HOOKS['add_javascript']['camerainput'][] = 'lib/quagga/quagga2.min.js';
      $PLUGIN_HOOKS['add_javascript']['camerainput'][] = 'js/camerainput.js';
      $PLUGIN_HOOKS['add_css']['camerainput'][] = 'css/camerainput.css';
      // Add Config Page
      Plugin::registerClass('PluginCamerainputConfig', ['addtabon' => 'Config']);

      $PLUGIN_HOOKS['pre_item_add']['camerainput']['Config'] = ['PluginCamerainputConfig', 'preAddOrUpdateConfig'];
      $PLUGIN_HOOKS['pre_item_update']['camerainput']['Config'] = ['PluginCamerainputConfig', 'preAddOrUpdateConfig'];
   }
}

function plugin_version_camerainput()
{
	return [
	   'name'         => __('Camera Input', 'camerainput'),
	   'version'      => PLUGIN_CAMERAINPUT_VERSION,
	   'author'       => 'Curtis Conard',
	   'license'      => 'GPLv2',
	   'homepage'     =>'https://github.com/cconard96/glpi-camerainput-plugin',
	   'requirements' => [
	      'glpi'   => [
	         'min' => PLUGIN_CAMERAINPUT_MIN_GLPI,
	         'max' => PLUGIN_CAMERAINPUT_MAX_GLPI
	      ]
	   ]
	];
}

