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
 * Class PluginMoreticketTicketFollowup
 */
class PluginMoreticketTicketFollowup extends CommonDBTM {

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
    * @param $ticketfollowup
    *
    * @return bool
    */
   static function beforeAdd($ticketfollowup) {

      if (!is_array($ticketfollowup->input) || !count($ticketfollowup->input)) {
         // Already cancel by another plugin
         return false;
      }
      if ($ticketfollowup->input['itemtype'] == 'Ticket') {
         $config = new PluginMoreticketConfig();

         if (isset($ticketfollowup->input['_status']) && $config->useWaiting() == true) {
            $updates['id']                                = $ticketfollowup->input['items_id'];
            $updates['reason']                            = $ticketfollowup->input['reason'];
            $updates['date_report']                       = $ticketfollowup->input['date_report'];
            $updates['plugin_moreticket_waitingtypes_id'] = $ticketfollowup->input['plugin_moreticket_waitingtypes_id'];
            $updates['status']                            = $ticketfollowup->input['_status'];
            $ticket                                       = new Ticket();

            $ticket->update($updates);
            unset($ticketfollowup->input['_status']);
         }
      }
   }

   /**
    * @param $ticketfollowup
    *
    * @return bool
    */
   static function beforeUpdate($ticketfollowup) {

      if (!is_array($ticketfollowup->input) || !count($ticketfollowup->input)) {
         // Already cancel by another plugin
         return false;
      }

      if ($ticketfollowup->input['itemtype'] == 'Ticket') {
         $config = new PluginMoreticketConfig();

         if (isset($ticketfollowup->input['_status']) && $config->useWaiting() == true) {
            $updates['id']                                = $ticketfollowup->input['items_id'];
            $updates['reason']                            = $ticketfollowup->input['reason'];
            $updates['date_report']                       = $ticketfollowup->input['date_report'];
            $updates['plugin_moreticket_waitingtypes_id'] = $ticketfollowup->input['plugin_moreticket_waitingtypes_id'];
            $updates['status']                            = $ticketfollowup->input['_status'];
            $ticket                                       = new Ticket();
            $ticket->update($updates);
            unset($ticketfollowup->input['_status']);
         }
      }
   }
}
