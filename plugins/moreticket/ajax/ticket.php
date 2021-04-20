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

include('../../../inc/includes.php');

Html::header_nocache();
Session::checkLoginUser();
header("Content-Type: text/html; charset=UTF-8");

if (!isset($_POST['tickets_id']) || empty($_POST['tickets_id'])) {
   $_POST['tickets_id'] = 0;
}

if (isset($_POST['action'])) {
   switch ($_POST['action']) {
      case 'showForm':
         $config = new PluginMoreticketConfig();

         // Ticket is waiting
         if ($config->useWaiting()) {
            $waiting_ticket = new PluginMoreticketWaitingTicket();
            $waiting_ticket->showForm($_POST['tickets_id']);
         }

         // Ticket is closed
         if ($config->useSolution()) {
            if (isset($_POST['type']) && $_POST['type'] == 'add') {
               $close_ticket = new PluginMoreticketCloseTicket();
               $close_ticket->showForm($_POST['tickets_id']);
            }
         }
         break;

      case 'showFormTicketTask':
         $config = new PluginMoreticketConfig();

         if($config->useQuestion()){
            $waiting_ticket = new PluginMoreticketWaitingTicket();
            $waiting_ticket->showQuestionSign($_POST['tickets_id']);
         }
         // Ticket is waiting
         if ($config->useWaiting()) {
            $waiting_ticket = new PluginMoreticketWaitingTicket();
            $waiting_ticket->showFormTicketTask($_POST['tickets_id']);
         }

         break;
      case 'showFormUrgency':
         $config = new PluginMoreticketConfig();


         // Ticket is waiting
         if ($config->useUrgency()) {
            $urgency_ticket = new PluginMoreticketUrgencyTicket();
            $urgency_ticket->showForm($_POST['tickets_id']);
         }
         break;

      case 'showFormSolution':
         $config = new PluginMoreticketConfig();

         if ($config->useDurationSolution()) {
            $solution = new PluginMoreticketSolution();
            $solution->showFormSolution($_POST['tickets_id']);
         }
         break;
   }
}
