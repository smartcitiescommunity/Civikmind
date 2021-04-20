<?php

/*
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2019 by the Metademands Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

/**
 * Update from 2.6.4 to 2.7.1
 * Glpi upgrade to 9.5
 * @return bool for success (will die for most error)
 * */

ini_set("memory_limit", "-1");
ini_set("max_execution_time", 0);
chdir(dirname($_SERVER["SCRIPT_FILENAME"]));

if (!defined('GLPI_ROOT')) {
   define('GLPI_ROOT', realpath('../../..'));
}

include_once (GLPI_ROOT."/inc/autoload.function.php");
include_once (GLPI_ROOT."/inc/db.function.php");
include_once (GLPI_ROOT."/inc/based_config.php");
include_once (GLPI_CONFIG_DIR."/config_db.php");
include_once (GLPI_ROOT."/inc/define.php");

$GLPI = new GLPI();
$GLPI->initLogger();
Config::detectRootDoc();

if (is_writable(GLPI_SESSION_DIR)) {
   Session::setPath();
} else {
   die("Can't write in ".GLPI_SESSION_DIR."\r\n");
}
Session::start();
$_SESSION['glpi_use_mode'] = 0;
Session::loadLanguage();

Global $DB;
if (!$DB->connected) {
   die("No DB connection\r\n");
}
$CFG_GLPI['notifications_ajax']    = 0;
$CFG_GLPI['notifications_mailing'] = 0;
$CFG_GLPI['use_notifications']     = 0;

function update270_271() {
   global $DB;

   $metademands           = new PluginMetademandsMetademand();
   $metademands           = $metademands->find();
   $transient_metademands = [];

   foreach ($metademands as $metademand) {
      $itilcat                                                     = [$metademand['itilcategories_id']];
      $transient_metademands[$metademand['id']]['itil_categories'] = json_encode($itilcat);
      $transient_metademands[$metademand['id']]['metademands_id']  = $metademand['id'];
   }
   $query_alter_itilcat_type = "ALTER TABLE `glpi_plugin_metademands_metademands` CHANGE `itilcategories_id` `itilcategories_id` VARCHAR(255) NOT NULL DEFAULT '[]';";
   $DB->query($query_alter_itilcat_type);

   foreach ($transient_metademands as $transient_metademand) {
      $query_update_itilcat = "UPDATE `glpi_plugin_metademands_metademands` 
                                       SET `glpi_plugin_metademands_metademands`.`itilcategories_id` = '" . $transient_metademand['itil_categories'] . "'
                                                   WHERE `id` = '" . $transient_metademand['metademands_id'] . "';";
      $DB->query($query_update_itilcat);
   }
}
