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

// An action must be specified
if (!isset($_POST['command'], $_POST['jamf_id'], $_POST['itemtype'], $_POST['items_id'])) {
   throw new \RuntimeException('Required argument missing!');
}

if (!is_array($_POST['jamf_id'])) {
   $_POST['jamf_id'] = [$_POST['jamf_id']];
}

if (!is_array($_POST['items_id'])) {
   $_POST['items_id'] = [$_POST['items_id']];
}

$fields = [];
if (isset($_POST['fields'])) {
   parse_str($_POST['fields'], $fields);
}

$valid_types = ['MobileDevice'];

if (!in_array($_POST['itemtype'], $valid_types, true)) {
   die('Invalid itemtype. Cannot send Jamf MDM command.');
}

$items = [];
if ($_POST['itemtype'] === 'MobileDevice') {
   foreach ($_POST['items_id'] as $items_id) {
      /** @var PluginJamfMobileDevice $item */
      $item = new PluginJamfMobileDevice();
      $item->getFromDB((int) $items_id);
      $items[] = $item;
   }
}

foreach ($items as $k => $item) {
   $commands = PluginJamfItem_MDMCommand::getApplicableCommands($item);
   $command_names = array_keys($commands);

   if (!in_array($_POST['command'], $command_names, true)) {
      unset($items[$k]);
   }
}

if (!count($items)) {
   // No applicable items or no right to send command
   exit();
}

$payload = new SimpleXMLElement("<mobile_device_command/>");
$general = $payload->addChild('general');
$general->addChild('command', $_POST['command']);
$fields = array_flip($fields);
array_walk_recursive($fields, [$general, 'addChild']);
$mobile_devices = $payload->addChild('mobile_devices');
foreach ($items as $item) {
   $device_data = $item->getJamfDeviceData();
   $jamf_id = $device_data['jamf_items_id'];
   $m = $mobile_devices->addChild('mobile_device');
   $m->addChild('id', $jamf_id);
}
echo PluginJamfAPIClassic::addItem('mobiledevicecommands', $payload->asXML(), true);