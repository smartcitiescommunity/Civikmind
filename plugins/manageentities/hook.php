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

function plugin_manageentities_install() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/manageentities/inc/profile.class.php");
   include_once(GLPI_ROOT . "/plugins/manageentities/inc/preference.class.php");
   include_once(GLPI_ROOT . "/plugins/manageentities/inc/config.class.php");
   include_once(GLPI_ROOT . "/plugins/manageentities/inc/cridetail.class.php");

   $dbu       = new DbUtils();
   $update    = false;
   $update190 = false;
   if (!$DB->tableExists("glpi_plugin_manageentities_critypes")) {

      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/empty-3.2.2.sql");


      $query = "INSERT INTO `glpi_plugin_manageentities_critypes` ( `id`, `name`) VALUES ('1', '" . __('Urgent intervention', 'manageentities') . "');";
      $DB->query($query);
      $query = "INSERT INTO `glpi_plugin_manageentities_critypes` ( `id`, `name`) VALUES ('2', '" . __('Scheduled intervention', 'manageentities') . "');";
      $DB->query($query);
      $query = "INSERT INTO `glpi_plugin_manageentities_critypes` ( `id`, `name`) VALUES ('3', '" . __('Study and advice', 'manageentities') . "');";
      $DB->query($query);

   } else if ($DB->tableExists("glpi_plugin_manageentity_profiles") && !$DB->tableExists("glpi_plugin_manageentity_preference")) {

      $update = true;
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.4.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.5.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.0.sql");
      $update190 = true;
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.1.sql");

      $query = "INSERT INTO `glpi_plugin_manageentities_critypes` ( `id`, `name`) VALUES ('1', '" . __('Urgent intervention', 'manageentities') . "');";
      $DB->query($query);
      $query = "INSERT INTO `glpi_plugin_manageentities_critypes` ( `id`, `name`) VALUES ('2', '" . __('Scheduled intervention', 'manageentities') . "');";
      $DB->query($query);
      $query = "INSERT INTO `glpi_plugin_manageentities_critypes` ( `id`, `name`) VALUES ('3', '" . __('Study and advice', 'manageentities') . "');";
      $DB->query($query);

   } else if ($DB->tableExists("glpi_plugin_manageentity_profiles") && $DB->fieldExists("glpi_plugin_manageentity_profiles", "interface")) {

      $update = true;
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.5.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.0.sql");
      $update190 = true;
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.1.sql");

      $query = "INSERT INTO `glpi_plugin_manageentities_critypes` ( `id`, `name`) VALUES ('1', '" . __('Urgent intervention', 'manageentities') . "');";
      $DB->query($query);
      $query = "INSERT INTO `glpi_plugin_manageentities_critypes` ( `id`, `name`) VALUES ('2', '" . __('Scheduled intervention', 'manageentities') . "');";
      $DB->query($query);
      $query = "INSERT INTO `glpi_plugin_manageentities_critypes` ( `id`, `name`) VALUES ('3', '" . __('Study and advice', 'manageentities') . "');";
      $DB->query($query);

   } else if ($DB->tableExists("glpi_plugin_manageentity_config") && !$DB->fieldExists("glpi_plugin_manageentity_config", "hourbyday")) {

      $update = true;
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.5.1.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.0.sql");
      $update190 = true;
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.1.sql");

   } else if ($DB->tableExists("glpi_plugin_manageentity_profiles") && !$DB->tableExists("glpi_plugin_manageentities_profiles")) {

      $update = true;
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.6.0.sql");
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.0.sql");
      $update190 = true;
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.1.sql");

   } else if ($DB->tableExists("glpi_plugin_manageentities_profiles") && !$DB->tableExists("glpi_plugin_manageentities_contractstates")) {

      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.0.sql");
      $update190 = true;
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.1.sql");

   } else if ($DB->tableExists("glpi_plugin_manageentities_configs") && !$DB->fieldExists("glpi_plugin_manageentities_contracts", "contract_added")) {

      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.1.sql");
   }

   if ($DB->tableExists("glpi_plugin_manageentities_cridetails") && !$DB->fieldExists("glpi_plugin_manageentities_cridetails", "plugin_manageentities_contractdays_id")) {
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-1.9.2.sql");
   }

   if ($DB->tableExists("glpi_plugin_manageentities_configs") && $DB->fieldExists("glpi_plugin_manageentities_configs", "linktocontract")) {
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-2.0.0.sql");
   }

   if ($DB->tableExists("glpi_plugin_manageentities_configs") && !$DB->fieldExists("glpi_plugin_manageentities_configs", "company_address")) {
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-2.0.1.sql");
   }

   if (!$DB->tableExists("glpi_plugin_manageentities_interventionskateholders")) {
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-2.0.2.sql");
   }

   if (!$DB->fieldExists("glpi_plugin_manageentities_criprices", "plugin_manageentities_contractdays_id")) {
      include(GLPI_ROOT . "/plugins/manageentities/install/update_202_203.php");
      update202to203();
   }

   if (!$DB->fieldExists("glpi_plugin_manageentities_configs", "contract_states") && !$DB->tableExists('glpi_plugin_manageentities_business_contacts')) {
      include(GLPI_ROOT . "/plugins/manageentities/install/update_210_211.php");
      update210to211();
   }

   //version 2.1.3
   if (!$DB->fieldExists("glpi_plugin_manageentities_configs", "comment")) {
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-2.1.3.sql");
   }

   //version 2.1.4
   if (!$DB->fieldExists("glpi_plugin_manageentities_contracts", "moving_management")) {
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-2.1.4.sql");
   }

   //version 3.2.1
   if (!$DB->fieldExists("glpi_plugin_manageentities_configs", "non_accomplished_tasks")) {
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-3.2.1.sql");
   }

   //version 3.2.2
   if (!$DB->fieldExists("glpi_plugin_manageentities_configs", "disable_date_header")) {
      $DB->runFile(GLPI_ROOT . "/plugins/manageentities/install/sql/update-3.2.2.sql");
   }

   if ($update) {
      $index = [
         'FK_contracts'   => ['glpi_plugin_manageentities_contracts'],
         'FK_contracts_2' => ['glpi_plugin_manageentities_contracts'],
         'FK_entities'    => ['glpi_plugin_manageentities_contracts', 'glpi_plugin_manageentities_contacts'],
         'FK_entity'      => ['glpi_plugin_manageentities_contracts', 'glpi_plugin_manageentities_contacts'],
         'FK_contacts'    => ['glpi_plugin_manageentities_contacts'],
         'FK_contacts_2'  => ['glpi_plugin_manageentities_contacts']];


      foreach ($index as $oldname => $newnames) {
         foreach ($newnames as $table) {
            if ($dbu->isIndex($table, $oldname)) {
               $query = "ALTER TABLE `$table` DROP INDEX `$oldname`;";
               $DB->query($query);
            }
         }
      }

      $query_  = "SELECT *
            FROM `glpi_plugin_manageentities_profiles` ";
      $result_ = $DB->query($query_);
      if ($DB->numrows($result_) > 0) {

         while ($data = $DB->fetchArray($result_)) {
            $query = "UPDATE `glpi_plugin_manageentities_profiles`
                  SET `profiles_id` = '" . $data["id"] . "'
                  WHERE `id` = '" . $data["id"] . "';";
            $DB->query($query);

         }
      }

      $query = "ALTER TABLE `glpi_plugin_manageentities_profiles`
               DROP `name` ;";
      $DB->query($query);
   }

   if ($update190) {
      $config = PluginManageentitiesConfig::getInstance();
      if ($config->fields["backup"] == 1) {

         $criDetail = new PluginManageentitiesCriDetail();

         $query = "SELECT `glpi_documents`.`id` AS doc_id,
                          `glpi_documents`.`tickets_id` AS doc_tickets_id,
                          `glpi_plugin_manageentities_cridetails`.`id` AS cri_id,
                          `glpi_plugin_manageentities_cridetails`.`tickets_id` AS cri_tickets_id
              FROM `glpi_documents`
              LEFT JOIN `glpi_plugin_manageentities_cridetails`
                  ON (`glpi_documents`.`id` = `glpi_plugin_manageentities_cridetails`.`documents_id`)
              WHERE `glpi_documents`.`documentcategories_id` = '" .
                  $config->fields["documentcategories_id"] . "' ";

         $result = $DB->query($query);
         $number = $DB->numrows($result);

         if ($number != "0") {
            while ($data = $DB->fetchArray($result)) {
               if ($data['cri_tickets_id'] == '0') {
                  $criDetail->update(['id'         => $data['cri_id'],
                                      'tickets_id' => $data['doc_tickets_id']]);
               }
            }
         }
      }
   }

   if (!$DB->tableExists('glpi_plugin_manageentities_entitylogos')) {
      include(GLPI_ROOT . "/plugins/manageentities/install/update_211_212.php");
      update211to212();
   }

   //version 2.1.5
   if (!$DB->fieldExists("glpi_plugin_manageentities_contractdays", "contract_type")) {
      include(GLPI_ROOT . "/plugins/manageentities/install/update_214_215.php");
      update214to215();
   }

   $rep_files_manageentities = GLPI_PLUGIN_DOC_DIR . "/manageentities";
   if (!is_dir($rep_files_manageentities))
      mkdir($rep_files_manageentities);


   PluginManageentitiesProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   PluginManageentitiesProfile::initProfile();
   $DB->query("DROP TABLE IF EXISTS `glpi_plugin_manageentities_profiles`;");

   $pref_ID = PluginManageentitiesPreference::checkIfPreferenceExists(Session::getLoginUserID());
   if ($pref_ID) {
      $pref_value = PluginManageentitiesPreference::checkPreferenceValue(Session::getLoginUserID());
      if ($pref_value == 1) {
         $_SESSION["glpi_plugin_manageentities_loaded"] = 0;
      }
   }

   return true;
}

