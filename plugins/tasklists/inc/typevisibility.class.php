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

/**
 * Class PluginTasklistsTypeVisibility
 */
class PluginTasklistsTypeVisibility extends CommonDBTM {

   static $rightname = 'plugin_tasklists';

   /**
    * @param int $nb
    *
    * @return string
    */
   static function getTypeName($nb = 0) {
      return __('Visibility');
   }

   static $types = ['PluginTasklistsTaskType'];

   /**
    * Display tab for each users
    *
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      $dbu = new DbUtils();
      if (!$withtemplate) {
         if ($item->getType() == 'PluginTasklistsTaskType') {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return self::createTabEntry(self::getTypeName(),
                                           $dbu->countElementsInTable($this->getTable(),
                                                                      ["plugin_tasklists_tasktypes_id" => $item->getID()]));
            }
            return self::getTypeName();
         }
      }
      return '';
   }

   /**
    * Display content for each users
    *
    * @static
    *
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $vis = new self();
      if (in_array($item->getType(), self::getTypes(true))) {
         $vis->showVisibilities($item);
      }
      return true;
   }


   /**
    * Display form
    *
    * @param $item
    */
   function showVisibilities($item) {

      $used_groups = [];

      $dataGroups = $this->find(['plugin_tasklists_tasktypes_id' => $item->fields['id']]);

      $type    = new PluginTasklistsTaskType();
      $canedit = $type->can($item->fields['id'], UPDATE);

      if ($dataGroups) {
         foreach ($dataGroups as $field) {
            $used_groups[] = $field['groups_id'];
         }
      }

      $groups    = [];
      $group     = new Group();
      $condition = [];
      if (count($used_groups) > 0) {
         $condition [] = ["NOT" => [
            "id" => implode(',', $used_groups)
         ]];
      }
      $condition [] = getEntitiesRestrictCriteria($group->getTable(),'',$_SESSION["glpiactiveentities"],true);

      $dataGroup = $group->find($condition, ['name']);
      if ($dataGroup) {
         foreach ($dataGroup as $field) {
            $groups[$field['id']] = $field['completename'];
         }
      }

      if ($canedit) {
         echo "<form name='form' method='post' action='" .
              Toolbox::getItemTypeFormURL('PluginTasklistsTypeVisibility') . "'>";

         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='6'>" . __('Add a group', 'tasklists') . "</th></tr>";

         echo "<tr class='tab_bg_1'>";
         // Dropdown group
         echo "<td class='center'>";
         echo __('Group') . '&nbsp;';
         Dropdown::showFromArray("groups_id", $groups, ['name'     => 'groups_id',
                                                        'width'    => '150',
                                                        'multiple' => true]);
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='6'>";
         echo "<input type='submit' name='add_groups' class='submit' value='" . _sx('button', 'Add') . "' >";
         echo "<input type='hidden' name='plugin_tasklists_tasktypes_id' class='submit' value='" . $item->fields['id'] . "' >";
         echo "</td>";
         echo "</tr>";
         echo "</table></div>";
         Html::closeForm();
      }
      if ($dataGroups) {
         $this->listItems($dataGroups, $canedit);
      }
   }

   /**
    * @param $fields
    * @param $canedit
    */
   private function listItems($fields, $canedit) {

      $rand = mt_rand();
      echo "<div class='center'>";
      if ($canedit) {
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass' . __CLASS__ . $rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th colspan='3'>" . __('Groups allowed to use context', 'tasklists') . "</th>";
      echo "</tr>";
      echo "<tr>";
      echo "<th width='10'>";
      if ($canedit) {
         echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
      }
      echo "</th>";
      echo "<th>" . __('Name') . "</th>";
      echo "</tr>";
      foreach ($fields as $field) {
         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
         }
         echo "</td>";
         //DATA LINE
         echo "<td>" . Dropdown::getDropdownName('glpi_groups', $field['groups_id']) . "</td>";
         echo "</tr>";
      }

      if ($canedit) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</table>";
      echo "</div>";
   }

   /**
    * Type than could be linked to a typo
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    * */
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;
      $dbu   = new DbUtils();
      foreach ($types as $key => $type) {
         if (!($item = $dbu->getItemForItemtype($type))) {
            continue;
         }

         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

   /**
    * @param $plugin_tasklists_tasktypes_id
    *
    * @return bool
    */
   static function isUserHaveRight($plugin_tasklists_tasktypes_id) {
      $dbu = new DbUtils();
      // Get type groups
      $groups_data = $dbu->getAllDataFromTable('glpi_plugin_tasklists_typevisibilities',
                                               ['`plugin_tasklists_tasktypes_id`' => $plugin_tasklists_tasktypes_id]);

      if (!empty($groups_data)) {
         $groups_id = [];
         foreach ($groups_data as $groups) {
            $groups_id[] = $groups['groups_id'];
         }

         // Is the user allowed with his groups ?
         $group_user_data = Group_User::getUserGroups(Session::getLoginUserID());
         foreach ($group_user_data as $groups) {
            if (in_array($groups['id'], $groups_id)) {
               return true;
            }
         }

         return false;
      }

      // No restrictions if no group was added in type
      return true;
   }

   /**
    * @return array
    */
   static function seeAllowedTypes() {

      $allowed_types = [];
      $dbu           = new DbUtils();
      $types         = $dbu->getAllDataFromTable('glpi_plugin_tasklists_tasktypes');

      if (!empty($types)) {
         foreach ($types as $type) {
            if (self::isUserHaveRight($type['id'])) {
               $allowed_types[] = $type['id'];
            }
         }
      }

      return $allowed_types;
   }

   /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(1)
      ];

      $tab[] = [
         'id'            => '1',
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __('Name'),
         'datatype'      => 'itemlink',
         'itemlink_type' => $this->getType()
      ];

      $tab[] = [
         'id'       => '30',
         'table'    => $this->getTable(),
         'field'    => 'id',
         'name'     => __('ID'),
         'datatype' => 'number'
      ];

      $tab[] = [
         'id'       => '92',
         'table'    => 'glpi_groups',
         'field'    => 'name',
         'name'     => __('Group'),
         'datatype' => 'dropdown'
      ];

      return $tab;
   }

   /**
    * @param array $input
    *
    * @return array|bool
    */
   function prepareInputForAdd($input) {
      if (!$this->checkMandatoryFields($input)) {
         return false;
      }

      return $input;
   }

   /**
    * @param $input
    *
    * @return array|bool
    */
   function prepareInputForUpdate($input) {
      if (!$this->checkMandatoryFields($input)) {
         return false;
      }

      return $input;
   }

   /**
    * @param $input
    *
    * @return bool
    */
   function checkMandatoryFields($input) {
      $msg     = [];
      $checkKo = false;

      $mandatory_fields = ['groups_id' => __('Group')];

      foreach ($input as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               $msg[]   = $mandatory_fields[$key];
               $checkKo = true;
            }
         }
      }

      if ($checkKo) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"), implode(', ', $msg)), false, ERROR);
         return false;
      }
      return true;
   }
}
