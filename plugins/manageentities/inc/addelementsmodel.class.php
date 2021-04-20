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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

include_once(GLPI_ROOT . '/plugins/manageentities/common/commonGLPIModel.class.php');

// Enumeration like for Errors
class Errors extends CommonGLPIErrors {

   const ERROR_ENTITY                   = 0;
   const ERROR_CONTACT                  = 1;
   const ERROR_CONTRACT                 = 2;
   const ERROR_CONTRACT_MANAGEMENT_TYPE = 2;
   const ERROR_INTERVENTION             = 3;
   const ERROR_FIELDS                   = 4;
   const ERROR_CRIPRICE                 = 5;
   const ERROR_NAME_EXIST               = 'name_exist';
   const ERROR_ADD_PDF                  = 'error_add_pdf';
   const ERROR_CRIPRICE_EXIST           = 'error_criprice_exist';

}

// Enumeration like for Element type
class ElementType extends CommonGLPIElementType {

   const ENTITY                   = 'entity';
   const CONTACT                  = 'contact';
   const CONTRACT                 = 'contract';
   const CONTRACT_MANAGEMENT_TYPE = 'contract_management_type';
   const INTERVENTION             = 'intervention';
   const CRIPRICE                 = 'criprice';
   const ALL                      = 'all_elements';

}

// Enumeration like for Messages
class Messages extends CommonGLPIMessages {

}

// Enumeration like for Status
class Status extends CommonGLPIStatus {

   const SAVED         = 'saved';
   const NOT_SAVED     = 'not_saved';
   const PDF_ADDED     = 'pdf_added';
   const PDF_NOT_ADDED = 'pdf_not_added';

}

// Enumeration like for Database operations
class DBOperation extends CommonGLPIDBOperation {

}

// Enumeration like for Action type
class Action {

   const ADD_ONLY_ENTITY                         = 'add_only_entity';
   const ADD_ONLY_CONTACT                        = 'add_only_contact';
   const ADD_ONLY_CONTRACT                       = 'add_only_contract';
   const ADD_ONLY_INTERVENTION                   = 'add_only_intervention';
   const ADD_ENTITY_AND_CONTACT                  = 'add_entity_and_contact';
   const ADD_ENTITY_AND_CONTRACT                 = 'add_entity_and_contract';
   const ADD_ENTITY_AND_INTERVENTION             = 'add_entity_and_intervention';
   const ADD_ENTITY_INTERVENTION_AND_CONTRACT    = 'add_entity_intervention_and_contract';
   const ADD_INTERVENTION_AND_CONTRACT           = 'add_intervention_and_contract';
   const DELETE_CONTRACT_MANAGEMENT_TYPE         = 'delete_contract_management_type';
   const CONFIRM_DELETE_CONTRACT_MANAGEMENT_TYPE = 'confirm_delete_contract_management_type';
   const ADD_CONTRACT_MANAGEMENT_TYPE            = 'add_contract_management_type';
   const ADD_NEW_CONTACT                         = 'add_new_contact';
   const ADD_NEW_INTERVENTION                    = 'add_new_intervention';
   const LOAD_CONTRACT_TEMPLATE                  = 'load_contract_template';
   const UPDATE_CRI_PRICE                        = 'update_cri_price';
   const UPDATE_CONTRACT_LIST                    = 'update_contract_list';
   const ADD_NEW_CONTRACT_PDF                    = 'add_new_contract_pdf';
   const REINIT_FORMS                            = 'reinit_forms';
   const CONFIRM_ADD_ALL_ELEMENT                 = 'confirm_add_all_element';
   const CONFIRM_UPDATE_ALL_ELEMENT              = 'confirm_update_all_element';
   const ADD_ALL_ELEMENT                         = 'add_all_element';
   const UPDATE_ALL_ELEMENT                      = 'update_all_element';
   const SHOW_FORM_CRI_PRICE                     = 'show_form_cri_price';
   const ADD_CRI_PRICE                           = 'add_cri_price';
   const DELETE_CRI_PRICE                        = 'delete_cri_price';
   const UPDATE_CRI_PRICE_FROM_TYPE              = 'update_criprice_from_critype';

}

class PluginManageentitiesAddElementsModel extends CommonGLPIModel {

   private static $instance;
   private        $contacts;               // Contact list
   private        $contactManager;         // Id of contact manager (if exist)
   private        $entity;                 // Entity
   private        $contract;               // Contract
   private        $contractManagementType; // Contract mangement type
   private        $contractTemplate;       // Contract template (if used)
   private        $contractdays;           // Intervention list
   private        $nbContact;              // Number of contacts
   private        $nbContractDay;          // Number of interventions
   private        $nbCriPrice;             // Number of criprices
   private        $idContractTemplate;     // id template used for contract
   private        $isContractTemplate;     // If template is used for contract

   final public static function getInstance() {
      if (is_null(self::$instance) && !isset($_SESSION[PluginManageentitiesAddElementsModel::getType() . '_instance'])) {
         self::$instance                                                          = new PluginManageentitiesAddElementsModel();
         $_SESSION[PluginManageentitiesAddElementsModel::getType() . '_instance'] = serialize(self::$instance);
      } else {
         self::$instance = unserialize($_SESSION[PluginManageentitiesAddElementsModel::getType() . '_instance']);
      }

      return self::$instance;
   }

