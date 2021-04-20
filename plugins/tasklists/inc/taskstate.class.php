<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Tasklists plugin for GLPI
 Copyright (C) 2003-2016 by the Tasklists Development Team.

 https://github.com/InfotelGLPI/tasklists
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Tasklists.

 Tasklists is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Tasklists is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Tasklists. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

// Class for a Dropdown

/**
 * Class PluginTasklistsTaskState
 */
class PluginTasklistsTaskState extends CommonDropdown {

   static $rightname = 'plugin_tasklists_config';


   /**
    * @param int $nb
    *
    * @return translated
    */
   static function getTypeName($nb = 0) {
      return _n('Status', 'Statuses', $nb, 'tasklists');
   }

   /**
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   public function showForm($ID, $options = []) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Name') . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name", ['option' => "size='40'"]);
      echo "</td>";
      if (isset($options['from_edit_ajax'])
          && $options['from_edit_ajax']) {
         echo Html::hidden('from_edit_ajax', ['value' => $options['from_edit_ajax']]);
      }

      echo "<td rowspan='4'>" . __('Description') . "</td>";
      echo "<td rowspan='4'>";
      echo "<textarea name='comment' id ='comment' cols='45' rows='3'>" .
           $this->fields['comment'] .
           "</textarea>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Color') . "</td>";
      echo "<td>";
      Html::showColorField('color', ['value' => $this->fields['color']]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Finished state') . "</td>";
      echo "<td>";
      Dropdown::showYesNo('is_finished', $this->fields['is_finished']);
      echo "</td>";
      echo "</tr>";
      if (isset($options["from_edit_ajax"]) && $options["from_edit_ajax"]) {
         echo Html::hidden("tasktypes");
      } else {
         echo "<tr class='tab_bg_1'>";
         echo "<td>"
              . _n('Context', 'Contexts', 1, 'tasklists') . "</td>";
         echo "</td>";
         echo "<td>";
         echo Html::hidden("tasktypes");
         $possible_values = [];
         $dbu             = new DbUtils();
         $datatypes       = $dbu->getAllDataFromTable($dbu->getTableForItemType('PluginTasklistsTaskType'));
         if (!empty($datatypes)) {
            foreach ($datatypes as $datatype) {
               $possible_values[$datatype['id']] = $datatype['name'];
            }
         }
         $values = json_decode($this->fields['tasktypes']);
         if (!is_array($values)) {
            $values = [];
         }

         Dropdown::showFromArray("tasktypes",
                                 $possible_values,
                                 ['values'   => $values,
                                  'multiple' => 'multiples']);


         echo "</td>";
         echo "</tr>";
      }

      $this->showFormButtons($options);

      return true;
   }


   /**
    * @return array
    */
   function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'         => 11,
         'table'      => $this->getTable(),
         'field'      => 'color',
         'name'       => __('Color'),
         'searchtype' => 'contains',
         'datatype'   => 'specific',
      ];

      $tab[] = [
         'id'           => '12',
         'table'        => $this->getTable(),
         'field'        => 'tasktypes',
         'name'         => _n('Context', 'Contexts', 1, 'tasklists'),
         'nosearch'     => true,
         'masiveaction' => false,
         'datatype'     => 'specific'
      ];

      $tab[] = [
         'id'       => '13',
         'table'    => $this->getTable(),
         'field'    => 'is_finished',
         'name'     => __('Finished state'),
         'datatype' => 'bool'
      ];

      return $tab;
   }

   /**
    * @param $input
    *
    * @return array|\type
    */
   function prepareInputForAdd($input) {
      //      if (!$this->checkMandatoryFields($input)) {
      //         return false;
      //      }

      return $this->encodeSubtypes($input);
   }

   /**
    * @param $input
    *
    * @return array|\type
    */
   function prepareInputForUpdate($input) {
      //      if (!$this->checkMandatoryFields($input)) {
      //         return false;
      //      }

      return $this->encodeSubtypes($input);
   }

   /**
    * Encode sub types
    *
    * @param type $input
    *
    * @return \type
    */
   function encodeSubtypes($input) {
      if (!empty($input['tasktypes'])) {
         $input['tasktypes'] = json_encode(array_values($input['tasktypes']));
      }

      return $input;
   }

   /**
    * @param $field
    * @param $name (default '')
    * @param $values (default '')
    * @param $options      array
    **@since 0.84
    *
    */
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $dbu = new DbUtils();
      switch ($field) {
         case 'tasktypes':
            $datatypes = $dbu->getAllDataFromTable($dbu->getTableForItemType('PluginTasklistsTaskType'));
            if (!empty($datatypes)) {
               foreach ($datatypes as $datatype) {
                  $possible_values[$datatype['id']] = $datatype['name'];
               }
            }

            return Dropdown::showFromArray($name, $possible_values,
                                           ['display'  => false,
                                            'value'    => $values[$field],
                                            'multiple' => 'multiples']);

            break;
      }

      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }


   /**
    * @param $field
    * @param $values
    * @param $options   array
    **
    *
    * @return string
    * @since 0.84
    *
    */
   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }

      switch ($field) {
         case 'tasktypes':
            $types = json_decode($values[$field]);
            if (!is_array($types)) {
               return "&nbsp;";
            }
            $names    = [];
            $tasktype = new PluginTasklistsTaskType();
            foreach ($types as $type) {
               if ($tasktype->getFromDB($type)) {
                  $names[] = $tasktype->fields['name'];
               }
            }
            $out = implode(", ", $names);
            return $out;
         case 'color' :
            return "<div style='background-color: $values[$field];'>&nbsp;</div>";
            break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   /**
    * @return mixed
    */
   function getFinishedState() {
      return $this->fields['is_finished'];
   }

   /**
    * @return mixed
    */
   static function getAllKanbanColumns() {

      $taskStates = new self();
      $columns    = ['plugin_tasklists_taskstates_id' => []];
      $restrict   = [];
      $allstates  = $taskStates->find($restrict, ['is_finished ASC', 'id']);
      foreach ($allstates as $state) {
         $columns['plugin_tasklists_taskstates_id'][$state['id']] = [
            'name'         => $state['name'],
            'header_color' => $state['color']
         ];
      }
      return $columns['plugin_tasklists_taskstates_id'];

   }

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return boolean
    **/
   static function canCreate() {
      if (static::$rightname) {
         return Session::haveRight(static::$rightname, 1);
      }
      return false;
   }
   static function canUpdate() {
      if (static::$rightname) {
         return Session::haveRight(static::$rightname, 1);
      }
      return false;
   }

   static function canDelete() {
      if (static::$rightname) {
         return Session::haveRight(static::$rightname, 1);
      }
      return false;
   }
}
