<?php
/*
 -------------------------------------------------------------------------
 Gdrive plugin for GLPI
 Copyright (C) 2018 by the TICgal Team.

 https://github.com/pluginsGLPI/gdrive
 -------------------------------------------------------------------------

 LICENSE

 This file is part of the Gdrive plugin.

 Gdrive plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 Gdrive plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Gdrive. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   gdrive
 @author    the TICgal team
 @copyright Copyright (c) 2018 TICgal team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://tic.gal
 @since     2018
 ---------------------------------------------------------------------- */
define ('PLUGIN_GDRIVE_VERSION', '1.3.0');
// Minimal GLPI version, inclusive
define("PLUGIN_GDRIVE_MIN_GLPI", "9.5");
// Maximum GLPI version, exclusive
define("PLUGIN_GDRIVE_MAX_GLPI", "9.6");

function plugin_version_gdrive() {
	return ['name'       => 'GDrive',
		'version'        => PLUGIN_GDRIVE_VERSION,
		'author'         => '<a href="https://tic.gal">TICgal</a>',
		'homepage'       => 'https://tic.gal/en/project/gdrive-integration-glpi-google-drive/',
		'license'        => 'GPLv3+',
		'requirements'   => [
			'glpi'   => [
				'min' => PLUGIN_GDRIVE_MIN_GLPI,
				'max' => PLUGIN_GDRIVE_MAX_GLPI,
			]
		]
	];
}

/**
 * Check plugin's config before activation
 */
function plugin_gdrive_check_config($verbose=false) {
	return true;
}

function plugin_init_gdrive() {
	global $PLUGIN_HOOKS;

	if (Session::haveRightsOr("config", [READ, UPDATE])) {
		Plugin::registerClass('PluginGdriveConfig', ['addtabon' => 'Config']);
		$PLUGIN_HOOKS['config_page']['gdrive'] = 'front/config.form.php';
	}
	$PLUGIN_HOOKS['csrf_compliant']['gdrive'] = true;

	$PLUGIN_HOOKS['post_item_form']['gdrive'] = ['PluginGdriveTicket', 'postForm'];
	
}
