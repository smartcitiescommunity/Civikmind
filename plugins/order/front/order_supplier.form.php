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

include ("../../../inc/includes.php");

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}
if (!isset($_GET["plugin_order_orders_id"])) {
   $_GET["plugin_order_orders_id"] = "";
}

$supplier = new PluginOrderOrder_Supplier();
$order    = new PluginOrderOrder();

if (isset($_POST["add"])) {
   if (PluginOrderOrder_Supplier::canCreate()) {
      if (isset($_POST["plugin_order_orders_id"]) && $_POST["plugin_order_orders_id"] > 0) {
         if ($supplier->add($_POST)) {
            $new_value = __("Add"). " ";
            if ($_POST["num_quote"]) {
               $new_value .= __("Quote number", "order")." ".$_POST["num_quote"];
            }
            if ($_POST["num_order"]) {
               $new_value .= " - ".__("Order number")." : ".$_POST["num_order"];
            }
            $order->addHistory('PluginOrderOrder', "", $new_value, $_POST["plugin_order_orders_id"]);
         }
      }
   }
   Html::back();

} else if (isset($_POST["delete"])) {
   if (PluginOrderOrder_Supplier::canCreate()) {
      foreach ($_POST["check"] as $ID => $value) {
         if ($supplier->delete(["id" => $ID], 0, 0)) {
            $new_value = __("Delete", "order")." ".__("Supplier Detail", "order")." : ".$ID;
            $order->addHistory('PluginOrderOrder', "", $new_value, $_POST["plugin_order_orders_id"]);
         }
      }
   }
   Html::back();

} else if (isset($_POST["update"])) {
   if (PluginOrderOrder_Supplier::canCreate()) {
      $supplier->update($_POST);
   }
   Html::back();

} else {
   Html::header(__("Orders management", "order"), '', "plugins", "order", "order");
   $supplier->showForm($_GET["id"],
                       ['plugin_order_orders_id' => $_GET["plugin_order_orders_id"]]);
   Html::footer();
}
