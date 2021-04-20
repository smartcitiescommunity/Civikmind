<?php
/*
 -------------------------------------------------------------------------
 xivo plugin for GLPI
 Copyright (C) 2017 by the xivo Development Team.

 https://github.com/pluginsGLPI/xivo
 -------------------------------------------------------------------------

 LICENSE

 This file is part of xivo.

 xivo is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 xivo is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with xivo. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginXivoPhone extends CommonDBTM {
   static $rightname = 'phone';

   /**
    * Import a single device (phone) into GLPI
    *
    * @param  array  $device the device to import
    * @return mixed the phone id (integer) or false
    */
   static function importSingle($device = []) {
      if (!isset($device['id'])) {
         return false;
      }

      $phone        = new Phone;
      $xivophone    = new self;
      $model        = new PhoneModel;
      $manufacturer = new Manufacturer;
      $networkport  = new NetworkPort();
      $xivoconfig   = PluginXivoConfig::getConfig();

      $manufacturers_id = $manufacturer->importExternal($device['vendor']);
      $phonemodels_id   = $model->importExternal($device['model']);
      $number_line      = count($device['lines']);
      $contact          = '';
      $contact_num      = '';
      if ($number_line) {
         $last_line   = end($device['lines']);
         if (isset($last_line['caller_name'])) {
            $contact     = $last_line['caller_name'];
            $contact_num = $last_line['caller_num'];
         }
      }

      $input = [
         'name'             => Dropdown::getDropdownName('glpi_manufacturers', $manufacturers_id).
                               '-'.
                               Dropdown::getDropdownName('glpi_phonemodels', $phonemodels_id),
         'serial'           => $device['sn'],
         'manufacturers_id' => $manufacturers_id,
         'phonemodels_id'   => $phonemodels_id,
         'contact'          => $contact,
         'contact_num'      => $contact_num,
         'number_line'      => $number_line,
         'firmware'         => $device['plugin'],
         'comment'          => $device['description'],
         'is_dynamic'       => 1,
      ];

      $phones_id       = self::getPhoneID($device);
      $input_xivophone = [
         'phones_id'         => $phones_id,
         'xivo_id'           => $device['id'],
         'template'          => $device['template_id'] !== null ? $device['template_id'] : "",
         'date_mod'          => $_SESSION["glpi_currenttime"],
      ];

      if ($number_line && isset($last_line['protocol'])) {
         $input_xivophone['line_name']         = $last_line['protocol']."/".
                                                 $last_line['name'];
         $input_xivophone['provisioning_code'] = $last_line['provisioning_code'];
         if (isset($last_line['glpi_users_id'])) {
            $input['users_id'] = $last_line['glpi_users_id'];
         }
      }

      if (!$phones_id) {
         // add phone
         $input['entities_id'] = $xivoconfig['default_entity'];
         $phones_id = $phone->add($input);

         // add a line in object table (to store xivo id)
         $input_xivophone['phones_id']   = $phones_id;
         $xivophone->add($input_xivophone);
      } else {
         //update phone
         $input['id'] = $phones_id;
         unset($input['name']);
         $phone->update($input);

         // add line in object table (to store xivo id)
         if ($xivophone->getFromDBByCrit([
            'xivo_id' => $device['id']
         ])) {
            $input_xivophone['id'] = $xivophone->getID();
            $xivophone->update($input_xivophone);
         } else {
            $input_xivophone['phones_id']   = $phones_id;
            $xivophone->add($input_xivophone);
         }
      }

      // import network ports
      if (!empty($device['mac'])) {
         $found_netports = self::getFullNetworkPort($phones_id);
         $net_input = [
            'items_id'                    => $phones_id,
            'itemtype'                    => 'Phone',
            'entities_id'                 => $phone->fields['entities_id'],
            'mac'                         => $device['mac'],
            'instantiation_type'          => 'NetworkPortEthernet',
            'name'                        => '',
            'NetworkName_name'            => '',
            'NetworkName__ipaddresses'    => ['-1' => $device['ip']],
            '_create_children'            => true
         ];
         if (count($found_netports) == 0) {
            $networkport->add($net_input);
         } else {
            $netport = end($found_netports);
            if (isset($netport['networknames'])) {
               $net_input['id'] = $netport['id'];
               $net_input['NetworkName_id'] = $netport['networknames'][0]['id'];
               if (isset($netport['networknames'][0]['ipaddresses'][0]['id'])) {
                  $net_input['NetworkName__ipaddresses'] = [
                     $netport['networknames'][0]['ipaddresses'][0]['id'] => $device['ip']
                  ];
               }
               $networkport->update($net_input);
            }
         }
      }

      // import line of this phones
      PluginXivoPhone_Line::importAll($device['lines'], $phones_id);

      return $phones_id;
   }

   /**
    * Retrieve the tree of netports (with name and ipaddresses) for a glpi phone
    *
    * @param  integer $phones_id the phone id
    * @return array              the netport tree
    */
   static function getFullNetworkPort($phones_id = 0) {

      global $DB;

      $networports_iterator = $DB->request(
         [
            'FROM'  => NetworkPort::getTable(),
            'WHERE' => [
               'itemtype' => 'Phone',
               'items_id' => $phones_id,
            ]
         ]
      );

      $found_networkports = [];

      foreach ($networports_iterator as $networkport) {
         $networknames_iterator = $DB->request(
            [
               'FROM'  => NetworkName::getTable(),
               'WHERE' => [
                  'itemtype' => 'NetworkPort',
                  'items_id' => $networkport['id'],
               ]
            ]
         );

         $networkport['networknames'] = [];

         foreach ($networknames_iterator as $networkname) {

            $ipaddresses_iterator = $DB->request(
               [
                  'FROM'  => IPAddress::getTable(),
                  'WHERE' => [
                     'itemtype' => 'NetworkName',
                     'items_id' => $networkname['id'],
                  ]
               ]
            );

            $networkname['ipaddresses'] = [];
            foreach ($ipaddresses_iterator as $ipaddress) {
               $networkname['ipaddresses'][] = $ipaddress;
            }
            $networkport['networknames'][] = $networkname;
         }

         $found_networkports[$networkport['id']] = $networkport;
      }

      return $found_networkports;
   }

   /**
    * Trigger a synchronisation for a single phone
    *
    * @param  string $xivo_id the device id known in XIVO
    * @return boolean
    */
   static function forceSync($xivo_id = "") {
      // check if api config is valid
      if (!PluginXivoConfig::isValid(true)) {
         return false;
      }

      $apiclient       = new PluginXivoAPIClient;
      $device          = $apiclient->getSingleDevice($xivo_id);
      $device['lines'] = $apiclient->getSingleDeviceLines($xivo_id);

      // import lines
      foreach ($device['lines'] as &$line) {
         // add or update assets
         $lines_id         = PluginXivoLine::importSingle($line);
         $line['lines_id'] = $lines_id;
      }

      // import phone
      return self::importSingle($device);
   }

   /**
    * Retrieve the GLPI by a device array, try to find by
    *  - serial number
    *  - mac address
    *  - xivo id
    *
    * @param  array $device the device from xivo api
    * @return mixed         the phone id (integer) or false
    */
   static function getPhoneID($device = []) {
      global $DB;

      $phonetable = Phone::getTable();
      $table      = self::getTable();

      $query = "SELECT phone.`id`
                FROM `$phonetable` AS phone
                LEFT JOIN `$table` AS xivo
                  ON xivo.`phones_id` = phone.`id`
                LEFT JOIN `glpi_networkports` AS net
                  ON net.`itemtype` = 'Phone'
                  AND net.`items_id` = phone.`id`
                WHERE phone.`serial` = '{$device['sn']}'
                  AND phone.`serial` IS NOT NULL
                  AND phone.`serial` != ''
                  OR net.`mac` = '{$device['mac']}'
                  OR xivo.`xivo_id` = '{$device['id']}'
                ORDER BY phone.`id` ASC";
      $result = $DB->query($query);

      if ($DB->numrows($result) >= 1) {
         return $DB->result($result, 0, 'id');
      }
      return false;
   }

   /**
    * Purge its dependencies when a GLPI Phone is purged
    *
    * @param  Phone  $phone the purged phone
    * @return boolean
    */
   static function phonePurged(Phone $phone) {
      $xivophone = new self;
      return $xivophone->deleteByCriteria(['phones_id' => $phone->getID()]);
   }

   /**
    * Display on phone form additional informations
    *
    * @param  Phone  $phone
    * @return boolean
    */
   static function displayAutoInventory(Phone $phone) {
      $xivophone     = new self;
      if ($xivophone->getFromDBByCrit([
         'phones_id' => $phone->getID()
      ])) {
         echo "<h1 class='xivo_title'>".__('XIVO informations', 'xivo')."</h1>";
         echo "</td></tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>".__("Xivo ID", 'xivo')."</td>".
              "<td>".$xivophone->fields['xivo_id']."</td>";
         echo "<td>".__("Template", 'xivo')."</td>".
              "<td>".$xivophone->fields['template']."</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>".__("Last synchronisation", 'xivo')."</td>".
              "<td>".Html::convDateTime($xivophone->fields['date_mod'])."</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>";
         $form_url = self::getFormURL();
         echo Html::link(__("Force synchronization"),
                         "$form_url?forcesync&xivo_id=".$xivophone->fields['xivo_id'],
                         ['class' => 'vsubmit']);
         echo "</td>";
         echo "</tr>";
      }

      return true;
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean true on success
    */
   static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();
      if (!$DB->tableExists($table)) {
         $migration->displayMessage(sprintf(__("Installing %s"), $table));

         $query = "CREATE TABLE `$table` (
                  `id`                INT(11) NOT NULL auto_increment,
                  `phones_id`         INT(11) NOT NULL,
                  `xivo_id`           VARCHAR(255) NOT NULL DEFAULT '',
                  `template`          VARCHAR(255) NOT NULL DEFAULT '',
                  `date_mod`          DATETIME DEFAULT NULL,
                  PRIMARY KEY     (`id`),
                  KEY `phones_id` (`phones_id`),
                  KEY `xivo_id`   (`xivo_id`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($query) or die ($DB->error());
      }

      return true;
   }

   /**
    * Database table uninstallation for the item type
    *
    * @return boolean True on success
    */
   static function uninstall() {
      global $DB;
      $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`");

      return true;
   }
}