function plugin_manageentities_uninstall() {
   global $DB;

   $tables = ["glpi_plugin_manageentities_contracts",
              "glpi_plugin_manageentities_contacts",
              "glpi_plugin_manageentities_preferences",
              "glpi_plugin_manageentities_configs",
              "glpi_plugin_manageentities_critypes",
              "glpi_plugin_manageentities_criprices",
              "glpi_plugin_manageentities_contractdays",
              "glpi_plugin_manageentities_critechnicians",
              "glpi_plugin_manageentities_cridetails",
              "glpi_plugin_manageentities_contractstates",
              "glpi_plugin_manageentities_taskcategories",
              "glpi_plugin_manageentities_businesscontacts",
              "glpi_plugin_manageentities_companies",
              "glpi_plugin_manageentities_entitylogos",
              "glpi_plugin_manageentities_interventionskateholders"];

   foreach ($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   //old versions   
   $tables = ["glpi_plugin_manageentity_contracts",
              "glpi_plugin_manageentity_documents",
              "glpi_plugin_manageentity_contacts",
              "glpi_plugin_manageentity_profiles",
              "glpi_plugin_manageentity_preference",
              "glpi_plugin_manageentity_config",
              "glpi_dropdown_plugin_manageentity_critype",
              "glpi_plugin_manageentity_criprice",
              "glpi_plugin_manageentity_dayforcontract",
              "glpi_plugin_manageentity_critechnicians",
              "glpi_plugin_manageentity_cridetails"];

   foreach ($tables as $table)
      $DB->query("DROP TABLE IF EXISTS `$table`;");

   $rep_files_manageentities = GLPI_PLUGIN_DOC_DIR . "/manageentities";

   Toolbox::deleteDir($rep_files_manageentities);

   include_once(GLPI_ROOT . "/plugins/manageentities/inc/profile.class.php");

   PluginManageentitiesProfile::removeRightsFromSession();
   PluginManageentitiesProfile::removeRightsFromDB();

   return true;
}

function plugin_manageentities_addLeftJoin($type, $ref_table, $new_table, $linkfield, &$already_link_tables) {

   switch ($new_table) {
      case "glpi_plugin_manageentities_criprices" :
         $out = " LEFT JOIN `$new_table` ON (`$ref_table`.`id` = `$new_table`.`plugin_manageentities_critypes_id` AND `$new_table`.`entities_id` IN ('" . implode("','", $_SESSION["glpiactiveentities"]) . "')) ";
         return $out;
         break;
   }

   return "";
}

function plugin_manageentities_forceGroupBy($type) {

   return true;
   switch ($type) {
      case 'PluginManageentitiesCriType' :
         return true;
         break;

   }
   return false;
}

function plugin_manageentities_giveItem($type, $ID, $data, $num) {
   global $DB;

   $searchopt =& Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];
   switch ($type) {
      case 'PluginManageentitiesCriType':
         switch ($table . '.' . $field) {
            case "glpi_plugin_manageentities_criprices.price" :
               //               $manageentitiesCritypes = new PluginManageentitiesCriType();
               //               $manageentitiesCritypes->getFromDBByCrit(["id = $table.plugin_manageentities_critypes_id
               //                                                         AND entities_id IN IN ('" . implode("','", $_SESSION["glpiactiveentities"]) . "')"]);

               $query = "SELECT * 
                       FROM $table 
                       WHERE  id = $table.plugin_manageentities_critypes_id 
                       AND entities_id IN ('" . implode("','", $_SESSION["glpiactiveentities"]) . "')";

               $result = $DB->query($query);
               if ($DB->numrows($result)) {
                  while ($datas = $DB->fetchAssoc($result)) {
                     $data["ITEM_4"] = $datas['price'];
                  }
               } else {
                  $data["ITEM_4"] = 0;
               }

               $out = Html::formatnumber($data["ITEM_$num"], 2);
               return $out;
               break;

         }
         break;
   }
   return "";
}

