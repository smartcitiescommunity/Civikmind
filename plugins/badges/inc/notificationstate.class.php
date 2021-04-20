<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 badges plugin for GLPI
 Copyright (C) 2009-2016 by the badges Development Team.

 https://github.com/InfotelGLPI/badges
 -------------------------------------------------------------------------

 LICENSE

 This file is part of badges.

 badges is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 badges is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with badges. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginBadgesNotificationState
 */
class PluginBadgesNotificationState extends CommonDBTM {

   /**
    * @param $states_id
    *
    * @return bool
    */
   public function getFromDBbyState($states_id) {
      global $DB;

      $query = "SELECT * FROM `" . $this->getTable() . "` " .
               "WHERE `states_id` = '" . $states_id . "' ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   /**
    * @return string
    */
   public function findStates() {
      global $DB;

      $queryBranch = '';
      // Recherche les enfants

      $queryChilds = "SELECT `states_id`
      FROM `" . $this->getTable() . "`";
      if ($resultChilds = $DB->query($queryChilds)) {
         while ($dataChilds = $DB->fetchArray($resultChilds)) {
            $child = $dataChilds["states_id"];
            $queryBranch .= ",$child";
         }
      }

      return $queryBranch;
   }

   /**
    * @param $states_id
    */
   public function addNotificationState($states_id) {

      if ($this->getFromDBbyState($states_id)) {

         $this->update([
                          'id'        => $this->fields['id'],
                          'states_id' => $states_id]);
      } else {

         $this->add([
                       'states_id' => $states_id]);
      }
   }

   /**
    * @param $target
    */
   public function showAddForm($target) {

      $state = new self();
      $states = $state->find();
      $used = [];
      foreach ($states as $data) {
         $used[] = $data['states_id'];
      }

      echo "<div align='center'><form method='post'  action=\"$target\">";
      echo "<table class='tab_cadre_fixe' cellpadding='5'><tr ><th colspan='2'>";
      echo __('Unused status for expiration mailing', 'badges');
      echo "</th></tr>";
      echo "<tr class='tab_bg_1'><td>";
      Dropdown::show('State', ['name' => "states_id",
                               'used' => $used]);
      echo "</td>";
      echo "<td>";
      echo "<div align='center'>";
      echo Html::submit(_sx('button', 'Add'), ['name' => 'add']);
      echo "</div></td></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
   }

   /**
    * @param $target
    */
   public function showForm($target) {
      global $DB;

      $rand = mt_rand();

      $query = "SELECT *
      FROM `" . $this->getTable() . "`
      ORDER BY `states_id` ASC ";

      if ($result = $DB->query($query)) {
         $number = $DB->numrows($result);
         if ($number != 0) {

            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['item' => __CLASS__,
                                    'container' => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);

            echo "<div align='center'>";
            echo "<form method='post' name='massiveaction_form$rand' id='massiveaction_form$rand'  action=\"$target\">";
            echo "<table class='tab_cadre_fixe' cellpadding='5'>";
            echo "<tr>";
            echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
            echo "<th>" . __('Unused status for expiration mailing', 'badges') . "</th>";
            echo "</tr>";
            while ($ligne = $DB->fetchArray($result)) {

               echo "<tr class='tab_bg_1'>";
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $ligne["id"]);
               echo "</td>";
               echo "<td>" . Dropdown::getDropdownName("glpi_states", $ligne["states_id"]) . "</td>";
               echo "</tr>";
            }

            $paramsma['ontop'] = false;
            Html::showMassiveActions($paramsma);
            echo "</table>";
            Html::closeForm();
            echo "</div>";
         }
      }
   }

   /**
    * Get the specific massive actions
    *
    * @since version 0.84
    *
    * @param $checkitem link item to check right   (default NULL)
    *
    * @return an $array of massive actions
    */
   public function getSpecificMassiveActions($checkitem = null) {


      $actions['PluginBadgesNotificationState' . MassiveAction::CLASS_ACTION_SEPARATOR . 'purge'] = __('Delete');

      return $actions;
   }

   /**
    * @param MassiveAction $ma
    *
    * @return bool|false
    */
   /**
    * @param MassiveAction $ma
    *
    * @return bool|false
    */
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case 'purge':
            echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    *
    * @param MassiveAction $ma
    * @param CommonDBTM    $item
    * @param array         $ids
    *
    * @return nothing|void
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {

      $state = new self();

      switch ($ma->getAction()) {
         case "purge":

            foreach ($ids as $key) {
               if ($state->delete(['id' => $key])) {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
               }

            }
            break;
      }
   }
}
