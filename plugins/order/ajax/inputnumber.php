<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2015 Teclib'.

 http://glpi-project.org

 based on GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
*/

include ('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkLoginUser();

if (isset($_POST['name'])) {
   $step = 1;
   if (!isset($_REQUEST['force_integer'])) {
      $step = PLUGIN_ORDER_NUMBER_STEP;
   }

   $class = "";
   if (isset($_REQUEST['class'])) {
      $class = "class='".$_REQUEST['class']."'";
   }

   $min = 0;
   if (isset($_REQUEST['min'])) {
      if (isset($_REQUEST['force_integer']) && $_REQUEST['force_integer']) {
         $min = (int)$_REQUEST['min'];
      } else {
         $min = (float)$_REQUEST['min'];
      }
   }

   $data = Html::cleanInputText(Toolbox::clean_cross_side_scripting_deep(rawurldecode(stripslashes($_POST["data"]))));

   echo "<input type='number' step='$step' min='$min' name='".$_POST['name']."' value='$data' $class>";
}
