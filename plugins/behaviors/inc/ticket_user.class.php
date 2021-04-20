<?php
/**
 * @version $Id: ticket_user.class.php 338 2021-03-30 12:36:31Z yllen $
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
 @copyright Copyright (c) 2010-2021 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2011

 --------------------------------------------------------------------------
*/

class PluginBehaviorsTicket_User {

   static function afterAdd(Ticket_User $item) {
      global $DB;

      $config = PluginBehaviorsConfig::getInstance();

      // Check is the connected user is a tech
      if (!is_numeric(Session::getLoginUserID(false))
          || !Session::haveRight('ticket', Ticket::OWN)) {
         return false; // No check
      }

      $config = PluginBehaviorsConfig::getInstance();
      if (($config->getField('single_tech_mode') != 0)
          && ($item->input['type'] == CommonITILActor::ASSIGN)) {

         $crit = ['tickets_id' => $item->input['tickets_id'],
                  'type'       => CommonITILActor::ASSIGN];

         foreach ($DB->request('glpi_tickets_users', $crit) as $data) {
            if ($data['id'] != $item->getID()) {
               $gu = new Ticket_User();
               $gu->delete($data);
            }
         }

       if ($config->getField('single_tech_mode') == 2) {

            foreach ($DB->request('glpi_groups_tickets', $crit) as $data) {
               $gu = new Group_Ticket();
               $gu->delete($data);
            }
         }
      }
   }
}
