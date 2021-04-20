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

class PluginManageentitiesContract extends CommonDBTM {

   const MANAGEMENT_NONE      = 0;
   const MANAGEMENT_QUARTERLY = 1;
   const MANAGEMENT_ANNUAL    = 2;

   const CONTRACT_TYPE_NULL = 0;
   //time mode 
   const CONTRACT_TYPE_HOUR         = 1;
   const CONTRACT_TYPE_INTERVENTION = 2;
   const CONTRACT_TYPE_UNLIMITED    = 3;
   //Daily mode
   const CONTRACT_TYPE_AT      = 4;
   const CONTRACT_TYPE_FORFAIT = 5;

   static $rightname = 'plugin_manageentities';

   static function getTypeName($nb = 1) {

      return __('Type of management', 'manageentities');
   }

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   function prepareInputForAdd($input) {

      if (isset($input['date_renewal'])
          && empty($input['date_renewal']))
         $input['date_renewal'] = 'NULL';
      if (isset($input['date_signature'])
          && empty($input['date_signature']))
         $input['date_signature'] = 'NULL';

      if (isset($input['contract_added'])
          && ($input['contract_added'] === "on"
          || ($input['contract_added'] && $input['contract_added'] != 0 ))) {
         $input['contract_added'] = 1;
      } else {
         $input['contract_added'] = 0;
      }
      if (isset($input['refacturable_costs'])
          && ($input['refacturable_costs'] === "on"
          || ($input['refacturable_costs'] && $input['refacturable_costs'] != 0 ))) {
         $input['refacturable_costs'] = 1;
      } else {
         $input['refacturable_costs'] = 0;
      }

      return $input;
   }

