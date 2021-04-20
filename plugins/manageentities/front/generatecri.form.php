<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');
Session::checkLoginUser();

$PluginManageentitiesGenerateCri = new PluginManageentitiesGenerateCri();
$PluginManageentitiesCri         = new PluginManageentitiesCri();
$ticket = new Ticket();

if (count($_SESSION["glpiactiveentities"]) > 1
   && isset($_GET['active_entity'])) {

   if (!isset($_POST["is_recursive"])) {
      $_POST["is_recursive"] = 0;
   }
   if (Session::changeActiveEntities($_GET["active_entity"], $_POST["is_recursive"])) {
      if ($_GET["active_entity"] == $_SESSION["glpiactive_entity"]) {
         Html::redirect(preg_replace("/entities_id.*/", "", $_SERVER['HTTP_REFERER']));
      }
   }
}

if (isset($_POST['generatecri'])) {
   if (Session::haveRight('ticket', CREATE)) {

      $ko = $PluginManageentitiesGenerateCri->checkMandatoryFields($_POST);
      if (!$ko) {
         $ticket_id = $PluginManageentitiesGenerateCri->createTicketAndAssociateContract($_POST);
         if ($ticket_id) {
            $PluginManageentitiesGenerateCri->createTasks($_POST, $ticket_id);
            $config = PluginManageentitiesConfig::getInstance();
            $ticket->update(['id' => $ticket_id,'status' => $config->getField('ticket_state')]);
            if(isset($_POST['description-undone']) && $_POST['description-undone'] !=''){
               $_POST['content'] = $_POST['description-undone'];
               $PluginManageentitiesGenerateCri->createTicketTaskUndone($_POST, $ticket_id);
            }
//            $_POST['download'] = true;
            $PluginManageentitiesGenerateCri->generateCri($_POST, $ticket_id, $PluginManageentitiesCri);
            if(!$config->getField('get_pdf_cri')){
               Html::back();
            }
         }
      } else{
         Html::back();
      }


   } else {
      Html::displayRightError();
   }

} else if(isset($_GET['download'])){
   $ticket_id = $_GET['tickets_id'];
   $PluginManageentitiesGenerateCri->generateCri($_POST, $ticket_id, $PluginManageentitiesCri);
} else {
   Html::header(__('Entities portal', 'manageentities'), '', "helpdesk", "pluginmanageentitiesgeneratecri");
   $ticket->fields['itilcategories_id'] = isset($_POST['itilcategories_id']) ? $_POST['itilcategories_id'] : 0;
   $ticket->fields['type'] = isset($_POST['type']) ? $_POST['type'] : '';
   $_SESSION['glpiactive_entity'] = isset($_POST['entities_id']) ? $_POST['entities_id'] : 0;
   $_SESSION['glpiactive_entity'] = isset($_POST['entities_id']) ? $_POST['entities_id'] : 0;

   $PluginManageentitiesGenerateCri->showWizard($ticket, $_SESSION['glpiactive_entity']);
   Html::footer();

}

if (Session::getCurrentInterface() == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}