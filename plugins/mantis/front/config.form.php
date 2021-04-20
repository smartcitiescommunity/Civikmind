<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of mantis.
 *
 * mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2018 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/mantis
 * @link      https://pluginsglpi.github.io/mantis/
 * -------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Session::haveRight("config", UPDATE);

Html::header(PluginMantisConfig::getTypeName(1),
               $_SERVER['PHP_SELF'], "plugins", "mantis", "config");

if (!isset($_GET["id"])) {
   $_GET["id"] = 1;
}

$PluginMantisConfig = new PluginMantisConfig();

if (isset($_POST["update"])) {
   $PluginMantisConfig->check($_POST["id"], UPDATE);
   $PluginMantisConfig->update($_POST);
   Html::back();
}

$PluginMantisConfig->showForm($_GET["id"]);

Html::footer();