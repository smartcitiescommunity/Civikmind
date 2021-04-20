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

class PluginOrderLink extends CommonDBChild {

   public static $rightname         = 'plugin_order_order';

   public $dohistory                = true;

   public static $itemtype          = 'PluginOrderOrder';

   public static $items_id          = 'plugin_order_orders_id';

   public static $checkParentRights = self::DONT_CHECK_ITEM_RIGHTS;


   public static function getTypeName($nb = 0) {
      return __("Generation", "order");
   }


   public static function getTable($classname = null) {
      return "glpi_plugin_order_orders_items";
   }


   public static function getTypesThanCannotBeGenerared() {
      return ['ConsumableItem', 'CartridgeItem', 'SoftwareLicense', 'Contract'];
   }


   public function showItemGenerationForm($params) {
      global $CFG_GLPI;

      // Retrieve configuration for generate assets feature
      $config = PluginOrderConfig::getConfig();

      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      $colspan = "9";
      if (Session::isMultiEntitiesMode()) {
         $colspan = "10";
      }

      echo "<tr><th colspan='$colspan'>".__("Generate item", "order")."</th></tr>";

      echo "<tr>";
      echo "<th>".__("Product reference", "order")."</th>";
      echo "<th>".__("Name")."</th>";
      echo "<th>".__("Serial number")."</th>";
      echo "<th>".__("Inventory number")."</th>";
      echo "<th>".__("Template name")."</th>";
      if (Session::isMultiEntitiesMode() && count($_SESSION['glpiactiveentities']) > 1) {
         echo "<th>".__("Entity")."</th>";
      }
      echo "<th>" . __("Location") . "</th>";
      echo "<th>" . __("Group") . "</th>";
      echo "<th>" . __("Status") . "</th>";
      echo "</tr>";

      $order = new PluginOrderOrder();
      $order->getFromDB($params["plugin_order_orders_id"]);

      $reference = new PluginOrderReference();
      $i         = 0;
      $found     = false;

      foreach ($params["items"][__CLASS__] as $key => $val) {
         $detail = new PluginOrderOrder_Item();
         $detail->getFromDB($key);
         $reference->getFromDB($detail->getField('plugin_order_references_id'));
         if (!$detail->fields["items_id"]) {
            $itemtype = $detail->getField('itemtype');
            echo "<tr class='tab_bg_1'><td align='center'>".$reference->getField('name')."</td>";
            $templateID = $reference->checkIfTemplateExistsInEntity($val,
                                                                    $detail->getField('itemtype'),
                                                                    $order->fields["entities_id"]);

            if ($templateID) {
               $item = new $itemtype();
               $item->getFromDB($templateID);

               $name         = $item->fields["name"];
               $serial       = $item->fields["serial"];
               $otherserial  = $item->fields["otherserial"];
               $states_id    = $item->fields["states_id"];
               $locations_id = $item->fields["locations_id"];
               $groups_id    = $item->fields["groups_id"];
            } else {
               $name         = false;
               $serial       = false;
               $otherserial  = false;
               $states_id    = false;
               $locations_id = false;
               $groups_id    = false;
            }

            if (!$name) {
               echo "<td><input type='text' size='20' name='id[$i][name]'></td>";
            } else {
               echo "<td align='center'>".Dropdown::EMPTY_VALUE."</td>";
               echo Html::hidden("id[$i][name]", ['value' => '']);
            }

            echo "<td align='center'><input type='text' size='20' name='id[$i][serial]'></td>";

            if ($otherserial) {
               echo "<td align='center'>".Dropdown::EMPTY_VALUE."</td>";
               echo Html::hidden("id[$i][otherserial]", ['value' => '']);
            } else {
               echo "<td><input type='text' size='20' name='id[$i][otherserial]'></td>";
            }

            echo "<td align='center'>";
            if ($templateID) {
               echo $reference->getTemplateName($itemtype, $templateID);
            }
            echo "</td>";

            if (Session::isMultiEntitiesMode()
                  && count($_SESSION['glpiactiveentities']) > 1) {
               $order_web_dir = Plugin::getWebDir('order');
               echo "<td>";
               $rand = Entity::Dropdown([
               'name'   => "id[$i][entities_id]",
               'value'  => $order->fields["entities_id"],
               'entity' => $order->fields["is_recursive"] ? getSonsOf('glpi_entities', $order->fields["entities_id"]) : $order->fields["entities_id"]]
               );
               Ajax::updateItemOnSelectEvent("dropdown_id[$i][entities_id]$rand",
                                          "show_location_by_entity_id_$i",
                                          "$order_web_dir/ajax/linkactions.php",
                                          ['entities' => '__VALUE__',
                                           'action'   => 'show_location_by_entity',
                                           'id'       => $i
                                          ]);
               Ajax::updateItemOnSelectEvent("dropdown_id[$i][entities_id]$rand",
                                          "show_group_by_entity_id_$i",
                                          "$order_web_dir/ajax/linkactions.php",
                                          ['entities' => '__VALUE__',
                                           'action'   => 'show_group_by_entity',
                                           'id'       => $i
                                          ]);
               Ajax::updateItemOnSelectEvent("dropdown_id[$i][entities_id]$rand",
                                          "show_state_by_entity_id_$i",
                                          "$order_web_dir/ajax/linkactions.php",
                                          ['entities' => '__VALUE__',
                                           'action'   => 'show_state_by_entity',
                                           'id'       => $i
                                          ]);
               $entity = $order->fields["entities_id"];
               echo "</td>";
            } else {
               $entity = $_SESSION["glpiactive_entity"];
               echo "<input type='hidden' name='id[$i][entities_id]' value="
               . $entity . ">";
            }
               echo "<td>";

              echo "<span id='show_location_by_entity_id_$i'>";
              Location::dropdown(['name'   => "id[$i][locations_id]",
                                 'entity' => $entity,
                                 'value'  => $locations_id,
                                 ]);
              echo "</span>";
              echo "</td>";
              echo "<td>";
              echo "<span id='show_group_by_entity_id_$i'>";
              Group::dropdown(['name'   => "id[$i][groups_id]",
                               'entity' => $entity,
                               'value'  => $groups_id,
                              ]);
              echo "</span>";
              echo "</td>";
              echo "<td>";
              echo "<span id='show_state_by_entity_id_$i'>";
              $condition = self::getCondition($itemtype);
              State::dropdown(['name'      => "id[$i][states_id]",
                               'entity'    => $entity,
                               'condition' => $condition,
                               'value'     => $states_id,
                              ]);
               echo "</span>";
               echo "</td>";
               echo "</tr>";
               echo Html::hidden("id[$i][id]", ['value' => $key]);
               $found = true;
         }
         $i++;
      }

      if (!$found) {
         echo "<tr><td align='center' colspan='$colspan' class='tab_bg_2'>".
              __("No item to generate", "order")."</td></tr>";
      }

      echo "</table>";
      echo "</div>";

      if (!$found) {
         return false;
      }
   }

