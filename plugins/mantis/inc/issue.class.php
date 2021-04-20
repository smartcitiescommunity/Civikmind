<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of mantis.
 *
 * mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2018 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/mantis
 * @link      https://pluginsglpi.github.io/mantis/
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMantisIssue {

   public static $state_mantis = [
         'none' => '----',
         'new' => 'new',
         'feedback' => 'feedback',
         'acknowledged' => 'acknowledged',
         'confirmed' => 'confirmed',
         'assigned' => 'assigned',
         'resolved' => 'resolved',
         'closed' => 'closed'
   ];

   public static $champsMantis = [
         'none' => '----',
         'note' => 'note',
         'additional_information' => 'additional_information'
   ];

   public $additional_information = "";

   public $notes = "";

   public function __construct() {
   }

   /**
    * Function to add information to an existent issue
    *
    * @param $idTicket
    * @param $idMantis
    * @return bool|string|translated
    */
   public function addInfoToIssueMantis($idTicket, $idMantis) {
      global $DB;
      $itemType = $_POST['itemtype'];

      $ws = new PluginMantisMantisws();
      $ws->initializeConnection();

      $ticket = new $itemType();
      $ticket->getFromDB($idTicket);

      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      $champsGlpi = $_POST['glpiField'];
      $champsUrl = $_POST['glpiUrl'];
      $followAttachment = $_POST['followAttachment'];
      $followFollow = $_POST['followFollow'];
      $followTask = $_POST['followTask'];
      $followTitle = $_POST['followTitle'];
      $followDescription = $_POST['followDescription'];
      $followCategorie = $_POST['followCategorie'];
      $followLinkedticket = $_POST['linkedTicket'];

      $itilCategorie = new ITILCategory();

      $issue = $ws->getIssueById($idMantis);

      $error = "";

      // check if we follow the attachment
      if ($followAttachment == 'true') {

         // follow attachment for ticket
         $error .= $this->addAttachment($idTicket, $error, $ws, $idMantis, $itemType);

         // follow attachment for ticket linked
         if ($followLinkedticket == 'true') {
            $tickets = Ticket_Ticket::getLinkedTicketsTo($ticket->fields['id']);
            foreach ($tickets as $link_ticket) {
               $error .= $this->addAttachment($link_ticket['tickets_id'], $error, $ws, $idMantis, $itemType);
            }
         }
      }

      // create the note if needed
      if ($this->needNote($champsUrl, $champsGlpi)) {
         $id_note = $this->createNote($champsGlpi, $ticket, $itilCategorie, $champsUrl,
                                      $ws, $idMantis, $followFollow, $followTask, $followTitle,
                                      $followDescription, $followCategorie, $followLinkedticket, $itemType);
         if (! $id_note) {
            return __("Error creating the note, the process was interrupted", "mantis");
         }
      }

      // if both need a custom field
      if (($champsUrl != 'additional_information' && $champsUrl != 'note')
            && ($champsGlpi != 'additional_information' && $champsGlpi != 'note')) {

         include_once ('structcustomfield.php');

         // if it concerns the mm custom field
         if ($champsGlpi == $champsUrl) {

            // check every custom field, when the right one is found, update it
            foreach ($issue->custom_fields as $field) {
               if ($field->name = $champsGlpi) {
                  $field->value .= "<br/>" . $this->getInfoFromTicket($champsGlpi, $champsUrl,
                                                                      $ticket, $itilCategorie,
                                                                      $followFollow, $followTask,
                                                                      $followTitle, $followDescription,
                                                                      $followCategorie, $followLinkedticket,
                                                                      $itemType);
               }
            }
         } else {

            // check every custom field, when the right one is found, update it
            foreach ($issue->custom_fields as $field) {
               if ($field->name = $champsGlpi) {
                  $field->value .= "<br/>" . $this->getInfoFromTicket($champsGlpi, $champsUrl,
                                                                      $ticket, $itilCategorie,
                                                                      $followFollow, $followTask,
                                                                      $followTitle, $followDescription,
                                                                      $followCategorie, $followLinkedticket,
                                                                      $itemType);
               }
            }

            // check every custom field, when the right one is found, update it
            foreach ($issue->custom_fields as $field) {
               if ($field->name = $champsUrl) {
                  $field->value .= "<br/>" . $this->getInfoFromTicket($champsGlpi, $champsUrl,
                                                                      $ticket, $itilCategorie,
                                                                      $followFollow, $followTask,
                                                                      $followTitle, $followDescription,
                                                                      $followCategorie, $followLinkedticket,
                                                                      $itemType);
               }
            }
         }

         // if one of them requires it
      } else if (($champsUrl != 'additional_information' && $champsUrl != 'note')
                  || ($champsGlpi != 'additional_information' && $champsGlpi != 'note')) {

         include_once ('structcustomfield.php');

         if (($champsUrl != 'additional_information' && $champsUrl != 'note')) {
            // check every custom field, when the right one is found, update it
            foreach ($issue->custom_fields as $field) {
               if ($field->name = $champsGlpi) {
                  $field->value .= "<br/>" . $this->getInfoFromTicket($champsGlpi, $champsUrl,
                                                                      $ticket, $itilCategorie,
                                                                      $followFollow, $followTask,
                                                                      $followTitle, $followDescription,
                                                                      $followCategorie, $followLinkedticket,
                                                                      $itemType);
               }
            }
         } else {
            // check every custom field, when the right one is found, update it
            foreach ($issue->custom_fields as $field) {
               if ($field->name = $champsUrl) {
                  $field->value .= "<br/>" . $this->getInfoFromTicket($champsGlpi, $champsUrl,
                                                                      $ticket, $itilCategorie,
                                                                      $followFollow, $followTask,
                                                                      $followTitle, $followDescription,
                                                                      $followCategorie, $followLinkedticket,
                                                                      $itemType);
               }
            }
         }
      }

      // update additional information
      $issue->additional_information .= "<br>" . $this->getAdditionalInfo($champsGlpi, $champsUrl,
                                                                          $ticket, $itilCategorie,
                                                                          $followFollow, $followTask,
                                                                          $followTitle, $followDescription,
                                                                          $followCategorie, $followLinkedticket,
                                                                          $itemType);

      if ($ws->updateIssueMantis($issue->id, $issue)) {
         return true;
      } else {
         return __("Error when updating MantisBT issue", "mantis") . "(custom_fields)";
      }
   }

   /**
    * Function to create an issue mantisBT
    *
    * @return bool|string
    */
   public function linkisuetoProjectMantis() {
      global $CFG_GLPI, $DB;

      // initialize object
      $ws = new PluginMantisMantisws();
      $ws->initializeConnection();

      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      $mantis = new PluginMantisMantis();
      $itilCategorie = new ITILCategory();

      // retrieve $_POST values
      $categorie = $_POST['categorie'];
      $resume = $_POST['resume'];
      $description = $_POST['description'];
      $stepToReproduce = $_POST['stepToReproduce'];
      $followAttachment = $_POST['followAttachment'];
      $idTicket = $_POST['idTicket'];
      $nameMantisProject = $_POST['nameMantisProject'];
      $idUser = $_POST['user'];
      $date = $_POST['dateEscalade'];
      $assignId = $_POST['assign'];
      $champsGlpi = $_POST['glpiField'];
      $champsUrl = $_POST['glpiUrl'];

      $followFollow = $_POST['followFollow'];
      $followTask = $_POST['followTask'];
      $followTitle = $_POST['followTitle'];
      $followDescription = $_POST['followDescription'];
      $followCategorie = $_POST['followCategorie'];
      $followLinkedticket = $_POST['linkedTicket'];
      $itemType = $_POST['itemType'];

      $enable_assign = $conf->fields["enable_assign"];

      $ticket = new $itemType();
      $ticket->getFromDB($idTicket);

      $id_note = 0; // id of the note, create if needed
      $id_mantis = []; // id of mantis link if needed
      $id_attachment = []; // id of attachments if needed
      $post = []; // info mantis at the moment of creating the link

      // if the project exists
      if ($ws->existProjectWithName($nameMantisProject)) {

         // create a project with the id for the mantis issue creation
         $project = new PluginMantisProject();
         $project->setId($ws->getProjectIdWithName($nameMantisProject));

         // on assigne si demandé
         if ($enable_assign) {
            if ($assignId != '----') {
               require_once ('structaccountdata.php');
               $assigner = new PluginMantisStructaccountdata();
               $assigner->setId($assignId);
               $this->setHandler($assigner);
            }
         }

         // fullfil Mantis issue
         $this->setProject($project);
         $this->setCategory($categorie);
         $this->setDescription(stripslashes(str_replace('\n', '</br>', $description)));
         $this->setSteps_to_reproduce(stripslashes(str_replace('\n', '</br>', $stepToReproduce)));
         $this->setSummary(stripslashes($resume));
         $this->setAdditional_information($this->getAdditionalInfo($champsGlpi, $champsUrl,
                                                                   $ticket, $itilCategorie,
                                                                   $followFollow, $followTask,
                                                                   $followTitle, $followDescription,
                                                                   $followCategorie, $followLinkedticket,
                                                                   $itemType));

         // if both need a custom field
         if (($champsUrl != 'additional_information' && $champsUrl != 'note')
               && ($champsGlpi != 'additional_information' && $champsGlpi != 'note')) {

            include_once ('structcustomfield.php');

            // if it concerns the mm custom field
            if ($champsGlpi == $champsUrl) {

               $custom = new PluginMantisStructcustomField();
               $custom->setValue($this->getInfoFromTicket($champsGlpi, $champsUrl, $ticket,
                                                          $itilCategorie, $followFollow, $followTask,
                                                          $followTitle, $followDescription, $followCategorie,
                                                          $followLinkedticket, $itemType));
               $custom->setField($ws->getCustomFieldByNameAndProject($champsGlpi, $nameMantisProject));
               $this->setCustom_fields([
                     $custom
               ]);
            } else {

               $custom1 = new PluginMantisStructcustomField();
               $custom1->setValue($this->getInfoFromTicket($champsGlpi, $champsUrl, $ticket,
                                                           $itilCategorie, $followFollow,
                                                           $followTask, $followTitle,
                                                           $followDescription, $followCategorie,
                                                           $followLinkedticket, $itemType));
               $custom1->setField($ws->getCustomFieldByNameAndProject($champsGlpi, $nameMantisProject));

               $custom2 = new PluginMantisStructcustomField();
               $custom2->setValue($this->getInfoFromTicket($champsGlpi, $champsUrl, $ticket,
                                                           $itilCategorie, $followFollow,
                                                           $followTask, $followTitle,
                                                           $followDescription, $followCategorie,
                                                           $followLinkedticket, $itemType));
               $custom2->setField($ws->getCustomFieldByNameAndProject($champsUrl, $nameMantisProject));

               $this->setCustom_fields([
                     $custom1,
                     $custom2
               ]);
            }

            // if both need a custom field
         } else if (($champsUrl != 'additional_information' && $champsUrl != 'note')
                     || ($champsGlpi != 'additional_information' && $champsGlpi != 'note')) {

            include_once ('structcustomfield.php');

            if (($champsUrl != 'additional_information' && $champsUrl != 'note')) {

               $custom = new PluginMantisStructcustomField();
               $custom->setValue($this->getInfoFromTicket($champsGlpi, $champsUrl, $ticket,
                                                          $itilCategorie, $followFollow, $followTask,
                                                          $followTitle, $followDescription, $followCategorie,
                                                          $followLinkedticket, $itemType));
               $custom->setField($ws->getCustomFieldByNameAndProject($champsUrl, $nameMantisProject));
               $this->setCustom_fields([
                     $custom
               ]);
            } else {

               $custom = new PluginMantisStructcustomField();
               $custom->setValue($this->getInfoFromTicket($champsGlpi, $champsUrl, $ticket,
                                                          $itilCategorie, $followFollow, $followTask,
                                                          $followTitle, $followDescription, $followCategorie,
                                                          $followLinkedticket, $itemType));
               $custom->setField($ws->getCustomFieldByNameAndProject($champsGlpi, $nameMantisProject));
               $this->setCustom_fields([
                     $custom
               ]);
            }
         }

         // add issue
         $idIssueCreate = $ws->addIssue($this);

         // if mantis issue is not created
         if (! $idIssueCreate) {
            return __("Error: The process was interrupted", "mantis");
         } else {

            // create a link glpi -> mantis
            $post['items_id'] = $idTicket;
            $post['idMantis'] = $idIssueCreate;
            $post['dateEscalade'] = $date;
            $post['user'] = $idUser;
            $post['itemtype'] = $itemType;

            $res = $mantis->add($post);
            $id_mantis[] = $res;

            // if the link is not created
            if (! $res) {
               $ws->deleteIssue($idIssueCreate);
               return __("Error: The process was interrupted", "mantis");
            } else {

               if ($followLinkedticket == 'true' && $itemType == "Ticket") {

                  $tickets = Ticket_Ticket::getLinkedTicketsTo($ticket->fields['id']);

                  foreach ($tickets as $link_ticket) {
                     $t = new ticket();
                     $t->getFromDB($link_ticket['tickets_id']);

                     $mantis1 = new PluginMantisMantis();
                     $post['items_id'] = $t->fields['id'];
                     $post['idMantis'] = $idIssueCreate;
                     $post['dateEscalade'] = $date;
                     $post['user'] = $idUser;
                     $post['itemtype'] = $itemType;

                     $id_mantis[] = $mantis1->add($post);
                     unset($post);
                  }
               }

               $error = "";

               // take care of notes
               if ($this->needNote($champsUrl, $champsGlpi)) {
                  $id_note = $this->createNote($champsGlpi, $ticket, $itilCategorie, $champsUrl,
                                               $ws, $idIssueCreate, $followFollow, $followTask,
                                               $followTitle, $followDescription, $followCategorie,
                                               $followLinkedticket, $itemType);
                  // if there is an error at the moment of creating the note
                  if (! $id_note) {
                     $error .= __("Error creating the note, the process was interrupted", "mantis");
                  }
               }

               // check if we follow the attachemnts
               if ($followAttachment == 'true') {

                  // follow attachment for ticket
                  $error .= $this->addAttachment($idTicket, $error, $ws, $idIssueCreate, $itemType);

                  // follow attachment for ticket linked
                  if ($followLinkedticket == 'true' && $itemType == "Ticket") {
                     $tickets = Ticket_Ticket::getLinkedTicketsTo($ticket->fields['id']);
                     foreach ($tickets as $link_ticket) {
                        $error .= $this->addAttachment($link_ticket['tickets_id'], $error, $ws,
                                                       $idIssueCreate, $itemType);
                     }
                  }
               }

               if ($error != "") {

                  try {
                     foreach ($id_attachment as &$id) {
                        $ws->deleteAttachment($id);
                     }
                  } catch (Exception $e) {
                     Toolbox::logDebug($e);
                  }

                  try {
                     $ws->deleteNote($id_note);
                  } catch (Exception $e) {
                     Toolbox::logDebug($e);
                  }

                  try {

                     foreach ($id_mantis as $idMan) {
                        $post['id'] = $idMan;
                        $mantis->delete($post);
                     }
                  } catch (Exception $e) {
                     Toolbox::logDebug($e);
                  }

                  try {
                     $ws->deleteIssue($idIssueCreate);
                  } catch (Exception $e) {
                     Toolbox::logDebug($e);
                  }

                  return $error;
               } else {

                  // update ticket status if asked
                  if ($conf->fields['status_after_escalation'] != 0) {
                     $ticket->update([
                           'id' => $ticket->fields['id'],
                           'status' => $conf->fields['status_after_escalation']
                     ]);

                     if ($followLinkedticket == 'true' && $itemType == "Ticket") {
                        $tickets = Ticket_Ticket::getLinkedTicketsTo($ticket->fields['id']);

                        foreach ($tickets as $link_ticket) {
                           $t = new ticket();
                           $t->getFromDB($link_ticket['tickets_id']);
                           $t->update([
                                 'id' => $t->fields['id'],
                                 'status' => $conf->fields['status_after_escalation']
                           ]);
                        }
                     }
                  }

                  return true;
               }
            }
         }
      } else {

         //TRANS: %1$s is the MantisBT project name
         Toolbox::logInFile('mantis', sprintf(__('Project \'%1$s\' does not exist.', 'mantis'), $nameMantisProject) . "\n");
         echo sprintf(__('Project \'%1$s\' does not exist.', 'mantis'), $nameMantisProject);
      }
   }

   private function addAttachment($idTicket, $error, $ws, $idIssueCreate, $itemType) {
      global $DB;

      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      $query = "SELECT `glpi_documents_items`.*
            FROM `glpi_documents_items`,`glpi_documents`
            WHERE `glpi_documents`.`id` = `glpi_documents_items`.`documents_id`
            AND `glpi_documents_items`.`itemtype` = '" . $itemType . "'
            AND `glpi_documents_items`.`items_id` = '" . Toolbox::cleanInteger($idTicket) . "'";

      if ($conf->fields['doc_categorie'] != 0) {
         $query.= " AND `glpi_documents`.`documentcategories_id` = '"
                        . Toolbox::cleanInteger($conf->fields['doc_categorie']) . "'";
      }

      $res = $DB->query($query);

      if ($res->num_rows > 0) {
         while ($row = $res->fetch_assoc()) {
            $doc = new Document();
            $doc->getFromDB($row["documents_id"]);
            $path = GLPI_DOC_DIR . "/" . $doc->getField('filepath');

            if (file_exists($path)) {

               $data = file_get_contents($path);
               if (! $data) {

                  Toolbox::logInFile('mantis', sprintf(
                     __('Can\'t load the attachment [%1$s] to MantisBT, the process was interrupted.', 'mantis'),
                              $doc->getField('filename')));

                  $error .= sprintf(
                     __('Can\'t load the attachment [%1$s] to MantisBT, the process was interrupted.', 'mantis'),
                              $doc->getField('filename'));
               } else {

                  // $data = base64_encode($data);
                  $id_data = $ws->addAttachmentToIssue($idIssueCreate, $doc->getField('filename'), $doc->getField('mime'), $data);

                  if (! $id_data) {
                     $id_attachment[] = $id_data;
                     Toolbox::logInFile('mantis', sprintf(
                        __('Can\'t send the attachment [%1$s] to MantisBT, the process was interrupted.', 'mantis'),
                              $doc->getField('filename')));

                     $error .= sprintf(
                        __('Can\'t send the attachment [%1$s] to MantisBT, the process was interrupted.', 'mantis'),
                              $doc->getField('filename'));
                  }
               }
            } else {

               Toolbox::logInFile('mantis', sprintf(
                  __('Attachment [%1$s] doesn\'t exists, the process was interrupted.', 'mantis'),
                           $doc->getField('filename')));

               $error .= sprintf(
                  __('Attachment [%1$s] doesn\'t exists, the process was interrupted.', 'mantis'),
                           $doc->getField('filename'));
            }
         }
      }

      return $error;
   }

   private function getAdditionalInfo($champsGlpi, $champsUrl, $ticket, $itilCategorie, $followFollow, $followTask, $followTitle, $followDescription, $followCategorie, $linkedTicket, $itemType) {
      $infoTicket = "";
      global $CFG_GLPI;

      if ($champsGlpi == 'additional_information') {

         if ($followTitle == 'true') {
            $infoTicket .= sprintf(__('Title = %1$s <br/>', 'mantis'), $ticket->fields["name"]);
         }

         if ($followDescription == 'true') {
            $infoTicket .= sprintf(__('Description = %1$s <br/>', 'mantis'), $ticket->fields["content"]);
         }

         if ($followFollow == 'true' && $itemType == "Ticket") {
            $infoTicket .= $this->getFollowUpFromticket($ticket);
         }

         if ($followTask == 'true') {
            $infoTicket .= $this->getTaskFromticket($ticket, $itemType);
         }

         if ($followCategorie == 'true') {
            if ($itilCategorie->getFromDB($ticket->fields['itilcategories_id'])) {
               $infoTicket .= sprintf(__('Category = %1$s <br/>', 'mantis'), $itilCategorie->fields["name"]);
            }
         }
      }

      if ($champsUrl == 'additional_information') {
         $infoTicket .= sprintf(__('Link to GLPi object = %1$s <br/>', 'mantis'), $_SERVER['HTTP_REFERER']);
      }

      if ($linkedTicket == 'true') {

         $tickets = Ticket_Ticket::getLinkedTicketsTo($ticket->fields['id']);

         foreach ($tickets as $link_ticket) {
            $t = new ticket();
            $t->getFromDB($link_ticket['tickets_id']);

            $infoTicket .= "<br/>";

            if ($champsGlpi == 'additional_information') {

               if ($followTitle == 'true') {
                  $infoTicket .= sprintf(__('Title = %1$s <br/>', 'mantis'), $t->fields["name"]);
               }

               if ($followDescription == 'true') {
                  $infoTicket .= sprintf(__('Description = %1$s <br/>', 'mantis'), $t->fields["content"]);
               }

               if ($followFollow == 'true') {
                  $infoTicket .= $this->getFollowUpFromticket($t);
               }

               if ($followTask == 'true') {
                  $infoTicket .= $this->getTaskFromticket($t, $itemType);
               }

               if ($followCategorie == 'true') {
                  if ($itilCategorie->getFromDB($t->fields['itilcategories_id'])) {
                     $infoTicket .= sprintf(__('Category = %1$s <br/>', 'mantis'), $itilCategorie->fields["name"]);
                  }
               }
            }

            if ($champsUrl == 'additional_information') {
               $infoTicket .= sprintf(__('Link to GLPi object = %1$s <br/>', 'mantis'),
                                      str_replace('id=' . $ticket->fields['id'],
                                                  'id=' . $t->fields['id'],
                                                  $_SERVER['HTTP_REFERER']));
            }
         }
      }

      return $infoTicket;
   }

   private function getInfoFromTicket($champsGlpi, $champsUrl, $ticket, $itilCategorie, $followFollow, $followTask, $followTitle, $followDescription, $followCategorie, $linkedTicket, $itemType) {
      $infoTicket = "";
      global $CFG_GLPI;

      if ($champsGlpi != 'additional_information' && $champsGlpi != 'note') {

         if ($followTitle == 'true') {
            $infoTicket .= sprintf(__('Title = %1$s <br/>', 'mantis'), $ticket->fields["name"]);
         }

         if ($followDescription == 'true') {
            $infoTicket .= sprintf(__('Description = %1$s <br/>', 'mantis'), $ticket->fields["content"]);
         }

         if ($followFollow == 'true' && $itemType == "Ticket") {
            $infoTicket .= $this->getFollowUpFromticket($ticket);
         }

         if ($followTask == 'true') {
            $infoTicket .= $this->getTaskFromticket($ticket, $itemType);
         }

         if ($followCategorie == 'true') {
            if ($itilCategorie->getFromDB($ticket->fields['itilcategories_id'])) {
               $infoTicket .= sprintf(__('Category = %1$s <br/>', 'mantis'), $itilCategorie->fields["name"]);
            }
         }
      }

      if ($champsUrl != 'additional_information' && $champsUrl != 'note') {
         $infoTicket .= sprintf(__('Link to GLPi object = %1$s <br/>', 'mantis'), $_SERVER['HTTP_REFERER']);
      }

      if ($linkedTicket == 'true') {

         $tickets = Ticket_Ticket::getLinkedTicketsTo($ticket->fields['id']);

         foreach ($tickets as $link_ticket) {

            $t = new ticket();
            $t->getFromDB($link_ticket['tickets_id']);

            $infoTicket .= "<br/>";

            if ($champsGlpi != 'additional_information' && $champsGlpi != 'note') {

               if ($followTitle == 'true') {
                  $infoTicket .= sprintf(__('Title = %1$s <br/>', 'mantis'), $t->fields["name"]);
               }

               if ($followDescription == 'true') {
                  $infoTicket .= sprintf(__('Description = %1$s <br/>', 'mantis'), $t->fields["content"]);
               }

               if ($followFollow == 'true') {
                  $infoTicket .= $this->getFollowUpFromticket($t);
               }

               if ($followTask == 'true') {
                  $infoTicket .= $this->getTaskFromticket($t, $itemType);
               }

               if ($followCategorie == 'true') {
                  if ($itilCategorie->getFromDB($t->fields['itilcategories_id'])) {
                     $infoTicket .= sprintf(__('Category = %1$s <br/>', 'mantis'), $_SERVER['HTTP_REFERER']);
                  }
               }
            }

            if ($champsUrl != 'additional_information' && $champsUrl != 'note') {
               $infoTicket .= sprintf(__('Link to GLPi object = %1$s <br/>', 'mantis'),
                                      str_replace('id=' . $ticket->fields['id'],
                                                  'id=' . $t->fields['id'],
                                                  $_SERVER['HTTP_REFERER']));
            }
         }
      }

      return $infoTicket;
   }

   private function createNote($champsGlpi, $ticket, $itilCategorie, $champsUrl, $ws, $idIssueCreate, $followFollow, $followTask, $followTitle, $followDescription, $followCategorie, $linkedTicket, $itemType) {
      global $CFG_GLPI;
      $note = "";

      if ($champsGlpi == 'note') {

         if ($followTitle == 'true') {
            $note .= sprintf(__('Title = %1$s <br/>', 'mantis'), $ticket->fields["name"]);
         }

         if ($followDescription == 'true') {
            $note .= sprintf(__('Description = %1$s <br/>', 'mantis'), $ticket->fields["content"]);
         }

         if ($followFollow == 'true' && $itemType == "Ticket") {
            $note .= $this->getFollowUpFromticket($ticket);
         }

         if ($followTask == 'true') {
            $note .= $this->getTaskFromticket($ticket, $itemType);
         }

         if ($followCategorie == 'true') {
            if ($itilCategorie->getFromDB($ticket->fields['itilcategories_id'])) {
               $note .= sprintf(__('Category = %1$s <br/>', 'mantis'), $itilCategorie->fields["name"]);
            }
         }
      }

      if ($champsUrl == 'note') {
         $note .= sprintf(__('Link to GLPi object = %1$s <br/>', 'mantis'), $_SERVER['HTTP_REFERER']);
      }

      if ($linkedTicket == 'true') {

         $tickets = Ticket_Ticket::getLinkedTicketsTo($ticket->fields['id']);

         foreach ($tickets as $link_ticket) {

            $note .= "<br/>";
            $t = new ticket();
            $t->getFromDB($link_ticket['tickets_id']);

            if ($champsGlpi == 'note') {

               if ($followTitle == 'true') {
                  $note .= sprintf(__('Title = %1$s <br/>', 'mantis'), $t->fields["name"]);
               }

               if ($followDescription == 'true') {
                  $note .= sprintf(__('Description = %1$s <br/>', 'mantis'), $t->fields["content"]);
               }

               if ($followFollow == 'true') {
                  $note .= $this->getFollowUpFromticket($t);
               }

               if ($followTask == 'true') {
                  $note .= $this->getTaskFromticket($t, $itemType);
               }

               if ($followCategorie == 'true') {
                  if ($itilCategorie->getFromDB($t->fields['itilcategories_id'])) {
                     $note .= sprintf(__('Category = %1$s <br/>', 'mantis'), $itilCategorie->fields["name"]);
                  }
               }
            }

            if ($champsUrl == 'note') {
               $note .= sprintf(__('Link to GLPi object = %1$s <br/>', 'mantis'),
                                str_replace('id=' . $ticket->fields['id'],
                                            'id=' . $t->fields['id'],
                                            $_SERVER['HTTP_REFERER']));
            }
         }
      }

      if ($note != "") {

         $issueNote = new PluginMantisStructissuenotedata();
         $issueNote->setDate_submitted(date("Y-m-d"));
         $issueNote->setText($note);
         return $ws->addNoteToIssue($idIssueCreate, $issueNote);
      } else {

         return true;
      }
   }

   static function getLinkToticket($ticket) {
      global $CFG_GLPI;
      return '<a id="link" href=' . $CFG_GLPI['root_doc'] . '/front/ticket.form.php?id=' . $ticket->fields['id'] . ' target="_blank">' . $ticket->getname() . '</a>';
   }

   /**
    * Function to determine if the issue need a note
    *
    * @param $champsGlpi
    * @param $champsUrl
    * @return bool
    */
   private function needNote($champsGlpi, $champsUrl) {
      if ($champsGlpi == 'note' || $champsUrl == 'note') {
         return true;
      } else {
         return false;
      }
   }

   /**
    * Function to get the information of ticket followup
    *
    * @param $ticket
    * @return string content
    */
   private function getFollowUpFromticket($ticket) {
      global $DB;

      $content = '';

      $res = $DB->query("SELECT *
                        FROM `glpi_itilfollowups`
                        WHERE `items_id` = '" . Toolbox::cleanInteger($ticket->fields["id"]) . "'
                        AND `itemtype` = 'Ticket'");

      if ($res->num_rows > 0) {

         while ($row = $res->fetch_assoc()) {

            $ticket_followUp = new ITILFollowup();
            $ticket_followUp->getFromDB($row["id"]);

            $request_type = new RequestType();
            $request_type->getFromDB($row["requesttypes_id"]);

            $user = new User();
            $user->getFromDB($row["users_id"]);

            $content .= sprintf(
               __('Followups = %1$s -> date : %2$s, request type : %3$s, user : %4$s, content : %5$s<br/>', 'mantis'),
                        $ticket_followUp->fields['id'],
                        $ticket_followUp->fields['date'],
                        $request_type->fields['name'],
                        $user->getName(),
                        $ticket_followUp->getField('content'));
         }
      } else {
         $content .= __("No follow-up", "mantis");
      }

      return $content;
   }

   /**
    * Function to get tasks string from glpi ticket
    *
    * @param $ticket
    */
   private function getTaskFromticket($ticket, $itemType) {
      global $DB;

      $content = '';

      $res = $DB->query("SELECT `glpi_tickettasks`.*
                        FROM `glpi_tickettasks`
                        WHERE `glpi_tickettasks`.`tickets_id` = '" . Toolbox::cleanInteger($ticket->fields["id"]) . "'");

      if ($res->num_rows > 0) {
         while ($row = $res->fetch_assoc()) {

            if ($itemType == "Ticket") {
               $task = new TicketTask();
            } else {
               $task = new ProblemTask();
            }

            $task->getFromDB($row["id"]);

            $user = new User();
            $user->getFromDB($row["users_id"]);

            $content .= sprintf(
               __('Task = %1$s -> date : %2$s, description : %3$s, time : %4$s<br/>', 'mantis'),
                        $task->fields['id'],
                        $task->fields['date'],
                        $task->fields['content'],
                        Html::timestampToString($task->fields['actiontime']));
         }
      } else {
         $content .= __("No task", "mantis");
      }

      return $content;
   }

   /**
    * Get id value
    *
    * @return integer|null
    */
   public function getId() {
      return $this->id;
   }

   /**
    * Set id value
    *
    * @param integer $_id the id
    * @return integer
    */
   public function setId($_id) {
      return ($this->id = $_id);
   }

   /**
    * Get view_state value
    *
    * @return MantisStructObjectRef|null
    */
   public function getView_state() {
      return $this->view_state;
   }

   /**
    * Set view_state value
    *
    * @param MantisStructObjectRef $_view_state the view_state
    * @return MantisStructObjectRef
    */
   public function setView_state($_view_state) {
      return ($this->view_state = $_view_state);
   }

   /**
    * Get last_updated value
    *
    * @return dateTime|null
    */
   public function getLast_updated() {
      return $this->last_updated;
   }

   /**
    * Set last_updated value
    *
    * @param dateTime $_last_updated the last_updated
    * @return dateTime
    */
   public function setLast_updated($_last_updated) {
      return ($this->last_updated = $_last_updated);
   }

   /**
    * Get project value
    *
    * @return MantisStructObjectRef|null
    */
   public function getProject() {
      return $this->project;
   }

   /**
    * Set project value
    *
    * @param MantisStructObjectRef $_project the project
    * @return MantisStructObjectRef
    */
   public function setProject($_project) {
      return ($this->project = $_project);
   }

   /**
    * Get category value
    *
    * @return string|null
    */
   public function getCategory() {
      return $this->category;
   }

   /**
    * Set category value
    *
    * @param string $_category the category
    * @return string
    */
   public function setCategory($_category) {
      return ($this->category = $_category);
   }

   /**
    * Get priority value
    *
    * @return MantisStructObjectRef|null
    */
   public function getPriority() {
      return $this->priority;
   }

   /**
    * Set priority value
    *
    * @param MantisStructObjectRef $_priority the priority
    * @return MantisStructObjectRef
    */
   public function setPriority($_priority) {
      return ($this->priority = $_priority);
   }

   /**
    * Get severity value
    *
    * @return MantisStructObjectRef|null
    */
   public function getSeverity() {
      return $this->severity;
   }

   /**
    * Set severity value
    *
    * @param MantisStructObjectRef $_severity the severity
    * @return MantisStructObjectRef
    */
   public function setSeverity($_severity) {
      return ($this->severity = $_severity);
   }

   /**
    * Get status value
    *
    * @return MantisStructObjectRef|null
    */
   public function getStatus() {
      return $this->status;
   }

   /**
    * Set status value
    *
    * @param MantisStructObjectRef $_status the status
    * @return MantisStructObjectRef
    */
   public function setStatus($_status) {
      return ($this->status = $_status);
   }

   /**
    * Get reporter value
    *
    * @return MantisStructAccountData|null
    */
   public function getReporter() {
      return $this->reporter;
   }

   /**
    * Set reporter value
    *
    * @param MantisStructAccountData $_reporter the reporter
    * @return MantisStructAccountData
    */
   public function setReporter($_reporter) {
      return ($this->reporter = $_reporter);
   }

   /**
    * Get summary value
    *
    * @return string|null
    */
   public function getSummary() {
      return $this->summary;
   }

   /**
    * Set summary value
    *
    * @param string $_summary the summary
    * @return string
    */
   public function setSummary($_summary) {
      return ($this->summary = $_summary);
   }

   /**
    * Get version value
    *
    * @return string|null
    */
   public function getVersion() {
      return $this->version;
   }

   /**
    * Set version value
    *
    * @param string $_version the version
    * @return string
    */
   public function setVersion($_version) {
      return ($this->version = $_version);
   }

   /**
    * Get build value
    *
    * @return string|null
    */
   public function getBuild() {
      return $this->build;
   }

   /**
    * Set build value
    *
    * @param string $_build the build
    * @return string
    */
   public function setBuild($_build) {
      return ($this->build = $_build);
   }

   /**
    * Get platform value
    *
    * @return string|null
    */
   public function getPlatform() {
      return $this->platform;
   }

   /**
    * Set platform value
    *
    * @param string $_platform the platform
    * @return string
    */
   public function setPlatform($_platform) {
      return ($this->platform = $_platform);
   }

   /**
    * Get os value
    *
    * @return string|null
    */
   public function getOs() {
      return $this->os;
   }

   /**
    * Set os value
    *
    * @param string $_os the os
    * @return string
    */
   public function setOs($_os) {
      return ($this->os = $_os);
   }

   /**
    * Get os_build value
    *
    * @return string|null
    */
   public function getOs_build() {
      return $this->os_build;
   }

   /**
    * Set os_build value
    *
    * @param string $_os_build the os_build
    * @return string
    */
   public function setOs_build($_os_build) {
      return ($this->os_build = $_os_build);
   }

   /**
    * Get reproducibility value
    *
    * @return MantisStructObjectRef|null
    */
   public function getReproducibility() {
      return $this->reproducibility;
   }

   /**
    * Set reproducibility value
    *
    * @param MantisStructObjectRef $_reproducibility the reproducibility
    * @return MantisStructObjectRef
    */
   public function setReproducibility($_reproducibility) {
      return ($this->reproducibility = $_reproducibility);
   }

   /**
    * Get date_submitted value
    *
    * @return dateTime|null
    */
   public function getDate_submitted() {
      return $this->date_submitted;
   }

   /**
    * Set date_submitted value
    *
    * @param dateTime $_date_submitted the date_submitted
    * @return dateTime
    */
   public function setDate_submitted($_date_submitted) {
      return ($this->date_submitted = $_date_submitted);
   }

   /**
    * Get sponsorship_total value
    *
    * @return integer|null
    */
   public function getSponsorship_total() {
      return $this->sponsorship_total;
   }

   /**
    * Set sponsorship_total value
    *
    * @param integer $_sponsorship_total the sponsorship_total
    * @return integer
    */
   public function setSponsorship_total($_sponsorship_total) {
      return ($this->sponsorship_total = $_sponsorship_total);
   }

   /**
    * Get handler value
    *
    * @return MantisStructAccountData|null
    */
   public function getHandler() {
      return $this->handler;
   }

   /**
    * Set handler value
    *
    * @param MantisStructAccountData $_handler the handler
    * @return MantisStructAccountData
    */
   public function setHandler($_handler) {
      return ($this->handler = $_handler);
   }

   /**
    * Get projection value
    *
    * @return MantisStructObjectRef|null
    */
   public function getProjection() {
      return $this->projection;
   }

   /**
    * Set projection value
    *
    * @param MantisStructObjectRef $_projection the projection
    * @return MantisStructObjectRef
    */
   public function setProjection($_projection) {
      return ($this->projection = $_projection);
   }

   /**
    * Get eta value
    *
    * @return MantisStructObjectRef|null
    */
   public function getEta() {
      return $this->eta;
   }

   /**
    * Set eta value
    *
    * @param MantisStructObjectRef $_eta the eta
    * @return MantisStructObjectRef
    */
   public function setEta($_eta) {
      return ($this->eta = $_eta);
   }

   /**
    * Get resolution value
    *
    * @return MantisStructObjectRef|null
    */
   public function getResolution() {
      return $this->resolution;
   }

   /**
    * Set resolution value
    *
    * @param MantisStructObjectRef $_resolution the resolution
    * @return MantisStructObjectRef
    */
   public function setResolution($_resolution) {
      return ($this->resolution = $_resolution);
   }

   /**
    * Get fixed_in_version value
    *
    * @return string|null
    */
   public function getFixed_in_version() {
      return $this->fixed_in_version;
   }

   /**
    * Set fixed_in_version value
    *
    * @param string $_fixed_in_version the fixed_in_version
    * @return string
    */
   public function setFixed_in_version($_fixed_in_version) {
      return ($this->fixed_in_version = $_fixed_in_version);
   }

   /**
    * Get target_version value
    *
    * @return string|null
    */
   public function getTarget_version() {
      return $this->target_version;
   }

   /**
    * Set target_version value
    *
    * @param string $_target_version the target_version
    * @return string
    */
   public function setTarget_version($_target_version) {
      return ($this->target_version = $_target_version);
   }

   /**
    * Get description value
    *
    * @return string|null
    */
   public function getDescription() {
      return $this->description;
   }

   /**
    * Set description value
    *
    * @param string $_description the description
    * @return string
    */
   public function setDescription($_description) {
      return ($this->description = $_description);
   }

   /**
    * Get steps_to_reproduce value
    *
    * @return string|null
    */
   public function getSteps_to_reproduce() {
      return $this->steps_to_reproduce;
   }

   /**
    * Set steps_to_reproduce value
    *
    * @param string $_steps_to_reproduce the steps_to_reproduce
    * @return string
    */
   public function setSteps_to_reproduce($_steps_to_reproduce) {
      return ($this->steps_to_reproduce = $_steps_to_reproduce);
   }

   /**
    * Get additional_information value
    *
    * @return string|null
    */
   public function getAdditional_information() {
      return $this->additional_information;
   }

   /**
    * Set additional_information value
    *
    * @param string $_additional_information the additional_information
    * @return string
    */
   public function setAdditional_information($_additional_information) {
      return ($this->additional_information .= $_additional_information);
   }

   /**
    * Get attachments value
    *
    * @return Array|null
    */
   public function getAttachments() {
      return $this->attachments;
   }

   /**
    * Set attachments value
    *
    * @param Array $_attachments the attachments
    * @return Array
    */
   public function setAttachments($_attachments) {
      return ($this->attachments = $_attachments);
   }

   /**
    * Get relationships value
    *
    * @return Array|null
    */
   public function getRelationships() {
      return $this->relationships;
   }

   /**
    * Set relationships value
    *
    * @param Array $_relationships the relationships
    * @return Array
    */
   public function setRelationships($_relationships) {
      return ($this->relationships = $_relationships);
   }

   /**
    * Get notes value
    *
    * @return Array|null
    */
   public function getNotes() {
      return $this->notes;
   }

   /**
    * Set notes value
    *
    * @param Array $_notes the notes
    * @return Array
    */
   public function setNotes($_notes) {
      return ($this->notes .= $_notes);
   }

   /**
    * Get custom_fields value
    *
    * @return Array|null
    */
   public function getCustom_fields() {
      return $this->custom_fields;
   }

   /**
    * Set custom_fields value
    *
    * @param Array $_custom_fields the custom_fields
    * @return Array
    */
   public function setCustom_fields($_custom_fields) {
      return ($this->custom_fields = $_custom_fields);
   }

   /**
    * Get due_date value
    *
    * @return dateTime|null
    */
   public function getDue_date() {
      return $this->due_date;
   }

   /**
    * Set due_date value
    *
    * @param dateTime $_due_date the due_date
    * @return dateTime
    */
   public function setDue_date($_due_date) {
      return ($this->due_date = $_due_date);
   }

   /**
    * Get monitors value
    *
    * @return Array|null
    */
   public function getMonitors() {
      return $this->monitors;
   }

   /**
    * Set monitors value
    *
    * @param Array $_monitors the monitors
    * @return Array
    */
   public function setMonitors($_monitors) {
      return ($this->monitors = $_monitors);
   }

   /**
    * Get sticky value
    *
    * @return boolean|null
    */
   public function getSticky() {
      return $this->sticky;
   }

   /**
    * Set sticky value
    *
    * @param boolean $_sticky the sticky
    * @return boolean
    */
   public function setSticky($_sticky) {
      return ($this->sticky = $_sticky);
   }

   /**
    * Get tags value
    *
    * @return Array|null
    */
   public function getTags() {
      return $this->tags;
   }

   /**
    * Set tags value
    *
    * @param Array $_tags the tags
    * @return Array
    */
   public function setTags($_tags) {
      return ($this->tags = $_tags);
   }

   /**
    * Method returning the class name
    *
    * @return string __CLASS__
    */
   public function __toString() {
      return __CLASS__;
   }
}