   private function __construct() {
      global $CFG_GLPI;

      $this->url    = $CFG_GLPI ['root_doc'] . "/plugins/manageentities/ajax/addelements.listener.php";
      $this->errors = [];

      $entity = new Entity ();
      $this->setEntity($entity);

      $contact = new Contact();
      $contact->getEmpty();
      unset($contact->fields['id']);
      $this->contacts = [];
      $this->addContact($contact, 1);

      $this->contactManager = 0;

      $contractDay = new PluginManageentitiesContractDay();
      $contractDay->getEmpty();

      unset($contractDay->fields['id']);
      if (isset($contractDay->fields['plugin_manageentities_critypes_id'])) {
         unset($contractDay->fields['plugin_manageentities_critypes_id']);
      }
      $this->contractdays = [];

      $this->addContractDay($contractDay, 1);


      $contract = new Contract();
      $contract->getEmpty();
      $this->contract = $contract;

      $contractTemplate = new Contract();
      $contractTemplate->getEmpty();
      $this->contractTemplate = $contractTemplate;

      $contractManagementType = new PluginManageentitiesContract();
      $contractManagementType->getEmpty();
      $this->contractManagementType = $contractManagementType;


      $this->nbContact     = 1;
      $this->nbContractDay = 1;
      $this->nbCriPrice    = 0;

      $this->messages = [
         ElementType::ENTITY                                                     => [
            Errors::ERROR_FIELDS     => "<span style='color:red;'> " . __("All new entity fields are not filled.", "manageentities") . "</span>",
            Errors::ERROR_ADD        => "<span style='color:red;'> " . __("An error happend while saving the entity.", "manageentities") . "</span>",
            Errors::ERROR_NAME_EXIST => "<span style='color:red;'> " . __("Entity name already exists.", "manageentities") . "</span>",
            Errors::ERROR_UPDATE     => "<span style='color:red;'> " . __("An error happend while updating the entity.", "manageentities") . "</span>",
            Status::UPDATED          => __("Entity successfully updated.", "manageentities"),
            Status::ADDED            => __("Entity successfully added.", "manageentities"),
            Status::SAVED            => __("Entity saved in base", "manageentities"),
            Status::NOT_SAVED        => __("Entity informations not updated in base", "manageentities")
         ],
         ElementType::CONTACT                                                    => [
            Errors::ERROR_FIELDS => "<span style='color:red;'> " . __("All new contact fields are not filled.", "manageentities") . "</span>",
            Errors::ERROR_ADD    => "<span style='color:red;'> " . __("An error happend while saving the contact.", "manageentities") . "</span>",
            Errors::ERROR_UPDATE => "<span style='color:red;'> " . __("An error happend while updating the contact.", "manageentities") . "</span>",
            Status::UPDATED      => __("Contact successfully updated.", "manageentities"),
            Status::ADDED        => __("Contact successfully added.", "manageentities"),
            Status::SAVED        => __("Contact saved in base", "manageentities"),
            Status::NOT_SAVED    => __("Contact informations not updated in base", "manageentities")
         ],
         ElementType::CONTRACT                                                   => [
            Errors::ERROR_FIELDS     => "<span style='color:red;'> " . __("All new contract fields are not filled.", "manageentities") . "</span>",
            Errors::ERROR_ADD        => "<span style='color:red;'> " . __("An error happend while saving the contract.", "manageentities") . "</span>",
            Errors::ERROR_ADD_PDF    => "<span style='color:red;'> " . __("An error happend while uploading the document.", "manageentities") . "</span>",
            Errors::ERROR_NAME_EXIST => "<span style='color:red;'> " . __("Contract name already exists.", "manageentities") . "</span>",
            Errors::ERROR_UPDATE     => "<span style='color:red;'> " . __("An error happend while updating the contract.", "manageentities") . "</span>",
            Status::UPDATED          => __("Contract successfully updated", "manageentities"),
            Status::ADDED            => __("Contract successfully added", "manageentities"),
            Status::PDF_ADDED        => __("Document successfully added", "manageentities"),
            Status::SAVED            => __("Contract saved in base", "manageentities"),
            Status::NOT_SAVED        => __("Contract informations not updated in base", "manageentities")
         ],
         ElementType::CONTRACT_MANAGEMENT_TYPE                                   => [
            Errors::ERROR_FIELDS     => "<span style='color:red;'> " . __("All new contract management type fields are not filled.", "manageentities") . "</span>",
            Errors::ERROR_ADD        => "<span style='color:red;'> " . __("An error happend while saving the contract management type.", "manageentities") . "</span>",
            Errors::ERROR_NAME_EXIST => "<span style='color:red;'> " . __("Contract management type name already exists.", "manageentities") . "</span>",
            Errors::ERROR_UPDATE     => "<span style='color:red;'> " . __("An error happend while updating the contract management type.", "manageentities") . "</span>",
            Errors::ERROR_DELETE     => "<span style='color:red;'> " . __("An error happend while deleting the contract management type.", "manageentities") . "</span>",
            Status::UPDATED          => __("Contract management type successfully updated", "manageentities"),
            Status::ADDED            => __("Contract management type successfully added", "manageentities"),
            Status::SAVED            => __("Contract management type saved in base", "manageentities"),
            Status::NOT_SAVED        => __("Contract management type informations not updated in base", "manageentities"),
            Status::DELETED          => __("Contract management type succesfully deleted.", "manageentities")
         ],
         ElementType::CRIPRICE                                                   => [
            Errors::ERROR_FIELDS => "<span style='color:red;'> " . __("All new fields are not filled.", "manageentities") . "</span>",
            Errors::ERROR_ADD    => "<span style='color:red;'> " . __("An error happend while saving the price.", "manageentities") . "</span>",
            Errors::ERROR_UPDATE => "<span style='color:red;'> " . __("An error happend while updating the price.", "manageentities") . "</span>",
            Errors::ERROR_DELETE => "<span style='color:red;'> " . __("An error happend while deleting the price.", "manageentities") . "</span>",
            Status::UPDATED      => __("Price successfully updated", "manageentities"),
            Status::ADDED        => __("Price successfully added", "manageentities"),
            Status::SAVED        => __("Price saved in base", "manageentities"),
            Status::DELETED      => __("Price succesfully deleted.", "manageentities")
         ],
         ElementType::INTERVENTION                                               => [
            Errors::ERROR_FIELDS => "<span style='color:red;'> " . __("All new intervention fields are not filled.", "manageentities") . "</span>",
            Errors::ERROR_ADD    => "<span style='color:red;'> " . __("An error happend while saving the intervention.", "manageentities") . "</span>",
            Errors::ERROR_UPDATE => "<span style='color:red;'> " . __("An error happend while updating the intervention.", "manageentities") . "</span>",
            Status::UPDATED      => __("Intervention successfully updated.", "manageentities"),
            Status::ADDED        => __("Intervention successfully added.", "manageentities"),
            Status::SAVED        => __("Intervention saved in base", "manageentities"),
            Status::NOT_SAVED    => __("Intervention informations not updated in base", "manageentities")
         ],
         ElementType::ALL                                                        => [
            Errors::ERROR_FIELDS => "<span style='color:red;'> " . __("All fields are not filled.", "manageentities") . "</span>",
            Errors::ERROR_ADD    => "<span style='color:red;'> " . __("An error happend while saving the informations.", "manageentities") . "</span>",
            Errors::ERROR_UPDATE => "<span style='color:red;'> " . __("An error happend while updating the informations.", "manageentities") . "</span>",
            Status::UPDATED      => __("All infomations successfully updated.", "manageentities"),
            Status::ADDED        => __("All informations successfully added.", "manageentities"),
            Status::SAVED        => __("All informations saved in base", "manageentities"),
            Status::NOT_SAVED    => __("All informations not updated in base", "manageentities")
         ],
         ElementType::ENTITY . ElementType::CONTACT                              => [Status::ADDED => __("Entity and contact successfully added", "manageentities")],
         ElementType::ENTITY . ElementType::CONTRACT                             => [Status::ADDED => __("Entity and contract successfully added", "manageentities")],
         ElementType::ENTITY . ElementType::INTERVENTION                         => [Status::ADDED => __("Entity and intervention successfully added", "manageentities")],
         ElementType::CONTRACT . ElementType::INTERVENTION                       => [Status::ADDED => __("Contract and intervention successfully added", "manageentities")],
         ElementType::ENTITY . ElementType::CONTRACT . ElementType::INTERVENTION => [Status::ADDED => __("Entity, intervention and contract successfully added", "manageentities")],
         "mandatory_field"                                                       => "<span style='color:red;'> * </span>",
         "message_info"                                                          => _n("Information", "Informations", 1),
         "message_error"                                                         => __("Warning"),
         "irreversible_action"                                                   => __("This action is irreversible, Continue ?", "manageentities")
      ];

      $this->idContractTemplate = -1;
      $this->isContractTemplate = 0;

      $this->serializeInSession();
   }

   public function destroy() {
      unset($_SESSION[PluginManageentitiesAddElementsModel::getType() . '_instance']);
      self::$instance                                                          = new PluginManageentitiesAddElementsModel();
      $_SESSION[PluginManageentitiesAddElementsModel::getType() . '_instance'] = serialize(self::$instance);
   }

   /* ------------------------------------------------------------------------------------
    *    Abstract functions to be implemented
    * ------------------------------------------------------------------------------------ */

   public function serializeInSession() {
      $_SESSION[PluginManageentitiesAddElementsModel::getType() . "_instance"] = serialize($this);
   }

   /* ------------------------------------------------------------------------------------
    *    Business functions
    * ------------------------------------------------------------------------------------ */

   public function addEntityToBase($pView) {
      $entity = $this->getEntity();

      $childEntity = new Entity();
      if($childEntity->getFromDB($_POST['new_entity_entities_id'])){
         $entity->fields['completename'] = $childEntity->fields['completename'] . " > " . $entity->fields['name'];
      }

      // If entity already saved
      if (isset($entity->fields['id']) && $entity->fields['id'] > 0) {
         // Update entity
         $datas = $this->persistData($entity, DBOperation::UPDATE);
         if (isset($datas[Status::UPDATED]) && $datas[Status::UPDATED] == "true") {
            $this->setEntity($entity);
            $pView->changeBtnName("btnAddEntity", __("Update this entity", "manageentities"));
            $pView->updateTabTitle(1, $entity->fields['name'], "div#mytabsentity", $entity, $this->getMessage(ElementType::ENTITY, Status::SAVED));
            $pView->updateImgTabTitle(false, "'img_" . $entity->getType() . "1'", $this->getMessage(ElementType::ENTITY, Status::SAVED));
            $this->deleteError(Errors::ERROR_ENTITY, Errors::ERROR_ADD);
            return ["result" => Status::UPDATED,
                    "message" => $this->getMessage(ElementType::ENTITY, Status::UPDATED)];
         } else {
            $this->addError(Errors::ERROR_ENTITY, Errors::ERROR_UPDATE, 'true');
            return ["result" => Status::NOT_UPDATED,
                    "message" => $this->getMessage(ElementType::ENTITY, Errors::ERROR_UPDATE)];
         }
      } else {
         // Add entity
         $datas = $this->persistData($entity, DBOperation::ADD);

         if (isset($datas[Status::ADDED]) && $datas[Status::ADDED] == "true") {
            $this->setEntity($entity);
            $pView->changeBtnName("btnAddEntity", __("Update this entity", "manageentities"));
            $pView->updateTabTitle(1, $entity->fields['name'], "div#mytabsentity", $entity, $this->getMessage(ElementType::ENTITY, Status::SAVED), true);
            $pView->updateImgTabTitle(false, "'img_" . $entity->getType() . "1'", $this->getMessage(ElementType::ENTITY, Status::SAVED));
            $this->deleteError(Errors::ERROR_ENTITY, Errors::ERROR_ADD);
            return ["result" => Status::ADDED,
                    "message" => $this->getMessage(ElementType::ENTITY, Status::ADDED)];
         } else {
            $this->addError(Errors::ERROR_ENTITY, Errors::ERROR_ADD, 'true');
            unset($entity->fields['id']);
            if (isset($datas['cause'])) {
               $arrayRes = ["result" => Status::NOT_ADDED,
                            "cause" => $datas['cause']];
               switch ($datas['cause']) {
                  case Errors::ERROR_NAME_EXIST:
                     $arrayRes['message'] = $this->getMessage(ElementType::ENTITY, Errors::ERROR_NAME_EXIST);
                     break;
                  default:
                     $arrayRes['message'] = $this->getMessage(ElementType::ENTITY, Errors::ERROR_ADD);
                     break;
               }
               return $arrayRes;
            } else {
               return ["result" => Status::NOT_ADDED,
                       "message" => $this->getMessage(ElementType::ENTITY, Errors::ERROR_ADD)];
            }
         }
      }
   }

