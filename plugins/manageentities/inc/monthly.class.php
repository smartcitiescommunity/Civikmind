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

class PluginManageentitiesMonthly extends CommonDBTM {

   static $rightname = 'plugin_manageentities';

   // Css styles/class
   static $style = ['background-color: #FEC95C;color:#000',
                    'text-align:left',
                    'background-color: #FA6B6B;',
                    'background-color:#FFBA3B'];
   static $class = ['styleItemTitle', 'styleContractTitle'];

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   static function queryMonthly($values = []) {
      global $DB;

      $tabResults        = [];
      $tot_conso         = 0;
      $tot_depass        = 0;
      $tot_depass_amount = 0;
      $tot_conso_amount  = 0;
      $tot_credit        = 0;
      $taskCount         = 0;
      $dbu               = new DbUtils();
      // We configure the type of contract Hourly or Dayly
      $config = PluginManageentitiesConfig::getInstance();
      if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {// Hourly
         $configHourOrDay = "AND (`glpi_plugin_manageentities_contracts`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_NULL . "' 
                             OR `glpi_plugin_manageentities_contracts`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_HOUR . "'
                             OR `glpi_plugin_manageentities_contracts`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_INTERVENTION . "'
                             OR `glpi_plugin_manageentities_contracts`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED . "')";
         // Daily
      } else {
         $configHourOrDay = "AND (`glpi_plugin_manageentities_contractdays`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_NULL . "'
                             OR `glpi_plugin_manageentities_contractdays`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_AT . "'
                             OR `glpi_plugin_manageentities_contractdays`.`contract_type`='" . PluginManageentitiesContract::CONTRACT_TYPE_FORFAIT . "')";
      }

      //      $condition = getEntitiesRestrictRequest("", "glpi_plugin_manageentities_contractdays");
      $condition = " " . $dbu->getEntitiesRestrictRequest("", "glpi_entities");

      $queryEntity = "SELECT DISTINCT(`glpi_entities`.`id`) AS entities_id,
                        `glpi_entities`.`name` AS entities_name
                     FROM `glpi_tickets`
                     
                     LEFT JOIN `glpi_entities`
                        ON (`glpi_entities`.`id`
                           = `glpi_tickets`.`entities_id`)
                           
                     LEFT JOIN `glpi_tickettasks`
                        ON (`glpi_tickets`.`id`
                           = `glpi_tickettasks`.`tickets_id`)
                           
                     WHERE $condition
                     
                     AND (`glpi_tickettasks`.`begin` <= ADDDATE('" . $values['end_date'] . "', INTERVAL 1 DAY)
                           OR `glpi_tickettasks`.`begin` IS NULL)
                           
                     AND (`glpi_tickettasks`.`end` >= '" . $values['begin_date'] . "'
                           OR `glpi_tickettasks`.`end` IS NULL)
  
                     ORDER BY `glpi_entities`.`name`,
                              `glpi_tickettasks`.`end` ASC";

      $resEntity   = $DB->query($queryEntity);
      $nbTotEntity = ($resEntity ? $DB->numrows($resEntity) : 0);

