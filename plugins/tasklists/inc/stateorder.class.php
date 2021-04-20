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
 * Class PluginTasklistsStateOrder
 */
class PluginTasklistsStateOrder extends CommonDBTM {

   static $rightname = 'plugin_tasklists';

   /**
    * @param \CommonGLPI $item
    * @param int         $withtemplate
    *
    * @return string|\translated
    * @see CommonGLPI::getTabNameForItem()
    *
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == 'PluginTasklistsTaskType') {
         return PluginTasklistsTaskState::getTypeName(2);
      }
      return '';
   }

   /**
    * @param \CommonGLPI $item
    * @param int         $tabnum
    * @param int         $withtemplate
    *
    * @return bool
    * @see CommonGLPI::displayTabContentForItem()
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == 'PluginTasklistsTaskType') {
         self::showOrderStates($item->fields['id']);
      }
      return true;
   }


   /**
    * Display form
    *
    * @param $plugin_tasklists_tasktypes_id
    */
   static function showOrderStates($plugin_tasklists_tasktypes_id) {
      global $CFG_GLPI;

      Html::requireJs('tasklists');

      //      $dbu = new DbUtils();
      //      $condition = $dbu->getEntitiesRestrictRequest(" AND ", 'glpi_plugin_tasklists_stateorders', '', $_SESSION["glpiactive_entity"]);
      //   true);
      $condition = ['plugin_tasklists_tasktypes_id' => $plugin_tasklists_tasktypes_id];
      $order     = new self();
      $result    = $order->find($condition, "ranking");

      if (count($result) > 0) {

         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr>";
         echo "<th colspan='4'>";
         echo __('States ordering', 'tasklists');
         echo "</th>";
         echo "</tr>";

         echo '</table>';

         echo "<form name='form' method='post' id='form 'action='" .
              Toolbox::getItemTypeFormURL('PluginTasklistsStateOrder') . "'>";

         echo "<div id='drag'>";
         echo "<table class='tab_cadre_fixehov'>";
         echo '<input type="hidden" id="plugin_tasklists_tasktypes_id" value="' . $plugin_tasklists_tasktypes_id . '" />';
         //         echo '<input type="hidden" id="entity" value="' . $_SESSION["glpiactive_entity"] . '" />';
         foreach ($result as $data) {

            echo "<tr class='tab_bg_2'>";
            if ($_SESSION['glpi_use_mode'] == Session::DEBUG_MODE) {
               echo '<td class="rowhandler control center">';
               echo "<div class=\"drag row\" style=\"cursor: move;border-width: 0 !important;border-style: none !important; border-color: initial !important;border-image: initial !important;\">";
               echo $data['plugin_tasklists_tasktypes_id'];
               echo '</div>';
               echo '</td>';
            }
            echo '<td class="rowhandler control center">';
            echo "<div class=\"drag row\" style=\"cursor: move;border-width: 0 !important;border-style: none !important; border-color: initial !important;border-image: initial !important;\">";

            $state = new PluginTasklistsTaskState();
            $state->getFromDB($data['plugin_tasklists_taskstates_id']);
            //            echo Dropdown::getDropdownName("glpi_entities", $data['entities_id']);
            //            echo " - ";
            echo $state->fields['name'];
            echo '</div>';
            echo '</td>';

            echo '<td class="rowhandler control center">';
            echo "<div class=\"drag row\" style=\"cursor: move;border-width: 0 !important;border-style: none !important; border-color: initial !important;border-image: initial !important;\">";
            echo $data['ranking'];
            echo '</div>';
            echo '</td>';

            echo '<td class="rowhandler control center">';
            echo "<div class=\"drag row\" style=\"cursor: move;border-width: 0 !important;border-style: none !important; border-color: initial !important;border-image: initial !important;\">";
            echo "<i class=\"fa fa-arrows-alt\"></i>";
            echo '</div>';
            echo '</td>';
            echo "</tr>\n";
         }

         echo '</table>';
         echo '</div>';
         echo Html::scriptBlock('$(document).ready(function() {plugin_tasklists_redipsInit()});');
      }

      Html::closeForm();

      echo "<br><div align='center'>";
      echo "<table class='tab_cadre_fixehov'>";
      echo "<tr class='tab_bg_2'>";
      echo "<td class='center'>";
      echo "<a href='" . $CFG_GLPI['root_doc'] . "/plugins/tasklists/front/stateorder.form.php?addnew=1&plugin_tasklists_tasktypes_id=" . $plugin_tasklists_tasktypes_id . "'>";
      echo __('Add states from linked Kanban', 'tasklists');
      echo "</a>";
      echo "</td>";
      echo "<tr>";
      echo "</table></div>";
   }
   //   }

   /**
    * @param $plugin_tasklists_tasktypes_id
    */
   function addNewStates($plugin_tasklists_tasktypes_id) {

      $this->deleteByCriteria(['plugin_tasklists_tasktypes_id' => $plugin_tasklists_tasktypes_id,
                               //                               'entities_id' => $_SESSION["glpiactive_entity"]
                              ]);
      $dbu = new DbUtils();

      $states  = $dbu->getAllDataFromTable($dbu->getTableForItemType('PluginTasklistsTaskStates'));
      $ranking = 1;

      foreach ($states as $state) {
         $tasktypes = json_decode($state['tasktypes']);
         if (is_array($tasktypes)) {
            if (in_array($plugin_tasklists_tasktypes_id, $tasktypes)) {
               $input['plugin_tasklists_taskstates_id'] = $state['id'];
               //               $input['entities_id']            = $_SESSION["glpiactive_entity"];
               $input['plugin_tasklists_tasktypes_id'] = $plugin_tasklists_tasktypes_id;
               $input['ranking']                       = $ranking;
               $this->add($input);
               $ranking++;
            }
         }
      }
   }

   static function addStateContext($plugin_tasklists_tasktypes_id, $plugin_tasklists_taskstates_id) {
      $stateorder  = new self();
      $stateorders = $stateorder->find(["plugin_tasklists_tasktypes_id" => $plugin_tasklists_tasktypes_id]);
      $max         = 0;
      foreach ($stateorders as $order) {
         if ($order["ranking"] > $max) {
            $max = $order["ranking"];
         }
      }
      $input                                   = [];
      $input["plugin_tasklists_tasktypes_id"]  = $plugin_tasklists_tasktypes_id;
      $input["plugin_tasklists_taskstates_id"] = $plugin_tasklists_taskstates_id;
      $input["ranking"]                        = $max + 1;
      $stateorder->add($input);

   }

   static function removeStateContext($plugin_tasklists_tasktypes_id, $plugin_tasklists_taskstates_id) {
      $stateorder = new self();
      $stateorder->getFromDBByCrit(["plugin_tasklists_tasktypes_id" => $plugin_tasklists_tasktypes_id, "plugin_tasklists_taskstates_id" => $plugin_tasklists_taskstates_id]);
      $id          = $stateorder->getID();
      $ranking     = $stateorder->getField('ranking');
      $stateorders = $stateorder->find(["plugin_tasklists_tasktypes_id" => $plugin_tasklists_tasktypes_id]);

      foreach ($stateorders as $order) {
         $input = [];
         if ($order["ranking"] > $ranking) {
            $input["ranking"] = $order["ranking"] - 1;
            $input["id"]      = $order["id"];
         }
      }

      $stateorder->getFromDB($id);
      $stateorder->delete($stateorder->fields);

   }
}