   public function addPDFContractToBase($pView) {
      global $DB;

      if (isset($_POST['_filename']) && !empty($_POST['filename'])) {
         $contract = $this->getContract();
         if (isset($contract->fields['id']) && $contract->fields['id'] > 0) {
            $doc = new Document();

            $input = [
               "documentcategories_id" => $_POST['documentcategories_id'],
               "entities_id"           => $contract->fields['entities_id'],
               "is_reccursive"         => 0,
               "itemtype"              => $contract->getType(),
               "items_id"              => $contract->fields['id'],
               "_no_message"           => "true",
               "_filename"             => $_POST['_filename'],
               "_tag_filename"         => $_POST['_tag_filename']
            ];

            $doc->check(-1, CREATE, $input);

            if ($newID = $doc->add($input)) {
               $vals=  ["result" => Status::ADDED,
                  "message" => $this->getMessage(ElementType::CONTRACT, Status::PDF_ADDED)];
//               $pView->showResult(_n("Information", "Informations", 1), $this->getMessage(ElementType::CONTRACT, Status::PDF_ADDED));

               $item = $this->getContract();
               $arr  = $this->getQueryForDFContract($item);

               $query = $arr['query'];

               if ($query != null) {
                  $result = $DB->query($query);
                  $number = $DB->numrows($result);
               } else {
                  $number = false;
               }

//               if ($number && $number > 1) {
//                  $pView->addPDFContractToView($doc, false);
//               } else {
//                  $pView->addPDFContractToView($doc, true);
//               }
            } else {
               $vals=  ["result" => Status::NOT_ADDED,
                  "message" => $this->getMessage(ElementType::CONTRACT, Errors::ERROR_ADD_PDF)];
//               $pView->showDialog(_n("Information", "Informations", 1), $this->getMessage(ElementType::CONTRACT, Errors::ERROR_ADD_PDF));
            }
         }
      } else {
         $vals=  ["result" => Status::NOT_ADDED,
            "message" => $this->getMessage(ElementType::ALL, Status::ERROR_FIELDS)];
//         $pView->showDialog(_n("Information", "Informations", 1), $this->getMessage(ElementType::ALL, Errors::ERROR_FIELDS));
      }
      unset($_SESSION['MESSAGE_AFTER_REDIRECT']);
      return $vals;
   }

   public function addContractToBase($pView) {
      $contract = $this->contract;

      // Case "Previous entity created"
      if ($_POST['previous_entity_for_contract'] == "true") {
         $entity = $this->getEntity();
         // IF entity already created
         if (isset($entity->fields['id']) && $entity->fields['id'] != "") {
            $contract->fields['entities_id'] = $entity->fields['id'];
         } else {
            return ["result" => Action::ADD_ONLY_ENTITY,
                    "from" => $contract->getType()];
         }
      } else {
         if (isset($_POST['new_contract_entity_id'])) {
            $contract->fields['entities_id'] = $_POST['new_contract_entity_id'];
         } else {
            return ["result" => Status::NOT_ADDED,
                    "message" => $this->getMessage(ElementType::ENTITY, Errors::ERROR_ENTITY)];
         }
      }

      // If contract already created
      if (isset($contract->fields['id']) && $contract->fields['id'] > 0) {
         // update contract
         $datas = $this->persistData($contract, DBOperation::UPDATE);
         if (isset($datas[Status::UPDATED]) && $datas[Status::UPDATED] == "true") {
            $this->setContract($contract);
            $pView->updateTabTitle(1, $contract->fields['name'], "div#mytabscontract", $contract, $this->getMessage(ElementType::CONTRACT, Status::SAVED));
            $pView->updateImgTabTitle(false, "'img_" . $contract->getType() . "1'", $this->getMessage(ElementType::CONTRACT, Status::SAVED));
            $pView->changeBtnName("btnAddContract", __("Update this contract only", "manageentities"));
            $this->deleteError(Errors::ERROR_CONTRACT, Errors::ERROR_UPDATE);
            $this->setIsContractTemplate(0);
            $pView->showFormAddContractManagementType($contract);
            return ["result" => Status::UPDATED,
                    "message" => $this->getMessage(ElementType::CONTRACT, Status::UPDATED)];
         } else {
            $this->addError(Errors::ERROR_CONTRACT, Errors::ERROR_ADD, 'true');
            return ["result" => Status::NOT_UPDATED,
                    "message" => $this->getMessage(ElementType::CONTRACT, Errors::ERROR_UPDATE)];
         }
      } else {
         // Add contract
         if (isset($contract->fields['id']))
            unset($contract->fields['id']);

         $datas = $this->persistData($contract, DBOperation::ADD);

         if (isset($datas[Status::ADDED]) && $datas[Status::ADDED] == "true") {
            $this->deleteError(Errors::ERROR_CONTRACT, Errors::ERROR_ADD);
            $pView->changeBtnName("btnAddContract", __("Update this contract only", "manageentities"));
            $this->setContract($contract);
            $strTitleTab = isset($contract->fields['name']) ? $contract->fields['name'] : "";
            $pView->updateTabTitle(1, $strTitleTab, "div#mytabscontract", $contract, $this->getMessage(ElementType::CONTRACT, Status::SAVED), true);
            $pView->updateImgTabTitle(false, "'img_" . $contract->getType() . "1'", $this->getMessage(ElementType::CONTRACT, Status::SAVED));
            $this->setIsContractTemplate(0);
            $pView->showFormAddContractManagementType($contract);
            $pView->showFormAddPDFcontract();
            $pView->removeElementFromView("row_contract_template");

            return ["result" => Status::ADDED,
                    "message" => $this->getMessage(ElementType::CONTRACT, Status::ADDED)];
         } else {
            unset($contract->fields['id']);
            $this->addError(Errors::ERROR_CONTRACT, Errors::ERROR_ADD, 'true');

            if (isset($datas['cause'])) {
               $arrayRes = ["result" => Status::NOT_ADDED,
                            "cause" => $datas['cause']];
               switch ($datas['cause']) {
                  case Errors::ERROR_NAME_EXIST:
                     $arrayRes['message'] = $this->getMessage(ElementType::CONTRACT, Errors::ERROR_NAME_EXIST);
                     break;
                  default:
                     $arrayRes['message'] = $this->getMessage(ElementType::CONTRACT, Errors::ERROR_ADD);
                     break;
               }
               return $arrayRes;
            } else {
               return ["result" => Status::NOT_ADDED,
                       "message" => $this->getMessage(ElementType::CONTRACT, Errors::ERROR_ADD)];
            }
         }
      }
   }

   public function addContractManagementTypeToBase($pView) {
      $contract               = $this->getContract();
      $contractManagementType = $this->getContractManagementType();

      // If contract management type already created
      if (isset($contractManagementType->fields['id']) && $contractManagementType->fields['id'] > 0) {
         // update contract management type
         $datas = $this->persistData($contractManagementType, DBOperation::UPDATE);
         if (isset($datas[Status::UPDATED]) && $datas[Status::UPDATED] == "true") {
            $this->setContractManagementType($contractManagementType);
            $pView->changeBtnName("btnAddContractManagementType", _sx('button', 'Update'));
            $pView->changeElementVisibility("btnDeleteContractManagementType", true);
            $pView->updateTabTitle(1, $contract->fields['name'], "div#mytabscontract", $contract, $this->getMessage(ElementType::CONTRACT, Status::SAVED));
            $this->deleteError(Errors::ERROR_CONTRACT_MANAGEMENT_TYPE, Errors::ERROR_UPDATE);
            return ["result" => Status::UPDATED,
                    "message" => $this->getMessage(ElementType::CONTRACT_MANAGEMENT_TYPE, Status::UPDATED)];
         } else {
            $this->addError(Errors::ERROR_CONTRACT_MANAGEMENT_TYPE, Errors::ERROR_UPDATE, 'true');
            return ["result" => Status::NOT_UPDATED,
                    "message" => $this->getMessage(ElementType::CONTRACT_MANAGEMENT_TYPE, Errors::ERROR_UPDATE)];
         }
      } else {
         // Add contract managment type
         if (isset($contractManagementType->fields['id']))
            unset($contractManagementType->fields['id']);

         $datas = $this->persistData($contractManagementType, DBOperation::ADD);

         if (isset($datas[Status::ADDED]) && $datas[Status::ADDED] == "true") {
            $this->deleteError(Errors::ERROR_CONTRACT_MANAGEMENT_TYPE, Errors::ERROR_ADD);
            $this->setContractManagementType($contractManagementType);

            $pView->changeBtnName("btnAddContractManagementType", _sx('button', 'Update'));
            $pView->changeElementVisibility("btnDeleteContractManagementType", true);

            $pView->updateTabTitle(1, $contract->fields['name'], "div#mytabscontract", $contract, $this->getMessage(ElementType::CONTRACT, Status::SAVED));

            return ["result" => Status::ADDED,
                    "message" => $this->getMessage(ElementType::CONTRACT_MANAGEMENT_TYPE, Status::ADDED)];
         } else {
            unset($contractManagementType->fields['id']);
            $this->addError(Errors::ERROR_CONTRACT_MANAGEMENT_TYPE, Errors::ERROR_ADD, 'true');

            if (isset($datas['cause'])) {
               $arrayRes = ["result" => Status::NOT_ADDED,
                            "cause" => $datas['cause']];
               switch ($datas['cause']) {
                  case Errors::ERROR_NAME_EXIST:
                     $arrayRes['message'] = $this->getMessage(ElementType::CONTRACT_MANAGEMENT_TYPE, Errors::ERROR_NAME_EXIST);
                     break;
                  default:
                     $arrayRes['message'] = $this->getMessage(ElementType::CONTRACT_MANAGEMENT_TYPE, Errors::ERROR_ADD);
                     break;
               }
               return $arrayRes;
            } else {
               return ["result" => Status::NOT_ADDED,
                       "message" => $this->getMessage(ElementType::CONTRACT_MANAGEMENT_TYPE, Errors::ERROR_ADD)];
            }
         }
      }
   }

