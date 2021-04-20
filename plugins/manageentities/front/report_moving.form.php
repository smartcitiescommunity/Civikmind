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

include('../../../inc/includes.php');

Html::header(__('Entities portal', 'manageentities'), '', "plugins", "manageentities");

if (isset($_GET)) $tab = $_GET;
if (empty($tab) && isset($_POST)) $tab = $_POST;

if (empty($_POST["date1"]) && empty($_POST["date2"])) {
   $lastday = cal_days_in_month(CAL_GREGORIAN, date("m"), date("Y"));
   if (date("d") == $lastday) {
      $_POST["date2"] = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
      $_POST["date1"] = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
   } else {
      $month          = date("m");
      $lastday        = $month == 1 ? 31 : cal_days_in_month(CAL_GREGORIAN, $month - 1, date("Y"));
      $_POST["date2"] = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, $lastday, date("Y")));
      $_POST["date1"] = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
   }
}
if ($_POST["date1"] != "" && $_POST["date2"] != "" && strcmp($_POST["date2"], $_POST["date1"]) < 0) {
   $tmp            = $_POST["date1"];
   $_POST["date1"] = $_POST["date2"];
   $_POST["date2"] = $tmp;
}


Report::title();

$PluginManageentitiesEntity = new PluginManageentitiesEntity();
if ($PluginManageentitiesEntity->canView() || Session::haveRight("config", UPDATE)) {

   if (isset($_POST["send"])) {

      echo "<div align='center'><form action=\"report_moving.form.php\" method=\"post\">";
      echo "<table class='tab_cadre'><tr class='tab_bg_2'><td class='right'>";
      echo __('Start date') . " :</td><td>";
      Html::showDateField("date1", ['value' => $_POST["date1"]]);
      echo "<td class='right'>" . __('End date') . " :</td><td>";
      Html::showDateField("date2", ['value' => $_POST["date2"]]);
      echo "</td></tr>";
      //stats Entity
      echo "<tr class='tab_bg_2'><td class='right'>";
      echo __('Entity') . " :</td><td>";

      $entity    = new Entity();
      $data      = [Dropdown::EMPTY_VALUE];
      $condition = ['id' => $_SESSION["glpiactiveentities"]];
      $data      = $entity->find($condition);


      foreach ($data as $val) {
         $elements[$val['entities_id']] = Dropdown::getDropdownName("glpi_entities", $val['entities_id']);
      }

      Dropdown::showFromArray('entities_id', $elements,
                              ['values' => isset($_POST['entities_id']) ? $_POST['entities_id'] : [], 'multiple' => true, 'entity' => $_SESSION['glpiactiveentities']]);

      echo "<td class='right'>" . __('Task category') . " :</td><td>";
      TaskCategory::dropdown(['name' => 'category_id', 'value' => isset($_POST['category_id']) ? $_POST['category_id'] : 0]);
      echo "</td></tr>";
      echo "<tr class='tab_bg_2'></td><td colspan='4' class='center'><input type=\"submit\" class='button' name=\"send\" Value=\"" . _sx('button', 'Post') . "\" /></td></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
      $report = new PluginManageentitiesReport();
      $report->showMovingReports($_POST["entities_id"], $_POST['category_id'], $_POST["date1"], $_POST["date2"]);

   } else {

      echo "<div align='center'><form action=\"report_moving.form.php\" method=\"post\">";
      echo "<table class='tab_cadre'><tr class='tab_bg_2'><td class='right'>";
      echo __('Start date') . " :</td><td>";
      Html::showDateField("date1", ['value' => $_POST["date1"]]);
      echo "<td class='right'>" . __('End date') . " :</td><td>";
      Html::showDateField("date2", ['value' => $_POST["date2"]]);
      echo "</td></tr>";
      //stats Entity
      echo "<tr class='tab_bg_2'><td class='right'>";
      echo __('Entity') . " :</td><td>";

      $entity    = new Entity();
      $data      = [Dropdown::EMPTY_VALUE];
      $condition = ['id' => $_SESSION["glpiactiveentities"]];
      $data      = $entity->find($condition);


      foreach ($data as $val) {
         $elements[$val['entities_id']] = Dropdown::getDropdownName("glpi_entities", $val['entities_id']);
      }
      Dropdown::showFromArray('entities_id', $elements,
                              ['multiple' => true, 'entity' => $_SESSION['glpiactiveentities']]);

      echo "<td class='right'>" . __('Task category') . " :</td><td>";
      TaskCategory::dropdown(['name' => 'category_id', 'value' => isset($_POST['category_id']) ? $_POST['category_id'] : 0]);
      echo "</td></tr>";
      echo "<tr class='tab_bg_2'></td><td colspan='4' class='center'><input type=\"submit\" class='button' name=\"send\" Value=\"" . _sx('button', 'Post') . "\" /></td></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";

   }

} else {
   Html::displayRightError();
}

Html::footer();