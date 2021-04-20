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
 * Class PluginResourcesResourceBadge
 */
class PluginResourcesConfigHabilitation extends CommonDBTM {

   static $rightname = 'plugin_resources_habilitation';
   public $dohistory = true;

   const ACTION_ADD    = 1;
   const ACTION_DELETE = 2;

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param int $nb
    * @return string
    */
   static function getTypeName($nb = 0) {

      return _n('Super habilitation management', 'Super habilitations management', 2, 'resources');
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
    * Get the name of the action
    *
    * @param type $action
    * @return type
    */
   static function getNameAction($action) {
      switch ($action) {
         case self::ACTION_ADD :
            return __('Declare a super habilitation', 'resources');
         case self::ACTION_DELETE :
            return __('Remove a super habilitation', 'resources');
      }
   }

   /**
    * Display of the link to configure the super habilitation interface
    */
   function showFormConfig() {
      echo "<br>";
      echo "<form name='form' method='post' action='".self::getFormURL()."'>";
      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th>" . self::getTypeName(2) . "</th></tr>";
      echo "<tr class='tab_bg_1'><td class='center'>";
      echo "<a href=\"./confighabilitation.form.php?config\">".PluginMetademandsMetademand_Resource::getTypeName(2)."</a>";
      echo "</td></tr></table></div>";
      Html::closeForm();
      echo "<br>";
   }

   /**
    * Choose link with metademand
    *
    * @return bool
    */
   function showFormHabilitation() {

      if (!$this->canView()) {
         return false;
      }
      if (!$this->canCreate()) {
         return false;
      }

      $used_data = [];
      $data_entities = $this->find(['entities_id' => $_SESSION['glpiactive_entity']]);

      $number_action = count($data_entities);

      if ($data_entities) {
         foreach ($data_entities as $field) {
            $used_data[$field['action']] = $field['action'];
         }
      }
      $canedit = $this->canCreate();

      if ($canedit) {
         if ($number_action == 2) {
            echo "<div align='center'>";
            __('The current entity is already linked to a meta-demand', 'resources');
            echo "</div>";
         } else {
            //form to choose the metademand
            echo "<form name='form' method='post' action='" .
               Toolbox::getItemTypeFormURL('PluginResourcesConfigHabilitation') . "'>";

            echo "<div align='center'><table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'><th colspan='2'>" . PluginMetademandsMetademand_Resource::getTypeName(2) . "</th></tr>";
            echo "<tr class='tab_bg_1'><td class='center'>";
            echo _n('Action', 'Actions', 1) . '&nbsp;';
            Dropdown::showFromArray('action',
               [self::ACTION_ADD => self::getNameAction(self::ACTION_ADD),
                     self::ACTION_DELETE => self::getNameAction(self::ACTION_DELETE)],
               ['used' => $used_data]);
            echo "</td><td>";
            echo PluginMetademandsMetademand::getTypeName(1) . '&nbsp;';
            Dropdown::show('PluginMetademandsMetademand', ['name' => 'plugin_metademands_metademands_id',
                                                                'entity' => $_SESSION['glpiactive_entity']]);
            echo "</td></tr>";

            echo "<tr class='tab_bg_1'><td colspan='2' class='tab_bg_2 center'><input type=\"submit\" name=\"add_metademand\" class=\"submit\"
            value=\"" . _sx('button', 'Add') . "\" >";
            echo "<input type='hidden' name='entities_id' value='" . $_SESSION['glpiactive_entity'] . "'>";

            echo "</td></tr>";
            echo "</table></div>";
            Html::closeForm();
         }
      }
      //list metademands
      $data = $this->find();
      $this->listItems($data, $canedit);
   }

   /**
    * List of metademands
    *
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
         echo "<th colspan='4'>".__('Meta-demands linked', 'metademands')."</th>";
         echo "</tr>";
         echo "<tr>";
         if ($canedit) {
            echo "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand)."</th>";
         }
         echo "<th>".__('Name')."</th>";
         echo "<th>".__('Action')."</th>";
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
            echo "<td>".self::getNameAction($field['action'])."</td>";
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
    * Display Menu
    */
   function showMenu() {
      global $CFG_GLPI;

      $plugin = new Plugin();

      echo "<div align='center'><table class='tab_cadre' width='30%' cellpadding='5'>";
      echo "<tr><th colspan='2'>" . self::getTypeName(2) . "</th></tr>";

      $canresting = Session::haveright('plugin_resources_habilitation', UPDATE);

      echo "<tr class='tab_bg_1'>";
      if ($canresting) {
         $colspan = 1;
         if ($plugin->isActivated("metademands")) {
            //new habilitation
            echo "<td class='center'>";
            echo "<a href=\"./confighabilitation.form.php?new\">";
            echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/habilitationnew.png' 
                  alt='" . __('Declare a super habilitation', 'resources') . "'>";
            echo "<br>" . __('Declare a super habilitation', 'resources') . "</a>";
            echo "</td>";

            //delete habilitation
            echo "<td class='center' colspan='$colspan'>";
            echo "<a href=\"./confighabilitation.form.php?delete\">";
            echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/habilitationdelete.png' 
                  alt='" . __('Remove a super habilitation', 'resources') . "'>";
            echo "<br>" . __('Remove a super habilitation', 'resources') . "</a>";
            echo "</td>";

         } else {
            echo "<td class='center' colspan='3'>";
            echo "</td>";
         }

      }
      echo "</tr></table>";
      Html::closeForm();

      echo "</div>";

   }

}
