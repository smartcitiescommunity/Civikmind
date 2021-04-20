<?php
/*
 LICENSE

 This file is part of the order plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Order. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   order
 @author    the order plugin team
 @copyright Copyright (c) 2010-2015 Order plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/order
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginOrderConfig extends CommonDBTM {

   static $rightname = 'config';

   const CONFIG_NEVER   = 0;
   const CONFIG_YES     = 1;
   const CONFIG_ASK     = 2;


   public function __construct() {
      global $DB;
      if ($DB->tableExists(self::getTable())) {
         $this->getFromDB(1);
      }
   }


   static function canView() {
      return Session::haveRight('config', READ);
   }


   static function canCreate() {
      return Session::haveRight('config', UPDATE);
   }


   public static function getConfig($update = false) {
      static $config = null;

      if (is_null($config)) {
         $config = new self();
      }
      if ($update) {
         $config->getFromDB(1);
      }

      return $config;
   }


   public static function getTypeName($nb = 0) {
      return __("Orders management", "order");
   }


   public static function getMenuContent() {
      global $CFG_GLPI;

      $menu  = parent::getMenuContent();
      $menu['page']   = PluginOrderMenu::getSearchURL(false);
      $menu['links']['add']    = null;
      $menu['links']['search'] = null;
      $menu['links']['config'] = self::getFormURL(false);

      return $menu;
   }


   public function showForm() {
      $this->getFromDB(1);

      echo "<div class='center'>";
      echo "<form name='form' method='post' action='".$this->getFormURL()."'>";

      echo Html::hidden('id', ['value' => 1]);

      echo "<table class='tab_cadre_fixe'>";

      echo "<tr><th colspan='2'>".__("Plugin configuration", "order")."</th></tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Default VAT", "order")."</td>";
      echo "<td>";
      PluginOrderOrderTax::Dropdown([
         'name'                => "default_taxes",
         'value'               => $this->fields["default_taxes"],
         'display_emptychoice' => true,
         'emptylabel'          => __("No VAT", "order")
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Use validation process", "order")."</td>";
      echo "<td>";
      Dropdown::showYesNo("use_validation", $this->fields["use_validation"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Order generation in ODT", "order")."</td><td>";
      Dropdown::showYesNo("generate_order_pdf", $this->fields["generate_order_pdf"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Activate suppliers quality satisfaction", "order")."</td>";
      echo "<td>";
      Dropdown::showYesNo("use_supplier_satisfaction", $this->fields["use_supplier_satisfaction"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Display order's suppliers informations", "order")."</td><td>";
      Dropdown::showYesNo("use_supplier_informations", $this->fields["use_supplier_informations"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Color to be displayed when order due date is overtaken", "order")."</td>";
      echo "<td>";
      echo "<input type='color' name='shoudbedelivered_color'
               value='".$this->fields['shoudbedelivered_color']."'>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Copy order documents when a new item is created", "order")."</td>";
      echo "<td>";
      Dropdown::showYesNo("copy_documents", $this->fields["copy_documents"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Default heading when adding a document to an order", "order")."</td>";
      echo "<td>";
      DocumentCategory::Dropdown(['value' => $this->fields["documentcategories_id"]]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Author group", "order").' ('.__("Default values").")</td>";
      echo "<td>";
      Group::Dropdown([
         'value' => $this->fields["groups_id_author"],
         'name'  => 'groups_id_author',
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Recipient group", "order").' ('.__("Default values").")</td>";
      echo "<td>";
      Group::Dropdown([
         'value' => $this->fields["groups_id_recipient"],
         'name'  => 'groups_id_recipient',
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Recipient").' ('.__("Default values").")</td>";
      echo "<td>";
      User::Dropdown([
         'name'   => 'users_id_recipient',
         'value'  => $this->fields["users_id_recipient"],
         'right'  => 'all',
         'entity' => 0,
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Hide inactive budgets", 'order')."</td>";
      echo "<td>";
      Dropdown::showYesNo("hide_inactive_budgets", $this->fields["hide_inactive_budgets"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Transmit budget change to linked assets", 'order')."</td>";
      echo "<td>";
      Dropdown::showYesNo("transmit_budget_change", $this->fields["transmit_budget_change"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("Display account section on order form", 'order') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("order_accountsection_display", $this->fields["order_accountsection_display"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("Set account section as mandatory on order form", 'order') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("order_accountsection_mandatory", $this->fields["order_accountsection_mandatory"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Use free references", 'order')."</td>";
      echo "<td>";
      Dropdown::showYesNo("use_free_reference", $this->fields["use_free_reference"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("Rename documents added in order", 'order') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("rename_documents", $this->fields["rename_documents"]);
      echo "</td>";
      echo "</tr>";

      // Automatic actions
      echo "<tr class='tab_bg_1' align='center'>";
      echo "<th colspan='2'>".__("Automatic actions when delivery", "order")."</th>";
      echo "</tr>";

      // ASSETS
      echo "<tr class='tab_bg_1' align='center'>";
      echo "<th colspan='2'>".__('Item')."</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("Display analytic nature on item form", 'order') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("order_analyticnature_display", $this->fields["order_analyticnature_display"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>" . __("Set analytic nature as mandatory on item form", 'order') . "</td>";
      echo "<td>";
      Dropdown::showYesNo("order_analyticnature_mandatory", $this->fields["order_analyticnature_mandatory"]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1 center'>";
      echo "<td>".__("Enable automatic generation", "order")."</td>";
      echo "<td>";
      Dropdown::showFromArray('generate_assets',
                              [self::CONFIG_NEVER => __('No'),
                               self::CONFIG_YES   => __('Yes'),
                               self::CONFIG_ASK   => __('Asked', 'order')],
                              ['value' => $this->canGenerateAsset()]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Default state", "order")."</td>";
      echo "<td>";
      State::Dropdown([
         'name'   => 'default_asset_states_id',
         'value'  => $this->fields["default_asset_states_id"],
         'entity' => $_SESSION["glpiactiveentities"],
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Add order location to item", "order")."</td>";
      echo "<td>";
      Dropdown::showYesNo("add_location", $this->canAddLocation());
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Add billing details to item", "order")."</td><td>";
      Dropdown::showYesNo("add_bill_details", $this->canAddBillDetails());
      echo "</td>";
      echo "</tr>";

      if ($this->canGenerateAsset()) {
         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>".__("Default name", "order")."</td>";
         echo "<td>";
         Html::autocompletionTextField($this, "generated_name");
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>".__("Default serial number", "order")."</td>";
         echo "<td>";
         Html::autocompletionTextField($this, "generated_serial");
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>".__("Default inventory number", "order")."</td>";
         echo "<td>";
         Html::autocompletionTextField($this, "generated_otherserial");
         echo "</td>";
         echo "</tr>";

         // TICKETS
         echo "<tr class='tab_bg_1' align='center'>";
         echo "<th colspan='2'>".__("Ticket")."</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_1' align='center'>";
         echo "<td>".TicketTemplate::getTypeName(1)."</td>";
         echo "<td>";
         Dropdown::show('TicketTemplate', [
            'name'  => 'tickettemplates_id_delivery',
            'value' => $this->fields['tickettemplates_id_delivery'],
         ]);
         echo "</td>";
         echo "</tr>";
      }

      /* Workflow */
      echo "<tr class='tab_bg_1' align='center'>";
      echo "<th colspan='2'>".__("Order lifecycle", "order")."</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("State before validation", "order")."</td>";
      echo "<td>";
      PluginOrderOrderState::Dropdown([
         'name'   => 'order_status_draft',
         'value'  => $this->fields["order_status_draft"],
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Waiting for validation state", "order")."</td>";
      echo "<td>";
      PluginOrderOrderState::Dropdown([
         'name'   => 'order_status_waiting_approval',
         'value'  => $this->fields["order_status_waiting_approval"],
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Validated order state", "order")."</td>";
      echo "<td>";
      PluginOrderOrderState::Dropdown([
         'name'   => 'order_status_approved',
         'value'  => $this->fields["order_status_approved"],
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Order being delivered state", "order")."</td>";
      echo "<td>";
      PluginOrderOrderState::Dropdown([
         'name'   => 'order_status_partially_delivred',
         'value'  => $this->fields["order_status_partially_delivred"],
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Order delivered state", "order")."</td>";
      echo "<td>";
      PluginOrderOrderState::Dropdown([
         'name'   => 'order_status_completly_delivered',
         'value'  => $this->fields["order_status_completly_delivered"],
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Order paied state", "order")."</td>";
      echo "<td>";
      PluginOrderOrderState::Dropdown([
         'name'   => 'order_status_paid',
         'value'  => $this->fields["order_status_paid"],
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td>".__("Canceled order state", "order")."</td>";
      echo "<td>";
      PluginOrderOrderState::Dropdown([
         'name'   => 'order_status_canceled',
         'value'  => $this->fields["order_status_canceled"],
      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1' align='center'>";
      echo "<td colspan='2' align='center'>";
      echo "<input type='submit' name='update' value=\""._sx("button", "Post")."\" class='submit' >";
      echo"</td>";
      echo "</tr>";

      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }


   //----------------- Getters and setters -------------------//

   public function useValidation() {
      return $this->fields['use_validation'];
   }


   public function getDraftState() {
      return $this->fields['order_status_draft'];

   }


   public function getWaitingForApprovalState() {
      return $this->fields['order_status_waiting_approval'];

   }


   public function getApprovedState() {
      return $this->fields['order_status_approved'];

   }


   public function getPartiallyDeliveredState() {
      return $this->fields['order_status_partially_delivred'];

   }


   public function getDeliveredState() {
      return $this->fields['order_status_completly_delivered'];

   }


   public function getCanceledState() {
      return $this->fields['order_status_canceled'];

   }


   public function getPaidState() {
      return $this->fields['order_status_paid'];

   }

   public function isAccountSectionDisplayed() {

      return $this->fields['order_accountsection_display'];
   }

   public function isAccountSectionMandatory() {

      return $this->fields['order_accountsection_mandatory'];
   }

   public function isAnalyticNatureDisplayed() {

      return $this->fields['order_analyticnature_display'];
   }

   public function isAnalyticNatureMandatory() {

      return $this->fields['order_analyticnature_mandatory'];
   }

   public function getDefaultTaxes() {
      return $this->fields['default_taxes'];
   }


   public function canGenerateAsset() {
      return $this->fields['generate_assets'];
   }


   public function canGenerateTicket() {
      return ($this->fields['tickettemplates_id_delivery'] > 0);
   }


   public function canAddLocation() {
      return $this->fields['add_location'];
   }


   public function canAddBillDetails() {
      return $this->fields['add_bill_details'];
   }


   public function getGeneratedAssetName() {
      return $this->fields['generated_name'];
   }


   public function getGeneratedAssetSerial() {
      return $this->fields['generated_serial'];
   }


   public function getGeneratedAssetState() {
      return $this->fields['default_asset_states_id'];
   }


   public function getGeneratedAssetOtherserial() {
      return $this->fields['generated_otherserial'];
   }


   public function canUseSupplierSatisfaction() {
      return $this->fields['use_supplier_satisfaction'];
   }


   public function canUseSupplierInformations() {
      return $this->fields['use_supplier_informations'];
   }


   public function canGenerateOrderPDF() {
      return $this->fields['generate_order_pdf'];
   }


   public function canCopyDocuments() {
      return $this->fields['copy_documents'];
   }


   public function getShouldBeDevileredColor() {
      return $this->fields['shoudbedelivered_color'];
   }


   public function getDefaultDocumentCategory() {
      return $this->fields['documentcategories_id'];
   }


   public function getDefaultAuthorGroup() {
      return $this->fields['groups_id_author'];
   }


   public function getDefaultRecipientGroup() {
      return $this->fields['groups_id_recipient'];
   }


   public function getDefaultRecipient() {
      return $this->fields['users_id_recipient'];
   }


   public function canHideInactiveBudgets() {
      return $this->fields['hide_inactive_budgets'];
   }

   public function useFreeReference() {
      return $this->fields['use_free_reference'];
   }

   public function canRenameDocuments() {
      return $this->fields['rename_documents'];
   }


   //----------------- Install & uninstall -------------------//
   public static function install(Migration $migration) {
      global $DB;

      $table  = self::getTable();
      $config = new self();

      //This class is available since version 1.3.0
      if (!$DB->tableExists($table)
          && !$DB->tableExists("glpi_plugin_order_config")) {
            $migration->displayMessage("Installing $table");

            //Install
            $query = "CREATE TABLE `$table` (
                        `id` int(11) NOT NULL auto_increment,
                        `use_validation` tinyint(1) NOT NULL default '0',
                        `use_supplier_satisfaction` tinyint(1) NOT NULL default '0',
                        `use_supplier_informations` tinyint(1) NOT NULL default '0',
                        `use_supplier_infos` tinyint(1) NOT NULL default '1',
                        `generate_order_pdf` tinyint(1) NOT NULL default '0',
                        `copy_documents` tinyint(1) NOT NULL default '0',
                        `default_taxes` int(11) NOT NULL default '0',
                        `generate_assets` int(11) NOT NULL default '0',
                        `generated_name` varchar(255) collate utf8_unicode_ci default NULL,
                        `generated_serial` varchar(255) collate utf8_unicode_ci default NULL,
                        `generated_otherserial` varchar(255) collate utf8_unicode_ci default NULL,
                        `default_asset_states_id` int(11) NOT NULL default '0',
                        `tickettemplates_id_delivery` int(11) NOT NULL default '0',
                        `order_status_draft` int(11) NOT NULL default '0',
                        `order_status_waiting_approval` int(11) NOT NULL default '0',
                        `order_status_approved` int(11) NOT NULL default '0',
                        `order_status_partially_delivred` int(11) NOT NULL default '0',
                        `order_status_completly_delivered` int(11) NOT NULL default '0',
                        `order_status_canceled` int(11) NOT NULL default '0',
                        `order_status_paid` int(11) NOT NULL default '0',
                        `order_analyticnature_display` int(11) NOT NULL default '0',
                        `order_analyticnature_mandatory` int(11) NOT NULL default '0',
                        `order_accountsection_display` int(11) NOT NULL default '0',
                        `order_accountsection_mandatory` int(11) NOT NULL default '0',
                        `shoudbedelivered_color` char(20) collate utf8_unicode_ci default '#ff5555',
                        `documentcategories_id` int(11) NOT NULL default '0',
                        `groups_id_author` int(11) NOT NULL default '0',
                        `groups_id_recipient` int(11) NOT NULL default '0',
                        `users_id_recipient` int(11) NOT NULL default '0',
                        `add_location` tinyint(1) NOT NULL default '0',
                        `add_bill_details` tinyint(1) NOT NULL default '0',
                        `hide_inactive_budgets` tinyint(1) NOT NULL default '0',
                        `rename_documents` tinyint(1) NOT NULL default '0',
                        `transmit_budget_change` tinyint(1) NOT NULL default '0',
                        `use_free_reference` tinyint(1) NOT NULL default '0',
                        PRIMARY KEY  (`id`)
                     ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
               $DB->query($query) or die ($DB->error());

               $tobefilled = "TOBEFILLED";
               $config->add([
                  'id'                          => 1,
                  'use_validation'              => 0,
                  'default_taxes'               => 0,
                  'generate_assets'             => 0,
                  'generated_name'              => $tobefilled,
                  'generated_serial'            => $tobefilled,
                  'generated_otherserial'       => $tobefilled,
                  'default_asset_states_id'     => 0,
                  'generated_title'             => $tobefilled,
                  'generated_content'           => $tobefilled,
                  'default_ticketcategories_id' => 0,
                  'shoudbedelivered_color'      => '#ff5555',
               ]);
      } else {
         //Upgrade
         $migration->displayMessage("Upgrading $table");

         //1.2.0
         $migration->renameTable("glpi_plugin_order_config", $table);

         if (!countElementsInTable("glpi_plugin_order_configs")) {
            $query = "INSERT INTO `glpi_plugin_order_configs`(`id`,`use_validation`,`default_taxes`) VALUES (1,0,0);";
            $DB->query($query) or die($DB->error());
         }

         $migration->changeField($table, "ID", "id", "int(11) NOT NULL auto_increment");

         //1.3.0
         $migration->addField($table, "generate_assets", "tinyint(1) NOT NULL default '0'");
         $migration->addField($table, "generated_name", "varchar(255) collate utf8_unicode_ci default NULL");
         $migration->addField($table, "generated_serial", "varchar(255) collate utf8_unicode_ci default NULL");
         $migration->addField($table, "generated_otherserial", "varchar(255) collate utf8_unicode_ci default NULL");
         $migration->addField($table, "default_asset_entities_id", "int(11) NOT NULL default '0'");
         $migration->addField($table, "default_asset_states_id", "int(11) NOT NULL default '0'");
         $migration->addField($table, "generated_title", "varchar(255) collate utf8_unicode_ci default NULL");
         $migration->addField($table, "generated_content", "text collate utf8_unicode_ci");
         $migration->addField($table, "default_ticketcategories_id", "int(11) NOT NULL default '0'");
         $migration->addField($table, "use_supplier_satisfaction", "tinyint(1) NOT NULL default '0'");
         $migration->addField($table, "generate_order_pdf", "tinyint(1) NOT NULL default '0'");
         $migration->addField($table, "use_supplier_informations", "tinyint(1) NOT NULL default '1'");
         $migration->addField($table, "shoudbedelivered_color", "char(20) collate utf8_unicode_ci default '#ff5555'");
         $migration->addField($table, "copy_documents", "tinyint(1) NOT NULL DEFAULT '0'");
         $migration->addField($table, "documentcategories_id", "integer");
         $migration->addField($table, "groups_id_author", "integer");
         $migration->addField($table, "groups_id_recipient", "integer");
         $migration->addField($table, "users_id_recipient", "integer");

         $migration->changeField($table, "default_ticketcategories_id",
                                 "default_itilcategories_id", "integer");

         //1.9.0
         $migration->addField($table, "add_location", "TINYINT(1) NOT NULL DEFAULT '0'");
         $migration->addField($table, "add_bill_details", "TINYINT(1) NOT NULL DEFAULT '0'");

         $config = new self();
         $config->getFromDB(1);
         $templateID = false;

         $migration->addField($table, "tickettemplates_id_delivery", 'integer');
         $migration->migrationOneTable($table);

         $migration->dropField($table, "generated_title");
         $migration->dropField($table, "generated_content");
         $migration->dropField($table, "default_itilcategories_id");

         $migration->addField($table, "hide_inactive_budgets", "bool");
         $migration->addField($table, "rename_documents", "bool");

         //0.85+1.2
         $migration->addField($table, "transmit_budget_change", "bool");

         $migration->migrationOneTable($table);
         if ($templateID) {
            $config->update(['id' => 1, 'tickettemplates_id_delivery' => $templateID]);
         }

         //version 2.0.1
         $migration->addField($table, "use_free_reference", "bool");

      }

      $migration->displayMessage("Add default order state workflow");
      $new_states = ['order_status_draft'               => 1,
                     'order_status_waiting_approval'    => 2,
                     'order_status_approved'            => 3,
                     'order_status_partially_delivred'  => 4,
                     'order_status_completly_delivered' => 5,
                     'order_status_canceled'            => 6,
                     'order_status_paid'                => 7];

      foreach ($new_states as $field => $value) {
         $migration->addField($table, $field, "int(11) NOT NULL default '0'", ['update' => $value]);
      }

      if (!$DB->fieldExists($table, 'order_analyticnature_display')) {
         $migration->addField($table, 'order_analyticnature_display', 'integer');
      }
      if (!$DB->fieldExists($table, 'order_accountsection_display')) {
         $migration->addField($table, 'order_accountsection_display', 'integer');
      }
      if (!$DB->fieldExists($table, 'order_analyticnature_mandatory')) {
         $migration->addField($table, 'order_analyticnature_mandatory', 'integer');
      }
      if (!$DB->fieldExists($table, 'order_accountsection_mandatory')) {
         $migration->addField($table, 'order_accountsection_mandatory', 'integer');
      }

      $migration->migrationOneTable($table);
   }


   public static function uninstall() {
      global $DB;

      //Old table
      $DB->query("DROP TABLE IF EXISTS `glpi_plugin_order_config`");

      //New table
      $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`");
   }


   function rawSearchOptions() {
      $tab = [];

      $tab[] = [
         'id'            => '2',
         'table'         => $this->getTable(),
         'field'         => 'generated_name',
         'name'          => __('Default name', 'order'),
         'autocomplete'  => true,
      ];

      $tab[] = [
         'id'            => '3',
         'table'         => $this->getTable(),
         'field'         => 'generated_serial',
         'name'          => __('Default serial number', 'order'),
         'autocomplete'  => true,
      ];

      $tab[] = [
         'id'            => '4',
         'table'         => $this->getTable(),
         'field'         => 'generated_otherserial',
         'name'          => __('Default inventory number', 'order'),
         'autocomplete'  => true,
      ];

      return $tab;
   }
}