      //We get entities datas
      if ($resEntity && $nbTotEntity > 0) {
         while ($dataEntity = $DB->fetchArray($resEntity)) {
            $tabResults[$dataEntity['entities_id']]['entities_name'] = $dataEntity['entities_name'];
            $tabResults[$dataEntity['entities_id']]['entities_id']   = $dataEntity['entities_id'];

            $queryContractDay = "SELECT `glpi_plugin_manageentities_contractdays`.`name`       AS name_contractdays,
                                        `glpi_plugin_manageentities_contractdays`.`id`         AS contractdays_id,
                                        `glpi_plugin_manageentities_contractdays`.`report`     AS report,
                                        `glpi_plugin_manageentities_contractdays`.`nbday`      AS nbday,
                                        `glpi_plugin_manageentities_contractdays`.`begin_date` AS begin_date,
                                        `glpi_plugin_manageentities_contractdays`.`end_date`   AS end_date,
                                        `glpi_plugin_manageentities_contractdays`.`charged`    AS charged,
                                        `glpi_plugin_manageentities_contractdays`.`plugin_manageentities_contractstates_id` AS contractstates_id,
                                        `glpi_plugin_manageentities_contractdays`.`plugin_manageentities_critypes_id`,";

            if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {// Hourly
               $queryContractDay .= "`glpi_plugin_manageentities_contracts`.`contract_type` AS contract_type,";
            } else {
               $queryContractDay .= "`glpi_plugin_manageentities_contractdays`.`contract_type` AS contract_type,";
            }

            $queryContractDay .= "`glpi_contracts`.`name` AS name,
                                        `glpi_contracts`.`num`  AS num,
                                        `glpi_contracts`.`id`   AS contracts_id,
                                        `glpi_contracts`.`entities_id` AS entities_id,
                                        `glpi_plugin_manageentities_contractstates`.`is_closed` AS is_closed,
                                        `glpi_plugin_manageentities_contractstates`.`color`
                        
                     FROM `glpi_plugin_manageentities_contractdays`
                        
                     LEFT JOIN `glpi_contracts`
                        ON (`glpi_contracts`.`id`
                        = `glpi_plugin_manageentities_contractdays`.`contracts_id`)
                  
                     LEFT JOIN `glpi_plugin_manageentities_contracts`
                        ON (`glpi_contracts`.`id`
                        = `glpi_plugin_manageentities_contracts`.`contracts_id`)
                  
                     LEFT JOIN `glpi_plugin_manageentities_contractstates`
                        ON (`glpi_plugin_manageentities_contractstates`.`id`
                        = `glpi_plugin_manageentities_contractdays`.`plugin_manageentities_contractstates_id`)
                        
                     LEFT JOIN `glpi_plugin_manageentities_cridetails` 
                        ON (`glpi_plugin_manageentities_cridetails`.`plugin_manageentities_contractdays_id` = `glpi_plugin_manageentities_contractdays`.`id`)
                                            
                     LEFT JOIN `glpi_tickets` 
                        ON (`glpi_plugin_manageentities_cridetails`.`tickets_id` = `glpi_tickets`.`id`)
                     
                     LEFT JOIN `glpi_tickettasks` 
                        ON (`glpi_tickettasks`.`tickets_id` = `glpi_tickets`.`id`)
                        
                     WHERE `glpi_plugin_manageentities_contractdays`.`entities_id`='" . $dataEntity['entities_id'] . "'
                           
                     AND `glpi_contracts`.`is_deleted` != 1
                            
                     AND (`glpi_tickettasks`.`begin` >= '" . $values['begin_date'] . " 00:00:00'
                           OR `glpi_tickettasks`.`begin` IS NULL)
                                 
                     AND (`glpi_tickettasks`.`end` <= '" . $values['end_date'] . " 23:59:59'
                           OR `glpi_tickettasks`.`end` IS NULL)
                                 
                     " . $configHourOrDay . " 
                     
                     GROUP BY `glpi_plugin_manageentities_contractdays`.`id`
                     
                     ORDER BY `glpi_contracts`.`name`,
                              `glpi_plugin_manageentities_contractdays`.`end_date` ASC";


            //                                 AND (`glpi_plugin_manageentities_contractdays`.`begin_date` <= ADDDATE('".$values['end_date']."', INTERVAL 1 DAY)
            //                           OR `glpi_plugin_manageentities_contractdays`.`begin_date` IS NULL)
            //
            //                     AND (`glpi_plugin_manageentities_contractdays`.`end_date` >= '".$values['begin_date']."'
            //                           OR `glpi_plugin_manageentities_contractdays`.`end_date` IS NULL)
            $resContractDay   = $DB->query($queryContractDay);
            $nbTotContractDay = ($resContractDay ? $DB->numrows($resContractDay) : 0);

            // We get contract days datas
            if ($resContractDay && $nbTotContractDay > 0) {
               while ($dataContractDay = $DB->fetchAssoc($resContractDay)) {
                  $contract_credit = 0;

                  // We get all cri details
                  $resultCriDetail             = PluginManageentitiesCriDetail::getCriDetailData($dataContractDay,
                                                                                                 ['contract_type_id' => $dataContractDay['contract_type'],
                                                                                                  'begin_date'       => $values['begin_date'],
                                                                                                  'end_date'         => $values['end_date']]);
                  $resultCriDetail_beforeMonth = PluginManageentitiesCriDetail::getCriDetailData($dataContractDay,
                                                                                                 ['contract_type_id' => $dataContractDay['contract_type'],
                                                                                                  'end_date'         => date('Y-m-d', strtotime($values['begin_date'] . ' - 1 DAY'))]);

                  $remaining = $lastMonthRemaining = $resultCriDetail_beforeMonth['resultOther']['reste'];

                  if (sizeof($resultCriDetail['result']) > 0) {
                     // Credit
                     $credit          = $dataContractDay['nbday'] + $dataContractDay['report'];
                     $contract_credit += $credit;
                     $tot_credit      += $credit;

                     // link of contract
                     $link_contract = Toolbox::getItemTypeFormURL("Contract");
                     $name_contract = "<a href='" . $link_contract . "?id=" . $dataContractDay["contracts_id"] . "' target='_blank'>";
                     if ($dataContractDay["num"] == NULL) $name_contract .= "(" . $dataContractDay["contracts_id"] . ")";
                     else $name_contract .= $dataContractDay["num"];
                     $name_contract .= "</a>";

                     // Contract day informations
                     $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']]['name_contract']     = $name_contract;
                     $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']]['name_contractdays'] = $dataContractDay["name_contractdays"];
                     $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']]['contracts_id']      = $dataContractDay["name_contractdays"];
                     $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']]['is_closed']         = $dataContractDay["is_closed"];
                     $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']]['num']               = $dataContractDay["num"];
                     $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']]['contract_type']     = PluginManageentitiesContract::getContractType($dataContractDay["contract_type"]);
                     $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']]['credit']            = $credit;

                     foreach ($resultCriDetail['result'] as $cridetails_id => $dataCriDetail) {
                        $taskCount++;

                        // Conso per tech
                        $conso_per_tech = [];
                        $contract_conso = 0;
                        foreach ($dataCriDetail['conso_per_tech'] as $tickets) {
                           foreach ($tickets as $users_id => $time) {
                              $remaining -= $time['conso'];
                              $depass    = 0;


                              if ($remaining < 0) {
                                 $depass    = abs($remaining);
                                 $remaining = 0;
                              }

                              $contract_conso                             += $time['conso'];
                              $tot_conso                                  += $time['conso'];
                              $tot_depass                                 += $depass;
                              $conso_per_tech[$users_id]['conso']         = $time['conso'];
                              $conso_per_tech[$users_id]['depass']        = $depass;
                              $conso_per_tech[$users_id]['depass_amount'] = $conso_per_tech[$users_id]['depass'] * $dataCriDetail['pricecri'];
                              $conso_per_tech[$users_id]['conso_amount']  = $time['conso'] * $dataCriDetail['pricecri'];
                              $tot_conso_amount                           += $conso_per_tech[$users_id]['conso_amount'];
                              $tot_depass_amount                          += $conso_per_tech[$users_id]['depass_amount'];
                           }
                        }

                        // Task informations
                        $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']][$cridetails_id]['conso_per_tech'] = $conso_per_tech;
                        $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']][$cridetails_id]['tech']           = $dataCriDetail['tech'];
                        $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']][$cridetails_id]['documents_id']   = $dataCriDetail['documents_id'];
                        $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']][$cridetails_id]['pricecri']       = $dataCriDetail['pricecri'];
                     }

                     // Contract informations
                     $contractdays_state = '';
                     $color              = $dataContractDay["color"];
                     if ($dataContractDay['contract_type'] == PluginManageentitiesContract::CONTRACT_TYPE_AT) {
                        if ($dataContractDay['charged'] == 0) {
                           $contractdays_state = __('To present an invoice', 'manageentities');
                        } else {
                           $contractdays_state = __('Already charged', 'manageentities');
                        }

                     } else if ($dataContractDay['contract_type'] == PluginManageentitiesContract::CONTRACT_TYPE_FORFAIT) {
                        if ($contract_credit - $contract_conso <= 0) {
                           if ($dataContractDay['charged'] == 0) {
                              $contractdays_state = __('To present an invoice', 'manageentities');
                              if ($dataContractDay["is_closed"]) {
                                 $color = self::$style[3];
                              }
                           } else {
                              $contractdays_state = __('Already charged', 'manageentities');
                           }
                        } elseif ($dataContractDay["contract_type"] == PluginManageentitiesContract::CONTRACT_TYPE_FORFAIT && $dataContractDay['charged']) {
                           $contractdays_state = __('Already charged', 'manageentities');
                        } elseif ($dataContractDay["contract_type"] == PluginManageentitiesContract::CONTRACT_TYPE_FORFAIT && $dataContractDay["is_closed"]) {
                           $contractdays_state = __('To present an invoice', 'manageentities');
                           $color              = self::$style[3];
                        } else {
                           $contractdays_state = __('In progress', 'manageentities');
                        }
                     }

                     $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']]['contractstates_color'] = $color;
                     $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']]['contract_credit']      = $contract_credit;
                     $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']]['contract_remaining']   = $lastMonthRemaining;
                     $tabResults[$dataEntity['entities_id']][$dataContractDay['contractdays_id']]['contractdays_state']   = $contractdays_state;
                  }
               }
            }
         }
      }

