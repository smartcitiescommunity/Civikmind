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

Html::header(
   __("Orders management", "order"),
   $_SERVER['PHP_SELF'],
   "management",
   "PluginOrderMenu"
);

$PluginOrderOrder     = new PluginOrderOrder();
$PluginOrderReference = new PluginOrderReference();
$PluginOrderBill      = new PluginOrderBill();

//If there's only one possibility, do not display menu!
if (PluginOrderOrder::canView() && !PluginOrderReference::canView() && !PluginOrderBill::canView()) {
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginOrderOrder'));

} else if (!PluginOrderOrder::canView() && PluginOrderReference::canView() && !PluginOrderBill::canView()) {
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginOrderReference'));

} else if (!PluginOrderOrder::canView() && !PluginOrderReference::canView() && PluginOrderBill::canView()) {
   Html::redirect(Toolbox::getItemTypeSearchURL('PluginOrderBill'));
}

if (PluginOrderOrder::canView() || PluginOrderReference::canView()) {
   echo "<div class='center'>";
   echo "<table class='tab_cadre'>";
   echo "<tr><th colspan='2'>" . __("Orders management", "order") . "</th></tr>";

   if (PluginOrderOrder::canView()) {
      echo "<tr class='tab_bg_1 center'>";
      echo "<td><img src='../pics/order-icon.png'></td>";
      echo "<td><a href='".Toolbox::getItemTypeSearchURL('PluginOrderOrder')."'>" .
         __("Orders", "order") . "</a></td></tr>";
   }

   if (PluginOrderReference::canView()) {
      echo "<tr class='tab_bg_1 center'>";
      echo "<td><img src='../pics/reference-icon.png'></td>";
      echo "<td><a href='".Toolbox::getItemTypeSearchURL('PluginOrderReference')."'>" .
         __("Products references", "order") . "</a></td></tr>";
   }

   if (PluginOrderBill::canView()) {
      echo "<tr class='tab_bg_1 center'>";
      echo "<td><img src='../pics/bill-icon.png'></td>";
      echo "<td><a href='".Toolbox::getItemTypeSearchURL('PluginOrderBill')."'>" .
         __("Bills", "order") . "</a></td></tr>";
   }

   echo "</table></div>";
} else {
   echo "<div class='center'><br><br><img src=\"" . $CFG_GLPI["root_doc"] .
         "/pics/warning.png\" alt=\"warning\"><br><br>";
   echo "<b>" . __("Access denied") . "</b></div>";
}

Html::footer();
