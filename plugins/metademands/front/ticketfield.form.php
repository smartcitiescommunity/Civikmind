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

if (empty($_GET["id"])) {
   $_GET["id"] = "";
}

$ticketField = new PluginMetademandsTicketField();

if (isset($_POST["add"])) {
   // Check update rights for fields
   $ticketField->can(-1, UPDATE, $_POST);
   $_POST['value'] = $_POST[$_POST['field']];
   $_POST['id'] = $ticketField->add($_POST);

   Html::back();

} else if (isset($_POST["update"])) {
   // Check update rights for fields
   $_POST['value'] = $_POST[$_POST['field']];
   $ticketField->can(-1, UPDATE, $_POST);
   $ticketField->update($_POST);

   Html::back();

} else if (isset($_POST["purge"])) {
   // Check update rights for fields
   $ticketField->can(-1, UPDATE, $_POST);
   $ticketField->delete($_POST, 1);
   $ticketField->redirectToList();

} else if (isset($_POST['template_sync'])) {
   PluginMetademandsTicketField::updateMandatoryTicketFields($_POST);
   Html::back();

} else {
   $ticketField->checkGlobal(READ);
   Html::header(PluginMetademandsTicket_Field::getTypeName(2), '', "helpdesk", "pluginmetademandsmetademand");
   $ticketField->display(['id' => $_GET["id"]]);
   Html::footer();
}
