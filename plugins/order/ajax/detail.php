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

header("Content-Type: text/html; charset=UTF-8");

Html::header_nocache();

if (!defined('GLPI_ROOT')) {
   die("Can not acces directly to this file");
}

Session::checkCentralAccess();

$rand         = $_POST["rand"];
$paramsaction = [
   'plugin_order_references_id' => '__VALUE__',
   'entity_restrict'            => $_POST["entity_restrict"],
   'suppliers_id'               => $_POST["suppliers_id"],
   'itemtype'                   => $_POST['itemtype'],
];
$fields = [
   "quantity",
   "priceht",
   "pricediscounted",
   "taxe",
   "validate",
];

$order_url = Plugin::getWebDir('order');

foreach ($fields as $field) {
   $paramsaction['update'] = $field;
   Ajax::updateItem("show_$field",
                    "$order_url/ajax/referencedetail.php",
                    $paramsaction, "dropdown_reference$rand");
   Ajax::updateItemOnSelectEvent("dropdown_reference$rand",
                                 "show_$field",
                                 "$order_url/ajax/referencedetail.php",
                                 $paramsaction);
}
