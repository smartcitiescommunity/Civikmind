<?php
/**
 * @version $Id:  yllen $
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
 @author    Remi Collet, Nelly Mahu-Lasson
 @copyright Copyright (c) 2018-2021 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
*/

class PluginBehaviorsITILSolution {


   static function beforeAdd(ITILSolution $soluce) {
      global $DB;

      if (!is_array($soluce->input) || !count($soluce->input)) {
         // Already cancel by another plugin
         return false;
      }

      $config = PluginBehaviorsConfig::getInstance();

      // Check is the connected user is a tech
      if (!is_numeric(Session::getLoginUserID(false))
          || !Session::haveRight('ticket', UPDATE)) {
         return false; // No check
      }

      // Wand to solve/close the ticket
      if ($config->getField('is_ticketsolutiontype_mandatory')
          && $soluce->input['itemtype'] == 'Ticket') {
         if ($soluce->input['solutiontypes_id'] == 0) {
            $soluce->input = false;
            Session::addMessageAfterRedirect(__("Type of solution is mandatory before ticket is solved/closed",
                                                'behaviors'), true, ERROR);
            return;
         }
      }
      if ($config->getField('is_ticketsolution_mandatory')
          && $soluce->input['itemtype'] == 'Ticket') {
         if (empty($soluce->input['content'])) {
            $soluce->input = false;
            Session::addMessageAfterRedirect(__("Description of solution is mandatory before ticket is solved/closed",
                                                'behaviors'), true, ERROR);
            return;
         }
      }
      $ticket = new Ticket();
      if ($ticket->getFromDB($soluce->input['items_id'])
          && $soluce->input['itemtype'] == 'Ticket') {

         if ($config->getField('is_ticketrealtime_mandatory')
             && ($ticket->fields['actiontime'] == 0)) {
            $soluce->input = false;
            Session::addMessageAfterRedirect(__("Duration is mandatory before ticket is solved/closed",
                                             'behaviors'), true, ERROR);
            return;
         }
         if ($config->getField('is_ticketcategory_mandatory')
             && ($ticket->fields['itilcategories_id'] == 0)) {
            $soluce->input = false;
            Session::addMessageAfterRedirect(__("Category is mandatory before ticket is solved/closed",
                                             'behaviors'), true, ERROR);
            return;
         }
         if ($config->getField('is_tickettech_mandatory')
             && ($ticket->countUsers(CommonITILActor::ASSIGN) == 0)
             && !$config->getField('ticketsolved_updatetech')) {
            $soluce->input = false;
            Session::addMessageAfterRedirect(__("Technician assigned is mandatory before ticket is solved/closed",
                                             'behaviors'), true, ERROR);
            return;
         }
         if ($config->getField('is_tickettechgroup_mandatory')
             && ($ticket->countGroups(CommonITILActor::ASSIGN) == 0)) {
            $soluce->input = false;
            Session::addMessageAfterRedirect(__("Group of technicians assigned is mandatory before ticket is solved/closed",
                                             'behaviors'), true, ERROR);
            return;
         }
         if ($config->getField('is_ticketlocation_mandatory')
             && ($ticket->fields['locations_id'] == 0)) {
            $soluce->input = false;
            Session::addMessageAfterRedirect(__("Location is mandatory before ticket is solved/closed",
                                             'behaviors'), true, ERROR);
            return;
         }
         if ($config->getField('is_tickettasktodo')) {
            foreach($DB->request('glpi_tickettasks',
                                 ['tickets_id' => $ticket->getField('id')]) as $task) {
               if ($task['state'] == 1) {
                  $soluce->input = false;
                  Session::addMessageAfterRedirect(__("You cannot solve/close a ticket with task do to",
                                                   'behaviors'), true, ERROR);
                  return;
               }
            }
         }
      }

      // Wand to solve/close a problem
      if ($config->getField('is_problemsolutiontype_mandatory')
          && $soluce->input['itemtype'] == 'Problem') {
         if ($soluce->input['solutiontypes_id'] == 0) {
            $soluce->input = false;
            Session::addMessageAfterRedirect(__("Type of solution is mandatory before problem is solved/closed",
                                                'behaviors'), true, ERROR);
            return;
         }
      }
   }


   static function beforeUpdate(ITILSolution $soluce) {

      if (!is_array($soluce->input) || !count($soluce->input)) {
         // Already cancel by another plugin
         return false;
      }

      //Toolbox::logDebug("PluginBehaviorsTicket::beforeAdd(), Ticket=", $ticket);
      $config = PluginBehaviorsConfig::getInstance();

      // Check is the connected user is a tech
      if (!is_numeric(Session::getLoginUserID(false))
            || !Session::haveRight('ticket', UPDATE)) {
         return false; // No check
      }

      // Wand to solve/close the ticket
      if ($config->getField('is_ticketsolutiontype_mandatory')
          && $soluce->input['itemtype'] == 'Ticket') {
         if (empty($soluce->input['solutiontypes_id'])) {
            $soluce->input['content'] = $soluce->fields['content'];
            $soluce->input['solutiontypes_id'] = $soluce->fields['solutiontypes_id'];
            Session::addMessageAfterRedirect(__("Type of solution is mandatory before ticket is solved/closed",
                                                'behaviors'), true, ERROR);
         }
      }
      if ($config->getField('is_ticketsolution_mandatory')
          && $soluce->input['itemtype'] == 'Ticket') {
         if (empty($soluce->input['content'])) {
            $soluce->input['content'] = $soluce->fields['content'];
            $soluce->input['solutiontypes_id'] = $soluce->fields['solutiontypes_id'];
            Session::addMessageAfterRedirect(__("Description of solution is mandatory before ticket is solved/closed",
                                                'behaviors'), true, ERROR);
         }
      }
   }


