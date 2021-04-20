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

class PluginXivoPhone_Line extends CommonDBRelation {
   // From CommonDBRelation
   static public $itemtype_1 = 'Phone';
   static public $items_id_1 = 'phones_id';
   static public $itemtype_2 = 'Line';
   static public $items_id_2 = 'lines_id';

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $nb = 0;
      switch ($item->getType()) {
         case "Line":
            if ($_SESSION['glpishow_count_on_tabs']) {
               $nb = countElementsInTable(self::getTable(), ['lines_id' => $item->getID()]);
            }
            return self::createTabEntry(Phone::getTypeName($nb), $nb);
         case "Phone":
            if ($_SESSION['glpishow_count_on_tabs']) {
               $nb = countElementsInTable(self::getTable(), ['phones_id' => $item->getID()]);
            }
            return self::createTabEntry(PluginXivoLine::getTypeName($nb), $nb);
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case "Line":
            return self::showForLine($item, $withtemplate);
         case "Phone":
            return self::showForPhone($item, $withtemplate);
      }

      return true;
   }

   static function showForLine(Line $line, $withtemplate = 0) {
      global $DB;

      $lines_id = $line->fields['id'];
      $rand    = mt_rand();
      $phones = [];
      $used   = [];
      $canedit = $line->can($lines_id, UPDATE);

      $query = "SELECT
                  `glpi_plugin_xivo_phones_lines`.`id`,
                  `glpi_plugin_xivo_phones_lines`.`phones_id`,
                  `glpi_phones`.`name`,
                  `glpi_phones`.`phonemodels_id`,
                  `glpi_phones`.`manufacturers_id`,
                  `glpi_phones`.`serial`,
                  `glpi_phones`.`entities_id`
                FROM `glpi_plugin_xivo_phones_lines`
                LEFT JOIN `glpi_lines`
                  ON `glpi_lines`.`id` = `glpi_plugin_xivo_phones_lines`.`lines_id`
                LEFT JOIN `glpi_plugin_xivo_lines`
                  ON `glpi_lines`.`id` = `glpi_plugin_xivo_lines`.`lines_id`
                LEFT JOIN `glpi_phones`
                  ON `glpi_phones`.`id` = `glpi_plugin_xivo_phones_lines`.`phones_id`
                WHERE `glpi_plugin_xivo_phones_lines`.`lines_id` = $lines_id
                  ".getEntitiesRestrictRequest(" AND", "glpi_phones", '', '', true). "
                ORDER BY `glpi_phones`.`name`
                ";

      $result    = $DB->query($query);
      if ($number = $DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $phones[$data['phones_id']] = $data;
            $used[$data['phones_id']]   = $data['phones_id'];
         }
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form method='post'  action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
         echo "<input type='hidden' name='lines_id' value='$lines_id'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Add a phone', 'xivo')."</th></tr>";

         echo "<tr class='tab_bg_1'><td class='right'>";

         Phone::dropdown(['used'         => $used,
                          'entity'       => $line->fields["entities_id"],
                          'entity_sons'  => $line->fields["is_recursive"]]);
         echo "</td><td class='center'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = ['num_displayed' => $number,
                                 'container'     => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      $header_begin  = "<tr>";
      $header_top    = '';
      $header_bottom = '';
      $header_end    = '';
      if ($canedit && $number) {
         $header_top    .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_top    .= "</th>";
         $header_bottom .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_bottom .= "</th>";
      }
      $header_end .= "<th>".__('name')."</th>";
      $header_end .= "<th>".__('Entity')."</th>";
      $header_end .= "<th>".__('Model')."</th>";
      $header_end .= "<th>".__('Manufacturer')."</th>";
      $header_end .= "<th>".__('Serial number')."</th>";
      $header_end .= "</tr>";
      echo $header_begin.$header_top.$header_end;

      $used = [];
      foreach ($phones as $data) {
         echo "<tr class='tab_bg_1'>";
         if ($canedit) {
            echo "<td>";
            Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
            echo "</td>";
         }
         $phones_name  = $data['name'];
         $phones_id = $data['phones_id'];
         if ($_SESSION["glpiis_ids_visible"]
             || empty($phones_name)) {
            $phones_name = sprintf(__('%1$s (%2$s)'), $phones_name, $phones_id);
         }
         echo "<td class='center'><a href='".Phone::getFormURLWithID($phones_id)."'>".
              $phones_name."</a></td>";
         echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities",
                                                              $data['entities_id'])."</td>";
         echo "<td class='center'>".Dropdown::getDropdownName("glpi_phonemodels",
                                                              $data['phonemodels_id'])."</td>";
         echo "<td class='center'>".Dropdown::getDropdownName("glpi_manufacturers",
                                                              $data['manufacturers_id'])."</td>";
         echo "<td class='center'>".$data['serial']."</td>";
         echo "</tr>";
      }
      if ($number) {
         echo $header_begin.$header_bottom.$header_end;
      }
      echo "</table>";
      if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }

   static function showForPhone(Phone $phone, $withtemplate = 0) {
      global $DB;

      $phones_id = $phone->fields['id'];
      $rand    = mt_rand();
      $lines = [];
      $used   = [];
      $canedit = $phone->can($phones_id, UPDATE);

      $query = "SELECT
                  `glpi_plugin_xivo_phones_lines`.`id`,
                  `glpi_plugin_xivo_phones_lines`.`lines_id`,
                  `glpi_plugin_xivo_lines`.`protocol`,
                  `glpi_lines`.`name`,
                  `glpi_plugin_xivo_lines`.`provisioning_code`,
                  `glpi_lines`.`caller_num`,
                  `glpi_lines`.`caller_name`,
                  `glpi_lines`.`entities_id`
                FROM `glpi_plugin_xivo_phones_lines`
                LEFT JOIN `glpi_lines`
                  ON `glpi_lines`.`id` = `glpi_plugin_xivo_phones_lines`.`lines_id`
                LEFT JOIN `glpi_plugin_xivo_lines`
                  ON `glpi_lines`.`id` = `glpi_plugin_xivo_lines`.`lines_id`
                LEFT JOIN `glpi_phones`
                  ON `glpi_phones`.`id` = `glpi_plugin_xivo_phones_lines`.`phones_id`
                WHERE `glpi_plugin_xivo_phones_lines`.`phones_id` = $phones_id
                  ".getEntitiesRestrictRequest(" AND", "glpi_phones", '', '', true). "
                ORDER BY `glpi_lines`.`name`
                ";

      $result    = $DB->query($query);
      if ($number = $DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $lines[$data['lines_id']] = $data;
            $used[$data['lines_id']]  = $data['lines_id'];
         }
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form method='post'  action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
         echo "<input type='hidden' name='phones_id' value='$phones_id'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='2'>".__('Add a line', 'xivo')."</th></tr>";

         echo "<tr class='tab_bg_1'><td class='right'>";

         Line::dropdown(['used'         => $used,
                         'entity'       => $phone->fields["entities_id"],
                         'entity_sons'  => $phone->fields["is_recursive"]]);
         echo "</td><td class='center'>";
         echo "<input type='submit' name='add' value=\""._sx('button', 'Add')."\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = ['num_displayed' => $number,
                                 'container'     => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      $header_begin  = "<tr>";
      $header_top    = '';
      $header_bottom = '';
      $header_end    = '';
      if ($canedit && $number) {
         $header_top    .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_top    .= "</th>";
         $header_bottom .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_bottom .= "</th>";
      }
      $header_end .= "<th>".__('Protocol', 'xivo')."</th>";
      $header_end .= "<th>".__('Name')."</th>";
      $header_end .= "<th>".__('Entity')."</th>";
      $header_end .= "<th>".__('Provisioning code', 'xivo')."</th>";
      $header_end .= "<th>".__('Caller num', 'xivo')."</th>";
      $header_end .= "<th>".__('Caller name', 'xivo')."</th>";
      $header_end .= "</tr>";
      echo $header_begin.$header_top.$header_end;

      $used = [];
      foreach ($lines as $data) {
         echo "<tr class='tab_bg_1'>";
         if ($canedit) {
            echo "<td>";
            Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
            echo "</td>";
         }
         $lines_name = $data['name'];
         $lines_id   = $data['lines_id'];
         if ($_SESSION["glpiis_ids_visible"]
             || empty($lines_name)) {
            $lines_name = sprintf(__('%1$s (%2$s)'), $lines_name, $lines_id);
         }
         echo "<td class='center'>".$data['protocol']."</td>";
         echo "<td class='center'><a href='".Line::getFormURLWithID($lines_id)."'>".
              $lines_name."</a></td>";
         echo "<td class='center'>".Dropdown::getDropdownName("glpi_entities", $data['entities_id'])."</td>";
         echo "<td class='center'>".$data['provisioning_code']."</td>";
         echo "<td class='center'>".$data['caller_num']."</td>";
         echo "<td class='center'>".$data['caller_name']."</td>";
         echo "</tr>";
      }
      if ($number) {
         echo $header_begin.$header_bottom.$header_end;
      }
      echo "</table>";
      if ($canedit && $number) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
   }

   static function importAll($phone_lines = [], $phones_id = 0) {
      global $DB;

      $my_phone_line = new self;

      // check existing relation for phone
      $current_lines_iterator = $DB->request(
         [
            'FROM'  => self::getTable(),
            'WHERE' => [
               'phones_id' => $phones_id
            ],
         ]
      );

      $current_lines = [];
      foreach ($current_lines_iterator as $current_line) {
         $current_lines[$current_line['id']] = $current_line;
      }

      // import all relations
      foreach ($phone_lines as $phone_line) {
         $phone_line['phones_id'] = $phones_id;
         $id = self::importSingle($phone_line);
         unset($current_lines[$id]);
      }

      // remove old lines
      foreach ($current_lines as $id => $current_line) {
         $my_phone_line->delete(['id' => $id]);
      }
   }


   /**
    * Import a single phone_line relation
    *
    * @param  array  $phone_line the relation to import
    * @return mixed the relation id (integer) or false
    */
   static function importSingle($phone_line = []) {
      $my_phone_line = new self;
      $phone         = new Phone;
      $line          = new Line;

      // check existence of items in relation
      if ($phone->getFromDB($phone_line['phones_id'])
          && $line->getFromDB($phone_line['lines_id'])) {

         // check existing relation for phone+line
         $my_phone_line->getFromDBForItems($phone, $line);
         $id = $my_phone_line->getID();
         if ($id == -1) {
            // add new lines
            return $my_phone_line->add([
               'phones_id' => $phone_line['phones_id'],
               'lines_id'  => $phone_line['lines_id'],
            ]);
         } else {
            return $id;
         }
      }

      return false;
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
                  `id`                   INT(11) NOT NULL auto_increment,
                  `phones_id`            INT(11) NOT NULL DEFAULT 0,
                  `lines_id` INT(11) NOT NULL DEFAULT 0,
                  PRIMARY KEY                 (`id`),
                  UNIQUE INDEX `unicity` (`phones_id`, `lines_id`)
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
