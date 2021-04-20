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
$titre = $LANG['plugin_resources']['resourceemploymentdiff'];
$report = new PluginReportsAutoReport($titre);

// Columns title (optional)
$report->setColumns( [new PluginReportsColumn('registration_number', __('Administrative number'),
                                                   ['sorton' => 'registration_number']),
                           new PluginReportsColumnLink('resource_id', __('Surname'), 'PluginResourcesResource',
                                                   ['sorton' => 'resource_name']),
                           new PluginReportsColumn('firstname', __('First name'),
                                                   ['sorton' => 'firstname']),
                           new PluginReportsColumnInteger('quota', __('Quota', 'resources'),
                                                   ['sorton' => 'quota']),
                           new PluginReportsColumn('resource_rank', __('Resource', 'resources')." - ".PluginResourcesRank::getTypeName(1),
                                                   ['sorton' => 'resource_rank']),
                           new PluginReportsColumn('resource_profession', __('Resource', 'resources')." - ".PluginResourcesProfession::getTypeName(1),
                                                   ['sorton' => 'resource_profession']),
                           new PluginReportsColumn('resource_professionline', __('Resource', 'resources')." - ".PluginResourcesProfessionLine::getTypeName(1),
                                                   ['sorton' => 'resource_professionline']),
                           new PluginReportsColumn('resource_professioncategory', __('Resource', 'resources')." - ".PluginResourcesProfessionCategory::getTypeName(1),
                                                   ['sorton' => 'resource_professioncategory']),
                           new PluginReportsColumnLink('employment_id', __('Name')." - ".PluginResourcesEmployment::getTypeName(1),
                                                   'PluginResourcesEmployment', ['sorton' => 'employment_name']),
                           new PluginReportsColumnFloat('ratio_employment_budget', __('Ratio Employment / Budget', 'resources'),
                                                   ['sorton' => 'ratio_employment_budget']),
                           new PluginReportsColumn('employment_rank', PluginResourcesEmployment::getTypeName(1)." - ".PluginResourcesRank::getTypeName(1),
                                                   ['sorton' => 'employment_rank']),
                           new PluginReportsColumn('employment_profession', PluginResourcesEmployment::getTypeName(1)." - ".PluginResourcesProfession::getTypeName(1),
                                                   ['sorton' => 'employment_profession']),
                           new PluginReportsColumn('employment_professionline', PluginResourcesEmployment::getTypeName(1)." - ".PluginResourcesProfessionLine::getTypeName(1),
                                                   ['sorton' => 'employment_professionline']),
                           new PluginReportsColumn('employment_professioncategory', PluginResourcesEmployment::getTypeName(1)." - ".PluginResourcesProfessionCategory::getTypeName(1),
                                                   ['sorton' => 'employment_professioncategory']),
                           new PluginReportsColumnDate('begin_date', __('Begin date'),
                                                   ['sorton' => 'begin_date']),
                           new PluginReportsColumnDate('end_date', __('End date'),
                                                   ['sorton' => 'end_date']),
                           new PluginReportsColumn('employment_state', PluginResourcesEmploymentState::getTypeName(1),
                                                   ['sorton' => 'employment_state']),
                           new PluginReportsColumn('employer_name', __('Name')." - ".PluginResourcesEmployer::getTypeName(1),
                                                   ['sorton' => 'employer_name']),]);

$dbu = new DbUtils();
// SQL statement
$condition = $dbu->getEntitiesRestrictRequest(' AND ', "glpi_plugin_resources_employments", '', '', false);

