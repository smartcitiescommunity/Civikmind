<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

//Options for GLPI 0.71 and newer : need slave db to access the report
$USEDBREPLICATE         = 1;
$DBCONNECTION_REQUIRED  = 0;

include ("../../../../inc/includes.php");

//"Rapport listant les ressources sans utilisateurs";
//"Report listing resource without user";
// Instantiate Report with Name
$titre = $LANG['plugin_resources']['resourcewithoutuser'];
$report = new PluginReportsAutoReport($titre);

// Columns title (optional)
$report->setColumns( [new PluginReportsColumnLink('resource_id', __('Surname'), 'PluginResourcesResource',
                                                   ['sorton' => 'resource_name']),
                           new PluginReportsColumn('firstname', __('First name'),
                                                   ['sorton' => 'firstname']),
                           new PluginReportsColumn('rank', PluginResourcesRank::getTypeName(1),
                                                   ['sorton' => 'rank']),
                           new PluginReportsColumn('situation', PluginResourcesResourceSituation::getTypeName(1),
                                                   ['sorton' => 'situation']),
                           new PluginReportsColumn('state', PluginResourcesResourceState::getTypeName(1),
                                                   ['sorton' => 'state']),
                           new PluginReportsColumnDate('date_begin', __('Arrival date', 'resources'),
                                                   ['sorton' => 'date_begin']),
                           new PluginReportsColumnDate('date_end', __('Departure date', 'resources'),
                                                   ['sorton' => 'date_end'])]);

// SQL statement
$dbu       = new DbUtils();
$condition = $dbu->getEntitiesRestrictRequest(' AND ', "glpi_plugin_resources_resources", '', '', false);
$date      = date("Y-m-d");

//display only resource without user linked
$query = "SELECT `glpi_plugin_resources_resources`.`id` as resource_id,
                 `glpi_plugin_resources_resources`.`name` as resource_name,
                 `glpi_plugin_resources_resources`.`firstname`,
                 `glpi_plugin_resources_ranks`.`name` AS rank,
                 `glpi_plugin_resources_resourcesituations`.`name` AS situation,
                 `glpi_plugin_resources_resourcestates`.`name` AS state,
                 `glpi_plugin_resources_resources`.`date_begin`,
                 `glpi_plugin_resources_resources`.`date_end`
          FROM `glpi_plugin_resources_resources`
          LEFT JOIN `glpi_plugin_resources_ranks`
               ON (`glpi_plugin_resources_resources`.`plugin_resources_ranks_id` = `glpi_plugin_resources_ranks`.`id`)
          LEFT JOIN `glpi_plugin_resources_resourcesituations`
               ON (`glpi_plugin_resources_resources`.`plugin_resources_resourcesituations_id` = `glpi_plugin_resources_resourcesituations`.`id`)
          LEFT JOIN `glpi_plugin_resources_resourcestates`
               ON (`glpi_plugin_resources_resources`.`plugin_resources_resourcestates_id` = `glpi_plugin_resources_resourcestates`.`id`)
          WHERE (`glpi_plugin_resources_resources`.`is_leaving` = 0
             AND `glpi_plugin_resources_resources`.`id` NOT IN (SELECT `plugin_resources_resources_id`
               FROM `glpi_plugin_resources_resources_items`
               WHERE `glpi_plugin_resources_resources_items`.`itemtype`= 'User')
             AND `glpi_plugin_resources_resources`.`is_deleted` = 0
             AND `glpi_plugin_resources_resources`.`is_template` = 0
             ".$condition.")
             AND ((`glpi_plugin_resources_resources`.`date_end` IS NULL )
                  OR (`glpi_plugin_resources_resources`.`date_end` > '".$date."' ))
             AND ((`glpi_plugin_resources_resources`.`date_begin` IS NULL)
                  OR ( `glpi_plugin_resources_resources`.`date_begin` < '".$date."'))".
          $report->getOrderBy('resource_id');


$report->setSqlRequest($query);
$report->execute();
