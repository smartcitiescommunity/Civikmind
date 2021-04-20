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

$reference = new PluginOrderReference();

if (isset($_POST["action"])) {
   switch ($_POST["action"]) {
      case "generation":
         echo Html::hidden('plugin_order_references_id',
                           ['value' => $_POST["plugin_order_references_id"]]);
         echo "&nbsp;";
         echo Html::submit(_sx('button', 'Post'), ['name' => 'generation']);
         break;

      case "createLink":

         echo Html::hidden('itemtype', ['value' => $_POST["itemtype"]]);
         echo Html::hidden('plugin_order_orders_id',
                           ['value' => $_POST["plugin_order_orders_id"]]);

         $reference->getFromDB($_POST["plugin_order_references_id"]);
         $reference->dropdownAllItemsByType("items_id", $_POST["itemtype"],
                                            $_SESSION["glpiactiveentities"],
                                            $reference->fields["types_id"],
                                            $reference->fields["models_id"]);
         echo "&nbsp;";
         echo Html::submit(_sx('button', 'Post'), ['name' => 'createLinkWithItem']);

         break;

      case "deleteLink":
         echo "&nbsp;";
         echo Html::submit(_sx('button', 'Post'), ['name' => 'deleteLinkWithItem']);
         break;

      case "show_location_by_entity":
         Location::dropdown(['name'   => "id[".$_POST['id']."][locations_id]",
                           'entity' => $_POST['entities']
                          ]);
         break;

      case "show_group_by_entity":
         Group::dropdown(['name'      => "id[".$_POST['id']."][groups_id]",
                          'entity'    => $_POST['entities'],
                          'condition' => ['is_assign' => 1],
                         ]);
         break;

      case "show_state_by_entity":
         $condition = PluginOrderLink::getCondition($_POST["itemtype"]);
         State::dropdown(['name'      => "id[".$_POST['id']."][states_id]",
                          'entity'    => $_POST['entities'],
                          'condition' => $condition
                         ]);
         break;
   }
}