   public function addContactToBase($pView, $idContact = -1) {
      $contacts = $this->getContacts();
      if ($idContact == -1) {
         $contact = $contacts[$_POST['fakeid_new_contact']];
      } else {
         $contact = $contacts[$idContact];
      }


      $nbContact = $this->getNbContact();

      // Case "Previous entity created"
      if ($_POST['previous_entity_for_contact'] == "true") {
         $entity = $this->getEntity();
         // IF entity already created
         if (isset($entity->fields['id']) && $entity->fields['id'] != "") {
            $contact->fields['entities_id'] = $entity->fields['id'];
         } else {
            return ["result" => Action::ADD_ONLY_ENTITY,
                    "from" => $contact->getType()];
         }
      } else {
         if (isset($_POST['new_contact_entity_id'])) {
            $contact->fields['entities_id'] = $_POST['new_contact_entity_id'];
         } else {
            return ["result" => Status::NOT_ADDED,
                    "message" => $this->getMessage(ElementType::CONTACT, Errors::ERROR_ENTITY)];
         }
      }


      // If contact already created
      if (isset($contact->fields['id']) && $contact->fields['id'] > 0) {
         // update contact
         $datas = $this->persistData($contact, DBOperation::UPDATE);

         if (isset($datas[Status::UPDATED]) && $datas[Status::UPDATED] == "true") {
            $listContact                               = $this->getContacts();
            $listContact[$_POST['fakeid_new_contact']] = $contact;
            $this->setContacts($listContact);

            $this->manageMEntitiesContacts($contact, DBOperation::UPDATE, $pView);

            $strTitleTab = isset($contact->fields['firstname']) ? $contact->fields['firstname'] : "";
            $strTitleTab .= " ";
            $strTitleTab .= isset($contact->fields['name']) ? $contact->fields['name'] : "";

            $pView->updateTabTitle($_POST['fakeid_new_contact'], $strTitleTab, "div#mytabscontacts", $contact, $this->getMessage(ElementType::CONTACT, Status::SAVED));
            //                  $pView->updateImgTabTitle(false,"img_".$contact->getType().$_POST['fakeid_new_contact'],$this->getMessage("contact_saved_db"));
            $pView->changeBtnName("btnAddContact" . $_POST['fakeid_new_contact'], __("Update this contact only", "manageentities"));
            return ["result" => Status::UPDATED,
                    "message" => $this->getMessage(ElementType::CONTACT, Status::UPDATED)];
         } else {
            $this->addError(Errors::ERROR_CONTACT, Errors::ERROR_ADD, 'true');
            return ["result" => Status::NOT_UPDATED,
                    "message" => $this->getMessage(ElementType::CONTACT, Errors::ERROR_UPDATE)];
         }
      } else {
         // Add contact
         if (isset($contact->fields['id']))
            unset($contact->fields['id']);

         $datas = $this->persistData($contact, DBOperation::ADD);

         if (isset($datas[Status::ADDED]) && $datas[Status::ADDED] == "true") {
            $this->addContact($contact, $nbContact);
            $this->deleteError(Errors::ERROR_CONTACT, Errors::ERROR_ADD);

            $this->manageMEntitiesContacts($contact, DBOperation::ADD, $pView);

            if (isset($datas[Status::ADDED]) && $datas[Status::ADDED] == "true") {
               $pView->changeBtnName("btnAddContact" . $_POST['fakeid_new_contact'], __("Update this contact only", "manageentities"));

               $strTitleTab = isset($contact->fields['firstname']) ? $contact->fields['firstname'] : "";
               $strTitleTab .= " ";
               $strTitleTab .= isset($contact->fields['name']) ? $contact->fields['name'] : "";

               $pView->updateTabTitle($_POST['fakeid_new_contact'], $strTitleTab, "div#mytabscontacts", $contact, $this->getMessage(ElementType::CONTACT, Status::SAVED), true);
               $pView->updateImgTabTitle(false, "'img_" . $contact->getType() . $_POST['fakeid_new_contact'] . "'", $this->getMessage(ElementType::CONTACT, Status::SAVED));

               return ["result" => Status::ADDED,
                       "message" => $this->getMessage(ElementType::CONTACT, Status::ADDED)];
            } else {
               unset($contact->fields['id']);
               $this->addError(Errors::ERROR_CONTACT, Errors::ERROR_ADD, 'true');
               return ["result" => Status::NOT_ADDED,
                       "message" => $this->getMessage(ElementType::CONTACT, Errors::ERROR_ADD)];
            }
         } else {
            unset($contact->fields['id']);
            $this->addError(Errors::ERROR_CONTACT, Errors::ERROR_ADD, 'true');
            return ["result" => Status::NOT_ADDED,
                    "message" => $this->getMessage(ElementType::CONTACT, Errors::ERROR_ADD)];
         }
      }
   }

