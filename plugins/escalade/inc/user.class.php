<?php
/**
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Behaviors plugin for GLPI.

 Behaviors is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Behaviors is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

 @package   behaviors
 @author    Remi Collet
 @copyright Copyright (c) 2010-2011 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.indepnet.net/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 @modified Alexandre Delaunay for re-use in escalade plugin (2013-01)
 --------------------------------------------------------------------------
*/

class PluginEscaladeUser extends CommonDBTM {

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      switch ($ma->action) {
         case "use_filter_assign_group" :
            Dropdown::showYesNo("use_filter_assign_group", 0, -1, [
               'width' => '100%',
            ]);
            echo "<br><br><input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
               _sx('button', 'Post') . "\" >";
         break;
      }
      return true;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      switch ($ma->getAction()) {
         case "use_filter_assign_group":
            $escalade_user = new self();
            $input = $ma->getInput();

            foreach ($ids as $id) {
               if ($escalade_user->getFromDBByCrit(['users_id' => $id])) {
                  $escalade_user->fields['use_filter_assign_group'] = $input['use_filter_assign_group'];
                  if ($escalade_user->update($escalade_user->fields)) {
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
      }
   }

   static private function getUserGroup($entity, $userid, $filter = '', $first = true) {
      global $DB;

      $query = "SELECT glpi_groups.id
                FROM glpi_groups_users
                INNER JOIN glpi_groups ON (glpi_groups.id = glpi_groups_users.groups_id)
                WHERE glpi_groups_users.users_id='$userid'".
                getEntitiesRestrictRequest(' AND ', 'glpi_groups', '', $entity, true, true);

      if ($filter) {
         $query .= "AND ($filter)";
      }

      $query.= " ORDER BY glpi_groups_users.id";

      $rep = [];
      foreach ($DB->request($query) as $data) {
         if ($first) {
            return $data['id'];
         }
         $rep[]=$data['id'];
      }
      return ($first ? 0 : array_pop($rep));
   }

   static function getRequesterGroup($entity, $userid, $first = true) {

      return self::getUserGroup($entity, $userid, '`is_requester`', $first);
   }

   static function getTechnicianGroup($entity, $userid, $first = true) {

      return self::getUserGroup($entity, $userid, '`is_assign`', $first);
   }

   function showForm($ID) {

      $is_exist = $this->getFromDBByCrit(['users_id' => $ID]);

      if (! $is_exist) { //"Security"
         $this->fields["use_filter_assign_group"] = 0;
      }

      echo "<form action='" . $this->getFormURL() . "' method='post'>";
      echo "<table class='tab_cadre_fixe'>";

      $rand = mt_rand();

      echo "<tr class='tab_bg_1'>";
      echo "<td><label>";
      echo __("Bypass filtering on the groups assignment", "escalade");
      echo "&nbsp;";
      Dropdown::showYesNo("use_filter_assign_group", $this->fields["use_filter_assign_group"], -1, [
         'width' => '100%',
         'rand'  => $rand,
      ]);
      echo "</label>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td class='center' colspan='2'>";
      echo "<input type='hidden' name='users_id' value='$ID'>";
      if (! $is_exist) {
         echo "<input type='submit' name='add' value='"._sx('button', 'Add')."' class='submit'>";
      } else {
         echo "<input type='hidden' name='id' value='".$this->getID()."'>";
         echo "<input type='submit' name='update' value='"._sx('button', 'Update')."' class='submit'>";
      }
      echo "</td></tr>";

      echo "</table>";
      Html::closeForm();
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case 'User':
            $user = new self();
            $ID   = $item->getField('id');
            $user->showForm($ID);
         break;
      }
      return true;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item->getType()) {
         case 'User':
            return __("Escalation", "escalade");
         default :
            return '';
      }
   }
}
