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
include ( "../../../inc/includes.php");

$config = new PluginGdriveConfig();
if (isset($_POST["update"])) {
	$config->check($_POST['id'], UPDATE);

	// save
	$config->update($_POST);


	Html::back();

} else if (isset($_POST["refresh"])) {
	$config->refresh($_POST); // used to refresh process list, task category list
	Html::back();
}

Html::redirect($CFG_GLPI["root_doc"]."/front/config.form.php?forcetab=".urlencode('PluginGdriveConfig$1'));