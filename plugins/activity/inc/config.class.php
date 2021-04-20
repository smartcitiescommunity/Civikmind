<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginActivityConfig extends CommonDBTM {

   static $rightname = "plugin_activity";

   static function getTypeName($nb = 0) {

      return _n('Activity', 'Activity', $nb, 'activity');
   }

   function showForm () {

      if (!$this->canView()) {
         return false;
      }
      if (!$this->canCreate()) {
         return false;
      }

      $used_entities = [];
      $dbu = new DbUtils();
      $dataConfig = $dbu->getAllDataFromTable($this->getTable());
      if ($dataConfig) {
         foreach ($dataConfig as $field) {
            $used_entities[] = $field['entities_id'];
         }
      }

      echo "<form name='form' method='post' action='".
         Toolbox::getItemTypeFormURL('PluginActivityConfig')."'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='3'>".__('Define internal helpdesk', 'activity')."</th></tr>";

      echo "<tr class='tab_bg_1'>";

      // Dropdown entity
      echo "<td>".__('Entity')."</td>";
      echo "<td>";
      Dropdown::show('Entity', ['name' => 'entities_id',
                                     'used' => $used_entities]);
      echo "</td>";

      // Checkbox is_internal_helpdesk
      echo "<td>";
      Html::autocompletionTextField($this, "name",
                                     ['name'   => 'name',
                                           'size'   => 70]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td class='center' colspan='3'><input type=\"submit\" name=\"update\" class=\"submit\"
         value=\""._sx('button', 'Save')."\" ></td></tr>";

      echo "</table></div>";
      Html::closeForm();

      if ($dataConfig) {
         $this->listItems($dataConfig);
      }

   }

   private function listItems($fields) {
      if (!$this->canView()) {
         return false;
      }

      $canedit = $this->canUpdate();

      $rand = mt_rand();
      $number = count($fields);

      echo "<div class='center'>";

      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams
            = ['container'
                        => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='4'>".__('Show internal helpdesk', 'activity')."</th></tr>";
      echo "<tr>";
      echo "<th width='10'>";
      if ($canedit && $number) {
         echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
      }
      echo "</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Type')."</th>";
      echo "</tr>";
      foreach ($fields as $field) {
         echo "<tr class='tab_bg_1'>";
         //CHECKBOX
         echo "<td width='10'>";
         $sel = "";
         if (isset($_GET["select"])&&$_GET["select"]=="all") {
            $sel="checked";
         }
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field["id"]);
         }
         echo "</td>";
         //DATA LINE
         echo "<td>".Dropdown::getDropdownName('glpi_entities', $field['entities_id'])."</td>";
         echo "<td>";
         echo $field['name'];
         echo "</td>";
         echo "</tr>";
      }

      if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }


   /**
    * getConfigFromDB : get all configs in the database
    *
    * @global type $DB
    * @param type $ID : configs_id
    * @return boolean
    */
   static function getConfigFromDB($entities_id) {

      $restrict = ["entities_id" => $entities_id];
      $dbu = new DbUtils();
      $dataConfig = $dbu->getAllDataFromTable("glpi_plugin_activity_configs", $restrict);

      return $dataConfig;
   }
}