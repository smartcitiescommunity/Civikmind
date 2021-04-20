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

$reception  = new PluginOrderReception();
$order_item = new PluginOrderOrder_Item();

if (isset ($_POST["update"])) {

   if (PluginOrderReception::canCreate()) {
      $order_item->getFromDB($_POST["id"]);
      if ($order_item->fields["itemtype"] == 'SoftwareLicense') {
         $result = $order_item->queryRef($order_item->fields["plugin_order_orders_id"],
                                         $order_item->fields["plugin_order_references_id"],
                                         $order_item->fields["price_taxfree"],
                                         $order_item->fields["discount"],
                                         PluginOrderOrder::ORDER_DEVICE_DELIVRED);
         $nb = $DB->numrows($result);

         if ($nb) {
            for ($i = 0; $i < $nb; $i++) {
               $ID = $DB->result($result, $i, 'id');
               $reception->update([
                  "id"                             => $ID,
                  "delivery_date"                  => $_POST["delivery_date"],
                  "delivery_number"                => $_POST["delivery_number"],
                  "plugin_order_deliverystates_id" => $_POST["plugin_order_deliverystates_id"],
                  "delivery_comment"               => $_POST["delivery_comment"],
               ]);
            }
         }
      } else {
         $reception->update($_POST);
      }
   }
   $reception->updateReceptionStatus([
      'items' => [
         'PluginOrderReception' => [
            $_POST['id'] => 'on'
         ]
      ]]
   );
   Html::redirect($_SERVER['HTTP_REFERER']);

} else if (isset ($_POST["delete"])) {
   $reception->deleteDelivery($_POST["id"]);
   $reception->updateReceptionStatus([
      'items' => [
         'PluginOrderReception' => [
            $_POST['id'] => 'on'
         ]
      ]
   ]);
   Html::redirect(Toolbox::getItemTypeFormURL('PluginOrderOrder')."?id=".$_POST["plugin_order_orders_id"]);

} else if (isset ($_POST["bulk_reception"])) {
   //Several new items are delivered
   $reception->updateBulkReceptionStatus($_POST);
   Html::redirect($_SERVER["HTTP_REFERER"]);

} else {
   Html::header(__("Orders management", "order"), '', "management", "PluginOrderMenu", "reception");
   $reception->showForm($_GET["id"]);
   Html::footer();
}
