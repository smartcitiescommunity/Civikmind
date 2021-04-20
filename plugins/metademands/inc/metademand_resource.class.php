<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginMetademandsMetademand_Resource
 */
class PluginMetademandsMetademand_Resource extends CommonDBTM {

   static $rightname = 'plugin_metademands';

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    *
    * @param int $nb
    *
    * @return string
    */
   static function getTypeName($nb = 0) {
      return _n('Link with a metademand', 'Link with metademands', $nb, 'metademands');
   }

   /**
    * @return bool|int
    */
   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * @return bool
    */
   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   /**
    * Display tab for each users
    *
    * @param CommonGLPI $item
    * @param int $withtemplate
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         if ($item->getType() == 'PluginResourcesContractType') {
            if ($_SESSION['glpishow_count_on_tabs']) {
               $dbu = new DbUtils();
               return self::createTabEntry(self::getTypeName(),
                                           $dbu->countElementsInTable($this->getTable(),
                                                                      ["plugin_resources_contracttypes_id" => $item->getID()]));
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
    * @param CommonGLPI $item
    * @param int $tabnum
    * @param int $withtemplate
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $metademand_resource = new self();

      if ($item->getType() == 'PluginResourcesContractType') {
         $metademand_resource->showPluginForResource($item);
      }
      return true;
   }


   /**
    * @param $resourceContractType
    *
    * @return bool
    */
   function showPluginForResource($resourceContractType) {

      if (!$this->canView()) {
         return false;
      }
      if (!$this->canCreate()) {
         return false;
      }

      $used_data = [];
      $data = $this->getDataForResourceContractType($resourceContractType->fields['id'], ['entities_id' => $_SESSION['glpiactiveentities']]);
      if ($data) {
         foreach ($data as $field) {
            $used_data[] = $field['plugin_metademands_metademands_id'];
         }
      }
      $canedit = $this->canCreate();
      if ($canedit) {
         echo "<form name='form' method='post' action='".
            Toolbox::getItemTypeFormURL('PluginMetademandsMetademand_Resource')."'>";

         echo "<div align='center'><table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><th>".self::getTypeName(1)."</th></tr>";
         echo "<tr class='tab_bg_1'><td class='center'>";
         echo PluginMetademandsMetademand::getTypeName(1).'&nbsp;';
         Dropdown::show('PluginMetademandsMetademand', ['name'       => 'plugin_metademands_metademands_id',
                                                             'used'       => $used_data,
                                                             'entity'     => $_SESSION['glpiactive_entity']]);
         echo "</td></tr>";
         echo "<tr class='tab_bg_1'><td class='tab_bg_2 center'><input type=\"submit\" name=\"update\" class=\"submit\"
            value=\""._sx('button', 'Add')."\" >";
         echo "<input type='hidden' name='entities_id' value='".$_SESSION['glpiactive_entity']."'>";
         echo "<input type='hidden' name='plugin_resources_contracttypes_id' value='".$resourceContractType->fields['id']."'>";
         echo "</td></tr>";
         echo "</table></div>";
         Html::closeForm();
      }

      $this->listItems($data, $canedit);
   }

   /**
    * @param $fields
    * @param $canedit
    */
   private function listItems($fields, $canedit) {
      if (!empty($fields)) {
         $rand = mt_rand();
         echo "<div class='center'>";
         if ($canedit) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand];
            Html::showMassiveActions($massiveactionparams);
         }
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='3'>".__('Meta-demands linked', 'metademands')."</th>";
         echo "</tr>";
         echo "<tr>";
         if ($canedit) {
            echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
         }
         echo "<th>".__('Name')."</th>";
         echo "<th>".__('Entity')."</th>";
         foreach ($fields as $field) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $field['id']);
               echo "</td>";
            }
            //DATA LINE
            echo "<td>".Dropdown::getDropdownName('glpi_plugin_metademands_metademands', $field['plugin_metademands_metademands_id'])."</td>";
            echo "<td>".Dropdown::getDropdownName('glpi_entities', $field['entities_id'])."</td>";
            echo "</tr>";
         }

         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
         echo "</div>";
      }
   }

   /**
    * @param \PluginResourcesResource $resources
    */
   static function redirectFormForResource(PluginResourcesResource $resources) {
      global $CFG_GLPI;

      $metademand_resource = new self();
      $resources_step = $resources->fields['resources_step'];

      if (isset($resources->fields["plugin_resources_resources_id"]) && !empty($resources->fields["plugin_resources_resources_id"])) {
         $resources->getFromDB($resources->fields["plugin_resources_resources_id"]);
      }

      if (!empty($resources->fields["plugin_resources_contracttypes_id"])) {
         $data = $metademand_resource->getDataForResourceContractType($resources->fields['plugin_resources_contracttypes_id'], ['entities_id' => $_SESSION['glpiactive_entity']]);
         $data = array_shift($data);
         if (!empty($data["plugin_metademands_metademands_id"])) {
            Html::redirect($CFG_GLPI["root_doc"]."/plugins/metademands/front/wizard.form.php?metademands_id=".$data["plugin_metademands_metademands_id"]."&resources_id=".$resources->fields['id']."&resources_step=".$resources_step."&step=2");
         }
      }
   }

   /**
    * @param        $resourceContractType_id
    * @param array  $condition
    *
    * @return array
*/
   function getDataForResourceContractType($resourceContractType_id, $condition = []) {
      $cond  = ['plugin_resources_contracttypes_id' => $resourceContractType_id]
                    + $condition;
      $data = $this->find($cond);
      return $data;
   }

}