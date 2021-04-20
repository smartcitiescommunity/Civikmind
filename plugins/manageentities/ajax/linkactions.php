<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

if (isset($_POST["action"])) {
   switch ($_POST["action"]) {
      case 'title_show_hourorday' :
         $config = PluginManageentitiesConfig::getInstance();
         switch ($_POST["hourorday"]) {
            case PluginManageentitiesConfig::DAY :
               echo __('Number of hours by day', 'manageentities');

               break;
            case PluginManageentitiesConfig::HOUR :
               echo __('Only ticket accepted are taking into account for consumption calculation', 'manageentities');

               break;
         }
         break;
      case 'value_show_hourorday' :
         $config = PluginManageentitiesConfig::getInstance();
         switch ($_POST["hourorday"]) {
            case PluginManageentitiesConfig::DAY :
               Html::autocompletionTextField($config, "hourbyday", ['size' => "5"]);
               echo "<input type='hidden' name='needvalidationforcri' value='0'>";

               break;
            case PluginManageentitiesConfig::HOUR :
               Dropdown::showYesNo("needvalidationforcri", $config->fields["needvalidationforcri"]);
               echo "<input type='hidden' name='hourbyday' value='0'>";

               break;
         }
         break;
   }
}
