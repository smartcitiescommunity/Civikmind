<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 moreticket plugin for GLPI
 Copyright (C) 2013-2016 by the moreticket Development Team.

 https://github.com/InfotelGLPI/moreticket
 -------------------------------------------------------------------------

 LICENSE

 This file is part of moreticket.

 moreticket is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 moreticket is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with moreticket. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginMoreticketConfig
 */
class PluginMoreticketConfig extends CommonDBTM {

   static $rightname = "plugin_moreticket";

   /**
    * @param bool $update
    *
    * @return null|PluginMoreticketConfig
    */
   static function getConfig($update = false) {
      static $config = null;

      if (is_null($config)) {
         $config = new self();
      }
      if ($update) {
         $config->getFromDB(1);
      }
      return $config;
   }

   /**
    * PluginMoreticketConfig constructor.
    */
   function __construct() {
      global $DB;

      if ($DB->tableExists($this->getTable())) {
         $this->getFromDB(1);
      }
   }

   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getTypeName($nb = 0) {
      return __("Setup");
   }

   /**
    * @param string $interface
    *
    * @return array
    */
   function getRights($interface = 'central') {

      $values = parent::getRights();

      unset($values[CREATE], $values[DELETE], $values[PURGE]);
      return $values;
   }

   function showForm() {

      $this->getFromDB(1);
      echo "<div class='center'>";
      echo "<form name='form' method='post' action='" . $this->getFormURL() . "'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>" . __("Setup") . "</th></tr>";
      echo "<tr><th colspan='2'>" . __("Ticket waiting", "moreticket") . "</th></tr>";
      echo "<input type='hidden' name='id' value='1'>";

      echo "<tr class='tab_bg_1'>
            <td>" . __("Use waiting process", "moreticket") . "</td><td>";
      Dropdown::showYesNo("use_waiting", $this->fields["use_waiting"], -1,
                          ['on_change' => 'hide_show_waiting(this.value);']);
      echo "</td>";
      echo "</tr>";

      echo Html::scriptBlock("
         function hide_show_waiting(val) {
            var display = (val == 0) ? 'none' : '';
            td = ($(\"td[id='show_waiting']\"));
            td.each(function (index, value) {
               td[index].style.display = display;
            });
         }");

      $style = ($this->useWaiting()) ? "" : "style='display: none '";

      echo "<tr class='tab_bg_1'>
               <td id='show_waiting' $style>" . __("Report date is mandatory", "moreticket") . "</td>";
      echo "<td id='show_waiting' $style>";
      Dropdown::showYesNo("date_report_mandatory", $this->fields["date_report_mandatory"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>
               <td id='show_waiting' $style>" . __("Waiting type is mandatory", "moreticket") . "</td>";
      echo "<td id='show_waiting' $style>";
      Dropdown::showYesNo("waitingtype_mandatory", $this->fields["waitingtype_mandatory"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>
               <td id='show_waiting' $style>" . __("Waiting reason is mandatory", "moreticket") . "</td>";
      echo "<td id='show_waiting' $style>";
      Dropdown::showYesNo("waitingreason_mandatory", $this->fields["waitingreason_mandatory"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='2'>" . __("Ticket resolution and close", "moreticket") . "</th></tr>";
      echo "<tr class='tab_bg_1'>
            <td>" . __("Use solution process", "moreticket") . "</td><td>";
      Dropdown::showYesNo("use_solution", $this->fields["use_solution"], -1,
                          ['on_change' => 'hide_show_solution(this.value);']);
      echo "</td>";
      echo "</tr>";

      echo Html::scriptBlock("
         function hide_show_solution(val) {
                        var display = (val == 0) ? 'none' : '';
            td = ($(\"td[id='show_solution']\"));
            td.each(function (index, value) {
               td[index].style.display = display;
            });
         }");

      $style = ($this->useSolution()) ? "" : "style='display: none '";

      echo "<tr class='tab_bg_1'>
               <td id='show_solution' $style>" . __("Solution type is mandatory", "moreticket") . "</td>";
      echo "<td id='show_solution' $style>";
      Dropdown::showYesNo("solutiontype_mandatory", $this->fields["solutiontype_mandatory"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>
            <td>" . __("Close ticket informations", "moreticket") . "</td><td>";
      Dropdown::showYesNo("close_informations", $this->fields["close_informations"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>
            <td>" . __("Status used to display solution bloc", "moreticket") . "</td><td>";

      $solution_status = $this->getSolutionStatus($this->fields["solution_status"]);

      foreach ([Ticket::CLOSED, Ticket::SOLVED] as $status) {
         $checked = isset($solution_status[$status]) ? 'checked' : '';
         echo "<input type='checkbox' name='solution_status[" . $status . "]' value='1' $checked>&nbsp;";
         echo Ticket::getStatus($status) . "<br>";
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>
            <td>" . __("Add a followup on immediate ticket closing", "moreticket") . "</td><td>";
      Dropdown::showYesNo("close_followup", $this->fields["close_followup"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>
            <td>" . __("Use the 'Duration' field in the add solution interface", "moreticket") . "</td><td>";
      Dropdown::showYesNo("use_duration_solution", $this->fields["use_duration_solution"], -1,
                          ['on_change' => 'hide_show_solution(this.value);']);
      echo "</td>";
      echo "</tr>";

      echo Html::scriptBlock("
         function hide_show_solution(val) {
            var display = (val == 0) ? 'none' : '';
            document.getElementById('mandatory_solution').style.display = display;
         }");

      $style = ($this->useDurationSolution()) ? "" : "style='display: none '";
      echo "<tr class='tab_bg_1' id='mandatory_solution' $style>
            <td>" . __("Mandatory 'Duration' field", "moreticket") . "</td><td>";
      Dropdown::showYesNo("is_mandatory_solution", $this->fields["is_mandatory_solution"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='2'>" . __("Ticket urgency", "moreticket") . "</th></tr>";
      echo "<tr class='tab_bg_1'>
            <td>" . __("Use a justification of the urgency field", "moreticket") . "</td><td>";
      Dropdown::showYesNo("urgency_justification", $this->fields["urgency_justification"], -1,
                          ['on_change' => 'hide_show_urgency(this.value);']);
      echo "</td>";
      echo "</tr>";

      echo Html::scriptBlock("
         function hide_show_urgency(val) {
            var display = (val == 0) ? 'none' : '';
            document.getElementById('show_urgency_td1').style.display = display;
            document.getElementById('show_urgency_td2').style.display = display;
         }");

      $style = ($this->useUrgency()) ? "" : "style='display: none '";
      echo "<tr class='tab_bg_1'>";
      echo "<td id='show_urgency_td1' $style>";
      echo __("Urgency impacted justification for the field", "moreticket");
      echo "</td>";

      $dbu = new DbUtils();

      echo "<td id='show_urgency_td2' $style>";
      $urgency_ids = self::getValuesUrgency();
      Dropdown::showFromArray('urgency_ids',
                              $urgency_ids,
                              ['multiple' => true,
                                    'values'   => $dbu->importArrayFromDB($this->fields["urgency_ids"])]);
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='2'>" . __('Ticket waiting',"moreticket") . "</th></tr>";
      echo "<tr class='tab_bg_1'>
            <td>" . __("Use the option ticket waiting in ticket followup", "moreticket") . "</td><td>";
      Dropdown::showYesNo("use_question", $this->fields["use_question"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='submit' name='update' value=\"" . _sx("button", "Post") . "\" class='submit' >";
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }

   /**
    * @param $input
    *
    * @return array|mixed
    */
   function getSolutionStatus($input) {

      $solution_status = [];

      if (!empty($input)) {
         $solution_status = json_decode($input, true);
      }

      return $solution_status;
   }

   /**
    * @return mixed
    */
   function useWaiting() {
      return $this->fields['use_waiting'];
   }

   /**
    * @return mixed
    */
   function mandatoryReportDate() {
      return $this->fields['date_report_mandatory'];
   }

   /**
    * @return mixed
    */
   function mandatoryWaitingType() {
      return $this->fields['waitingtype_mandatory'];
   }

   /**
    * @return mixed
    */
   function mandatoryWaitingReason() {
      return $this->fields['waitingreason_mandatory'];
   }

   /**
    * @return mixed
    */
   function useSolution() {
      return $this->fields['use_solution'];
   }

   /**
    * @return mixed
    */
   function mandatorySolutionType() {
      return $this->fields['solutiontype_mandatory'];
   }

   /**
    * @return mixed
    */
   function solutionStatus() {
      return $this->fields["solution_status"];
   }

   /**
    * @return mixed
    */
   function closeInformations() {
      return $this->fields["close_informations"];
   }

   /**
    * @return mixed
    */
   function closeFollowup() {
      return $this->fields["close_followup"];
   }

   /**
    * @return mixed
    */
   function useUrgency() {
      return $this->fields['urgency_justification'];
   }

   /**
    * @return array
    */
   function getUrgency_ids() {
      $dbu = new DbUtils();
      return $dbu->importArrayFromDB($this->fields['urgency_ids']);
   }

   /**
    * @return mixed
    */
   function useDurationSolution() {
      if(isset($this->fields['use_duration_solution'])){
         return $this->fields['use_duration_solution'];
      }
      return false;
   }

   /**
    * @return mixed
    */
   function isMandatorysolution() {
      return $this->fields['is_mandatory_solution'];
   }
   function useQuestion(){
      return $this->fields['use_question'];
   }

   /**
    * @return array
    */
   static function getValuesUrgency() {
      global $CFG_GLPI;

      $URGENCY_MASK_FIELD = 'urgency_mask';
      $values             = [];

      if (isset($CFG_GLPI[$URGENCY_MASK_FIELD])) {
         if ($CFG_GLPI[$URGENCY_MASK_FIELD] & (1 << 5)) {
            $values[5] = CommonITILObject::getUrgencyName(5);
         }

         if ($CFG_GLPI[$URGENCY_MASK_FIELD] & (1 << 4)) {
            $values[4] = CommonITILObject::getUrgencyName(4);
         }

         $values[3] = CommonITILObject::getUrgencyName(3);

         if ($CFG_GLPI[$URGENCY_MASK_FIELD] & (1 << 2)) {
            $values[2] = CommonITILObject::getUrgencyName(2);
         }

         if ($CFG_GLPI[$URGENCY_MASK_FIELD] & (1 << 1)) {
            $values[1] = CommonITILObject::getUrgencyName(1);
         }
      }
      return $values;
   }
}
