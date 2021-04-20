<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of useditemsexport.
 *
 * useditemsexport is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * useditemsexport is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright © 2015 - 2018 Teclib'
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/useditemsexport
 * @link      https://pluginsglpi.github.io/useditemsexport/
 * -------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Session::haveRight("config", UPDATE);

Html::header(PluginUseditemsexportConfig::getTypeName(1),
               $_SERVER['PHP_SELF'], "plugins", "useditemsexport", "config");

if (!isset($_GET["id"])) {
   $_GET["id"] = 1;
}

$PluginUseditemsexportConfig = new PluginUseditemsexportConfig();

if (isset($_POST["update"])) {
   $PluginUseditemsexportConfig->check($_POST["id"], UPDATE);
   $PluginUseditemsexportConfig->update($_POST);
   Html::back();
}

$PluginUseditemsexportConfig->showForm($_GET["id"]);

Html::footer();
