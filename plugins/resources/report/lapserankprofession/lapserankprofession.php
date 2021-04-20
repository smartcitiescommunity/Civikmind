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
$USEDBREPLICATE        = 1;
$DBCONNECTION_REQUIRED = 1;

include ("../../../../inc/includes.php");

// Instantiate Report with Name
$titre = $LANG['plugin_resources']['lapserankprofession'];
$report = new PluginReportsAutoReport($titre);

// Columns title (optional)
$report->setColumns( [new PluginReportsColumnLink('rank_id', PluginResourcesRank::getTypeName(1), 'PluginResourcesRank',
                                                   ['sorton' => 'rank_name']),
                           new PluginReportsColumn('rank_code', PluginResourcesRank::getTypeName(1)." - ".__('Code', 'resources'),
                                                   ['sorton' => 'rank_code']),
                           new PluginReportsColumnDate('rank_begin_date', PluginResourcesRank::getTypeName(1)." - ".__('Begin date'),
                                                   ['sorton' => 'rank_begin_date']),
                           new PluginReportsColumnDate('rank_end_date', PluginResourcesRank::getTypeName(1)." - ".__('End date'),
                                                   ['sorton' => 'rank_end_date']),
                           new PluginReportsColumnLink('prof_id', PluginResourcesProfession::getTypeName(1), 'PluginResourcesProfession',
                                                   ['sorton' => 'prof_name']),
                           new PluginReportsColumn('prof_code', PluginResourcesProfession::getTypeName(1)." - ".__('Code', 'resources'),
                                                   ['sorton' => 'prof_code']),
                           new PluginReportsColumnDate('prof_begin_date', PluginResourcesProfession::getTypeName(1)." - ".__('Begin date'),
                                                   ['sorton' => 'prof_begin_date']),
                           new PluginReportsColumnDate('prof_end_date', PluginResourcesProfession::getTypeName(1)." - ".__('End date'),
                                                   ['sorton' => 'prof_end_date']),]);

// SQL statement
$condition = $dbu->getEntitiesRestrictRequest('AND', 'glpi_plugin_resources_professions', '', '', true);
$date=date("Y-m-d");

//display only leaving resource with active employment
$query = "SELECT `glpi_plugin_resources_ranks`.`id` as rank_id,
                  `glpi_plugin_resources_ranks`.`name` as rank_name,
                 `glpi_plugin_resources_ranks`.`code` as rank_code,
                 `glpi_plugin_resources_ranks`.`begin_date` as rank_begin_date,
                 `glpi_plugin_resources_ranks`.`end_date` as rank_end_date,
                 `glpi_plugin_resources_professions`.`id` as prof_id,
                 `glpi_plugin_resources_professions`.`name` AS prof_name,
                 `glpi_plugin_resources_professions`.`code` AS prof_code,
                 `glpi_plugin_resources_professions`.`begin_date` AS prof_begin_date,
                 `glpi_plugin_resources_professions`.`end_date` AS prof_end_date
          FROM `glpi_plugin_resources_ranks`
          LEFT JOIN `glpi_plugin_resources_professions`
               ON (`glpi_plugin_resources_ranks`.`plugin_resources_professions_id` = `glpi_plugin_resources_professions`.`id`
               AND ((`glpi_plugin_resources_professions`.`begin_date` IS NULL)
                        OR (`glpi_plugin_resources_professions`.`begin_date` < '".$date."')
                    AND (`glpi_plugin_resources_professions`.`end_date` IS NULL)
                        OR (`glpi_plugin_resources_professions`.`end_date` > '".$date."')))
          WHERE (`glpi_plugin_resources_ranks`.`is_active` <> `glpi_plugin_resources_professions`.`is_active`)
               OR (`glpi_plugin_resources_ranks`.`begin_date` > `glpi_plugin_resources_professions`.`end_date`)
               OR (`glpi_plugin_resources_ranks`.`end_date` < `glpi_plugin_resources_professions`.`begin_date`)
               OR (`glpi_plugin_resources_ranks`.`end_date` > `glpi_plugin_resources_professions`.`end_date`)
               OR (`glpi_plugin_resources_ranks`.`begin_date` < `glpi_plugin_resources_professions`.`begin_date`)
               OR (`glpi_plugin_resources_ranks`.`end_date` IS NULL AND `glpi_plugin_resources_professions`.`end_date` IS NOT NULL)
               OR (`glpi_plugin_resources_ranks`.`end_date` IS NOT NULL AND `glpi_plugin_resources_professions`.`end_date` IS NULL)
             ".$condition.$report->getOrderBy('rank_id');


$report->setSqlRequest($query);
$report->execute();
