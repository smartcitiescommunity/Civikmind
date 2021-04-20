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

class PluginOrderReception extends CommonDBChild {

   public static $rightname          = 'plugin_order_order';

   public $dohistory                 = true;

   public static $itemtype           = 'PluginOrderOrder';

   public static $items_id           = 'plugin_order_orders_id';

   public static $checkParentRights  = self::DONT_CHECK_ITEM_RIGHTS;


   public static function getTable($classname = null) {
      return "glpi_plugin_order_orders_items";
   }


   public static function getTypeName($nb = 0) {
      return __("Delivery", "order");
   }


   public function canUpdateItem() {
      return Session::haveRight('plugin_order_order', PluginOrderOrder::RIGHT_DELIVERY);
   }


   public function canViewItem() {
      return Session::haveRight('plugin_order_order', PluginOrderOrder::RIGHT_DELIVERY)
         && Session::haveRight('plugin_order_order', READ);
   }


   public function getOrdersID() {
      return $this->fields["plugin_order_orders_id"];
   }


   public function getFromDBByOrder($plugin_order_orders_id) {
      global $DB;

      $query = "SELECT * FROM `".$this->table."`
               WHERE `plugin_order_orders_id` = '".$plugin_order_orders_id."' ";
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


   public function checkThisItemStatus($detailID, $states_id) {
      global $DB;

      $query = "SELECT `states_id`
                FROM `glpi_plugin_order_orders_items`
                WHERE `id` = '$detailID' ";
      $result = $DB->query($query);
      if ($DB->result($result, 0, "states_id") == $states_id) {
         return true;
      } else {
         return false;
      }
   }


   public function checkItemStatus($plugin_order_orders_id, $plugin_order_references_id, $states_id) {
      return countElementsInTable(
         "glpi_plugin_order_orders_items",
         [
            'plugin_order_orders_id' => $plugin_order_orders_id,
            'plugin_order_references_id' => $plugin_order_references_id,
            'states_id' => $states_id,
         ]
      );
   }


   public function deleteDelivery($detailID) {
      global $DB;

      $detail = new PluginOrderOrder_Item();
      $detail->getFromDB($detailID);

      if ($detail->fields["itemtype"] == 'SoftwareLicense') {

         $result = $PluginOrderOrder_Item->queryRef($_POST["plugin_order_orders_id"],
                                                    $detail->fields["plugin_order_references_id"],
                                                    $detail->fields["price_taxfree"],
                                                    $detail->fields["discount"],
                                                    PluginOrderOrder::ORDER_DEVICE_DELIVRED);
         $nb = $DB->numrows($result);

         if ($nb) {
            for ($i = 0; $i < $nb; $i++) {
               $detailID = $DB->result($result, $i, 'id');
               $detail->update([
                  "id"                             => $detailID,
                  "delivery_date"                  => 'NULL',
                  "states_id"                      => PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED,
                  "delivery_number"                => "",
                  "plugin_order_deliverystates_id" => 0,
                  "delivery_comment"               => "",
               ]);
            }
         }

      } else {
         $detail->update([
            "id"                             => $detailID,
            "date"                           => 0,
            "states_id"                      => PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED,
            "delivery_number"                => "",
            "plugin_order_deliverystates_id" => 0,
            "delivery_comment"               => "",
         ]);
      }
   }


   public function defineTabs($options = []) {
      $ong = [];
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }


   public function showForm ($ID, $options = []) {
      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      $order_order = new PluginOrderOrder();
      $order_order->getFromDB($this->getOrdersID());

      $order_reference = new PluginOrderReference();
      $order_reference->getFromDB($this->fields["plugin_order_references_id"]);

      $canedit = $order_order->can($this->getOrdersID(), UPDATE)
                 && !$order_order->canUpdateOrder()
                 && !$order_order->isCanceled();

      echo Html::hidden('plugin_order_orders_id', ['value' => $this->getOrdersID()]);

      echo "<tr class='tab_bg_1'>";

      echo "<td>".__("Reference").": </td>";
      echo "<td>";
      if ($this->fields['itemtype'] == 'PluginOrderReferenceFree') {
         echo $order_reference->fields["name"];
      } else {
         $data         = [];
         $data["id"]   = $this->fields["plugin_order_references_id"];
         $data["name"] = $order_reference->fields["name"];
         echo $order_reference->getReceptionReferenceLink($data);
      }
      echo "</td>";

      echo "<td>".__("Taken delivery", "order")."</td>";
      echo "<td>";
      Dropdown::showYesNo('states_id', $this->fields['states_id']);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>".__("Delivery form").": </td>";
      echo "<td>";
      if ($canedit) {
         Html::autocompletionTextField($this, "delivery_number");
      } else {
         echo $this->fields["delivery_number"];
      }
      echo "</td>";

      echo "<td>".__("Delivery date").": </td>";
      echo "<td>";
      if ($canedit) {
         Html::showDateField("delivery_date", [
            'value'      => $this->fields["delivery_date"],
            'maybeempty' => true,
            'canedit'    => true
         ]);
      } else {
         echo Html::convDate($this->fields["delivery_date"]);
      }
      echo "</td>";

      echo "</tr>";

      echo "<tr class='tab_bg_1'>";

      echo "<td>".__("Delivery status", "order").": </td>";
      echo "<td>";
      if ($canedit) {
         PluginOrderDeliveryState::Dropdown([
            'name'  => "plugin_order_deliverystates_id",
            'value' => $this->fields["plugin_order_deliverystates_id"]
         ]);
      } else {
         echo Dropdown::getDropdownName("glpi_plugin_order_deliverystates",
                                        $this->fields["plugin_order_deliverystates_id"]);
      }
      echo "</td>";

      echo "<td>".__("Bill", "order")."</td>";
      echo "<td>";
      if (Session::haveRight("plugin_order_bill", UPDATE)) {
         PluginOrderBill::Dropdown([
            'name'  => "plugin_order_bills_id",
            'value' => $this->fields["plugin_order_bills_id"]
         ]);
      } else if (Session::haveRight("plugin_order_bill", UPDATE)) {
         echo Dropdown::getDropdownName("glpi_plugin_order_bills",
                                        $this->fields["plugin_order_bills_id"]);
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>";
      //comments of order
      echo __("Comments").": </td>";
      echo "<td colspan='3'>";
      if ($canedit) {
         echo "<textarea cols='100' rows='4' name='delivery_comment'>" .
             $this->fields["delivery_comment"]."</textarea>";
      } else {
         echo $this->fields["delivery_comment"];
      }
      echo "</td>";
      echo "</tr>";
      $options['candel'] = false;
      $this->showFormButtons($options);

      return true;
   }


   public function showOrderReception($orders_id) {
      global $DB;

      $order_order = new PluginOrderOrder();
      $order_item  = new PluginOrderOrder_Item();
      $reference   = new PluginOrderReference();
      $order_order->getFromDB($orders_id);

      Session::initNavigateListItems($this->getType(),
                                     __("Order", "order")." = ".$order_order->fields["name"]);

      $canedit = self::canCreate()
                 && !$order_order->canUpdateOrder()
                 && !$order_order->isCanceled();

      $result_ref = $order_item->queryDetail($orders_id, 'glpi_plugin_order_references');
      $numref     = $DB->numrows($result_ref);

      while ($data_ref = $DB->fetchArray($result_ref)) {
         self::showOrderReceptionItem($data_ref, $numref, $canedit, $reference, $order_item, $orders_id, $order_order,
                                      'glpi_plugin_order_references');
      }

      $result_reffree = $order_item->queryDetail($orders_id, 'glpi_plugin_order_referencefrees');
      $numreffree     = $DB->numrows($result_reffree);

      while ($data_reffree = $DB->fetchArray($result_reffree)) {
         self::showOrderReceptionItem($data_reffree, $numreffree, $canedit, $reference, $order_item, $orders_id,
                                      $order_order, 'glpi_plugin_order_referencefrees');
      }
   }

   public function showOrderReceptionItem($data_ref, $numref, $canedit, $reference, $order_item, $orders_id, $order_order, $table) {
      global $DB, $CFG_GLPI;

      echo "<div class='center'><table class='tab_cadre_fixe'>";

      if (!$numref) {
         echo "<tr><th>" . __("No item to take delivery of", "order") . "</th></tr></table></div>";
      } else {
         $references_id  = $data_ref["id"];
         $typeRef        = $data_ref["itemtype"];
         $price_taxfree  = $data_ref["price_taxfree"];
         $discount       = $data_ref["discount"];
         $canmassive     = $order_order->canDeliver()
                           && $this->checkItemStatus($orders_id,
                                                     $references_id,
                                                     PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED);

         $massiveactionparams = [
            'extraparams' => [
               'massive_action_fields' => [
                  'plugin_order_orders_id',
                  'plugin_order_references_id',
               ]
            ]
         ];

         $item = new $typeRef();
         $rand = mt_rand();
         echo "<tr><th><ul><li>";
         echo "<a href=\"javascript:showHideDiv('reception$rand','reception_img$rand', '".
              $CFG_GLPI['root_doc']."/pics/plus.png','".$CFG_GLPI['root_doc']."/pics/moins.png');\">";
         echo "<img alt='' name='reception_img$rand' src=\"".$CFG_GLPI['root_doc']."/pics/plus.png\">";
         echo "</a>";
         echo "</li></ul></th>";
         echo "<th>" . __("Assets") . "</th>";
         echo "<th>" . __("Manufacturer") . "</th>";
         echo "<th>" . __("Product reference", "order") . "</th>";
         echo "<th>" . __("Delivered items", "order") . "</th>";
         echo "</tr>";
         echo "<tr class='tab_bg_1 center'>";
         echo "<td></td>";
         echo "<td align='center'>" . $item->getTypeName() . "</td>";
         echo "<td align='center'>" . Dropdown::getDropdownName("glpi_manufacturers",
                                                                $data_ref["manufacturers_id"]) . "</td>";
         if ($table == 'glpi_plugin_order_referencefrees') {
            echo "<td>" . $data_ref['name'] . "</td>";
         } else {
            echo "<td>" . $reference->getReceptionReferenceLink($data_ref) . "</td>";
         }
         $total = $order_item->getTotalQuantityByRefAndDiscount($orders_id,
                                                                $references_id,
                                                                $data_ref["price_taxfree"],
                                                                $data_ref["discount"]);
         echo "<td>" . $order_item->getDeliveredQuantity($orders_id,
                                                         $references_id,
                                                         $data_ref["price_taxfree"],
                                                         $data_ref["discount"])
              . " / " . $total . "</td>";
         echo "</tr></table>";

         echo "<div class='center' id='reception$rand' style='display:none'>";

         $query = "SELECT items.`id` AS IDD,
                             ref.`id` AS id,
                             ref.`templates_id`,
                             items.`states_id`,
                             items.`comment`,
                             items.`plugin_order_deliverystates_id`,
                             items.`delivery_date`,
                             items.`delivery_number`,
                             ref.`name`,
                             ref.`itemtype`,
                             items.`items_id`
                    FROM `glpi_plugin_order_orders_items` as items,
                         `".$table."` as ref
                    WHERE items.`plugin_order_orders_id` = '$orders_id'
                    AND items.`plugin_order_references_id` = '" . $references_id . "'
                    AND items.`plugin_order_references_id` = ref.`id`
                    AND items.`discount` LIKE '" . $discount . "'
                    AND items.`price_taxfree` LIKE '" . $price_taxfree . "' ";
         if ($typeRef == 'SoftwareLicense') {
            $query.=" GROUP BY ref.`name` ";
         }
         $query.=" ORDER BY ref.`name` ";
         $result = $DB->query($query);
         $num    = $DB->numrows($result);

         $all_data = [];
         while ($data = $DB->fetchArray($result)) {
            $all_data[] = $data;
         }

         if ($canmassive && $num) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams['container']   = 'mass'.__CLASS__.$rand;
            $massiveactionparams['item']        = $order_order;
            $massiveactionparams['rand']        = $rand;
            $massiveactionparams['extraparams']['plugin_order_orders_id']     = $orders_id;
            $massiveactionparams['extraparams']['plugin_order_references_id'] = $references_id;
            Html::showMassiveActions($massiveactionparams);
         }

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         if ($order_order->canDeliver()) {
            echo "<th width='15'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
         }
         if ($typeRef != 'SoftwareLicense') {
            echo "<th>" . __("ID") . "</th>";
         }
         echo "<th>" . __("Reference") . "</th>";
         echo "<th>" . __("Status") . "</th>";
         echo "<th>" . __("Delivery date") . "</th>";
         echo "<th>" . __("Delivery form") . "</th>";
         echo "<th>" . __("Delivery status", "order") . "</th>";
         echo "</tr>";

         foreach ($all_data as $data) {
            $detailID = $data["IDD"];
            Session::addToNavigateListItems($this->getType(), $detailID);
            echo "<tr class='tab_bg_2'>";
            $status    = 1;
            if ($typeRef != 'SoftwareLicense') {
               $status = $this->checkThisItemStatus($detailID,
                                                    PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED);
            }

            if ($order_order->canDeliver() && $status) {
               echo "<td width='15' align='left'>";
               Html::showMassiveActionCheckBox(__CLASS__, $detailID);
               echo "</td>";
            } else {
               echo "<td width='15' align='left'></td>";
            }

            if ($typeRef != 'SoftwareLicense') {
               echo "<td align='center'>" . $data["IDD"]."&nbsp;";
               Html::showTooltip($data['comment']);
               echo "</td>";
            }
            if ($table == 'glpi_plugin_order_referencefrees') {
               echo "<td align='center'>" . $data['name'] . "</td>";
            } else {
               echo "<td align='center'>" . $reference->getReceptionReferenceLink($data) . "</td>";
            }
            echo "<td align='center'>";
            $link=Toolbox::getItemTypeFormURL($this->getType());
            if ($canedit && $data["states_id"] == PluginOrderOrder::ORDER_DEVICE_DELIVRED) {
               echo "<a href=\"" . $link . "?id=".$data["IDD"]."\">";
            }
            echo $this->getReceptionStatus($detailID);
            if ($canedit && $data["states_id"] == PluginOrderOrder::ORDER_DEVICE_DELIVRED) {
               echo "</a>";
            }
            echo "</td>";
            echo "<td align='center'>" . Html::convDate($data["delivery_date"]) . "</td>";
            echo "<td align='center'>" . $data["delivery_number"] . "</td>";
            echo "<td align='center'>" .
                 Dropdown::getDropdownName("glpi_plugin_order_deliverystates",
                                           $data["plugin_order_deliverystates_id"]) . "</td>";
            echo Html::hidden("id[$detailID]",
                              ['value' => $detailID]);
            echo Html::hidden("name[$detailID]",
                              ['value' => $data["name"]]);
            echo Html::hidden("plugin_order_references_id[$detailID]",
                              ['value' => $data["id"]]);
            echo Html::hidden("itemtype[$detailID]",
                              ['value' => $data["itemtype"]]);
            echo Html::hidden("templates_id[$detailID]",
                              ['value' => $data["templates_id"]]);
            echo Html::hidden("states_id[$detailID]",
                              ['value' => $data["states_id"]]);

         }
         echo "</table>";

         if ($canmassive) {
            if ($num > 10) {
               $massiveactionparams['ontop'] = false;
               Html::showMassiveActions($massiveactionparams);
            }
            Html::closeForm();
         }

         echo "</div>";
      }
      echo "<br>";
   }


   function getForbiddenStandardMassiveAction() {
      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      $forbidden[] = 'purge';
      $forbidden[] = 'ObjectLock:unlock';
      return $forbidden;
   }


   function getSpecificMassiveActions($checkitem = null) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);
      $sep     = __CLASS__.MassiveAction::CLASS_ACTION_SEPARATOR;

