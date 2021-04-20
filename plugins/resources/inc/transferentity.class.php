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
 * Class PluginResourcesTransferEntity
 */
class PluginResourcesTransferEntity extends CommonDBTM {

   static $rightname = 'plugin_resources';

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 0) {
      return __('Transfer entities', 'resources');
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
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    **/
   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }


   /**
    * @param $target
    *
    * @return bool
    */
   function showForm($target) {
      global $CFG_GLPI;

      if (!$this->canView()) {
         return false;
      }
      if (!$this->canCreate()) {
         return false;
      }

      $used_entities = [];

      $dataEntity = $this->find();

      $canedit = true;

      if ($dataEntity) {
         foreach ($dataEntity as $field) {
            $used_entities[] = $field['entities_id'];
         }
      }

      if ($canedit) {
         echo "<form name='form' method='post' action='$target'>";

         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='2'>".self::getTypeName()."</th></tr>";

         echo "<tr class='tab_bg_1'>";
         // Dropdown group
         echo "<td class='center'>";
         echo __('Entity').'&nbsp;';
         $rand = Dropdown::show("Entity", ['name' => 'entities_id', 'used' => $used_entities, 'on_change' => 'entity_group()']);
         echo "<script type='text/javascript'>";
         echo "function entity_group(){";
         $params = ['action' => 'groupEntity', 'entities_id' => '__VALUE__'];
         Ajax::updateItemJsCode('entity_group', $CFG_GLPI['root_doc'].'/plugins/resources/ajax/resourceinfo.php', $params, 'dropdown_entities_id'.$rand);
         echo "}";
         echo "</script>";
         echo "</td>";
         echo "<td class='center'>";
         echo "<span id='entity_group'></span>";
         echo "</td>";
         echo "</tr>";

         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='2'>";
         echo "<input type='submit' name='add_transferentity' class='submit' value='"._sx('button', 'Add')."' >";
         echo "</td>";
         echo "</tr>";
         echo "</table></div>";
         Html::closeForm();
      }
      if ($dataEntity) {
         $this->listItems($dataEntity, $canedit);
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
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th colspan='3'>".__('Entities allowed to transfer a resource', 'resources')."</th>";
      echo "</tr>";
      echo "<tr>";
      echo "<th width='10'>";
      if ($canedit) {
         echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
      }
      echo "</th>";
      echo "<th>".__('Entity')."</th>";
      echo "<th>".__('Group')."</th>";
      echo "</tr>";
      foreach ($fields as $field) {
         echo "<tr class='tab_bg_1'>";
         echo "<td width='10'>";
         if ($canedit) {
            Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
         }
         echo "</td>";
         //DATA LINE
         echo "<td>".Dropdown::getDropdownName('glpi_entities', $field['entities_id'])."</td>";
         echo "<td>".Dropdown::getDropdownName('glpi_groups', $field['groups_id'])."</td>";
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
    * Provides search options configuration. Do not rely directly
    * on this, @see CommonDBTM::searchOptions instead.
    *
    * @since 9.3
    *
    * This should be overloaded in Class
    *
    * @return array a *not indexed* array of search options
    *
    * @see https://glpi-developer-documentation.rtfd.io/en/master/devapi/search.html
    **/
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(2)
      ];

      $tab[] = [
         'id'            => '1',
         'table'         => $this->getTable(),
         'field'         => 'name',
         'name'          => __('Name'),
         'datatype'      => 'itemlink',
         'itemlink_type' => $this->getType(),
         'massiveaction' => false
      ];

      $tab[] = [
         'id'            => '2',
         'table'         => $this->getTable(),
         'field'         => 'id',
         'name'          => __('ID'),
         'massiveaction' => false,
         'datatype'      => 'number'
      ];

      $tab[] = [
         'id'            => '92',
         'table'         => 'glpi_entities',
         'field'         => 'name',
         'name'          => __('Entity'),
         'massiveaction' => true,
         'datatype'      => 'dropdown'
      ];

      $tab[] = [
         'id'            => '93',
         'table'         => 'glpi_groups',
         'field'         => 'name',
         'name'          => __('Group'),
         'massiveaction' => true,
         'datatype'      => 'dropdown'
      ];
      return $tab;
   }

   /**
    * Prepare input datas for adding the item
    *
    * @param array $input datas used to add the item
    *
    * @return array the modified $input array
    **/
   function prepareInputForAdd($input) {
      if (!$this->checkMandatoryFields($input)) {
         return false;
      }

      return $input;
   }

   /**
    * Prepare input datas for updating the item
    *
    * @param array $input data used to update the item
    *
    * @return array the modified $input array
    **/
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

      $mandatory_fields = ['entities_id'  => __('Entity')];

      foreach ($input as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               $msg[] = $mandatory_fields[$key];
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