      // Total of all
      if ($taskCount != 0) {
         $tabResults['tot_credit']        = $tot_credit;
         $tabResults['tot_conso']         = $tot_conso;
         $tabResults['tot_depass']        = $tot_depass;
         $tabResults['tot_conso_amount']  = $tot_conso_amount;
         $tabResults['tot_depass_amount'] = $tot_depass_amount;
      }

      return $tabResults;
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
            $class = " class='tab_bg_1' ";
            if ($odd) {
               $class = " class='tab_bg_2' ";
            }
            $out = "<tr $class >";
      }
      return $out;
   }

   static function showMonthly($values = []) {
      global $PDF, $DB;

      $results = self::queryMonthly($values);

      $output_type = Search::HTML_OUTPUT;
      $PDF         = new PluginManageentitiesCriPDF('P', 'mm', 'A4');
      $parameters  = "begin_date=" . $values['begin_date'] . "&amp;end_date=" . $values['end_date'];
      $config      = PluginManageentitiesConfig::getInstance();

      $message_header = '';
      $message_body   = '';
      $numrows        = 1;
      $row_num        = 0;
      $start          = 0;
      $count_tasks    = 0;
      $nbColumn       = 14;
      $dbu            = new DbUtils();
      if ($config->fields['useprice'] != PluginManageentitiesConfig::PRICE) {
         $nbColumn = $nbColumn - 3;
      }

      if (isset($values["display_type"])) $output_type = $values["display_type"];

      $year   = date("Y");
      $month  = date('m', mktime(12, 0, 0, date("m"), 0, date("Y")));
      $date   = $year . "-" . $month . "-01";
      $query  = PluginManageentitiesContractDay::queryOldContractDaywithInterventions($date);
      $result = $DB->query($query);
      $nb     = $DB->numrows($result);
      if ($nb && $output_type == search::HTML_OUTPUT) {
         echo "<div class = 'center red b'>" . __('Warning : There are supplementary interventions which depends on  a prestation with a earlier end date', 'manageentities') . "</div>";
         echo _n('Ticket', 'Tickets', $nb);
         echo " : ";
         while ($data = $DB->fetchArray($result)) {
            $ticket = new Ticket();
            $ticket->getFromDB($data["tickets_id"]);
            echo $ticket->getLink() . "<br>";
         }

      }

      $num = 0;
      // Show headers
      $message_header .= Search::showHeader($output_type, 1, $nbColumn);
      $message_header .= Search::showBeginHeader($output_type);
      $message_header .= Search::showNewLine($output_type, ($row_num % 2));
      $message_header .= Search::showHeaderItem($output_type, _n('Client', 'Clients', 1, 'manageentities'), $num);
      $message_header .= Search::showHeaderItem($output_type, __('Contract'), $num);
      $message_header .= Search::showHeaderItem($output_type, PluginManageentitiesContractDay::getTypeName(1), $num);

      if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
         $message_header .= Search::showHeaderItem($output_type, __('Mode of management', 'manageentities'), $num);
      } else {
         $message_header .= Search::showHeaderItem($output_type, __('Type of contract', 'manageentities'), $num);
      }
      $message_header .= Search::showHeaderItem($output_type, __('Initial credit', 'manageentities'), $num);
      $message_header .= Search::showHeaderItem($output_type, __('Remaining on ', 'manageentities') . ' ' . Html::convDate($values['begin_date']), $num);
      if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
         if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
            $message_header .= Search::showHeaderItem($output_type, __('Daily rate', 'manageentities'), $num);
         } else {
            $message_header .= Search::showHeaderItem($output_type, __('Hourly rate', 'manageentities'), $num);
         }
      }
      $message_header .= Search::showHeaderItem($output_type, __('Production', 'manageentities'), $num);
      $message_header .= Search::showHeaderItem($output_type, _n('Current skateholder', 'Current stakeholders', 2, 'manageentities'), $num);
      if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
         $message_header .= Search::showHeaderItem($output_type, __('Total production', 'manageentities'), $num);
      }
      $message_header .= Search::showHeaderItem($output_type, __('Exceeding', 'manageentities'), $num);
      $message_header .= Search::showHeaderItem($output_type, _n('Current skateholder', 'Current stakeholders', 2, 'manageentities'), $num);
      if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
         $message_header .= Search::showHeaderItem($output_type, __('Total exceeding', 'manageentities'), $num);
      }
      $message_header .= Search::showHeaderItem($output_type, __('State of intervention', 'manageentities'), $num);
      $message_header .= Search::showEndLine($output_type);
      $message_header .= Search::showEndHeader($output_type);

      // We get all datas for monthly display
      foreach ($results as $dataEntity) {
         $firstEntity = true;
         if (is_array($dataEntity) && sizeof($dataEntity) > 2) {
            Session::initNavigateListItems("PluginManageentitiesContractDay");

            foreach ($dataEntity as $idContractDay => $dataContractDay) {
               if (is_array($dataContractDay)) {
                  Session::addToNavigateListItems("PluginManageentitiesContractDay", $idContractDay);

                  // Display details of contract
                  foreach ($dataContractDay as $dataTask) {
                     if (is_array($dataTask)) {
                        foreach ($dataTask['conso_per_tech'] as $users_id => $conso) {
                           $count_tasks++;

                           if ($conso['depass'] > 0) {
                              $depassClass = " style='" . self::$style[2] . "' ";
                           } elseif ($dataContractDay['contractstates_color'] == self::$style[3]) {
                              $depassClass = " style='" . self::$style[3] . "' ";
                           } else {
                              $depassClass = "";
                           }

                           $row_num++;
                           $num          = 0;
                           $message_body .= self::showNewLine($output_type, ($row_num % 2), $dataContractDay['is_closed'], $dataContractDay['contractstates_color']);

                           // Client
                           if ($firstEntity) {
                              $message_body .= Search::showItem($output_type, $dataEntity['entities_name'], $num, $row_num, $depassClass);
                           } else {
                              $message_body .= Search::showItem($output_type, '', $num, $row_num, $depassClass);
                           }
                           // Contract
                           if ($output_type == search::HTML_OUTPUT) {
                              $message_body .= Search::showItem($output_type, $dataContractDay['name_contract'], $num, $row_num, $depassClass);
                           } else {
                              $message_body .= Search::showItem($output_type, $dataContractDay['num'], $num, $row_num, $depassClass);
                           }
                           //Period
                           $message_body .= Search::showItem($output_type, $dataContractDay['name_contractdays'], $num, $row_num, $depassClass);
                           // Management mode 
                           $message_body .= Search::showItem($output_type, $dataContractDay['contract_type'], $num, $row_num, $depassClass);
                           // Initial credit
                           $message_body .= Search::showItem($output_type, Html::formatNumber($dataContractDay['contract_credit'], 0, 2), $num, $row_num, $depassClass);
                           // Remaining on
                           $message_body .= Search::showItem($output_type, Html::formatNumber($dataContractDay['contract_remaining'], 0, 2), $num, $row_num, $depassClass);
                           // Price cri
                           if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
                              $message_body .= Search::showItem($output_type, Html::formatNumber($dataTask['pricecri'], 0, 2), $num, $row_num, $depassClass);
                           }
                           // Conso
                           $message_body .= Search::showItem($output_type, self::checkValue($conso['conso'], $output_type), $num, $row_num, $depassClass);
                           // Stakeholder
                           $message_body .= Search::showItem($output_type, $dbu->getUserName($users_id), $num, $row_num, $depassClass);
                           // Total conso
                           if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
                              $message_body .= Search::showItem($output_type, Html::formatNumber($conso['conso_amount'], 0, 2), $num, $row_num, $depassClass);
                           }
                           // Depass
                           if (!empty($conso['depass'])) {
                              $message_body .= Search::showItem($output_type, self::checkValue($conso['depass'], $output_type), $num, $row_num, $depassClass);
                              // Stakeholder
                              $message_body .= Search::showItem($output_type, $dbu->getUserName($users_id), $num, $row_num, $depassClass);
                              // Total depass
                              if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
                                 $message_body .= Search::showItem($output_type, Html::formatNumber($conso['depass_amount'], 0, 2), $num, $row_num, $depassClass);
                              }
                           } else {
                              $message_body .= Search::showItem($output_type, '', $num, $row_num, $depassClass);
                              $message_body .= Search::showItem($output_type, '', $num, $row_num, $depassClass);
                              if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
                                 $message_body .= Search::showItem($output_type, '', $num, $row_num, $depassClass);
                              }
                           }

                           // Intervention state
                           $message_body .= Search::showItem($output_type, $dataContractDay['contractdays_state'], $num, $row_num, $depassClass);
                           $message_body .= Search::showEndLine($output_type);

                           $firstEntity = false;
                        }
                     }
                  }
               }
            }
         }
      }

      if ($count_tasks) {
         // Total
         $row_num++;
         $num          = 0;
         $message_body .= Search::showNewLine($output_type, ($row_num % 2));

         $colspan = 7;
         if ($config->fields['useprice'] != PluginManageentitiesConfig::PRICE) {
            $colspan = $colspan - 1;
         }
         $message_body .= Search::showHeaderItem($output_type, __('Total'), $num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");

         for ($i = 0; $i < 6; $i++) {
            $message_body .= Search::showHeaderItem($output_type, '', $num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
         }

         if ($results) {
            $message_body .= Search::showHeaderItem($output_type, Html::formatNumber($results['tot_conso'], 0, 2), $num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
            $message_body .= Search::showHeaderItem($output_type, '', $num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
            if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
               $message_body .= Search::showHeaderItem($output_type, Html::formatNumber($results['tot_conso_amount'], 0, 2), $num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
            }
            $message_body .= Search::showHeaderItem($output_type, Html::formatNumber($results['tot_depass'], 0, 2), $num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
            $message_body .= Search::showHeaderItem($output_type, '', $num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
            if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
               $message_body .= Search::showHeaderItem($output_type, Html::formatNumber($results['tot_depass_amount'], 0, 2), $num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
            }
            $message_body .= Search::showHeaderItem($output_type, '', $num, '', 0, '', "style='" . PluginManageentitiesMonthly::$style[1] . "'");
            $message_body .= Search::showEndLine($output_type);

            $message_body .= Search::showFooter($output_type, __('Entities portal', 'manageentities') . " - " . __('Monthly follow-up', 'manageentities'));
         }
         if ($output_type == Search::HTML_OUTPUT) {
            PluginManageentitiesFollowUp::printPager($start, $numrows, $_SERVER['PHP_SELF'], $parameters, "PluginManageentitiesMonthly");
         }
         echo $message_header . $message_body;

      } else {
         echo Search::showError($output_type);
      }
      if ($output_type == search::HTML_OUTPUT) {
         self::showLegendary();
      }
   }

   static function showLegendary() {
      $contractstate = new PluginManageentitiesContractState();
      $contracts     = $contractstate->find();
      $nb            = count($contracts);
      echo "<div align='center'>";
      echo "<table class='tab_cadre'><tr><th colspan='10'>" . __('Caption') . "</th></tr>";
      /*$i = 0;
      foreach ($contracts as $contract){
         if($i == 5){
            echo "</tr><tr>";
         }
         echo "<td width=10px style='background-color:".$contract['color']."'> </td>";
         echo "<td> ".$contract['name']."</td>";
         $i = $i + 1;
      }
      echo "</tr><tr>";*/
      echo "<tr><td width=10px style='" . self::$style[2] . "'></td>";
      echo "<td>" . __('Exceeding', 'manageentities') . "</td>";
      echo "<td width=10px style=" . self::$style[3] . "></td>";
      echo "<td>" . __('Closed') . " & " . __('To present an invoice', 'manageentities') . "</td>";
      echo "</tr></table></br>";
      echo "</div>";
   }

   static function checkValue($value, $output_type) {
      if (!empty($value)) {
         list($integer, $decimal) = explode('.', number_format($value, 2));
         if ($decimal != 00 && $decimal != 50 && $output_type == Search::HTML_OUTPUT) {
            return "<span style='color:red;'>" . html::formatNumber($value, 0, 2) . "</span>";
         }
      }
      return html::formatNumber($value, 0, 2);
   }

   function showHeader($options = []) {
      PluginManageentitiesEntity::showManageentitiesHeader(__('Monthly follow-up', 'manageentities'));

      $rand = mt_rand();
      echo "<form method='post' name='criterias_form$rand' id='criterias_form$rand'
               action=\"./entity.php\">";
      echo "<div class='plugin_manageentities_color' ><table style='margin: 0px auto 5px auto;'>";
      echo "<tr><td colspan='2' align='center' name='year'></td></tr>";
      echo "<tr><td>";
      echo "<ul id='last_year'></ul></td>";
      echo "<td><ul id='manageentities-months-list'></ul>";
      echo "<td>";
      echo "<ul id='next_year'></ul></td>";
      echo "</td></tr>";
      echo "</table></div>";
      $year  = ($_GET['year_current'] != 0) ? $_GET['year_current'] : Date('Y', strtotime('-1 month'));
      $month = date('m', strtotime($options['begin_date']));
      echo "<script type='text/javascript'>";
      echo "var yearIdElm = $('[name=\"year\"]');";
      echo "yearIdElm.html($year);";
      echo "lastYearManagesEntities('criterias_form$rand', '#last_year', $year, " . json_encode(Toolbox::getMonthsOfYearArray()) . ");";
      echo "manageentitiesShowMonth('criterias_form$rand', '#manageentities-months-list', " . json_encode(Toolbox::getMonthsOfYearArray()) . ", $year,  $month) ;";
      echo "nextYearManagesEntities('criterias_form$rand', '#next_year', $year, " . json_encode(Toolbox::getMonthsOfYearArray()) . ");";

      echo "</script>";

      echo "<div align='spaced'><table class='tab_cadrehov center'>";

      echo "<tr class='tab_bg_2'>";
      echo "<td class='center'>" . __('Begin date') . "</td>";
      echo "<td class='center'>";
      Html::showDateField("begin_date", ['value' => $options['begin_date']]);
      echo "</td><td class='center'>" . __('End date') . "</td>";
      echo "<td class='center'>";
      Html::showDateField("end_date", ['value' => $options['end_date']]);
      echo "</td></tr>";
      echo "<tr class='tab_bg_2'>";
      echo "<td class='center' colspan='8'>";
      echo "<input type='submit' name='searchmonthly' value='" . _sx('button', 'Search') . "' class='submit'>";
      echo "<input type='hidden' name='entities_id' value='" . $options['entities_id'] . "'>";
      echo "<input type='hidden' name='year_current' value=$year>";
      echo "</td></tr>";
      echo "</table></div>";

      Html::closeForm();
   }

}