//display only resource which have rank and profession not equal to employment rank or profession
$query = "SELECT `glpi_users`.`registration_number`,
                          `glpi_users`.`id` as user_id,
                          `glpi_plugin_resources_resources`.`id` as resource_id,
                          `glpi_plugin_resources_resources`.`name` as resource_name,
                          `glpi_plugin_resources_resources`.`firstname`,
                          `glpi_plugin_resources_resources`.`quota`,
                          `glpi_plugin_resources_ranks`.`name` AS resource_rank,
                          `glpi_plugin_resources_professions`.`name` AS resource_profession,
                          `glpi_plugin_resources_professionlines`.`name` AS resource_professionline,
                          `glpi_plugin_resources_professioncategories`.`name` AS resource_professioncategory,
                          `glpi_plugin_resources_employments`.`name` AS employment_name,
                          `glpi_plugin_resources_employments`.`id` AS employment_id,
                          `glpi_plugin_resources_employments`.`ratio_employment_budget`,
                          `glpi_plugin_resources_employmentranks`.`name` AS employment_rank,
                          `glpi_plugin_resources_employmentprofessions`.`name` AS employment_profession,
                          `glpi_plugin_resources_employmentprofessionlines`.`name` AS employment_professionline,
                          `glpi_plugin_resources_employmentprofessioncategories`.`name` AS employment_professioncategory,
                          `glpi_plugin_resources_employments`.`begin_date`,
                          `glpi_plugin_resources_employments`.`end_date`,
                          `glpi_plugin_resources_employmentstates`.`name` AS employment_state,
                          `glpi_plugin_resources_employers`.`name` AS employer_name
                   FROM `glpi_users`
                      LEFT JOIN `glpi_plugin_resources_resources_items`
                        ON (`glpi_users`.`id` = `glpi_plugin_resources_resources_items`.`items_id`
                                AND `glpi_plugin_resources_resources_items`.`itemtype`= 'User')
                      LEFT JOIN `glpi_plugin_resources_resources`
                        ON (`glpi_plugin_resources_resources`.`id` = `glpi_plugin_resources_resources_items`.`plugin_resources_resources_id`)
                      LEFT JOIN `glpi_plugin_resources_employments`
                        ON (`glpi_plugin_resources_resources`.`id` = `glpi_plugin_resources_employments`.`plugin_resources_resources_id` )
                      LEFT JOIN `glpi_plugin_resources_ranks`
                        ON (`glpi_plugin_resources_resources`.`plugin_resources_ranks_id` = `glpi_plugin_resources_ranks`.`id`)
                      LEFT JOIN `glpi_plugin_resources_professions`
                        ON (`glpi_plugin_resources_ranks`.`plugin_resources_professions_id` = `glpi_plugin_resources_professions`.`id`)
                      LEFT JOIN `glpi_plugin_resources_professions` AS `glpi_plugin_resources_employmentprofessions`
                        ON (`glpi_plugin_resources_employments`.`plugin_resources_professions_id` = `glpi_plugin_resources_employmentprofessions`.`id`)
                      LEFT JOIN `glpi_plugin_resources_employers`
                        ON (`glpi_plugin_resources_employments`.`plugin_resources_employers_id` = `glpi_plugin_resources_employers`.`id`)
                      LEFT JOIN `glpi_plugin_resources_professionlines`
                        ON (`glpi_plugin_resources_professions`.`plugin_resources_professionlines_id` = `glpi_plugin_resources_professionlines`.`id`)
                      LEFT JOIN `glpi_plugin_resources_professioncategories`
                        ON (`glpi_plugin_resources_professions`.`plugin_resources_professioncategories_id` = `glpi_plugin_resources_professioncategories`.`id`)
                      LEFT JOIN `glpi_plugin_resources_ranks` AS `glpi_plugin_resources_employmentranks`
                        ON (`glpi_plugin_resources_employments`.`plugin_resources_ranks_id` = `glpi_plugin_resources_employmentranks`.`id`)
                      LEFT JOIN `glpi_plugin_resources_professionlines` AS `glpi_plugin_resources_employmentprofessionlines`
                        ON (`glpi_plugin_resources_employmentprofessions`.`plugin_resources_professionlines_id` = `glpi_plugin_resources_employmentprofessionlines`.`id`)
                      LEFT JOIN `glpi_plugin_resources_professioncategories` AS `glpi_plugin_resources_employmentprofessioncategories`
                        ON (`glpi_plugin_resources_employmentprofessions`.`plugin_resources_professioncategories_id` = `glpi_plugin_resources_employmentprofessioncategories`.`id`)
                      LEFT JOIN `glpi_plugin_resources_employmentstates`
                        ON (`glpi_plugin_resources_employments`.`plugin_resources_employmentstates_id` = `glpi_plugin_resources_employmentstates`.`id`)
                   WHERE (`glpi_plugin_resources_resources`.`is_leaving` = 0
                          AND `glpi_users`.`is_active` = 1
                          AND `glpi_plugin_resources_employments`.`plugin_resources_resources_id` <> 0
                          AND `glpi_plugin_resources_resources`.`is_deleted` = 0
                          AND `glpi_plugin_resources_resources`.`is_template` = 0
                          ".$condition." )
                   GROUP BY `glpi_plugin_resources_employments`.`id`, `glpi_users`.`id`
                   HAVING (resource_profession <> employment_profession
                                 OR resource_rank <> employment_rank)".
                     $report->getOrderBy('registration_number');


$report->setSqlRequest($query);
$report->execute();
