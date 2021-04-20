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

class PluginOrderBill extends CommonDropdown
{

   public static $rightname  = 'plugin_order_bill';

   public $dohistory         = true;

   public $first_level_menu  = "plugins";

   public $second_level_menu = "order";


   public static function getTypeName($nb = 0) {
      return __("Bill", "order");
   }


   public function post_getEmpty() {
      $this->fields['value'] = 0;
   }


   public function prepareInputForAdd($input) {
      if (!isset ($input["number"])
          || $input["number"] == '') {
         Session::addMessageAfterRedirect(__("A bill number is mandatory", "order"), false, ERROR);
         return [];
      }
      return $input;
   }


   public function getAdditionalFields() {
      return [
         [
            'name'  => 'suppliers_id',
            'label' => __("Supplier"),
            'type'  => 'dropdownValue'
         ],
         [
            'name'  => 'value',
            'label' => __("Value"),
            'type'  => 'text'
         ],
         [
            'name'  => 'number',
            'label' => _x("phone", "Number")." <span class='red'>*</span>",
            'type'  => 'text',
            'mandatory' => true
         ],
         [
            'name'  => 'billdate',
            'label' => __("Date"),
            'type'  => 'date'
         ],
         [
            'name'  => 'plugin_order_billtypes_id',
            'label' => __("Type"),
            'type'  => 'dropdownValue'
         ],
         [
            'name'  => 'plugin_order_billstates_id',
            'label' => __("Status"),
            'type'  => 'dropdownValue'
         ],
         [
            'name'  => 'plugin_order_orders_id',
            'label' => __("Order", "order"),
            'type'  => 'dropdownValue'
         ],
         [
            'name'  => 'users_id_validation',
            'label' => __("Approver"),
            'type'  => 'UserDropdown'
         ],
         [
            'name'  => 'validationdate',
            'label' => __("Approval date"),
            'type'  => 'date'
         ]
      ];
   }


