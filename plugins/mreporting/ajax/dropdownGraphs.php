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

if (strpos($_SERVER['PHP_SELF'], "dropdownGraphs.php")) {
   include ("../../../inc/includes.php");
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkLoginUser();

if ($_POST['graphname'] != -1) {
   $test = explode(";", $_POST['graphname']);
   if (isset($test[0])) {
      $_POST['classname']  = $test[0];
      $_POST['name']       = $test[1];

      $config= new PluginMreportingConfig();
      echo "&nbsp;<a href='".$config->getFormURL()."?name=".$_POST['name']."&classname=".$_POST['classname']."'>".__('Send')."</a>";
   }
}
