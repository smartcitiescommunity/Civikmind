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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginResourcesCost
 */
class PluginResourcesCost extends CommonDropdown {

   var $can_be_translated  = true;

   /**
    * @since 0.85
    *
    * @param $nb
    **/
   static function getTypeName($nb = 0) {

      return _n('Budget cost', 'Budget costs', $nb, 'resources');
   }

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    **/
   static function canCreate() {
      if (Session::haveRight('dropdown', UPDATE)
         && Session::haveRight('plugin_resources_dropdown_public', UPDATE)) {
         return true;
      }
      return false;
   }

   /**
    * Have I the global right to "view" the Object
    *
    * Default is true and check entity if the objet is entity assign
    *
    * May be overloaded if needed
    *
    * @return booleen
    **/
   static function canView() {
      if (Session::haveRight('plugin_resources_dropdown_public', READ)) {
         return true;
      }
      return false;
   }

   /**
    * allow to control data before adding in bdd
    *
    * @param datas $input
    * @return array|datas|the
    */
   function prepareInputForAdd($input) {

      if (!isset ($input["plugin_resources_professions_id"])
         || $input["plugin_resources_professions_id"] == '0') {
         Session::addMessageAfterRedirect(__('The profession for the budget must be filled', 'resources'), false, ERROR);
         return  [];
      }

      return $input;
   }

   /**
    * allow to control data before updating in bdd
    *
    * @param datas $input
    * @return array|datas|the
    */
   function prepareInputForUpdate($input) {

      if (!isset ($input["plugin_resources_professions_id"])
         || $input["plugin_resources_professions_id"] == '0') {
         Session::addMessageAfterRedirect(__('The profession for the budget must be filled', 'resources'), false, ERROR);
         return  [];
      }

      return $input;
   }

   /**
    * Return Additional Fields for this type
    *
    * @return array
    **/
   function getAdditionalFields() {

      return [['name' => 'plugin_resources_professions_id',
                        'label' => __('Profession', 'resources'),
                        'type'  => 'dropdownValue',
                        'list'  => true],
                  ['name'  => 'plugin_resources_ranks_id',
                        'label' => __('Rank', 'resources'),
                        'type'  => 'dropdownValue',
                        'list'  => true],
                  ['name'  => 'begin_date',
                        'label' => __('Begin date'),
                        'type'  => 'date',
                        'list'  => false],
                  ['name'  => 'end_date',
                        'label' => __('End date'),
                        'type'  => 'date',
                        'list'  => false],
                  ['name'  => 'cost',
                        'label' => __('Budget cost', 'resources'),
                        'type'  => 'decimal',
                        'list'  => false],
      ];
   }

   /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'       => '14',
         'table'    => 'glpi_plugin_resources_professions',
         'field'    => 'name',
         'name'     => __('Profession', 'resources'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '15',
         'table'    => 'glpi_plugin_resources_ranks',
         'field'    => 'name',
         'name'     => __('Rank', 'resources'),
         'datatype' => 'dropdown'
      ];

      $tab[] = [
         'id'       => '17',
         'table'    => $this->getTable(),
         'field'    => 'begin_date',
         'name'     => __('Begin date'),
         'datatype' => 'date'
      ];

      $tab[] = [
         'id'       => '18',
         'table'    => $this->getTable(),
         'field'    => 'end_date',
         'name'     => __('End date'),
         'datatype' => 'date'
      ];

      $tab[] = [
         'id'       => '19',
         'table'    => $this->getTable(),
         'field'    => 'cost',
         'name'     => __('Budget cost', 'resources'),
         'datatype' => 'decimal'
      ];

      return $tab;
   }


   /**
    * Display the cost's form
    *
    * @param $ID
    * @param array $options
    * @return bool
    */
   function showForm($ID, $options = [""]) {
      global $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      $fields = $this->getAdditionalFields();
      $nb = count($fields);

      echo "<tr class='tab_bg_1'><td>".__('Name')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name");
      echo "</td>";

      echo "<td rowspan='".($nb+1)."'>";
      echo __('Comments')."</td>";
      echo "<td rowspan='".($nb+1)."'>
            <textarea cols='45' rows='".($nb+2)."' name='comment' >".$this->fields["comment"];
      echo "</textarea></td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Profession', 'resources')."</td>";
      echo "<td>";
      $params = ['name' => 'plugin_resources_professions_id',
                    'value' => $this->fields['plugin_resources_professions_id'],
                    'entity' => $this->fields["entities_id"],
                    'action' => $CFG_GLPI["root_doc"]."/plugins/resources/ajax/dropdownRank.php",
                    'span' => 'span_rank',
                     'sort' => false
                  ];
      PluginResourcesResource::showGenericDropdown('PluginResourcesProfession', $params);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Rank', 'resources')."</td><td>";
      echo "<span id='span_rank' name='span_rank'>";
      if ($this->fields["plugin_resources_ranks_id"]>0) {
         echo Dropdown::getDropdownName('glpi_plugin_resources_ranks',
            $this->fields["plugin_resources_ranks_id"]);
      } else {
         echo __('None');
      }
      echo "</span></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Begin date')."</td>";
      echo "<td>";
      Html::showDateField("begin_date", ['value' => $this->fields["begin_date"]]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('End date')."</td>";
      echo "<td>";
      Html::showDateField("end_date", ['value' => $this->fields["end_date"]]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Budget cost', 'resources')."</td>";
      echo "<td>";
      echo "<input type='text' name='cost' value='".Html::formatNumber($this->fields["cost"], true).
         "' size='14'></td></tr>";

      if (isset($this->fields['is_protected']) && $this->fields['is_protected']) {
         $options['candel'] = false;
      }
      $this->showFormButtons($options);
      return true;

   }

   /**
    * During rank or profession transfer
    *
    * @static
    * @param $ID
    * @param $entity
    * @return ID|int|the
    */
   static function transfer($ID, $entity) {
      global $DB;

      if ($ID>0) {
         // Not already transfer
         // Search init item
         $query = "SELECT *
                   FROM `glpi_plugin_resources_costs`
                   WHERE `id` = '$ID'";

         if ($result=$DB->query($query)) {
            if ($DB->numrows($result)) {
               $data = $DB->fetchAssoc($result);
               $data = Toolbox::addslashes_deep($data);
               $input['name'] = $data['name'];
               $input['entities_id']  = $entity;
               $temp = new self();
               $newID    = $temp->getID();

               if ($newID<0) {
                  $newID = $temp->import($input);
               }

               return $newID;
            }
         }
      }
      return 0;
   }
}

