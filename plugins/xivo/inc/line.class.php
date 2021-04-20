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

class PluginXivoLine extends CommonDBTM {
   static $rightname = 'phone';
   public $dohistory = true;

   static function getTypeName($nb = 0) {
      return _n("Line", "Lines", $nb, 'xivo');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item->getType()) {
         case "Line":
            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
               $nb = countElementsInTable(self::getTable(), ['lines_id' => $item->getID()]);
            }
            return self::createTabEntry(__("Xivo"), $nb);
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case "Line":
            return self::showForLine($item, $withtemplate);
      }

      return true;
   }

   /**
    * Import a single line into GLPI
    *
    * @param  array  $line the line to import
    * @return mixed the line id (integer) or false
    */
   static function importSingle($line = []) {
      $xivoconfig  = PluginXivoConfig::getConfig();
      $gline       = new Line;
      $xline       = new self;
      $x_lines_id  = false;
      $g_lines_id  = false;

      $xline->getFromDBByCrit([
         'xivo_line_id' => $line['id']
      ]);
      if (($x_lines_id = $xline->getID()) > 0) {
         $gline->getFromDB($xline->fields['lines_id']);
         $g_lines_id = $gline->getID();
      }

      $input_xline = [
         'protocol'               => $line['protocol'],
         'provisioning_extension' => $line['provisioning_extension'],
         'provisioning_code'      => $line['provisioning_code'],
         'device_slot'            => $line['device_slot'],
         'context'                => $line['context'],
         'position'               => $line['position'],
         'registrar'              => $line['registrar'],
         'xivo_line_id'           => $line['id'],
         'lines_id'               => $g_lines_id,
      ];
      $input_gline = [
         'name'                   => $line['name'],
         'caller_num'             => $line['caller_id_num'],
         'caller_name'            => $line['caller_id_name'],
      ];

      if (isset($line['glpi_users_id'])) {
         $input_gline['users_id'] = $line['glpi_users_id'];
      }

      if ($x_lines_id > 0) {
         $input_gline['id'] = $g_lines_id;
         $gline->update($input_gline);

         $input_xline['id'] = $x_lines_id;
         $xline->update($input_xline);

      } else {
         $input_gline['entities_id'] = $xivoconfig['default_entity'];
         $g_lines_id = $gline->add($input_gline);

         $input_xline['lines_id'] = $g_lines_id;
         $x_lines_id = $xline->add($input_xline);
      }

      return $g_lines_id;
   }

   static function showForLine(Line $line) {
      $ID = 0;
      $options = [];
      $xline = new self;
      if (!$xline->getFromDBByCrit([
         'lines_id' => $line->getID()
      ])) {
         // create missing link
         $xline->add([
            'lines_id' => $line->getID()
         ]);
         $xline->getFromDBByCrit([
            'lines_id' => $line->getID()
         ]);
      }

      // init form html
      $xline->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Protocol', 'xivo')."</td>";
      echo "<td>";
      echo Html::input('protocol', ['value' => $xline->fields['protocol']]);
      echo "</td>";
      echo "<td>".__('Xivo line_id', 'xivo')."</td>";
      echo "<td>";
      echo Html::input('line_id', ['value' => $xline->fields['xivo_line_id']]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Provisioning extension', 'xivo')."</td>";
      echo "<td>";
      echo Html::input('provisioning_extension', ['value' => $xline->fields['provisioning_extension']]);
      echo "</td>";
      echo "<td>".__('Provisioning code', 'xivo')."</td>";
      echo "<td>";
      echo Html::input('provisioning_code', ['value' => $xline->fields['provisioning_code']]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Device slot', 'xivo')."</td>";
      echo "<td>";
      Dropdown::showNumber('device_slot', $xline->fields['device_slot'], 0, 100, 1, []);
      echo "</td>";
      echo "<td>".__('Position', 'xivo')."</td>";
      echo "<td>";
      Dropdown::showNumber('position', $xline->fields['position'], 0, 100, 1, []);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Registrar', 'xivo')."</td>";
      echo "<td>";
      echo Html::input('registrar', ['value' => $xline->fields['registrar']]);
      echo "</td>";
      echo "</tr>";

      // end form html and show controls
      $xline->showFormButtons($options);

      return true;
   }

   static function getAddSearchOptions($itemtype = '') {
      $options = [];
      $index   = 95120;

      switch ($itemtype) {
         case "Phone":
            $options[$index] = [
               'table'         => "glpi_lines",
               'field'         => 'name',
               'name'          => __('Associated lines', 'xivo'),
               'datatype'      => 'itemlink',
               'forcegroupby'  => true,
               'massiveaction' => true,
               'joinparams'    => [
                  'beforejoin' => [
                     'table' => 'glpi_plugin_xivo_phones_lines',
                     'joinparams' => [
                        'jointype' => 'child'
                     ]
                  ]
               ]
            ];
            $index++;
            break;

         case 'Line':
            $options[$index] = [
               'table'    => self::getTable(),
               'field'    => 'protocol',
               'name'     => __('Protocol', 'xivo'),
               'datatype' => 'text'
            ];
            $index++;

            $options[$index] = [
               'table'    => self::getTable(),
               'field'    => 'provisioning_extension',
               'name'     => __('Provisioning extension', 'xivo'),
               'datatype' => 'text'
            ];
            $index++;

            $options[$index] = [
               'table'    => self::getTable(),
               'field'    => 'provisioning_code',
               'name'     => __('Provisioning code', 'xivo'),
               'datatype' => 'text'
            ];
            $index++;

            $options[$index] = [
               'table'    => self::getTable(),
               'field'    => 'device_slot',
               'name'     => __('Device slot', 'xivo'),
               'datatype' => 'integer'
            ];
            $index++;

            $options[$index] = [
               'table'    => self::getTable(),
               'field'    => 'position',
               'name'     => __('Position', 'xivo'),
               'datatype' => 'integer'
            ];
            $index++;

            $options[$index] = [
               'table'    => self::getTable(),
               'field'    => 'registrar',
               'name'     => __('Registrar', 'xivo'),
               'datatype' => 'text'
            ];
            $index++;

            $options[$index] = [
               'table'    => self::getTable(),
               'field'    => 'line_id',
               'name'     => __('Xivo line_id', 'xivo'),
               'datatype' => 'text'
            ];
            $index++;
            break;
      }

      return $options;
   }

   /**
    * Database table installation for the item type
    *
    * @param Migration $migration
    * @return boolean True on success
    */
   static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();
      if (!$DB->tableExists($table)) {
         $migration->displayMessage(sprintf(__("Installing %s"), $table));

         $query = "CREATE TABLE `$table` (
                  `id`                     INT(11) NOT NULL auto_increment,
                  `lines_id`               INT(11) NOT NULL DEFAULT 0,
                  `protocol`               VARCHAR(25) NOT NULL DEFAULT '',
                  `provisioning_extension` VARCHAR(25) NOT NULL DEFAULT '',
                  `provisioning_code`      VARCHAR(25) NOT NULL DEFAULT '',
                  `device_slot`            INT(11) NOT NULL DEFAULT 0,
                  `contect`                VARCHAR(25) NOT NULL DEFAULT '',
                  `position`               INT(11) NOT NULL DEFAULT 0,
                  `registrar`              VARCHAR(50) NOT NULL DEFAULT '',
                  `xivo_line_id`           VARCHAR(255) NOT NULL DEFAULT '',
                  PRIMARY KEY        (`id`),
                  KEY `lines_id`     (`lines_id`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($query) or die ($DB->error());
      }

      // migrate to core tables
      if (!$DB->fieldExists("glpi_plugin_xivo_lines", "lines_id")) {
         $line = new Line;

         // migrate lines data
         $new_lines = [];
         $xivo_lines = $DB->request("glpi_plugin_xivo_lines");
         foreach ($xivo_lines as $xivo_line) {
            $new_lines[$xivo_line['id']] = $line->add([
               'name'         => $xivo_line['name'],
               'entities_id'  => $xivo_line['entities_id'],
               'is_recursive' => $xivo_line['is_recursive'],
               'is_deleted'   => $xivo_line['is_deleted'],
               'caller_num'   => $xivo_line['caller_id_num'],
               'caller_name'  => $xivo_line['caller_id_name'],
               'users_id'     => $xivo_line['users_id'],
               'comment'      => $xivo_line['comment']
            ]);
         }

         // drop duplicate fields
         $migration->dropField('glpi_plugin_xivo_lines', 'name');
         $migration->dropField('glpi_plugin_xivo_lines', 'entities_id');
         $migration->dropField('glpi_plugin_xivo_lines', 'is_recursive');
         $migration->dropField('glpi_plugin_xivo_lines', 'is_deleted');
         $migration->dropField('glpi_plugin_xivo_lines', 'caller_id_num');
         $migration->dropField('glpi_plugin_xivo_lines', 'caller_id_name');
         $migration->dropField('glpi_plugin_xivo_lines', 'users_id');
         $migration->dropField('glpi_plugin_xivo_lines', 'comment');
         $migration->dropField('glpi_plugin_xivo_lines', 'date_mod');
         $migration->addField('glpi_plugin_xivo_lines', 'lines_id', 'integer', ['after' => 'id']);
         $migration->changeField('glpi_plugin_xivo_lines', 'line_id', 'xivo_line_id', 'string');
         $migration->migrationOneTable('glpi_plugin_xivo_lines');

         // migrate phone_lines
         $migration->addField('glpi_plugin_xivo_phones_lines', 'lines_id', 'integer',
                              ['after' => 'plugin_xivo_lines_id']);
         $migration->dropKey('glpi_plugin_xivo_phones_lines', 'unicity');
         $migration->addKey('glpi_plugin_xivo_phones_lines', ['phones_id', 'lines_id'], 'unicity', 'UNIQUE');
         $migration->migrationOneTable('glpi_plugin_xivo_phones_lines');

         // delete preference and logs
         $displaypreference = new DisplayPreference;
         $displaypreference->deleteByCriteria([
            'itemtype' => 'PluginXivoLine',
         ]);
         $log = new Log;
         $log->deleteByCriteria([
            'itemtype' => 'PluginXivoLine',
         ]);

         // migrate foreign key data
         foreach ($new_lines as $xivo_lines_id => $lines_id) {
            $DB->query("UPDATE glpi_plugin_xivo_lines
                       SET lines_id = $lines_id WHERE id = $xivo_lines_id");
            $DB->query("UPDATE glpi_plugin_xivo_phones_lines
                        SET lines_id = $lines_id WHERE plugin_xivo_lines_id = $xivo_lines_id");
            $DB->query("UPDATE glpi_contracts_items
                        SET items_id = $lines_id
                        WHERE items_id = $xivo_lines_id AND itemtype = 'PluginXivoLine'");
            $DB->query("UPDATE glpi_infocoms
                        SET items_id = $lines_id
                        WHERE items_id = $xivo_lines_id AND itemtype = 'PluginXivoLine'");
            $DB->query("UPDATE glpi_documents_items
                        SET items_id = $lines_id
                        WHERE items_id = $xivo_lines_id AND itemtype = 'PluginXivoLine'");
            $DB->query("UPDATE glpi_notepads
                        SET items_id = $lines_id
                        WHERE items_id = $xivo_lines_id AND itemtype = 'PluginXivoLine'");
            $DB->query("UPDATE glpi_logs
                        SET items_id = $lines_id
                        WHERE items_id = $xivo_lines_id AND itemtype = 'PluginXivoLine'");
         }

         $migration->dropField('glpi_plugin_xivo_phones_lines', 'plugin_xivo_lines_id');

         $migration->addKey('glpi_plugin_xivo_lines', 'lines_id', 'lines_id', 'UNIQUE');
         $migration->migrationOneTable('glpi_plugin_xivo_lines');
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
