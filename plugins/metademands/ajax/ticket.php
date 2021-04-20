<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Session::checkLoginUser();
header("Content-Type: text/html; charset=UTF-8");
//Html::header_nocache();
if (!isset($_POST['tickets_id'])) {
   $_POST['tickets_id'] = 0;
}

switch ($_POST['action']) {
//   case 'setTicketLinkFields':
//      $tickets = new Ticket();
//      $tickets->getFromDB($_POST['tickets_id']);
//      if (!isset($tickets->fields['entities_id'])) {
//         $tickets->fields['entities_id'] = $_SESSION['glpiactive_entity'];
//      }
//
//      $parent_groups_tickets_data = $tickets->getGroups(CommonITILActor::ASSIGN);
//
//      if (!empty($parent_groups_tickets_data)) {
//         $_SESSION["saveInput"]['Ticket']['_groups_id_requester'] = $parent_groups_tickets_data[0]['groups_id'];
//      }
//      $_SESSION["saveInput"]['Ticket']['entities_id'] = $tickets->fields['entities_id'];
//      $_SESSION["saveInput"]['Ticket']['_link']       = ['tickets_id_2' => $_POST['tickets_id'], 'link' => ''];
//
//      echo true;
//      break;
//

}
