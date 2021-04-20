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

class PluginManageentitiesDashboard extends CommonGLPI {

   public  $widgets = [];
   private $options;
   private $datas, $form;

   function __construct($options = []) {
      $this->options = $options;
   }

   function init() {


   }

   function getWidgetsForItem() {
      return [
         $this->getType() . "1" => __("Remaining days number by opened client contracts", "manageentities"),//Nombre de jours restants par contrat client
         $this->getType() . "2" => __("Client annuary", "manageentities"),
         $this->getType() . "3" => __("Tickets without CRI", "manageentities"),
         $this->getType() . "4" => __("Interventions with old contract", "manageentities"),
         $this->getType() . "5" => __("Opened contract prestations without remaining days", "manageentities"),
      ];
   }

   function getWidgetContentForItem($widgetId) {
      global $DB;

      $dbu = new DbUtils();
      if (empty($this->form))
         $this->init();
      switch ($widgetId) {
         case $this->getType() . "1":

            $plugin = new Plugin();
            $widget = new PluginMydashboardHtml();
            if ($plugin->isActivated("manageentities")) {
               $link_contract     = Toolbox::getItemTypeFormURL("Contract");
               $link_contract_day = Toolbox::getItemTypeFormURL("PluginManageentitiesContractDay");
               $entity            = new Entity();
               $contracts         = self::queryFollowUpSimplified($_SESSION['glpiactiveentities'], []);
               //               Toolbox::logDebug($contracts);
               $datas = [];
               if (!empty($contracts)) {
                  foreach ($contracts as $key => $contract_data) {
                     if (is_integer($key)) {

                        if (!is_null($contract_data['contract_begin_date'])) {

                           foreach ($contract_data['days'] as $key => $days) {
                              if ($days['contract_is_closed']) {
                                 unset($contract_data['days'][$key]);
                              }
                           }

                           if (!empty($contract_data['days'])) {

                              foreach ($contract_data['days'] as $day_data) {

                                 $entity->getFromDB($contract_data['entities_id']);
                                 $data["parent"] = $dbu->getTreeLeafValueName("glpi_entities", $entity->fields['entities_id']);

                                 $data["entities_id"] = $contract_data['entities_name'];

                                 $name_contract        = "<a href='" . $link_contract . "?id=" . $contract_data["contracts_id"] . "' target='_blank'>";
                                 $name_contract        .= $contract_data['name'] . "</a>";
                                 $data["contracts_id"] = $name_contract;

                                 $name_contract_day = "<a href='" . $link_contract_day . "?id=" . $day_data['contractdays_id'] . "' target='_blank'>";
                                 $name_contract_day .= $day_data['contractdayname'] . "</a>";
                                 $data["days"]      = $name_contract_day;
                                 $data["reste"]     = $day_data['reste'];
                                 $data["total"]     = $day_data['credit'];
                                 $data["end_date"]  = Html::convDate($day_data['end_date']);
                                 $datas[]           = $data;
                              }
                           }
                        }
                     }
                  }
               }

               $headers = [__('Team', 'manageentities'),
                           __('Entity'), __('Contract'),
                           __('Prestation', 'manageentities'),
                           __('Total remaining', 'manageentities'),
                           __('Total'),
                           __('End date')];

               $widget = new PluginMydashboardDatatable();

               $widget->setTabNames($headers);
               $widget->setTabDatas($datas);
               $widget->toggleWidgetRefresh();


            } else {
               $widget->setWidgetHtmlContent(__('Plugin is not activated', 'manageentities'));
            }

            $widget->setWidgetTitle(__("Remaining days number by opened client contracts", "manageentities"));

            return $widget;
            break;
         case $this->getType() . "2":
            $plugin = new Plugin();
            $widget = new PluginMydashboardHtml();

            if ($plugin->isActivated("manageentities")) {

               $query = "SELECT `glpi_entities`.`name` as client,`glpi_contacts`.`firstname`, `glpi_contacts`.`name`, `glpi_contacts`.`phone`, `glpi_contacts`.`mobile`
                           FROM `glpi_contacts`
                           LEFT JOIN `glpi_plugin_manageentities_contacts` ON (`glpi_plugin_manageentities_contacts`.`contacts_id` = `glpi_contacts`.`id`)
                           LEFT JOIN `glpi_entities` ON (`glpi_plugin_manageentities_contacts`.`entities_id` = `glpi_entities`.`id`)
                           WHERE `glpi_contacts`.`is_deleted` = 0
                           AND NOT `glpi_entities`.`name` = ''
                           AND ((NOT `glpi_contacts`.`phone` = ''
                           AND `glpi_contacts`.`phone` IS NOT NULL)
                           OR (NOT `glpi_contacts`.`mobile` = ''
                           AND `glpi_contacts`.`mobile` IS NOT NULL))
                           AND `glpi_entities`.`name` IS NOT NULL 
                           " . $dbu->getEntitiesRestrictRequest("AND", "glpi_contacts", "entities_id", '', true) . "
                           ORDER BY `glpi_entities`.`name`,`glpi_contacts`.`name`, `glpi_contacts`.`firstname` ASC";

               $widget  = PluginMydashboardHelper::getWidgetsFromDBQuery('table', $query);
               $headers = [_n('Client', 'Clients', 1, 'manageentities'),
                           __('First name'),
                           __('Name'),
                           __('Phone'),
                           __('Mobile phone')];
               $widget->setTabNames($headers);
               $widget->toggleWidgetRefresh();
            } else {
               $widget->setWidgetHtmlContent(__('Plugin is not activated', 'manageentities'));
            }
            $widget->setWidgetTitle(__("Client annuary", "manageentities"));

            return $widget;
            break;
         case $this->getType() . "3":
            $plugin = new Plugin();
            $widget = new PluginMydashboardHtml();
            if ($plugin->isActivated("manageentities")) {
               $link_contract_day = Toolbox::getItemTypeFormURL("PluginManageentitiesContractDay");
               $link_ticket       = Toolbox::getItemTypeFormURL("Ticket");

               $query = "SELECT `glpi_entities`.`name` as entity, 
                                    `glpi_tickets`.`date`,
                                    `glpi_tickets`.`id` as tickets_id, 
                                    `glpi_tickets`.`name` as title, 
                                    `glpi_plugin_manageentities_contractdays`.`name`, 
                                    `glpi_plugin_manageentities_contractdays`.`id`
                           FROM `glpi_plugin_manageentities_cridetails`
                           LEFT JOIN `glpi_tickets` ON (`glpi_tickets`.`id` = `glpi_plugin_manageentities_cridetails`.`tickets_id`)
                           LEFT JOIN `glpi_entities` ON (`glpi_tickets`.`entities_id` = `glpi_entities`.`id`)
                           LEFT JOIN `glpi_plugin_manageentities_contractdays` ON (`glpi_plugin_manageentities_contractdays`.`id` = `glpi_plugin_manageentities_cridetails`.`plugin_manageentities_contractdays_id`)
                           WHERE `glpi_tickets`.`is_deleted` = '0' AND `glpi_plugin_manageentities_cridetails`.`documents_id` = 0 AND `glpi_plugin_manageentities_cridetails`.`plugin_manageentities_contractdays_id` != 0
                                 AND `glpi_tickets`.`status` NOT IN (" . CommonITILObject::SOLVED . "," . CommonITILObject::CLOSED . ")
                           ORDER BY `glpi_tickets`.`date` DESC";

               $widget  = PluginMydashboardHelper::getWidgetsFromDBQuery('table', $query);
               $headers = [__('Opening date'),
                           _n('Client', 'Clients', 1, 'manageentities'),
                           __('Title'),
                           __('Prestation', 'manageentities')];
               $widget->setTabNames($headers);

               $result = $DB->query($query);
               $nb     = $DB->numrows($result);

               $datas = [];
               $i     = 0;
               if ($nb) {
                  while ($data = $DB->fetchAssoc($result)) {


                     $datas[$i]["date"] = Html::convDateTime($data['date']);

                     $datas[$i]["entity"] = $data['entity'];

                     $name_ticket        = "<a href='" . $link_ticket . "?id=" . $data['tickets_id'] . "' target='_blank'>";
                     $name_ticket        .= $data['title'] . "</a>";
                     $datas[$i]["title"] = $name_ticket;

                     $name_contract     = "<a href='" . $link_contract_day . "?id=" . $data['id'] . "' target='_blank'>";
                     $name_contract     .= $data['name'] . "</a>";
                     $datas[$i]["name"] = $name_contract;

                     $i++;
                  }

               }

               $widget->setTabDatas($datas);
               $widget->setOption("bSort", false);
               $widget->toggleWidgetRefresh();

            } else {
               $widget->setWidgetHtmlContent(__('Plugin is not activated', 'manageentities'));
            }

            $widget->setWidgetTitle(__("Tickets without CRI", "manageentities"));

            return $widget;
            break;
         case $this->getType() . "4":

            $year  = date("Y");
            $month = date('m', mktime(12, 0, 0, date("m"), 0, date("Y")));
            $date  = $year . "-" . $month . "-01";

            $link_contract_day = Toolbox::getItemTypeFormURL("PluginManageentitiesContractDay");
            $link_ticket       = Toolbox::getItemTypeFormURL("Ticket");

            $query = PluginManageentitiesContractDay::queryOldContractDaywithInterventions($date);

            $widget  = PluginMydashboardHelper::getWidgetsFromDBQuery('table', $query);
            $headers = [__('Creation date'),
                        _n('Client', 'Clients', 1, 'manageentities'),
                        __('Ticket'),
                        __('Prestation', 'manageentities'),
                        __('End date')];
            $widget->setTabNames($headers);

            $result = $DB->query($query);
            $nb     = $DB->numrows($result);

            $datas = [];
            $i     = 0;
            if ($nb) {
               while ($data = $DB->fetchAssoc($result)) {


                  $datas[$i]["date"] = Html::convDateTime($data['cridetails_date']);

                  $datas[$i]["entity"] = $data['entities_name'];

                  $name_ticket               = "<a href='" . $link_ticket . "?id=" . $data['tickets_id'] . "' target='_blank'>";
                  $name_ticket               .= $data['tickets_name'] . "</a>";
                  $datas[$i]["tickets_name"] = $name_ticket;

                  $name_contract     = "<a href='" . $link_contract_day . "?id=" . $data['id'] . "' target='_blank'>";
                  $name_contract     .= $data['name'] . "</a>";
                  $datas[$i]["name"] = $name_contract;

                  $datas[$i]["end_date"] = Html::convDateTime($data['end_date']);

                  $i++;
               }
            }

            $widget->setTabDatas($datas);
            //$widget->setOption("bSort", false);
            $widget->toggleWidgetRefresh();
            $widget->setWidgetTitle(__("Interventions with old contract", "manageentities"));

            return $widget;
            break;
         case $this->getType() . "5":

            $plugin = new Plugin();
            $widget = new PluginMydashboardHtml();
            if ($plugin->isActivated("manageentities")) {
               $link_contract     = Toolbox::getItemTypeFormURL("Contract");
               $link_contract_day = Toolbox::getItemTypeFormURL("PluginManageentitiesContractDay");
               $entity            = new Entity();
               $contracts         = self::queryFollowUpSimplified($_SESSION['glpiactiveentities'], []);
               $datas             = [];
               if (!empty($contracts)) {
                  foreach ($contracts as $key => $contract_data) {
                     if (is_integer($key)) {

                        if (!is_null($contract_data['contract_begin_date'])) {

                           foreach ($contract_data['days'] as $key => $days) {
                              if ($days['contract_is_closed']) {
                                 unset($contract_data['days'][$key]);
                              }
                              if ($days['reste'] > 0) {
                                 unset($contract_data['days'][$key]);
                              }
                           }

                           if (!empty($contract_data['days'])) {
                              $data = [];
                              foreach ($contract_data['days'] as $day_data) {

                                 $entity->getFromDB($contract_data['entities_id']);
                                 $data["parent"] = $dbu->getTreeLeafValueName("glpi_entities", $entity->fields['entities_id']);

                                 $data["entities_id"] = $contract_data['entities_name'];

                                 $name_contract        = "<a href='" . $link_contract . "?id=" . $contract_data["contracts_id"] . "' target='_blank'>";
                                 $name_contract        .= $contract_data['name'] . "</a>";
                                 $data["contracts_id"] = $name_contract;

                                 $name_contract_day = "<a href='" . $link_contract_day . "?id=" . $day_data['contractdays_id'] . "' target='_blank'>";
                                 $name_contract_day .= $day_data['contractdayname'] . "</a>";
                                 $data["days"]      = $name_contract_day;
                                 $data["reste"]     = $day_data['reste'];
                                 $data["total"]     = $day_data['credit'];
                                 $datas[]           = $data;
                              }
                           }
                        }
                     }
                  }
               }

               $headers = [__('Team', 'manageentities'),
                           __('Entity'), __('Contract'),
                           __('Prestation', 'manageentities'),
                           __('Total remaining', 'manageentities'),
                           __('Total')];

               $widget = new PluginMydashboardDatatable();

               $widget->setTabNames($headers);
               $widget->setTabDatas($datas);
               $widget->toggleWidgetRefresh();


            } else {
               $widget->setWidgetHtmlContent(__('Plugin is not activated', 'manageentities'));
            }

            $widget->setWidgetTitle(__("Opened contract prestations without remaining days", "manageentities"));

            return $widget;
            break;
      }
   }

