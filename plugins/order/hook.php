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
 along with Order. If not, see <http://www.gnu.org/licenses/>.
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


/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_order_install() {
   foreach (glob(PLUGIN_ORDER_DIR . '/inc/*.php') as $file) {
      //Do not load datainjection files (not needed and avoid missing class error message)
      if (!preg_match('/injection.class.php/', $file)) {
         include_once ($file);
      }
   }

   echo "<center>";
   echo "<table class='tab_cadre_fixe'>";
   echo "<tr><th>".__("Plugin installation or upgrade", "order")."<th></tr>";

   echo "<tr class='tab_bg_1'>";
   echo "<td align='center'>";

   $migration = new Migration(PLUGIN_ORDER_VERSION);
   $classes = ['PluginOrderConfig', 'PluginOrderBillState', 'PluginOrderBillType',
               'PluginOrderOrderState', 'PluginOrderOrder','PluginOrderOrder_Item',
               'PluginOrderReference', 'PluginOrderDeliveryState',
               'PluginOrderNotificationTargetOrder',
               'PluginOrderOrder_Supplier', 'PluginOrderBill', 'PluginOrderOrderPayment',
               'PluginOrderOrderType', 'PluginOrderOther', 'PluginOrderOtherType',
               'PluginOrderPreference', 'PluginOrderProfile', 'PluginOrderReference_Supplier',
               'PluginOrderSurveySupplier', 'PluginOrderOrderTax', 'PluginOrderDocumentCategory',
               'PluginOrderReferenceFree', 'PluginOrderAccountSection',
               'PluginOrderAnalyticNature'];
   foreach ($classes as $class) {
      if ($plug = isPluginItemType($class)) {
         $plugname = strtolower($plug['plugin']);
         $dir = Plugin::getPhpDir($plugname)."/inc/";
         $item = strtolower($plug['class']);
         if (file_exists("$dir$item.class.php")) {
            include_once ("$dir$item.class.php");
            call_user_func([$class, 'install'], $migration);
         }
      }
   }

   echo "</td>";
   echo "</tr>";
   echo "</table>";
   echo "</center>";

   //Create directories for the plugin's files
   $directories = [PLUGIN_ORDER_TEMPLATE_DIR        => 'templates',
                   PLUGIN_ORDER_SIGNATURE_DIR       => 'signatures',
                   PLUGIN_ORDER_TEMPLATE_CUSTOM_DIR => 'generate',
                   PLUGIN_ORDER_TEMPLATE_LOGO_DIR   => 'logo'
                  ];
   foreach ($directories as $new_directory => $old_directory) {
      if (!is_dir($new_directory)) {
                  @mkdir($new_directory, 0755, true)
                     or die(sprintf(__('%1$s %2$s'), __("Can't create folder", 'order'),
                                    $new_directory));
         //Copy files from the old directories to the new ones
         foreach (glob(PLUGIN_ORDER_DIR."/$old_directory/*") as $file) {
            $new_file = str_replace(PLUGIN_ORDER_DIR."/$old_directory", $new_directory, $file);
            if (!file_exists($new_directory.$file)) {
               copy($file, $new_file)
                  or die (sprintf(__('Cannot copy file %1$s to %2$s', 'order'),
                           $file, $new_file));
            }
         }
      }
   }
   return true;
}


/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_order_uninstall() {
   foreach (glob(PLUGIN_ORDER_DIR.'/inc/*.php') as $file) {
      //Do not load datainjection files (not needed and avoid missing class error message)
      if (!preg_match('/injection.class.php/', $file)) {
         include_once ($file);
      }
   }

   $classes = ['PluginOrderConfig', 'PluginOrderBill', 'PluginOrderBillState',
               'PluginOrderBillType', 'PluginOrderOrderState', 'PluginOrderOrder',
               'PluginOrderOrder_Item', 'PluginOrderReference', 'PluginOrderDeliveryState',
               'PluginOrderNotificationTargetOrder',
               'PluginOrderOrder_Supplier', 'PluginOrderOrderPayment','PluginOrderOrderTax',
               'PluginOrderOrderType', 'PluginOrderOther', 'PluginOrderOtherType',
               'PluginOrderPreference', 'PluginOrderProfile', 'PluginOrderReference_Supplier',
               'PluginOrderSurveySupplier', 'PluginOrderDocumentCategory',
               'PluginOrderAccountSection', 'PluginOrderAnalyticNature'];
   foreach ($classes as $class) {
      call_user_func([$class, 'uninstall']);
   }

   return true;
}


