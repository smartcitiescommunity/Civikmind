<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

class PluginActivityTicket {

   static function afterAddUser($item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      if ($item instanceof Ticket_User
            && $item->input['type'] == CommonITILActor::ASSIGN) {
         $ticket = new Ticket;
         $ticket->getFromDB($item->input['tickets_id']);

         $in_holiday = PluginActivityHoliday::isUserInHoliday(date('Y-m-d H:i:s', time()), [$item->input['users_id']]);
         if ($in_holiday) {
            Session::addMessageAfterRedirect(__("The following user is unavailable / on holiday : ", 'activity').implode("', '", $in_holiday), true, ERROR, false);
         }
      }
   }
}
