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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginManageentitiesConfig extends CommonDBTM {

   private static $instance;

   const DAY                 = 0;
   const HOUR                = 1;
   const NOPRICE             = 0;
   const PRICE               = 1;
   const REPORT_INTERVENTION = 0;
   const PERIOD_INTERVENTION = 1;

   function showForm() {
      global $DB, $CFG_GLPI;
      echo "<form name='form' method='post' action='" .
           Toolbox::getItemTypeFormURL('PluginManageentitiesConfig') . "'>";

      echo "<div align='center'><table class='tab_cadre_fixe'  cellspacing='2' cellpadding='2'>";
      echo "<tr><th colspan='2'>" . __('Options', 'manageentities') . "</th></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Save reports in glpi', 'manageentities') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("backup", $this->fields["backup"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Rubric by default for reports', 'manageentities') . "</td>";
      echo "<td>";
      Dropdown::show('DocumentCategory', ['name'  => "documentcategories_id",
                                          'value' => $this->fields["documentcategories_id"]]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Use of price', 'manageentities') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("useprice", $this->fields["useprice"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Configuration daily or hourly', 'manageentities') . "</td>";
      echo "<td>";
      $rand = Dropdown::showFromArray('hourorday', self::getConfigType(), ['value' => $this->fields["hourorday"]]);

      echo "<tr class='tab_bg_1 top'>";
      echo "<td><span id='title_show_hourorday'></span></td>";
      echo "<td><span id='value_show_hourorday'></span></td>";
      echo "</tr>";

      //js for load configuration
      Ajax::updateItem("title_show_hourorday", $CFG_GLPI["root_doc"] . "/plugins/manageentities/ajax/linkactions.php",
                       ['hourorday' => $this->fields["hourorday"], 'action' => 'title_show_hourorday'], "dropdown_hourorday$rand");
      Ajax::updateItem("value_show_hourorday", $CFG_GLPI["root_doc"] . "/plugins/manageentities/ajax/linkactions.php",
                       ['hourorday' => $this->fields["hourorday"], 'action' => 'value_show_hourorday'], "dropdown_hourorday$rand");
      //js for change configuration
      Ajax::updateItemOnSelectEvent("dropdown_hourorday$rand", "title_show_hourorday", $CFG_GLPI["root_doc"] . "/plugins/manageentities/ajax/linkactions.php",
                                    ['hourorday' => '__VALUE__', 'action' => 'title_show_hourorday']);
      Ajax::updateItemOnSelectEvent("dropdown_hourorday$rand", "value_show_hourorday", $CFG_GLPI["root_doc"] . "/plugins/manageentities/ajax/linkactions.php",
                                    ['hourorday' => '__VALUE__', 'action' => 'value_show_hourorday']);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Only public task are visible on intervention report', 'manageentities') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("use_publictask", $this->fields["use_publictask"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Allow periods on the same interval of dates', 'manageentities') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("allow_same_periods", $this->fields["allow_same_periods"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Configuring the client side view', 'manageentities') . "</td>";
      echo "<td>";
      self::dropdownConfigChoiceIntervention("choice_intervention", $this->fields["choice_intervention"]);
      echo "</td></tr>";

      $contractstate  = new PluginManageentitiesContractState();
      $contractstates = $contractstate->find();
      $states         = [];
      foreach ($contractstates as $key => $val) {
         $states[$key] = $val['name'];
      }

      echo "<tr class='tab_bg_1 top'><td>" . __('List of default statuses for general monitoring', 'manageentities') . "</td>";
      echo "<td>";
      if ($this->fields["contract_states"] == NULL) {
         Dropdown::showFromArray("contract_states", $states, ['multiple' => true, 'width' => 200, 'value' => $this->fields["contract_states"]]);
      } else {
         Dropdown::showFromArray("contract_states", $states, ['multiple' => true, 'width' => 200, 'values' => json_decode($this->fields["contract_states"], true)]);
      }
      echo "</td></tr>";

      $query = "SELECT  `glpi_users`.*, `glpi_plugin_manageentities_businesscontacts`.`id` as users_id
        FROM `glpi_plugin_manageentities_businesscontacts`, `glpi_users`
        WHERE `glpi_plugin_manageentities_businesscontacts`.`users_id`=`glpi_users`.`id`
        GROUP BY `glpi_plugin_manageentities_businesscontacts`.`users_id`";

      $result = $DB->query($query);

      $users = [];
      while ($data = $DB->fetchAssoc($result)) {
         $users[$data['id']] = $data['realname'] . " " . $data['firstname'];
      }
      echo "<tr class='tab_bg_1 top'><td>" . __('Default Business list for general monitoring', 'manageentities') . "</td>";
      echo "<td>";
      if ($this->fields["business_id"] == NULL) {
         Dropdown::showFromArray("business_id", $users, ['multiple' => true, 'width' => 200, 'value' => $this->fields["business_id"]]);
      } else {
         Dropdown::showFromArray("business_id", $users, ['multiple' => true, 'width' => 200, 'values' => json_decode($this->fields["business_id"], true)]);
      }
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Display comments from the company in the CRI', 'manageentities') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("comment", $this->fields["comment"]);
      echo "</td></tr>";

      echo "<tr><th colspan='2'>" . __('CRI generation form', 'manageentities') . "</th></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Use Non-accomplished tasks informations', 'manageentities') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("non_accomplished_tasks", $this->fields["non_accomplished_tasks"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Display PDF', 'manageentities') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("get_pdf_cri", $this->fields["get_pdf_cri"]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('State of ticket created', 'manageentities') . "</td>";
      echo "<td>";
      $status = Ticket::getAllStatusArray();
      Dropdown::showFromArray("ticket_state",$status,["value" => $this->fields["ticket_state"]]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Default duration', 'manageentities') . "</td>";
      echo "<td>";
      $rand = Dropdown::showTimeStamp("default_duration", ['value' => $this->fields["default_duration"],
         'min' => 0,
         'max' => 50 * HOUR_TIMESTAMP,
         'emptylabel' => __('Specify an end date')]);
      echo "<br><div id='date_end$rand'></div>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Default time AM', 'manageentities') . "</td>";
      echo "<td>";
      $rand = Dropdown::showTimeStamp("default_time_am", ['value' => $this->fields["default_time_am"],
         'min' => 0,
         'emptylabel' => "0h",
         'max' => 23.5 * HOUR_TIMESTAMP,
         'step' => MINUTE_TIMESTAMP * 30]);
      echo "<br><div id='date_end$rand'></div>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Default time PM', 'manageentities') . "</td>";
      echo "<td>";
      $rand = Dropdown::showTimeStamp("default_time_pm", ['value' => $this->fields["default_time_pm"],
         'min' => 0,
         'emptylabel' => "0h",
         'max' => 23.5 * HOUR_TIMESTAMP,
         'step' => MINUTE_TIMESTAMP * 30]);
      echo "<br><div id='date_end$rand'></div>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1 top'><td>" . __('Disable creation date in header of PDF', 'manageentities') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("disable_date_header", $this->fields["disable_date_header"]);
      echo "</td></tr>";

      echo "<input type='hidden' name='id' value='1'>";
      echo "<tr class='tab_bg_1 center'><td colspan='2'>
            <span style=\"font-weight:bold; color:red\">" . __('Warning: changing the configuration daily or hourly impacts the types of contract', 'manageentities') . "</td></span></tr>";
      echo "<tr class='tab_bg_2 center'><td colspan='2'><input type=\"submit\" name=\"update_config\" class=\"submit\"
         value=\"" . _sx('button', 'Save') . "\" ></td></tr>";

      echo "</table></div>";
      Html::closeForm();
   }

   function prepareInputForUpdate($input) {
      if (isset($input['contract_states'])) {
         $input['contract_states'] = json_encode($input['contract_states']);
      } else {
         $input['contract_states'] = 'NULL';
      }
      if (isset($input['business_id'])) {
         $input['business_id'] = json_encode($input['business_id']);
      } else {
         $input['business_id'] = 'NULL';
      }
      return $input;
   }

   /*function showDetails() {
      echo "<form name='form' method='post' action='".
         Toolbox::getItemTypeFormURL('PluginManageentitiesConfig')."'>";

      echo "<div align='center'><table class='tab_cadre_fixe'  cellspacing='2' cellpadding='2'>";
      

      echo "<input type='hidden' name='id' value='1'>";

      echo "<tr><th colspan='2'><input type=\"submit\" name=\"update_config\" class=\"submit\"
         value=\""._sx('button', 'Save')."\" ></th></tr>";

      echo "</table></div>";
      Html::closeForm();
   }
   
   
   function showFormAddress(){
       echo "<form name='form' method='post' action='".
         Toolbox::getItemTypeFormURL('PluginManageentitiesConfig')."'>";

      echo "<div align='center'><table class='tab_cadre_fixe'  cellspacing='2' cellpadding='2'>";
      echo "<tr><th colspan='2'>".__('Address')."</th></tr>";
   
      echo "<tr class='tab_bg_1'>";
      echo "<td class='center'>";
      
      echo "<textarea cols='80' rows='8' name='company_address' id='company_address' >";
      
      echo $this->fields['company_address'];
      echo "</textarea></td></tr>\n";
      
      echo "</td>";
      echo "</tr>";

      echo "<tr><th colspan='2'><input type='submit' name='update_config' class='submit'
         value='"._sx('button', 'Save')."' ></th></tr>";

      echo "<input type='hidden' name='id' value='1'>";
        
        
      echo "</table></div>";
      Html::closeForm();
   }*/

   function showFormCompany() {
      //add a company
      PluginManageentitiesCompany::addNewCompany(['title' => __('Add a company', 'manageentities')]);
      Html::closeForm();

      $plugin_company = new PluginManageentitiesCompany();
      $result         = $plugin_company->find();
      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr><th colspan='2'>" . _n('Company', 'Companies', 2, 'manageentities') . "</th></tr>";

      foreach ($result as $data) {
         echo "<tr>";
         echo "<td>";
         $link_period = Toolbox::getItemTypeFormURL("PluginManageentitiesCompany");
         echo "<a class='ganttWhite' href='" . $link_period . "?id=" . $data["id"] . "'>";
         $plugin_company->getFromDB($data["id"]);
         echo $plugin_company->getNameID() . "</a>";
         echo "</td>";
         echo "</tr>";
      }
      echo "<tr>";
      echo "</tr>";
      echo "</table>";
      echo "</div>";
   }

   function isCommentCri() {
      $config = new PluginManageentitiesConfig();
      $config->GetFromDB(1);
      return $config->fields['comment'];
   }

   function getConfigType() {
      return ([self::DAY  => _x('periodicity', 'Daily'),
               self::HOUR => __('Hourly', 'manageentities')]);
   }

   function dropdownConfigChoiceIntervention($name, $value = 0) {
      $configTypes = [self::REPORT_INTERVENTION => _n('Intervention report', 'Intervention reports', 2, 'manageentities'),
                      self::PERIOD_INTERVENTION => _n('Period of contract', 'Periods of contract', 2, 'manageentities')];

      if (!empty($configTypes)) {
         return Dropdown::showFromArray($name, $configTypes, ['value' => $value]);
      } else {
         return false;
      }
   }

   public static function getInstance() {
      if (!isset(self::$instance)) {
         $temp = new PluginManageentitiesConfig();
         $temp->getFromDB('1');
         self::$instance = $temp;
      }

      return self::$instance;
   }

}