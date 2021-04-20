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

class PluginManageentitiesFollowUp extends CommonDBTM {

   static $rightname = 'plugin_manageentities';

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [READ, CREATE, UPDATE, DELETE]);
   }

   static function queryFollowUp($instID, $options = []) {
      global $DB;

      $dbu = new DbUtils();
      if (isset($options['entities_id']) && $options['entities_id'] != '-1') {
         $sons     = $dbu->getSonsOf('glpi_entities', $options['entities_id']);
         $entities = "";
         $first    = true;
         if (is_array($sons)) {
            foreach ($sons as $son) {
               if ($first) {
                  $entities .= "'" . $son . "'";
                  $first    = false;
               } else {
                  $entities .= ",'" . $son . "'";
               }
            }
         }

         $condition = " `glpi_contracts`.`entities_id` IN (" .
                      $entities . ") ";
      } else {

         if (Session::getCurrentInterface() == 'central') {
            $condition = $dbu->getEntitiesRestrictRequest("", "glpi_contracts");
         } else {
            if (is_array($instID)) {
               $instID    = "'" . implode("', '", $instID) . "'";
               $condition = " `glpi_contracts`.`entities_id` IN (" .
                            $instID . ") ";
            } else {
               $condition = " `glpi_contracts`.`entities_id` = '" .
                            $instID . "'";
            }

         }
      }


      $beginDateAfter         = '';
      $beginDateBefore        = '';
      $endDateAfter           = '';
      $endDateBefore          = '';
      $beginDate              = 'NULL';
      $endDate                = 'NULL';
      $contractState          = '';
      $queryBusiness          = '';
      $queryCompany           = '';
      $num                    = 0;
      $list                   = [];
      $tot_credit             = 0;
      $contract_credit        = 0;
      $tot_conso              = 0;
      $contract_conso         = 0;
      $tot_reste              = 0;
      $tot_depass             = 0;
      $tot_forfait            = 0;
      $contract_forfait       = 0;
      $tot_reste_montant      = 0;
      $contract_reste_montant = 0;
      $nbContratByEntities    = 0;// Count the contracts for all entities
      $contract_depass        = 0;
      $pricecri               = [];

      // We configure the type of contract Hourly or Dayly
      $config = PluginManageentitiesConfig::getInstance();
      if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {// Hourly
         $configHourOrDay = "AND (`glpi_plugin_manageentities_contracts`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_NULL . "' 
                             OR `glpi_plugin_manageentities_contracts`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_HOUR . "'
                             OR `glpi_plugin_manageentities_contracts`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_INTERVENTION . "'
                             OR `glpi_plugin_manageentities_contracts`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED . "')";
      } else {// Daily
         $configHourOrDay = "AND (`glpi_plugin_manageentities_contractdays`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_NULL . "'
                             OR `glpi_plugin_manageentities_contractdays`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_AT . "'
                             OR `glpi_plugin_manageentities_contractdays`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_FORFAIT . "')";
      }

      if (isset($options['begin_date_after']) && $options['begin_date_after'] != 'NULL') {
         $beginDateAfter = " AND (`glpi_plugin_manageentities_contractdays`.`begin_date` >='" .
                           $options['begin_date_after'] . "' )";
         $beginDate      = $options['begin_date_after'];
      }

      if (isset($options['begin_date_before']) && $options['begin_date_before'] != 'NULL') {
         $beginDateBefore = " AND (`glpi_plugin_manageentities_contractdays`.`begin_date` <= ADDDATE('" .
                            $options['begin_date_before'] . "', INTERVAL 1 DAY))";
      }

      if (isset($options['end_date_after']) && $options['end_date_after'] != 'NULL') {
         $endDateAfter = " AND (`glpi_plugin_manageentities_contractdays`.`end_date` >= '" .
                         $options['end_date_after'] . "' )";
      }

      if (isset($options['end_date_before']) && $options['end_date_before'] != 'NULL') {
         $endDateBefore = " AND (`glpi_plugin_manageentities_contractdays`.`end_date` <= ADDDATE('" .
                          $options['end_date_before'] . "', INTERVAL 1 DAY))";
         $endDate       = $options['end_date_before'];
      }

      $plugin_config = new PluginManageentitiesConfig();
      $config_states = $plugin_config->find();
      $config_states = reset($config_states);

      $plugin_pref = new PluginManageentitiesPreference();
      $preferences = $plugin_pref->find(['users_id' => Session::getLoginUserID()]);
      $preferences = reset($preferences);

      if (isset($options['contract_states']) && $options['contract_states'] != '0') {
         $contractState .= " AND `glpi_plugin_manageentities_contractdays`.`plugin_manageentities_contractstates_id`  IN ('" . implode("','", $options['contract_states']) . "') ";
      } elseif (isset($preferences['contract_states']) && $preferences['contract_states'] != NULL) {
         $contractState .= " AND `glpi_plugin_manageentities_contractdays`.`plugin_manageentities_contractstates_id`  IN ('" . implode("','", json_decode($preferences['contract_states'], true)) . "') ";
      } elseif (isset($config_states['contract_states']) && $config_states['contract_states'] != NULL) {
         $contractState .= " AND `glpi_plugin_manageentities_contractdays`.`plugin_manageentities_contractstates_id`  IN ('" . implode("','", json_decode($config_states['contract_states'], true)) . "') ";
      }

      if (isset($options['business_id']) && $options['business_id'] != '0') {
         $queryBusiness .= " AND `glpi_plugin_manageentities_businesscontacts`.`users_id` IN ('" . implode("','", $options['business_id']) . "') ";
      } elseif (isset($preferences['business_id']) && $preferences['business_id'] != NULL) {
         $queryBusiness .= " AND `glpi_plugin_manageentities_businesscontacts`.`users_id` IN ('" . implode("','", json_decode($preferences['business_id'], true)) . "') ";
      } elseif (isset($config_states['business_id']) && $config_states['business_id'] != NULL) {
         $queryBusiness .= " AND `glpi_plugin_manageentities_businesscontacts`.`users_id`  IN ('" . implode("','", json_decode($config_states['business_id'], true)) . "') ";
      }

      if (isset($options['company_id']) && $options['company_id'] != '0') {
         $temp = 0;
         foreach ($options['company_id'] as $id) {
            $plugin_company = new PluginManageentitiesCompany();
            $company        = $plugin_company->find(['id' => $id]);
            $company        = reset($company);
            $sons           = [];
            if ($company['recursive'] == 1) {
               $sons = $dbu->getSonsOf('glpi_entities', $company['entity_id']);
            } else {
               $sons[0] = $company['entity_id'];
            }
            if ($temp == 0) {
               $queryCompany .= "AND (`glpi_entities`.`id` IN ('" . implode("','", $sons) . "')";
            } else {
               $queryCompany .= "OR `glpi_entities`.`id` IN ('" . implode("','", $sons) . "')";
            }
            $temp++;
         }
         $queryCompany .= ")";
      } elseif (isset($preferences['companies_id']) && $preferences['companies_id'] != NULL) {
         $temp = 0;
         foreach (json_decode($preferences['companies_id'], true) as $id) {
            $sons           = [];
            $plugin_company = new PluginManageentitiesCompany();
            $company        = $plugin_company->find(['id' => $id]);
            $company        = reset($company);
            if ($company['recursive'] == 1) {
               $sons = $dbu->getSonsOf('glpi_entities', $company['entity_id']);
            } else {
               $sons[0] = $company['entity_id'];
            }
            if ($temp == 0) {
               $queryCompany .= "AND (`glpi_entities`.`id` IN ('" . implode("','", $sons) . "')";
            } else {
               $queryCompany .= "OR `glpi_entities`.`id` IN ('" . implode("','", $sons) . "')";
            }
            $temp++;
         }
         $queryCompany .= ")";
      }

      $queryEntity = "SELECT DISTINCT(`glpi_entities`.`id`) AS entities_id,
                      `glpi_entities`.`name` AS entities_name
               FROM `glpi_contracts`
               
               LEFT JOIN `glpi_entities`
                  ON (`glpi_entities`.`id`
                  = `glpi_contracts`.`entities_id`)
                  
               WHERE $condition
               AND `glpi_entities`.`name` IS NOT NULL 
               AND `glpi_entities`.`id` IS NOT NULL
               " . $dbu->getEntitiesRestrictRequest("AND", "glpi_entities", 'id', "", true) . "
               ORDER BY `glpi_entities`.`name`";

      $resEntity   = $DB->query($queryEntity);
      $nbTotEntity = ($resEntity ? $DB->numrows($resEntity) : 0);

      if ($resEntity && $nbTotEntity > 0) {
         while ($dataEntity = $DB->fetchArray($resEntity)) {
            $queryContract = "SELECT `glpi_contracts`.`id` AS contracts_id,
                                     `glpi_contracts`.`name` AS name,
                                     `glpi_contracts`.`num` AS num,
                                     `glpi_contracts`.`begin_date` AS contract_begin_date,
                                     `glpi_contracts`.`duration` AS duration,
                                     `glpi_contracts`.`entities_id` AS entities_id,
                                     `glpi_plugin_manageentities_contracts`.`management` AS management,
                                     `glpi_plugin_manageentities_contracts`.`contract_type` AS contract_type,
                                     `glpi_plugin_manageentities_contracts`.`date_signature` AS date_signature,
                                     `glpi_plugin_manageentities_contracts`.`date_renewal` AS date_renewal,
                                     `glpi_plugin_manageentities_contracts`.`contract_added` AS contract_added,
                                     `glpi_plugin_manageentities_contracts`.`show_on_global_gantt` AS show_on_global_gantt";
            if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {// Hourly
               $queryContract .= ", `glpi_plugin_manageentities_contracts`.`contract_type` AS contract_type";
            }
            $queryContract .= " FROM `glpi_contracts`
                        
                        LEFT JOIN `glpi_plugin_manageentities_contracts`
                           ON (`glpi_contracts`.`id`
                           = `glpi_plugin_manageentities_contracts`.`contracts_id`)
                        
                        WHERE `glpi_contracts`.`entities_id`='" . $dataEntity['entities_id'] . "'
                           
                        AND `glpi_contracts`.`is_deleted` != 1 ";

            if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {// Hourly
               $queryContract .= $configHourOrDay;
            }

            $queryContract .= "GROUP BY `glpi_contracts`.`id`
                        ORDER BY `glpi_plugin_manageentities_contracts`.`date_signature` ASC,
                                 `glpi_contracts`.`name`";

            foreach ($DB->request($queryContract) as $dataContract) {
               $queryContractDay = "SELECT `glpi_plugin_manageentities_contractdays`.`name` AS name_contractdays,
                                           `glpi_plugin_manageentities_contractdays`.`plugin_manageentities_contractstates_id` AS contractstates_id,
                                           `glpi_plugin_manageentities_contractdays`.`id` AS contractdays_id,
                                           `glpi_plugin_manageentities_contractdays`.`plugin_manageentities_critypes_id`,
                                           `glpi_plugin_manageentities_contractdays`.`report` AS report,
                                           `glpi_plugin_manageentities_contractdays`.`nbday` AS nbday,
                                           `glpi_plugin_manageentities_contractdays`.`end_date` AS end_date,
                                           `glpi_plugin_manageentities_contractdays`.`begin_date` AS begin_date,
                                           `glpi_plugin_manageentities_contractstates`.`is_closed` AS is_closed,
                                           `glpi_plugin_manageentities_contractstates`.`color`";
               if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {// Daily
                  $queryContractDay .= ", `glpi_plugin_manageentities_contractdays`.`contract_type` AS contract_type";
               }

               $queryContractDay .= " FROM `glpi_plugin_manageentities_contractdays`
                       
                       LEFT JOIN `glpi_contracts`
                          ON (`glpi_contracts`.`id` 
                          = `glpi_plugin_manageentities_contractdays`.`contracts_id`)
                          
                       LEFT JOIN `glpi_plugin_manageentities_contractstates`
                          ON (`glpi_plugin_manageentities_contractstates`.`id` 
                          = `glpi_plugin_manageentities_contractdays`.`plugin_manageentities_contractstates_id`)
                        
                       LEFT JOIN `glpi_plugin_manageentities_businesscontacts`
                          ON (`glpi_plugin_manageentities_businesscontacts`.`entities_id` = `glpi_plugin_manageentities_contractdays`.`entities_id`)
                          
                      LEFT JOIN `glpi_entities`
                          ON (`glpi_entities`.`id` = `glpi_plugin_manageentities_contractdays`.`entities_id`)

                       WHERE `glpi_contracts`.`entities_id`='" . $dataEntity['entities_id'] . "'
                          $beginDateAfter $beginDateBefore $endDateAfter $endDateBefore $contractState $queryBusiness $queryCompany";

               if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {// Daily
                  $queryContractDay .= $configHourOrDay;
               }

               $queryContractDay .= "AND `glpi_plugin_manageentities_contractdays`.`contracts_id` = '" .
                                    $dataContract["contracts_id"] . "'
                             
                       GROUP BY `glpi_plugin_manageentities_contractdays`.`id`
                       ORDER BY `glpi_plugin_manageentities_contractdays`.`end_date` ASC";

               $requestContractDay = $DB->query($queryContractDay);
               $nbContractDay      = ($requestContractDay ? $DB->numrows($requestContractDay) : 0);

               if ($requestContractDay && $nbContractDay > 0) {
                  $nbContratByEntities++;
                  $contract_reste = 0;
                  $name_contract  = "";

                  if (Session::getCurrentInterface() == 'central') {
                     $link_contract = Toolbox::getItemTypeFormURL("Contract");
                     $name_contract .= "<a href='" . $link_contract . "?id=" . $dataContract["contracts_id"] . "'>";
                  }
                  if ($dataContract["name"] == NULL) {
                     $name = "(" . $dataContract["contracts_id"] . ")";
                  } else {
                     $name = $dataContract["name"];
                  }
                  if (Session::getCurrentInterface() == 'central') {
                     $name_contract .= $name . "</a>";
                  }

                  $list[$num]['entities_name']        = $dataEntity['entities_name'];
                  $list[$num]['entities_id']          = $dataEntity['entities_id'];
                  $list[$num]['contract_name']        = $name_contract;
                  $list[$num]['name']                 = $name;
                  $list[$num]['contract_num']         = $dataContract['num'];
                  $list[$num]['management']           = PluginManageentitiesContract::getContractManagement($dataContract['management']);
                  $list[$num]['contract_type']        = $dataContract['contract_type'];
                  $list[$num]['contract_added']       = Dropdown::getYesNo($dataContract['contract_added']);
                  $list[$num]['date_signature']       = Html::convDate($dataContract['date_signature']);
                  $list[$num]['date_renewal']         = Html::convDate($dataContract['date_renewal']);
                  $list[$num]['contract_begin_date']  = Html::convDate($dataContract['contract_begin_date']);
                  $list[$num]['duration']             = $dataContract['duration'];
                  $list[$num]['contracts_id']         = $dataContract['contracts_id'];
                  $list[$num]['show_on_global_gantt'] = $dataContract['show_on_global_gantt'];

                  for ($i = 0; $dataContractDay = $DB->fetchAssoc($requestContractDay); $i++) {
                     $name_period = "";
                     if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {// Daily
                        $dataContractDay["contract_type"] = $dataContract["contract_type"];
                     }

                     if (Session::getCurrentInterface() == 'central') {
                        $link_period = Toolbox::getItemTypeFormURL("PluginManageentitiesContractDay");
                        $name_period = "<a class='ganttWhite' href='" . $link_period . "?id=" . $dataContractDay["contractdays_id"] . "&showFromPlugin=1'>";
                     } else {
                        $name_period = $dataContractDay["name_contractdays"];
                     }

                     if ($dataContractDay["name_contractdays"] == NULL) {
                        $nameperiod = "(" . $dataContractDay["contractdays_id"] . ")";
                     } else {
                        $nameperiod = $dataContractDay["name_contractdays"];
                     }
                     if (Session::getCurrentInterface() == 'central') {
                        $name_period .= $nameperiod . "</a>";
                     }

                     // We get all cri details
                     $dataContractDay['values_begin_date'] = $beginDate;
                     $dataContractDay['values_end_date']   = $endDate;
                     $dataContractDay['contracts_id']      = $dataContract['contracts_id'];
                     $dataContractDay['entities_id']       = $dataContract['entities_id'];
                     $dataContractDay['contractdays_id']   = $dataContractDay["contractdays_id"];

                     $resultCriDetail = PluginManageentitiesCriDetail::getCriDetailData($dataContractDay, ["contract_type_id" => $dataContractDay["contract_type"]]);

                     $tot_amount = 0;
                     $forfait    = $resultCriDetail['resultOther']['forfait'];
                     $depass     = 0;
                     $conso      = 0;

                     foreach ($resultCriDetail['result'] as $dataCriDetail) {
                        $conso      += $dataCriDetail['conso'];
                        $tot_amount += $dataCriDetail['conso_amount'];

                        $pricecri[$dataCriDetail['plugin_manageentities_critypes_id']] = $dataCriDetail['pricecri'];
                     }

                     //Rest number / depass
                     $reste = ($dataContractDay["nbday"] + $dataContractDay["report"]) - $conso;
                     if ($reste < 0) {
                        $depass = abs($reste);
                        $reste  = 0;
                     }

                     //Rest amount
                     $reste_montant = $resultCriDetail['resultOther']['reste_montant'];

                     if (Session::getCurrentInterface() == 'helpdesk'
                         && $dataContractDay["contract_type"] == PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED) {
                        $credit = PluginManageentitiesContract::getContractType($dataContractDay["contract_type"]);

                     } else {
                        $credit     = $dataContractDay['nbday'] + $dataContractDay['report'];
                        $tot_credit += $credit;
                        $tot_reste  += $resultCriDetail['resultOther']['reste'];
                        $tot_depass += $resultCriDetail['resultOther']['depass'];
                     }
                     $contract_credit        += $credit;
                     $tot_conso              += $conso;
                     $contract_conso         += $conso;
                     $tot_forfait            += $forfait;
                     $contract_forfait       += $forfait;
                     $tot_reste_montant      += $reste_montant;
                     $contract_reste_montant += $reste_montant;

                     $and = "";

                     if ($config->fields['useprice'] == PluginManageentitiesConfig::NOPRICE) {

                        if (!empty($dataContractDay['begin_date']))
                           $and .= " AND `glpi_tickets`.`date` >= '" . $dataContractDay['begin_date'] . "' ";

                        if (!empty($dataContractDay['end_date']))
                           $and .= " AND `glpi_tickets`.`date` <= ADDDATE('" . $dataContractDay['end_date'] . "', INTERVAL 1 DAY) ";

                        $queryTicket = "SELECT `glpi_tickets`.`date`
                               FROM `glpi_tickets`
                               WHERE `entities_id` = '" . $dataEntity['entities_id'] . "'
                               $and 
                               AND `glpi_tickets`.`is_deleted` = 0 
                               ORDER BY `date` DESC
                               LIMIT 1";

                     } else {
                        if (!empty($dataContractDay['begin_date']))
                           $and .= " AND `glpi_plugin_manageentities_cridetails`.`date` >= '" . $dataContractDay['begin_date'] . "' ";

                        if (!empty($dataContractDay['end_date']))
                           $and .= " AND `glpi_plugin_manageentities_cridetails`.`date` <= ADDDATE('" . $dataContractDay['end_date'] . "', INTERVAL 1 DAY) ";

                        $queryTicket = "SELECT `glpi_plugin_manageentities_cridetails`.`date`
                               FROM `glpi_plugin_manageentities_cridetails`
                               LEFT JOIN `glpi_tickets`
                                 ON (`glpi_plugin_manageentities_cridetails`.`tickets_id` = `glpi_tickets`.`id`)
                               WHERE `glpi_plugin_manageentities_cridetails`.`entities_id` = '" . $dataEntity['entities_id'] . "'
                               $and 
                               AND `glpi_tickets`.`is_deleted` = 0 
                               ORDER BY `glpi_plugin_manageentities_cridetails`.`date` DESC
                               LIMIT 1";
                     }
                     $resTicket = $DB->query($queryTicket);
                     $date      = NULL;
                     for ($j = 0; $dataTicket = $DB->fetchAssoc($resTicket); $j++) {
                        $date = Html::convDate($dataTicket['date']);
                     }


                     $queryColor = "SELECT `glpi_plugin_manageentities_contractstates`.`color`
                               FROM `glpi_plugin_manageentities_contractstates`
                               WHERE `glpi_plugin_manageentities_contractstates`.`id` = '" . $dataContractDay['contractstates_id'] . "'";
                     $color      = $DB->result($DB->query($queryColor), 0, "color");

                     $list[$num]['days'][$i]['contract_is_closed']   = $dataContractDay['is_closed'];
                     $list[$num]['days'][$i]['contractday_name']     = $name_period;
                     $list[$num]['days'][$i]['contractdayname']      = $nameperiod;
                     $list[$num]['days'][$i]['contractstates']       = Dropdown::getDropdownName('glpi_plugin_manageentities_contractstates', $dataContractDay['contractstates_id']);
                     $list[$num]['days'][$i]['contractstates_color'] = $color;
                     $list[$num]['days'][$i]['begin_date']           = Html::convDate($dataContractDay['begin_date']);
                     $list[$num]['days'][$i]['end_date']             = Html::convDate($dataContractDay['end_date']);
                     $list[$num]['days'][$i]['credit']               = $credit;
                     $list[$num]['days'][$i]['conso']                = $conso;
                     $list[$num]['days'][$i]['reste']                = $resultCriDetail['resultOther']['reste'];
                     $list[$num]['days'][$i]['depass']               = $resultCriDetail['resultOther']['depass'];
                     $list[$num]['days'][$i]['price']                = $pricecri;
                     $list[$num]['days'][$i]['forfait']              = Html::formatNumber($forfait);
                     $list[$num]['days'][$i]['reste_montant']        = Html::formatNumber($resultCriDetail['resultOther']['reste_montant']);
                     $list[$num]['days'][$i]['last_visit']           = $date;
                     $list[$num]['days'][$i]['contractdays_id']      = $dataContractDay["contractdays_id"];
                     $list[$num]['days'][$i]['contract_type']        = $dataContractDay["contract_type"];
                     $list[$num]['days'][$i]['contracts_id']         = $dataContractDay['contracts_id'];

                     $contract_reste  += $resultCriDetail['resultOther']['reste'];
                     $contract_depass += $resultCriDetail['resultOther']['depass'];
                  }


                  if ($contract_reste < 0) {
                     $contract_depass = abs($contract_reste);
                     $contract_reste  = 0;
                  }

                  $list[$num]['contract_tot']['contract_credit']        = $contract_credit;
                  $list[$num]['contract_tot']['contract_conso']         = $contract_conso;
                  $list[$num]['contract_tot']['contract_reste']         = $contract_reste;
                  $list[$num]['contract_tot']['contract_depass']        = $contract_depass;
                  $list[$num]['contract_tot']['contract_forfait']       = $contract_forfait;
                  $list[$num]['contract_tot']['contract_reste_montant'] = $contract_reste_montant;

                  $contract_credit        = 0;
                  $contract_conso         = 0;
                  $contract_depass        = 0;
                  $contract_forfait       = 0;
                  $contract_reste_montant = 0;
                  $num++;
               }
            }
         }
         if ($nbContratByEntities > 0) {
            $list['tot']['tot_credit']        = $tot_credit;
            $list['tot']['tot_conso']         = $tot_conso;
            $list['tot']['tot_reste']         = $tot_reste;
            $list['tot']['tot_depass']        = $tot_depass;
            $list['tot']['tot_forfait']       = $tot_forfait;
            $list['tot']['tot_reste_montant'] = $tot_reste_montant;
         }
      }

      return $list;
   }

   static function showFollowUp($values) {
      global $DB;
      $list = self::queryFollowUp($_SESSION["glpiactive_entity"], $values);

      $default_values["start"]  = $start = 0;
      $default_values["id"]     = $id = 0;
      $default_values["export"] = $export = false;

      foreach ($default_values as $key => $val) {
         if (isset($values[$key])) {
            $$key = $values[$key];
         }
      }

      // Set display type for export if define
      $output_type = Search::HTML_OUTPUT;

      if (isset($values["display_type"]))
         $output_type = $values["display_type"];

      $nbcols  = 12;
      $row_num = 0;
      $numrows = 1;
      $config  = PluginManageentitiesConfig::getInstance();

      $contract_states = NULL;
      if (isset($values['contract_states']) && $values['contract_states'] != '0') {
         foreach ($values['contract_states'] as $key => $contract_state) {
            $contract_states .= "&amp;contract_states[$key]=$contract_state";
         }
      } else {
         $contract_states .= "&amp;contract_states=0";
      }

      $business_ids = null;
      if (isset($values['business_id']) && $values['business_id'] != '0') {
         foreach ($values['business_id'] as $key => $id) {
            $business_ids .= "&amp;business_id[$key]=$id";
         }
      }

      $company_ids = null;
      if (isset($values['company_id']) && $values['company_id'] != '0') {
         foreach ($values['company_id'] as $key => $id) {
            $company_ids .= "&amp;company_id[$key]=$id";
         }
      }

      $parameters = "begin_date_after=" . $values['begin_date_after'] . "&amp;begin_date_before=" .
                    $values['begin_date_before'] . "&amp;end_date_after=" . $values['end_date_after'] .
                    "&amp;end_date_before=" . $values['end_date_before']
                    . $contract_states . "&amp;entities_id=" . $values['entities_id'] . "&amp;" . $business_ids . $company_ids;

      // Colspan
      $colspan = '2';
      if (Session::getCurrentInterface() == 'helpdesk') {
         $colspan = '6';
      }
      $colspan_contract = $colspan + 1;

      if (!empty($list)) {
         if ($output_type == Search::HTML_OUTPUT && Session::getCurrentInterface() == 'central') {
            self::showLegendary();
            self::printPager($start, $numrows, $_SERVER['PHP_SELF'], $parameters, "PluginManageentitiesFollowUp");
         }

         echo Search::showHeader($output_type, 1, $nbcols);
         echo Search::showBeginHeader($output_type);
         $item_num = 0;
         echo Search::showNewLine($output_type);
         if ($output_type != Search::HTML_OUTPUT) {
            if (Session::getCurrentInterface() == 'central')
               echo Search::showHeaderItem($output_type, _n('Client', 'Clients', 1, 'manageentities'), $item_num);

            echo Search::showHeaderItem($output_type, __('Contract'), $item_num, "", 0, "", "colspan='" . $colspan_contract . "'");
            echo Search::showHeaderItem($output_type, '', $item_num);
            echo Search::showHeaderItem($output_type, '', $item_num);
            echo Search::showHeaderItem($output_type, _x('phone', 'Number'), $item_num, "", 0, "", "colspan='" . $colspan . "'");
            echo Search::showHeaderItem($output_type, '', $item_num);
         }

         if (Session::getCurrentInterface() == 'central') {
            if ($output_type != Search::HTML_OUTPUT) {
               if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) echo Search::showHeaderItem($output_type, __('Contract present', 'manageentities'), $item_num, "", 0, "", "colspan='2'");
               else echo Search::showHeaderItem($output_type, '', $item_num);
               echo Search::showHeaderItem($output_type, '', $item_num);
               echo Search::showHeaderItem($output_type, __('Date of signature', 'manageentities'), $item_num, "", 0, "", "colspan='2'");
               echo Search::showHeaderItem($output_type, '', $item_num);
               echo Search::showHeaderItem($output_type, __('Date of renewal', 'manageentities'), $item_num, "", 0, "", "colspan='2'");
               //               echo Search::showHeaderItem($output_type, '', $item_num);
               if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
                  echo Search::showHeaderItem($output_type, __('Mode of management', 'manageentities'), $item_num);
                  echo Search::showHeaderItem($output_type, __('Type of service contract', 'manageentities'), $item_num);
               }
            }
         }

         echo Search::showEndLine($output_type);
         echo Search::showEndHeader($output_type);

         $entity_id = 0;
         $first     = true;

         foreach ($list as $v => $contract) {
            if ($config->fields['useprice'] == PluginManageentitiesConfig::NOPRICE) $colspanNoprice = "colspan='2'"; else $colspanNoprice = "";

            if (is_numeric($v)) {
               $first = false;// First entity ?

               // Display Entity
               if ($output_type == Search::HTML_OUTPUT && Session::getCurrentInterface() == 'central') {
                  if ($entity_id != $contract['entities_id']) {
                     $row_num++;
                     $item_num = 0;

                     echo Search::showNewLine($output_type);
                     if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) $colspanContract = "colspan = '13'"; else $colspanContract = "colspan = '12'";
                     if (empty($contract['contract_name'])) $contract['contract_name'] = $contract['name'];
                     echo Search::showHeaderItem($output_type, '<b>' . _n('Client', 'Clients', 1, 'manageentities') . ' : </b>' . $contract['entities_name'], $item_num, '', 0, '', $colspanContract . " style='" . PluginManageentitiesMonthly::$style[0] . "' ");
                     echo Search::showEndLine($output_type);
                  }
               }

               $row_num++;
               $item_num = 0;

               echo Search::showNewLine($output_type);
               // Display Entity
               if (Session::getCurrentInterface() == 'central') {
                  if ($entity_id != $contract['entities_id']) {
                     if ($output_type != Search::HTML_OUTPUT) echo Search::showItem($output_type, $contract['entities_name'], $item_num, $row_num);
                  } else {
                     if ($output_type != Search::HTML_OUTPUT) echo Search::showItem($output_type, '', $item_num, $row_num);
                  }
                  $entity_id = $contract['entities_id'];
               }

               // Display Contract title
               if (empty($contract['contract_name'])) $contract['contract_name'] = $contract['name'];
               if ($output_type != Search::HTML_OUTPUT) {
                  echo Search::showItem($output_type, $contract['contract_name'], $item_num, $row_num);
                  echo Search::showItem($output_type, '', $item_num, $row_num);
                  echo Search::showItem($output_type, '', $item_num, $row_num);
               } else {
                  $colspanContractName = "colspan='4'";

                  echo Search::showItem($output_type, '<b>' . __('Contract') . ' : </b>' . $contract['contract_name'], $item_num, $row_num, $colspanContractName);
               }

               // Display contract Num
               if ($output_type != Search::HTML_OUTPUT) {
                  echo Search::showItem($output_type, $contract['contract_num'], $item_num, $row_num, "colspan='" . $colspan . "'");
                  echo Search::showItem($output_type, '', $item_num, $row_num);
               } else {
                  echo Search::showItem($output_type, '<b>' . _x('phone', 'Number') . ' : </b>' . $contract['contract_num'], $item_num, $row_num, "colspan='" . $colspan . "'");
               }

               if (Session::getCurrentInterface() == 'central') {
                  // Display contract added
                  if ($output_type != Search::HTML_OUTPUT) {
                     if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) echo Search::showItem($output_type, $contract['contract_added'], $item_num, $row_num, "colspan='2'");
                     else echo Search::showItem($output_type, '', $item_num, $row_num);
                     echo Search::showItem($output_type, '', $item_num, $row_num);
                  } else {
                     if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) echo Search::showItem($output_type, '<b>' . __('Contract present', 'manageentities') . ' : </b>' . $contract['contract_added'], $item_num, $row_num, "colspan='2'");
                  }
                  // Display Signature
                  if ($output_type != Search::HTML_OUTPUT) {
                     echo Search::showItem($output_type, $contract['date_signature'], $item_num, $row_num, "colspan='2'");
                     echo Search::showItem($output_type, '', $item_num, $row_num);
                  } else {
                     echo Search::showItem($output_type, '<b>' . __('Date of signature', 'manageentities') . ' : </b>' . $contract['date_signature'], $item_num, $row_num, "colspan='2'");
                  }
                  // Display reconduction
                  if ($output_type != Search::HTML_OUTPUT) {
                     echo Search::showItem($output_type, $contract['date_renewal'], $item_num, $row_num, "colspan='2'");
                     //                     echo Search::showItem($output_type, '', $item_num, $row_num);
                  } else {
                     echo Search::showItem($output_type, '<b>' . __('Date of renewal', 'manageentities') . ' : </b>' . $contract['date_renewal'], $item_num, $row_num, "colspan='2'");
                  }
                  // Display contract Type and contract mode
                  if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
                     if ($output_type != Search::HTML_OUTPUT) {
                        echo Search::showItem($output_type, $contract['management'], $item_num, $row_num);
                        echo Search::showItem($output_type, PluginManageentitiesContract::getContractType($contract['contract_type']), $item_num, $row_num);
                     } else {
                        echo Search::showItem($output_type, '<b>' . __('Mode of management', 'manageentities') . ' : </b>' . $contract['management'], $item_num, $row_num);
                        echo Search::showItem($output_type, '<b>' . __('Type of service contract', 'manageentities') . ' : </b>' . PluginManageentitiesContract::getContractType($contract['contract_type']), $item_num, $row_num);
                     }
                  }
               }
               echo Search::showEndLine($output_type);

               // Contract details headers
               $row_num++;
               $item_num = 0;

               echo Search::showNewLine($output_type);
               if (Session::getCurrentInterface() == 'central' && $output_type != Search::HTML_OUTPUT) {
                  echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
               }
               echo Search::showHeaderItem($output_type, __('Period of contract', 'manageentities'), $item_num, '', 0, '', " $colspanNoprice style='" . PluginManageentitiesMonthly::$style[1] . "'");

               if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR)// Coslpan if type = Hourly
                  echo Search::showHeaderItem($output_type, __('State of contract', 'manageentities'), $item_num, '', 0, '', "colspan='2' style='" . PluginManageentitiesMonthly::$style[1] . "'");
               else
                  echo Search::showHeaderItem($output_type, __('State of contract', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");

               if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY)
                  echo Search::showHeaderItem($output_type, __('Type of contract', 'manageentities'), $item_num, '', 0, '', "colspan='2' style='" . PluginManageentitiesMonthly::$style[1] . "'");

               if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR)// Coslpan if type = Hourly
                  echo Search::showHeaderItem($output_type, __('End date'), $item_num, '', 0, '', "colspan='2'");
               else
                  echo Search::showHeaderItem($output_type, __('End date'), $item_num, '');

               echo Search::showHeaderItem($output_type, __('Initial credit', 'manageentities'), $item_num, '', 0, '', "$colspanNoprice style='" . PluginManageentitiesMonthly::$style[1] . "'");
               echo Search::showHeaderItem($output_type, __('Total consummated', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
               if (Session::getCurrentInterface() == 'helpdesk'
                   && ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $contract['contract_type'] == PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
                  echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
               } else {
                  echo Search::showHeaderItem($output_type, __('Total remaining', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  if (Session::getCurrentInterface() == 'central') {
                     echo Search::showHeaderItem($output_type, __('Total exceeding', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                     if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
                        echo Search::showHeaderItem($output_type, __('Last visit', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                        //                        if($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) echo Search::showItem($output_type, __('Applied daily rate', 'manageentities'), $item_num, '', 0, '', "style='".PluginManageentitiesMonthly::$style[1]."'");
                        //                        else echo Search::showHeaderItem($output_type, __('Applied hourly rate', 'manageentities'), $item_num, '', 0, '', "style='".PluginManageentitiesMonthly::$style[1]."'");
                        echo Search::showHeaderItem($output_type, __('Guaranteed package', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                        echo Search::showHeaderItem($output_type, __('Remaining total (amount)', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                     } else {
                        echo Search::showHeaderItem($output_type, __('Last visit', 'manageentities'), $item_num, '', 0, '', " colspan='2' style='" . PluginManageentitiesMonthly::$style[1] . "'");
                        if ($output_type != Search::HTML_OUTPUT) {
                           //                           echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='".PluginManageentitiesMonthly::$style[1]."'");
                           echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                           echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                        }
                     }
                  }
               }
               if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $output_type != Search::HTML_OUTPUT) {
                  echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
               }
               echo Search::showEndLine($output_type);

               foreach ($contract['days'] as $w => $day) {
                  $row_num++;
                  $item_num = 0;

                  echo PluginManageentitiesFollowUp::showNewLine($output_type, false, $day['contract_is_closed'], $day['contractstates_color']);
                  if (Session::getCurrentInterface() == 'central' && $output_type != Search::HTML_OUTPUT)
                     echo Search::showItem($output_type, '', $item_num, $row_num);
                  echo Search::showItem($output_type, $day['contractday_name'], $item_num, $row_num, " $colspanNoprice ");

                  if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR)// Coslpan if type = Hourly
                     echo Search::showItem($output_type, $day['contractstates'], $item_num, $row_num, "colspan='2' ");
                  else
                     echo Search::showItem($output_type, $day['contractstates'], $item_num, $row_num, "");

                  if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY)
                     echo Search::showItem($output_type, PluginManageentitiesContract::getContractType($day['contract_type']), $item_num, $row_num, "colspan='2' ");

                  if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR)// Coslpan if type = Hourly
                     echo Search::showItem($output_type, $day['end_date'], $item_num, $row_num, "colspan='2' ");
                  else
                     echo Search::showItem($output_type, $day['end_date'], $item_num, $row_num, "");

                  if ((Session::getCurrentInterface() == 'helpdesk' &&
                       ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY && $day['contract_type'] == PluginManageentitiesContract::CONTRACT_TYPE_FORFAIT)) ||
                      ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $day['contract_type'] == PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
                     echo Search::showItem($output_type, Dropdown::EMPTY_VALUE, $item_num, $row_num, "$colspanNoprice ");
                  } else {
                     echo Search::showItem($output_type, Html::formatNumber($day['credit'], 0, 2), $item_num, $row_num, "$colspanNoprice ");
                  }

                  if (Session::getCurrentInterface() == 'central' ||
                      ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY && $day['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_FORFAIT)) {
                     if (Session::getCurrentInterface() == 'helpdesk' &&
                         ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $day['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED && $day['conso'] > $day['credit'])) {
                        echo Search::showItem($output_type, Html::formatNumber($day['credit'], 0, 2), $item_num, $row_num, "");
                     } else {
                        echo Search::showItem($output_type, Html::formatNumber($day['conso'], 0, 2), $item_num, $row_num, "");
                     }
                  } else {
                     echo Search::showItem($output_type, Dropdown::EMPTY_VALUE, $item_num, $row_num, "");
                  }
                  if (Session::getCurrentInterface() == 'helpdesk'
                      && ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $contract['contract_type'] == PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED)) {
                     echo Search::showItem($output_type, '', $item_num, $row_num, "");
                     echo Search::showItem($output_type, '', $item_num, $row_num, "");
                  } else {
                     if (Session::getCurrentInterface() == 'central' || $day['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_FORFAIT) {
                        echo Search::showItem($output_type, Html::formatNumber($day['reste'], 0, 2), $item_num, $row_num, "");
                     } else {
                        echo Search::showItem($output_type, Dropdown::EMPTY_VALUE, $item_num, $row_num, "");
                     }
                     if (Session::getCurrentInterface() == 'central') {
                        echo Search::showItem($output_type, Html::formatNumber($day['depass'], 0, 2), $item_num, $row_num, "");
                     }
                  }
                  if (Session::getCurrentInterface() == 'central') {
                     if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
                        echo Search::showItem($output_type, $day['last_visit'], $item_num, $row_num, "");
                        //                        echo Search::showItem($output_type, Html::formatNumber($day['price'], 0, 2), $item_num, $row_num, "");
                        echo Search::showItem($output_type, $day['forfait'], $item_num, $row_num, "");
                        echo Search::showItem($output_type, $day['reste_montant'], $item_num, $row_num, "");
                     } else {
                        echo Search::showItem($output_type, $day['last_visit'], $item_num, $row_num, "colspan='2' ");
                        if ($output_type != Search::HTML_OUTPUT) {
                           //                           echo Search::showItem($output_type, '', $item_num, $row_num, "");
                           echo Search::showItem($output_type, '', $item_num, $row_num, "");
                           echo Search::showItem($output_type, '', $item_num, $row_num);
                        }
                     }
                     if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $output_type != Search::HTML_OUTPUT) {
                        echo Search::showItem($output_type, '', $item_num, $row_num);
                        echo Search::showItem($output_type, '', $item_num, $row_num);
                     }
                  }
                  echo Search::showEndLine($output_type);
               }

               if (Session::getCurrentInterface() == 'central') {
                  $row_num++;
                  $item_num = 0;

                  echo Search::showNewLine($output_type);
                  if ($output_type != Search::HTML_OUTPUT)
                     echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");

                  echo Search::showHeaderItem($output_type, '', $item_num, $row_num, 0, '', " $colspanNoprice style='" . PluginManageentitiesMonthly::$style[1] . "'");

                  echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "colspan='2' style='" . PluginManageentitiesMonthly::$style[1] . "'");

                  echo Search::showHeaderItem($output_type, __('Subtotal', 'manageentities'), $item_num, '', 0, '', "colspan='2' style='" . PluginManageentitiesMonthly::$style[1] . "'");

                  echo Search::showHeaderItem($output_type, Html::formatNumber($contract['contract_tot']['contract_credit'], 0, 2), $item_num, '', 0, '', "$colspanNoprice style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  echo Search::showHeaderItem($output_type, Html::formatNumber($contract['contract_tot']['contract_conso'], 0, 2), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  echo Search::showHeaderItem($output_type, Html::formatNumber($contract['contract_tot']['contract_reste'], 0, 2), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  echo Search::showHeaderItem($output_type, Html::formatNumber($contract['contract_tot']['contract_depass'], 0, 2), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");

                  if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
                     echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");

                     echo Search::showHeaderItem($output_type, Html::formatNumber($contract['contract_tot']['contract_forfait'], 0, 2), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                     echo Search::showHeaderItem($output_type, Html::formatNumber($contract['contract_tot']['contract_reste_montant'], 0, 2), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  } else {
                     echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', " $colspanNoprice style='" . PluginManageentitiesMonthly::$style[1] . "'");
                     if ($output_type != Search::HTML_OUTPUT) {
                        echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                        echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                     }
                  }
                  if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $output_type != Search::HTML_OUTPUT) {
                     echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                     echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  }
                  echo Search::showEndLine($output_type);
               }
            } else {
               //line total
               if (Session::getCurrentInterface() == 'central') {

                  if ($output_type == Search::HTML_OUTPUT) {
                     $row_num++;
                     $item_num = 0;

                     if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) $colspanTotal = "colspan = '14'"; else $colspanTotal = "colspan = '13'";

                     echo Search::showNewLine($output_type);
                     echo Search::showItem($output_type, '', $item_num, $row_num, "$colspanTotal style='" . PluginManageentitiesMonthly::$style[0] . "'");
                     echo Search::showEndLine($output_type);
                  }

                  $row_num++;
                  $item_num = 0;

                  echo Search::showNewLine($output_type);
                  if ($output_type != Search::HTML_OUTPUT) echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', " $colspanNoprice style='" . PluginManageentitiesMonthly::$style[1] . "'");

                  echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "colspan ='2' style='" . PluginManageentitiesMonthly::$style[1] . "'");

                  echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "colspan ='2' style='" . PluginManageentitiesMonthly::$style[1] . "'");

                  echo Search::showHeaderItem($output_type, __('Total initial credit', 'manageentities'), $item_num, '', 0, '', "$colspanNoprice style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  echo Search::showHeaderItem($output_type, __('Total consummated', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  echo Search::showHeaderItem($output_type, __('Total remaining', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  echo Search::showHeaderItem($output_type, __('Total exceeding', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");

                  if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
                     echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                     echo Search::showHeaderItem($output_type, __('Total Guaranteed package', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                     echo Search::showHeaderItem($output_type, __('Remaining total (amount)', 'manageentities'), $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  } else {
                     echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', " $colspanNoprice style='" . PluginManageentitiesMonthly::$style[1] . "'");
                     if ($output_type != Search::HTML_OUTPUT) {
                        echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                        echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                     }
                  }
                  if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $output_type != Search::HTML_OUTPUT) {
                     echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                     echo Search::showHeaderItem($output_type, '', $item_num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
                  }
                  echo Search::showEndLine($output_type);

                  $row_num++;
                  $item_num = 0;

                  echo Search::showNewLine($output_type);
                  if ($output_type != Search::HTML_OUTPUT) echo Search::showItem($output_type, '', $item_num, $row_num);
                  echo Search::showItem($output_type, '', $item_num, $row_num, " $colspanNoprice ");

                  echo Search::showItem($output_type, '', $item_num, $row_num, "colspan='2'");

                  echo Search::showItem($output_type, '', $item_num, $row_num, "colspan='2' ");

                  echo Search::showItem($output_type, Html::formatNumber($contract['tot_credit'], 0, 2), $item_num, $row_num, "$colspanNoprice ");
                  echo Search::showItem($output_type, Html::formatNumber($contract['tot_conso'], 0, 2), $item_num, $row_num, "");
                  echo Search::showItem($output_type, Html::formatNumber($contract['tot_reste'], 0, 2), $item_num, $row_num, "");
                  echo Search::showItem($output_type, Html::formatNumber($contract['tot_depass'], 0, 2), $item_num, $row_num, "");

                  echo Search::showItem($output_type, '', $item_num, $row_num, " ");

                  if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
                     echo Search::showItem($output_type, Html::formatNumber($contract['tot_forfait'], 0, 2), $item_num, $row_num, "");
                     echo Search::showItem($output_type, Html::formatNumber($contract['tot_reste_montant'], 0, 2), $item_num, $row_num, "");
                  } else {
                     echo Search::showItem($output_type, '', $item_num, $row_num, " $colspanNoprice ");
                     if ($output_type != Search::HTML_OUTPUT) {
                        echo Search::showItem($output_type, '', $item_num, $row_num, "");
                     }
                  }
                  if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $output_type != Search::HTML_OUTPUT) {
                     echo Search::showItem($output_type, '', $item_num, $row_num, "");
                     echo Search::showItem($output_type, '', $item_num, $row_num);
                  }
                  echo Search::showEndLine($output_type);
               }
            }
         }
         if ($output_type == Search::HTML_OUTPUT) {
            Html::closeForm();
         }
         // Display footer
         echo Search::showFooter($output_type, __('Entities portal', 'manageentities') . " - " . __('General follow-up', 'manageentities'));
      } else {
         echo Search::showError($output_type);
      }
   }


   /**
    * Print generic new line
    *
    * @param $type         display type (0=HTML, 1=Sylk,2=PDF,3=CSV)
    * @param $odd          is it a new odd line ? (false by default)
    * @param $is_deleted   is it a deleted search ? (false by default)
    * @param $color        color the line with this one
    *
    * @return string to display
    **/
   static function showNewLine($type, $odd = false, $is_deleted = false, $color = "") {

      $out = "";
      switch ($type) {
         case Search::PDF_OUTPUT_LANDSCAPE : //pdf
         case Search::PDF_OUTPUT_PORTRAIT :
            global $PDF_TABLE;
            $style = "";
            if ($odd) {
               $style = " style=\"background-color:#DDDDDD;\" ";
            }
            $PDF_TABLE .= "<tr $style nobr=\"true\">";
            break;

         case Search::SYLK_OUTPUT : //sylk
         case Search::CSV_OUTPUT : //csv
            break;

         default :

            if ($color != "") {
               $class = " style='background-color:" . $color . "' ";
            } else {
               $class = " class='tab_bg_1' ";
               if ($odd) {
                  $class = " class='tab_bg_2' ";
               }
            }
            $out = "<tr $class >";
      }
      return $out;
   }

   static function showLegendary() {
      $contractstate = new PluginManageentitiesContractState();
      $contracts     = $contractstate->find();
      $nb            = count($contracts);
      echo "<div align='center'>";
      echo "<table class='tab_cadre'><tr><th colspan='20'>" . __('Caption') . "</th></tr>";
      $i = 0;
      foreach ($contracts as $contract) {
         if ($i == 10) {
            echo "</tr><tr>";
         }
         echo "<td width=10px style='background-color:" . $contract['color'] . "'> </td>";
         echo "<td> " . $contract['name'] . "</td>";
         $i = $i + 1;
      }
      echo "</tr></table></br>";
      echo "</div>";
   }

   function showCriteriasForm($options = []) {
      global $DB;
      PluginManageentitiesEntity::showManageentitiesHeader(__('General follow-up', 'manageentities'));

      if (Session::getCurrentInterface() == 'central') {

         $rand = mt_rand();

         echo "<form method='post' name='criterias_form$rand' id='criterias_form$rand'
               action=\"./entity.php\">";

         echo "<div align='spaced'><table class='tab_cadrehov'>";

         echo "<tr class='tab_bg_1'>";
         if ((isset($_SESSION['glpiactive_entity_recursive'])
              && $_SESSION['glpiactive_entity_recursive'])
             || (isset($_SESSION['glpishowallentities'])
                 && $_SESSION['glpishowallentities'])) {
            echo "<td>" . __('Entity') . "</td>";
            echo "<td>";
            Dropdown::show('Entity', ['value' => $options['entities_id']]);
            echo "</td>";
            $colspan = '1';
         } else {
            $colspan = '2';
            echo "<input type='hidden' name='entities_id' value='-1'>";
         }

         $plugin_config = new PluginManageentitiesConfig();
         $config_states = $plugin_config->find();
         $config_states = reset($config_states);

         $plugin_pref = new PluginManageentitiesPreference();
         $preferences = $plugin_pref->find(['users_id' => Session::getLoginUserID()]);
         $preferences = reset($preferences);

         $contractstate  = new PluginManageentitiesContractState();
         $contractstates = $contractstate->find();
         $states         = [];
         foreach ($contractstates as $key => $val) {
            $states[$key] = $val['name'];
         }
         echo "<td class='left' colspan='$colspan'>" . _n('State of contract', 'States of contract', 2, 'manageentities') . "</td>";
         echo "<td class='left' colspan='$colspan'>";

         if (isset($options['contract_states']) && $options['contract_states'] != '0') {
            Dropdown::showFromArray("contract_states", $states, ['multiple' => true,
                                                                 'width'    => 200,
                                                                 'values'   => $options['contract_states']]);
         } elseif (isset($preferences['contract_states']) && $preferences['contract_states'] != NULL) {
            $options['contract_states'] = json_decode($preferences['contract_states'], true);
            Dropdown::showFromArray("contract_states", $states, ['multiple' => true,
                                                                 'width'    => 200,
                                                                 'values'   => $options['contract_states']]);
         } elseif (isset($config_states['contract_states']) && $config_states['contract_states'] != NULL) {
            $options['contract_states'] = json_decode($config_states['contract_states'], true);
            Dropdown::showFromArray("contract_states", $states, ['multiple' => true,
                                                                 'width'    => 200,
                                                                 'values'   => $options['contract_states']]);
         } else {
            Dropdown::showFromArray("contract_states", $states, ['multiple' => true,
                                                                 'width'    => 200,
                                                                 'value'    => "contract_states"]);
         }
         echo "</td></tr><tr class='tab_bg_1'>";

         echo "<td class='left'>" . __('Begin date') . " " .
              __('of period of contract', 'manageentities') . ", " . __('after') . "</td>";
         echo "<td class='left'>";
         Html::showDateField("begin_date_after", ['value' => $options['begin_date_after']]);
         echo "</td>";
         echo "<td class='left'>" . __('Begin date') . " " .
              __('of period of contract', 'manageentities') . ", " . __('before') . "</td>";
         echo "<td class='left'>";
         Html::showDateField("begin_date_before", ['value' => $options['begin_date_before']]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td class='left'>" . __('End date') . " " .
              __('of period of contract', 'manageentities') . ", " . __('after') . "</td>";
         echo "<td class='left'>";
         Html::showDateField("end_date_after", ['value' => $options['end_date_after']]);
         echo "</td>";
         echo "<td class='left'>" . __('End date') . " " .
              __('of period of contract', 'manageentities') . ", " . __('before') . "</td>";
         echo "<td class='left'>";
         Html::showDateField("end_date_before", ['value' => $options['end_date_before']]);
         echo "</td>";
         echo "</tr>";


         $query = "SELECT  `glpi_users`.*, `glpi_plugin_manageentities_businesscontacts`.`id` as users_id
                  FROM `glpi_plugin_manageentities_businesscontacts`, `glpi_users`
                  WHERE `glpi_plugin_manageentities_businesscontacts`.`users_id`=`glpi_users`.`id`
                  GROUP BY `glpi_plugin_manageentities_businesscontacts`.`users_id`";

         $result = $DB->query($query);
         $users  = [];
         while ($data = $DB->fetchAssoc($result)) {
            $users[$data['id']] = $data['realname'] . " " . $data['firstname'];
         }

         echo "<tr class='tab_bg_1'>";
         echo "<td class='left'>";
         echo __('Business', 'manageentities');
         echo "</td>";
         echo "<td class='left'>";

         if (isset($options['business_id']) && $options['business_id'] != '0') {
            Dropdown::showFromArray("business_id", $users, ['multiple' => true,
                                                            'width'    => 200,
                                                            'values'   => $options['business_id']]);
         } elseif (isset($preferences['business_id']) && $preferences['business_id'] != NULL) {
            $options['business_id'] = json_decode($preferences['business_id'], true);
            Dropdown::showFromArray("business_id", $users, ['multiple' => true,
                                                            'width'    => 200,
                                                            'values'   => $options['business_id']]);
         } elseif (isset($config_states['business_id']) && $config_states['business_id'] != NULL) {
            $options['business_id'] = json_decode($config_states['business_id'], true);
            Dropdown::showFromArray("business_id", $users, ['multiple' => true,
                                                            'width'    => 200,
                                                            'values'   => $options['business_id']]);
         } else {
            Dropdown::showFromArray("business_id", $users, ['multiple' => true,
                                                            'width'    => 200,
                                                            'value'    => 'name']);
         }

         $plugin_company = new PluginManageentitiesCompany();
         $result         = $plugin_company->find();

         $company = [];
         foreach ($result as $data) {
            $company[$data['id']] = $data['name'];
         }
         echo "</td>";
         echo "<td class='left'>";
         echo _n('Company', 'Companies', 2, 'manageentities');
         echo "</td>";
         echo "<td class='left'>";

         if (isset($options['company_id']) && $options['company_id'] != '0') {
            Dropdown::showFromArray("company_id", $company, ['multiple' => true,
                                                             'width'    => 200,
                                                             'values'   => $options['company_id']]);
         } elseif (isset($preferences['companies_id']) && $preferences['companies_id'] != NULL) {
            $options['company_id'] = json_decode($preferences['companies_id'], true);
            Dropdown::showFromArray("company_id", $company, ['multiple' => true,
                                                             'width'    => 200,
                                                             'values'   => $options['company_id']]);
         } else {
            Dropdown::showFromArray("company_id", $company, ['multiple' => true,
                                                             'width'    => 200,
                                                             'value'    => 'name']);
         }
         echo "</td></tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td class='center' colspan='4'>";
         echo "<input type='submit' name='searchcontract' value='" . _sx('button', 'Search') . "' class='submit'>";
         echo "<input type='hidden' name='begin_date' value='" . $options['begin_date'] . "'>";
         echo "<input type='hidden' name='end_date' value='" . $options['end_date'] . "'>";
         echo "</td></tr>";


         echo "</table></div>";

         Html::closeForm();
      }
   }

   static function printPager($start, $numrows, $target, $parameters, $item_type_output = 0, $item_type_output_param = 0) {
      global $CFG_GLPI;

      $list_limit = $_SESSION['glpilist_limit'];
      // Forward is the next step forward
      $forward = $start + $list_limit;

      // This is the end, my friend
      $end = $numrows - $list_limit;

      // Human readable count starts here
      $current_start = $start + 1;

      // And the human is viewing from start to end
      $current_end = $current_start + $list_limit - 1;
      if ($current_end > $numrows) {
         $current_end = $numrows;
      }

      // Backward browsing
      if ($current_start - $list_limit <= 0) {
         $back = 0;
      } else {
         $back = $start - $list_limit;
      }

      // Print it

      echo "<form method='GET' action=\"" . $CFG_GLPI["root_doc"] .
           "/front/report.dynamic.php\" target='_blank'>\n";

      echo "<table class='tab_cadre_pager'>\n";
      echo "<tr>\n";

      if (Session::getCurrentInterface()
          && Session::getCurrentInterface()) {
         echo "<td class='tab_bg_2' width='30%'>";

         echo "<input type='hidden' name='item_type' value='" . $item_type_output . "'>";
         if ($item_type_output_param != 0)
            echo "<input type='hidden' name='item_type_param' value='" .
                 serialize($item_type_output_param) . "'>";

         $explode = explode("&amp;", $parameters);
         for ($i = 0; $i < count($explode); $i++) {
            $pos = strpos($explode[$i], '=');
            echo "<input type='hidden' name=\"" . substr($explode[$i], 0, $pos) . "\" value=\"" .
                 substr($explode[$i], $pos + 1) . "\">";
         }
         echo "<select name='display_type'>";
         echo "<option value='" . Search::PDF_OUTPUT_LANDSCAPE . "'>" . __('Current page in landscape PDF') . "</option>";
         echo "<option value='" . Search::PDF_OUTPUT_PORTRAIT . "'>" . __('Current page in portrait PDF') . "</option>";
         echo "<option value='" . Search::SYLK_OUTPUT . "'>" . __('Current page in SLK') . "</option>";
         echo "<option value='" . Search::CSV_OUTPUT . "'>" . __('Current page in CSV') . "</option>";
         echo "</select>&nbsp;";
         echo "<button type='submit' name='export' class='unstyled pointer' " .
              " title=\"" . _sx('button', 'Export') . "\">" .
              "<i class='far fa-save'></i><span class='sr-only'>" . _sx('button', 'Export') . "<span>";
         echo "</td>";
      }

      // End pager
      echo "</tr>\n";
      echo "</table><br>\n";
   }

}