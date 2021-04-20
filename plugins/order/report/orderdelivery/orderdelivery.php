<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
 */

/*
 * ----------------------------------------------------------------------
 * Original Author of file: Nelly Lasson
 *
 * Purpose of file:
 *       Generate location report
 *       Illustrate use of simpleReport
 * ----------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE = 1;
$DBCONNECTION_REQUIRED = 0; // Really a big SQL request

include ("../../../../inc/includes.php");

$report = new PluginReportsAutoReport(__("orderdelivery_report_title", "order"));
new PluginReportsDateIntervalCriteria($report, 'order_date', __("Date of order", "order"));
new PluginReportsDateIntervalCriteria($report, 'deliverydate', __("Delivery date"));
new PluginReportsLocationCriteria($report, 'locations_id', __("Delivery location", "order"));
new PluginReportsSupplierCriteria($report, 'suppliers_id', __("Supplier"));
new PluginReportsDropdownCriteria($report, 'plugin_order_orderstates_id', 'PluginOrderOrderState',
                                  __("Status"));
$report->displayCriteriasForm();

if ($report->criteriasValidated()) {
   $report->setSubNameAuto();

   $report->setColumns([
      new PluginReportsColumnLink('entities_id',
                                  __("Entity"), 'Entity'),
      new PluginReportsColumnLink('id', __("Name"),
                                  'PluginOrderOrder',
                                  [
                                    'with_comment'  => true,
                                    'with_navigate' => true
                                  ]),
      new PluginReportsColumn('num_order', __("Order number", "order")),
      new PluginReportsColumnLink('suppliers_id',
                                  __("Supplier"), 'Supplier'),
      new PluginReportsColumnLink('plugin_order_orderstates_id',
                                  __("Status"),
                                  'PluginOrderOrderState',
                                  ['with_comment' => true]),
      new PluginReportsColumnDateTime('order_date', __("Date of order", "order")),
      new PluginReportsColumnDateTime('duedate', __("Estimated due date", "order")),
      new PluginReportsColumnDateTime('deliverydate', __("Delivery date")),
      new PluginReportsColumnLink('locations_id',
                                  __("Delivery location", "order"), 'Location',
                                  ['with_comment' => true])
   ]);

   //TODO : ne pas chercher dans la poublelles
   $query = "SELECT * FROM `glpi_plugin_order_orders`";
   $query .= getEntitiesRestrictRequest(" WHERE", "glpi_plugin_order_orders");
   $query .= $report->addSqlCriteriasRestriction();
   $query .= " AND `is_deleted`='0' AND `is_template`='0' ";
   $query .= "GROUP BY `entities_id`, `plugin_order_orderstates_id`, `num_order`, `order_date`";
   $report->setGroupBy("entities_id", "plugin_order_orderstates_id", "num_order", "order_date");
   $report->setSqlRequest($query);
   $report->execute();
}