// Hook done on purge item case
function plugin_pre_item_purge_manageentities($item) {

   $PluginManageentitiesConfig    = new PluginManageentitiesConfig();
   $PluginManageentitiesCriDetail = new PluginManageentitiesCriDetail();
   $PluginManageentitiesEntity    = new PluginManageentitiesEntity();

   switch (get_class($item)) {
      case 'Entity' :
         $temp = new PluginManageentitiesContract();
         $temp->deleteByCriteria(['entities_id' => $item->getField('id')]);

         $temp = new PluginManageentitiesContact();
         $temp->deleteByCriteria(['entities_id' => $item->getField('id')]);

         $temp = new PluginManageentitiesCriPrice();
         $temp->deleteByCriteria(['entities_id' => $item->getField('id')]);

         $temp = new PluginManageentitiesContractDay();
         $temp->deleteByCriteria(['entities_id' => $item->getField('id')]);

         $temp = new PluginManageentitiesCriDetail();
         $temp->deleteByCriteria(['entities_id' => $item->getField('id')]);
         break;
      case 'Ticket' :
         $temp = new PluginManageentitiesCriTechnician();
         $temp->deleteByCriteria(['tickets_id' => $item->getField('id')]);

         $temp = new PluginManageentitiesCriDetail();
         $temp->deleteByCriteria(['tickets_id' => $item->getField('id')]);
         break;
      case 'Contract' :
         $temp = new PluginManageentitiesContract();
         $temp->deleteByCriteria(['contracts_id' => $item->getField('id')]);

         $temp = new PluginManageentitiesContractDay();
         $temp->deleteByCriteria(['contracts_id' => $item->getField('id')]);

         $temp = new PluginManageentitiesCriDetail();
         $temp->deleteByCriteria(['contracts_id' => $item->getField('id')]);
         break;
      case 'Contact' :
         $temp = new PluginManageentitiesContact();
         $temp->deleteByCriteria(['contacts_id' => $item->getField('id')]);
         break;
      case 'TaskCategory' :
         $temp = new PluginManageentitiesTaskCategory();
         $temp->deleteByCriteria(['taskcategories_id' => $item->getField('id')]);
         break;
   }
}