   static function afterAdd(ITILSolution $soluce) {

      $ticket = new Ticket();
      $config = PluginBehaviorsConfig::getInstance();
      if ($ticket->getFromDB($soluce->input['items_id'])
          && $soluce->input['itemtype'] == 'Ticket') {

         if ($config->getField('ticketsolved_updatetech')) {
            $ticket_user      = new Ticket_User();
            $ticket_user->getFromDBByCrit(['tickets_id' => $ticket->getID(),
                                           'type'       => CommonITILActor::ASSIGN]);

            if (isset($ticket_user->fields['users_id'])
                && ($ticket_user->fields['users_id'] != Session::getLoginUserID())) {
               $ticket_user->add(['tickets_id' => $ticket->getID(),
                                  'users_id'   => Session::getLoginUserID(),
                                  'type'       => CommonITILActor::ASSIGN]);
            }
         }
      }
   }


   /**
    * show warning message
    *
    * @param $params
    *
    * @return string
    **/
   static function checkWarnings($params) {
      global $DB;

      $ticket = $params['options']['item'];
      $config = PluginBehaviorsConfig::getInstance();

      // Check is the connected user is a tech
      if (!is_numeric(Session::getLoginUserID(false))
          || !Session::haveRight('ticket', UPDATE)) {
         return false; // No check
      }

      // Want to solve/close the ticket
      $dur     = (isset($ticket->fields['actiontime'])
                  ? $ticket->fields['actiontime']
                  : 0);
      $cat    = (isset($ticket->fields['itilcategories_id'])
                 ? $ticket->fields['itilcategories_id']
                 : 0);
      $loc    = (isset($ticket->fields['locations_id'])
                  ? $ticket->fields['locations_id']
                  : 0);

      $warnings = [];
      if ($config->getField('is_ticketrealtime_mandatory')) {
         if ($dur == 0) {
             $warnings[] = __("Duration is mandatory before ticket is solved/closed", 'behaviors');
         }

      }
      if ($config->getField('is_ticketcategory_mandatory')) {
         if ($cat == 0) {
            $warnings[] = __("Category is mandatory before ticket is solved/closed", 'behaviors');
         }
      }

      if ($config->getField('is_tickettech_mandatory')) {
         if (($ticket->countUsers(CommonITILActor::ASSIGN) == 0)
             && !isset($input["_itil_assign"]['users_id'])
             && !$config->getField('ticketsolved_updatetech')) {

            $warnings[] = __("Technician assigned is mandatory before ticket is solved/closed",
                             'behaviors');
         }
      }

      if ($config->getField('is_tickettechgroup_mandatory')) {
         if (($ticket->countGroups(CommonITILActor::ASSIGN) == 0)
             && !isset($input["_itil_assign"]['groups_id'])) {

            $warnings[] = __("Group of technicians assigned is mandatory before ticket is solved/closed",
                             'behaviors');
         }
      }

      if ($config->getField('is_ticketlocation_mandatory')) {
         if ($loc == 0) {
            $warnings[] = __("Location is mandatory before ticket is solved/closed", 'behaviors');
         }
      }

      if ($config->getField('is_tickettasktodo')) {
         foreach ($DB->request('glpi_tickettasks',
                              ['tickets_id' => $ticket->getField('id')]) as $task) {
            if ($task['state'] == 1) {
               $warnings[] = __("You cannot solve/close a ticket with task do to", 'behaviors');
            }
         }
      }

      return $warnings;
   }


   /**
    * Displaying message solution
    *
    * @param $params
   **/
   static function messageWarningSolution($params) {

      if (isset($params['item'])) {
         $item = $params['item'];
         if ($item->getType() == 'ITILSolution') {
            $warnings = self::checkWarnings($params);
            if (count($warnings)) {
               echo "<div class='warning' style='display: flow-root;'>";
               echo "<i class='fa fa-exclamation-triangle fa-5x'></i>";
               echo "<ul><li>" . implode('</li><li>', $warnings) . "</li></ul>";
               echo "<div class='sep'></div>";
               echo "</div>";
            }
         }
      }
   }


   /**
    * Displaying Add solution button or not
    *
    * @param $params
    *
    * @return array
   **/
   static function deleteAddSolutionButtton($params) {

      if (isset($params['item'])) {
         $item = $params['item'];
         if ($item->getType() == 'ITILSolution') {
            $warnings = self::checkWarnings($params);
            if (count($warnings)) {
               $params['options']['canedit'] = false;
               return $params;
            }
         }
      }
   }


}
