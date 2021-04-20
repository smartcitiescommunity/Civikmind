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
/**
 * Install all necessary elements for the plugin
 *
 * @return boolean True if success
 */
function plugin_gdrive_install() {
	global $DB;
	$version   = plugin_version_gdrive();
	$migration = new Migration($version['version']);

	// Parse inc directory
	foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
		// Load *.class.php files and get the class name
		if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
			$classname = 'PluginGdrive' . ucfirst($matches[1]);
			include_once($filepath);
			// If the install method exists, load it
			if (method_exists($classname, 'install')) {
				$classname::install($migration);
			}
		}
	}
	if (!$DB->TableExists("glpi_plugin_gdrive_configs")) {
		$query = "  CREATE TABLE `glpi_plugin_gdrive_configs` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`developer_key` VARCHAR(250) NOT NULL DEFAULT 'xxxxxxxYYYYYYYY-12345678',
				`client_id` VARCHAR(250) NOT NULL DEFAULT '1234567890-abcdefghijklmnopqrstuvwxyz.apps.googleusercontent.com',
				`app_id` VARCHAR(50) NOT NULL DEFAULT '1234567890',
				PRIMARY KEY (`id`)
			)
			COLLATE='utf8_general_ci'
			ENGINE=InnoDB;";

		$DB->query($query) or die("error creating glpi_plugin_gdrive_configs" . $DB->error());

		// add configuration singleton
		$query = "INSERT INTO `glpi_plugin_gdrive_configs` (`id`) VALUES (1);";
		$DB->query( $query ) or die("error creating default record in glpi_plugin_gdrive_configs" . $DB->error());
	}
	return true;
}

/**
 * Uninstall previously installed elements of the plugin
 *
 * @return boolean True if success
 */
function plugin_gdrive_uninstall() {
	global $DB;
	// Parse inc directory
	foreach (glob(dirname(__FILE__).'/inc/*') as $filepath) {
		// Load *.class.php files and get the class name
		if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
			$classname = 'PluginGdrive' . ucfirst($matches[1]);
			include_once($filepath);
			// If the install method exists, load it
			if (method_exists($classname, 'uninstall')) {
				$classname::uninstall();
			}
		}
	}
	//Delete table
	$DB->query("DROP TABLE IF EXISTS  `glpi_plugin_gdrive_configs`") or die ($DB->error());
	
	return true;
}