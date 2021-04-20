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

class PluginManageentitiesContractDay extends CommonDBTM {

   static $rightname = 'plugin_manageentities';

   // From CommonDBTM
   public $dohistory = true;

   static function getTypeName($nb = 0) {
      return _n('Period of contract', 'Periods of contract', $nb, 'manageentities');
   }

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   function rawSearchOptions() {
      $config = PluginManageentitiesConfig::getInstance();

      $tab = [];

      $tab[] = [
         'id'   => 'common',
         'name' => PluginManageentitiesContractDay::getTypeName(1)
      ];

      $tab[] = [
         'id'            => '1',
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __('Name'),
         'datatype'      => 'itemlink',
         'itemlink_type' => $this->getType()
      ];

      $tab[] = [
         'id'       => '2',
         'table'    => $this->getTable(),
         'field'    => 'begin_date',
         'name'     => __('Start date'),
         'datatype' => 'date'
      ];

      $tab[] = [
         'id'       => '3',
         'table'    => $this->getTable(),
         'field'    => 'end_date',
         'name'     => __('End date'),
         'datatype' => 'date'
      ];

      $tab[] = [
         'id'       => '4',
         'table'    => $this->getTable(),
         'field'    => 'nbday',
         'name'     => __('Initial credit', 'manageentities'),
         'datatype' => 'decimal'
      ];

      //      $tab[5]['table']    = 'glpi_plugin_manageentities_critypes';
      //      $tab[5]['field']    = 'name';
      //      $tab[5]['name']     = __('Intervention type', 'manageentities');
      //      $tab[5]['datatype'] = 'dropdown';

      $tab[] = [
         'id'       => '6',
         'table'    => $this->getTable(),
         'field'    => 'report',
         'name'     => __('Postponement', 'manageentities'),
         'datatype' => 'decimal'
      ];

      $tab[] = [
         'id'       => '7',
         'table'    => 'glpi_contracts',
         'field'    => 'name',
         'name'     => __('Contract'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '8',
         'table'    => 'glpi_plugin_manageentities_contractstates',
         'field'    => 'name',
         'name'     => __('State of contract', 'manageentities'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'               => '9',
         'table'            => $this->getTable(),
         'field'            => 'id',
         'credit_remaining' => true,
         'name'             => __('Credit remaining', 'manageentities'),
         'datatype'         => 'specific'
      ];

      if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
         $tab[] = [
            'id'       => '10',
            'table'    => $this->getTable(),
            'field'    => 'contract_type',
            'name'     => __('Type of service contract', 'manageentities'),
            'datatype' => 'specific'
         ];
      }

      $tab[] = [
         'id'    => '30',
         'table' => $this->getTable(),
         'field' => 'id',
         'name'  => __('ID')
      ];

      if (Session::getCurrentInterface() == 'central') {
         $tab[] = [
            'id'       => '80',
            'table'    => 'glpi_entities',
            'field'    => 'completename',
            'name'     => _n('Entity', 'Entities', 1),
            'datatype' => 'dropdown'
         ];
      }

      return $tab;
   }

   /**
    * @param $field
    * @param $values
    * @param $options   array
    **/
   static function getSpecificValueToDisplay($field, $values, $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'id':
            if (isset($options['searchopt']['credit_remaining']) && $options['searchopt']['credit_remaining']) {
               $contractDay                 = new self();
               $contract                    = $contractDay->find(['id' => $values['id']]);
               $contract                    = reset($contract);
               $contract['contractdays_id'] = $values['id'];
               $resultCriDetail             = PluginManageentitiesCriDetail::getCriDetailData($contract);
               $nbtheoricaldays             = 0;
               if ($resultCriDetail['resultOther']['default_criprice'] > 0) {
                  $nbtheoricaldays = $resultCriDetail['resultOther']['reste_montant'] / $resultCriDetail['resultOther']['default_criprice'];
               }
               return Html::formatNumber($nbtheoricaldays, false);
            }
            break;
         case 'contract_type':
            $contract = new PluginManageentitiesContract();
            return $contract->getContractType($values[$field]);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * Display tab for each contractDay
    * */
   function defineTabs($options = []) {

      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab('PluginManageentitiesCriDetail', $ong, $options);
      $this->addStandardTab('PluginManageentitiesCriPrice', $ong, $options);
      $this->addStandardTab('PluginManageentitiesInterventionSkateholder', $ong, $options);
      $this->addStandardTab('Document', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   /**
    *
    * @param type  $plugin_manageentities_critypes_id
    * @param type  $contracts_id
    * @param type  $entities_id
    *
    * @return boolean
    * @global type $DB
    *
    */
   function getFromDBbyTypeAndContract($plugin_manageentities_critypes_id, $contracts_id, $entities_id) {
      global $DB;

      $query = "SELECT *
      FROM `" . $this->getTable() . "`
      WHERE `plugin_manageentities_critypes_id` = '" . $plugin_manageentities_critypes_id . "'
      AND `contracts_id` = '" . $contracts_id . "'
      AND `entities_id` = '" . $entities_id . "' ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   /**
    * Add number day in contractday
    *
    * @param type $values
    */
   function addNbDay($values) {

      if ($this->getFromDBbyTypeAndContract($values["plugin_manageentities_critypes_id"], $values["contracts_id"], $values["entities_id"])) {

         $this->update([
                          'id'          => $this->fields['id'],
                          'nbday'       => $values["nbday"],
                          'entities_id' => $values["entities_id"]]);
      } else {

         $this->add([
                       'plugin_manageentities_critypes_id' => $values["plugin_manageentities_critypes_id"],
                       'contracts_id'                      => $values["contracts_id"],
                       'nbday'                             => $values["nbday"],
                       'entities_id'                       => $values["entities_id"]]);
      }
   }

   /**
    * Display the contractday form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return boolean item found
    * */
   function showForm($ID, $options = []) {
      global $CFG_GLPI;

      //validation des droits
      if (!$this->canView())
         return false;

      $config      = PluginManageentitiesConfig::getInstance();
      $conso       = 0;
      $contract_id = 0;
      $contract    = new Contract();

      if (isset($options['contract_id'])) {
         $contract_id = $options['contract_id'];
      }

      if ($ID > 0) {
         $this->check($ID, READ);
         $contract_id = $this->fields["contracts_id"];
         $contract->getFromDB($contract_id);

      } else {
         // Create item
         $input = ['contract_id' => $contract_id];
         $this->check(-1, UPDATE, $input);
         $contract->getFromDB($contract_id);
         $options['entities_id'] = $contract->fields['entities_id'];
      }

      // Set session saved if exists
      $this->setSessionValues();

      //init values
      if (empty($this->fields['nbday'])) {
         $this->fields['nbday'] = 0;
      }
      if (empty($this->fields['report'])) {
         $this->fields['report'] = 0;
      }

      // Fix to get contract navigate list when comming from followup or monthly
      if (isset($options['showFromPlugin']) && $options['showFromPlugin']) {
         $_SERVER['REQUEST_URI'] = $CFG_GLPI["root_doc"] . "/front/contract.form.php?id=" . $contract_id;
         Session::initNavigateListItems("PluginManageentitiesContractDay", $contract->getName());
         Session::addToNavigateListItems("PluginManageentitiesContractDay", $ID);
      }

      $restrict        = ["`glpi_plugin_manageentities_contracts`.`entities_id`"  => $contract->fields['entities_id'],
                          "`glpi_plugin_manageentities_contracts`.`contracts_id`" => $contract->fields['id']];
      $dbu             = new DbUtils();
      $pluginContracts = $dbu->getAllDataFromTable("glpi_plugin_manageentities_contracts", $restrict);
      $pluginContract  = reset($pluginContracts);

      $unit = PluginManageentitiesContract::getUnitContractType($config, $pluginContract['contract_type']);

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Contract') . "</td>";
      $link          = Toolbox::getItemTypeFormURL('Contract');
      $contract_name = "<a href='" . $link . "?id=" . $contract->fields['id'] . "'>" .
                       $contract->fields['name'] . "</a>";
      echo "<td>" . $contract_name . "</td>";

      if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
         echo "<td>" . __('Type of service contract', 'manageentities') . "<span style='color:red;'>&nbsp;*&nbsp;</span></td><td>";
         PluginManageentitiesContract::dropdownContractType("contract_type", $this->fields['contract_type']);
      } else {
         echo "</td><td colspan='2'></td>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . PluginManageentitiesContractDay::getTypeName(1) . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name", ['value' => $this->fields["name"]]);
      echo "</td>";

      if (($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) || ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
         echo "<td>" . __('Postponement', 'manageentities') . "</td>";
         echo "<td><input type='text' name='report' value='" .
              Html::formatNumber($this->fields["report"]) . "'size='5'>";
         echo "&nbsp;" . $unit;
         echo "</td>";
      } else {
         echo "<td></td><td></td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Begin date') . "<span style='color:red;'>&nbsp;*&nbsp;</span></td>";
      echo "<td>";
      Html::showDateField("begin_date", ['value' => $this->fields["begin_date"]]);
      echo "</td>";
      echo "<td>" . __('End date') . "</td><td>";
      Html::showDateField("end_date", ['value' => $this->fields["end_date"]]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Initial credit', 'manageentities') . "</td>";
      if (($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) ||
          ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
         echo "<td><input type='text' name='nbday' value='" .
              Html::formatNumber($this->fields["nbday"]) . "'size='5'>";
      } else {
         echo "<td>";
      }
      echo "&nbsp;" . $unit;
      echo "</td>";
      echo "<td>" . __('State of contract', 'manageentities') . "<span style='color:red;'>&nbsp;*&nbsp;</span></td><td>";
      Dropdown::show('PluginManageentitiesContractState', ['value'  => $this->fields['plugin_manageentities_contractstates_id'],
                                                           'entity' => $this->fields["entities_id"]]);
      echo "</td></tr>";

      echo "<input type='hidden' name='contracts_id' value='" . $contract_id . "'>";
      echo "<input type='hidden' name='contract_id' value='" . $contract_id . "'>";
      echo "<input type='hidden' name='entities_id' value='" . $contract->fields['entities_id'] . "'>";

      // We get all cri detail data
      $this->fields['contractdays_id'] = $this->fields['id'];
      $resultCriDetail                 = PluginManageentitiesCriDetail::getCriDetailData($this->fields);

      foreach ($resultCriDetail['result'] as $dataCriDetail) {
         //Conso
         $conso += $dataCriDetail['conso'];
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Total consummated', 'manageentities') . "</td>";
      echo "<td>";
      echo Html::formatNumber($conso);
      if (($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) ||
          ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
         echo "&nbsp;" . $unit;
      } else {
         echo "&nbsp;" . PluginManageentitiesContract::getUnitContractType($config, PluginManageentitiesContract::CONTRACT_TYPE_HOUR);
      }
      echo "</td>";

      if (($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) ||
          ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
         echo "<td>" . __('Total remaining', 'manageentities') . "</td>";
         echo "<td>";
         echo Html::formatNumber($resultCriDetail['resultOther']['reste']);
         echo "&nbsp;" . $unit;
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __('Total exceeding', 'manageentities') . "</td>";
         echo "<td>";
         echo Html::formatNumber($resultCriDetail['resultOther']['depass']);
         echo "&nbsp;" . $unit;
         echo "</td>";
      }

      if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
         //         echo "<td>".__('Intervention type by default', 'manageentities')."</td>";
         //         echo "<td>";
         //         Dropdown::show('PluginManageentitiesCriType', ['value' => $this->fields['plugin_manageentities_critypes_id'],
         //             'entity' => $this->fields["entities_id"]]);
         //         echo "</td>";

         echo "<td>" . __('Guaranteed package', 'manageentities') . "</td>";
         echo "<td>";
         echo Html::formatNumber($resultCriDetail['resultOther']['forfait']);
         echo "</td>";
      }

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Already charged', 'manageentities') . "</td>";
      echo "<td>";

      $this->fields['charged'] == 1 ? $isCharged = "checked='checked'" : $isCharged = '';
      echo "<input type='checkbox' name='charged' id='charged' " . $isCharged . " />";

      echo "</td>";
      if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {

         echo "<td>" . __('Remaining total (amount)', 'manageentities') . "</td>";
         echo "<td>";
         echo Html::formatNumber($resultCriDetail['resultOther']['reste_montant']);
         echo "</td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Comments') . " ";
      echo "</td><td>";
      echo "<textarea cols='40' rows='5' name='comment'>" . $this->fields["comment"] . "</textarea>";
      echo "</td><td></td><td></td></tr>";
      echo "</tr>";


      $this->showFormButtons($options);

      return true;
   }

   /**
    * Add a new contract day
    *
    * @param Contract $contract
    * @param type     $options
    */
   static function addNewContractDay(Contract $contract, $options = []) {
      $contract_id = $contract->fields['id'];
      $canEdit     = $contract->can($contract_id, UPDATE);
      $addButton   = "";

      if (Session::haveRight('plugin_manageentities', UPDATE) && $canEdit) {
         $rand = mt_rand();

         $addButton = "<form method='post' name='contractDays_form'.$rand.'' id='contractDays_form" . $rand . "'
               action='" . Toolbox::getItemTypeFormURL('PluginManageentitiesContractDay') . "?contract_id=" . $contract->fields['id'] . "'>
               <input type='hidden' name='contract_id' value='" . $contract_id . "'>
               <input type='hidden' name='id' value=''>
               <input type='submit' name='addperiod' value='" . _sx('button', 'Add') . "' class='submit'>";
      }

      if (isset($options['title'])) {
         echo '<table class="tab_cadre_fixe">';
         echo '<tr><th>' . $options['title'] . '</th></tr>';
         echo '<tr class="tab_bg_1">
               <td class="center">';
         echo $addButton;
         Html::closeForm();
         echo '</td></tr></table>';

      } else {
         echo '<tr class="tab_bg_1">
               <td class="center" colspan="' . $options['colspan'] . '">';
         echo $addButton;
         Html::closeForm();
         echo '</td></tr>';
      }
   }

   static function showForContract(Contract $contract) {
      $rand      = mt_rand();
      $canView   = $contract->can($contract->fields['id'], READ);
      $canEdit   = $contract->can($contract->fields['id'], UPDATE);
      $canCreate = $contract->can($contract->fields['id'], CREATE);
      $config    = PluginManageentitiesConfig::getInstance();

      if (!$canView) {
         return false;
      }

      if ($canCreate) {
         self::addNewContractDay($contract, ['title' => __('Add a contract day', 'manageentities')]);
      }

      $restrict = ["`entities_id`"  => $contract->fields['entities_id'],
                   "`contracts_id`" => $contract->fields['id'],
                   'ORDER' => '`date_signature` ASC'];

      $restrict_days = ["`entities_id`"  => $contract->fields['entities_id'],
                   "`contracts_id`" => $contract->fields['id'],
                   'ORDER' => '`begin_date` ASC, `name`'];

      $dbu             = new DbUtils();
      $pluginContracts = $dbu->getAllDataFromTable("glpi_plugin_manageentities_contracts", $restrict);
      $pluginContract  = reset($pluginContracts);

      $pluginContractDays = $dbu->getAllDataFromTable("glpi_plugin_manageentities_contractdays", $restrict_days);
      if (count($pluginContractDays)) {
         echo "<div class='center'>";
         if ($canEdit) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);
         }
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='12'>" . PluginManageentitiesContractDay::getTypeName(1) . "</th>";
         echo "</tr>";

         echo "<tr>";
         echo "<th width='10'>";
         if ($canEdit) {
            echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
         }
         echo "</th>";
         echo "<th>" . PluginManageentitiesContractDay::getTypeName(1) . "</th>";
         if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
            echo "<th>" . __('Type of contract', 'manageentities') . "</th>";
         }
         echo "<th>" . __('Begin date') . "</th>";
         echo "<th>" . __('End date') . "</th>";
         if (($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) ||
             ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
            echo "<th>" . __('Initial credit', 'manageentities') . "</th>";
         }
         echo "<th>" . __('State of contract', 'manageentities') . "</th>";
         if (($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) ||
             ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
            echo "<th>" . __('Postponement', 'manageentities') . "</th>";
         }
         echo "<th>" . __('Total consummated', 'manageentities') . "</th>";
         if (($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) ||
             ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
            echo "<th>" . __('Total remaining', 'manageentities') . "</th>";
            echo "<th>" . __('Total exceeding', 'manageentities') . "</th>";
         }
         echo "<th>" . PluginManageentitiesCriPrice::getTypeName() . "</th>";
         echo "</tr>";

         Session::initNavigateListItems("PluginManageentitiesContractDay", $contract->getName());

         foreach ($pluginContractDays as $pluginContractDay) {
            $contractday = new PluginManageentitiesContractDay();
            $contractday->getFromDB($pluginContractDay["id"]);

            Session::addToNavigateListItems("PluginManageentitiesContractDay", $pluginContractDay["id"]);

            echo "<tr class='tab_bg_1'>";
            echo "<td width='10'>";
            if ($canEdit) {
               Html::showMassiveActionCheckBox(__CLASS__, $pluginContractDay['id']);
            }
            echo "</td>";
            // Name
            echo "<td>" . $contractday->getLink() . "</td>";
            //type of contract
            if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
               echo "<td>" . PluginManageentitiesContract::getContractType($contractday->fields['contract_type']) . "</td>";
            }
            // Begin
            echo "<td>" . Html::convDate($pluginContractDay['begin_date']) . "</td>";
            // End
            echo "<td>" . Html::convDate($pluginContractDay['end_date']) . "</td>";
            // Nb day
            echo "<td>";
            if (($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) ||
                ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
               echo Html::formatNumber($pluginContractDay['nbday']);
               echo "</td><td>";
            }
            // State
            echo Dropdown::getDropdownName('glpi_plugin_manageentities_contractstates', $pluginContractDay['plugin_manageentities_contractstates_id']);
            // Report
            if (($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) ||
                ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
               echo "</td><td class='center'>";
               echo Html::formatNumber($pluginContractDay['report']);
            }
            echo "</td>";
            //Conso
            echo "<td>";
            $contractDay = new PluginManageentitiesContractDay();
            $contractDay->getFromDB($pluginContractDay['id']);
            $contractDay->fields['contractdays_id'] = $contractDay->fields['id'];
            $resultCriDetail                        = PluginManageentitiesCriDetail::getCriDetailData($contractDay->fields);
            $conso                                  = 0;
            foreach ($resultCriDetail['result'] as $dataCriDetail) {
               $conso += $dataCriDetail['conso'];
            }
            echo Html::formatNumber($conso);
            // Depass
            if (($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) ||
                ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
               echo "</td><td class='center'>";
               echo Html::formatNumber($resultCriDetail['resultOther']['reste']);
               echo "</td><td class='center'>";
               echo Html::formatNumber($resultCriDetail['resultOther']['depass']);
            }
            echo "</td>";
            $criprice = new PluginManageentitiesCriPrice();
            echo "</td><td class='center'>";

            if ($criprice->getFromDBByCrit(['plugin_manageentities_contractdays_id' => $contractDay->fields['id'],
                                            'is_default'                            => 1])) {
               echo Html::formatNumber($criprice->fields["price"], false);
            }
            echo "</td>";
            echo "</tr>";
         }
         echo "</table>";

         if ($canEdit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }

         echo "</div>";
      }
   }

   static function queryOldContractDaywithInterventions($date) {
      global $DB;

      $query = "SELECT `glpi_plugin_manageentities_cridetails`.`contracts_id`,
                 `glpi_entities`.`name` AS entities_name,
                 `glpi_plugin_manageentities_cridetails`.`tickets_id`,
                 `glpi_plugin_manageentities_cridetails`.`id` AS cridetails_id,
                 `glpi_plugin_manageentities_cridetails`.`date` as cridetails_date,
                 `glpi_tickets`.`name` AS tickets_name ,
                  `glpi_plugin_manageentities_contractdays`.`name`, 
                  `glpi_plugin_manageentities_contractdays`.`id`, 
                  `glpi_plugin_manageentities_contractdays`.`end_date`"
               . " FROM `glpi_plugin_manageentities_cridetails` "
               . " LEFT JOIN `glpi_tickets` 
               ON (`glpi_plugin_manageentities_cridetails`.`tickets_id` = `glpi_tickets`.`id`)"
               . " LEFT JOIN `glpi_entities` 
               ON (`glpi_plugin_manageentities_cridetails`.`entities_id` = `glpi_entities`.`id`) "
               . " LEFT JOIN `glpi_plugin_manageentities_contractdays` 
               ON (`glpi_plugin_manageentities_contractdays`.`id` = `glpi_plugin_manageentities_cridetails`.`plugin_manageentities_contractdays_id`)"
               . " WHERE `glpi_tickets`.`is_deleted` = 0 
                  AND `glpi_plugin_manageentities_contractdays`.`end_date` < '" . $date . "'
                  AND `glpi_plugin_manageentities_cridetails`.`date` > '" . $date . "'  ";

      return $query;
   }

   function setSessionValues() {
      if (isset($_SESSION['plugin_manageentities']['contractday']) && !empty($_SESSION['plugin_manageentities']['contractday'])) {
         foreach ($_SESSION['plugin_manageentities']['contractday'] as $key => $val) {
            $this->fields[$key] = $val;
         }
      }
      unset($_SESSION['plugin_manageentities']['contractday']);
   }


   function prepareInputForUpdate($input) {
      (isset($input['charged']) && $input['charged'] == true) ? $input['charged'] = 1 : $input['charged'] = 0;

      if (!$this->checkPeriod($input)) {
         return false;
      }

      if (!$this->checkMandatoryFields($input)) {
         return false;
      }
      return $input;
   }

   function prepareInputForAdd($input) {
      (isset($input['charged']) && $input['charged'] == true) ? $input['charged'] = 1 : $input['charged'] = 0;

      if (!$this->checkPeriod($input)) {
         $_SESSION['plugin_manageentities']['contractday'] = $input;
         return false;
      }

      if (!$this->checkMandatoryFields($input)) {
         $_SESSION['plugin_manageentities']['contractday'] = $input;
         return false;
      }

      return $input;
   }

   /**
    * checkPeriod : Check if a period allready exists, to avoid 2 same periods on a contract
    *
    * @param type  $input
    *
    * @return boolean
    * @global type $DB
    *
    */
   public function checkPeriod($input) {
      global $DB;

      $config = PluginManageentitiesConfig::getInstance();

      if (isset($input['end_date']) && isset($input['begin_date']) && !$config->fields['allow_same_periods'] && $input['end_date'] != null) {
         if ($input['end_date'] != 'NULL' && strtotime($input['end_date']) < strtotime($input['begin_date'])) {
            Session::addMessageAfterRedirect(__('End date cannot be less than begin date', 'manageentities'), true, ERROR);
            return false;
         }

         $contract = new Contract();
         $contract->getFromDB($input['contracts_id']);

         $output = [];

         $queryCheck = "SELECT `glpi_plugin_manageentities_contractdays`.`begin_date`,`glpi_plugin_manageentities_contractdays`.`end_date`
                           FROM `glpi_plugin_manageentities_contractdays`
                           WHERE `glpi_plugin_manageentities_contractdays`.`entities_id` ='" . $input['entities_id'] . "' 
                           AND `glpi_plugin_manageentities_contractdays`.`contracts_id` = '" . $input['contracts_id'] . "'";

         if (isset($input['id'])) {
            $queryCheck .= " AND `glpi_plugin_manageentities_contractdays`.`id` != '" . $input['id'] . "'";
         }

         if ($resultCheck = $DB->query($queryCheck)) {
            if ($DB->numrows($resultCheck) != 0) {// If the period exists return false
               while ($data = $DB->fetchAssoc($resultCheck)) {
                  $output[] = $data;
               }
            }
         }

         foreach ($output as $date) {
            if (!((strtotime($input['begin_date']) < strtotime($date['begin_date']) && strtotime($input['end_date']) < strtotime($date['begin_date']))
                  || (strtotime($input['begin_date']) > strtotime($date['end_date']) && (strtotime($input['end_date']) > strtotime($date['end_date']) || $input['end_date'] == 'NULL')))) {

               Session::addMessageAfterRedirect(sprintf(__('The contract period %s already exists', 'manageentities'), Html::convDate($input['begin_date']) . ' - ' . Html::convDate($input['end_date'])), true, ERROR);
               return false;
            }
         }
      }

      return true;
   }

   /**
    * checkMandatoryFields
    *
    * @param type $input
    *
    * @return boolean
    */
   function checkMandatoryFields($input) {
      $msg     = [];
      $checkKo = false;

      $config = PluginManageentitiesConfig::getInstance();

      $mandatory_fields = ['plugin_manageentities_contractstates_id' => PluginManageentitiesContractState::getTypeName(),
                           'begin_date'                              => __('Begin date')];

      if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
         $mandatory_fields['contract_type'] = __('Type of service contract', 'manageentities');
      }

      foreach ($input as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value) || $value == 'NULL') {
               $msg[]   = $mandatory_fields[$key];
               $checkKo = true;
            }
         }
      }

      if ($checkKo) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), false, ERROR);
         return false;
      }

      return true;
   }

}