/* define dropdown tables to be manage in GLPI : */
function plugin_order_getDropdown() {
   /* table => name */
   $plugin = new Plugin();
   if ($plugin->isActivated("order")) {
      return ['PluginOrderOrderTax'         => __("VAT", "order"),
              'PluginOrderOrderPayment'     => __("Payment conditions", "order"),
              'PluginOrderOrderType'        => __("Type"),
              'PluginOrderOrderState'       => __("Order status", "order"),
              'PluginOrderOtherType'        => __("Other type of item", "order"),
              'PluginOrderDeliveryState'    => __("Delivery status", "order"),
              'PluginOrderBillState'        => __("Bill status", "order"),
              'PluginOrderBillType'         => __("Bill type", "order"),
              'PluginOrderAnalyticNature'   => __("Analytic nature", "order"),
              'PluginOrderAccountSection'   => __("Account section", "order"),
              'PluginOrderDocumentCategory' => __("Orders", "order")];
   } else {
      return [];
   }
}


/* define dropdown relations */
function plugin_order_getDatabaseRelations() {
   $plugin = new Plugin();
   if ($plugin->isActivated("order")) {
      return [
         "glpi_plugin_order_orderpayments" => [
            "glpi_plugin_order_orders" => "plugin_order_orderpayments_id"
         ],
         "glpi_plugin_order_ordertaxes" => [
            "glpi_plugin_order_orders" => "plugin_order_ordertaxes_id"
         ],
         "glpi_plugin_order_ordertypes" => [
            "glpi_plugin_order_orders" => "plugin_order_ordertypes_id"
         ],
         "glpi_plugin_order_orderstates" => [
            "glpi_plugin_order_orders" => "plugin_order_orderstates_id"
         ],
         "glpi_plugin_order_accountsections" => [
            "glpi_plugin_order_accountsections" => "plugin_order_accountsections_id"
         ],
         "plugin_order_analyticnatures" => [
            "glpi_plugin_order_orders_items" => "plugin_order_analyticnatures_id"
         ],
         "glpi_plugin_order_deliverystates" => [
            "glpi_plugin_order_orders_items" => "plugin_order_deliverystates_id"
         ],
         "glpi_plugin_order_orders" => [
            "glpi_plugin_order_orders_items"     => "plugin_order_orders_id",
            "glpi_plugin_order_orders_suppliers" => "plugin_order_orders_id"
         ],
         "glpi_plugin_order_references" => [
            "glpi_plugin_order_orders_items"         => "plugin_order_references_id",
            "glpi_plugin_order_references_suppliers" => "plugin_order_references_id"
         ],
         "glpi_entities" => [
            "glpi_plugin_order_orders"    => "entities_id",
           "glpi_plugin_order_references" => "entities_id",
           "glpi_plugin_order_others"     => "entities_id",
           "glpi_plugin_order_bills"      => "entities_id"
         ],
         "glpi_budgets" => [
            "glpi_plugin_order_orders" => "budgets_id"
         ],
         "glpi_plugin_order_othertypes" => [
            "glpi_plugin_order_others" => "othertypes_id"
         ],
         "glpi_suppliers" => [
            "glpi_plugin_order_orders"               => "suppliers_id",
            "glpi_plugin_order_orders_suppliers"     => "suppliers_id",
            "glpi_plugin_order_references_suppliers" => "suppliers_id"
         ],
         "glpi_manufacturers" => [
            "glpi_plugin_order_references" => "manufacturers_id"
         ],
         "glpi_contacts" => [
            "glpi_plugin_order_orders" => "contacts_id"
         ],
         "glpi_locations" => [
            "glpi_plugin_order_orders" => "locations_id"
         ],
         "glpi_profiles" => [
            "glpi_plugin_order_profiles" => "profiles_id"
         ]
      ];

   } else {
      return [];
   }
}


////// SEARCH FUNCTIONS ///////(){

// Define search option for types of the plugins
function plugin_order_getAddSearchOptions($itemtype) {
   $plugin = new Plugin();

   $sopt = [];
   if ($plugin->isInstalled('order')
       && $plugin->isActivated('order')
       && Session::haveRight("plugin_order_order", READ)) {
      if (in_array($itemtype, PluginOrderOrder_Item::getClasses(true))) {
         $sopt[3160]['table']         = 'glpi_plugin_order_orders';
         $sopt[3160]['field']         = 'name';
         $sopt[3160]['linkfield']     = '';
         $sopt[3160]['name']          = __("Order name", "order");
         $sopt[3160]['forcegroupby']  = true;
         $sopt[3160]['datatype']      = 'itemlink';
         $sopt[3160]['itemlink_type'] = 'PluginOrderOrder';

         $sopt[3161]['table']         = 'glpi_plugin_order_orders';
         $sopt[3161]['field']         = 'num_order';
         $sopt[3161]['linkfield']     = '';
         $sopt[3161]['name']          = __("Order number", "order");
         $sopt[3161]['forcegroupby']  = true;
         $sopt[3161]['datatype']      = 'itemlink';
         $sopt[3161]['itemlink_type'] = 'PluginOrderOrder';
      }
   }
   return $sopt;
}