// Hook done on transfered item case
function plugin_item_transfer_manageentities($parm) {
   $dbu = new DbUtils();

   switch ($parm['type']) {
      case 'Contract' :
         $contract = new Contract();
         $contract->getFromDB($parm['id']);
         $pluginContract     = new PluginManageentitiesContract();
         $old_entity         = '';
         $restrict           = ["`glpi_plugin_manageentities_contracts`.`contracts_id`" => $parm['id']];
         $allPluginContracts = $dbu->getAllDataFromTable('glpi_plugin_manageentities_contracts', $restrict);
         if (!empty($allPluginContracts)) {
            foreach ($allPluginContracts as $onePluginContract) {
               $old_entity = $onePluginContract['entities_id'];
               $pluginContract->update(['id'           => $onePluginContract['id'],
                                        'contracts_id' => $contract->fields['id'],
                                        'entities_id'  => $contract->fields['entities_id']]);

            }
         }

         $contractDay           = new PluginManageentitiesContractDay();
         $condition             = ["`glpi_plugin_manageentities_contractdays`.`contracts_id`" => $parm['id']];
         $allPluginContractDays = $dbu->getAllDataFromTable('glpi_plugin_manageentities_contractdays', $condition);
         if (!empty($allPluginContractDays)) {
            foreach ($allPluginContractDays as $onePluginContractDays) {
               $criPrice  = new PluginManageentitiesCriPrice();
               $cond      = ["`glpi_plugin_manageentities_criprices`.`entities_id`"                       => $old_entity,
                             "`glpi_plugin_manageentities_criprices`.`plugin_manageentities_critypes_id`" =>
                                $onePluginContractDays['plugin_manageentities_critypes_id']];
               $allPrices = $dbu->getAllDataFromTable('glpi_plugin_manageentities_criprices', $cond);
               if (!empty($allPrices)) {
                  foreach ($allPrices as $onePrice) {
                     //créer un nouveau si n'existe pas dans la nouvelle entité sinon prendre l'ID de l'existant
                     $newPrice = $criPrice->getFromDBbyType($onePluginContractDays['plugin_manageentities_critypes_id'],
                                                            $contract->fields['entities_id']);
                     if (!$newPrice) {
                        $criPrice->add(['entities_id'                       => $contract->fields['entities_id'],
                                        'plugin_manageentities_critypes_id' => $onePrice['plugin_manageentities_critypes_id'],
                                        'price'                             => $onePrice['price']]);
                     }
                  }
               }
               $contractDay->update(['id'           => $onePluginContractDays['id'],
                                     'contracts_id' => $contract->fields['id'],
                                     'entities_id'  => $contract->fields['entities_id']]);

            }
         }

         $criDetail           = new PluginManageentitiesCriDetail();
         $restr               = ["`glpi_plugin_manageentities_cridetails`.`contracts_id`" => $parm['id']];
         $allPluginCriDetails = $dbu->getAllDataFromTable('glpi_plugin_manageentities_cridetails', $restr);
         if (!empty($allPluginCriDetails)) {
            foreach ($allPluginCriDetails as $onePluginCriDetail) {
               $criPrice  = new PluginManageentitiesCriPrice();
               $cond      = ["`glpi_plugin_manageentities_criprices`.`entities_id`"                       => $old_entity,
                             "`glpi_plugin_manageentities_criprices`.`plugin_manageentities_critypes_id`" =>
                                $onePluginCriDetail['plugin_manageentities_critypes_id']];
               $allPrices = $dbu->getAllDataFromTable('glpi_plugin_manageentities_criprices', $cond);
               if (!empty($allPrices)) {
                  foreach ($allPrices as $onePrice) {
                     //créer un nouveau si n'existe pas dans la nouvelle entité sinon prendre l'ID de l'existant
                     $newPrice = $criPrice->getFromDBbyType($onePluginCriDetail['plugin_manageentities_critypes_id'],
                                                            $contract->fields['entities_id']);
                     if (!$newPrice) {
                        $criPrice->add(['entities_id'                       => $contract->fields['entities_id'],
                                        'plugin_manageentities_critypes_id' => $onePrice['plugin_manageentities_critypes_id'],
                                        'price'                             => $onePrice['price']]);
                     }
                  }
               }

               $document = new Document();
               $document->getFromDB($onePluginCriDetail['documents_id']);
               $document->update(['id'          => $onePluginCriDetail['documents_id'],
                                  'entities_id' => $contract->fields['entities_id']]);

               $ticket = new Ticket();
               $ticket->getFromDB($onePluginCriDetail['tickets_id']);
               $ticket->update(['id'          => $onePluginCriDetail['tickets_id'],
                                'entities_id' => $contract->fields['entities_id']]);

               $criDetail->update(['id'           => $onePluginCriDetail['id'],
                                   'contracts_id' => $contract->fields['id'],
                                   'entities_id'  => $contract->fields['entities_id']]);
            }
         }
         break;
   }
}