   static function queryFollowUpSimplified($instID, $options = []) {
      global $DB;

      $dbu = new DbUtils();
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

      $beginDateAfter      = '';
      $beginDateBefore     = '';
      $endDateAfter        = '';
      $endDateBefore       = '';
      $beginDate           = 'NULL';
      $contractState       = '';
      $queryCompany        = '';
      $num                 = 0;
      $list                = [];
      $nbContratByEntities = 0;// Count the contracts for all entities

      // We configure the type of contract Hourly or Dayly
      $config = PluginManageentitiesConfig::getInstance();
      if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {// Hourly
         $configHourOrDay = "AND `glpi_plugin_manageentities_contracts`.`contract_type` IN 
                              ('" . PluginManageentitiesContract::CONTRACT_TYPE_NULL . "', 
                              '" . PluginManageentitiesContract::CONTRACT_TYPE_HOUR . "',
                               '" . PluginManageentitiesContract::CONTRACT_TYPE_INTERVENTION . "', 
                               '" . PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED . "')";
      } else {// Daily
         $configHourOrDay = "AND `glpi_plugin_manageentities_contractdays`.`contract_type` IN ('" . PluginManageentitiesContract::CONTRACT_TYPE_NULL . "',
                             '" . PluginManageentitiesContract::CONTRACT_TYPE_AT . "',
                             '" . PluginManageentitiesContract::CONTRACT_TYPE_FORFAIT . "')";
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
                                     `glpi_contracts`.`entities_id` AS entities_id,
                                     `glpi_plugin_manageentities_contracts`.`contract_type` AS contract_type,
                                     `glpi_plugin_manageentities_contracts`.`show_on_global_gantt` AS show_on_global_gantt";
            if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {// Hourly
               $queryContract .= ", `glpi_plugin_manageentities_contracts`.`contract_type` AS contract_type";
            }
            $queryContract .= " FROM `glpi_contracts`
                        
                        LEFT JOIN `glpi_plugin_manageentities_contracts`
                           ON (`glpi_contracts`.`id`
                           = `glpi_plugin_manageentities_contracts`.`contracts_id`)
                        
                        WHERE `glpi_contracts`.`entities_id`='" . $dataEntity['entities_id'] . "'
                           
                        AND `glpi_contracts`.`is_deleted` = 0 ";

            if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {// Hourly
               $queryContract .= $configHourOrDay;
            }

            $queryContract .= "GROUP BY `glpi_contracts`.`id`
                        ORDER BY `glpi_plugin_manageentities_contracts`.`date_signature` ASC,
                                 `glpi_contracts`.`name`";

            foreach ($DB->request($queryContract) as $dataContract) {
               $queryContractDay = "SELECT `glpi_plugin_manageentities_contractdays`.`name` AS name_contractdays,
                                           `glpi_plugin_manageentities_contractdays`.`id` AS contractdays_id,
                                           `glpi_plugin_manageentities_contractdays`.`report` AS report,
                                           `glpi_plugin_manageentities_contractdays`.`nbday` AS nbday,
                                           `glpi_plugin_manageentities_contractstates`.`is_closed` AS is_closed,
                                           `glpi_plugin_manageentities_contractdays`.`begin_date` AS begin_date,
                                           `glpi_plugin_manageentities_contractdays`.`end_date` AS end_date";
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
                          
                       WHERE `glpi_contracts`.`entities_id`='" . $dataEntity['entities_id'] . "'
                          $beginDateAfter $beginDateBefore $endDateAfter $endDateBefore $contractState";

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
                  $name_contract = "";

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
                  $list[$num]['contracts_id']         = $dataContract['contracts_id'];
                  $list[$num]['contract_begin_date']  = Html::convDate($dataContract['contract_begin_date']);
                  $list[$num]['show_on_global_gantt'] = $dataContract['show_on_global_gantt'];

                  for ($i = 0; $dataContractDay = $DB->fetchAssoc($requestContractDay); $i++) {
                     if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {// Daily
                        $dataContractDay["contract_type"] = $dataContract["contract_type"];
                     }

                     if ($dataContractDay["name_contractdays"] == NULL) {
                        $nameperiod = "(" . $dataContractDay["contractdays_id"] . ")";
                     } else {
                        $nameperiod = $dataContractDay["name_contractdays"];
                     }

                     // We get all cri details
                     $dataContractDay['values_begin_date'] = $beginDate;
                     $dataContractDay['contracts_id']      = $dataContract['contracts_id'];
                     $dataContractDay['entities_id']       = $dataContract['entities_id'];
                     $dataContractDay['contractdays_id']   = $dataContractDay["contractdays_id"];

                     $resultCriDetail = self::getCriDetailDataSimplified($dataContractDay,
                                                                         ["contract_type_id" => $dataContractDay["contract_type"]]);

                     if (Session::getCurrentInterface() == 'helpdesk'
                         && $dataContractDay["contract_type"] == PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED) {
                        $credit = PluginManageentitiesContract::getContractType($dataContractDay["contract_type"]);

                     } else {
                        $credit = $dataContractDay['nbday'] + $dataContractDay['report'];
                     }

                     $list[$num]['days'][$i]['contract_is_closed'] = $dataContractDay['is_closed'];
                     $list[$num]['days'][$i]['contractdayname']    = $nameperiod;
                     $list[$num]['days'][$i]['credit']             = $credit;
                     $list[$num]['days'][$i]['end_date']           = $dataContractDay['end_date'];
                     $list[$num]['days'][$i]['reste']              = $resultCriDetail['resultOther']['reste'];
                     $list[$num]['days'][$i]['depass']             = $resultCriDetail['resultOther']['depass'];
                     $list[$num]['days'][$i]['contractdays_id']    = $dataContractDay["contractdays_id"];
                     $list[$num]['days'][$i]['contracts_id']       = $dataContractDay['contracts_id'];

                  }
                  $num++;
               }
            }
         }
      }

      return $list;
   }

   static function getCriDetailDataSimplified($contractDayValues = [], $options = []) {
      global $DB;
      $params['condition'] = '1';

      foreach ($options as $key => $value) {
         $params[$key] = $value;
      }

      $tot_conso = 0;

      $config = PluginManageentitiesConfig::getInstance();

      $PDF = new PluginManageentitiesCriPDF('P', 'mm', 'A4');

      $tabOther = ['depass' => 0,
                   'reste'  => 0];

      $queryCriDetail = "SELECT `glpi_plugin_manageentities_cridetails`.`tickets_id`,
                                `glpi_plugin_manageentities_cridetails`.`id` AS cridetails_id,
                                `glpi_tickets`.`global_validation`
                        FROM `glpi_plugin_manageentities_cridetails`
                        LEFT JOIN `glpi_tickets` 
                     ON (`glpi_plugin_manageentities_cridetails`.`tickets_id` = `glpi_tickets`.`id`)
                        LEFT JOIN `glpi_tickettasks` 
                     ON (`glpi_tickettasks`.`tickets_id` = `glpi_tickets`.`id`)
                        WHERE `glpi_plugin_manageentities_cridetails`.`contracts_id` = '" . $contractDayValues["contracts_id"] . "' 
                 AND `glpi_plugin_manageentities_cridetails`.`entities_id` = '" . $contractDayValues["entities_id"] . "' 
                 AND `glpi_plugin_manageentities_cridetails`.`plugin_manageentities_contractdays_id` = '" . $contractDayValues["contractdays_id"] . "' 
                 AND `glpi_tickets`.`is_deleted` = 0
                 AND `glpi_tickettasks`.`actiontime` > 0";

      $queryCriDetail .= " GROUP BY `glpi_plugin_manageentities_cridetails`.`id`
                           ORDER BY `glpi_plugin_manageentities_cridetails`.`date` ASC";


      $resultCriDetail = $DB->query($queryCriDetail);
      $numberCriDetail = $DB->numrows($resultCriDetail);

      $restrict = ["`glpi_plugin_manageentities_contracts`.`entities_id`"  => $contractDayValues["entities_id"],
                   "`glpi_plugin_manageentities_contracts`.`contracts_id`" => $contractDayValues["contracts_id"]];

      $dbu             = new DbUtils();
      $pluginContracts = $dbu->getAllDataFromTable("glpi_plugin_manageentities_contracts", $restrict);
      $pluginContract  = reset($pluginContracts);

      if ($numberCriDetail != 0) {

         while ($dataCriDetail = $DB->fetchArray($resultCriDetail)) {

            $join = "";
            $and  = "";
            if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
               $join = " LEFT JOIN `glpi_plugin_manageentities_taskcategories`
                     ON (`glpi_plugin_manageentities_taskcategories`.`taskcategories_id` =
                     glpi_tickettasks.taskcategories_id)";
               $and  = " AND `glpi_plugin_manageentities_taskcategories`.`is_usedforcount` = 1";
            }

            $queryTask = "SELECT `actiontime`
                           FROM `glpi_tickettasks` $join
                           LEFT JOIN `glpi_plugin_manageentities_cridetails`
                              ON (`glpi_plugin_manageentities_cridetails`.`tickets_id` = `glpi_tickettasks`.`tickets_id`)
                           WHERE `glpi_tickettasks`.`tickets_id` = '" . $dataCriDetail['tickets_id'] . "'
                           AND `glpi_tickettasks`.`is_private` = 0 $and
                           AND `glpi_plugin_manageentities_cridetails`.`id` = '" . $dataCriDetail['cridetails_id'] . "'";

            $queryTask .= " ORDER BY `glpi_tickettasks`.`begin`";

            $resultTask = $DB->query($queryTask);
            $numberTask = $DB->numrows($resultTask);
            $conso      = 0;

            if ($numberTask != 0) {
               while ($dataTask = $DB->fetchArray($resultTask)) {

                  // Set conso per techs
                  $tmp = PluginManageentitiesCriDetail::setConso($dataTask['actiontime'],
                                                                 0,
                                                                 $config,
                                                                 $dataCriDetail,
                                                                 $pluginContract,
                                                                 1);

                  // Set global conso of contractday
                  $conso += $PDF->TotalTpsPassesArrondis(round($tmp, 2));
               }
            }

            $tot_conso += $conso;

         }
      }

      //Rest number / depass
      $tabOther['reste'] = ($contractDayValues["nbday"] + $contractDayValues["report"]) - $tot_conso;
      if ($tabOther['reste'] < 0) {
         $tabOther['depass'] = abs($tabOther['reste']);
         $tabOther['reste']  = 0;
      }

      return ['resultOther' => $tabOther];
   }


}
