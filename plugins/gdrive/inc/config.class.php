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
if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access this file directly");
}

class PluginGdriveConfig extends CommonDBTM {
	static private $_instance = NULL;

	/**
	* Summary of canCreate
	* @return boolean
	*/
	static function canCreate() {
		return Session::haveRight('config', UPDATE);
	}

	/**
	* Summary of canView
	* @return boolean
	*/
	static function canView() {
		return Session::haveRight('config', READ);
	}

	/**
	* Summary of canUpdate
	* @return boolean
	*/
	static function canUpdate() {
		return Session::haveRight('config', UPDATE);
	}

	/**
	* Summary of getTypeName
	* @param mixed $nb plural
	* @return mixed
	*/
	static function getTypeName($nb=0) {
		return __('Gdrive setup','gdrive');
	}

	/**
	* Summary of getName
	* @param mixed $with_comment with comment
	* @return mixed
	*/
	function getName($with_comment=0) {
		return __('Gdrive','gdrive');
	}

	/**
	* Summary of getInstance
	* @return PluginProcessmakerConfig
	*/
	static function getInstance() {

		if (!isset(self::$_instance)) {
			self::$_instance = new self();
			if (!self::$_instance->getFromDB(1)) {
				self::$_instance->getEmpty();
			}
		}
		return self::$_instance;
	}

	static function getConfig() {
		$config = new self();
		$config->getFromDB(1);
		return $config;
	}

	/**
	* Summary of showConfigForm
	* @param mixed $item is the config
	* @return boolean
	*/
	static function showConfigForm($item) {
		global $LANG, $CFG_GLPI;

		$config = self::getInstance();

		$config->showFormHeader(['colspan' => 4]);

		echo "<tr class='tab_bg_1'>";
		echo "<td >".__('The Browser API key obtained from the Google API Console','gdrive')."</td><td >";
		echo "<input size='50' type='text' name='developer_key' value='".$config->fields['developer_key']."'>";
		echo "</td></tr>\n";

		echo "<tr class='tab_bg_1'>";
		echo "<td >".__('The Client ID obtained from the Google API Console','gdrive')."</td><td >";
		echo "<input size='50' type='text' name='client_id' value='".$config->fields['client_id']."'>";
		echo "</td></tr>\n";

		echo "<tr class='tab_bg_1'>";
		echo "<td >".__("See 'Project number' under 'IAM & Admin' > 'Settings'",'gdrive')."</td><td >";
		echo "<input type='text' name='app_id' value='".$config->fields['app_id']."'>";
		echo "</td></tr>\n";

		$config->showFormButtons(array('candel'=>false));

		return false;
	}

	function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

		if ($item->getType()=='Config') {
			return __('GDrive','gdrive');
		}
		return '';
	}

	static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

		if ($item->getType()=='Config') {
			self::showConfigForm($item);
		}
		return true;
	}
}