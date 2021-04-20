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

/**
 * Class PluginTasklistsTicket
 */
class PluginTasklistsTicket extends CommonDBTM {

   public static $rightname = 'plugin_tasklists';

   /**
    * Returns the type name with consideration of plural
    *
    * @param int $nb Number of item(s)
    *
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return _n('Ticket', 'Tickets', $nb);
   }

   /**
    * Return the name of the tab for item including forms like the config page
    *
    * @param CommonGLPI $item Instance of a CommonGLPI Item (The Config Item)
    * @param integer    $withtemplate
    *
    * @return String                   Name to be displayed
    */
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      $dbu = new DbUtils();
      if (Session::getCurrentInterface() == 'central' && Session::haveRight(self::$rightname, READ)) {
         switch ($item->getType()) {
            case "PluginTasklistsTask":
               $nb = 0;
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb = $dbu->countElementsInTable('glpi_plugin_tasklists_tickets',
                                                   ["plugin_tasklists_tasks_id" => $item->getID()]);
               }
               return self::createTabEntry(self::getTypeName(2), $nb);
               break;
            case "Ticket":
               $nb = 0;
               if ($_SESSION['glpishow_count_on_tabs']) {
                  $nb = $dbu->countElementsInTable('glpi_plugin_tasklists_tickets',
                                                   ["tickets_id" => $item->getID()]);
               }
               return self::createTabEntry(_n('Linked task', 'Linked tasks', $nb, 'tasklists'), $nb);
               break;
         }
      }
      return '';
   }

   /**
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return void
    * @throws \GlpitestSQLError
    */
   public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $ticket = new self();

      switch ($item->getType()) {
         case "PluginTasklistsTask":
            $ID = $item->getField('id');
            $ticket->showForTask($ID);
            break;
         case "Ticket":
            $ticket->showForTicket($item);
            break;
      }
   }

   /**
    * @param $item
    */
   static function cleanForTicket($item) {

      $temp = new self();
      $temp->deleteByCriteria(['tickets_id' => $item->getID()]);

   }

   /**
    * @param $ticket
    *
    * @return bool
    * @throws \GlpitestSQLError
    */
   function showForTicket($ticket) {
      global $DB;

      $ID = $ticket->getField('id');
      if (!$ticket->can($ID, READ)) {
         return false;
      }

      $canedit = $ticket->canEdit($ID);
      $rand    = mt_rand();

      $query = "SELECT DISTINCT `glpi_plugin_tasklists_tasks`.* 
                FROM `glpi_plugin_tasklists_tickets`
                LEFT JOIN `glpi_plugin_tasklists_tasks`
                 ON (`glpi_plugin_tasklists_tickets`.`plugin_tasklists_tasks_id`=`glpi_plugin_tasklists_tasks`.`id`)
                WHERE `glpi_plugin_tasklists_tickets`.`tickets_id` = '$ID' 
                ORDER BY `glpi_plugin_tasklists_tasks`.`date_creation`";

      $result = $DB->query($query);
      $number = $DB->numrows($result);

      $tickets = [];
      $used    = [];
      if ($numrows = $DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $tickets[$data['id']] = $data;
            $used[$data['id']]    = $data['id'];
         }
      }
      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='taskticket_form$rand' id='taskticket_form$rand' method='post'
               action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='3'>" . __('Add task', 'tasklists') . "</th></tr>";
         echo "<tr class='tab_bg_2'><td>";
         echo "<input type='hidden' name='tickets_id' value='$ID'>";
         PluginTasklistsTask::dropdown(['used'      => $used,
                                        'entity'    => $ticket->getEntityID(),
                                        'condition' => ['is_archived' => 0,
                                                        'is_deleted'  => 0,
                                                        'is_template' => 0]]);
         echo "</td><td class='center'>";
         echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
         echo "</td>";
         echo "</tr></table>";
         Html::closeForm();
         echo "</div>";
      }

      echo "<div class='spaced'>";
      if ($canedit && $numrows) {
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         $massiveactionparams
            = ['num_displayed'    => min($_SESSION['glpilist_limit'], $numrows),
               'specific_actions' => ['purge' => _x('button', 'Delete permanently')],
               'container'        => 'mass' . __CLASS__ . $rand,
               'extraparams'      => ['tickets_id' => $ticket->getID()]];
         Html::showMassiveActions($massiveactionparams);
      }

      echo "<table class='tab_cadre_fixehov'>";

      echo "<tr class='noHover'><th colspan='9'>" . _n('Linked task', 'Linked tasks', $number, 'tasklists') . "</th>";
      echo "</tr>";

      if ($number > 0) {
         echo "<tr>";
         echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
         echo "<th>" . __('Name') . "</th>";
         echo "<th>" . __('Date') . "</th>";
         //         echo "<th>" . _n('Context', 'Contexts', 1, 'tasklists') . "</th>";
         //         echo "<th>" . __('Status') . "</th>";
         echo "<th>" . __('Priority') . "</th>";
         echo "<th>" . __('Description') . "</th>";
         echo "</tr>";

         foreach ($tickets as $data) {

            echo "<tr class='tab_bg_1'>";
            echo "<td>";
            echo Html::getMassiveActionCheckBox(__CLASS__, $data['id']);
            echo "</td>";

            echo "<td>";
            $url = Toolbox::getItemTypeFormURL('PluginTasklistsTask') . "?id=" . $data['id'];
            echo "<a id='task" . $data['id'] . "' href='$url'>" . $data['name'] . "</a>";
            echo "</td>";

            echo "<td>";
            echo html::convDateTime($data['date_creation'], 1);
            echo "</td>";

            $style = "style=\"background-color:" . $_SESSION["glpipriority_" . $data['priority']] . ";\" ";
            echo "<td $style>";
            echo CommonITILObject::getPriorityName($data['priority']);
            echo "</td>";

            echo "<td>";
            echo Html::resume_text(Html::Clean($data['comment']), 80);
            echo "</td>";

            echo "</tr>";
         }
      } else {
         echo "<tr class='tab_bg_1'>";
         echo "<td>";
         echo __('No task linked to this ticket yet', 'tasklists');
         echo "</td>";
         echo "</tr>";
      }

      echo "</table>";
      if ($canedit && $numrows) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }
      echo "</div>";
      Html::closeForm();
   }

   /**
    * @param       $ID
    * @param array $options
    */
   function showForTask($ID) {

      $task   = new PluginTasklistsTask();
      $ticket = new Ticket();

      $task->getFromDB($ID);
      echo "<div class='center'>";
      echo "<form method='post' name='task_form'
      id='task_form'  action='" . Toolbox::getItemTypeFormURL("PluginTasklistsTask") . "'>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Link a existant ticket', 'tasklists') . "</th></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      Ticket::dropdown(['name'        => "tickets_id",
                        'entity'      => $task->getEntityID(),
                        'entity_sons' => $task->isRecursive(),
                        'displaywith' => ['id']]);

      echo "</td></tr>";

      echo "<tr class='tab_bg_1 center'><td>";
      echo "<input type='submit' name='ticket_link' value=\"" . _sx('button', 'Save') . "\" class='submit'>";
      echo "<input type='hidden' name='plugin_tasklists_tasks_id' value=" . $ID . ">";
      echo "</td></tr>";

      echo "</table>";
      Html::closeForm();
      echo "</div>";

      $task_ticket = new PluginTasklistsTicket();
      $tickets     = $task_ticket->find(['plugin_tasklists_tasks_id' => $task->fields['id']]);

      if (count($tickets) > 0) {

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th colspan='5'>" . __('Linked tickets', 'tasklists') . "</th>";
         echo "</tr>";

         echo "<tr>";
         echo "<th>" . __('Name') . "</th>";
         echo "<th>" . __('Date') . "</th>";
         echo "<th>" . __('Status') . "</th>";
         echo "<th>" . __('Priority') . "</th>";
         //         echo "<th>" . __('Associated element', 'tasklists') . "</th>";
         echo "</tr>";

         foreach ($tickets as $data) {

            if ($ticket->getFromDB($data['tickets_id'])) {
               echo "<tr class='tab_bg_1'>";
               echo "<td class='center'>";
               echo $ticket->getLink();
               echo "</td>";
               echo "<td class='center'>";
               echo Html::convDateTime($ticket->fields["date"]);
               echo "</td>";
               echo "<td class='center'>";
               echo Ticket::getStatus($ticket->fields["status"]);
               echo "</td>";
               $style = "style=\"background-color:" . $_SESSION["glpipriority_" . $ticket->fields['priority']] . ";\" ";
               echo "<td class='center' $style>";
               echo CommonITILObject::getPriorityName($ticket->fields["priority"]);
               echo "</td>";
               //               echo "<td class='center'>";
               //               $item_ticket = new Item_Ticket();
               //               $items       = $item_ticket->getUsedItems($ticket->fields["id"]);
               //               foreach ($items as $itemtype => $items_id) {
               //                  $item = new $itemtype();
               //                  foreach ($items_id as $item_id) {
               //                     echo $item::getTypeName();
               //                  }
               //                  $item->getFromDB($item_id);
               //                  echo "<br>";
               //                  echo $item->getLink();
               //                  echo "<br>";
               //               }
               //               echo "</td>";
               echo "</tr>";
            }
         }
         echo "</table>";
      }
   }
}
