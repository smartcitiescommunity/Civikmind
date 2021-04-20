<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

$resource             = new PluginResourcesResource();
$employee             = new PluginResourcesEmployee();
$choice               = new PluginResourcesChoice();
$resourcehabilitation = new PluginResourcesResourceHabilitation();

$resource->checkGlobal(READ);

if (Session::getCurrentInterface() == 'central') {
   //from central
   Html::header(PluginResourcesResource::getTypeName(2), '', "admin", PluginResourcesMenu::getType());
} else {
   //from helpdesk
   Html::helpHeader(PluginResourcesResource::getTypeName(2));
}

if (isset($_POST["first_step"]) || isset($_GET["first_step"])) {
   if (!isset($_POST["template"])) {
      $_POST["template"]     = $_GET["template"];
   }
   if (!isset($_POST["withtemplate"])) {
      $_POST["withtemplate"] = $_GET["withtemplate"];
   }

   // Set default value...
   $values = ['name'                                     => '',
              'firstname'                                => '',
              'comment'                                  => '',
              'locations_id'                             => 0,
              'users_id'                                 => 0,
              'users_id_sales'                           => 0,
              'plugin_resources_departments_id'          => 0,
              'date_begin'                               => 'NULL',
              'date_end'                                 => 'NULL',
              'quota'                                    => 1.0000,
              'plugin_resources_resourcesituations_id'   => 0,
              'plugin_resources_contractnatures_id'      => 0,
              'plugin_resources_ranks_id'                => 0,
              'plugin_resources_resourcespecialities_id' => 0,
              'plugin_resources_leavingreasons_id'       => 0,
              'plugin_resources_habilitations_id'        => 0,
              'sensitize_security'                       => 0,
              'read_chart'                               => 0,
   ];

   // Clean text fields
   $values['name']    = stripslashes($values['name']);
   $values['comment'] = Html::cleanPostForTextArea($values['comment']);

   $values['target']       = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
   $values['withtemplate'] = $_POST["withtemplate"];
   $values['new']          = 1;
   $resource->wizardSecondForm($_POST["template"], $values);

} else if (isset($_POST["undo_first_step"])) {
   //if ($resource->getfromDB($_POST["id"]))
   //$resource->deleteByCriteria(array('id' => $_POST["id"]));
   $resource->wizardFirstForm();

} else if (isset($_POST["second_step"]) || isset($_POST["second_step_update"])) {
   $required = $resource->checkRequiredFields($_POST);
   if (count($required) > 0) {
      // Set default value...
      foreach ($_POST as $key => $val) {
         $values[$key] = $val;
      }

      // Clean text fields
      $values['name']    = stripslashes($values['name']);
      $values['comment'] = Html::cleanPostForTextArea($values['comment']);

      $values['target']       = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
      $values['withtemplate'] = $_POST["withtemplate"];

      if (isset($_POST["second_step"])) {
         $values['new'] = 1;
      } else if (isset($_POST["second_step_update"])) {
         $values['new'] = 0;
      }

      $values["requiredfields"] = 1;

      $_SESSION["MESSAGE_AFTER_REDIRECT"][ERROR] = ["<h3><span class='red'>".
              __('Required fields are not filled. Please try again.', 'resources')."</span></h3>"];

      Html::displayMessageAfterRedirect();

      $resource->wizardSecondForm($_POST["id"], $values);

   } else {
      if ($resource->canCreate() && isset($_POST["second_step"])) {
         $newID = $resource->add($_POST);
      } else if ($resource->canCreate() && isset($_POST["second_step_update"])) {
         $resource->update($_POST);
         $newID = $_POST["id"];
      }
      //if employee right : next step
      if ($newID) {
         $wizard_employee     = PluginResourcesContractType::checkWizardSetup($newID, "use_employee_wizard");
         $wizard_need         = PluginResourcesContractType::checkWizardSetup($newID, "use_need_wizard");
         $wizard_picture      = PluginResourcesContractType::checkWizardSetup($newID, "use_picture_wizard");
         $wizard_habilitation = PluginResourcesContractType::checkWizardSetup($newID, "use_habilitation_wizard");

         if ($employee->canCreate() && $wizard_employee) {
            $employee->wizardThirdForm($newID);
         } else if ($wizard_need) {
            $choice->wizardFourForm($newID);
         } else if ($wizard_picture) {
            $values           = [];
            $values['target'] = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
            $resource->wizardFiveForm($newID, $values);
         } else if ($wizard_habilitation) {
            $resourcehabilitation->wizardSixForm($newID);
         } else {
            $resource->fields['resources_step'] = 'second_step';
            Plugin::doHook('item_show', $resource);
            $resource->redirectToList();
         }
      } else {
         Html::back();
      }
   }

} else if (isset($_POST["undo_second_step"])) {
   // Set default value...
   $values['target']       = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
   $values['withtemplate'] = 0;
   $values['new']          = 0;

   $resource->wizardSecondForm($_POST["plugin_resources_resources_id"], $values);

} else if (isset($_POST["third_step"])) {
   if (isset($_POST['id']) && $_POST['id'] > 0) {
      $employee->update($_POST);
   } else {
      $newid = $employee->add($_POST);
   }

   $wizard_need         = PluginResourcesContractType::checkWizardSetup($_POST["plugin_resources_resources_id"],
                                                                        "use_need_wizard");
   $wizard_picture      = PluginResourcesContractType::checkWizardSetup($_POST["plugin_resources_resources_id"],
                                                                        "use_picture_wizard");
   $wizard_habilitation = PluginResourcesContractType::checkWizardSetup($_POST["plugin_resources_resources_id"],
                                                                        "use_habilitation_wizard");

   if ($wizard_need) {
      $choice->wizardFourForm($_POST["plugin_resources_resources_id"]);
   } else if ($wizard_picture) {
      $values           = [];
      $values['target'] = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
      $resource->wizardFiveForm($_POST["plugin_resources_resources_id"], $values);
   } else if ($wizard_habilitation) {
      $resourcehabilitation->wizardSixForm($_POST["plugin_resources_resources_id"]);
   } else {
      $resource->fields['plugin_resources_resources_id'] = $_POST['plugin_resources_resources_id'];
      $resource->fields['resources_step']                = 'third_step';
      Plugin::doHook('item_show', $resource);
      $resource->redirectToList();
   }

} else if (isset($_POST["four_step"])) {
   $wizard_picture      = PluginResourcesContractType::checkWizardSetup($_POST["plugin_resources_resources_id"], "use_picture_wizard");
   $wizard_habilitation = PluginResourcesContractType::checkWizardSetup($_POST["plugin_resources_resources_id"], "use_habilitation_wizard");

   if ($wizard_picture) {
      $values           = [];
      $values['target'] = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
      $resource->wizardFiveForm($_POST["plugin_resources_resources_id"], $values);
   } else if ($wizard_habilitation) {
      $resourcehabilitation->wizardSixForm($_POST["plugin_resources_resources_id"]);
   } else {
      $resource->fields['plugin_resources_resources_id'] = $_POST['plugin_resources_resources_id'];
      $resource->fields['resources_step']                = 'four_step';
      Plugin::doHook('item_show', $resource);
      $resource->redirectToList();
   }
} else if (isset($_POST["undo_four_step"])) {

   $wizard_employee = PluginResourcesContractType::checkWizardSetup($_POST['plugin_resources_resources_id'],
      "use_employee_wizard");

   if($wizard_employee){
      $employee->wizardThirdForm($_POST['plugin_resources_resources_id']);
   }
   else{
      $values['target']       = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
      $values['withtemplate'] = 0;
      $values['new']          = 0;

      $resource->wizardSecondForm($_POST["plugin_resources_resources_id"], $values);
   }

} else if (isset($_POST["five_step"])) {
   $wizard_habilitation = PluginResourcesContractType::checkWizardSetup($_POST["plugin_resources_resources_id"], "use_habilitation_wizard");

   if ($wizard_habilitation) {
      $resourcehabilitation->wizardSixForm($_POST["plugin_resources_resources_id"]);
   } else {
      $resource->fields['plugin_resources_resources_id'] = $_POST['plugin_resources_resources_id'];
      $resource->fields['resources_step'] = 'five_step';
      Plugin::doHook('item_show', $resource);
      $resource->redirectToList();
   }

} else if(isset($_POST["undo_five_step"])){

   $resources_id = $_POST['plugin_resources_resources_id'];

   $wizard_employee     = PluginResourcesContractType::checkWizardSetup($resources_id, "use_employee_wizard");
   $wizard_need         = PluginResourcesContractType::checkWizardSetup($resources_id, "use_need_wizard");

   if ($wizard_need) {
      $choice->wizardFourForm($resources_id);
   } else if ($wizard_employee) {
      $employee->wizardThirdForm($resources_id);
   } else {
      // Set default value...
      $values['target']       = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
      $values['withtemplate'] = 0;
      $values['new']          = 0;

      $resource->wizardSecondForm($resources_id, $values);
   }

} else if (isset($_POST["updateneedcomment"])) {
   if ($resource->canCreate()) {
      foreach ($_POST["updateneedcomment"] as $key => $val) {
         $varcomment            = "commentneed".$key;
         $values['id']          = $key;
         $values['commentneed'] = $_POST[$varcomment];
         $choice->addNeedComment($values);
      }
   }

   $choice->wizardFourForm($_POST["plugin_resources_resources_id"]);

} else if (isset($_POST["addcomment"])) {
   if ($resource->canCreate()) {
      $choice->addComment($_POST);
   }

   $choice->wizardFourForm($_POST["plugin_resources_resources_id"]);

} else if (isset($_POST["updatecomment"])) {
   if ($resource->canCreate()) {
      $choice->updateComment($_POST);
   }

   $choice->wizardFourForm($_POST["plugin_resources_resources_id"]);

} else if (isset($_POST["addchoice"])) {
   if ($resource->canCreate() && $_POST['plugin_resources_choiceitems_id'] > 0 && $_POST['plugin_resources_resources_id'] > 0) {
      $choice->addHelpdeskItem($_POST);
   }
   $choice->wizardFourForm($_POST["plugin_resources_resources_id"]);

} else if (isset($_POST["deletechoice"])) {
   if ($resource->canCreate()) {
      $choice->delete(['id' => $_POST["id"]]);
   }

   $choice->wizardFourForm($_POST["plugin_resources_resources_id"]);

   //next step : email and finish resource creation
} else if (isset($_POST["upload_five_step"])) {
   if (isset($_FILES) && isset($_FILES['picture'])) {
      if ($_FILES['picture']['type'] == "image/jpeg" || $_FILES['picture']['type'] == "image/pjpeg") {
         $max_size = Toolbox::return_bytes_from_ini_vars(ini_get("upload_max_filesize"));
         if ($_FILES['picture']['size'] <= $max_size) {
            $resource->getFromDB($_POST["plugin_resources_resources_id"]);
            $_POST['picture'] = $resource->addPhoto($resource);

            $_POST["id"] = $_POST["plugin_resources_resources_id"];
            $resource->update($_POST);
            $newID       = $_POST["id"];
         } else {
            echo "<div align='center'><b><span class='plugin_resources_date_over_color'>".
            __('Failed to send the file (probably too large)')."</span></b></div><br \>";
         }
      } else {
         echo "<div align='center'><b><span class='plugin_resources_date_over_color'>".
         __('Invalid filename')." : ".$_FILES['picture']['type']."</span></b></div><br \>";
      }
   }

   $values           = [];
   $values['target'] = Toolbox::getItemTypeFormURL('PluginResourcesWizard');

   $resource->wizardFiveForm($_POST["plugin_resources_resources_id"], $values);

} else if (isset($_POST["six_step"])) {
   $wizard_habilitation = PluginResourcesContractType::checkWizardSetup($_POST["plugin_resources_resources_id"], "use_habilitation_wizard");

   if ($wizard_habilitation && $_POST['plugin_resources_resources_id'] > 0) {
      if ($resourcehabilitation->checkRequiredFields($_POST)) {
         $resourcehabilitation->addResourceHabilitation($_POST);

         $_POST['target'] = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
         $resource->widgetSevenForm($_POST['plugin_resources_resources_id'], $_POST);

      } else {
         $_SESSION["MESSAGE_AFTER_REDIRECT"][ERROR] = ["<h3><span class='red'>".
                                                       __('Required fields are not filled. Please try again.', 'resources')."</span></h3>"];

         Html::displayMessageAfterRedirect();
         $resourcehabilitation->wizardSixForm($_POST["plugin_resources_resources_id"]);
         $continue = false;
      }

   }

} else if(isset($_POST["undo_six_step"])){

   $resources_id = $_POST['plugin_resources_resources_id'];

   $wizard_employee     = PluginResourcesContractType::checkWizardSetup($resources_id, "use_employee_wizard");
   $wizard_need         = PluginResourcesContractType::checkWizardSetup($resources_id, "use_need_wizard");
   $wizard_picture      = PluginResourcesContractType::checkWizardSetup($resources_id, "use_picture_wizard");
   $wizard_habilitation = PluginResourcesContractType::checkWizardSetup($resources_id, "use_habilitation_wizard");

   if ($wizard_picture) {
      $values           = [];
      $values['target'] = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
      $resource->wizardFiveForm($resources_id, $values);
   } else if ($wizard_need) {
      $choice->wizardFourForm($resources_id);
   } else if ($employee->canCreate() && $wizard_employee) {
      $employee->wizardThirdForm($resources_id);
   } else{
      $values['target']       = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
      $values['withtemplate'] = 0;
      $values['new']          = 0;

      $resource->wizardSecondForm($_POST["plugin_resources_resources_id"], $values);
   }

} else if(isset($_POST["add_doc_seven_step"])) {

   $document_item   = new Document_Item();
   $document_item->check(-1, CREATE, $_POST);
   $document_item->add($_POST);

   $_POST['target'] = Toolbox::getItemTypeFormURL('PluginResourcesWizard');

   $resource->widgetSevenForm($_POST['plugin_resources_resources_id'], $_POST);

} else if(isset($_POST["upload_seven_step"])){

   $doc = new Document();
   $doc->check(-1, CREATE, $_POST);
   if (isset($_POST['_filename']) && is_array($_POST['_filename'])) {
      $fic = $_POST['_filename'];
      $tag = $_POST['_tag_filename'];
      $prefix = $_POST['_prefix_filename'];
      foreach (array_keys($fic) as $key) {
         $_POST['_filename']        = [$fic[$key]];
         $_POST['_tag_filename']    = [$tag[$key]];
         $_POST['_prefix_filename'] = [$prefix[$key]];
         $newID = $doc->add($_POST);
      }
   }
   $_POST['target'] = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
   $resource->widgetSevenForm($_POST['plugin_resources_resources_id'], $_POST);

} else if(isset($_POST["seven_step"])){

   $resource->fields['plugin_resources_resources_id'] = $_POST['plugin_resources_resources_id'];
   $resource->fields['resources_step']                = 'six_step';
   Plugin::doHook('item_show', $resource);
   $resource->redirectToList();

} else if(isset($_POST["undo_seven_step"])){

   $resources_id = $_POST['plugin_resources_resources_id'];

   $wizard_employee     = PluginResourcesContractType::checkWizardSetup($resources_id, "use_employee_wizard");
   $wizard_need         = PluginResourcesContractType::checkWizardSetup($resources_id, "use_need_wizard");
   $wizard_picture      = PluginResourcesContractType::checkWizardSetup($resources_id, "use_picture_wizard");
   $wizard_habilitation = PluginResourcesContractType::checkWizardSetup($resources_id, "use_habilitation_wizard");

   if ($wizard_habilitation) {
      $resourcehabilitation->wizardSixForm($resources_id);
   } else if ($wizard_picture) {
      $values           = [];
      $values['target'] = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
      $resource->wizardFiveForm($resources_id, $values);
   } else if ($wizard_need) {
      $choice->wizardFourForm($resources_id);
   } else if ($employee->canCreate() && $wizard_employee) {
      $employee->wizardThirdForm($resources_id);
   } else{
      $values['target']       = Toolbox::getItemTypeFormURL('PluginResourcesWizard');
      $values['withtemplate'] = 0;
      $values['new']          = 0;

      $resource->wizardSecondForm($_POST["plugin_resources_resources_id"], $values);
   }

} else {
   $resource->wizardFirstForm();
}

if (Session::getCurrentInterface() == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