   public function addInterventionToBase($pView, $idIntervention = -1) {
      $interventions = $this->getContractDays();

      if ($idIntervention == -1) {
         $intervention = $interventions[$_POST['fakeid_new_intervention']];
      } else {
         $intervention = $interventions[$idIntervention];
      }

      $arrayRes = [];
      $cpt      = 0;

      // Case "Previous entity created"
      if ($_POST['previous_entity_for_intervention'] == "true") {
         $entity = $this->getEntity();
         // IF entity already created
         if (isset($entity->fields['id']) && $entity->fields['id'] != "") {
            $intervention->fields['entities_id'] = $entity->fields['id'];
         } else {
            $arrayRes["result"] = Action::ADD_ONLY_ENTITY;
            $cpt++;
         }
      } else {
         if (isset($_POST['new_intervention_entity_id'])) {
            $intervention->fields['entities_id'] = $_POST['new_intervention_entity_id'];
         } else {
            return ["result" => Errors::ERROR_FIELDS,
                    "message" => $this->getMessage(ElementType::ENTITY, Errors::ERROR_FIELDS)];
         }
      }


      if (isset($_POST['previous_contract_for_intervention']) && $_POST['previous_contract_for_intervention'] == "true") {
         $contract = $this->getContract();
         if (isset($contract->fields['id']) && $contract->fields['id'] != "") {
            $intervention->fields['contracts_id'] = $contract->fields['id'];
         } else {
            $arrayRes["result"] = Action::ADD_ONLY_CONTRACT;
            $cpt++;
         }
      } else {
         if (isset($_POST['new_intervention_contract_id']) && $_POST['new_intervention_contract_id'] > 0) {
            $intervention->fields['contracts_id'] = $_POST['new_intervention_contract_id'];
         } else {
            return ["result" => Errors::ERROR_FIELDS,
                    "message" => $this->getMessage(ElementType::CONTRACT, Errors::ERROR_FIELDS)];
         }
      }

      if ($cpt == 2) {
         return ["result" => Action::ADD_ENTITY_AND_CONTRACT,
                 "from" => $intervention->getType()];
      } else if ($cpt == 1) {
         $arrayRes["from"] = $intervention->getType();
         return $arrayRes;
      }


      // Cri prices
      $price = $this->getCriPrice($_POST['fakeid_new_intervention']);

      // If intervention already created
      if (isset($intervention->fields['id']) && $intervention->fields['id'] > 0) {
         // update intervention
         $tmpInter = new PluginManageentitiesContractDay();

         $tmpInter->getFromDB($intervention->fields['id']);
         $nbDaysBefore = $tmpInter->fields['nbday'];
         $datas        = $this->persistData($intervention, DBOperation::UPDATE);
         $nbDaysAfter  = $intervention->fields['nbday'];
         if (isset($datas[Status::UPDATED]) && $datas[Status::UPDATED] == "true") {
            $nbIntervention                                   = $this->getNbContractDays();
            $interventions[$_POST['fakeid_new_intervention']] = $intervention;
            $this->setContractDays($interventions);
            $pView->updateImgTabTitle(false, "'img_" . $intervention->getType() . $_POST['fakeid_new_intervention'] . "'", $this->getMessage(ElementType::INTERVENTION, Status::NOT_SAVED));
            $strTitleTab = isset($intervention->fields['name']) ? $intervention->fields['name'] : "";

            $pView->updateTabTitle($_POST['fakeid_new_intervention'], $strTitleTab, "div#mytabsinterventions", $intervention, $this->getMessage(ElementType::INTERVENTION, Status::SAVED));
            $pView->updateImgTabTitle(false, "'img_" . $intervention->getType() . $_POST['fakeid_new_intervention'] . "'", $this->getMessage(ElementType::INTERVENTION, Status::SAVED));

            $pView->changeBtnName("btnAddIntervention" . $_POST['fakeid_new_intervention'], __("Update this intervention only", "manageentities"));


            // Update criprices
            if ($price != null && $intervention->fields['id'] <= 0) {
               $cprice = new PluginManageentitiesCriPrice();
               $cprice->getEmpty();
               $cprice->getFromDBByCrit(['entities_id'                       => $intervention->fields['entities_id'],
                                         'plugin_manageentities_critypes_id' => $intervention->fields['plugin_manageentities_critypes_id']]);

               if (isset($cprice->fields['id']) && $cprice->fields['id'] > 0) {
                  $typeInsert = DBOperation::UPDATE;
               } else {
                  $cprice->fields['entities_id']                       = $intervention->fields['entities_id'];
                  $cprice->fields['plugin_manageentities_critypes_id'] = $intervention->fields['plugin_manageentities_critypes_id'];

                  $typeInsert = DBOperation::ADD;
               }

               $cprice->fields['price'] = $price;

               $datas = $this->persistData($cprice, $typeInsert);
            }

            if ((isset($datas[Status::UPDATED]) && $datas[Status::UPDATED] == "true") ||
                (isset($datas[Status::ADDED]) && $datas[Status::ADDED] == "true")) {

               $interventionSkateholder = new PluginManageentitiesInterventionSkateholder();
               $interventionSkateholder->displayTabContentForItem($intervention);
               return ["result" => Status::UPDATED,
                       "message" => $this->getMessage(ElementType::INTERVENTION, Status::UPDATED)];

            } else {
               $this->addError(Errors::ERROR_INTERVENTION, Errors::ERROR_ADD, 'true');
               return ["result" => Status::NOT_UPDATED,
                       "message" => $this->getMessage(ElementType::INTERVENTION, Errors::ERROR_UPDATE)];
            }

         } else {
            $this->addError(Errors::ERROR_INTERVENTION, Errors::ERROR_ADD, 'true');
            return ["result" => Status::NOT_UPDATED,
                    "message" => $this->getMessage(ElementType::INTERVENTION, Errors::ERROR_UPDATE)];
         }

      } else {
         // Add intervention
         if (isset($intervention->fields['id']))
            unset($intervention->fields['id']);
         if (empty($intervention->fields['plugin_manageentities_critypes_id'])) {
            unset($intervention->fields['plugin_manageentities_critypes_id']);
         }
         $datas = $this->persistData($intervention, DBOperation::ADD);

         if (isset($datas[Status::ADDED]) && $datas[Status::ADDED] == "true") {

            $nbIntervention = $this->getNbContractDays();
            $this->addContractDay($intervention, $nbIntervention);
            $this->deleteError(Errors::ERROR_INTERVENTION, Errors::ERROR_ADD);
            $pView->changeBtnName("btnAddIntervention" . $_POST['fakeid_new_intervention'], __("Update this intervention only", "manageentities"));
            $strTitleTab = isset($intervention->fields['name']) ? $intervention->fields['name'] : "";
            $pView->updateTabTitle($nbIntervention, $strTitleTab, "div#mytabsinterventions", $intervention, $this->getMessage(ElementType::INTERVENTION, Status::SAVED), true);
            $pView->initCriPricesView($intervention, $nbIntervention);

            if (isset($datas[Status::ADDED]) && $datas[Status::ADDED] == "true") {
               if ((isset($datas[Status::UPDATED]) && $datas[Status::UPDATED] == "true") ||
                   (isset($datas[Status::ADDED]) && $datas[Status::ADDED] == "true")) {

                  $interventionSkateholder = new PluginManageentitiesInterventionSkateholder();
                  $interventionSkateholder->displayTabContentForItem($intervention);
                  return ["result" => Status::ADDED,
                          "message" => $this->getMessage(ElementType::INTERVENTION, Status::ADDED)];

               } else {
                  $this->addError(Errors::ERROR_INTERVENTION, Errors::ERROR_ADD, 'true', $_POST['fakeid_new_intervention']);
                  return ["result" => Status::NOT_ADDED,
                          "message" => $this->getMessage(ElementType::INTERVENTION, Errors::ERROR_ADD)];
               }

            } else {
               $this->addError(Errors::ERROR_INTERVENTION, Errors::ERROR_ADD, true, $_POST['fakeid_new_intervention']);
               return ["result" => Status::NOT_ADDED,
                       "message" => $this->getMessage(ElementType::INTERVENTION, Errors::ERROR_ADD)];
            }

         } else {
            $this->addError(Errors::ERROR_INTERVENTION, Errors::ERROR_ADD, true, $_POST['fakeid_new_intervention']);
            unset($intervention->fields['id']);
            if (isset($datas['cause'])) {
               $arrayRes = ["result" => Status::NOT_ADDED,
                            "cause" => $datas['cause']];
               switch ($datas['cause']) {
                  case Errors::ERROR_NAME_EXIST:
                     $arrayRes['message'] = $this->getMessage(ElementType::INTERVENTION, Errors::ERROR_NAME_EXIST);
                     break;
                  default:
                     $arrayRes['message'] = $this->getMessage(ElementType::INTERVENTION, Errors::ERROR_ADD);
                     break;
               }
               return $arrayRes;
            } else {
               return ["result" => Status::NOT_ADDED,
                       "message" => $this->getMessage(ElementType::INTERVENTION, Errors::ERROR_ADD)];
            }
         }
      }
   }

   public function deleteCriPrice($pView, $tabIdIntervention, $idCriPrice) {
      $error = false;

      if ($idCriPrice <= 0) {
         $error = true;
      } else {
         $criPrice = new PluginManageentitiesCriPrice();
         $criPrice->getFromDB($idCriPrice);

         $oldId = $criPrice->fields['id'];

         if (!isset($criPrice->fields['id']) || $criPrice->fields <= 0) {
            $error = true;
         } else {
            if ($criPrice->deleteFromDB($criPrice->fields)) {
               $pView->deleteRowOnTable("row_criprice_" . $oldId);
               $this->removeCriPrice($tabIdIntervention, $criPrice->fields['plugin_manageentities_critypes_id']);
               $error = false;
            } else {
               $error = true;
            }
         }
      }

      if ($error) {
         $this->addError(Errors::ERROR_CRIPRICE, Errors::ERROR_DELETE, 'true');
         return ["result" => Status::NOT_DELETED,
                 "message" => $this->getMessage(ElementType::CRIPRICE, Errors::ERROR_DELETE)];
      } else {
         return ["result" => Status::DELETED,
                 "message" => $this->getMessage(ElementType::CRIPRICE, Status::DELETED)];
      }
   }

