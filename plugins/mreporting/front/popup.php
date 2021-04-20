<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Mreporting plugin for GLPI
 Copyright (C) 2003-2011 by the mreporting Development Team.

 https://forge.indepnet.net/projects/mreporting
 -------------------------------------------------------------------------

 LICENSE

 This file is part of mreporting.

 mreporting is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 mreporting is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with mreporting. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ("../../../inc/includes.php");

Session::checkLoginUser();

if (isset($_GET["classname"])) {
   $_POST["classname"] = $_GET["classname"];
}

Html::popHeader(__("General Report - ODT", 'mreporting'), $_SERVER['PHP_SELF']);

$common = new PluginMreportingCommon();
$common->showExportForm($_POST);

echo "<div class='center'>";
echo "<br>";
echo "<a href='javascript:window.close()'>".__("Back")."</a>";
echo "</div>";
Html::popFooter();
