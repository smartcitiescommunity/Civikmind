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

Html::header_nocache();
Session::checkLoginUser();

switch ($_POST['action']) {
   case 'showCriForm' :
      $PluginManageentitiesCri = new PluginManageentitiesCri();
      $params                  = $_POST["params"];

      $PluginManageentitiesCri->showForm($params["job"], ['action'   => $params["pdf_action"],
                                                          'modal'    => $_POST["modal"],
                                                          'toupdate' => $params["toupdate"]]);
      break;

   case 'addCri':
      $PluginManageentitiesCri = new PluginManageentitiesCri();
      if ($PluginManageentitiesCri->canCreate()) {
         $input                 = json_decode(stripslashes($_POST["formInput"]));
         $input->REPORT_DESCRIPTION = urldecode($input->REPORT_DESCRIPTION);
         $params                = $_POST["params"];
         $input->enregistrement = false;
         if (isset($input->REPORT_ACTIVITE) && $input->REPORT_ACTIVITE) {
            $PluginManageentitiesCri->generatePdf($input,
                                                  ['modal'    => $_POST["modal"],
                                                   'toupdate' => $params["toupdate"]]);
         } elseif (isset($input->WITHOUTCONTRACT) && $input->WITHOUTCONTRACT) {
            $ticket = new Ticket();
            $ticket->getFromDB($params['job']);
            $input->REPORT_ACTIVITE = $ticket->fields['name'];
            $PluginManageentitiesCri->generatePdf($input,
                                                  ['modal'    => $_POST["modal"],
                                                   'toupdate' => $params["toupdate"]]);
         } else {
            echo json_encode(['success' => false,
                              'message' => __('Thanks to select a intervention type', 'manageentities')]);
         }
      }
      break;

   case 'updateCri':
      $PluginManageentitiesCri = new PluginManageentitiesCri();
      if ($PluginManageentitiesCri->canCreate()) {
         $input  = json_decode(stripslashes($_POST["formInput"]));
         $params = $_POST["params"];

         $input->enregistrement = false;
         if ($input->REPORT_ACTIVITE) {
            // Purge cri 
            $criDetail           = new PluginManageentitiesCriDetail();
            $data_criDetail      = $criDetail->find(['tickets_id' => $input->REPORT_ID]);
            $data_criDetail      = reset($data_criDetail);
            $input->documents_id = $data_criDetail['documents_id'];
            // Generate a new cri
            $PluginManageentitiesCri->generatePdf($input,
                                                  ['modal'    => $_POST["modal"],
                                                   'toupdate' => $params["toupdate"]]);

         } elseif (isset($input->WITHOUTCONTRACT) && $input->WITHOUTCONTRACT) {
            $ticket = new Ticket();
            $ticket->getFromDB($params['job']);
            $input->REPORT_ACTIVITE = $ticket->fields['name'];
            $PluginManageentitiesCri->generatePdf($input,
                                                  ['modal'    => $_POST["modal"],
                                                   'toupdate' => $params["toupdate"]]);
         } else {
            echo json_encode(['success' => false,
                              'message' => __('Thanks to select a intervention type', 'manageentities')]);
         }
      }
      break;

   case 'saveCri':
      $PluginManageentitiesCri = new PluginManageentitiesCri();
      if ($PluginManageentitiesCri->canCreate()) {
         $input                 = json_decode(stripslashes($_POST["formInput"]));
         $params                = $_POST["params"];
         $input->enregistrement = true;
         if ($input->REPORT_ACTIVITE) {
            $PluginManageentitiesCri->generatePdf($input,
                                                  ['modal'    => $_POST["modal"],
                                                   'toupdate' => $params["toupdate"]]);
         } elseif (isset($input->WITHOUTCONTRACT) && $input->WITHOUTCONTRACT) {
            $ticket = new Ticket();
            $ticket->getFromDB($params['job']);
            $input->REPORT_ACTIVITE = $ticket->fields['name'];
            $PluginManageentitiesCri->generatePdf($input,
                                                  ['modal'    => $_POST["modal"],
                                                   'toupdate' => $params["toupdate"]]);
         } else {
            echo json_encode(['success' => false,
                              'message' => __('Thanks to select a intervention type', 'manageentities')]);
         }
      }
      break;

   case 'deleteTech':
      $PluginManageentitiesCri = new PluginManageentitiesCri();
      if ($PluginManageentitiesCri->canCreate()) {
         $input                             = json_decode(stripslashes($_POST["formInput"]));
         $params                            = $_POST["params"];
         $PluginManageentitiesCriTechnician = new PluginManageentitiesCriTechnician();
         $PluginManageentitiesCriTechnician->deleteByCriteria(['users_id' => $params['tech_id']]);

         $PluginManageentitiesCri->showForm($params["job"], ['action'   => $params["pdf_action"],
                                                             'modal'    => $_POST["modal"],
                                                             'toupdate' => $params["toupdate"]]);
      }
      break;

   case 'addTech':
      $PluginManageentitiesCri = new PluginManageentitiesCri();
      if ($PluginManageentitiesCri->canCreate()) {
         $input  = json_decode(stripslashes($_POST["formInput"]));
         $params = $_POST["params"];

         $toadd["users_id"]                 = $input->users_id;
         $toadd["tickets_id"]               = $params["job"];
         $PluginManageentitiesCriTechnician = new PluginManageentitiesCriTechnician();
         $PluginManageentitiesCriTechnician->add($toadd);

         $PluginManageentitiesCri->showForm($params["job"], ['action'   => $params["pdf_action"],
                                                             'modal'    => $_POST["modal"],
                                                             'toupdate' => $params["toupdate"]]);
      }
      break;
}