   public function addCripriceToBase($pView, $tabIdIntervention, $typeIntervention) {
      $criPrice = $this->getCriPrice($tabIdIntervention, $typeIntervention);

      $intervention = $this->getContractDay($tabIdIntervention);

      // If criprice already saved
      if (isset($criPrice->fields['id']) && $criPrice->fields['id'] > 0) {
         // Update criprice
         $datas = $this->persistData($criPrice, DBOperation::UPDATE);
         if (isset($datas[Status::UPDATED]) && $datas[Status::UPDATED] == "true") {
            $this->addCriPrice($criPrice, $tabIdIntervention, $typeIntervention);

            $strTitleTab = isset($intervention->fields['name']) ? $intervention->fields['name'] : "";
            $pView->updateTabTitle($_POST['fakeid_new_intervention'], $strTitleTab, "div#mytabsinterventions", $intervention, $this->getMessage(ElementType::INTERVENTION, Status::SAVED));
            $pView->updateImgTabTitle(false, "'img_" . $intervention->getType() . $_POST['fakeid_new_intervention'] . "'", $this->getMessage(ElementType::INTERVENTION, Status::SAVED));

            $pView->updateListItems($intervention, $_POST['fakeid_new_intervention'], $criPrice, Status::UPDATED);

            $this->deleteError(Errors::ERROR_CRIPRICE, Errors::ERROR_UPDATE);
            return ["result" => Status::UPDATED,
                    "message" => $this->getMessage(ElementType::CRIPRICE, Status::UPDATED)];

         } else {
            $this->addError(Errors::ERROR_CRIPRICE, Errors::ERROR_ADD, 'true');
            return ["result" => Status::NOT_UPDATED,
                    "message" => $this->getMessage(ElementType::CRIPRICE, Errors::ERROR_UPDATE)];
         }

      } else {
         // Add criprice
         $datas = $this->persistData($criPrice, DBOperation::ADD);

         if (isset($datas[Status::ADDED]) && $datas[Status::ADDED] == "true") {
            $this->addCriPrice($criPrice, $tabIdIntervention, $typeIntervention);

            $strTitleTab = isset($intervention->fields['name']) ? $intervention->fields['name'] : "";

            $pView->updateTabTitle($_POST['fakeid_new_intervention'], $strTitleTab, "div#mytabsinterventions", $intervention, $this->getMessage(ElementType::INTERVENTION, Status::SAVED));
            $pView->updateImgTabTitle(false, "'img_PluginManageentitiesContractDay" . $_POST['fakeid_new_intervention'] . "'", $this->getMessage(ElementType::INTERVENTION, Status::SAVED));
            $pView->changeBtnName("btn_add_cprice_" . $tabIdIntervention, __("Update this price", "manageentities"));
            $pView->updateCPriceID($criPrice->fields['id']);
            $pView->updateListItems($intervention, $_POST['fakeid_new_intervention'], $criPrice, Status::ADDED);

            $this->deleteError(Errors::ERROR_CRIPRICE, Errors::ERROR_ADD);
            return ["result" => Status::ADDED,
                    "message" => $this->getMessage(ElementType::CRIPRICE, Status::ADDED)];

         } else {
            $this->addError(Errors::ERROR_CRIPRICE, Errors::ERROR_ADD, 'true');
            unset($criPrice->fields['id']);
            if (isset($datas['cause'])) {
               $arrayRes            = ["result" => Status::NOT_ADDED,
                                       "cause" => $datas['cause']];
               $arrayRes['message'] = $this->getMessage(ElementType::CRIPRICE, Errors::ERROR_ADD);
               return $arrayRes;
            } else {
               return ["result" => Status::NOT_ADDED,
                       "message" => $this->getMessage(ElementType::CRIPRICE, Errors::ERROR_ADD)];
            }
         }
      }
   }

   public function deleteContractManagementType($pView) {
      $restrict = ["`glpi_plugin_manageentities_contracts`.`entities_id`"  => $_POST['entities_id'],
                   "`glpi_plugin_manageentities_contracts`.`contracts_id`" => $_POST['contracts_id']];
      $dbu      = new DbUtils();

      $pluginContracts = $dbu->getAllDataFromTable("glpi_plugin_manageentities_contracts", $restrict);
      $pluginContract  = reset($pluginContracts);
      $oldCMTid        = $pluginContract['id'];

      $objContractType = new PluginManageentitiesContract();
      $objContractType->getFromDB($oldCMTid);

      $deleteResult = $this->persistData($objContractType, DBOperation::DELETE);

      if (isset($deleteResult[Status::DELETED]) && $deleteResult[Status::DELETED] == "true") {
         $objContractType->getEmpty();
         $this->setContractManagementType($objContractType);
         $pView->reinitContractManagementType(ElementType::CONTRACT_MANAGEMENT_TYPE);
         $this->deleteError(Errors::ERROR_CONTRACT_MANAGEMENT_TYPE, Errors::ERROR_DELETE);
         return ["result" => Status::DELETED,
                 "message" => $this->getMessage(ElementType::CONTRACT_MANAGEMENT_TYPE, Status::DELETED)];
      } else {
         $this->addError(Errors::ERROR_CONTRACT_MANAGEMENT_TYPE, Errors::ERROR_DELETE, 'true');
         return ["result" => Status::NOT_DELETED,
                 "message" => $this->getMessage(ElementType::CONTRACT_MANAGEMENT_TYPE, Errors::ERROR_DELETE)];
      }
   }

   public function getQueryForDFContract($item) {
      $dbu = new DbUtils();
      $ID  = $item->getField('id');

      if ($item->isNewID($ID)) {
         return false;
      }
      if (($item->getType() != 'Ticket')
          && ($item->getType() != 'KnowbaseItem')
          && ($item->getType() != 'Reminder')
          && !Session::haveRight('document', READ)) {
         return false;
      }

      if (!$item->can($item->fields['id'], READ)) {
         return false;
      }

      if (empty($withtemplate)) {
         $withtemplate = 0;
      }
      $linkparam = '';

      if (get_class($item) == 'Ticket') {
         $linkparam = "&amp;tickets_id=" . $item->fields['id'];
      }

      $canedit      = $item->canadditem('Document');
      $rand         = mt_rand();
      $is_recursive = $item->isRecursive();


      if (isset($_POST["order"]) && ($_POST["order"] == "ASC")) {
         $order = "ASC";
      } else {
         $order = "DESC";
      }

      if (isset($_POST["sort"]) && !empty($_POST["sort"])) {
         $sort = "`" . $_POST["sort"] . "`";
      } else {
         $sort = "`assocdate`";
      }

      $query = "SELECT `glpi_documents_items`.`id` AS assocID,
                       `glpi_documents_items`.`date_mod` AS assocdate,
                       `glpi_entities`.`id` AS entityID,
                       `glpi_entities`.`completename` AS entity,
                       `glpi_documentcategories`.`completename` AS headings,
                       `glpi_documents`.*
                FROM `glpi_documents_items`
                LEFT JOIN `glpi_documents`
                          ON (`glpi_documents_items`.`documents_id`=`glpi_documents`.`id`)
                LEFT JOIN `glpi_entities` ON (`glpi_documents`.`entities_id`=`glpi_entities`.`id`)
                LEFT JOIN `glpi_documentcategories`
                        ON (`glpi_documents`.`documentcategories_id`=`glpi_documentcategories`.`id`)
                WHERE `glpi_documents_items`.`items_id` = '$ID'
                      AND `glpi_documents_items`.`itemtype` = '" . $item->getType() . "' ";

      if (Session::getLoginUserID()) {
         $query .= $dbu->getEntitiesRestrictRequest(" AND", "glpi_documents", '', '', true);
      } else {
         // Anonymous access from FAQ
         $query .= " AND `glpi_documents`.`entities_id`= '0' ";
      }

      $query .= " ORDER BY $sort $order ";

      return ["query" => $query,
              "rand" => $rand,
              "linkparam" => $linkparam];
   }

   private function manageMEntitiesContacts($contact, $typeInsert, $pView) {
      $mEntitiesContact = new PluginManageentitiesContact();

      $mEntitiesContact->fields['contacts_id'] = $contact->fields['id'];
      $mEntitiesContact->fields['entities_id'] = $contact->fields['entities_id'];

      switch ($typeInsert) {
         case DBOperation::ADD:
            if ($this->getContactManager() == $_POST['fakeid_new_contact']) {
               $mEntitiesContact->fields['is_default'] = 1;
               $datas                                  = $this->persistData($mEntitiesContact, DBOperation::ADD);
               $this->manageMEntitiesContacts($contact, "update-allothers", $pView);
            } else {
               $mEntitiesContact->fields['is_default'] = 0;
               $datas                                  = $this->persistData($mEntitiesContact, DBOperation::ADD);
            }
            break;

         case DBOperation::UPDATE:
            $mEntitiesContact->getFromDBByCrit(['contacts_id' => $contact->fields['id']]);
            $mEntitiesContact->fields['contacts_id'] = $contact->fields['id'];
            $mEntitiesContact->fields['entities_id'] = $contact->fields['entities_id'];

            if ($this->getContactManager() == $_POST['fakeid_new_contact']) {
               $mEntitiesContact->fields['is_default'] = 1;
               $datas                                  = $this->persistData($mEntitiesContact, DBOperation::UPDATE);
               $this->manageMEntitiesContacts($contact, "update-allothers", $pView);
            } else {
               $mEntitiesContact->fields['is_default'] = 0;
               $datas                                  = $this->persistData($mEntitiesContact, DBOperation::UPDATE);
            }
            break;

         case 'update-allothers':
            $dbu       = new DbUtils();
            $condition = ["`entities_id`" => $contact->fields['entities_id'],
                          'NOT'           => ["`contacts_id`" => $contact->fields['id']]];
            $contacts  = $dbu->getAllDataFromTable(PluginManageentitiesContact::getTable(), $condition);

            if (sizeof($contacts) > 0) {
               foreach ($contacts as $tmpMEContact) {
                  $tmpContact = new Contact();
                  $tmpContact->getFromDB($tmpMEContact['contacts_id']);
                  $this->manageMEntitiesContacts($tmpContact, "update--force-false", $pView);
               }
            }
            $pView->updateDropdownsContactManager();
            break;

         case 'update--force-false':
            $mEntitiesContact->getFromDBByCrit(['contacts_id' => $contact->fields['id'],
                                                'entities_id' => $contact->fields['entities_id']]);
            $mEntitiesContact->fields['is_default'] = 0;
            $this->persistData($mEntitiesContact, DBOperation::UPDATE);
            break;

         default:
            break;
      }
   }

