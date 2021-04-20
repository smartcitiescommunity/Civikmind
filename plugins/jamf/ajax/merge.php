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
if ($_REQUEST['action'] === 'merge') {
   // Trigger extension attribute definition sync
   PluginJamfMobileSync::syncExtensionAttributeDefinitions();
   PluginJamfComputerSync::syncExtensionAttributeDefinitions();
   // An array of item IDs is required
   if (isset($_REQUEST['item_ids']) && is_array($_REQUEST['item_ids'])) {
      foreach ($_REQUEST['item_ids'] as $glpi_id => $data) {
         if (!isset($data['jamf_id'], $data['itemtype'])) {
            continue;
         }
         $jamf_id = $data['jamf_id'];
         $itemtype = $data['itemtype'];

         if (($itemtype !== 'Computer') && ($itemtype !== 'Phone')) {
            // Invalid itemtype for a mobile device
            throw new RuntimeException('Invalid itemtype!');
         }
         $item = new $itemtype();
         /** @var PluginJamfAbstractDevice $plugin_itemtype */
         $plugin_itemtype = 'PluginJamf'.$data['jamf_type'];
         /** @var PluginJamfDeviceSync $plugin_sync_itemtype */
         $plugin_sync_itemtype = 'PluginJamf'.$data['jamf_type'].'Sync';
         if ($data['jamf_type'] === 'MobileDevice') {
            $plugin_sync_itemtype = 'PluginJamfMobileSync';
         }

         $jamf_item = PluginJamfAPIClassic::getItems('mobiledevices', ['id' => $jamf_id]);
         if ($jamf_item === null) {
            // API error or device no longer exists in Jamf
            throw new RuntimeException('Jamf API error or item no longer exists!');
         }

         // Run import rules on merged devices manually since this doesn't go through the usual import process
         $rules = new PluginJamfRuleImportCollection();
         $ruleinput = [
            'name'            => $jamf_item['general']['name'],
            'itemtype'        => $itemtype,
            'last_inventory'  => $jamf_item['general']['last_inventory_update_utc'],
            'managed'         => $jamf_item['general']['managed'],
            'supervised'      => $jamf_item['general']['supervised'],
         ];
         $ruleinput = $rules->processAllRules($ruleinput, $ruleinput, ['recursive' => true]);
         $import = isset($ruleinput['_import']) ? $ruleinput['_import'] : 'NS';

         if (isset($ruleinput['_import']) && !$ruleinput['_import']) {
            // Dropped by rules
            continue;
         }

         $DB->beginTransaction();
         try {
            // Link
            $plugin_item = new $plugin_itemtype();
            $plugin_item->add([
               'itemtype'        => $itemtype,
               'items_id'        => $glpi_id,
               'jamf_items_id'   => $data['jamf_id'],
            ]);

            // Sync
            $sync_result = $plugin_sync_itemtype::sync($itemtype, $glpi_id, false);

            // Update merged device and then delete the pending import
            if ($sync_result) {
               $DB->update($plugin_itemtype::getTable(), [
                  'import_date'  => $_SESSION['glpi_currenttime']
               ], [
                  'itemtype' => $itemtype,
                  'items_id' => $glpi_id
               ]);
               $DB->delete(PluginJamfImport::getTable(), [
                  'jamf_type' => $data['jamf_type'],
                  'jamf_items_id' => $jamf_id
               ]);
               $DB->commit();
            } else {
               $DB->rollBack();
            }
         } catch (Exception $e) {
            $DB->rollBack();
         }

      }
   } else {
      throw new RuntimeException('Required argument missing!');
   }
} else {
   throw new RuntimeException('Invalid action!');
}