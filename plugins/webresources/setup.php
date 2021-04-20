<?php

/*
 -------------------------------------------------------------------------
 Web Resources Plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/glpi-webresources-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Web Resources Plugin for GLPI.
 Web Resources Plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Web Resources Plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Web Resources Plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_WEBRESOURCES_VERSION', '1.3.2');
define('PLUGIN_WEBRESOURCES_MIN_GLPI', '9.5.0');
define('PLUGIN_WEBRESOURCES_MAX_GLPI', '9.6.0');

function plugin_init_webresources()
{
	global $PLUGIN_HOOKS;
	$PLUGIN_HOOKS['csrf_compliant']['webresources'] = true;
   $plugin = new Plugin();
   if ($plugin->isInstalled('webresources') && $plugin->isActivated('webresources')) {
      $config = Config::getConfigurationValues('plugin:Webresources', ['menu']);
      if (Session::haveRight(PluginWebresourcesResource::$rightname, READ)) {
         $PLUGIN_HOOKS['menu_toadd']['webresources'] = [$config['menu'] ?? 'plugins' => 'PluginWebresourcesDashboard'];
      }
      Plugin::registerClass('PluginWebresourcesProfile', ['addtabon' => ['Profile']]);
      Plugin::registerClass('PluginWebresourcesConfig', ['addtabon' => 'Config']);
      $PLUGIN_HOOKS['post_item_form']['webresources'] = 'plugin_webresources_showPostItemForm';
      $PLUGIN_HOOKS['pre_item_update']['webresources'] = [
         'Supplier' => 'plugin_webresources_preupdateitem',
         'Entity' => 'plugin_webresources_preupdateitem',
      ];
      $PLUGIN_HOOKS['pre_item_purge']['webresources'] = 'plugin_webresources_preItemPurge';
      if ($_SESSION['glpipalette'] === 'darker') {
         $PLUGIN_HOOKS['add_css']['webresources'][] = 'css/webresources-dark.scss';
      } else {
         $PLUGIN_HOOKS['add_css']['webresources'][] = 'css/webresources.scss';
      }
   }
}

function plugin_version_webresources()
{
	return [
	      'name'         => __('Web Resources', 'webresources'),
	      'version'      => PLUGIN_WEBRESOURCES_VERSION,
	      'author'       => 'Curtis Conard',
	      'license'      => 'GPLv2+',
	      'homepage'     =>'https://github.com/cconard96/glpi-webresources-plugin',
	      'requirements' => [
	         'glpi'   => [
	            'min' => PLUGIN_WEBRESOURCES_MIN_GLPI,
	            'max' => PLUGIN_WEBRESOURCES_MAX_GLPI
	         ]
	      ]
	   ];
}