      $actions[$sep.'reception'] = __("Take item delivery", "order");

      return $actions;
   }


   static function showMassiveActionsSubForm(MassiveAction $ma) {
      $reception = new self;
      switch ($ma->getAction()) {
         case 'reception':
            $reception->showReceptionForm($ma->POST);
            break;
      }

      return parent::showMassiveActionsSubForm($ma);
   }


   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      $reception  = new PluginOrderReception();
      switch ($ma->getAction()) {
         case 'reception':
            $reception->updateReceptionStatus($ma);
            break;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }


   public function showReceptionForm($params = []) {
      echo "<label class='order_ma'>" . __("Delivery date") . "</label>";
      Html::showDateField("delivery_date", ['value'      => date("Y-m-d"),
                                            'maybeempty' => true,
                                            'canedit'    => true]);

      echo "<label class='order_ma'>".__("Delivery form") . "</label>";
      echo "<input type='text' name='delivery_number' size='20'>";

      echo "<label class='order_ma'>".__("Delivery status", "order") . "</label>";
      PluginOrderDeliveryState::Dropdown(['name' => "plugin_order_deliverystates_id"]);

      $config = PluginOrderConfig::getConfig();
      if ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK) {
         echo "<label class='order_ma'>". __("Enable automatic generation", "order") . "</label>";
         Dropdown::showFromArray('manual_generate', [
            PluginOrderConfig::CONFIG_NEVER => __('No'),
            PluginOrderConfig::CONFIG_YES   => __('Yes')
         ]);

         echo "<label class='order_ma'>" . __("Default name", "order") . "</label>";
         Html::autocompletionTextField($config, "generated_name");

         echo "<label class='order_ma'>" . __("Default serial number", "order") . "</label>";
         Html::autocompletionTextField($config, "generated_serial");

         echo "<label class='order_ma'>" . __("Default inventory number", "order") . "<label>";
         Html::autocompletionTextField($config, "generated_otherserial");
      }
   }


   public function dropdownReceptionActions($itemtype, $plugin_order_references_id, $plugin_order_orders_id) {
      global $CFG_GLPI;

      $rand = mt_rand();

      echo "<select name='receptionActions$rand' id='receptionActions$rand'>";
      echo "<option value='0' selected>".Dropdown::EMPTY_VALUE."</option>";
      echo "<option value='reception'>".__("Take item delivery", "order")."</option>";
      echo "</select>";
      $params = [
         'action'                     => '__VALUE__',
         'itemtype'                   => $itemtype,
         'plugin_order_references_id' => $plugin_order_references_id,
         'plugin_order_orders_id'     => $plugin_order_orders_id,
      ];
      Ajax::updateItemOnSelectEvent("receptionActions$rand", "show_receptionActions$rand",
                                    Plugin::getWebDir('order')."/ajax/receptionactions.php",
                                    $params);
      echo "<span id='show_receptionActions$rand'>&nbsp;</span>";
   }


   public function getReceptionStatus($ID) {
      global $DB;

      $detail = new PluginOrderOrder_Item();
      $detail->getFromDB($ID);

      switch ($detail->fields["states_id"]) {
         case PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED :
            return __("Waiting for delivery", "order");

         case PluginOrderOrder::ORDER_DEVICE_DELIVRED :
            return __("Taken delivery", "order");

         default :
            return "";
      }
   }


   public function updateBulkReceptionStatus($params) {
      global $DB;

      $query = "SELECT `id`, `itemtype`, 'entities_id'
               FROM `glpi_plugin_order_orders_items`
               WHERE `plugin_order_orders_id` = '{$params["plugin_order_orders_id"]}'
               AND `plugin_order_references_id` = '{$params["plugin_order_references_id"]}'
               AND `states_id` = 0 ";

      $result  = $DB->query($query);
      $nb      = $DB->numrows($result);

      if ($nb < $params['number_reception']) {
         Session::addMessageAfterRedirect(__("Not enough items to deliver", "order"), true, ERROR);
      } else {
         for ($i = 0; $i < $params['number_reception']; $i++) {
            $this->receptionOneItem($DB->result($result, $i, 0), $params['plugin_order_orders_id'],
                                    $params["delivery_date"], $params["delivery_number"],
                                    $params["plugin_order_deliverystates_id"]);

            // Automatic generate asset
            $options = [
               "itemtype"                   => $DB->result($result, $i, "itemtype"),
               "items_id"                   => $DB->result($result, $i, "id"),
               "entities_id"                => $_SESSION['glpiactive_entity'],
               "plugin_order_orders_id"     => $params['plugin_order_orders_id'],
               "plugin_order_references_id" => $params["plugin_order_references_id"],
            ];

            $config = PluginOrderConfig::getConfig();
            if ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK) {
               $options['manual_generate'] = $params['manual_generate'];
               if ($params['manual_generate'] == 1) {
                  $options['name']            = $params['generated_name'];
                  $options['serial']          = $params['generated_serial'];
                  $options['otherserial']     = $params['generated_otherserial'];
                  $options['generate_assets'] = $params['generate_assets'];
               }
            }
            self::generateAsset($options);
            $this->updateReceptionStatus([
               'items' => [
                  __CLASS__ => [
                     $DB->result($result, $i, 0) => 'on'
                  ]
               ]
            ]);
         }
         self::updateDelivryStatus($params['plugin_order_orders_id']);
      }
   }


   public function receptionOneItem($detailID, $orders_id, $delivery_date, $delivery_nb, $state_id) {
      global $CFG_GLPI;

      $detail = new PluginOrderOrder_Item();
      $detail->update([
         "id"                             => $detailID,
         "delivery_date"                  => $delivery_date,
         "states_id"                      => PluginOrderOrder::ORDER_DEVICE_DELIVRED,
         "delivery_number"                => $delivery_nb,
         "plugin_order_deliverystates_id" => $state_id,
      ]);

      Session::addMessageAfterRedirect(__("Item successfully taken delivery", "order"), true);
   }


   public function receptionAllItem($detailID, $ref_id, $orders_id, $delivery_date, $delivery_nb, $state_id) {
      global $DB;

      $detail = new PluginOrderOrder_Item();
      $detail->getFromDB($detailID);
      $result = $detail->queryRef($_POST["plugin_order_orders_id"],
                                  $ref_id,
                                  $detail->fields["price_taxfree"],
                                  $detail->fields["discount"],
                                  PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED);
      $nb = $DB->numrows($result);

      if ($nb) {
         for ($i = 0; $i < $nb; $i++) {
            $detailID = $DB->result($result, $i, 'id');
            $detail->update([
               "id"                             => $detailID,
               "delivery_date"                  => $delivery_date,
               "states_id"                      => PluginOrderOrder::ORDER_DEVICE_DELIVRED,
               "delivery_number"                => $delivery_nb,
               "plugin_order_deliverystates_id" => $state_id,
            ]);
         }
      }
      Session::addMessageAfterRedirect(__("Item successfully taken delivery", "order"), true);
   }


   public function updateReceptionStatus($params) {
      global $DB;

      $detail                 = new PluginOrderOrder_Item();
      $plugin_order_orders_id = 0;
      $ma                     = false;

      // from MassiveAction process, we get ma object, so convert it into array
      if (is_object($params)) {
         $ma      = $params;
         $params2 = (array) $params;
      }

      if (isset($params2['items'][__CLASS__])) {
         $additional_data_ite = $DB->request([
            'SELECT' => [
               'glpi_plugin_order_orders_items.id',
               'glpi_plugin_order_references.id AS plugin_order_references_id',
               'glpi_plugin_order_references.itemtype',
            ],
            'FROM' => [
               'glpi_plugin_order_orders_items'
            ],
            'LEFT JOIN' => [
               'glpi_plugin_order_references' => [
                  'FKEY' => [
                     'glpi_plugin_order_orders_items' => 'plugin_order_references_id',
                     'glpi_plugin_order_references'   => 'id',
                  ]
               ]
            ],
            'WHERE' => [
               'glpi_plugin_order_orders_items.id' => array_keys($params2['items'][__CLASS__])
            ]
         ]);
         $additional_data = [];
         foreach ($additional_data_ite as $add_values) {
            $additional_data[$add_values['id']] = $add_values;
         }

         foreach ($params2['items'][__CLASS__] as $key => $val) {
            if ($val > 1) {
               $add_data = $additional_data[$key];
               if ($add_data["itemtype"] == 'SoftwareLicense') {
                  $this->receptionAllItem($key,
                                          $add_data["plugin_order_references_id"],
                                          $params2['POST']["plugin_order_orders_id"],
                                          $params2['POST']["delivery_date"],
                                          $params2['POST']["delivery_number"],
                                          $params2['POST']["plugin_order_deliverystates_id"]);

                  $plugin_order_orders_id = $params2['POST']["plugin_order_orders_id"];
               } else {
                  if ($detail->getFromDB($key)) {
                     if (!$plugin_order_orders_id) {
                        $plugin_order_orders_id = $detail->fields["plugin_order_orders_id"];
                     }

                     if ($detail->fields["states_id"] == PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED) {
                        $this->receptionOneItem($key, $plugin_order_orders_id,
                                                $params2['POST']["delivery_date"],
                                                $params2['POST']["delivery_number"],
                                                $params2['POST']["plugin_order_deliverystates_id"]);
                        if ($ma !== false) {
                           $ma->itemDone(__CLASS__, $key, MassiveAction::ACTION_OK);
                        }
                     } else {
                        Session::addMessageAfterRedirect(__("Item already taken delivery", "order"), true, ERROR);
                        if ($ma !== false) {
                           $ma->itemDone(__CLASS__, $key, MassiveAction::ACTION_KO);
                        }
                     }

                     // Automatic generate asset
                     $options = [
                        "itemtype"                   => $add_data["itemtype"],
                        "items_id"                   => $key,
                        'entities_id'                => $detail->getEntityID(),
                        "plugin_order_orders_id"     => $detail->fields["plugin_order_orders_id"],
                        "plugin_order_references_id" => $add_data["plugin_order_references_id"],
                     ];

                     $config = PluginOrderConfig::getConfig(true);
                     if ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK) {
                        $options['manual_generate'] = $params2['POST']['manual_generate'];
                        if ($params2['POST']['manual_generate'] == 1) {
                           $options['name']            = $params2['POST']['generated_name'];
                           $options['serial']          = $params2['POST']['generated_serial'];
                           $options['otherserial']     = $params2['POST']['generated_otherserial'];
                        }
                     }
                     self::generateAsset($options);
                  }
               }
            }// $val == 1
         }

         self::updateDelivryStatus($plugin_order_orders_id);
      } else {
         Session::addMessageAfterRedirect(__("No item selected", "order"), false, ERROR);
      }
   }


   public static function updateDelivryStatus($orders_id) {
      global $DB;

      $config = PluginOrderConfig::getConfig();
      $order  = new PluginOrderOrder();

      $order->getFromDB($orders_id);

      $query = "SELECT `states_id`
                FROM `glpi_plugin_order_orders_items`
                WHERE `plugin_order_orders_id` = '$orders_id'";
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      $delivery_status = 0;
      $is_delivered    = 1; //Except order to be totally delivered
      if ($number) {
         while ($data = $DB->fetchArray($result)) {
            if ($data["states_id"] == PluginOrderOrder::ORDER_DEVICE_DELIVRED) {
               $delivery_status = 1;
            } else {
               $is_delivered    = 0;
            }
         }
      }

      //Are all items delivered ?
      if ($is_delivered && !$order->isDelivered()) {
          $order->updateOrderStatus($orders_id, $config->getDeliveredState());
         //At least one item is delivered
      } else {
         if ($delivery_status) {
            $order->updateOrderStatus($orders_id,
                                      $config->getPartiallyDeliveredState());
         }
      }
   }


   public function prepareInputForUpdate($input) {
      if (isset($input['states_id']) && !$input['states_id']) {
         $input['delivery_date']                  = null;
         $input['delivery_number']                = '';
         $input['plugin_order_deliverystates_id'] = 0;
      }
      return $input;
   }


   public function post_updateItem($history = 1) {
      self::updateDelivryStatus($this->fields['plugin_order_orders_id']);
   }


   public function post_purgeItem() {
      self::updateDelivryStatus($this->fields['plugin_order_orders_id']);
   }


   /**
   *
   * @param $options
   *
   * return nothing
   */
   public static function generateAsset($options = []) {
      // No asset should be generated for PluginOrderOther and PluginOrderReferenceFree items.
      if (array_key_exists('itemtype', $options)
          && (in_array($options['itemtype'], [PluginOrderOther::class, PluginOrderReferenceFree::class]))) {
         return;
      }

      // Retrieve configuration for generate assets feature
      $config = PluginOrderConfig::getConfig();
      if ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_YES
          || ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK
              && $options['manual_generate'] == 1)) {
         // Automatic generate assets on delivery
         $rand = mt_rand();
         $item = [
            "name"                   => $config->getGeneratedAssetName().$rand,
            "serial"                 => $config->getGeneratedAssetSerial().$rand,
            "otherserial"            => $config->getGeneratedAssetOtherserial().$rand,
            "entities_id"            => $options['entities_id'],
            "locations_id"           => '0',
            "groups_id"              => '0',
            "states_id"              => '0',
            "itemtype"               => $options["itemtype"],
            "id"                     => $options["items_id"],
            "plugin_order_orders_id" => $options["plugin_order_orders_id"],
         ];

         if ($config->canGenerateAsset() == PluginOrderConfig::CONFIG_ASK
             && ($options['manual_generate'] == 1)) {
            $item['name']        = $options['name'].$rand;
            $item['serial']      = $options['serial'].$rand;
            $item['otherserial'] = $options['otherserial'].$rand;
         }

         $options_gen = [
            "plugin_order_orders_id"     => $options["plugin_order_orders_id"],
            "plugin_order_references_id" => $options["plugin_order_references_id"],
            "id"                         => [$item],
            'itemtype'                   => $options['itemtype'],
         ];

         if ($config->canGenerateTicket()) {
            $options_gen["generate_ticket"] = [
               "entities_id"        => $options['entities_id'],
               "tickettemplates_id" => $config->fields['tickettemplates_id_delivery'],
            ];
         }

         $link = new PluginOrderLink();
         $link->generateNewItem($options_gen);
      }
   }


   public static function countForOrder(PluginOrderOrder $item) {
      return countElementsInTable('glpi_plugin_order_orders_items',
                                  ['plugin_order_orders_id' => $item->getID()]);
   }


   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'PluginOrderOrder'
          && Session::haveRight('plugin_order_order', PluginOrderOrder::canView())
          && $item->getState() > PluginOrderOrderState::WAITING_FOR_APPROVAL) {
         return self::createTabEntry(__("Item delivered", "order"), self::countForOrder($item));
      }
   }


   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'PluginOrderOrder') {
         $reception = new self();
         $reception->showOrderReception($item->getID());
      }

      return true;
   }


   function rawSearchOptions() {
      $tab = [];

      $tab[] = [
         'id'            => '2',
         'table'         => $this->getTable(),
         'field'         => 'delivery_number',
         'name'          => __('Delivery form'),
         'autocomplete'  => true,
      ];

      return $tab;
   }
}