   public function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Note', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   public function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'            => 'common',
         'name'          => __('Bill', 'order'),
      ];

      $tab[] = [
         'id'            => 1,
         'table'         => self::getTable(),
         'field'         => 'number',
         'name'          => _x('phone', 'Number'),
         'datatype'      => 'itemlink',
      ];

      $tab[] = [
         'id'            => 2,
         'table'         => self::getTable(),
         'field'         => 'billdate',
         'name'          => __('Date'),
         'datatype'      => 'datetime',
      ];

      $tab[] = [
         'id'            => 3,
         'table'         => self::getTable(),
         'field'         => 'validationdate',
         'name'          => __('Approval date'),
         'datatype'      => 'datetime',
      ];

      $tab[] = [
         'id'            => 4,
         'table'         => User::getTable(),
         'field'         => 'name',
         'linkfield'     => 'users_id_validation',
         'name'          => __('Approver'),
      ];

      $tab[] = [
         'id'            => 5,
         'table'         => PluginOrderBillType::getTable(),
         'field'         => 'name',
         'name'          => __('Type'),
      ];

      $tab[] = [
         'id'            => 6,
         'table'         => PluginOrderBillState::getTable(),
         'field'         => 'name',
         'name'          => __('Status'),
      ];

      $tab[] = [
         'id'            => 7,
         'table'         => Supplier::getTable(),
         'field'         => 'name',
         'name'          => __('Supplier'),
         'datatype'      => 'itemlink',
         'itemlink_type' => 'Supplier',
      ];

      $tab[] = [
         'id'            => 8,
         'table'         => PluginOrderOrder::getTable(),
         'field'         => 'name',
         'name'          => __('Order', 'order'),
         'datatype'      => 'itemlink',
         'itemlink_type' => 'PluginOrderOrder',
      ];

      $tab[] = [
         'id'            => 9,
         'table'         => self::getTable(),
         'field'         => 'name',
         'name'          => __('Name'),
         'datatype'      => 'itemlink',
      ];

      $tab[] = [
         'id'            => 16,
         'table'         => self::getTable(),
         'field'         => 'comment',
         'name'          => __('Description'),
         'datatype'      => 'text',
      ];

      $tab[] = [
         'id'            => 30,
         'table'         => self::getTable(),
         'field'         => 'id',
         'name'          => __('ID'),
      ];

      $tab[] = [
         'id'            => 80,
         'table'         => 'glpi_entities',
         'field'         => 'completename',
         'name'          => __('Entity'),
      ];

      $tab[] = [
         'id'            => 86,
         'table'         => self::getTable(),
         'field'         => 'is_recursive',
         'name'          => __('Child entities'),
         'datatype'      => 'bool',
         'massiveaction' => false,
      ];

      return $tab;
   }


   public static function showItems(PluginOrderBill $bill) {
      global $DB;

      echo "<div class='spaced'><table class='tab_cadre_fixehov'>";
      echo "<tr><th>";
      Html::printPagerForm();
      echo "</th><th colspan='5'>";
      echo _n("Item", "Items", 2);
      echo "</th></tr>";

      $bills_id = $bill->getID();
      $table = PluginOrderOrder_Item::getTable();

      $query  = "SELECT * FROM `$table`";
      $query .= " WHERE `plugin_order_bills_id` = '$bills_id'";
      $query .= getEntitiesRestrictRequest(" AND", $table, "entities_id", $bill->getEntityID(), true);
      $query .= "GROUP BY `itemtype`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if (!$number) {
         echo "</th><td>";
         echo _n("Item", "Items", 2);
         echo "</td></tr>";
      } else {
         echo "<tr>";
         echo "<th>".__("Type")."</th>";
         echo "<th>".__("Entity")."</th>";
         echo "<th>".__("Reference")."</th>";
         echo "<th>".__("Status")."</th>";
         echo "</tr>";

         $old_itemtype = '';
         $num          = 0;

         while ($data = $DB->fetchArray($result)) {
            if (!class_exists($data['itemtype'])) {
               continue;
            }
            $item = new $data['itemtype']();
            if ($data['itemtype']::canView()) {
               echo "<tr class='tab_bg_1'>";

               $ID = "";
               if ($_SESSION["glpiis_ids_visible"]
                   || empty($data["name"])) {
                    $ID = " (".$data["id"].")";
               }
               $name = NOT_AVAILABLE;
               if ($item->getFromDB($data["id"])) {
                  $name = $item->getLink();
               }

               echo "<td class='center top'>".$item->getTypeName()."</td>";
               echo "<td class='center top'>";
               echo Dropdown::getDropdownName('glpi_entities', $item->getEntityID())."</td>";

               if ($data['itemtype'] == 'PluginOrderReferenceFree') {
                  $reference = new PluginOrderReferenceFree();
                  $reference->getFromDB($data["plugin_order_references_id"]);
               } else {
                  $reference = new PluginOrderReference();
                  $reference->getFromDB($data["plugin_order_references_id"]);
               }

               echo "<td class='center'>";
               if (PluginOrderReference::canView()) {
                  echo $reference->getLink();
               } else {
                  echo $reference->getName(true);
               }
               echo "</td>";
               echo "<td class='center'>";
               Dropdown::getDropdownName("glpi_plugin_order_deliverystates",
                                         $data["plugin_order_deliverystates_id"]);
               echo "</td>";
               echo "</tr>";
            }
         }
      }
      echo "</table></div>";
   }


   public static function showOrdersItems(PluginOrderBill $bill) {
      global $DB, $CFG_GLPI;

      $reference = new PluginOrderReference();
      $order     = new PluginOrderOrder();
      $order->getFromDB($bill->fields['plugin_order_orders_id']);

      //Can write orders, and order is not already paid
      $canedit = $order->can($order->getID(), UPDATE)
                 && !$order->isPaid()
                 && !$order->isCanceled();

      $bill = new self();
      $query_ref = $bill->queryRef($order->getID(), 'glpi_plugin_order_references');
      $result_ref = $DB->query($query_ref);

      while ($data_ref = $DB->fetchArray($result_ref)) {
         self::showOrder($data_ref, $result_ref, $canedit, $order, $reference, 'glpi_plugin_order_references');
      }

      $query_reffree = $bill->queryRef($order->getID(), 'glpi_plugin_order_referencefrees');
      $result_reffree = $DB->query($query_reffree);

      while ($data_reffree = $DB->fetchArray($result_reffree)) {
         self::showOrder($data_reffree, $result_reffree, $canedit, $order, $reference, 'glpi_plugin_order_referencefrees');
      }
      echo "<br>";
   }

   public static function showOrder($data_ref, $result_ref, $canedit, $order, $reference, $table) {
      global $DB, $CFG_GLPI;
      echo "<div class='center'><table class='tab_cadre_fixe'>";
      if (!$DB->numrows($result_ref)) {
         echo "<tr><th>" . __("No item to take delivery of", "order") . "</th></tr></table></div>";

      } else {
         $order_item = new PluginOrderOrder_Item();

         $rand     = mt_rand();
         $itemtype = $data_ref["itemtype"];
         $item     = new $itemtype();
         echo "<tr><th><ul><li>";
         echo "<a href=\"javascript:showHideDiv('generation$rand','generation_img$rand', '"
              . $CFG_GLPI['root_doc'] . "/pics/plus.png','" . $CFG_GLPI['root_doc'] . "/pics/moins.png');\">";
         echo "<img alt='' name='generation_img$rand' src=\"" . $CFG_GLPI['root_doc'] . "/pics/plus.png\">";
         echo "</a>";
         echo "</li></ul></th>";
         echo "<th>" . __("Type") . "</th>";
         echo "<th>" . __("Manufacturer") . "</th>";
         echo "<th>" . __("Product reference", "order") . "</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_1 center'>";
         echo "<td></td>";
         echo "<td align='center'>" . $item->getTypeName() . "</td>";

         //Entity
         echo "<td align='center'>";
         echo Dropdown::getDropdownName('glpi_entities', $order->getEntityID());
         echo "</td>";
         if ($table == 'glpi_plugin_order_referencefrees') {
            echo "<td>" . $data_ref['name'] . "</td>";
         } else {
            echo "<td>" . $reference->getReceptionReferenceLink($data_ref) . "</td>";
         }
         echo "</tr></table>";

         echo "<div class='center' id='generation$rand' style='display:none'>";
         echo "<form method='post' name='bills_form$rand' id='bills_form$rand'
                     action='" . Toolbox::getItemTypeFormURL('PluginOrderBill') . "'>";

         echo Html::hidden('plugin_order_orders_id', ['value' => $order->getID()]);

         echo "<table class='tab_cadre_fixe'>";

         echo "<th></th>";
         echo "<th>" . __("Reference") . "</th>";
         echo "<th>" . __("Type") . "</th>";
         echo "<th>" . __("Model") . "</th>";
         echo "<th>" . __("Bill", "order") . "</th>";
         echo "<th>" . __("Bill status", "order") . "</th>";
         echo "</tr>";

         $results = $order_item->queryBills($order->getID(), $data_ref['id'], $table);
         while ($data = $DB->fetchArray($results)) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td width='10'>";
               $sel = "";
               if (isset($_GET["select"]) && $_GET["select"] == "all") {
                  $sel = "checked";
               }
               echo "<input type='checkbox' name='item[" . $data["IDD"] . "]' value='1' $sel>";
               echo Html::hidden('plugin_order_orders_id', ['value' => $order->getID()]);
               echo "</td>";
            }

            //Reference
            echo "<td align='center'>";
            echo $reference->getReceptionReferenceLink($data);
            echo "</td>";

            //Type
            echo "<td align='center'>";
            if (file_exists($CFG_GLPI['root_doc']."/inc/".strtolower($data["itemtype"])."type.class.php")) {
               echo Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Type"),
                                              $data["types_id"]);
            }
            echo "</td>";

            //Model
            echo "<td align='center'>";
            if (file_exists($CFG_GLPI['root_doc']."/inc/".strtolower($data["itemtype"])."model.class.php")) {
               echo Dropdown::getDropdownName(getTableForItemType($data["itemtype"]."Model"),
                                              $data["models_id"]);
            }
            $bill = new PluginOrderBill();
            echo "<td align='center'>";
            if ($data["plugin_order_bills_id"] > 0) {
               if ($bill->can($data['plugin_order_bills_id'], READ)) {
                  echo "<a href='" . $bill->getLinkURL() . "'>" . $bill->getName(true) . "</a>";
               } else {
                  echo $bill->getName();
               }
            }
            echo "</td>";
            echo "<td align='center'>";
            echo Dropdown::getDropdownName(getTableForItemType('PluginOrderBillState'),
                                           $data['plugin_order_billstates_id']);
            echo "</td>";
            echo "</tr>";
         }
      }
      echo "</table>";

      if ($canedit) {
         echo "<div class='center'>";
         echo "<table width='950px' class='tab_glpi'>";
         echo "<tr><td><img src=\"" . $CFG_GLPI["root_doc"]
              . "/pics/arrow-left.png\" alt=''></td><td class='center'>";
         echo "<a onclick= \"if ( markCheckboxes('bills_form$rand') ) "
              . "return false;\" href='#'>" . __("Check all") . "</a></td>";

         echo "<td>/</td><td class='center'>";
         echo "<a onclick= \"if ( unMarkCheckboxes('bills_form$rand') ) "
              . "return false;\" href='#'>" . __("Uncheck all") . "</a>";
         echo "</td><td align='left' width='80%'>";
         echo Html::hidden('plugin_order_orders_id', ['value' => $order->getID()]);
         $order_item->dropdownBillItemsActions($order->getID());
         echo "</td>";
         echo "</table>";
         echo "</div>";
      }
      Html::closeForm();
      echo "</div>";
   }

   public function queryRef($ID, $table) {
      if ($table == 'glpi_plugin_order_references') {
         $condition = "AND `glpi_plugin_order_orders_items`.`itemtype` NOT LIKE 'PluginOrderReferenceFree' ";
      } else {
         $condition = "AND `glpi_plugin_order_orders_items`.`itemtype` LIKE 'PluginOrderReferenceFree' ";
      }

      $query_ref = "SELECT `glpi_plugin_order_orders_items`.`id` AS IDD, " .
                   "`glpi_plugin_order_orders_items`.`plugin_order_references_id` AS id, " .
                   "ref.`name`, " .
                   "ref.`itemtype`, " .
                   "ref.`manufacturers_id` " .
                   "FROM `glpi_plugin_order_orders_items`, `" . $table . "` ref " .
                   "WHERE `glpi_plugin_order_orders_items`.`plugin_order_orders_id` = '" . $ID . "' " .
                   "AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = ref.`id` " .
                   $condition .
                   "GROUP BY `glpi_plugin_order_orders_items`.`plugin_order_references_id` " .
                   "ORDER BY ref.`name`";
      return $query_ref;
   }


   public static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();

      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");
         $query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_order_bills` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
                    `number` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
                    `billdate` timestamp NULL DEFAULT NULL,
                    `validationdate` timestamp NULL DEFAULT NULL,
                    `comment` text COLLATE utf8_unicode_ci,
                    `plugin_order_billstates_id` int(11) NOT NULL DEFAULT '0',
                    `value` decimal(20,6) NOT NULL DEFAULT '0.000000',
                    `plugin_order_billtypes_id` int(11) NOT NULL DEFAULT '0',
                    `suppliers_id` int(11) NOT NULL DEFAULT '0',
                    `plugin_order_orders_id` int(11) NOT NULL DEFAULT '0',
                    `users_id_validation` int(11) NOT NULL DEFAULT '0',
                    `entities_id` int(11) NOT NULL DEFAULT '0',
                    `is_recursive` int(11) NOT NULL DEFAULT '0',
                    `notepad` text COLLATE utf8_unicode_ci,
                    PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
         $DB->query($query) or die ($DB->error());
      } else {
         if ($DB->fieldExists("glpi_plugin_order_orders_suppliers", "num_bill")) {
            //Migrate bills
            $bill  = new PluginOrderBill();
            $query = "SELECT * FROM `glpi_plugin_order_orders_suppliers`";
            foreach (getAllDataFromTable('glpi_plugin_order_orders_suppliers') as $data) {
               if (!is_null($data['num_bill'])
                  && $data['num_bill'] != ''
                     && !countElementsInTable('glpi_plugin_order_bills',
                                              ['number' => $data['num_bill']])) {
                  //create new bill and link it to the order
                  $tmp['name']                   = $tmp['number'] = $data['num_bill'];

                  //Get supplier from the order
                  $tmp['suppliers_id']           = $data['suppliers_id'];

                  //Bill has the same entities_id and is_recrusive
                  $tmp['entities_id']            = $data['entities_id'];
                  $tmp['is_recursive']           = $data['is_recursive'];
                  //Link bill to order
                  $tmp['plugin_order_orders_id'] = $data['plugin_order_orders_id'];
                  //Create bill
                  $bills_id                      = $bill->add($tmp);

                  //All order items are now linked to this bill
                  $query = "UPDATE `glpi_plugin_order_orders_items`
                            SET `plugin_order_bills_id` = '$bills_id'
                            WHERE `plugin_order_orders_id` = '".$data['plugin_order_orders_id']."'";
                  $DB->query($query);
               }
            }
         }
         $migration->changeField($table, "value", "value", "decimal(20,6) NOT NULL DEFAULT '0.000000'");
         $migration->migrationOneTable($table);
      }
      $migration->dropField("glpi_plugin_order_orders_suppliers", "num_bill");
      $migration->migrationOneTable("glpi_plugin_order_orders_suppliers");
   }


   public static function uninstall() {
      global $DB;

      $table = self::getTable();
      foreach (["displaypreferences", "documents_items", "savedsearches", "logs"] as $t) {
         $query = "DELETE FROM `glpi_$t` WHERE `itemtype` = '".__CLASS__."'";
         $DB->query($query);
      }
      $DB->query("DROP TABLE IF EXISTS`".$table."`") or die ($DB->error());
   }


   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'PluginOrderOrder':
               return self::getTypeName();
               break;
            case __CLASS__:
               $ong[1] = __("Orders", "order");
               $ong[2] = _n("Associated item", "Associated items", 2);
               return $ong;
         }
      }
      return '';
   }


   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'PluginOrderOrder') {
         $order_item = new PluginOrderOrder_Item();
         $order_item->showBillsItems($item);

      } else if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 1 :
               self::showOrdersItems($item);
               break;
            case 2 :
               self::showItems($item);
               break;
         }
      }
      return true;
   }


}
