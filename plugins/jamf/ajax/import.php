<?php

/*
 -------------------------------------------------------------------------
 JAMF plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/jamf
 -------------------------------------------------------------------------
 LICENSE
 This file is part of JAMF plugin for GLPI.
 JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 JAMF plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');
Html::header_nocache();

Session::checkLoginUser();

global $DB;

// Get AJAX input and load it into $_REQUEST
$input = file_get_contents('php://input');
parse_str($input, $_REQUEST);

// An action must be specified
if (!isset($_REQUEST['action'])) {
   throw new RuntimeException('Required argument missing!');
}

if ($_REQUEST['action'] == 'import') {
   // An array of item IDs is required
   if (isset($_REQUEST['import_ids']) && is_array($_REQUEST['import_ids'])) {
      // Get data for each item to import
      $toimport = $DB->request([
         'SELECT' => ['type', 'jamf_type', 'jamf_items_id'],
         'FROM'   => PluginJamfImport::getTable(),
         'WHERE'  => [
            'id'  => $_REQUEST['import_ids']
         ]
      ]);
      // Trigger extension attribute definition sync
      PluginJamfMobileSync::syncExtensionAttributeDefinitions();
      PluginJamfComputerSync::syncExtensionAttributeDefinitions();
      // Import the requested device(s)
      while ($data = $toimport->next()) {
         if ($data['jamf_type'] === 'MobileDevice') {
            PluginJamfMobileSync::import($data['type'], $data['jamf_items_id']);
         } else {
            PluginJamfComputerSync::import($data['type'], $data['jamf_items_id']);
         }
      }
   } else {
      throw new RuntimeException('Required argument missing!');
   }
} else {
   throw new RuntimeException('Invalid action!');
}