function plugin_order_addSelect($type, $ID, $num) {
   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   if ($table == "glpi_plugin_order_references"
       && $num != 0) {
      return "`$table`.`itemtype`, `$table`.`$field` AS `ITEM_$num`, ";
   } else {
      return "";
   }
}


function plugin_order_addLeftJoin($type, $ref_table, $new_table, $linkfield, &$already_link_tables) {
   $out = "";
   switch ($new_table) {
      case "glpi_plugin_order_orders": // From items
         $out = " LEFT JOIN `glpi_plugin_order_orders_items`
                     ON `$ref_table`.`id` = `glpi_plugin_order_orders_items`.`items_id`
                     AND `glpi_plugin_order_orders_items`.`itemtype` = '$type' ";
         $out .= " LEFT JOIN `glpi_plugin_order_orders`
                     ON `glpi_plugin_order_orders`.`id` = `glpi_plugin_order_orders_items`.`plugin_order_orders_id` ";
         break;
      case "glpi_budgets": // From order list
         $out = " LEFT JOIN `glpi_budgets`
                     ON `glpi_plugin_order_orders`.`budgets_id` = `glpi_budgets`.`id` ";
         break;
      case "glpi_contacts": // From order list
         $out = " LEFT JOIN `glpi_contacts`
                     ON `glpi_plugin_order_orders`.`contacts_id` = `glpi_contacts`.`id` ";
         break;
   }

   return $out;
}


/* display custom fields in the search */
function plugin_order_giveItem($type, $ID, $data, $num) {
   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];
   $reference = new PluginOrderReference();
   $itemnum   = $data['raw']["ITEM_".$num];
   $itemtype  = isset($data['raw']["ITEM_".$num."_itemtype"])
      ? $data['raw']["ITEM_".$num."_itemtype"]
      : '';

   switch ($table.'.'.$field) {
      /* display associated items with order */
      case "glpi_plugin_order_references.types_id" :
         if ($itemtype == 'PluginOrderOther') {
            $file = PLUGIN_ORDER_DIR."/inc/othertype.class.php";
         } else {
            $file = GLPI_ROOT."/inc/".strtolower($itemtype)."type.class.php";
         }
         if (file_exists($file)) {
            return Dropdown::getDropdownName(getTableForItemType($itemtype."Type"),
                                             $itemnum);
         } else {
            return " ";
         }
         break;
      case "glpi_plugin_order_references.models_id" :
         if (file_exists(GLPI_ROOT."/inc/".strtolower($itemtype)."model.class.php")) {
            return Dropdown::getDropdownName(getTableForItemType($itemtype."Model"),
                                             $itemnum);
         } else {
            return " ";
         }
         break;
      case "glpi_plugin_order_references.templates_id" :
         if (!$itemnum) {
            return " ";
         } else {
            return $reference->getTemplateName($itemtype, $itemnum);
         }
         break;
   }
   return "";
}


function plugin_order_displayConfigItem($type, $ID, $data, $num) {
   global $CFG_GLPI;

   $searchopt = &Search::getOptions($type);
   $table = $searchopt[$ID]["table"];
   $field = $searchopt[$ID]["field"];
   switch ($table.'.'.$field) {
      case "glpi_plugin_order_orders.is_late":
         $message = "";
         if ($data['raw']["ITEM_".$num]) {
            $config = PluginOrderConfig::getConfig();
            if ($config->getShouldBeDevileredColor() != '') {
               $message .= " style=\"background-color:".$config->getShouldBeDevileredColor().";\" ";
            }
         }
         return $message;

   }
}


/* hook done on purge item case */
function plugin_item_purge_order($item) {
   global $DB;
   $query = "UPDATE `glpi_plugin_order_orders_items`
             SET `items_id`='0'
             WHERE `itemtype`='".$item->getType()."'
               AND `items_id`='".$item->getField('id')."'";
   $DB->query($query);

   return true;
}


/**
 * Get itemtypes to migration from GLPI 0.72 to GLPI 0.78+
 */
function plugin_order_migratetypes($types) {
   $types[3150] = 'PluginOrderOrder';
   $types[3151] = 'PluginOrderReference';
   $types[3152] = 'PluginOrderReference_Supplier';
   $types[3153] = 'PluginOrderBudget';
   $types[3154] = 'PluginOrderReception';

   return $types;
}


function plugin_datainjection_populate_order() {
   global $INJECTABLE_TYPES;
   $INJECTABLE_TYPES['PluginOrderOrderInjection']     = 'order';
   $INJECTABLE_TYPES['PluginOrderReferenceInjection'] = 'order';
}


function plugin_order_AssignToTicket($types) {
   $types['PluginOrderOrder'] = __("Order", "order");
   return $types;
}