   private function persistData(&$object, $typeInsert) {
      switch ($typeInsert) {
         case DBOperation::ADD:
            if ($object->getType() == Entity::getType()) {
               $tmpObj = clone $object;
               $tmpObj->getEmpty();
               $tmpObj->getFromDBByCrit(['name' => $object->fields['name']]);
               if (isset($tmpObj->fields['id']) && $tmpObj->fields['id'] > 0) {
                  return [Status::NOT_ADDED => false,
                          'cause' => Errors::ERROR_NAME_EXIST];
               }
            }

            if (isset($object->fields['id']))
               unset($object->fields['id']);

            if ($object->add($object->fields)) {
               return [Status::ADDED => true];
            } else {
               return [Status::NOT_ADDED => true];
            }
            break;

         case DBOperation::UPDATE:
            if ($object->update($object->fields)) {
               return [Status::UPDATED => true];
            } else {
               return [Status::NOT_UPDATED => true];
            }
            break;

         case DBOperation::DELETE:
            if ($object->delete($object->fields)) {
               return [Status::DELETED => true];
            } else {
               return [Status::NOT_DELETED => true];
            }
            break;

         default:
            break;
      }
   }

   public function storeDatasInSession($type, $object) {
      $config = PluginManageentitiesConfig::getInstance();

      switch ($type) {
         case ElementType::ENTITY:
            $object->fields['name']        = isset($_POST['new_entity_name']) ? $_POST['new_entity_name'] : "";
            $object->fields['comment']     = isset($_POST['new_entity_comment']) ? $_POST['new_entity_comment'] : "";
            $object->fields['phonenumber'] = isset($_POST['new_entity_phone']) ? $_POST['new_entity_phone'] : "";
            $object->fields['fax']         = isset($_POST['new_entity_fax']) ? $_POST['new_entity_fax'] : "";
            $object->fields['website']     = isset($_POST['new_entity_website']) ? $_POST['new_entity_website'] : "";
            $object->fields['email']       = isset($_POST['new_entity_email']) ? $_POST['new_entity_email'] : "";
            $object->fields['postcode']    = isset($_POST['new_entity_postcode']) ? $_POST['new_entity_postcode'] : "";
            $object->fields['state']       = isset($_POST['new_entity_state']) ? $_POST['new_entity_state'] : "";
            $object->fields['country']     = isset($_POST['new_entity_country']) ? $_POST['new_entity_country'] : "";
            $object->fields['town']        = isset($_POST['new_entity_city']) ? $_POST['new_entity_city'] : "";
            $object->fields['address']     = isset($_POST['new_entity_address']) ? $_POST['new_entity_address'] : "";
            $object->fields['entities_id'] = isset($_POST['new_entity_entities_id']) ? $_POST['new_entity_entities_id'] : "";

            $this->setEntity($object);
            break;

         case ElementType::CONTACT:
            $object->fields['name']            = $_POST['new_contact_name'];
            $object->fields['firstname']       = $_POST['new_contact_firstname'];
            $object->fields['phone']           = $_POST['new_contact_phone'];
            $object->fields['phone2']          = $_POST['new_contact_phone2'];
            $object->fields['comment']         = $_POST['new_contact_comment'];
            $object->fields['mobile']          = $_POST['new_contact_mobile'];
            $object->fields['address']         = $_POST['new_contact_address'];
            $object->fields['fax']             = $_POST['new_contact_fax'];
            $object->fields['postcode']        = $_POST['new_contact_postcode'];
            $object->fields['town']            = $_POST['new_contact_town'];
            $object->fields['email']           = $_POST['new_contact_email'];
            $object->fields['state']           = $_POST['new_contact_state'];
            $object->fields['country']         = $_POST['new_contact_country'];
            $object->fields['contacttypes_id'] = $_POST['new_contact_contact_type'];
            $object->fields['usertitles_id']   = $_POST['new_contact_user_title'];
            $object->fields['is_deleted']      = 0;

            if ($_POST['new_contact_subentity_yn'] == true) {
               $object->fields['is_recursive'] = 1;
            } else {
               $object->fields['is_recursive'] = 0;
            }

            $this->addContact($object, $_POST['fakeid_new_contact']);

            if (isset($_POST['new_contact_is_manager']) && $_POST['new_contact_is_manager'] == 1) {
               $this->setContactManager($_POST['fakeid_new_contact']);
            }
            break;

         case ElementType::CONTRACT:
            $object->fields['name'] = $_POST['new_contract_name'];

            if (isset($_POST['new_contract_date_begin']) && $_POST['new_contract_date_begin'] != "" && $this->isDateFormatOK($_POST['new_contract_date_begin'])) {
               $object->fields['begin_date'] = date('Y-m-d', strtotime($_POST['new_contract_date_begin']));
            } else {
               $object->fields['begin_date'] = null;
            }

            if (isset($_POST['new_contract_date_end']) && $_POST['new_contract_date_end'] != "" && $this->isDateFormatOK($_POST['new_contract_date_end'])) {
               $object->fields['end_date'] = date('Y-m-d', strtotime($_POST['new_contract_date_end']));
            } else {
               $object->fields['end_date'] = null;
            }

            $object->fields['num']                 = $_POST['new_contract_num'];
            $object->fields['duration']            = $_POST['new_contract_duration'];
            $object->fields['notice']              = $_POST['new_contract_notice'];
            $object->fields['billing']             = $_POST['new_contract_billing'];
            $object->fields['accounting_number']   = $_POST['new_contract_accounting_number'];
            $object->fields['periodicity']         = $_POST['new_contract_periodicity'];
            $object->fields['renewal']             = $_POST['new_contract_renewal'];
            $object->fields['max_links_allowed']   = $_POST['new_contract_max_links_allowed'];
            $object->fields['comment']             = $_POST['new_contract_comment'];
            $object->fields['is_template']         = 0;
            $object->fields['entities_id']         = $_POST['new_contract_entity_id'];
            $object->fields['week_begin_hour']     = $_POST['new_contract_week_begin_hour'];
            $object->fields['week_end_hour']       = $_POST['new_contract_week_end_hour'];
            $object->fields['saturday_begin_hour'] = $_POST['new_contract_sat_degin_hour'];
            $object->fields['saturday_end_hour']   = $_POST['new_contract_sat_end_hour'];
            $object->fields['monday_begin_hour']   = $_POST['new_contract_mon_begin_hour'];
            $object->fields['monday_end_hour']     = $_POST['new_contract_mon_end_hour'];
            $object->fields['use_monday']          = $_POST['new_contract_use_monday'];
            $object->fields['use_saturday']        = $_POST['new_contract_use_saturday'];
            $object->fields['is_deleted']          = 0;

            if (isset($_POST['new_contract_contracttype_id']) && $_POST['new_contract_contracttype_id'] > 0) {
               $object->fields['contracttypes_id'] = $_POST['new_contract_contracttype_id'];
            } else {
               $object->fields['contracttypes_id'] = 0;
            }

            if ($_POST['new_contract_subentity_yn'] == true) {
               $object->fields['is_recursive'] = 1;
            } else {
               $object->fields['is_recursive'] = 0;
            }

            $this->setContract($object);
            break;

         case ElementType::INTERVENTION:
            $object->fields['name'] = $_POST['new_intervention_name'];

            if (isset($_POST['new_intervention_begin_date']) && $_POST['new_intervention_begin_date'] != "" && $this->isDateFormatOK($_POST['new_intervention_begin_date'])) {
               $object->fields['begin_date'] = date('Y-m-d', strtotime($_POST['new_intervention_begin_date']));
            } else {
               $object->fields['begin_date'] = null;
            }
            if (isset($_POST['new_intervention_end_date']) && $_POST['new_intervention_end_date'] != "" && $this->isDateFormatOK($_POST['new_intervention_end_date'])) {
               $object->fields['end_date'] = date('Y-m-d', strtotime($_POST['new_intervention_end_date']));
            } else {
               $object->fields['end_date'] = null;
            }
            if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
               $object->fields['contract_type'] = $_POST['contract_type'];
            } else {
               $object->fields['contract_type'] = 0;
            }
            $object->fields['nbday']                                   = $_POST['new_intervention_nbday'];
            $object->fields['report']                                  = $_POST['new_intervention_report'];
            $object->fields['charged']                                 = $_POST['new_intervention_charged'] == 'true' ? 1 : 0;
            $object->fields['plugin_manageentities_contractstates_id'] = $_POST['new_intervention_contractstate_id'];

            $this->addContractDay($object, $_POST['fakeid_new_intervention']);
            break;

         case ElementType::CONTRACT_MANAGEMENT_TYPE:
            if ($this->isDateFormatOK($_POST['date_signature'])) {
               $object->fields['date_signature'] = date('Y-m-d', strtotime($_POST['date_signature']));
            } else {
               unset($object->fields['date_signature']);
            }

            if ($this->isDateFormatOK($_POST['date_renewal'])) {
               $object->fields['date_renewal'] = date('Y-m-d', strtotime($_POST['date_renewal']));
            } else {
               unset($object->fields['date_renewal']);
            }

            if ($_POST['contract_added'] == "true") {
               $object->fields['contract_added'] = 1;
            } else {
               $object->fields['contract_added'] = 0;
            }

            if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
               $object->fields['contract_type'] = $_POST['contract_type'];
            } else {
               $object->fields['contract_type'] = 0;
            }
            $object->fields['show_on_global_gantt'] = $_POST['show_on_global_gantt'];
            $object->fields['contracts_id']         = $_POST['contracts_id'];
            $object->fields['entities_id']          = $_POST['entities_id'];
            $object->fields['is_default']           = 0;
            $object->fields['management']           = 0;
            $object->fields['moving_management']    = $_POST['moving_management'];
            $object->fields['duration_moving']      = $_POST['duration_moving'];