// Define dropdown relations
function plugin_manageentities_getDatabaseRelations() {

   $plugin = new Plugin();

   if ($plugin->isActivated("manageentities"))
      return ["glpi_plugin_manageentities_critypes"       => ["glpi_plugin_manageentities_criprices"    => "plugin_manageentities_critypes_id",
                                                              "glpi_plugin_manageentities_cridetails"   => "plugin_manageentities_critypes_id",
                                                              "glpi_plugin_manageentities_contractdays" => "plugin_manageentities_critypes_id"],
              "glpi_contracts"                            => ["glpi_plugin_manageentities_contracts"    => "contracts_id",
                                                              "glpi_plugin_manageentities_contractdays" => "contracts_id",
                                                              "glpi_plugin_manageentities_cridetails"   => "contracts_id"],
              "glpi_contacts"                             => ["glpi_plugin_manageentities_contacts" => "contacts_id"],
              "glpi_users"                                => ["glpi_plugin_manageentities_preferences"    => "users_id",
                                                              "glpi_plugin_manageentities_critechnicians" => "users_id"],
              "glpi_documents"                            => ["glpi_plugin_manageentities_cridetails" => "documents_id",
                                                              "glpi_plugin_manageentities_companies"  => "logo_id"],
              "glpi_documentcategories"                   => ["glpi_plugin_manageentities_configs" => "documentcategories_id"],
              "glpi_tickets"                              => ["glpi_plugin_manageentities_critechnicians" => "tickets_id",
                                                              "glpi_plugin_manageentities_cridetails"     => "tickets_id"],
              "glpi_entities"                             => ["glpi_plugin_manageentities_contracts"    => "entities_id",
                                                              "glpi_plugin_manageentities_contacts"     => "entities_id",
                                                              "glpi_plugin_manageentities_criprices"    => "entities_id",
                                                              "glpi_plugin_manageentities_contractdays" => "entities_id",
                                                              "glpi_plugin_manageentities_cridetails"   => "entities_id",
                                                              "glpi_plugin_manageentities_entitylogos"  => "entities_id"],
              "glpi_plugin_manageentities_contractstates" => ["glpi_plugin_manageentities_contractdays" => "plugin_manageentities_contractstates_id"],
              "glpi_taskcategories"                       => ["glpi_plugin_manageentities_taskcategories" => "taskcategories_id"]];
   else
      return [];
}