   public function queryRef($ID, $table) {
      global $DB;
      if ($table == 'glpi_plugin_order_references') {
         $condition = "AND `glpi_plugin_order_orders_items`.`itemtype` NOT LIKE 'PluginOrderReferenceFree'";
      } else {
         $condition = "AND `glpi_plugin_order_orders_items`.`itemtype` LIKE 'PluginOrderReferenceFree'";
      }
      $query_ref = "SELECT `glpi_plugin_order_orders_items`.`id` AS IDD,
                           `glpi_plugin_order_orders_items`.`plugin_order_references_id` AS id,
                           ref.`name`,
                           ref.`itemtype`,
                           ref.`manufacturers_id`,
                           `glpi_plugin_order_orders_items`.`price_taxfree`,
                           `glpi_plugin_order_orders_items`.`discount`
                    FROM `glpi_plugin_order_orders_items`, `" . $table . "` ref
                    WHERE `glpi_plugin_order_orders_items`.`plugin_order_orders_id` = '$ID'
                    AND `glpi_plugin_order_orders_items`.`plugin_order_references_id` = ref.`id`
                    AND `glpi_plugin_order_orders_items`.`states_id` = '" . PluginOrderOrder::ORDER_DEVICE_DELIVRED . "'
                    $condition
                    GROUP BY `glpi_plugin_order_orders_items`.`plugin_order_references_id`
                    ORDER BY ref.`name`";
      return $DB->query($query_ref);

   }


   public static function getCondition($itemtype) {
      switch ($itemtype) {
         case 'Computer' :
            return ['is_visible_computer' => 1];
         case 'Monitor' :
            return ['is_visible_monitor' => 1];
         case 'Printer' :
            return ['is_visible_printer' => 1];
         case 'Phone' :
            return ['is_visible_phone' => 1];
         case 'NetworkEquipment' :
            return ['is_visible_networkequipment' => 1];
         case 'Peripheral' :
            return ['is_visible_peripheral' => 1];
         case 'SoftwareLicense':
            return ['is_visible_softwareversion' => 1];
      }
   }


   public function showOrderLink($plugin_order_orders_id) {
      global $DB, $CFG_GLPI;

      $PluginOrderOrder      = new PluginOrderOrder();

      $PluginOrderOrder->getFromDB($plugin_order_orders_id);
      $canedit = $PluginOrderOrder->canDeliver()
                  && !$PluginOrderOrder->canUpdateOrder()
                  && !$PluginOrderOrder->isCanceled();

      $result_ref = $this->queryRef($plugin_order_orders_id, 'glpi_plugin_order_references');
      $numref     = $DB->numrows($result_ref);
      while ($data_ref = $DB->fetchArray($result_ref)) {
         $link = new self();
         $link->showOrderLinkItem($numref, $data_ref, $canedit, $plugin_order_orders_id, $PluginOrderOrder,
                                  'glpi_plugin_order_references');
      }

      $result_reffree = $this->queryRef($plugin_order_orders_id, 'glpi_plugin_order_referencefrees');
      $numreffree     = $DB->numrows($result_reffree);
      while ($data_reffree = $DB->fetchArray($result_reffree)) {
         $link = new self();
         $link->showOrderLinkItem($numreffree, $data_reffree, $canedit, $plugin_order_orders_id, $PluginOrderOrder,
                                  'glpi_plugin_order_referencefrees');
      }
   }