            if (isset($_POST['refacturable_costs']) && $_POST['refacturable_costs'] == "true") {
               $object->fields['refacturable_costs'] = 1;
            } else {
               $object->fields['refacturable_costs'] = 0;
            }

            $this->setContractManagementType($object);
            break;

         case ElementType::CRIPRICE:
            $object = new PluginManageentitiesCriPrice();
            if (isset($_POST['id_criprice'])) {
               $object->fields['id'] = $_POST['id_criprice'];
            }
            $object->fields['entities_id']                           = $_POST['entities_id'];
            $object->fields['plugin_manageentities_critypes_id']     = $_POST['new_criprice_critypes'];
            $object->fields['plugin_manageentities_contractdays_id'] = $_POST['plugin_manageentities_contractdays_id'];
            $object->fields['is_default']                            = $_POST['new_criprice_is_default'] == true ? 1 : 0;
            $object->fields['price']                                 = $_POST['new_criprice_pricefield'];

            $this->addCriPrice($object, $_POST['fakeid_new_intervention'], $_POST['new_criprice_critypes']);
            break;

         default:
            break;
      }
   }

   public function getCriPriceFromType($post) {
      $criPrice = new PluginManageentitiesCriPrice();
      $criPrice->getFromDBByCrit(['plugin_manageentities_critypes_id'     => $post['new_criprice_critype'],
                                  'entities_id'                           => $post['entities_id'],
                                  'plugin_manageentities_contractdays_id' => $post['plugin_manageentities_contractdays_id']]);
      if (isset($criPrice->fields['id']) && $criPrice->fields['id'] > 0) {
         return $criPrice;
      } else {
         return null;
      }
   }

   /* ------------------------------------------------------------------------------------
    *    Utility
    * ------------------------------------------------------------------------------------ */

   function isDateFormatOK($date, $format = 'd-m-Y') {
      $d = DateTime::createFromFormat($format, $date);
      return $d && $d->format($format) == $date;
   }

   public function getCripriceFromDB($critypeId, $entitiesId) {
      $cprice = new PluginManageentitiesCriPrice();
      $cprice->getFromDBByCrit(['entities_id'                       => $entitiesId,
                                'plugin_manageentities_critypes_id' => $critypeId]);
      if (isset($cprice->fields['id']) && $cprice->fields['id'] > 0) {
         return $cprice;
      } else {
         return false;
      }
   }

   public function addError($id, $type, $val, $opt = null) {
      $this->errors = [];
      if ($opt == null) {
         $this->errors[$id][$type] = $val;
      } else {
         $this->errors[$id][$type][$opt] = $val;
      }

      $this->serializeInSession();
   }

   public function deleteError($id, $type, $opt = null) {
      if (isset($this->errors[$id][$type])) {
         if ($opt != null) {
            if (isset($this->errors[$id][$type][$opt]) && is_array($this->errors[$id][$type][$opt])) {
               unset($this->errors[$id][$type][$opt]);
            }
         } else {
            unset($this->errors[$id][$type]);
         }
         $this->serializeInSession();
      }
   }

   public function getIconSrcFromExtension($ext) {
      $docType = new DocumentType();
      $docType->getFromDBByCrit(['ext' => $ext]);
      if (isset($docType->fields['id']) && $docType->fields['id'] > 0) {
         return $docType->fields['icon'];
      } else {
         return "";
      }
   }

   public function getFileExtension($fileName) {
      return substr(strrchr($fileName, '.'), 1);
   }

   /* ------------------------------------------------------------------------------------
    *    Getters & Setters
    * ------------------------------------------------------------------------------------ */

   public function getContacts($i = -1) {
      if ($i == -1) {
         return $this->contacts;
      } else {
         return isset($this->contacts[$i]) ? $this->contacts[$i] : null;
      }
   }

   public function setContacts($value) {
      $this->contacts = $value;
      $this->serializeInSession();
   }

   public function getContactManager() {
      return $this->contactManager;
   }

   public function setContactManager($value) {
      $this->contactManager = $value;
      $this->serializeInSession();
   }

   public function addContact($contact, $tabId) {
      $this->contacts[$tabId] = $contact;
      $this->serializeInSession();
   }

   public function getContractDays() {
      return $this->contractdays;
   }

   public function setContractDays($value) {
      $this->contractdays = $value;
      $this->serializeInSession();
   }

   public function getContractDay($i = -1) {
      if ($i == -1) {
         return $this->contractdays;
      } else {
         return isset($this->contractdays[$i]) ? $this->contractdays[$i] : null;
      }
   }

   public function addCriPrice($value, $tabIdInterv, $idTypeInterv) {
      if ($value != "") {
         if (!isset($this->contractdays['criprice'][$tabIdInterv][$idTypeInterv])) {
            $this->nbCriPrice++;
         }
         $this->contractdays['criprice'][$tabIdInterv][$idTypeInterv] = $value;
         $this->serializeInSession();
      }
   }

   public function removeCriPrice($tabIdInterv, $idTypeInterv) {
      if (isset($this->contractdays['criprice'][$tabIdInterv][$idTypeInterv])) {
         $this->nbCriPrice--;
         unset($this->contractdays['criprice'][$tabIdInterv][$idTypeInterv]);
         if (sizeof($this->contractdays['criprice'][$tabIdInterv]) == 0) {
            unset($this->contractdays['criprice'][$tabIdInterv]);
         }
         $this->serializeInSession();
      }
   }

   public function getAllCriPrice() {
      return $this->contractdays['criprice'];
   }

   public function getCriPrice($tabIdInterv = -1, $idTypeInterv = -1) {
      if ($tabIdInterv == -1) {
         return null;
      } else if ($idTypeInterv == -1) {
         return isset($this->contractdays['criprice'][$tabIdInterv]) ? $this->contractdays['criprice'][$tabIdInterv] : null;
      } else {
         return isset($this->contractdays['criprice'][$tabIdInterv][$idTypeInterv]) ? $this->contractdays['criprice'][$tabIdInterv][$idTypeInterv] : null;
      }
   }

   public function addContractDay($contractDay, $tabId) {
      $this->contractdays[$tabId] = $contractDay;
      $this->serializeInSession();
   }

   public function getEntity() {
      return $this->entity;
   }

   public function setEntity($value) {
      $this->entity = $value;
      $this->serializeInSession();
   }

   public function getContract() {
      return $this->contract;
   }

   public function setContract($value) {
      $this->contract = $value;
      $this->serializeInSession();
   }

   public function getContractManagementType() {
      return $this->contractManagementType;
   }

   public function setContractManagementType($value) {
      $this->contractManagementType = $value;
      $this->serializeInSession();
   }

   public function getContractTemplate() {
      return $this->contractTemplate;
   }

   public function setContractTemplate($value) {
      $this->contractTemplate = $value;
      $this->serializeInSession();
   }

   public function getNbContact() {
      return $this->nbContact;
   }

   public function setNbContact($value) {
      $this->nbContact = $value;
      $this->serializeInSession();
   }

   public function getNbContractDays() {
      return $this->nbContractDay;
   }

   public function getNbCriPrice() {
      //      return sizeof($this->nbContractDay['criprice']);
      return $this->nbCriPrice;
   }

   public function setNbContractDays($value) {
      $this->nbContractDay = $value;
      $this->serializeInSession();
   }

   public function getIdContractTemplate() {
      return $this->idContractTemplate;
   }

   public function setIdContractTemplate($value) {
      $this->idContractTemplate = $value;
      $this->serializeInSession();
   }

   public function getIsContractTemplate() {
      return $this->isContractTemplate;
   }

   public function setIsContractTemplate($value) {
      $this->isContractTemplate = $value;
      $this->serializeInSession();
   }

   public function getErrors() {
      return $this->errors;
   }

   public function setErrors($value) {
      $this->errors = $value;
      $this->serializeInSession();
   }

   static public function getType() {
      return "PluginManageentitiesAddElementsModel";
   }

}