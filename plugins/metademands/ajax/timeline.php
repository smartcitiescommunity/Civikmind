<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2020 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

include('../../../inc/includes.php');

Session::checkLoginUser();

if (!isset($_REQUEST['action'])) {
   exit;
}

if ($_REQUEST['action'] == 'change_task_state' || $_REQUEST['action'] == 'done_fail') {
   header("Content-Type: application/json; charset=UTF-8");
} else {
   header("Content-Type: text/html; charset=UTF-8");
}

//$objType    = $_REQUEST['parenttype']::getType();
//$foreignKey = $_REQUEST['parenttype']::getForeignKeyField();

switch ($_REQUEST['action']) {
   case "viewsubitem":

      Html::header_nocache();

      $metavalidation = new PluginMetademandsMetademandValidation();
      $metavalidation->viewValidation($_REQUEST);

      //      Html::ajaxFooter();
      break;
   case "validationMeta":
      $metavalidation = new PluginMetademandsMetademandValidation();
      $metavalidation->validateMeta($_REQUEST);
      Html::back();
      //      Html::ajaxFooter();
      break;
}