   function prepareInputForUpdate($input) {

      if (isset($input['date_renewal'])
          && empty($input['date_renewal']))
         $input['date_renewal'] = 'NULL';
      if (isset($input['date_signature'])
          && empty($input['date_signature']))
         $input['date_signature'] = 'NULL';

      if (isset($input['contract_added'])
          && ($input['contract_added'] === "on"
          || ($input['contract_added'] && $input['contract_added'] != 0 ))) {
         $input['contract_added'] = 1;
      } else {
         $input['contract_added'] = 0;
      }
      if (isset($input['refacturable_costs'])
          && ($input['refacturable_costs'] === "on"
          || ($input['refacturable_costs'] && $input['refacturable_costs'] != 0 ))) {
         $input['refacturable_costs'] = 1;
      } else {
         $input['refacturable_costs'] = 0;
      }
      return $input;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'Contract'
          && !isset($withtemplate) || empty($withtemplate)) {

         $dbu                = new DbUtils();
         $restrict           = ["`entities_id`"  => $item->fields['entities_id'],
                                "`contracts_id`" => $item->fields['id']];
         $pluginContractDays = $dbu->countElementsInTable("glpi_plugin_manageentities_contractdays", $restrict);
         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(__('Contract detail', 'manageentities'), $pluginContractDays);
         }
         return __('Contract detail', 'manageentities');
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if (get_class($item) == 'Contract') {

         self::showForContract($item);

         if (self::canView()) {
            PluginManageentitiesContractDay::showForContract($item);
         }
      }
      return true;
   }

   function addContractByDefault($id, $entities_id) {
      global $DB;

      $query  = "SELECT *
        FROM `" . $this->getTable() . "`
        WHERE `entities_id` IN (" . $entities_id . ") ";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number) {
         while ($data = $DB->fetchArray($result)) {

            $query_nodefault = "UPDATE `" . $this->getTable() . "`
            SET `is_default` = 0 WHERE `id` = " . $data["id"];
            $DB->query($query_nodefault);
         }
      }

      $query_default = "UPDATE `" . $this->getTable() . "`
        SET `is_default` = 1 WHERE `id` = $id";
      $DB->query($query_default);
   }

   static function showForContract(Contract $contract) {
      $rand    = mt_rand();
      $canView = $contract->can($contract->fields['id'], READ);
      $canEdit = $contract->can($contract->fields['id'], UPDATE);
      $config  = PluginManageentitiesConfig::getInstance();

      if (!$canView) return false;

      $restrict        = ["`glpi_plugin_manageentities_contracts`.`entities_id`"  => $contract->fields['entities_id'],
                          "`glpi_plugin_manageentities_contracts`.`contracts_id`" => $contract->fields['id']];
      $dbu             = new DbUtils();
      $pluginContracts = $dbu->getAllDataFromTable("glpi_plugin_manageentities_contracts", $restrict);
      $pluginContract  = reset($pluginContracts);

      if ($canEdit) {
         echo "<form method='post' name='contract_form$rand' id='contract_form$rand'
               action='" . Toolbox::getItemTypeFormURL('PluginManageentitiesContract') . "'>";
      }

      echo "<div align='spaced'><table class='tab_cadre_fixe center'>";

      echo "<tr><th colspan='4'>" . PluginManageentitiesContract::getTypeName(0) . "</th></tr>";

      echo "<tr class='tab_bg_1'><td>" . __('Date of signature', 'manageentities') . "</td>";
      echo "<td>";
      $sign = (isset($pluginContract['date_signature']) ? $pluginContract['date_signature'] : NULL);
      Html::showDateField("date_signature", ['value' => $sign]);
      echo "</td><td>" . __('Date of renewal', 'manageentities') . "</td><td>";
      $ren = (isset($pluginContract['date_renewal']) ? $pluginContract['date_renewal'] : NULL);
      Html::showDateField("date_renewal", ['value' => $ren]);
      echo "</td></tr>";

      if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
         echo "<tr class='tab_bg_1'><td>" . __('Mode of management', 'manageentities') . "</td>";
         echo "<td>";
         PluginManageentitiesContract::dropdownContractManagement("management", $pluginContract['management']);
         echo "</td><td>" . __('Type of service contract', 'manageentities') . "</td><td>";
         PluginManageentitiesContract::dropdownContractType("contract_type", $pluginContract['contract_type']);
         echo "</td></tr>";
      }

      if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY && $config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
         echo "<tr class='tab_bg_1'><td>" . __('Contract is imported in GLPI', 'manageentities') . "</td>";
         echo "<td>";
         $sel_contract = "";
         if (isset($pluginContract['contract_added']) && $pluginContract['contract_added'] == "1") {
            $sel_contract = "checked";
         }
         echo "<input type='checkbox' name='contract_added' $sel_contract>";
         echo "</td><td colspan='2'></td>";
         echo "</td></tr>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Show on global GANTT') . "</td>";
      echo "<td>";
      $gantt = (isset($pluginContract['show_on_global_gantt']) ? $pluginContract['show_on_global_gantt'] : 0);
      Dropdown::showYesNo("show_on_global_gantt", $gantt);
      echo "</td>";
      echo "<td>" . __('Refacturable costs', 'manageentities') . "</td>";
      echo "<td>";
      $sel = "";
      if (isset($pluginContract['refacturable_costs']) && $pluginContract['refacturable_costs'] == "1") {
         $sel = "checked";
      }
      echo "<input type='checkbox' name='refacturable_costs' $sel>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Movement management', 'manageentities') . "</td>";
      echo "<td>";
      $mov  = (isset($pluginContract['moving_management']) ? $pluginContract['moving_management'] : 0);
      $rand = Dropdown::showYesNo("moving_management", $mov, -1, ['on_change' => 'changemovement();']);
      echo Html::scriptBlock("
         function changemovement(){
            if($('#dropdown_moving_management$rand').val() != 0){
               $('#movementlabel').show();
               $('#movement').show();
            } else {
               $('#movementlabel').hide();
               $('#movement').hide();
            }
         }
         changemovement();
      ");
      echo "</td>";
      echo "<td><div id='movementlabel'>" . __('Duration of moving', 'manageentities') . "</div></td>";

      echo "<td><div id='movement'>";
      $duration = (isset($pluginContract['duration_moving']) ? $pluginContract['duration_moving'] : NULL);
      Dropdown::showTimeStamp('duration_moving', ['value'           => $duration,
                                                  'addfirstminutes' => true]);
      echo "</div></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<input type='hidden' name='contracts_id' value='" . $contract->fields['id'] . "'>";
      echo "<input type='hidden' name='entities_id' value='" . $contract->fields['entities_id'] . "'>";

      if ($canEdit) {
         if (empty($pluginContract)) {
            echo "<td class='center' colspan='4'>";
            echo "<input type='submit' name='addcontract' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
         } else {
            echo "<input type='hidden' name='id' value='" . $pluginContract['id'] . "'>";
            echo "<td class='center' colspan='2'>";
            echo "<input type='submit' name='updatecontract' value='" . _sx('button', 'Update') . "' class='submit'>";
            echo "</td><td class='center' colspan='2'>";
            echo "<input type='submit' name='delcontract' value='" . _sx('button', 'Delete permanently') . "' class='submit'>";
         }
         echo "</td>";
      }
      echo "</tr>";
      echo "</table></div>";
      if ($canEdit) {
         Html::closeForm();
      }
   }


   function showContracts($instID) {
      global $DB, $CFG_GLPI;

      PluginManageentitiesEntity::showManageentitiesHeader(__('Associated assistance contracts', 'manageentities'));

      $entitiesID = "'" . implode("', '", $instID) . "'";
      $config     = PluginManageentitiesConfig::getInstance();

      $query  = "SELECT `glpi_contracts`.*,
                       `" . $this->getTable() . "`.`contracts_id`,
                       `" . $this->getTable() . "`.`management`,
                       `" . $this->getTable() . "`.`contract_type`,
                       `" . $this->getTable() . "`.`is_default`,
                       `" . $this->getTable() . "`.`id` as myid
        FROM `" . $this->getTable() . "`, `glpi_contracts`
        WHERE `" . $this->getTable() . "`.`contracts_id` = `glpi_contracts`.`id`
        AND `" . $this->getTable() . "`.`entities_id` IN (" . $entitiesID . ")
        ORDER BY `glpi_contracts`.`begin_date`, `glpi_contracts`.`name`";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number) {
         echo "<form method='post' action=\"./entity.php\">";
         echo "<div align='center'><table class='tab_cadre_me center'>";
         echo "<tr><th>" . __('Name') . "</th>";
         echo "<th>" . _x('phone', 'Number') . "</th>";
         echo "<th>" . __('Comments') . "</th>";
         if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
            echo "<th>" . __('Mode of management', 'manageentities') . "</th>";
            echo "<th>" . __('Type of service contract', 'manageentities') . "</th>";
         } else if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY && $config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
            echo "<th>" . __('Type of service contract', 'manageentities') . "</th>";
         }
         echo "<th>" . __('Used by default', 'manageentities') . "</th>";
         if ($this->canCreate() && sizeof($instID) == 1)
            echo "<th>&nbsp;</th>";
         echo "</tr>";

         $used = [];

         while ($data = $DB->fetchArray($result)) {
            $used[] = $data["contracts_id"];

            echo "<tr class='" . ($data["is_deleted"] == '1' ? "_2" : "") . "'>";
            echo "<td><a href=\"" . $CFG_GLPI["root_doc"] . "/front/contract.form.php?id=" . $data["contracts_id"] . "\">" . $data["name"] . "";
            if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) echo " (" . $data["contracts_id"] . ")";
            echo "</a></td>";
            echo "<td class='center'>" . $data["num"] . "</td>";
            echo "<td class='center'>" . nl2br($data["comment"]) . "</td>";
            if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
               echo "<td class='center'>" . self::getContractManagement($data["management"]) . "</td>";
               echo "<td class='center'>" . self::getContractType($data['contract_type']) . "</td>";
            } else if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY && $config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
               echo "<td class='center'></td>";
               //               echo "<td class='center'>".self::getContractType($data['contract_type'])."</td>";
            }
            echo "<td class='center'>";
            if (sizeof($instID) == 1) {
               if ($data["is_default"]) {
                  echo __('Yes');
               } else {
                  Html::showSimpleForm($CFG_GLPI['root_doc'] . '/plugins/manageentities/front/entity.php',
                                       'contractbydefault',
                                       __('No'),
                                       ['myid' => $data["myid"], 'entities_id' => $_SESSION["glpiactive_entity"]]);
               }
            } else {
               echo Dropdown::getYesNo($data["is_default"]);
            }
            echo "</td>";
            if ($this->canCreate() && sizeof($instID) == 1) {
               echo "<td class='center' class='tab_bg_2'>";

               Html::showSimpleForm($CFG_GLPI['root_doc'] . '/plugins/manageentities/front/entity.php',
                                    'deletecontracts',
                                    _x('button', 'Delete permanently'),
                                    ['id' => $data["myid"]]);
               echo "</td>";
            }
            echo "</tr>";

         }

         if ($this->canCreate() && sizeof($instID) == 1) {
            if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
               echo "<tr class='tab_bg_1'><td colspan='6' class='center'>";
            } else if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY && $config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
               echo "<tr class='tab_bg_1'><td colspan='5' class='center'>";
            } else {
               echo "<tr class='tab_bg_1'><td colspan='4' class='center'>";
            }
            echo "<input type='hidden' name='entities_id' value='" . $_SESSION["glpiactive_entity"] . "'>";
            Dropdown::show('Contract', ['name' => "contracts_id",
                                        'used' => $used]);
            echo "<a href='" . $CFG_GLPI['root_doc'] . "/front/setup.templates.php?itemtype=Contract&add=1' target='_blank'><i title=\"" . _sx('button', 'Add') . "\" class=\"far fa-plus-square\" style='cursor:pointer; margin-left:2px;'></i></a>";
            echo "</td><td class='center'><input type='submit' name='addcontracts' value=\"" . _sx('button', 'Add') . "\" class='submit'></td>";
            echo "</tr>";
         }
         echo "</table></div>";
         Html::closeForm();

      } else {
         echo "<form method='post' action=\"./entity.php\">";
         echo "<div align='center'><table class='tab_cadrehov center'>";
         echo "<tr><th colspan='3'>" . __('Associated assistance contracts', 'manageentities') . ":</th></tr>";
         echo "<tr><th>" . __('Name') . "</th>";
         echo "<th>" . _x('phone', 'Number') . "</th>";
         echo "<th>" . __('Comments') . "</th>";

         echo "</tr>";
         if ($this->canCreate()) {
            echo "<tr class='tab_bg_1'><td class='center'>";
            echo "<input type='hidden' name='entities_id' value=" . $_SESSION["glpiactive_entity"] . ">";
            Dropdown::show('Contract', ['name' => "contracts_id"]);
            echo "<a href='" . $CFG_GLPI['root_doc'] . "/front/setup.templates.php?itemtype=Contract&add=1' target='_blank'>
            <i title=\"" . _sx('button', 'Add') . "\" class=\"far fa-plus-square\" style='cursor:pointer; margin-left:2px;'></i></a>";
            echo "</td><td class='center'><input type='submit' name='addcontracts' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td><td></td>";
            echo "</tr>";
         }
         echo "</table></div>";
         Html::closeForm();
      }
   }

   /**
    * Dropdown list contract management
    *
    * @param type $name
    * @param type $value
    * @param type $rand
    *
    * @return boolean
    */
   static function dropdownContractManagement($name, $value = 0, $rand = null) {
      $contractManagements = [self::MANAGEMENT_NONE      => Dropdown::EMPTY_VALUE,
                              self::MANAGEMENT_QUARTERLY => __('Quarterly', 'manageentities'),
                              self::MANAGEMENT_ANNUAL    => __('Annual', 'manageentities')];

      if (!empty($contractManagements)) {
         if ($rand == null) {
            return Dropdown::showFromArray($name, $contractManagements, ['value' => $value]);
         } else {
            return Dropdown::showFromArray($name, $contractManagements, ['value' => $value, 'rand' => $rand]);
         }
      } else {
         return false;
      }
   }

   /**
    * Return the name of contract management
    *
    * @param type $value
    *
    * @return string
    */
   static function getContractManagement($value) {
      switch ($value) {
         case self::MANAGEMENT_NONE :
            return Dropdown::EMPTY_VALUE;
         case self::MANAGEMENT_QUARTERLY :
            return __('Quarterly', 'manageentities');
         case self::MANAGEMENT_ANNUAL :
            return __('Annual', 'manageentities');
         default :
            return "";
      }
   }

   /**
    * dropdown list of the types of contract
    *
    * @param type $name
    * @param type $value
    * @param type $rand
    * @param type $on_change
    *
    * @return boolean
    */
   static function dropdownContractType($name, $value = 0, $rand = null) {
      $config = PluginManageentitiesConfig::getInstance();

      if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
         $contractTypes = [self::CONTRACT_TYPE_NULL         => Dropdown::EMPTY_VALUE,
                           self::CONTRACT_TYPE_HOUR         => __('Hourly', 'manageentities'),
                           self::CONTRACT_TYPE_INTERVENTION => __('By intervention', 'manageentities'),
                           self::CONTRACT_TYPE_UNLIMITED    => __('Unlimited')];
      } else if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY && $config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
         $contractTypes = [self::CONTRACT_TYPE_NULL    => Dropdown::EMPTY_VALUE,
                           self::CONTRACT_TYPE_AT      => __('Technical Assistance', 'manageentities'),
                           self::CONTRACT_TYPE_FORFAIT => __('Package', 'manageentities')];
      }

      if (!empty($contractTypes)) {
         if ($rand == null) {
            return Dropdown::showFromArray($name, $contractTypes, ['value' => $value]);
         } else {
            return Dropdown::showFromArray($name, $contractTypes, ['value' => $value, 'rand' => $rand]);
         }
      } else {
         return false;
      }
   }

   /**
    * Returns the name of the type of contract
    *
    * @param type $value
    *
    * @return string
    */
   static function getContractType($value) {
      switch ($value) {
         case self::CONTRACT_TYPE_NULL :
            return Dropdown::EMPTY_VALUE;
         case self::CONTRACT_TYPE_HOUR :
            return __('Hourly', 'manageentities');
         case self::CONTRACT_TYPE_INTERVENTION :
            return __('By intervention', 'manageentities');
         case self::CONTRACT_TYPE_UNLIMITED :
            return __('Unlimited');
         case self::CONTRACT_TYPE_AT :
            return __('Technical Assistance', 'manageentities');
         case self::CONTRACT_TYPE_FORFAIT :
            return __('Package', 'manageentities');
         default :
            return "";
      }
   }

   /**
    * Return the unit
    *
    * @param type $config
    * @param type $value
    *
    * @return type
    */
   static function getUnitContractType($config, $value) {
      if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
         switch ($value) {
            case self::CONTRACT_TYPE_HOUR :
               return _n('Hour', 'Hours', 2);
            case self::CONTRACT_TYPE_INTERVENTION :
               return _n('Intervention', 'Interventions', 2, 'manageentities');
            case self::CONTRACT_TYPE_UNLIMITED :
               return __('Unlimited');
         }
      } else {
         return _n('Day', 'Days', 2);
      }

   }

   static function checkRemainingOpenContractDays($contracts_id) {
      global $DB;

      $query = "SELECT count(*) as count
                FROM `glpi_plugin_manageentities_contractdays`
                LEFT JOIN `glpi_plugin_manageentities_contractstates`
                    ON (`glpi_plugin_manageentities_contractdays`.`plugin_manageentities_contractstates_id` = `glpi_plugin_manageentities_contractstates`.`id`)
                WHERE `glpi_plugin_manageentities_contractdays`.`contracts_id` = " . $contracts_id . " 
                AND `glpi_plugin_manageentities_contractstates`.`is_active` = 1";

      $result = $DB->query($query);
      while ($data = $DB->fetchArray($result)) {
         if ($data['count'] > 0) {
            return true;
         }
      }

      return false;
   }

}