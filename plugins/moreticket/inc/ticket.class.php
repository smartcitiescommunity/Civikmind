<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 moreticket plugin for GLPI
 Copyright (C) 2013-2016 by the moreticket Development Team.

 https://github.com/InfotelGLPI/moreticket
 -------------------------------------------------------------------------

 LICENSE

 This file is part of moreticket.

 moreticket is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 moreticket is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with moreticket. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}


/**
 * Class PluginMoreticketTicket
 */
class PluginMoreticketTicket extends CommonITILObject {

   static $rightname = "plugin_moreticket";

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    *
    * @param int $nb
    *
    * @return string|translated
    */
   public static function getTypeName($nb = 0) {

      return _n('Ticket', 'Tickets', $nb);
   }

   /**
    * @param Ticket $ticket
    */
   static function emptyTicket(Ticket $ticket) {
      if (!empty($_POST)) {
         self::setSessions($_POST);
      } else if (!empty($_REQUEST)) {
         self::setSessions($_REQUEST);
      }
   }

   /**
    * @param Ticket $ticket
    *
    * @return bool
    */
   static function beforeAdd(Ticket $ticket) {

      if (!is_array($ticket->input) || !count($ticket->input)) {
         // Already cancel by another plugin
         return false;
      }

      $clean_close_ticket   = true;

      if (Session::haveRight("plugin_moreticket", UPDATE)) {
         PluginMoreticketWaitingTicket::preAddWaitingTicket($ticket);
         if (PluginMoreticketCloseTicket::preAddCloseTicket($ticket)) {
            $clean_close_ticket = false;
         }

      }

      if (Session::haveRight("plugin_moreticket_justification", READ)) {
         PluginMoreticketUrgencyTicket::preAddUrgencyTicket($ticket);
      }

      //cleaning the information entered in the ticket for adding solution but not useful so delete to not add solution.
      if ($clean_close_ticket) {
         PluginMoreticketCloseTicket::cleanCloseTicket($ticket);
      }

   }


   /**
    * @param Ticket $ticket
    *
    * @return bool
    */
   static function afterAdd(Ticket $ticket) {

      if (!is_array($ticket->input) || !count($ticket->input)) {
         // Already cancel by another plugin
         return false;
      }
      if (Session::haveRight("plugin_moreticket", UPDATE)) {
         PluginMoreticketWaitingTicket::postAddWaitingTicket($ticket);
         PluginMoreticketCloseTicket::postAddCloseTicket($ticket);
         if (isset($_SESSION['glpi_plugin_moreticket_close'])) {
            unset($_SESSION['glpi_plugin_moreticket_close']);
         }

         if (isset($_SESSION['glpi_plugin_moreticket_waiting'])) {
            unset($_SESSION['glpi_plugin_moreticket_waiting']);
         }
      }

      if (Session::haveRight("plugin_moreticket_justification", READ)) {
         PluginMoreticketUrgencyTicket::postAddUrgencyTicket($ticket);

         if (isset($_SESSION['glpi_plugin_moreticket_urgency'])) {
            unset($_SESSION['glpi_plugin_moreticket_urgency']);
         }
      }

   }


   /**
    * @param Ticket $ticket
    *
    * @return bool
    */
   static function beforeUpdate(Ticket $ticket) {

      if (!is_array($ticket->input) || !count($ticket->input)) {
         // Already cancel by another plugin
         return false;
      }

      if (Session::haveRight("plugin_moreticket", UPDATE)) {
         PluginMoreticketWaitingTicket::preUpdateWaitingTicket($ticket);
      }

      if (Session::haveRight("plugin_moreticket_justification", READ)) {
         PluginMoreticketUrgencyTicket::preUpdateUrgencyTicket($ticket);
      }

   }

   /**
    * @param Ticket $ticket
    */
   static function afterUpdate(Ticket $ticket) {
      if (Session::haveRight("plugin_moreticket", UPDATE)) {
         PluginMoreticketWaitingTicket::postUpdateWaitingTicket($ticket);

         if (isset($_SESSION['glpi_plugin_moreticket_close'])) {
            unset($_SESSION['glpi_plugin_moreticket_close']);
         }

         if (isset($_SESSION['glpi_plugin_moreticket_waiting'])) {
            unset($_SESSION['glpi_plugin_moreticket_waiting']);
         }
      }

      if (Session::haveRight("plugin_moreticket_justification", READ)) {
         PluginMoreticketUrgencyTicket::postUpdateUrgencyTicket($ticket);

         if (isset($_SESSION['glpi_plugin_moreticket_urgency'])) {
            unset($_SESSION['glpi_plugin_moreticket_urgency']);
         }
      }
   }


   /**
    * @param $input
    */
   static function setSessions($input) {

      foreach ($input as $key => $values) {
         switch ($key) {
            case 'reason':
               $_SESSION['glpi_plugin_moreticket_waiting'][$key] = $values;
               break;
            case 'plugin_moreticket_waitingtypes_id':
               $_SESSION['glpi_plugin_moreticket_waiting'][$key] = $values;
               break;
            case 'date_report':
               $_SESSION['glpi_plugin_moreticket_waiting'][$key] = $values;
               break;
            case 'solutiontemplates_id':
               $_SESSION['glpi_plugin_moreticket_close'][$key] = $values;
               break;
            case 'solutiontypes_id':
               $_SESSION['glpi_plugin_moreticket_close'][$key] = $values;
               break;
            case 'solution':
               $_SESSION['glpi_plugin_moreticket_close'][$key] = $values;
               break;
            case 'justification':
               $_SESSION['glpi_plugin_moreticket_urgency'][$key] = $values;
               break;
         }
      }
      //      if (isset($_SESSION['glpi_plugin_moreticket_close'])) {
      //         print_r($_SESSION['glpi_plugin_moreticket_close']);
      //      }
   }

   static function getDefaultValues($entity = 0) {
      // TODO: Implement getDefaultValues() method.
   }
   
   public static function getItemLinkClass(): string {
      return false;
   }
}