   public function showOrderLinkItem($numref, $data_ref, $canedit, $plugin_order_orders_id, $PluginOrderOrder, $table) {
      global $DB, $CFG_GLPI;

      $PluginOrderOrder_Item = new PluginOrderOrder_Item();
      $PluginOrderReference  = new PluginOrderReference();
      $PluginOrderReception  = new PluginOrderReception();

      echo "<div class='center'>";
      echo "<table class='tab_cadre_fixe'>";
      if (!$numref) {
         echo "<tr><th>".__("No item to take delivery of", "order")."</th></tr>";
         echo "</table>";
         echo "</div>";
      } else {
         $plugin_order_references_id = $data_ref["id"];
         $itemtype                   = $data_ref["itemtype"];
         $canuse                     = ($itemtype != 'PluginOrderOther') && ($itemtype != 'PluginOrderReferenceFree');
         $item                       = new $itemtype();
         $rand                       = mt_rand();

         // add hidden fields which need to be passed between massive action functions
         $massiveactionparams = [
            'extraparams' => [
               'massive_action_fields' => [
                  'plugin_order_orders_id',
                  'plugin_order_references_id',
               ]
            ]
         ];

         $query = "SELECT  items.`id` AS IDD,
                              ref.`id` AS id,
                              ref.`templates_id`,
                              items.`states_id`,
                              items.`delivery_date`,
                              items.`delivery_number`,
                              ref.`name`,
                              ref.`itemtype`,
                              items.`items_id`,
                              items.`price_taxfree`,
                              items.`discount`
                       FROM `glpi_plugin_order_orders_items` as items,
                            `".$table."` ref
                       WHERE items.`plugin_order_orders_id` = '$plugin_order_orders_id'
                        AND items.`plugin_order_references_id` = '$plugin_order_references_id'
                        AND items.`plugin_order_references_id` = ref.`id`
                        AND items.`states_id` = '".PluginOrderOrder::ORDER_DEVICE_DELIVRED."'";
         if ($table == 'glpi_plugin_order_referencefrees') {
            $query .= "AND items.`itemtype` LIKE 'PluginOrderReferenceFree'";
         } else {
            $query .= "AND items.`itemtype` NOT LIKE 'PluginOrderReferenceFree'";
         }
         if ($itemtype == 'SoftwareLicense') {
            $query .= " GROUP BY items.`price_taxfree`,
                                    items.`discount`";
         }
         $query .= " ORDER BY ref.`name`";

         $result = $DB->query($query);
         $num    = $DB->numrows($result);
         $all_data = [];
         while ($data = $DB->fetchArray($result)) {
            $all_data[] = $data;
         }

         echo "<tr><th>";
         echo "<ul><li>";
         echo "<a href=\"javascript:showHideDiv('generation$rand','generation_img$rand', " .
              "'".$CFG_GLPI['root_doc']."/pics/plus.png','".$CFG_GLPI['root_doc']."/pics/moins.png');\">";
         echo "<img alt='' name='generation_img$rand' src=\"".$CFG_GLPI['root_doc']."/pics/plus.png\">";
         echo "</a>";
         echo "</li></ul></th>";
         echo "<th>".__("Assets")."</th>";
         echo "<th>".__("Manufacturer")."</th>";
         echo "<th>".__("Product reference", "order")."</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_1 center'>";
         echo "<td></td>";
         echo "<td align='center'>" . $item->getTypeName() . "</td>";
         echo "<td align='center'>"
              . Dropdown::getDropdownName("glpi_manufacturers", $data_ref["manufacturers_id"]) . "</td>";
         if ($table == 'glpi_plugin_order_referencefrees') {
            echo "<td>" . $data_ref['name'] . "&nbsp;($num)</td>";
         } else {
            echo "<td>" . $PluginOrderReference->getReceptionReferenceLink($data_ref) . "&nbsp;($num)</td>";
         }
         echo "</tr>";

         echo "</table>";

         echo "<div class='center' id='generation$rand' style='display:none'>";
         if ($canedit & $canuse && $num) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams['container']   = 'mass'.__CLASS__.$rand;
            $massiveactionparams['item']        = $PluginOrderOrder;
            $massiveactionparams['rand']        = $rand;
            $massiveactionparams['extraparams']['plugin_order_orders_id']     = $plugin_order_orders_id;
            $massiveactionparams['extraparams']['plugin_order_references_id'] = $plugin_order_references_id;
            Html::showMassiveActions($massiveactionparams);
         }

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         if ($canedit & $canuse && $num) {
            echo $header = "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)
                           ."</th>";
         }
         if ($itemtype != 'SoftwareLicense') {
            echo "<th>" . __("ID") . "</th>";
         } else {
            echo "<th>" . __("Quantity", "order") . "</th>";
         }
         echo "<th>" . __("Reference") . "</th>";
         echo "<th>" . __("Status") . "</th>";
         echo "<th>" . __("Delivery date") . "</th>";
         echo "<th>" . _n("Associated item", "Associated items", 2) . "</th>";
         echo "<th>" . __("Serial number") . "</th></tr>";

         foreach ($all_data as $data) {
            $detailID = $data["IDD"];

            echo "<tr class='tab_bg_2'>";

            if ($canedit & $canuse) {
               echo "<td width='15' align='left'>";
               Html::showMassiveActionCheckBox(__CLASS__, $detailID);
               echo "</td>";
            }

            if ($itemtype != 'SoftwareLicense') {
               echo "<td align='center'>" . $data["IDD"] . "</td>";
            } else {
               echo "<td align='center'>";
               echo $PluginOrderOrder_Item->getTotalQuantityByRefAndDiscount($plugin_order_orders_id,
                                                                             $plugin_order_references_id,
                                                                             $data["price_taxfree"],
                                                                             $data["discount"]);
               echo "</td>";
            }
            if ($table == 'glpi_plugin_order_referencefrees') {
               echo "<td align='center'>" . $data['name'] . "</td>";
            } else {
               echo "<td align='center'>" . $PluginOrderReference->getReceptionReferenceLink($data) . "</td>";
            }
            echo "<td align='center'>" . $PluginOrderReception->getReceptionStatus($detailID) . "</td>";
            echo "<td align='center'>" . Html::convDate($data["delivery_date"]) . "</td>";
            echo "<td align='center'>" . $this->getReceptionItemName($data["items_id"], $data["itemtype"]);
            echo "<td align='center'>" . $this->getItemSerialNumber($data["items_id"], $data["itemtype"]) . "</td>";
         }
         echo "</tr>";
         echo "</table>";
         if ($canedit && $canuse) {
            if ($num > 10) {
               $massiveactionparams['ontop'] = false;
               Html::showMassiveActions($massiveactionparams);
            }
            Html::closeForm();
         }
         echo "</div>";
         echo "</div>";
      }
      echo "<br>";
   }

   /**
    * Returns serial number of associated item.
    *
    * @param integer $items_id
    * @param string  $itemtype
    * @return string
    */
   protected function getItemSerialNumber($items_id, $itemtype) {

      global $DB;

      if ($itemtype == 'PluginOrderOther' || $itemtype == 'PluginOrderReferenceFree') {
         return '';
      }

      $result = $DB->request([
         'SELECT' => 'serial',
         'FROM'   => $itemtype::getTable(),
         'WHERE'  => ['id' => $items_id]
      ]);
      $data = $result->next();
      return $data['serial'];
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

      $actions[$sep.'generation'] = __("Generate item", "order");
      $actions[$sep.'createLink'] = __("Link to an existing item", "order");
      $actions[$sep.'deleteLink'] = __("Delete item link", "order");

      return $actions;
   }


   static function showMassiveActionsSubForm(MassiveAction $ma) {
      $link = new self;
      $reference = new PluginOrderReference;

      switch ($ma->getAction()) {
         case 'generation':
            return $link->showItemGenerationForm($ma->POST);
            break;
         case 'createLink' :
            $reference->getFromDB($ma->POST["plugin_order_references_id"]);
            $reference->dropdownAllItemsByType("items_id", $reference->fields["itemtype"],
                                               $_SESSION["glpiactiveentities"],
                                               $reference->fields["types_id"],
                                               $reference->fields["models_id"]);
            break;
      }

      return parent::showMassiveActionsSubForm($ma);
   }


   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      global $DB;

      // retrieve additional informations for each items
      $ma->POST['add_items'] = [];
      if (isset($ma->items[__CLASS__])) {
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
               'glpi_plugin_order_orders_items.id' => array_keys($ma->items[__CLASS__])
            ]
         ]);
         foreach ($additional_data_ite as $add_values) {
            $ma->POST['add_items'][$add_values['id']] = $add_values;
         }
      }

      $link = new self;
      switch ($ma->getAction()) {
         case 'generation':
            $newIDs = $link->generateNewItem($ma->POST);
            foreach ($ma->items[__CLASS__] as $key => $val) {
               if (isset($newIDs[$key]) && $newIDs[$key]) {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               }
            }

            break;

         case 'createLink':
            if (count($ids) > 1) {
               $ma->addMessage(__("Cannot link several items to one detail line", "order"));
               foreach ($ids as $id) {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
               }
               return false;
            }

            $order_item = new PluginOrderOrder_Item();
            foreach ($ma->items[__CLASS__] as $key => $val) {
               $order_item->getFromDB($val);
               if ($order_item->fields["states_id"] == PluginOrderOrder::ORDER_DEVICE_NOT_DELIVRED) {
                  $ma->addMessage(__("Cannot link items not delivered", "order"));
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               } else {
                  $link->createLinkWithItem($key,
                                            $ma->POST["items_id"],
                                            $ma->POST['add_items'][$key]['itemtype'],
                                            $ma->POST['plugin_order_orders_id']);
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
               }
            }
            break;

         case 'deleteLink':
            foreach ($ma->items[__CLASS__] as $key => $val) {
               $link->deleteLinkWithItem($key,
                                         $ma->POST['add_items'][$key]['itemtype'],
                                         $ma->POST['plugin_order_orders_id']);
               $ma->itemDone($item->getType(), $val, MassiveAction::ACTION_OK);
            }
            break;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }


   public function getLinkedItemDetails($itemtype, $items_id) {
      $comments = "";

      switch ($itemtype) {
         case 'ConsumableItem' :
            $ci = new Consumable();
            if ($ci->getFromDB($items_id)) {
               $ct = new ConsumableItem();
               $ct->getFromDB($ci->fields['consumableitems_id']);

               $comments .= "<strong>".__("Entity").":</strong> ";
               $comments .= Dropdown::getDropdownName("glpi_entities", $ct->fields["entities_id"]);
               $comments .= '<br><strong>'.__("Consumable").' : </strong> #'.$items_id;
               $comments .= '<br><strong>'.__("Consumable type").' : </strong>';
               $comments .= $ct->fields['name'];
               $comments .= '<br><strong>'.__("Manufacturer").' : </strong>';
               $comments .= Dropdown::getDropdownName('glpi_manufacturers', $ct->fields['manufacturers_id']);
               $comments .= '<br><strong>'.__("State").' : </strong>';
               $comments .= (!$ci->fields['users_id'] ? __("In stock") : __("Used"));
               if ($ci->fields['users_id']) {
                  $comments .= '<br><strong>'.__("User").' : </strong>';
                  $comments .= Dropdown::getDropdownName('glpi_users', $ci->fields['users_id']);
               }
            }
            break;
         case 'CartridgeItem' :
            $ci = new Cartridge();
            if ($ci->getFromDB($items_id)) {
               $ct = new CartridgeItem();
               $ct->getFromDB($ci->fields['cartridgeitems_id']);

               $comments .= "<strong>".__("Entity").":</strong> ";
               $comments .= Dropdown::getDropdownName("glpi_entities", $ct->fields["entities_id"]);
               $comments .= '<br><strong>'.__("Cartridge").' : </strong> #'.$items_id;
               $comments .= '<br><strong>'._n("New", "New", 2).' : </strong>';
               $comments .= $ct->fields['name'];
               $comments .= '<br><strong>'.__("Manufacturer").' : </strong>';
               $comments .= Dropdown::getDropdownName('glpi_manufacturers', $ct->fields['manufacturers_id']);
            }
            break;
         // case 'Computer' :
         // case 'Monitor' :
         // case 'NetworkEquipment' :
         // case 'Peripheral' :
         // case 'Phone' :
         // case 'Printer' :
         default :
            $item = new $itemtype();
            $item->getFromDB($items_id);

            if ($item->getField("name")) {
               $comments .= "<strong>".__("Name").":</strong> ";
               $comments .= $item->getField("name");
            }

            if ($item->getField("entities_id")) {
               $comments .= "<strong>".__("Entity").":</strong> ";
               $comments .= Dropdown::getDropdownName("glpi_entities", $item->getField("entities_id"));
            }

            if ($item->getField("serial") != '') {
               $comments .= "<br><strong>".__("Serial number").":</strong> ";
               $comments .= $item->getField("serial");
            }

            if ($item->getField("otherserial") != '') {
               $comments .= "<br><strong>".__("Inventory number").":</strong> ";
               $comments .= $item->getField("otherserial");
            }
            if ($item->getField("locations_id")) {
               $comments .= "<br><strong>".__("Location").":</strong> ";
               $comments .= Dropdown::getDropdownName('glpi_locations', $item->getField("locations_id"));
            }

            if ($item->getField("users_id")) {
               $comments .= "<br><strong>".__("User").":</strong> ";
               $comments .= Dropdown::getDropdownName('glpi_users', $item->getField("users_id"));
            }
            break;
      }

      return ($comments);
   }


   public function getReceptionItemName($items_id, $itemtype) {
      global $CFG_GLPI;

      if ($items_id == 0) {
         return (__("No associated item", "order"));
      } else {
         switch ($itemtype) {
            case 'ConsumableItem' :
            case 'CartridgeItem' :
               $table = $itemtype::getTable();
               if ($itemtype == 'ConsumableItem') {
                  $item = new Consumable();
               } else {
                  $item = new Cartridge();
               }
               $item->getFromDB($items_id);
               $item_type = new $itemtype();
               $item_type->getFromDB($item->fields[getForeignKeyFieldForTable($table)]);
               return $item_type->getLink(['comments' => 1]);
            default :
               $item = new $itemtype();
               $item->getFromDB($items_id);
               return $item->getLink(['comments' => 1]);
         }
      }
   }


   /*public function dropdownLinkActions($itemtype, $plugin_order_references_id,
                                       $plugin_order_orders_id, $entities_id) {
      global $CFG_GLPI;

      $rand       = mt_rand();
      $reception  = new PluginOrderReception();
      $actions[0] = Dropdown::EMPTY_VALUE;
      if ($reception->checkItemStatus($plugin_order_orders_id, $plugin_order_references_id,
                                      PluginOrderOrder::ORDER_DEVICE_DELIVRED)) {
         if (!in_array($itemtype, self::getTypesThanCannotBeGenerared())) {
            $actions['generation'] = __("Generate item", "order");
         }
         if ($itemtype::canView()) {
            $actions['createLink'] = __("Link to an existing item", "order");
            $actions['deleteLink'] = __("Delete item link", "order");
         }
      }
      $rand   = Dropdown::showFromArray('generationActions', $actions);
      Ajax::updateItemOnSelectEvent("dropdown_generationActions$rand", "show_generationActions$rand",
                                  Plugin::getWebDir('order')."/ajax/linkactions.php",
                                  ['action'                     => '__VALUE__',
                                   'itemtype'                   => $itemtype,
                                   'plugin_order_references_id' => $plugin_order_references_id,
                                   'plugin_order_orders_id'     => $plugin_order_orders_id,
                                   'entities_id'                => $entities_id]);
      echo "<span id='show_generationActions$rand'>&nbsp;</span>";
   }*/

   public function itemAlreadyLinkedToAnOrder($itemtype, $items_id, $plugin_order_orders_id,
                                              $detailID = 0) {
      global $DB;
      if (!in_array($itemtype, self::getTypesThanCannotBeGenerared())) {
         $query = "SELECT COUNT(*) AS cpt
                   FROM `glpi_plugin_order_orders_items`
                   WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'
                   AND `items_id` = '$items_id'
                   AND `itemtype` = '$itemtype'";

         $result = $DB->query($query);

         if ($DB->result($result, 0, "cpt") > 0) {
            return true;
         } else {
            return false;
         }

      } else {
         $detail = new PluginOrderOrder_Item();
         $detail->getFromDB($detailID);

         if (!$detail->fields['items_id']) {
            return false;
         } else {
            return true;
         }
      }
   }


   public function isItemLinkedToOrder($itemtype, $items_id) {
      global $DB;

      $query = "SELECT `id`
                FROM `glpi_plugin_order_orders_items`
                WHERE `itemtype` = '$itemtype'
                AND `items_id` = '$items_id'";
      $result = $DB->query($query);

      if ($DB->numrows($result)) {
         return ($DB->result($result, 0, 'id'));
      } else {
         return 0;
      }
   }


   public function generateInfoComRelatedToOrder($entity, $detailID, $itemtype, $items_id, $templateID = 0) {
      global $CFG_GLPI;

      //Do not try to generate infocoms if itemtype doesn't support it (ie contracts...)
      if (in_array($itemtype, $CFG_GLPI["infocom_types"])) {

         // Retrieve configuration for generate assets feature
         $config = PluginOrderConfig::getConfig();

         $fields = [];

         //Create empty infocom, in order to forward entities_id and is_recursive
         $ic = new Infocom();
         $infocomID = !$ic->getFromDBforDevice($itemtype, $items_id) ? false : $ic->fields["id"];

         $detail = new PluginOrderOrder_Item();
         $detail->getFromDB($detailID);

         $order  = new PluginOrderOrder();
         $order->getFromDB($detail->fields["plugin_order_orders_id"]);

         $order_supplier = new PluginOrderOrder_Supplier();
         $order_supplier->getFromDBByOrder($detail->fields["plugin_order_orders_id"]);

         if ($templateID) {
            if ($ic->getFromDBforDevice($itemtype, $templateID)) {
               $fields = $ic->fields;
               unset ($fields["id"]);
               if (isset ($fields["immo_number"])) {
                  $fields["immo_number"] = autoName($fields["immo_number"], "immo_number", 1,
                                                    'Infocom', $entity);
               }

               if (empty ($fields['buy_date'])) {
                  unset ($fields['buy_date']);
               }
            }
         }

         $fields["entities_id"]     = $entity;
         $fields["itemtype"]        = $itemtype;
         $fields["items_id"]        = $items_id;
         $fields["order_number"]    = $order->fields["num_order"];
         $fields["delivery_number"] = $detail->fields["delivery_number"];
         $fields["budgets_id"]      = $order->fields["budgets_id"];
         $fields["suppliers_id"]    = $order->fields["suppliers_id"];
         $fields["value"]           = $detail->fields["price_discounted"];
         $fields["order_date"]      = $order->fields["order_date"];
         $fields["buy_date"]        = $order->fields["order_date"];

         if (!is_null($detail->fields["delivery_date"])) {
            $fields["delivery_date"] = $detail->fields["delivery_date"];
         }

         // Get bill data
         if ($config->canAddBillDetails()) {
            $bill = new PluginOrderBill();
            if ($bill->getFromDB($detail->fields["plugin_order_bills_id"])) {
               $fields['bill']          = $bill->fields['number'];
               $fields['warranty_date'] = $bill->fields['billdate'];
            }
         }

         foreach (['warranty_date', 'buy_date', 'inventory_date'] as $date) {
            if (!isset($fields[$date])) {
               $fields[$date] = 'NULL';
            }
         }

         $fields['_no_warning'] = true;

         if ($infocomID) {
            $fields['id'] = $infocomID;
            $ic->update($fields);
         } else {
            $ic->add($fields);
         }
      }
   }


   public function removeInfoComRelatedToOrder($itemtype, $items_id) {
      $infocom = new InfoCom();
      $infocom->getFromDBforDevice($itemtype, $items_id);
      $infocom->update([
         "id"              => $infocom->fields["id"],
         "order_number"    => "",
         "delivery_number" => "",
         "budgets_id"      => 0,
         "suppliers_id"    => 0,
         "bill"            => "",
         "value"           => 0,
         "order_date"      => null,
         "delivery_date"   => null,
      ]);
   }


   public function createLinkWithItem($detailID = 0, $items_id = 0, $itemtype = 0,
                                      $plugin_order_orders_id = 0, $entity = 0, $templateID = 0,
                                      $history = true, $check_link = true) {
      global $DB;

      if (!$check_link
         || !$this->itemAlreadyLinkedToAnOrder($itemtype, $items_id, $plugin_order_orders_id,
                                               $detailID)) {
         $detail     = new PluginOrderOrder_Item();
         $restricted = ['ConsumableItem', 'CartridgeItem'];

         if ($itemtype == 'SoftwareLicense') {
            $detail->getFromDB($detailID);
            $query = "SELECT `id`
                      FROM `glpi_plugin_order_orders_items`
                      WHERE `plugin_order_orders_id` = '$plugin_order_orders_id'
                        AND `plugin_order_references_id` = '{$detail->fields["plugin_order_references_id"]}'
                        AND `price_taxfree` LIKE '{$detail->fields["price_taxfree"]}'
                        AND `discount` LIKE '{$detail->fields["discount"]}'
                        AND `states_id` = 1 ";
            $result = $DB->query($query);
            $nb     = $DB->numrows($result);

            if ($nb) {
               for ($i = 0; $i < $nb; $i++) {
                  $ID                = $DB->result($result, $i, 'id');
                  $input["id"]       = $ID;
                  $input["items_id"] = $items_id;
                  $detail->update($input);

                  $this->generateInfoComRelatedToOrder($entity, $ID, $itemtype, $items_id, 0);

                  $lic              = new SoftwareLicense();
                  $lic->getFromDB($items_id);
                  $values["id"]     = $lic->fields["id"];
                  $values["number"] = $lic->fields["number"] + 1;
                  $lic->update($values);

               }

               if ($history) {
                  $order     = new PluginOrderOrder();
                  $new_value = __("Item linked to order", "order").' : '.$lic->getField("name");
                  $order->addHistory('PluginOrderOrder', '', $new_value, $plugin_order_orders_id);
               }
            }

         } else if (in_array($itemtype, $restricted)) {
            if ($itemtype == 'ConsumableItem') {
               $item = new Consumable();
               $type = 'Consumable';
               $pkey = 'consumableitems_id';
            } else if ($itemtype == 'CartridgeItem') {
               $item = new Cartridge();
               $type = 'Cartridge';
               $pkey = 'cartridgeitems_id';
            }
            $detail->getFromDB($detailID);
            $input[$pkey]     = $items_id;
            $input["date_in"] = $detail->fields["delivery_date"];
            $newID            = $item->add($input);

            $input["id"]       = $detailID;
            $input["items_id"] = $newID;
            $input["itemtype"] = $itemtype;
            if ($detail->update($input)) {
               $this->generateInfoComRelatedToOrder($entity, $detailID, $type, $newID, 0);
            }

         } else if ($itemtype == 'Contract') {
            $input = [
               "id"       => $detailID,
               "items_id" => $items_id,
               "itemtype" => $itemtype,
            ];
            if ($detail->update($input)) {
               $detail->getFromDB($detailID);
               $item = new Contract();

               if ($item->update(['id'   => $items_id,
                                  'cost' => $detail->fields["price_discounted"]])) {
                  $order = new PluginOrderOrder();
                  $order->getFromDB($plugin_order_orders_id);
                  if (!countElementsInTable(
                     'glpi_contracts_suppliers',
                     ['contracts_id' => $items_id, 'suppliers_id' => $order->fields['suppliers_id']])) {

                     $contract_supplier = new Contract_Supplier();
                     $contract_supplier->add([
                        'contracts_id' => $items_id,
                        'suppliers_id'  => $order->fields['suppliers_id']
                     ]);
                  }
               }
            }
         } else {
            $input = [
               "id"       => $detailID,
               "items_id" => $items_id,
               "itemtype" => $itemtype,
            ];
            if ($detail->update($input)) {
               $this->generateInfoComRelatedToOrder($entity, $detailID, $itemtype, $items_id,
                     $templateID);

               if ($history) {
                  $order = new PluginOrderOrder();
                  $order->getFromDB($detail->fields["plugin_order_orders_id"]);

                  $item  = new $itemtype();
                  $item->getFromDB($items_id);

                  $new_value = __("Item linked to order", "order").' : '.$item->getField("name");
                  $order->addHistory('PluginOrderOrder', '', $new_value, $order->fields["id"]);
               }
            }
         }
         if ($history) {
            $order = new PluginOrderOrder();
            $order->getFromDB($detail->fields["plugin_order_orders_id"]);
            $new_value = __("Item linked to order", "order").' : '.$order->fields["name"];
            $order->addHistory($itemtype, '', $new_value, $items_id);
         }

         Session::addMessageAfterRedirect(__("Item linked to order", "order"), true);
      } else {
         Session::addMessageAfterRedirect(__("Item already linked to another one", "order"), true, ERROR);
      }

   }


   public function deleteLinkWithItem($detailID, $itemtype, $plugin_order_orders_id) {
      global $DB;

      if ($itemtype == 'SoftWareLicense') {
         $detail  = new PluginOrderOrder_Item();
         $detail->getFromDB($detailID);
         $license = $detail->fields["items_id"];

         $this->removeInfoComRelatedToOrder($itemtype, $license);
         $result = $PluginOrderOrder_Item->queryRef($detail->fields["plugin_order_orders_id"],
                                                    $detail->fields["plugin_order_references_id"],
                                                    $detail->fields["price_taxfree"],
                                                    $detail->fields["discount"],
                                                    PluginOrderOrder::ORDER_DEVICE_DELIVRED);
         if ($nb = $DB->numrows($result)) {
            for ($i = 0; $i < $nb; $i++) {
               $ID = $DB->result($result, $i, 'id');
               $detail->update([
                  "id"       => $ID,
                  "items_id" => 0,
               ]);

               $lic = new SoftwareLicense();
               $lic->getFromDB($license);
               $values["id"]     = $lic->fields["id"];
               $values["number"] = $lic->fields["number"] - 1;
               $lic->update($values);
            }

            $order = new PluginOrderOrder();
            $order->getFromDB($detail->fields["plugin_order_orders_id"]);
            $new_value = __("Item unlink form order", "order").' : '.$order->fields["name"];
            $order->addHistory($itemtype, '', $new_value, $license);

            $item = new $itemtype();
            $item->getFromDB($license);
            $new_value = __("Item unlink form order", "order").' : '.$item->getField("name");
            $order->addHistory('PluginOrderOrder', '', $new_value, $order->fields["id"]);
         }
      } else {
         $order = new PluginOrderOrder();
         $order->getFromDB($plugin_order_orders_id);

         $detail = new PluginOrderOrder_Item();
         $detail->getFromDB($detailID);
         $items_id = $detail->fields["items_id"];

         $this->removeInfoComRelatedToOrder($itemtype, $items_id);

         if ($items_id != 0) {
            $input = $detail->fields;
            $input["items_id"] = 0;
            $detail->update($input);
         } else {
            Session::addMessageAfterRedirect(__("One or several selected rows haven't linked items", "order"), true, ERROR);
         }

         $new_value = __("Item unlink form order", "order").' : '.$order->fields["name"];
         $order->addHistory($itemtype, '', $new_value, $items_id);

         $item = new $itemtype();
         $item->getFromDB($items_id);
         $new_value = __("Item unlink form order", "order").' : '.$item->getField("name");
         $order->addHistory('PluginOrderOrder', '', $new_value, $order->fields["id"]);
      }
   }


   public function generateNewItem($params) {
      $newIDs = [];

      // Retrieve plugin configuration
      $config    = new PluginOrderConfig();
      $reference = new PluginOrderReference();

      foreach ($params["id"] as $key => $values) {
         $add_item = $values;
         if (array_key_exists('add_items', $params)
             && array_key_exists($values['id'], $params['add_items'])) {
            $add_item = array_merge($params['add_items'][$values['id']], $add_item);
         }

         //retrieve plugin_order_references_id from param if needed
         if (!isset($add_item["plugin_order_references_id"])) {
            $add_item["plugin_order_references_id"] = $params['plugin_order_references_id'];
         }

         //If itemtype cannot be generated, go to the new occurence
         if (in_array($add_item['itemtype'], self::getTypesThanCannotBeGenerared())) {
            continue;
         }

         $entity = $values["entities_id"];
         //------------- Template management -----------------------//
         //Look for a template in the entity
         $templateID = $reference->checkIfTemplateExistsInEntity($values["id"], $add_item["itemtype"],
                                                                 $entity);

         $item  = new $add_item["itemtype"]();
         if ($add_item['itemtype']) {
            $order = new PluginOrderOrder();
         }
         $order->getFromDB($params["plugin_order_orders_id"]);
         $reference->getFromDB($add_item["plugin_order_references_id"]);

         if ($templateID) {
            $item->getFromDB($templateID);
            unset($item->fields["is_template"]);
            unset($item->fields["date_mod"]);

            $input  = [];
            $fields = [];
            foreach ($item->fields as $key => $value) {
               if ($value != ''
                   && (!isset($fields[$key])
                       || $fields[$key] == ''
                       || $fields[$key] == 0)) {
                  $input[$key] = $value;
               }
            }

            if (isset($values["states_id"]) && $values["states_id"] != 0) {
               $input['states_id'] = $values['states_id'];
            } else {
               if ($config->getGeneratedAssetState()) {
                  $input["states_id"] = $config->getGeneratedAssetState();
               }
            }
            $input['groups_id'] = $values['groups_id'];
            if (isset($values["locations_id"]) && $values["locations_id"] != 0) {
               $input['locations_id'] = $values['locations_id'];
            } else {
               // Get bill data
               if ($config->canAddLocation()) {
                  $input['locations_id'] = $order->fields['locations_id'];
               }
            }

            $input["entities_id"] = $entity;
            $input["serial"]      = $values["serial"];

            if (isset($item->fields['name']) && $item->fields['name']) {
               $input["name"] = autoName($item->fields["name"], "name", $templateID,
                                $add_item["itemtype"], $entity);
            } else {
               $input["name"] = $values["name"];
            }

            if ($item->getField('otherserial') != NOT_AVAILABLE) {
               if ($item->fields['otherserial']) {
                  $input["otherserial"] = autoName($item->fields["otherserial"], "otherserial",
                                          $templateID, $add_item["itemtype"], $entity);
               } else {
                  $input["otherserial"] = $values["otherserial"];
               }
            }

         } else if ($add_item["itemtype"] == 'Contract') {
            $input["name"]             = $values["name"];
            $input["entities_id"]      = $entity;
            $input['contracttypes_id'] = $reference->fields['types_id'];

         } else {
            if (isset($values["states_id"]) && $values["states_id"] != 0) {
               $input['states_id']     = $values['states_id'];
            } else {
               if ($config->getGeneratedAssetState()) {
                  $input["states_id"]  = $config->getGeneratedAssetState();
               } else {
                  $input["states_id"]  = 0;
               }
            }
            $input['groups_id']        = $values['groups_id'];
            if (isset($values["locations_id"]) && $values["locations_id"] != 0) {
               $input['locations_id'] = $values['locations_id'];
            } else {
               // Get bill data
               if ($config->canAddLocation()) {
                  $input['locations_id'] = $order->fields['locations_id'];
               }
            }

            $input["entities_id"]      = $entity;
            $input["serial"]           = $values["serial"];
            $input["otherserial"]      = $values["otherserial"];
            $input["name"]             = $values["name"];
         }

         if (!array_key_exists('manufacturers_id', $input) || $input["manufacturers_id"] == 0) {
            $input["manufacturers_id"] = $reference->fields["manufacturers_id"];
         }
         $typefield = getForeignKeyFieldForTable(getTableForItemType($add_item["itemtype"]."Type"));
         if (!array_key_exists($typefield, $input) || $input[$typefield] == 0) {
            $input[$typefield] = $reference->fields["types_id"];
         }
         $modelfield = getForeignKeyFieldForTable(getTableForItemType($add_item["itemtype"]."Model"));
         if (!array_key_exists($modelfield, $input) || $input[$modelfield] == 0) {
            $input[$modelfield] = $reference->fields["models_id"];
         }

         $input = Toolbox::addslashes_deep($input);
         $newID = $item->add($input);
         $newIDs[$values["id"]] = $newID;

         // Attach new ticket if option is on
         if (isset($params['generate_ticket'])) {
            $tkt = new TicketTemplate();
            if ($tkt->getFromDB($params['generate_ticket']['tickettemplates_id'])) {
               $input = [];
               $input = Ticket::getDefaultValues($entity);
               $ttp        = new TicketTemplatePredefinedField();
               $predefined = $ttp->getPredefinedFields($params['generate_ticket']['tickettemplates_id'], true);
               if (count($predefined)) {
                  foreach ($predefined as $predeffield => $predefvalue) {
                     $input[$predeffield] = $predefvalue;
                  }
               }

               $input['entities_id']         = $entity;
               $input['_users_id_requester'] = empty($order->fields['users_id']) ? Session::getLoginUserID() : $order->fields['users_id'];
               $input['items_id']            = $newID;
               $input['itemtype']            = $add_item["itemtype"];

               $ticket = new Ticket();
               $ticketID = $ticket->add($input);
            }
         }

         //-------------- End template management ---------------------------------//
         $result = $this->createLinkWithItem($values["id"], $newID, $add_item["itemtype"],
                                             $params["plugin_order_orders_id"], $entity, $templateID,
                                             false, false);

         //Add item's history
         $new_value = __("Item generated by using order", "order").' : '.$order->fields["name"];
         $order->addHistory($add_item["itemtype"], '', $new_value, $newID);

         //Add order's history
         $new_value  = __("Item generated by using order", "order").' : ';
         $new_value .= $item->getTypeName()." -> ".$item->getField("name");
         $order->addHistory('PluginOrderOrder', '', $new_value, $params["plugin_order_orders_id"]);

         //Copy order documents if needed
         self::copyDocuments($add_item['itemtype'], $newID, $params["plugin_order_orders_id"], $entity);

         Session::addMessageAfterRedirect(__("Item successfully selected", "order"), true);
      }

      return $newIDs;
   }


   public static function countForOrder(PluginOrderOrder $item) {
      return countElementsInTable(
         'glpi_plugin_order_orders_items',
         [
            'plugin_order_orders_id' => $item->getID(),
            'states_id' => PluginOrderOrder::ORDER_DEVICE_DELIVRED,
         ]
      );
   }


   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->getType() == 'PluginOrderOrder'
         && $item->checkIfDetailExists($item->getID(), true)
         && Session::haveRight('plugin_order_order', READ)) {
         return self::createTabEntry(_n("Associated item", "Associated items", 2),
                                     self::countForOrder($item));
      }
   }


   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'PluginOrderOrder') {
         $link = new self();
         $link->showOrderLink($item->getID());
      }
      return true;
   }


   /**
    * Copy order documents into the newly generated item
    * @since 1.5.3
    * @param unknown_type $itemtype
    * @param unknown_type $items_id
    * @param unknown_type $orders_id
    * @param unknown_type $entity
    */
   public static function copyDocuments($itemtype, $items_id, $orders_id, $entity) {
      global $CFG_GLPI;

      $config = PluginOrderConfig::getConfig();

      if ($config->canCopyDocuments() && in_array($itemtype, $CFG_GLPI["document_types"])) {
         $document = new Document();
         $docitem  = new Document_Item();

         $item = new $itemtype();
         $item->getFromDB($items_id);
         $is_recursive = 0;

         foreach (getAllDataFromTable('glpi_documents_items',
                                       ['itemtype' => 'PluginOrderOrder',
                                        'items_id' => $orders_id]) as $doc) {

            //Create a new document
            $document->getFromDB($doc['documents_id']);
            if (($document->getEntityID() != $entity && !$document->fields['is_recursive'])
               || !in_array($entity, getSonsOf('glpi_entities', $document->getEntityID()))) {
               $found_docs = getAllDataFromTable(
                  'glpi_documents',
                  [
                     'entities_id' => $entity,
                     'sha1sum' => $document->fields['sha1sum'],
                  ]
               );
               if (empty($found_docs)) {
                  $tmpdoc                = $document->fields;
                  $tmpdoc['entities_id'] = $entity;
                  unset($tmpdoc['id']);
                  $documents_id = $document->add($tmpdoc);
                  $is_recursive = $document->fields['is_recursive'];
               } else {
                  $found_doc = array_pop($found_docs);
                  $documents_id = $found_doc['id'];
                  $is_recursive = $found_doc['is_recursive'];
               }
            } else {
               $documents_id = $document->getID();
               $is_recursive = $document->fields['is_recursive'];

            }
            //Link the document to the newly generated item
            $docitem->add([
               'documents_id' => $documents_id,
               'entities_id'  => $entity,
               'items_id'     => $items_id,
               'itemtype'     => $itemtype,
               'is_recursive' => $is_recursive,
            ]);
         }
      }
   }


}
