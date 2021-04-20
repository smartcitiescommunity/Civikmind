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

$AJAX_INCLUDE = 1;

include('../../../inc/includes.php');
header("Content-Type: application/json; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_GET['node'])) {

   $target = "resource.php";

   $nodes = [];

   // Root node
   if ($_GET['node'] == -1) {
      $entity = $_SESSION['glpiactive_entity'];
      $dbu    = new DbUtils();

      $iterator = $DB->request([
                                  'SELECT'     => 'plugin_resources_contracttypes_id',
                                  'DISTINCT'   => true,
                                  'FROM'       => 'glpi_plugin_resources_contracttypes',
                                  'INNER JOIN' => [
                                     'glpi_plugin_resources_resources' => [
                                        'FKEY' => [
                                           'glpi_plugin_resources_contracttypes' => 'id',
                                           'glpi_plugin_resources_resources'     => 'plugin_resources_contracttypes_id'
                                        ]
                                     ]
                                  ],
                                  'WHERE'      => [
                                     'is_deleted' => 0
                                  ],
                                  'ORDER'      => 'glpi_plugin_resources_contracttypes.name'
                               ]
      );

      while ($contract = $iterator->next()) {
         $ID = $contract['plugin_resources_contracttypes_id'];
         $value = Dropdown::getDropdownName("glpi_plugin_resources_contracttypes", $ID);
         $nodes[] = [
            'id'     => $ID,
            'text'   => $value,
            'a_attr' => ["onclick" => 'window.location.replace("'.$CFG_GLPI["root_doc"] . '/plugins/resources/front/' . $target .
                                      '?criteria[0][field]=37&criteria[0][searchtype]=contains&criteria[0][value]=^' .
                                      rawurlencode($value) . '&itemtype=PluginResourcesResource&start=0")']
         ];
      }
   }
   echo json_encode($nodes);
}