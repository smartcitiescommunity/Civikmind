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
include('../../../inc/includes.php');

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$resource        = new PluginResourcesResource();
$checklist       = new PluginResourcesChecklist();
$checklistconfig = new PluginResourcesChecklistconfig();
$employee        = new PluginResourcesEmployee();
$choice          = new PluginResourcesChoice();
$resource_item   = new PluginResourcesResource_Item();
$cat             = new PluginResourcesTicketCategory();
$task            = new PluginResourcesTask();
/////////////////////////////////resource from helpdesk///////////////////////////////

if (isset($_POST["resend"])) {
   $resource->reSendResourceCreation($_POST);
   $resource->redirectToList();

   //from helpdesk
   //add items needs of a resource
} else if (isset($_POST["addhelpdeskitem"])) {
   if ($_POST['plugin_resources_choiceitems_id'] > 0 && $_POST['plugin_resources_resources_id'] > 0) {
      if ($resource->canCreate()) {
         $choice->addHelpdeskItem($_POST);
      }
   }
   Html::back();
} //from helpdesk
//delete items needs of a resource
else if (isset($_POST["deletehelpdeskitem"])) {
   if ($resource->canCreate()) {
      $choice->delete(['id' => $_POST["id"]]);
   }
   Html::back();

   ///////////////////////////////employees///////////////////////////////
   //from central
   // add employee and resource if adding employee informations from user details form
} else if (isset($_POST["addressourceandemployee"])) {
   if ($employee->canCreate()) {
      $User = new user();
      $User->getFromDB($_POST["users_id"]);
      //Check unicity by criteria control
      $_POST["name"]                              = $User->fields["realname"];
      $_POST["firstname"]                         = $User->fields["firstname"];
      $_POST["entities_id"]                       = $_SESSION["glpiactive_entity"];
      $_POST["plugin_resources_contracttypes_id"] = 0;
      $_POST["users_id"]                          = 0;
      $_POST["users_id_sales"]                    = 0;
      $_POST["date_end"]                          = "";
      $_POST["departments_id"]                    = 0;
      $_POST["is_leaving"]                        = 0;
      $_POST["users_id_recipient_leaving"]        = 0;
      $_POST["comment"]                           = "";
      $_POST["notes"]                             = "";
      $_POST["is_template"]                       = 0;
      $_POST["template_name"]                     = "";
      $_POST["is_deleted"]                        = 0;
      $_POST["withtemplate"]                      = 0;

      if ($_POST["templates_id"] > 0) {
         $resource->getFromDB($_POST["templates_id"]);
         unset($resource->fields["is_template"]);
         unset($resource->fields["date_mod"]);

         $fields = [];
         foreach ($resource->fields as $key => $value) {
            if ($value != '' && (!isset($fields[$key]) || $fields[$key] == '' || $fields[$key] == 0)) {
               $_POST[$key] = $value;
            }
         }

         $_POST["withtemplate"] = 1;
      }
      //for not create employee informations with template
      $_POST["add_from_helpdesk"]  = 1;
      $_POST["comment"]            = addslashes($_POST["comment"]);
      $_POST["locations_id"]       = $User->fields["locations_id"];
      $_POST["date_begin"]         = $_SESSION["glpi_currenttime"];
      $_POST["users_id_recipient"] = Session::getLoginUserID();
      $_POST["date_declaration"]   = $_SESSION["glpi_currenttime"];

      //add resources
      $newID = $resource->add($_POST);

      if ($newID) {
         //add link with User
         $opt["plugin_resources_resources_id"] = $newID;
         $opt["items_id"]                      = $User->fields["id"];
         $opt["itemtype"]                      = 'User';

         $resource_item->addItem($opt);

         //add employee
         $values["plugin_resources_resources_id"] = $newID;
         $values["plugin_resources_employers_id"] = $_POST["plugin_resources_employers_id"];
         $values["plugin_resources_clients_id"]   = $_POST["plugin_resources_clients_id"];
         $values["is_template"]                   = 0;
         $employee->add($values);
      }
   }

   Html::back();

   //from central
   //add employee informations from user details form or resource form
} else if (isset($_POST["addemployee"])) {
   if ($_POST['plugin_resources_resources_id'] > 0) {
      if ($employee->canCreate()) {
         $values["plugin_resources_resources_id"] = $_POST["plugin_resources_resources_id"];
         $values["plugin_resources_employers_id"] = $_POST["plugin_resources_employers_id"];
         $values["plugin_resources_clients_id"]   = $_POST["plugin_resources_clients_id"];
         $newID                                   = $employee->add($values);
      }
   }
   Html::back();
} //from central OR helpdesk
//update employee informations from user details form or resource form
else if (isset($_POST["updateemployee"])) {
   if ($_POST['plugin_resources_resources_id'] > 0) {
      if ($employee->canCreate()) {
         $values["id"]                            = $_POST['id'];
         $values["plugin_resources_resources_id"] = $_POST["plugin_resources_resources_id"];
         $values["plugin_resources_employers_id"] = $_POST["plugin_resources_employers_id"];
         $values["plugin_resources_clients_id"]   = $_POST["plugin_resources_clients_id"];
         $employee->update($values);
      }
   }
   Html::back();
} //from central
//delete employee informations from user details form or resource form
else if (isset($_POST["deleteemployee"])) {
   if ($employee->canCreate()) {
      $employee->delete($_POST, 1);
   }
   Html::back();

   /////////////////////////////////resource from central///////////////////////////////
   //add resource
} else if (isset($_POST["add"])) {
   $resource->check(-1, UPDATE, $_POST);
   $newID = $resource->add($_POST);
   Html::back();
} //from central
//update resource
else if (isset($_POST["update"])) {
   $resource->check($_POST['id'], UPDATE);
   $resource->update($_POST);
   Html::back();
} //from central
//delete resource
else if (isset($_POST["delete"])) {
   $resource->check($_POST['id'], UPDATE);
   if (!empty($_POST["withtemplate"])) {
      $resource->delete($_POST, 1);
   } else {
      $resource->delete($_POST);
   }

   if (!empty($_POST["withtemplate"])) {
      Html::redirect($CFG_GLPI["root_doc"] . "/plugins/resources/front/setup.templates.php?add=0");
   } else {
      $resource->redirectToList();
   }
} //from central
//restore resource
else if (isset($_POST["restore"])) {
   $resource->check($_POST['id'], UPDATE);
   $resource->restore($_POST);
   $resource->redirectToList();
} //from central
//purge resource template
else if (isset($_POST["purge"])) {
   $resource->check($_POST['id'], UPDATE);
   $resource->delete($_POST, 1);
   if (!empty($_POST["withtemplate"])) {
      Html::redirect($CFG_GLPI["root_doc"] . "/plugins/resources/front/setup.templates.php?add=0");
   } else {
      $resource->redirectToList();
   }
} //from central
//purge resource
else if (isset($_POST["purge"])) {
   $resource->check($_POST['id'], UPDATE);
   $resource->delete($_POST, 1);
   $resource->redirectToList();
} //from central
//add items of a resource
else if (isset($_POST["additem"])) {
   if (!empty($_POST['itemtype']) && !empty($_POST['items_id'])) {
      $resource_item->addItem($_POST);
   }
   Html::back();
} //from central
//update comment of item of a resource
else if (isset($_POST["updatecomment"])) {
   foreach ($_POST["updatecomment"] as $key => $val) {
      $varcomment = "comment" . $key;
      $resource_item->updateItem($key, $_POST[$varcomment]);
   }
   Html::back();
} //from central
//delete item of a resource
else if (isset($_POST["deleteitem"])) {

   foreach ($_POST["item"] as $key => $val) {
      if ($val == 1) {
         $resource_item->check($key, UPDATE);
         $resource_item->deleteItem($key);
      }
   }

   Html::back();
} //from central
//delete item of a resource form items detail
else if (isset($_POST["deleteresources"])) {
   $input = ['id' => $_POST["id"]];
   $resource_item->check($_POST["id"], UPDATE);
   $resource_item->deleteItem($_POST["id"]);
   Html::back();
} //from central
//add checklist from resource form
else if (isset($_POST["add_checklist_resources"])) {
   if ($checklist->canCreate()) {
      $resource->getFromDB($_POST["id"]);

      $checklistconfig->addChecklistsFromRules($resource, PluginResourcesChecklist::RESOURCES_CHECKLIST_IN);
      $checklistconfig->addChecklistsFromRules($resource, PluginResourcesChecklist::RESOURCES_CHECKLIST_OUT);
      $checklistconfig->addChecklistsFromRules($resource, PluginResourcesChecklist::RESOURCES_CHECKLIST_TRANSFER);
   }
   Html::back();
} ///////////////////////////////checklists///////////////////////////////
//from central
//add checklist
else if (isset($_POST["add_checklist"])) {
   if ($checklist->canCreate()) {
      $newID = $checklist->add($_POST);
   }
   Html::back();

   //from central
   //close checklist
} else if (isset($_POST["close_checklist"])) {
   $isfinished = PluginResourcesChecklist::checkifChecklistFinished($_POST);

   if ($isfinished) {
      PluginResourcesChecklist::createTicket($_POST);
   } else {
      Session::addMessageAfterRedirect(__('The checklist is not finished', 'resources'), true, ERROR);
   }
   Html::back();

   //from central
   //open checklist
} else if (isset($_POST["open_checklist"])) {
   if ($checklist->canCreate()) {
      $checklist->openFinishedChecklist($_POST);
   }
   Html::back();

   //from central
   //up / down checklist
} else if (isset($_POST["move"])) {
   $checklist->changeRank($_POST);
   Html::back();

} else if (isset($_POST["report"])) {
   $restrict = ["itemtype"                      => 'User',
                "plugin_resources_resources_id" => $_POST["id"]];
   $dbu        = new DbUtils();
   $linkeduser = $dbu->getAllDataFromTable('glpi_plugin_resources_resources_items', $restrict);

   if (!empty($linkeduser)) {
      $resource->sendReport($_POST);
      Session::addMessageAfterRedirect(__('Notification sent', 'resources'), true);
   } else {
      Session::addMessageAfterRedirect(__('The notification is not sent because the resource is not linked with a user', 'resources'), true, ERROR);
   }
   Html::back();

} else if (isset($_POST["delete_picture"])) {
   if (isset($_POST['picture'])) {
      $filename = GLPI_PLUGIN_DOC_DIR . "/resources/pictures/" . $_POST['picture'];
      if (file_exists($filename)) {
         if (unlink($filename)) {
            $_POST['picture'] = 'NULL';
            $resource->check($_POST['id'], UPDATE);
            $resource->update($_POST);
         }
      }
   }
   Html::back();

} else {
   $resource->checkGlobal(READ);

   if (Session::getCurrentInterface() == 'central') {
      //from central
      Html::header(PluginResourcesResource::getTypeName(2), '', "admin", PluginResourcesMenu::getType());
   } else {
      //from helpdesk
      Html::helpHeader(PluginResourcesResource::getTypeName(2));
   }

   $resource->display(['id' => $_GET["id"], 'withtemplate' => $_GET["withtemplate"]]);

   /* if (Session::getCurrentInterface() != 'central') {

     //with no template
     if ($_GET["withtemplate"]<2 && (isset($_GET['id'])&&$_GET['id']!=-1)) {

     //show employee form
     if($employee->canCreate()) {
     $employee->showFormHelpdesk($_GET["id"],1);
     }
     //show needs of a resource
     $choice->showItemHelpdesk($_GET["id"],1);

     }

     } */

   if (Session::getCurrentInterface() == 'central') {
      Html::footer();
   } else {
      Html::helpFooter();
   }
}