// Define Dropdown tables to be manage in GLPI :
function plugin_manageentities_getDropdown() {
   $plugin = new Plugin();

   if ($plugin->isActivated("manageentities"))
      return ['PluginManageentitiesCriType'       => __('Intervention type', 'manageentities'),
              'PluginManageentitiesContractState' => __('State of contract', 'manageentities')];
   else
      return [];
}

// Do special actions for dynamic report
function plugin_manageentities_dynamicReport($parm) {

   if ($parm["item_type"] == 'PluginManageentitiesFollowUp'
       && isset($parm["display_type"])) {

      PluginManageentitiesFollowUp::showFollowUp($parm);

      return true;
   } else if ($parm["item_type"] == 'PluginManageentitiesMonthly'
              && isset($parm["display_type"])) {

      PluginManageentitiesMonthly::showMonthly($parm);

      return true;
   }

   // Return false if no specific display is done, then use standard display
   return false;
}

////// SEARCH FUNCTIONS ///////
// Define search option for types of the plugins
function plugin_manageentities_getAddSearchOptions($itemtype) {
   $sopt = [];

   if ($itemtype == "Ticket") {
      if (Session::haveRight("plugin_manageentities", READ)) {
         $sopt[4455]['table']         = 'glpi_contracts';
         $sopt[4455]['field']         = 'name';
         $sopt[4455]['linkfield']     = 'contracts_id';
         $sopt[4455]['name']          = _n('Contract', 'Contracts', 1);
         $sopt[4455]['datatype']      = 'itemlink';
         $sopt[4455]['itemlink_type'] = 'Contract';
         $sopt[4455]['forcegroupby']  = true;
         $sopt[4455]['massiveaction'] = false;
         $sopt[4455]['joinparams']    = ['beforejoin'
                                         => ['table'      => 'glpi_plugin_manageentities_cridetails',
                                             'joinparams' => ['jointype' => 'child']]];
      }
   }
   return $sopt;
}

function plugin_manageentities_postinit() {
   global $PLUGIN_HOOKS;

   $plugin = 'manageentities';
   foreach (['add_css', 'add_javascript'] as $type) {
      if (isset($PLUGIN_HOOKS[$type][$plugin])) {
         foreach ($PLUGIN_HOOKS[$type][$plugin] as $data) {
            if (!empty($PLUGIN_HOOKS[$type])) {
               foreach ($PLUGIN_HOOKS[$type] as $key => $plugins_data) {
                  if (is_array($plugins_data) && $key != $plugin) {
                     foreach ($plugins_data as $key2 => $values) {
                        if ($values == $data) {
                           unset($PLUGIN_HOOKS[$type][$key][$key2]);
                        }
                     }
                  }
               }
            }
         }
      }
   }

   $PLUGIN_HOOKS['item_purge']['manageentities']["Document"]
      = ['PluginManageentitiesEntityLogo', 'cleanForItem'];
}

function plugin_manageentities_displayConfigItem($type, $ID, $data, $num) {

   $searchopt =& Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   switch ($table . '.' . $field) {
      case "glpi_plugin_manageentities_contractdays.end_date" :
         if ($data[$num][0]['name'] <= date('Y-m-d') && !empty($data[$num][0]['name']))
            return " class=\"deleted\" ";
         break;
   }
   return "";
}
