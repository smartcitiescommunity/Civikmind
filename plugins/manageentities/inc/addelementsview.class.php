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

include_once(GLPI_ROOT . '/plugins/manageentities/common/commonGLPIView.class.php');

class    PluginManageentitiesAddElementsView extends CommonGLPIView {

   private $pModel;

   public function __construct() {

   }

   // ----------------------------------------------------------------------------------------------------------------------------------------------------------
   // Form show functions
   // ----------------------------------------------------------------------------------------------------------------------------------------------------------

   public function showForm() {
      $this->showTitle();
      // addEntite
      $entityContent = $this->showFormEntity();
      // addContacts
      $contactContent = $this->showFormContact();
      // addContract
      $contractContent = $this->showFormContract();

      $this->initFormAddPDFContract();
      // interventions
      $interventionContent = $this->showFormIntervention();

      // Show button 'Add all in DB'
      //      $allContent = $this->showBtnAddAll();
      //      echo "<br/><br/>";
      // Init JS Functions
      $this->initJSFunctions($entityContent, $contactContent, $contractContent, $interventionContent);
   }

   public function showTitle() {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

      echo "<div id='mytabsaddelement' class='tab_cadre_fixe'>";
      echo "   <ul id='mytabsaddelementtitle' class='center'>";
      echo "      <li class='center'> <a href='#elementtitle'>" . __("Add elements form", "manageentities") . "</a></li>";
      echo "</ul>";
      echo "</span>";
      echo "</div>";
   }

   public function showFormEntity() {
      echo "<div id='mytabsentity' class='tab_cadre_fixe'>";
      echo "   <ul id='tabentitytitle'>";

      if (isset($this->pModel->getEntity()->fields['name']) && $this->pModel->getEntity()->fields['name'] != "" && !$this->pModel->isOnError(Errors::ERROR_ENTITY, Errors::ERROR_ALL)) {
         echo "      <li> ";
         echo "<a href='#tabs-1'>" . $this->pModel->getEntity()->getField("name") . $this->showImgSaved($this->pModel->getEntity(), $this->pModel->getMessage(ElementType::ENTITY, Status::SAVED), 1) . "</a></li>";
      } else {
         echo "      <li> <a href='#tabs-1'>" . __("New entity", "manageentities") . "</a></li>";
      }


      echo "</ul>";
      $entityContent = $this->showFormAddEntity();
      echo "</div>";
      echo "<br/><br/>";
      return $entityContent;
   }

   public function showFormContact() {
      if (sizeof($this->pModel->getContacts()) > 1) {
         echo "<div id='mytabscontacts' class='tab_cadre_fixe'>";
         echo "   <ul id='tabcontacttitle'>";
         for ($i = 1; $i <= $this->pModel->getNbContact(); $i++) {
            $contact = $this->pModel->getContacts($i);
            if (($contact != null) &&
                ((isset($contact->fields['name']) && $contact->fields['name'] != "") || (isset($contact->fields['firstname']) && $contact->fields['firstname'] != "")) &&
                !$this->pModel->isOnError(Errors::ERROR_CONTACT, Errors::ERROR_ALL)) {
               echo "      <li>";
               echo "<a href='#tabs-" . ($i) . "'>" . $contact->fields['firstname'] . " " . $contact->fields['name'] . $this->showImgSaved($this->pModel->getContacts($i), $this->pModel->getMessage(ElementType::CONTACT, Status::SAVED), $i) . "</a></li>";
            } else {
               echo "      <li><a href='#tabs-" . ($i) . "'>" . __("New contact", "manageentities") . "</a></li>";
            }
         }
         echo "   </ul>";
         for ($i = 1; $i <= $this->pModel->getNbContact(); $i++) {
            $contactContent[$i] = $this->showFormAddContact($i);
         }
         echo "</div>";
      } else {

         $contacts = $this->pModel->getContacts();
         $contact  = $contacts[1];

         echo "<div id='mytabscontacts' class='tab_cadre_fixe'>";
         echo "   <ul>";
         $strTitleTab = isset($contact->fields['firstname']) ? $contact->fields['firstname'] : "";
         $strTitleTab .= " ";
         $strTitleTab .= isset($contact->fields['name']) ? $contact->fields['name'] : "";
         if (trim($strTitleTab) == "" || $this->pModel->isOnError(Errors::ERROR_CONTACT, Errors::ERROR_ALL)) {
            $strTitleTab = __("New contact", "manageentities");
         }
         echo "      <li>";
         echo "<a href='#tabs-1'>" . $strTitleTab . $this->showImgSaved($contact, $this->pModel->getMessage(ElementType::CONTACT, Status::SAVED), 1) . "</a></li>";
         echo "   </ul>";
         $contactContent[1] = $this->showFormAddContact(1);
         echo "</div>";
      }
      echo "<br/><br/>";
      return $contactContent;
   }

   /**
    * @param array $params
    *
    * @return array
    */
   public function showFormContract($params = []) {
      global $CFG_GLPI;

      if (isset($params["presales"])) {
         $quotation = new PluginPresalesBusiness();
         $quotation->getFromDB($params['id_quotation']);
         $contract                 = $this->pModel->getContract();
         $contract->fields["name"] = $quotation->fields["name"];
         $this->pModel->setContract($contract);
      }
      $contract = $this->pModel->getContract();
      echo "<div id='mytabscontract' class='tab_cadre_fixe'>";
      echo "   <ul id='tabcontracttitle'>";
      $strTitleTab = (isset($contract->fields['name']) && $contract->fields['name'] != "" && $this->pModel->getIsContractTemplate() != 1 && !$this->pModel->isOnError(Errors::ERROR_CONTRACT, Errors::ERROR_ALL)) ? $contract->fields['name'] : __("New contract", "manageentities");

      echo "      <li> ";
      echo "<a href='#tabs-1'>" . $strTitleTab . $this->showImgSaved($this->pModel->getContract(), $this->pModel->getMessage(ElementType::CONTRACT, Status::SAVED), 1) . "</a></li>";
      echo "</ul>";

      if (isset($params["presales"])) {
         echo "<form name='contract' id='contract' method='post'
            action='" . $CFG_GLPI['root_doc'] . "/plugins/presales/front/finalizeopportunity.form.php'>";
         echo Html::hidden("id_quotation", ["value" => $params["id_quotation"]]);
      }
      echo "<div id='divContract' class='center' >";
      $contractContent = $this->showFormAddContract($params);
      echo "</div>";

      if (isset($params["presales"])) {
         echo "<div class='center'>";
         echo "<input type='submit' name='add_contract' value=\"" . _x('button', 'Next') . "\" class='submit'>";
         echo "</div>";
         Html::closeForm();
      }

      echo "</div>";

      return $contractContent;

   }

   public function showFormIntervention($params = []) {

      if (isset($params["presales"])) {
         $quotationline  = new PluginPresalesQuotationLine();
         $quotationlines = $quotationline->find(["plugin_presales_businesses_id" => $params["id_quotation"]]);
         $i              = 1;
         foreach ($quotationlines as $line) {
            $intervention = new PluginManageentitiesContractDay();
            $intervention->getEmpty();
            $intervention->fields["name"]  = $line["designation"];
            $intervention->fields["nbday"] = $line["amount"];
            //            $intervention->fields[""] = $line->field[""];
            $this->pModel->addContractDay($intervention, $i);
            $this->pModel->setNbContractDays($i);
            $i++;
         }

      }
      //      if(isset($params["presales"])) {
      //         echo "<form name='contract' id='contract' method='post'
      //            action='" . $CFG_GLPI['root_doc'] . "/plugins/presales/front/finalizeopportunity.form.php'>";
      //         echo Html::hidden("id_quotation",["value"=>$params["id_quotation"]]);
      //      }
      if (sizeof($this->pModel->getContractDays()) > 1) {
         echo "<div id='mytabsinterventions' class='tab_cadre_fixe'>";
         echo "   <ul id='tabinterventiontitle'>";
         for ($i = 1; $i <= $this->pModel->getNbContractDays(); $i++) {
            $intervention = $this->pModel->getContractDay($i);
            if (($intervention != null) && ((isset($intervention->fields['name']) && $intervention->fields['name'] != "")) && !$this->pModel->isOnError(Errors::ERROR_INTERVENTION, Errors::ERROR_ALL, $i)) {
               echo "      <li>";
               echo "<a href='#tabs-" . ($i) . "'>" . $intervention->fields['name'] . $this->showImgSaved($this->pModel->getContractDay($i), $this->pModel->getMessage(ElementType::INTERVENTION, Status::SAVED), $i) . "</a></li>";
            } else {
               echo "      <li><a href='#tabs-" . ($i) . "'>" . __("New intervention", "manageentities") . "</a></li>";
            }
         }
         echo "   </ul>";
         for ($i = 1; $i <= $this->pModel->getNbContractDays(); $i++) {
            $interventionContent[$i] = $this->showFormAddInterventions($i, $params);
         }
         echo "</div>";
      } else {

         $interventions = $this->pModel->getContractDays();
         $intervention  = $interventions[1];
         echo "<div id='mytabsinterventions' class='tab_cadre_fixe'>";
         echo "   <ul>";
         $strTitleTab = isset($intervention->fields['name']) ? $intervention->fields['name'] : "";
         if (trim($strTitleTab) == "" || $this->pModel->isOnError(Errors::ERROR_INTERVENTION, Errors::ERROR_ALL, 1)) {
            $strTitleTab = __("New intervention", "manageentities");
         }
         echo "      <li>";
         echo "<a href='#tabs-1'>" . $strTitleTab . $this->showImgSaved($intervention, $this->pModel->getMessage(ElementType::INTERVENTION, Status::SAVED), 1) . "</a></li>";
         echo "   </ul>";
         $interventionContent[1] = $this->showFormAddInterventions(1, $params);
         echo "</div>";
      }
      if (!isset($params["presales"])) {

         echo "<br/><br/>";

         $this->showBtnRAZ();
      } else {
         $this->pModel->setNbContractDays(1);
      }


      return $interventionContent;

   }


   /**
    * L'idee globale, c'est que chacun des parametres de la fonction initJSFunctions contient au
    * minimum :
    *    - ['listId'] : la liste des id des differents input oe les donnees e sauvegarder sont
    *    - ['idDivAjax'] : l'id de la div oe les resultats du traitement seront  affiches
    *    - ['params'] : different parametres (dont le 'action' qui definit quel traitement doit
    * etre
    *                   effectue depuis le controlleur)
    *
    * Pour contact et intervention, il contiennent en plus :
    *    - ['paramsAddNewContact'] : l'action / l'id de la div sont differentes d'un ajout de
    * contact / intervention
    *
    * @param $entityContent
    * @param $contactContent
    * @param $contractContent
    * @param $interventionContent
    */
   public function initJSPresalesContract($contractContent) {
      $pModel                   = $this->pModel;
      $formIds['entity']        = 0;
      $formIds['contacts']      = 0;
      $formIds['interventions'] = 0;
      $formIds['contract']      = 1;

      $this->activateForms($formIds);
      foreach ($contractContent['listIds'] as $ids => $val) {
         $allContent['listIds'][$ids]    = $val;
         $allContent[1]['listIds'][$ids] = $val;
      }
      $contractContent['params']['action'] = Action::ADD_ONLY_CONTRACT;
      $this->showJSfunction("addOnlyContract" . $contractContent['params']['rand'], $contractContent['idDivAjax'], $pModel->getUrl(), $contractContent['listIds'], $contractContent['params']);
   }

   public function initJSPresalesIntervention($interventionContent) {
      $pModel                   = $this->pModel;
      $formIds['entity']        = 0;
      $formIds['contacts']      = 0;
      $formIds['contract']      = 0;
      $formIds['interventions'] = 0;
      $formIds['interventions'] = 1;


      $this->activateForms($formIds);

      foreach ($interventionContent as $iContent) {
         foreach ($iContent['listIds'] as $ids => $val) {
            $allContent['listIds'][$ids]    = $val;
            $allContent[1]['listIds'][$ids] = $val;
         }
      }
      if (is_array($interventionContent)) {
         $i = 1;
         foreach ($interventionContent as $intervention) {
            $this->showJSfunction("addOnlyIntervention" . $i, $intervention['idDivAjax'], $pModel->getUrl(), $intervention['listIds'], $intervention['params'], $intervention['idDivStakeholdersAjax']);
            $this->showJSfunction("addAnotherIntervention" . $i, $intervention['idDivNewIntervention'], $pModel->getUrl(), $intervention['listIds'], $intervention['paramsAddNewIntervention']);
            $i++;
         }
      }


   }

   private function initJSFunctions($entityContent, $contactContent, $contractContent, $interventionContent) {

      $pModel = $this->pModel;
      // forms shown
      $formIds = [];
      // Entity
      if (isset($pModel->getEntity()->fields['id']) && $pModel->getEntity()->fields['id'] > 0) {
         $formIds['entity'] = 1;
      } else {
         $formIds['entity'] = 0;
      }
      // Contacts
      $formIds['contacts'] = 0;
      foreach ($pModel->getContacts() as $contact) {
         if (isset($contact->fields['id']) && $contact->fields['id'] > 0) {
            $formIds['contacts'] = 1;
            break;
         }
      }
      // Contract
      if (isset($pModel->getContract()->fields['id']) && $pModel->getContract()->fields['id'] > 0) {
         $formIds['contract'] = 1;
      } else {
         $formIds['contract'] = 0;
      }
      // periods
      $formIds['interventions'] = 0;
      foreach ($pModel->getContractDay() as $contractDay) {
         if (isset($contractDay->fields['id']) && $contractDay->fields['id'] > 0) {
            $formIds['interventions'] = 1;
            break;
         }
      }

      $this->activateForms($formIds);

      // init add all element
      foreach ($entityContent['listIds'] as $ids => $val) {
         $allContent['listIds'][$ids]    = $val;
         $allContent[1]['listIds'][$ids] = $val;
      }
      foreach ($contactContent as $cContent) {
         foreach ($cContent['listIds'] as $ids => $val) {
            $allContent['listIds'][$ids]    = $val;
            $allContent[1]['listIds'][$ids] = $val;
         }
      }
      foreach ($contractContent['listIds'] as $ids => $val) {
         $allContent['listIds'][$ids]    = $val;
         $allContent[1]['listIds'][$ids] = $val;
      }
      foreach ($interventionContent as $iContent) {
         foreach ($iContent['listIds'] as $ids => $val) {
            $allContent['listIds'][$ids]    = $val;
            $allContent[1]['listIds'][$ids] = $val;
         }
      }


      // only entity
      $this->showJSfunction("addOnlyEntity", $entityContent['idDivAjax'], $pModel->getUrl(), $entityContent['listIds'], $entityContent['params']);


      // only contract
      $contractContent['params']['action'] = Action::ADD_ONLY_CONTRACT;
      $this->showJSfunction("addOnlyContract" . $contractContent['params']['rand'], $contractContent['idDivAjax'], $pModel->getUrl(), $contractContent['listIds'], $contractContent['params']);


      // Entity AND Contract
      $contractContent['params']['action'] = Action::ADD_ENTITY_AND_CONTRACT;
      foreach ($entityContent['listIds'] as $ids => $val) {
         $contractContent['listIds'][$ids]    = $val;
         $contractContent[1]['listIds'][$ids] = $val;
      }
      $this->showJSfunction("addEntityAndContract", $contractContent['idDivAjax'], $pModel->getUrl(), $contractContent['listIds'], $contractContent['params']);


      // Contacts
      if (is_array($contactContent)) {
         $i = 1;
         foreach ($contactContent as $content) {
            $this->showJSfunction("addOnlyContact" . $i, $content['idDivAjax'], $pModel->getUrl(), $content['listIds'], $content['params']);
            $this->showJSfunction("addAnotherContact" . $i, $content['idDivNewContact'], $pModel->getUrl(), $content['listIds'], $content['paramsAddNewContact']);
            $i++;
         }
      }


      $contactContent[1]['params']['action'] = Action::ADD_ENTITY_AND_CONTACT;
      foreach ($entityContent['listIds'] as $ids => $val) {
         $contactContent['listIds'][$ids]    = $val;
         $contactContent[1]['listIds'][$ids] = $val;
      }
      $this->showJSfunction("addEntityAndContact", $contactContent[1]['idDivAjax'], $pModel->getUrl(), $contactContent[1]['listIds'], $contactContent[1]['params']);

      // Interventions
      if (is_array($interventionContent)) {
         $i = 1;
         foreach ($interventionContent as $intervention) {
            $this->showJSfunction("addOnlyIntervention" . $i, $intervention['idDivAjax'], $pModel->getUrl(), $intervention['listIds'], $intervention['params'], $intervention['idDivStakeholdersAjax']);
            $this->showJSfunction("addAnotherIntervention" . $i, $intervention['idDivNewIntervention'], $pModel->getUrl(), $intervention['listIds'], $intervention['paramsAddNewIntervention']);
            $i++;
         }
      }

      // interventions and entity
      foreach ($entityContent['listIds'] as $ids => $val) {
         $interventionContent['listIds'][$ids]    = $val;
         $interventionContent[1]['listIds'][$ids] = $val;
      }
      $this->showJSfunction("addEntityAndContract", $contractContent['idDivAjax'], $pModel->getUrl(), $contractContent['listIds'], $contractContent['params']);


      $interventionContent[1]['params']['action'] = Action::ADD_ENTITY_AND_INTERVENTION;
      $this->showJSfunction("addEntityAndIntervention", $interventionContent[1]['idDivAjax'], $pModel->getUrl(), $interventionContent[1]['listIds'], $interventionContent[1]['params']);


      // Interventions AND contract
      $interventionContent[1]['params']['action'] = Action::ADD_INTERVENTION_AND_CONTRACT;
      foreach ($contractContent['listIds'] as $ids => $val) {
         $interventionContent['listIds'][$ids]    = $val;
         $interventionContent[1]['listIds'][$ids] = $val;
      }
      $this->showJSfunction("addInterventionAndContract", $interventionContent[1]['idDivAjax'], $pModel->getUrl(), $interventionContent[1]['listIds'], $interventionContent[1]['params']);


      // Interventions AND entity AND Contract
      $interventionContent[1]['params']['action'] = Action::ADD_ENTITY_INTERVENTION_AND_CONTRACT;
      foreach ($entityContent['listIds'] as $ids => $val) {
         $interventionContent['listIds'][$ids]    = $val;
         $interventionContent[1]['listIds'][$ids] = $val;
      }

      foreach ($contractContent['listIds'] as $ids => $val) {
         $interventionContent['listIds'][$ids]    = $val;
         $interventionContent[1]['listIds'][$ids] = $val;
      }
      $this->showJSfunction("addEntityInterventionAndContract", $interventionContent[1]['idDivAjax'], $pModel->getUrl(), $interventionContent[1]['listIds'], $interventionContent[1]['params']);

      // ADD ALL ELEMENTS -> Entity - Contacts - Contract - Periods
      /*
        $allContent[1]['params']['action'] = Action::ADD_ALL_ELEMENT;
        $this->showJSfunction("addAllElements" ,$allContent['idDivAjax'],$pModel->getUrl(),$allContent[1]['listIds'],$allContent['params']);
        $allContent[1]['params']['action'] = Action::UPDATE_ALL_ELEMENT;
        $this->showJSfunction("addUpdateElements" ,$allContent['idDivAjax'],$pModel->getUrl(),$allContent[1]['listIds'],$allContent['params']);

        $this->showJSfunction("confirmAddAllElements" ,$allContent['idDivAjax'],$pModel->getUrl(),[],array('action' => '".Action::CONFIRM_ADD_ALL_ELEMENT."','id_div_ajax' => $allContent['idDivAjax']));
        $this->showJSfunction("confirmUpdateAllElements" ,$allContent['idDivAjax'],$pModel->getUrl(),[],array('action' => Action::CONFIRM_UPDATE_ALL_ELEMENT,'id_div_ajax' => $allContent['idDivAjax']));
       */
   }

   public function showFormAddEntity() {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

      $currentEntity = $this->pModel->getEntity();

      if (!isset($currentEntity->fields['id']) || $currentEntity->fields['id'] <= 0) {
         $currentEntity->getEmpty();
      }
      $ID   = -1;
      $rand = mt_rand();


      echo "<div id='tabs-1' style='padding:0px;' onchange=\"javascript:";
      $this->updateImgTabTitle(true, "'img_" . $this->pModel->getEntity()->getType() . "1'", $this->pModel->getMessage(ElementType::ENTITY, Status::NOT_SAVED));
      echo "\" >";

      $fields = $currentEntity->getAdditionalFields();
      $field  = $fields [0];
      echo "<table class='tab_cadre_fixe' >";

      echo "<tr class='tab_bg_1'><td>" . __('Name') . $this->pModel->getMessage("mandatory_field") . "</td>";
      echo "<td>";

      // Non utilise : impossibilite de reccuperer l'ID de l'input via tml::autocompletionTextField
      //      $ret = Html::autocompletionTextField ($currentEntity, "entity_name", array("display" => true) );
      echo "<input type='text' name='entity_name' id='entity_name' value='" . $currentEntity->fields["name"] . "'/> ";
      echo "</td>";

      echo "<td>";
      echo __("Comments");
      echo "</td>";

      echo "<td>";
      echo "<textarea cols='45' rows='2' name='entity_comment' id='entity_comment' >";
      echo $currentEntity->fields["comment"];
      echo "</textarea></td></tr>\n";

      echo "</td>";

      echo "</tr>";

      echo "<tr  class='tab_bg_1'>";
      echo "<td>" . __("As child of") . "</td>";
      echo "<td colspan='3'>";

      $dbu           = new DbUtils();
      $condition     = $dbu->getEntitiesRestrictCriteria("glpi_entities", "", "", true);
      $idEntityChild = Dropdown::show($dbu->getItemTypeForTable(Entity::getTable()), [
         'value'     => $currentEntity->fields [$field ['name']],
         'name'      => $field ['name'],
         'used'      => ($ID > 0 ? $dbu->getSonsOf($currentEntity->getTable(), $ID) : []),
         'condition' => $condition
      ]);


      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><th colspan='4'>" . __('Address') . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Phone') . "</td>";
      echo "<td>";
      echo "<input type='text' name='entity_phonenumber' id='entity_phonenumber' value='" . $currentEntity->fields["name"] . "'/> ";
      echo "</td>";
      echo "<td rowspan='7'>" . __('Address') . "</td>";
      echo "<td rowspan='7'>";
      echo "<textarea cols='45' rows='8' name='entity_address' id='entity_address'>" . $currentEntity->fields["address"] . "</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Fax') . "</td>";
      echo "<td>";
      echo "<input type='text' name='entity_fax' id='entity_fax' value='" . $currentEntity->fields["fax"] . "'/> ";
      echo "</td></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Website') . "</td>";
      echo "<td>";
      echo "<input type='text' name='entity_website' id='entity_website' value='" . $currentEntity->fields["website"] . "'/> ";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . _n('Email', 'Emails', 1) . "</td>";
      echo "<td>";
      echo "<input type='text' name='entity_email' id='entity_email' value='" . $currentEntity->fields["email"] . "'/> ";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Postal code') . "</td>";
      echo "<td>";
      echo "<input type='text' name='entity_postcode' id='entity_postcode' size='7' value='" . $currentEntity->fields["postcode"] . "'/> ";
      echo "&nbsp;&nbsp;" . __('City') . "&nbsp;";
      echo "<input type='text' name='entity_city' id='entity_city' size='27' value='" . $currentEntity->fields["town"] . "'/> ";

      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . _x('location', 'State') . "</td>";
      echo "<td>";
      echo "<input type='text' name='entity_state' id='entity_state' value='" . $currentEntity->fields["state"] . "'/> ";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Country') . "</td>";
      echo "<td>";
      echo "<input type='text' name='entity_country' id='entity_country' value='" . $currentEntity->fields["country"] . "'/> ";
      echo "</td></tr>";


      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='right'>";
      echo "<input type='submit' class='submit' name='btnAddEntity' id='btnAddEntity' ";

      if (isset($this->pModel->getEntity()->fields["id"]) && $currentEntity->fields["id"] > 0) {
         echo "value='" . __("Update this entity", "manageentities") . "'";
      } else {
         echo "value='" . _sx('button', "Add only the entity", "manageentities") . "'";
      }


      echo " onclick='javascript:addOnlyEntity();'";
      echo "/>";
      echo "</tr>";

      echo "</table>";


      // Variables to ajax add entity

      $idDivAjax = "tabentityajax";

      echo "</div>";

      echo "<div id='" . $idDivAjax . "' style='text-align:center;'></div>";

      // Entity
      $listId = [
         "entity_name"                           => ["text", "new_entity_name"],
         "entity_phonenumber"                    => ["text", "new_entity_phone"],
         "entity_fax"                            => ["text", "new_entity_fax"],
         "entity_website"                        => ["text", "new_entity_website"],
         "entity_email"                          => ["text", "new_entity_email"],
         "entity_postcode"                       => ["text", "new_entity_postcode"],
         "entity_state"                          => ["text", "new_entity_state"],
         "entity_country"                        => ["text", "new_entity_country"],
         "entity_city"                           => ["text", "new_entity_city"],
         "entity_address"                        => ["text", "new_entity_address"],
         "entity_comment"                        => ["text", "new_entity_comment"],
         "dropdown_entities_id" . $idEntityChild => ["dropdown", "new_entity_entities_id"],
      ];

      $params = [
         'action'      => Action::ADD_ONLY_ENTITY,
         'id_div_ajax' => $idDivAjax
      ];

      $entityContent = [
         'idDivAjax' => $idDivAjax,
         'listIds'   => $listId,
         'params'    => $params
      ];


      return $entityContent;
   }

   public function showFormAddContact($idContact = 0) {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();
      $listIdsRet   = [];
      $ID           = -1;
      $rand         = $idContact;

      $contacts       = $this->pModel->getContacts();
      $currentContact = $contacts[$idContact];
      $dbu            = new DbUtils();

      // Onchange for img purpose
      echo "   <div id='tabs-" . ($idContact) . "' style='padding:0px;' onchange=\"javascript:";
      $this->updateImgTabTitle(true, "'img_" . $currentContact->getType() . ($idContact) . "'", $this->pModel->getMessage(ElementType::CONTACT, Status::NOT_SAVED));
      echo "\" >";

      echo "<table class='tab_cadre_fixe'>";

      echo "<input type='hidden' name='fakeid_new_contact" . $rand . "' id='fakeid_new_contact" . $rand . "' value='" . $idContact . "' />";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Entity") . $this->pModel->getMessage("mandatory_field") . "</td>";
      echo "<td>";

      echo "<div id='div_select_entity_for_contact" . $rand . "' ";
      if (isset($currentContact->fields['entities_id']) && isset($this->pModel->getEntity()->fields['id']) && $this->pModel->getEntity()->fields['id'] > 0 && $currentContact->fields['entities_id'] == $this->pModel->getEntity()->fields['id'] || ((!isset($currentContact->fields['entities_id']) || $currentContact->fields['entities_id'] == "") && isset($this->pModel->getEntity()->fields['id']) && $this->pModel->getEntity()->fields['id'] > 0)) {
         echo " style='visibility:hidden;' ";
      }
      echo ">";

      $condition = $dbu->getEntitiesRestrictCriteria("glpi_entities");

      $idDpEntity = Dropdown::show($dbu->getItemTypeForTable(Entity::getTable()), [
         'name'       => 'contact_entities_id',
         'value'      => isset($currentContact->fields['entities_id']) ? $currentContact->fields ['entities_id'] : 0,
         'emptylabel' => __("New entity", "manageentities"),
         'disabled'   => 'disabled',
         'condition'  => $condition
      ]);
      echo "</div>";
      $listIdsRet[] = $idDpEntity;


      echo "<label for='previous_entity_for_contact" . $rand . "'> <input type='checkbox'
         name='previous_entity_for_contact" . $rand . "' id='previous_entity_for_contact" . $rand . "' 
         title='" . __("New entity created previously", "manageentities") . "' ";

      if (isset($currentContact->fields['entities_id']) && isset($this->pModel->getEntity()->fields['id']) && $this->pModel->getEntity()->fields['id'] > 0 && $currentContact->fields['entities_id'] == $this->pModel->getEntity()->fields['id'] || ((!isset($currentContact->fields['entities_id']) || $currentContact->fields['entities_id'] == "") && isset($this->pModel->getEntity()->fields['id']) && $this->pModel->getEntity()->fields['id'] > 0)) {
         echo " checked='checked' ";
      }

      echo "onclick=\"switchElementsEnableFromCb(this,'div_select_entity_for_contact" . $rand . "');\" /> " . __("New entity created previously", "manageentities") . "</label>";


      echo "</td>";
      echo "<td>" . __("Child entities") . "</td>";
      echo "<td>";
      if (isset($currentContact->fields['is_recursive']) && $currentContact->fields['is_recursive'] == 1) {
         $value = 1;
      } else {
         $value = 0;
      }
      $idDpYNsubEntity = Dropdown::showYesNo("new_contact_subentity_yn", $value);

      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Surname') . $this->pModel->getMessage("mandatory_field") . "</td>";
      echo "<td>";
      echo "<input type='text' name='contact_name" . $rand . "' id='contact_name" . $rand . "' value='" . (isset($currentContact->fields['name']) ? $currentContact->fields['name'] : '') . "' /> ";
      echo "</td>";

      echo "<td rowspan='4' class='left'>" . __('Comments') . "</td>";
      echo "<td class='left' rowspan='4'>";
      echo "<textarea cols='45' rows='7' name='contact_comment" . $rand . "' id='contact_comment" . $rand . "' >" . (isset($currentContact->fields['comment']) ? $currentContact->fields['comment'] : '') . "</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('First name') . "</td>";
      echo "<td>";
      echo "<input type='text' name='contact_firstname" . $rand . "' id='contact_firstname" . $rand . "' value='" . (isset($currentContact->fields['firstname']) ? $currentContact->fields['firstname'] : '') . "'/> ";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Phone') . "</td>";
      echo "<td>";
      echo "<input type='text' name='contact_phone" . $rand . "' id='contact_phone" . $rand . "' value='" . (isset($currentContact->fields['phone']) ? $currentContact->fields['phone'] : '') . "'/> ";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Phone 2') . "</td>";
      echo "<td>";
      echo "<input type='text' name='contact_phone2" . $rand . "' id='contact_phone2" . $rand . "' value='" . (isset($currentContact->fields['phone2']) ? $currentContact->fields['phone2'] : '') . "'/> ";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Mobile phone') . "</td>";
      echo "<td>";
      echo "<input type='text' name='contact_mobile" . $rand . "' id='contact_mobile" . $rand . "' value='" . (isset($currentContact->fields['mobile']) ? $currentContact->fields['mobile'] : '') . "'/> ";
      echo "</td>";
      echo "<td class='middle'>" . __('Address') . "</td>";
      echo "<td class='middle'>";
      echo "<textarea cols='37' rows='3' name='contact_address" . $rand . "' id='contact_address" . $rand . "'>" . (isset($currentContact->fields['address']) ? $currentContact->fields['address'] : '') . "</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Fax') . "</td>";
      echo "<td>";
      echo "<input type='text' name='contact_fax" . $rand . "' id='contact_fax" . $rand . "' value='" . (isset($currentContact->fields['fax']) ? $currentContact->fields['fax'] : '') . "'/> ";
      echo "</td>";
      echo "<td>" . __('Postal code') . "</td>";
      echo "<td>";
      echo "<input type='text' name='contact_postcode" . $rand . "' id='contact_postcode" . $rand . "' size='10' value='" . (isset($currentContact->fields['postcode']) ? $currentContact->fields['postcode'] : '') . "'/> ";

      echo "&nbsp;&nbsp;" . __('City') . "&nbsp;";

      echo "<input type='text' name='contact_town" . $rand . "' id='contact_town" . $rand . "' size='23' value='" . (isset($currentContact->fields['town']) ? $currentContact->fields['town'] : '') . "'/> ";

      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . _n('Email', 'Emails', 1) . "</td>";
      echo "<td>";
      echo "<input type='text' name='contact_email" . $rand . "' id='contact_email" . $rand . "' value='" . (isset($currentContact->fields['email']) ? $currentContact->fields['email'] : '') . "'/> ";
      echo "</td>";

      echo "<td>" . _x('location', 'State') . "</td>";
      echo "<td>";
      echo "<input type='text' name='contact_state" . $rand . "' id='contact_state" . $rand . "' value='" . (isset($currentContact->fields['state']) ? $currentContact->fields['state'] : '') . "'/> ";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Type') . "</td>";
      echo "<td>";
      $idTypeDropdown = ContactType::dropdown([
                                                 'name'  => 'contact_contacttypes_id',
                                                 'value' => (isset($currentContact->fields['contacttypes_id']) ? $currentContact->fields['contacttypes_id'] : '')
                                              ]);


      echo "</td>";
      echo "<td>" . __('Country') . "</td>";
      echo "<td>";

      echo "<input type='text' name='contact_country" . $rand . "' id='contact_country" . $rand . "' value='" . (isset($currentContact->fields['country']) ? $currentContact->fields['country'] : '') . "'/> ";
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'><td>" . _x('person', 'Title') . "</td><td>";
      $idUserTitleDp = UserTitle::dropdown([
                                              'value' => (isset($currentContact->fields['usertitles_id']) ? $currentContact->fields['usertitles_id'] : '')
                                           ]);


      echo "</td>";
      echo "<td>" . __('Manager') . "</td>";
      echo "<td>";

      if ($this->pModel->getContactManager() == $idContact) {
         $value = 1;
      } else {
         $value = 0;
      }

      $idDpYmanager = Dropdown::showYesNo("is_manager_yn", $value, -1, ['rand' => $rand]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='right'>";
      echo "<input type='submit' class='submit' name='btnAddContact" . $idContact . "' id='btnAddContact" . $idContact . "' ";


      if (isset($currentContact->fields["id"]) && $currentContact->fields["id"] > 0) {
         echo "value='" . __("Update this contact", "manageentities") . "'";
      } else {
         echo "value='" . __("Add only the contact", "manageentities") . "'";
      }

      echo " onclick='javascript:addOnlyContact" . $idContact . "();'";
      echo "/>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='right'>";
      //      echo "<input type='submit' class='submit' name='btnAddnewFormContact'  id='btnAddnewFormContact'  value='" . __ ( "Add another contact", "manageentities" ) . "' onclick=\"javascript:addAnotherContact();document.getElementById(this.id).style.visibility='hidden';\"/>";
      echo "<input type='submit' class='submit' name='btnAddnewFormContact" . $rand . "'  id='btnAddnewFormContact" . $rand . "'  value='" . __("Add another contact", "manageentities") . "' onclick=\"javascript:addAnotherContact" . $idContact . "();\"/>";
      echo "</td>";
      echo "</tr>";


      echo "</table>";

      $idDivAjax       = "tabcontactajax" . $rand;
      $idDivNewContact = "divaddnewcontact" . $rand;

      // Variables to ajax add entity
      $listId = [
         "fakeid_new_contact" . $rand                           => ["hidden", "fakeid_new_contact"],
         "contact_name" . $rand                                 => ["text", "new_contact_name"],
         "contact_firstname" . $rand                            => ["text", "new_contact_firstname"],
         "contact_phone" . $rand                                => ["text", "new_contact_phone"],
         "contact_phone2" . $rand                               => ["text", "new_contact_phone2"],
         "contact_comment" . $rand                              => ["text", "new_contact_comment"],
         "contact_mobile" . $rand                               => ["text", "new_contact_mobile"],
         "contact_address" . $rand                              => ["text", "new_contact_address"],
         "contact_fax" . $rand                                  => ["text", "new_contact_fax"],
         "contact_postcode" . $rand                             => ["text", "new_contact_postcode"],
         "contact_town" . $rand                                 => ["text", "new_contact_town"],
         "contact_email" . $rand                                => ["text", "new_contact_email"],
         "contact_state" . $rand                                => ["text", "new_contact_state"],
         "contact_country" . $rand                              => ["text", "new_contact_country"],
         "btnAddnewFormContact" . $rand                         => ["button", "id_btn_add_contact"],
         "dropdown_contact_contacttypes_id" . $idTypeDropdown   => ["dropdown", "new_contact_contact_type"],
         "dropdown_usertitles_id" . $idUserTitleDp              => ["dropdown", "new_contact_user_title"],
         "dropdown_contact_entities_id" . $idDpEntity           => ["dropdown", "new_contact_entity_id"],
         "previous_entity_for_contact" . $rand                  => ["checkbox", "previous_entity_for_contact"],
         "dropdown_is_manager_yn" . $idDpYmanager               => ["dropdown", "new_contact_is_manager"],
         "dropdown_new_contact_subentity_yn" . $idDpYNsubEntity => ["dropdown", "new_contact_subentity_yn"],
      ];

      $paramsAddNewContact = [
         "action"             => Action::ADD_NEW_CONTACT,
         "id_div_new_contact" => $idDivNewContact,
         "id_div_ajax"        => $idDivAjax
      ];

      $params = [
         "action"             => Action::ADD_ONLY_CONTACT,
         "id_div_ajax"        => $idDivAjax,
         "fakeid_new_contact" => $idContact
      ];

      echo "</div>";

      echo "<div id='" . $idDivAjax . "' style='text-align:center;'></div>";
      echo "<div id='" . $idDivNewContact . "'></div>";


      $contactContent = [
         'idDivAjax'           => $idDivAjax,
         'listIds'             => $listId,
         'params'              => $params,
         'paramsAddNewContact' => $paramsAddNewContact,
         'idDivNewContact'     => $idDivNewContact,
      ];

      return $contactContent;
   }

   public function showFormAddContract($params = []) {
      $dbu          = new DbUtils();
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

      echo "   <div id='tabs-1' style='padding:0px;' onchange=\"javascript:";
      $this->updateImgTabTitle(true, "'img_" . $this->pModel->getContract()->getType() . "1'", $this->pModel->getMessage(ElementType::CONTRACT, Status::NOT_SAVED));
      echo "\" >";


      $idTemplate = $this->pModel->getIdContractTemplate();
      $ID         = -1;
      $rand       = mt_rand();

      $currentContract = $this->pModel->getContract();

      echo "<table class='tab_cadre_fixe'>";

      // Template

      if (!isset($currentContract->fields['id']) || $currentContract->fields['id'] <= 0) {
         echo "<tr class='tab_bg_1' id='row_contract_template'>";
         echo "<td>" . __("Templates") . "</td>";
         echo "<td colspan='3'>";

         // Liste des templates
         $cond = ["`is_template`" => 1,
                   'ORDER' => 'name ASC'];

         $listTemplate = $dbu->getAllDataFromTable($currentContract->getTable(), $cond);
         $listOptions  = ["-1" => __("Blank Template")];
         if (sizeof($listTemplate) > 0) {
            foreach ($listTemplate as $tpl) {
               $listOptions[$tpl['id']] = $tpl['template_name'];
            }
         }

         $idDpTemplates = Dropdown::showFromArray("select_contract_template", $listOptions, [
            'rand'      => $rand,
            'name'      => 'contract_template',
            'value'     => $idTemplate,
            'on_change' => 'javascript:loadContractTemplate' . $rand . '()'
         ]);
         //         INFOTEL : MODIFICATION PRESALES
         $this->showJSfunction(
            "loadContractTemplate" . $rand, "divContract", $this->pModel->getUrl(), ["dropdown_select_contract_template" . $idDpTemplates => ["dropdown", "selected_template"],
                                                                                     "paramshide"                                         => ["dropdown", "paramshide"]], ['action' => Action::LOAD_CONTRACT_TEMPLATE]
         );
         //        INFOTEL

         echo "</td>";
         echo "</tr>";
      }

      // Entite
      echo "<tr  class='tab_bg_1'>";
      //         INFOTEL : MODIFICATION PRESALES
      if (!isset($params["presales"]) || (isset($params["presales"]) && $params["presales"] == false ) ) {


         echo "<td>" . __("Entity") . $this->pModel->getMessage("mandatory_field") . "</td>";
         echo "<td>";
         echo "<div id='div_select_entity_for_contract' ";

         $condition = $dbu->getEntitiesRestrictCriteria("glpi_entities");

         if (isset($currentContract->fields['entities_id']) && isset($this->pModel->getEntity()->fields['id']) && $this->pModel->getEntity()->fields['id'] > 0 && $currentContract->fields['entities_id'] == $this->pModel->getEntity()->fields['id'] || ((!isset($currentContract->fields['entities_id']) || $currentContract->fields['entities_id'] == "") && isset($this->pModel->getEntity()->fields['id']) && $this->pModel->getEntity()->fields['id'] > 0)) {
            echo " style='visibility:hidden;' ";
         }
         echo " >";

         $idDpEntity = Dropdown::show($dbu->getItemTypeForTable($this->pModel->getEntity()->getTable()), [
            'name'       => 'contract_entities_id',
            'value'      => isset($currentContract->fields['entities_id']) ? $currentContract->fields ['entities_id'] : 0,
            'emptylabel' => __("New entity", "manageentities"),
            'condition'  => $condition,
            'rand'       => $rand
         ]);

         echo "</div>";
         echo "<label for='previous_entity_for_contract'> <input type='checkbox' name='previous_entity_for_contract' id='previous_entity_for_contract' ";
         if (isset($currentContract->fields['entities_id'])
             && isset($this->pModel->getEntity()->fields['id'])
             && $this->pModel->getEntity()->fields['id'] > 0
             && $currentContract->fields['entities_id'] == $this->pModel->getEntity()->fields['id']
             || ((!isset($currentContract->fields['entities_id']) || $currentContract->fields['entities_id'] == "")
                 && isset($this->pModel->getEntity()->fields['id'])
                 && $this->pModel->getEntity()->fields['id'] > 0)) {
            echo " checked='checked' ";
         }
         echo " title='" . __("New entity created previously", "manageentities") . "'";
         echo "onclick=\"switchElementsEnableFromCb(this,'div_select_entity_for_contract');\" /> ";
         echo __("New entity created previously", "manageentities") . "</label>";
         echo "</td>";
         echo "</td>";
      } else {

         echo "<td hidden>";
         $condition                              = $dbu->getEntitiesRestrictCriteria("glpi_entities");
         $currentContract->fields['entities_id'] = $params["presales"];
         $idDpEntity                             = Dropdown::show($dbu->getItemTypeForTable($this->pModel->getEntity()->getTable()), [
            'name'       => 'contract_entities_id',
            'value'      => $params["presales"],
            'emptylabel' => __("New entity", "manageentities"),
            'condition'  => $condition,
            'rand'       => $rand
         ]);
         //         echo Html::hidden("contract_entities_id", ["value" => $params["presales"], "id" => "paramshide"]);


         echo "</td>";
         echo "<td></td>";
         echo "<td></td>";
      }
      //        INFOTEL
      echo "<td>" . __("Child entities") . "</td>";
      echo "<td>";
      $idDpYNsubEntity = Dropdown::showYesNo("new_contract_subentity_yn", 0, -1, ['rand' => $rand]);

      echo "</td>";
      echo "</tr>";


      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Name') . $this->pModel->getMessage("mandatory_field") . "</td><td>";
      echo "<input type='text' name='contract_name' id='contract_name' value='" . (isset($currentContract->fields['name']) ? $currentContract->fields['name'] : '') . "' /> ";
      echo "</td>";
      echo "<td>" . __('Contract type') . "</td><td >";
      $idDpContractType = ContractType::dropdown([
                                                    'rand'  => mt_rand(),
                                                    'name'  => 'contract_contracttypes_id',
                                                    'value' => $currentContract->fields["contracttypes_id"]]
      );

      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . _x('phone', 'Number') . "</td>";
      echo "<td>";
      echo "<input type='text' name='contract_num' id='contract_num' value='" . (isset($currentContract->fields['num']) ? $currentContract->fields['num'] : '') . "' /> ";
      echo "</td>";
      $randDropdown = mt_rand();
      echo "<td><label for='dropdown_states_id$randDropdown'>".__('Status')."</label></td>";
      echo "<td>";
      State::dropdown([
                         'value'     => $currentContract->fields["states_id"],
                         'entity'    => $currentContract->fields["entities_id"],
                         'condition' => ['is_visible_contract' => 1],
                         'rand'      => $randDropdown
                      ]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Start date') . "</td>";
      echo "<td>";


      $idDateBegin = "contract_begin_date" . $rand;

      echo "<input id='" . $idDateBegin . "' type='text' size='10' name='" . $idDateBegin . "' value='";
      echo(
      isset($currentContract->fields['begin_date']) && $currentContract->fields['begin_date'] != "NULL" && $currentContract->fields['begin_date'] ?
         date('d-m-Y', strtotime($currentContract->fields['begin_date'])) :
         ''
      );
      echo "' >";
      $this->initDate($idDateBegin);

      echo "</td>";
      echo "<td>" . __('Initial contract period') . "</td><td>";
      $idDpContractDuration = Dropdown::showNumber("contract_duration", ['value' => $currentContract->fields["duration"],
                                                                         'min'   => 1,
                                                                         'max'   => 120,
                                                                         'toadd' => [0 => Dropdown::EMPTY_VALUE],
                                                                         ['unit' => 'month', 'rand' => $rand]]);
      if (!empty($currentContract->fields["begin_date"])) {
         echo " -> " . Infocom::getWarrantyExpir($currentContract->fields["begin_date"], $currentContract->fields["duration"], 0, true);
      }
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Notice') . "</td><td>";
      $idDpNotice = Dropdown::showNumber("contract_notice", ['value' => $currentContract->fields["notice"],
                                                             'max'   => 120,
                                                             ['unit' => 'month', 'rand' => $rand]]);
      if (!empty($currentContract->fields["begin_date"]) && ($currentContract->fields["notice"] > 0)) {
         echo " -> " . Infocom::getWarrantyExpir($currentContract->fields["begin_date"], $currentContract->fields["duration"], $currentContract->fields["notice"], true);
      }

      echo "</td>";
      echo "<td>" . __('Account number') . "</td><td>";
      echo "<input type='text' name='contract_accounting_number' id='contract_accounting_number' value='" . (isset($currentContract->fields['accounting_number']) ? $currentContract->fields['accounting_number'] : '') . "' />";
      echo "</td></tr>";
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Contract renewal period') . "</td><td>";
      $idDpInterventionicity = Dropdown::showNumber("contract_periodicity", ['value' => $currentContract->fields["periodicity"],
                                                                             'min'   => 2,
                                                                             'max'   => 60,
                                                                             'step'  => 12,
                                                                             'unit'  => 'month',
                                                                             'rand'  => $rand,
                                                                             'toadd' => [0 => Dropdown::EMPTY_VALUE,
                                                                                         1 => sprintf(_n('%d month', '%d months', 1), 1),
                                                                                         2 => sprintf(_n('%d month', '%d months', 2), 2),
                                                                                         3 => sprintf(_n('%d month', '%d months', 3), 3),
                                                                                         6 => sprintf(_n('%d month', '%d months', 6), 6)]]);

      echo "<td>" . __('Invoice period') . "</td>";
      echo "<td>";
      $idDpBilling = Dropdown::showNumber("contract_billing", ['value' => $currentContract->fields["billing"],
                                                               'min'   => 12,
                                                               'max'   => 60,
                                                               'step'  => 12,
                                                               'unit'  => 'month',
                                                               'rand'  => $rand,
                                                               'toadd' => [0 => Dropdown::EMPTY_VALUE,
                                                                           1 => sprintf(_n('%d month', '%d months', 1), 1),
                                                                           2 => sprintf(_n('%d month', '%d months', 2), 2),
                                                                           3 => sprintf(_n('%d month', '%d months', 3), 3),
                                                                           6 => sprintf(_n('%d month', '%d months', 6), 6)]]);
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'><td>" . __('Renewal') . "</td><td>";
      //      $idDpRenewal = Contract::dropdownContractRenewal("contract_renewal", $currentContract->fields["renewal"]);

      $tmp = [
         0 => __('Never'),
         1 => __('Tacit'),
         2 => __('Express')
      ];

      $idDpRenewal = Dropdown::showFromArray("contract_renewal", $tmp, ['value'   => $currentContract->fields["renewal"],
                                                                        'display' => true,
                                                                        'rand'    => $rand]);


      echo "</td>";
      echo "<td>" . __('Max number of items') . "</td><td>";
      $idMaxItems = Dropdown::showNumber("contract_max_links_allowed", ['value' => $currentContract->fields["max_links_allowed"],
                                                                        'min'   => 1,
                                                                        'max'   => 200,
                                                                        "rand"  => $rand,
                                                                        'toadd' => [0 => __('Unlimited')]]);
      echo "</td>";
      echo "</tr>";


      if (Entity::getUsedConfig("use_contracts_alert", $currentContract->fields["entities_id"])) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __('Email alarms') . "</td>";
         echo "<td>";

         Contract::dropdownAlert(['name'  => "contract_alert",
                                  'value' => $currentContract->fields["alert"]]);
         Alert::displayLastAlert(__CLASS__, $ID);
         echo "</td>";
         echo "<td colspan='2'>&nbsp;</td>";
         echo "</tr>";
      }
      echo "<tr class='tab_bg_1'><td class='top'>" . __('Comments') . "</td>";
      echo "<td class='center' colspan='3'>";
      echo "<textarea cols='50' rows='4' name='contract_comment' id='contract_comment'>" . $currentContract->fields["comment"] . "</textarea>";
      echo "</td></tr>";

      echo "<tr class='tab_bg_2'><td>" . __('Support hours') . "</td>";
      echo "<td colspan='3'>&nbsp;</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('on week') . "</td>";
      echo "<td colspan='3'>" . __('Start') . "&nbsp;";
      $idDpContractWkBeginHour = $this->showHours("contract_week_begin_hour", $currentContract->fields["week_begin_hour"], 0, $rand);

      echo "<span class='small_space'>" . __('End') . "</span>&nbsp;";
      $idDpContractWkEndHour = $this->showHours("contract_week_end_hour", $currentContract->fields["week_end_hour"], 0, $rand);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('on Saturday') . "</td>";
      echo "<td colspan='3'>";
      $idDpYNsat = Dropdown::showYesNo("contract_use_saturday", $currentContract->fields["use_saturday"], -1, ["rand" => $rand]);
      echo "<span class='small_space'>" . __('Start') . "</span>&nbsp;";
      $idDpContractSatBeginHour = $this->showHours("contract_saturday_begin_hour", $currentContract->fields["saturday_begin_hour"], 0, $rand);
      echo "<span class='small_space'>" . __('End') . "</span>&nbsp;";
      $idDpContractSatEndHour = $this->showHours("contract_saturday_end_hour", $currentContract->fields["saturday_end_hour"], 0, $rand);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Sundays and holidays') . "</td>";
      echo "<td colspan='3'>";
      $idDpYNmon = Dropdown::showYesNo("contract_use_monday",
                                       $currentContract->fields["use_monday"],
                                       -1,
                                       ["rand" => $rand]);
      echo "<span class='small_space'>" . __('Start') . "</span>&nbsp;";
      $idDpContractMonBeginHour = $this->showHours("contract_monday_begin_hour", $currentContract->fields["monday_begin_hour"], 0, $rand);
      echo "<span class='small_space'>" . __('End') . "</span>&nbsp;";
      $idDpContractMonEndHour = $this->showHours("contract_monday_end_hour", $currentContract->fields["monday_end_hour"], 0, $rand);
      echo "</td></tr>";


      if (isset($currentContract->fields['id']) && $currentContract->fields['id'] > 0) {
         $display = "style='display:table-row;'";
      } else {
         $display = "style='display:none;'";
      }

      echo "<tr  class='tab_bg_2'>";
      echo "<td colspan='4' class='right'>";
      echo "<input type='submit' class='submit' name='btnAddContract' id='btnAddContract' value='";
      if (isset($currentContract->fields['id']) && $currentContract->fields['id'] > 0) {
         echo "" . __("Update this contract only", "manageentities") . "' ";
      } else {
         echo "" . __("Add only the contract", "manageentities") . "' ";
      }
      echo " onclick='javascript:addOnlyContract" . $rand . "();'";
      echo "/>";
      echo "</tr>";

      echo "</table>";

      echo "<table  class='tab_cadre_fixe' id='tbl_list_pdf_contract'>";
      echo "<tr class='tab_bg_1'  id='tr_add_contract' " . $display . ">";
      echo "<td>";
      echo __("Add a document");
      echo "</td>";
      echo "<td colspan='5'>";
      echo "<a onclick=\"showFormAddPDFContract('" . __("Add a document") . "','" . _sx('button', 'Add') . "','" . _sx('button', 'Cancel') . "');\" class='pointer'>";
      echo "<i class=\"fas fa-3x fa-plus-square\"></i></a>";
      echo "</td>";
      echo "</tr>";
      $this->showListPDFcontract();
      echo "</table>";

      $listId = [
         "contract_name"                                            => ["text", "new_contract_name"],
         "contract_num"                                             => ["text", "new_contract_num"],
         $idDateBegin                                               => ["text", "new_contract_date_begin"],
         "contract_accounting_number"                               => ["text", "new_contract_accounting_number"],
         "contract_comment"                                         => ["text", "new_contract_comment"],
         "dropdown_contract_use_saturday" . $idDpYNsat              => ["dropdown", "new_contract_use_saturday"],
         "dropdown_contract_use_monday" . $idDpYNmon                => ["dropdown", "new_contract_use_monday"],
         "contract_week_begin_hour" . $idDpContractWkBeginHour      => ["dropdown", "new_contract_week_begin_hour"],
         "contract_week_end_hour" . $idDpContractWkEndHour          => ["dropdown", "new_contract_week_end_hour"],
         "contract_saturday_begin_hour" . $idDpContractSatBeginHour => ["dropdown", "new_contract_sat_degin_hour"],
         "contract_saturday_end_hour" . $idDpContractSatEndHour     => ["dropdown", "new_contract_sat_end_hour"],
         "contract_monday_begin_hour" . $idDpContractMonBeginHour   => ["dropdown", "new_contract_mon_begin_hour"],
         "contract_monday_end_hour" . $idDpContractMonEndHour       => ["dropdown", "new_contract_mon_end_hour"],
         "dropdown_contract_max_links_allowed" . $idMaxItems        => ["dropdown", "new_contract_max_links_allowed"],
         "dropdown_contract_renewal" . $idDpRenewal                 => ["dropdown", "new_contract_renewal"],
         "dropdown_contract_billing" . $idDpBilling                 => ["dropdown", "new_contract_billing"],
         "dropdown_contract_periodicity" . $idDpInterventionicity   => ["dropdown", "new_contract_periodicity"],
         "dropdown_contract_duration" . $idDpContractDuration       => ["dropdown", "new_contract_duration"],
         "dropdown_contract_notice" . $idDpNotice                   => ["dropdown", "new_contract_notice"],
         "dropdown_contract_entities_id" . $rand                    => ["dropdown", "new_contract_entity_id"],
         "dropdown_contract_contracttypes_id" . $idDpContractType   => ["dropdown", "new_contract_contracttype_id"],
         "previous_entity_for_contract"                             => ["checkbox", "previous_entity_for_contract"],
         "dropdown_new_contract_subentity_yn" . $idDpYNsubEntity    => ["dropdown", "new_contract_subentity_yn"]
      ];

      $idDivAjax = "tabcontractajax";

      $params = [
         'action'      => Action::ADD_ONLY_CONTRACT,
         "id_div_ajax" => $idDivAjax,
         "rand"        => $rand
      ];

      echo "<div id='" . $idDivAjax . "' style='text-align:center;'>";
      if (isset($currentContract->fields['id']) && $currentContract->fields['id'] > 0) {
         $this->showFormAddContractManagementType($currentContract);
      }
      echo "</div>";
      echo "</div>";

      $contractContent = [
         'idDivAjax' => $idDivAjax,
         'listIds'   => $listId,
         'params'    => $params
      ];

      return $contractContent;
   }

   function showListPDFcontract($display = true) {
      global $DB;
      $ret = "";
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();
      $item         = $this->pModel->getContract();

      $arr       = $this->pModel->getQueryForDFContract($item);
      $query     = isset($arr['query']) ? $arr['query'] : null;
      $linkparam = isset($arr['linkparam']) ? $arr['linkparam'] : '';


      if ($query != null) {
         $result = $DB->query($query);
         $number = $DB->numrows($result);
         $i      = 0;

         $documents = [];
         $used      = [];
         if ($numrows = $DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               $documents[$data['assocID']] = $data;
               $used[$data['id']]           = $data['id'];
            }
         }
      } else {
         $number = false;
      }


      if ($number) {

         $ret.= "<tr class='tab_bg_1'>";
         $ret.= "<th colspan='6' class='center'>";
         $ret.= __("Document list", "manageentities");
         $ret.= "</th>";
         $ret.= "</tr>";


         $ret.= "<tr class='tab_bg_1'>";

         $columns = ['name'      => __('Name'),
                     'entity'    => __('Entity'),
                     'filename'  => __('File'),
                     'headings'  => __('Heading'),
                     'mime'      => __('MIME type'),
                     'assocdate' => __('Date')];

         foreach ($columns as $key => $val) {
            $ret.= "<th>" . $val . "</th>";
         }
         $ret.= "</tr>";
         $used = [];

         // Don't use this for document associated to document
         // To not loose navigation list for current document
         if ($item->getType() != 'Document') {
            Session::initNavigateListItems('Document',
               //TRANS : %1$s is the itemtype name,
               //        %2$s is the name of the item (used for headings of a list)
                                           sprintf(__('%1$s = %2$s'), $item->getTypeName(1), $item->getName()));
         }

         $document = new Document();
         foreach ($documents as $data) {
            $docID        = $data["id"];
            $link         = NOT_AVAILABLE;
            $downloadlink = NOT_AVAILABLE;

            if ($document->getFromDB($docID)) {
               $link         = $document->getLink();
               $downloadlink = $document->getDownloadLink($linkparam);
            }

            if ($item->getType() != 'Document') {
               Session::addToNavigateListItems('Document', $docID);
            }
            $used[$docID] = $docID;

            $ret.= "<tr class='tab_bg_1" . ($data["is_deleted"] ? "_2" : "") . "'>";
            $ret.= "<td class='center'>$link</td>";
            $ret.= "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entityID']);
            $ret.= "</td>";
            $ret.= "<td class='left'>$downloadlink</td>";
            $ret.= "<td class='center'>" . Dropdown::getDropdownName("glpi_documentcategories", $data["documentcategories_id"]);
            $ret.= "</td>";
            $ret.= "<td class='center'>" . $data["mime"] . "</td>";
            $ret.= "<td class='center'>" . Html::convDateTime($data["assocdate"]) . "</td>";
            $ret.= "</tr>";
            $i++;
         }
      }
      if($display){
         echo $ret;
      }else{
         return $ret;
      }
   }

   public function showFormAddPDFcontract() {
      $this->showHeaderJS();
      echo "$('#tr_add_contract').css('display','block')";
      $this->closeFormJS();
   }

      private function initFormAddPDFContract() {
         $dbu          = new DbUtils();
         $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

         echo "<div id='form-add-contract' style='display: none;'>";
         echo "<form id='add-form-contract' method='POST' enctype='multipart/form-data' action='" . $this->pModel->getUrl() . "'>";
         echo "<input type='hidden' name='action' id='action' value='" . Action::ADD_NEW_CONTRACT_PDF . "' />";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __("Heading") . "</td>";
         echo "<td>";
         if (isset($this->pModel->getContract()->fields['entities_id']) && $this->pModel->getContract()->fields['entities_id'] > 0) {
            DocumentCategory::dropdown(['entity' => $dbu->getSonsOf(Entity::getTable(),
                                                                    $this->pModel->getContract()->fields['entities_id'])]);
         } else {
            DocumentCategory::dropdown();
         }
         echo "</td>";
         echo "<td colspan='3' style='padding-top:16px;'>";
         echo Html::file();
         echo "</td><td class='left'>";
         echo "(" . Document::getMaxUploadSize() . ")&nbsp;";
         echo "</td>";
         echo "</tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

   /**
    * @param $oldCMTid
    */
   public function reinitContractManagementType($oldCMTid) {
      $pModel = PluginManageentitiesAddElementsModel::getInstance();
      $this->showHeaderJS();
      echo " if ($('#cmanagetype_date_signature" . $oldCMTid . "')){
                  $('#cmanagetype_date_signature" . $oldCMTid . "').val('');
                }";
      echo " if ($('#cmanagetype_date_renewal" . $oldCMTid . "')){
                  $('#cmanagetype_date_renewal" . $oldCMTid . "').val('');
                }";
      echo " if ($('#contract_added')){
                  $('#contract_added').attr('checked', false);
                }";
      echo " if ($('#dropdown_contract_type" . $oldCMTid . "')){
                  $('#dropdown_contract_type" . $oldCMTid . "').val(0);
                }";

      echo " if ($('#dropdown_show_on_global_gantt" . $oldCMTid . "')){
                  $('#dropdown_show_on_global_gantt" . $oldCMTid . "').val(0);
                }";
      echo " if ($('#refacturable_costs')){
                  $('#refacturable_costs').attr('checked', false);
                }";
      echo " if ($('#dropdown_moving_management" . $oldCMTid . "')){
                  $('#dropdown_moving_management" . $oldCMTid . "').val(0);
                }";
      echo " if ($('#dropdown_duration_moving" . $oldCMTid . "')){
                  $('#dropdown_duration_moving" . $oldCMTid . "').val(0);
                }";

      $this->closeFormJS();


      $this->changeBtnName("btnAddContractManagementType", _sx('button', 'Add'));
      $this->changeElementVisibility("btnDeleteContractManagementType", false);
   }

   /**
    * @param \Contract $contract
    *
    * @return bool
    */
   public function showFormAddContractManagementType(Contract $contract) {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

      $rand    = mt_rand();
      $canView = $contract->can($contract->fields['id'], READ);
      $canEdit = $contract->can($contract->fields['id'], UPDATE);
      $config  = PluginManageentitiesConfig::getInstance();

      if (!$canView)
         return false;

      $restrict         = ["`glpi_plugin_manageentities_contracts`.`entities_id`"  => $contract->fields['entities_id'],
                           "`glpi_plugin_manageentities_contracts`.`contracts_id`" => $contract->fields['id']];
      $dbu              = new DbUtils();
      $pluginContracts  = $dbu->getAllDataFromTable("glpi_plugin_manageentities_contracts", $restrict);
      $pluginContract   = reset($pluginContracts);
      $idPluginContract = ElementType::CONTRACT_MANAGEMENT_TYPE;

      echo "<br><br><div align='spaced' id='content_contract_management_type'><table class='tab_cadre_fixe center'>";

      echo "<tr><th colspan='4'>" . PluginManageentitiesContract::getTypeName(0) . "</th></tr>";

      echo "<tr class='tab_bg_1'><td>" . __('Date of signature', 'manageentities') . "</td>";
      echo "<td>";
      echo "<input id='cmanagetype_date_signature" . $idPluginContract . "' type='text' size='10' name='cmanagetype_date_signature" . $idPluginContract . "'
            value='" . (isset($pluginContract['date_signature']) && $pluginContract['date_signature'] != "NULL" && $pluginContract['date_signature'] ? date('d-m-Y', strtotime($pluginContract['date_signature'])) : '') . "' >";

      echo "</td><td>" . __('Date of renewal', 'manageentities') . "</td><td>";

      echo "<input id='cmanagetype_date_renewal" . $idPluginContract . "' type='text' size='10' name='cmanagetype_date_renewal" . $idPluginContract . "'
            value='" . (isset($pluginContract['date_renewal']) && $pluginContract['date_renewal'] != "NULL" && $pluginContract['date_renewal'] ? date('d-m-Y', strtotime($pluginContract['date_renewal'])) : '') . "' >";

      echo "</td></tr>";
      $idDpContractType = 0;
      if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
         echo "<tr class='tab_bg_1'><td>" . __('Mode of management', 'manageentities') . "</td>";
         echo "<td>";
         PluginManageentitiesContract::dropdownContractManagement("management", $pluginContract['management'], $idPluginContract);
         echo "</td><td>" . __('Type of service contract', 'manageentities') . "</td><td>";
         $idDpContractType = PluginManageentitiesContract::dropdownContractType("contract_type", $pluginContract['contract_type'], $idPluginContract);
         echo "</td></tr>";
      }

      if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY && $config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
         echo "<tr class='tab_bg_1'><td>" . __('Contract is imported in GLPI', 'manageentities') . "</td>";
         echo "<td>";
         $sel = "";
         if (isset($pluginContract['contract_added']) && $pluginContract['contract_added'] == "1") {
            $sel = "checked";
            echo "<input type='checkbox' name='contract_added' id='contract_added' value='0' $sel>";
         } else {
            echo "<input type='checkbox' name='contract_added' id='contract_added' value='1' $sel>";
         }
         echo "</td><td colspan='2'></td></tr>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Show on global GANTT') . "</td>";
      echo "<td>";
      $idDpYNonGANT = Dropdown::showYesNo("show_on_global_gantt",
                                          isset($pluginContract["show_on_global_gantt"]) ? $pluginContract["show_on_global_gantt"] : 0,
                                          -1,
                                          ["rand" => $idPluginContract]);
      echo "</td>";

      echo "<td>" . __('Refacturable costs', 'manageentities') . "</td>";
      echo "<td>";
      $sel = "";
      if (isset($pluginContract['refacturable_costs']) && $pluginContract['refacturable_costs'] == "1") {
         $sel = "checked";
         echo "<input type='checkbox' name='refacturable_costs' id='refacturable_costs' value='0' $sel>";
      } else {
         echo "<input type='checkbox' name='refacturable_costs' id='refacturable_costs' value='1' $sel>";
      }
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Movement management', 'manageentities') . "</td>";
      echo "<td>";
      $rand = Dropdown::showYesNo("moving_management",
                                  isset($pluginContract["moving_management"]) ? $pluginContract["moving_management"] : 0,
                                  -1,
                                  ['on_change' => 'changemovement();']);
      echo Html::scriptBlock("
         function changemovement(){
            if($('#dropdown_moving_management$rand').val() != 0){
               $('#movementlabel').show();
               $('#movement').show();
            } else {
               $('#movementlabel').hide();
               $('#movement').hide();
            }
         }
         changemovement();
      ");
      echo "</td>";
      echo "<td><div id='movementlabel'>" . __('Duration of moving', 'manageentities') . "</div></td>";

      echo "<td><div id='movement'>";
      $rand_duration = Dropdown::showTimeStamp('duration_moving',
                                               ['value'           => isset($pluginContract["duration_moving"]) ? $pluginContract["duration_moving"] : 0,
                                                'addfirstminutes' => true]);
      echo "</div></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<input type='hidden' name='contracts_id' value='" . $contract->fields['id'] . "'>";
      echo "<input type='hidden' name='entities_id' value='" . $contract->fields['entities_id'] . "'>";

      if ($canEdit) {
         echo "<td class='center' colspan='2'>";
         echo "<input type='submit' class='submit' name='btnAddContractManagementType' id='btnAddContractManagementType'";
         if (empty($pluginContract)) {
            echo " value=\"" . _sx('button', 'Add') . "\" ";
         } else {
            echo " value=\"" . _sx('button', 'Update') . "\" ";
         }
         echo " class='submit' onclick='addContractManagementType();'>";
         echo "</td><td class='center' colspan='2'>";
         echo "<input type='submit' class='submit' name='btnDeleteContractManagementType' id='btnDeleteContractManagementType' value='" . _sx('button', 'Delete permanently') . "' class='submit' onclick='confirm_deleteContractManagementType();' ";
         if (empty($pluginContract)) {
            echo " style='visibility:hidden' ";
         }
         echo " />";
         echo "</td>";
      }
      echo "</tr>";
      echo "</table></div>";

      $this->initDate("cmanagetype_date_signature" . $idPluginContract);
      $this->initDate("cmanagetype_date_renewal" . $idPluginContract);

      $listId = [
         "cmanagetype_date_signature" . $idPluginContract => ["text", "date_signature"],
         "cmanagetype_date_renewal" . $idPluginContract   => ["text", "date_renewal"],
         "contract_added"                                 => ["checkbox", "contract_added"],
         "dropdown_show_on_global_gantt" . $idDpYNonGANT  => ["dropdown", "show_on_global_gantt"],
         "dropdown_contract_type" . $idDpContractType     => ["dropdown", "contract_type"],
         "refacturable_costs"                             => ["checkbox", "refacturable_costs"],
         "dropdown_moving_management" . $rand             => ["dropdown", "moving_management"],
         "dropdown_duration_moving" . $rand_duration      => ["dropdown", "duration_moving"],
      ];

      $idDivAjax = "tabcontractmanagementajax";

      $params = [
         'action'       => Action::ADD_CONTRACT_MANAGEMENT_TYPE,
         "id_div_ajax"  => $idDivAjax,
         "contracts_id" => $contract->fields['id'],
         "entities_id"  => $contract->fields['entities_id']
      ];

      echo "<div id='" . $idDivAjax . "' style='text-align:center;'></div>";

      $this->showJSfunction("addContractManagementType", $idDivAjax, $this->pModel->getUrl(), $listId, $params);

      $params['action'] = Action::CONFIRM_DELETE_CONTRACT_MANAGEMENT_TYPE;
      $this->showJSfunction("confirm_deleteContractManagementType", $idDivAjax, $this->pModel->getUrl(), [], $params);

      $params['action'] = Action::DELETE_CONTRACT_MANAGEMENT_TYPE;
      $this->showJSfunction("deleteContractManagementType", $idDivAjax, $this->pModel->getUrl(), [], $params);
   }

   /**
    * @param int   $idIntervention
    * @param array $paramsF
    *
    * @return array
    */
   public function showFormAddInterventions($idIntervention = 1, $paramsF = []) {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

      $currentContractday = $this->pModel->getContractDay($idIntervention);

      $rand     = $idIntervention;
      $realRand = mt_rand();

      $config      = PluginManageentitiesConfig::getInstance();
      $conso       = 0;
      $contract_id = 0;
      $contract    = $this->pModel->getContract();
      if (!isset($contract->fields['id'])) {
         $contract->getEmpty();
      }

      $entity = $this->pModel->getEntity();
      if (!isset($entity->fields['id'])) {
         $entity->getEmpty();
      }

      $ID      = 0;
      $options = ["contract_id" => ""];

      $restrict = ["`glpi_plugin_manageentities_contracts`.`entities_id`"  => $contract->fields['entities_id'],
                   "`glpi_plugin_manageentities_contracts`.`contracts_id`" => $contract->fields['id']];

      $dbu             = new DbUtils();
      $pluginContracts = $dbu->getAllDataFromTable("glpi_plugin_manageentities_contracts", $restrict);
      $pluginContract  = reset($pluginContracts);

      $unit = PluginManageentitiesContract::getUnitContractType($config, isset($pluginContract['contract_type']) ? $pluginContract['contract_type'] : []);


      // Onchange for img purpose
      echo "   <div id='tabs-" . ($idIntervention) . "' style='padding:0px;' onchange=\"javascript:";
      $this->updateImgTabTitle(true, "'img_" . $currentContractday->getType() . ($idIntervention) . "'", $this->pModel->getMessage(ElementType::INTERVENTION, Status::NOT_SAVED));
      echo "\" >";

      echo "<input type='hidden' name='fakeid_new_intervention" . $rand . "' id='fakeid_new_intervention" . $rand . "' value='" . $idIntervention . "' />";


      echo "<table class='tab_cadre_fixe'>";

      // Entite
      echo "<tr  class='tab_bg_1'>";
      //         INFOTEL : MODIFICATION PRESALES
      if (!isset($paramsF["presales"])) {
         echo "<td>" . __("Entity") . $this->pModel->getMessage("mandatory_field") . "</td>";
         echo "<td colspan='3'>";

         echo "<div id='div_select_entity_for_intervention" . $rand . "' ";

         if (isset($currentContractday->fields['entities_id']) && isset($this->pModel->getEntity()->fields['id']) && $this->pModel->getEntity()->fields['id'] > 0 && $currentContractday->fields['entities_id'] == $this->pModel->getEntity()->fields['id'] || ((!isset($currentContractday->fields['entities_id']) || $currentContractday->fields['entities_id'] == "") && isset($this->pModel->getEntity()->fields['id']) && $this->pModel->getEntity()->fields['id'] > 0)) {
            echo " style='visibility:hidden;' ";
         }
         echo " >";

         $condition  = $dbu->getEntitiesRestrictCriteria("glpi_entities");
         $idDpEntity = Dropdown::show($dbu->getItemTypeForTable("glpi_entities"), [
            'name'       => 'intervention_entities_id',
            'value'      => isset($currentContractday->fields['entities_id']) ? $currentContractday->fields ['entities_id'] : 0,
            'emptylabel' => __("New entity", "manageentities"),
            'on_change'  => 'updateCriPrice' . $idIntervention . '();updateContractList' . $realRand . '()',
            'condition'  => $condition
         ]);
         echo "</div>";

         echo "<label for='previous_entity_for_intervention" . $rand . "'> <input type='checkbox' name='previous_entity_for_intervention" . $rand . "' id='previous_entity_for_intervention" . $rand . "' ";
         if (isset($currentContractday->fields['entities_id']) && isset($this->pModel->getEntity()->fields['id']) && $this->pModel->getEntity()->fields['id'] > 0 && $currentContractday->fields['entities_id'] == $this->pModel->getEntity()->fields['id'] || ((!isset($currentContractday->fields['entities_id']) || $currentContractday->fields['entities_id'] == "") && isset($this->pModel->getEntity()->fields['id']) && $this->pModel->getEntity()->fields['id'] > 0)) {
            echo " checked='checked' ";
         }

         echo " title='" . __("New entity created previously", "manageentities") . "' onclick=\"switchElementsEnableFromCb(this,'div_select_entity_for_intervention" . $rand . "');\" /> " . __("New entity created previously", "manageentities") . "</label>";


         echo "</td>";
         echo "</tr>";

         // Contrat
         echo "<tr  class='tab_bg_1'>";
         echo "<td>" . __("Contract") . $this->pModel->getMessage("mandatory_field") . "</td>";
         echo "<td  id='cell_select_contract_for_intervention" . $idIntervention . "' >";
         echo "<div id='div_select_contract_for_intervention" . $idIntervention . "' ";


         if (isset($currentContractday->fields['contracts_id']) && isset($this->pModel->getContract()->fields['id']) && $this->pModel->getContract()->fields['id'] > 0 && $currentContractday->fields['contracts_id'] == $this->pModel->getContract()->fields['id'] || ((!isset($currentContractday->fields['contracts_id']) || $currentContractday->fields['contracts_id'] == "") && isset($this->pModel->getContract()->fields['id']) && $this->pModel->getContract()->fields['id'] > 0)) {
            echo " style='visibility:hidden;' ";
         }
         echo " >";

         $idDpContract = Dropdown::show($dbu->getItemTypeForTable("glpi_contracts"), [
            'name'       => 'intervention_contracts_id',
            'value'      => isset($currentContractday->fields['contracts_id']) ? $currentContractday->fields ['contracts_id'] : 'name',
            'emptylabel' => __("New contract", "manageentities")
         ]);

         echo "</div>";
         echo "<label for='previous_contract_for_intervention" . $rand . "'> <input type='checkbox' name='previous_contract_for_intervention" . $rand . "' id='previous_contract_for_intervention" . $rand . "' ";
         if (isset($currentContractday->fields['contracts_id']) && isset($this->pModel->getContract()->fields['id']) && $this->pModel->getContract()->fields['id'] > 0 && $currentContractday->fields['contracts_id'] == $this->pModel->getContract()->fields['id'] || ((!isset($currentContractday->fields['contracts_id']) || $currentContractday->fields['contracts_id'] == "") && isset($this->pModel->getContract()->fields['id']) && $this->pModel->getContract()->fields['id'] > 0)) {
            echo " checked='true' ";
         }
         echo " autocomplete='off' title='" . __("New contract created previously", "manageentities") . "' onclick=\"switchElementsEnableFromCb(this,'div_select_contract_for_intervention" . $rand . "');\" /> " . __("New contract created previously", "manageentities") . "</label>";
         echo "</td>";

      } else {
         echo "<input type='hidden' name='presales" . $rand . "' id='presales" . $rand . "' value='" . $paramsF["presales"] . "' />";
         echo "<td hidden>";

         $condition  = $dbu->getEntitiesRestrictCriteria("glpi_entities");
         $idDpEntity = Dropdown::show($dbu->getItemTypeForTable("glpi_entities"), [
            'name'       => 'intervention_entities_id',
            'value'      => $paramsF["presales"],
            'emptylabel' => __("New entity", "manageentities"),
            'on_change'  => 'updateCriPrice' . $idIntervention . '();updateContractList' . $realRand . '()',
            'condition'  => $condition
         ]);

         //         echo Html::hidden("contract_entities_id", ["value" => $params["presales"], "id" => "paramshide"]);


         echo "</td>";
         echo "</tr>";
         echo "<tr  class='tab_bg_1'>";
         echo "<td></td>";
         echo "<td></td>";
         echo "<td hidden>";


         $idDpContract = Dropdown::show($dbu->getItemTypeForTable("glpi_contracts"), [
            'name'       => 'intervention_contracts_id',
            'value'      => $paramsF["contracts_id"],
            'emptylabel' => __("New contract", "manageentities")
         ]);

         echo "</td>";

      }
      $idDpContractType = 0;
      if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
         echo "<td>" . __('Type of service contract', 'manageentities') . "<span style='color:red;'>&nbsp;*&nbsp;</span></td>";
         echo "<td id='div_select_contract_type" . $idIntervention . "'>";
         $idDpContractType = PluginManageentitiesContract::dropdownContractType("contract_type", $currentContractday->fields['contract_type']);
         echo "</td>";
      } else {
         echo "<td colspan='2'></td>";
      }
      echo "</tr>";

      if (isset($options['contract_id'])) {
         $contract_id = $options['contract_id'];
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Period name", "manageentities") . $this->pModel->getMessage("mandatory_field") . "</td>";
      echo "<td>";
      echo "<input type='text' name='intervention_name" . $idIntervention . "' id='intervention_name" . $idIntervention . "'  value='" . (isset($currentContractday->fields['name']) ? $currentContractday->fields['name'] : '') . "'  />";
      echo "</td>";

      if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] == PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED) {
         echo "<td></td><td></td>";
      } else {
         echo "<td>" . __('Postponement', 'manageentities') . "</td>";
         echo "<td><input type='text' name='report" . $idIntervention . "' id='report" . $idIntervention . "' value='" .
              Html::formatNumber($currentContractday->fields["report"], true, 1) . "'size='5'>";
         echo "&nbsp;" . $unit;
         echo "</td>";
      }
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Begin date') . $this->pModel->getMessage("mandatory_field") . "</td>";
      echo "<td>";

      echo "<input id='intervention_begin_date" . $idIntervention . "' type='text' size='10' name='intervention_begin_date" . $idIntervention . "' value='" . (isset($currentContractday->fields['begin_date']) && $currentContractday->fields['begin_date'] != "NULL" && $currentContractday->fields['begin_date'] ? Html::convDate($currentContractday->fields['begin_date']) : '') . "' >";

      echo "</td>";
      echo "<td>" . __('End date') . "</td><td>";
      echo "<input id='intervention_end_date" . $idIntervention . "' type='text' size='10' name='intervention_end_date" . $idIntervention . "'  value='" . (isset($currentContractday->fields['end_date']) && $currentContractday->fields['end_date'] != "NULL" ? $currentContractday->fields['end_date'] : '') . "' >";

      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Initial credit', 'manageentities') . "</td>";
      if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && $pluginContract['contract_type'] == PluginManageentitiesContract::CONTRACT_TYPE_UNLIMITED) {
         echo "<td>&nbsp;";
      } else {
         echo "<td><input type='text' name='nbday" . $idIntervention . "' id='nbday" . $idIntervention . "' value='" .
              Html::formatNumber($currentContractday->fields["nbday"], true, 1) . "'size='5'>";
      }
      echo "&nbsp;" . $unit;

      echo "</td>";
      echo "<td>" . __('State of contract', 'manageentities') . $this->pModel->getMessage("mandatory_field") . "</td>";
      echo "<td id='div_select_contractstate" . $idIntervention . "'>";
      $idDpContractState = Dropdown::show('PluginManageentitiesContractState', ['value'  => $currentContractday->fields['plugin_manageentities_contractstates_id'],
                                                                                'entity' => $currentContractday->fields["entities_id"]]);
      echo "<input type='hidden' name='contracts_id' value='" . $contract_id . "'>";
      echo "<input type='hidden' name='contract_id' value='" . $contract_id . "'>";

      echo "</td></tr>";


      // We get all cri detail data
      //      $currentContractday->fields['contractdays_id'] = isset($currentContractday->fields['id']) ? $currentContractday->fields['id'] : 0;
      //      $resultCriDetail = PluginManageentitiesCriDetail::getCriDetailData($currentContractday->fields);
      //      foreach($resultCriDetail['result'] as $dataCriDetail){
      //         //Conso
      //         $conso += $dataCriDetail['conso'];
      //      }
      //      var_dump($this->pModel->getCriPrice($idIntervention));
      //      if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE && ($this->pModel->getCriPrice($idIntervention) == null || (is_array($this->pModel->getCriPrice($idIntervention)) && sizeof($this->pModel->getCriPrice($idIntervention)) <= 0 ))) {
      //         echo "<tr class='tab_bg_1' id='cripriceContentTypeInterv".$idIntervention."'><td>".__('Intervention type by default', 'manageentities').$this->pModel->getMessage("mandatory_field")."</td>";
      //         echo "<td colspan='3' id='div_select_interventiontype".$idIntervention."'>";
      //         Dropdown::show('PluginManageentitiesCriType',
      //         array(
      //               'rand' => $rand,
      //               'value' => $currentContractday->fields['plugin_manageentities_critypes_id'],
      //               'entity' => $currentContractday->fields["entities_id"],
      //               'on_change' => 'updateCriPrice'.$idIntervention.'()'));
      //         echo "</td>";
      //         echo "<tr class='tab_bg_1'>";
      //         echo "<td id='cripriceContentHourDay".$idIntervention."' >";
      //         if($config->fields['hourorday'] == PluginManageentitiesConfig::DAY){
      //            echo __('Daily rate', 'manageentities'). $this->pModel->getMessage("mandatory_field")."</td>"."</td>";
      //         }elseif($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR){
      //            echo __('Hourly rate', 'manageentities'). $this->pModel->getMessage("mandatory_field")."</td>"."</td>";
      //         }
      //         echo "<td colspan='3'>";
      //         $arrCriprice = $this->pModel->getCriPrice($idIntervention);
      //
      //         echo "<input type='text' name='price_".$idIntervention."' id='price_".$idIntervention."'  ";//value='".(isset($currentContractday->fields['name']) ? $currentContractday->fields['name']: '')."'  />";
      //         echo " value='".$arrCriprice[$currentContractday->fields['plugin_manageentities_critypes_id']]."' disabled";
      //         echo " />";
      //         echo "</tr>";
      //         echo "<tr class='tab_bg_1'>";
      //         echo "<td>".__('Remaining total (amount)', 'manageentities')."</td>";
      //         echo "<td colspan='3'>";
      //         echo Html::formatNumber($resultCriDetail['resultOther']['reste_montant']);
      //         echo "</tr>";
      //      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Already charged', 'manageentities') . "</td>";
      echo "<td colspan='3'>";
      $currentContractday->fields['charged'] == 1 ? $isCharged = "checked='checked'" : $isCharged = '';
      echo "<input type='checkbox' name='charged' id='charged' " . $isCharged . " />";

      echo "</td>";
      echo "</tr>";

      echo "<table class='tab_cadre_fixe' >";
      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='right'>";
      echo "<input type='submit' class='submit' name='btnAddIntervention" . $idIntervention . "' id='btnAddIntervention" . $idIntervention . "' ";

      if (isset($currentContractday->fields["id"]) && $currentContractday->fields["id"] > 0) {
         echo " value='" . __("Update this intervention", "manageentities") . "' ";
      } else {
         echo " value='" . __("Add only the intervention", "manageentities") . "' ";
      }


      echo " onclick='javascript:addOnlyIntervention" . $idIntervention . "();'";
      echo "/>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='right'>";
      echo "<input type='submit' class='submit' name='btnAddnewFormIntervention" . $idIntervention . "'  id='btnAddnewFormIntervention" . $idIntervention . "'  value='" . __("Add another intervention", "manageentities") . "' onclick=\"javascript:addAnotherIntervention" . $idIntervention . "();\"/>";
      echo "</td> </tr>";

      echo "</table>";

      //      var_dump($this->pModel->getAllCriPrice());


      if (isset($currentContractday->fields['id']) && $currentContractday->fields['id'] > 0) {
         $this->initCriPricesView($currentContractday, $idIntervention);
      }

      $this->initDate("intervention_begin_date" . $idIntervention);
      $this->initDate("intervention_end_date" . $idIntervention);

      $idDivNewIntervention  = "divaddnewintervention" . $rand;
      $idDivAjax             = "tabinterventionajax" . $rand;
      $idDivStakeHoldersAjax = "tabstakeholderajax" . $rand;

      $listId = [
         "fakeid_new_intervention" . $rand                 => ["hidden", "fakeid_new_intervention"],
         //         "price_".$idIntervention                                    => ["text","new_intervention_price"],
         "dropdown_intervention_entities_id" . $idDpEntity => ["dropdown", "new_intervention_entity_id"],
         "previous_entity_for_intervention" . $rand        => ["checkbox", "previous_entity_for_intervention"],
      ];

      $params = ['action'          => Action::UPDATE_CRI_PRICE,
                 "id_div_ajax"     => $idDivAjax,
                 "id_intervention" => $idIntervention
      ];
      $this->showJSfunction("updateCriPrice" . $idIntervention, $idDivAjax, $this->pModel->getUrl(), $listId, $params);

      $listId = ["fakeid_new_intervention" . $rand                 => ["hidden", "fakeid_new_intervention"],
                 "dropdown_intervention_entities_id" . $idDpEntity => ["dropdown", "new_intervention_entity_id"],
                 "previous_entity_for_intervention" . $rand        => ["checkbox", "previous_entity_for_intervention"],
      ];

      $params = ['action'               => Action::UPDATE_CONTRACT_LIST,
                 "id_div_ajax"          => "div_select_contract_for_intervention" . $idIntervention,
                 "id_intervention"      => $idIntervention,
                 "id_dropdown_entity"   => $idDpEntity,
                 "id_dropdown_contract" => $idDpContract
      ];

      $this->showJSfunction("updateContractList" . $realRand, "div_select_contract_for_intervention" . $idIntervention, $this->pModel->getUrl(), $listId, $params);

      $listId = [
         "fakeid_new_intervention" . $rand                                       => ["hidden", "fakeid_new_intervention"],
         "intervention_name" . $idIntervention                                   => ["text", "new_intervention_name"],
         "intervention_begin_date" . $idIntervention                             => ["text", "new_intervention_begin_date"],
         "intervention_end_date" . $idIntervention                               => ["text", "new_intervention_end_date"],
         "nbday" . $idIntervention                                               => ["text", "new_intervention_nbday"],
         "btnAddnewFormIntervention" . $idIntervention                           => ["button", "id_btn_add_intervention"],
         "report" . $idIntervention                                              => ["text", "new_intervention_report"],
         "dropdown_intervention_entities_id" . $idDpEntity                       => ["dropdown", "new_intervention_entity_id"],
         "dropdown_intervention_contracts_id" . $idDpContract                    => ["dropdown", "new_intervention_contract_id"],
         "charged"                                                               => ["checkbox", "new_intervention_charged"],
         "previous_entity_for_intervention" . $rand                              => ["checkbox", "previous_entity_for_intervention"],
         "previous_contract_for_intervention" . $rand                            => ["checkbox", "previous_contract_for_intervention"],
         "dropdown_plugin_manageentities_contractstates_id" . $idDpContractState => ["dropdown", "new_intervention_contractstate_id"],
         "dropdown_contract_type" . $idDpContractType                            => ["dropdown", "contract_type"]
      ];
      //         INFOTEL : MODIFICATION PRESALES
      if (isset($paramsF["presales"])) {

         //         array_push();
         $listId = array_merge($listId, ["presales" . $rand => ["hidden", "presales"]]);
      }


      //      if ($this->pModel->getCriPrice($idIntervention) == null){
      //         $listId["dropdown_plugin_manageentities_critypes_id".$rand] = array("dropdown","new_intervention_critypes_id");
      //         $listId["price_".$idIntervention]                                          = array("text","new_intervention_price");
      //      }


      $paramsAddNewIntervention = ["action"             => Action::ADD_NEW_INTERVENTION,
                                   "id_div_new_contact" => $idDivNewIntervention,
                                   "id_div_ajax"        => $idDivAjax
      ];

      $params = ['action'          => Action::ADD_ONLY_INTERVENTION,
                 "id_div_ajax"     => $idDivAjax,
                 "id_intervention" => $idIntervention
      ];

      echo "<div id='viewlistcriprice" . $rand . "'>";
      echo "</div>";


      echo "<div id='" . $idDivStakeHoldersAjax . "' style='text-align:center;'>";

      $interventionSkateholder = new PluginManageentitiesInterventionSkateholder();
      if (isset($currentContractday->fields['id']) && $currentContractday->fields['id'] > 0) {
         $interventionSkateholder->displayTabContentForItem($currentContractday);
      }
      echo "</div>";

      echo "<div id='" . $idDivAjax . "' style='text-align:center;'>";


      echo "</div>";
      echo "</div>";
      echo "<div id='" . $idDivNewIntervention . "'></div>";

      $interventionContent = ['idDivAjax'                => $idDivAjax,
                              'listIds'                  => $listId,
                              'params'                   => $params,
                              'paramsAddNewIntervention' => $paramsAddNewIntervention,
                              'idDivNewIntervention'     => $idDivNewIntervention,
                              'idDivStakeholdersAjax'    => $idDivStakeHoldersAjax
      ];

      return $interventionContent;
   }

   /**
    * @param     $item
    * @param     $fakeIdIntervention
    * @param int $idCriPrice
    *
    * @return bool
    */
   public function initCriPricesView($item, $fakeIdIntervention, $idCriPrice = -1) {
      $idIntervention = $item->fields['id'];
      $this->pModel   = PluginManageentitiesAddElementsModel::getInstance();

      $this->showHeaderJS();
      echo "
         if (document.getElementById('cripriceContentTypeInterv" . $idIntervention . "')){
            document.getElementById('cripriceContentTypeInterv" . $idIntervention . "').style.display='none';
            document.getElementById('cripriceContentHourDay" . $idIntervention . "').style.display='none';
         }
      ";
      $this->closeFormJS();

      if (!$item->canView())
         return false;
      if (!$item->canCreate())
         return false;
      if (!isset($item->fields['id']))
         return false;

      $canedit = $item->can($item->fields['id'], UPDATE);

      $rand = mt_rand();

      if (isset($_POST["start"])) {
         $start = $_POST["start"];
      } else {
         $start = 0;
      }

      $criPrice = new PluginManageentitiesCriPrice();
      $data     = $criPrice->getItems($item->fields['id'], $start);

      if ($canedit) {
         echo "<div id='viewcriprice_" . $fakeIdIntervention . "'></div>\n";

         $params = ['action'                  => Action::SHOW_FORM_CRI_PRICE,
                    "id_div_ajax"             => "viewcriprice_" . $fakeIdIntervention,
                    "id_intervention"         => isset($item->fields['id']) && $item->fields['id'] > 0 ? $item->fields['id'] : -1,
                    "id_criprice"             => $idCriPrice,
                    "parent"                  => "PluginManageentitiesContractDay",
                    "fakeid_new_intervention" => $fakeIdIntervention
         ];

         $this->showJSfunction("showFormCriPrice" . $rand, "viewcriprice_" . $fakeIdIntervention, $this->pModel->getUrl(), [], $params);

         echo "<div class='center firstbloc'>" .
              "<input type='button' class='vsubmit' onclick='showFormCriPrice" . $rand . "();' value='" . __('Add a new price', 'manageentities') . "' /></div>\n";
      }


      $this->listCriPrices($item->fields['id'], $data, $canedit, $rand, $idCriPrice, $fakeIdIntervention);
   }

   /**
    * @param $item
    * @param $fakeIdIntervention
    * @param $criPrice
    * @param $status
    */
   public function updateListItems($item, $fakeIdIntervention, $criPrice, $status) {

      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

      switch ($status) {
         case Status::ADDED:
            $criType = new PluginManageentitiesCriType();
            $criType->getFromDB($criPrice->fields['plugin_manageentities_critypes_id']);

            $isDefault   = $criPrice->fields['is_default'] == 0 ? __('No') : __('Yes');
            $criTypeName = $criType->fields['name'];

            $cells = ["entity"     => Dropdown::getDropdownName("glpi_entities", $criPrice->fields['entities_id']),
                      "cprice"     => Html::formatNumber($criPrice->fields['price'], true),
                      "critype"    => $criTypeName,
                      "is_default" => $isDefault,
                      "id"         => $criPrice->fields['id']
            ];

            $onclick = "showFormCriPrice" . $criPrice->fields['id'] . "()";

            $params = ['action'                  => Action::SHOW_FORM_CRI_PRICE,
                       "id_div_ajax"             => "viewcriprice_" . $fakeIdIntervention,
                       "id_intervention"         => isset($item->fields['id']) && $item->fields['id'] > 0 ? $item->fields['id'] : -1,
                       "parent"                  => "PluginManageentitiesContractDay",
                       "fakeid_new_intervention" => $fakeIdIntervention,
                       "id_criprice"             => $criPrice->fields['id']
            ];

            $listId = [];

            $this->showJSfunction("showFormCriPrice" . $criPrice->fields['id'], "viewcriprice_" . $fakeIdIntervention, $this->pModel->getUrl(), $listId, $params);

            $this->showHeaderJS();
            echo "if (document.getElementById('tablelistcriprices" . $fakeIdIntervention . "').style.visibility=='hidden'){
               document.getElementById('tablelistcriprices" . $fakeIdIntervention . "').style.visibility='visible';
            }";
            $this->closeFormJS();

            $this->addRowOnTable('tablelistcriprices' . $fakeIdIntervention, 'row_criprice_' . $criPrice->fields['id'], $cells, null);
            break;

         case Status::UPDATED:

            $criType = new PluginManageentitiesCriType();
            $criType->getFromDB($criPrice->fields['plugin_manageentities_critypes_id']);

            $isDefault   = $criPrice->fields['is_default'] == 0 ? __('No') : __('Yes');
            $criTypeName = $criType->fields['name'];

            $this->updateCellOnTable('price_' . $criPrice->fields['id'], Html::formatNumber($criPrice->fields['price'], true));
            $this->updateCellOnTable('is_default_' . $criPrice->fields['id'], Dropdown::getYesNo($criPrice->fields['is_default']));

            break;
      }


      //      $this->showHeaderJS();
      //       echo "
      //       if (document.getElementById('contentListCriPrices') != null){
      //            $('#price_".$criPrice->fields['id']."').html(".Html::formatNumber($criPrice->fields['price'], true).");
      //            $('#critypes_".$criPrice->fields['id']."').html('".$criTypeName."');
      //            $('#is_default_".$criPrice->fields['id']."').html('".$isDefault."');
      //      }";
      //      $this->closeFormJS();
   }

   /**
    * @param $ID
    * @param $data
    * @param $canedit
    * @param $rand
    * @param $idCriPrice
    * @param $fakeIdIntervention
    */
   public function listCriPrices($ID, $data, $canedit, $rand, $idCriPrice, $fakeIdIntervention) {
      global $CFG_GLPI;


      //      echo "<div id='viewlistcriprice".$fakeIdIntervention."'>";

      echo "<div class='center' id='contentListCriPrices" . $fakeIdIntervention . "'>";

      if (!isset($ID) || $ID <= 0) {
         $ID = -1;
      }

      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();
      $config       = PluginManageentitiesConfig::getInstance();


      echo "<table class='tab_cadre_fixehov' id='tablelistcriprices" . $fakeIdIntervention . "'";

      if (empty($data)) {
         echo " style='visibility:hidden;' ";
      }
      echo " >";

      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Entity') . "</th>";

      //Intervention type only for daily
      //      if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
      echo "<th>" . __('Intervention type', 'manageentities') . "</th>";
      //      }
      // Display for hourly or daily price title
      if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
         echo "<th>" . __('Daily rate', 'manageentities') . "</th>";
      } elseif ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
         echo "<th>" . __('Hourly rate', 'manageentities') . "</th>";
      }
      echo "<th>" . __('Is default', 'manageentities') . "</th>";
      echo "</tr>";

      foreach ($data as $field) {
         //         $onclick = ($canedit ? "style='cursor:pointer' onClick=\"showFormCriPrice".$field['id'].$rand."();\"" : '');
         $onclick = "";

         echo "<tr class='tab_bg_2' id='row_criprice_" . $field['id'] . "'>";
         if ($canedit) {

            $params = ['action'                  => Action::SHOW_FORM_CRI_PRICE,
                       "id_div_ajax"             => "viewcriprice_" . $fakeIdIntervention,
                       "id_intervention"         => $ID,
                       "parent"                  => "PluginManageentitiesContractDay",
                       "fakeid_new_intervention" => $fakeIdIntervention,
                       "id_criprice"             => $field['id'],
            ];

            $listId = [];

            $this->showJSfunction("showFormCriPrice" . $field['id'] . $rand, "viewcriprice_" . $fakeIdIntervention, $this->pModel->getUrl(), $listId, $params);
         }
         echo "<td $onclick>" . Dropdown::getDropdownName("glpi_entities", $field['entities_id']) . "</td>";

         //Intervention type only for daily
         //         if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
         echo "<td $onclick id='critypes_" . $field['id'] . "'>" . $field["critypes_name"] . "</td>";
         //         }
         //         echo "<td $onclick > <span id='price_".$field['id']."'> ".Html::formatNumber($field["price"], true)."</span></td>";
         //         echo "<td $onclick > <span id='is_default_".$field['id']."'> ".Dropdown::getYesNo($field["is_default"])."</span></td>";

         echo "<td $onclick id='price_" . $field['id'] . "'>" . Html::formatNumber($field["price"], true) . "</td>";
         echo "<td $onclick id='is_default_" . $field['id'] . "'>" . Dropdown::getYesNo($field["is_default"]);
         //         echo "<input type='hidden' name='id_criprice".$ID."' id='id_criprice".$ID."' value='".$ID."' />";
         echo "</td>";
         echo "</tr>";
      }

      echo "</table>";

      echo "</div>";
   }

   /**
    * @param $idCriPrice
    * @param $idIntervention
    * @param $fakeIdIntervention
    * @param $options
    */
   public function showFormCriPrice($idCriPrice, $idIntervention, $fakeIdIntervention, $options) {
      $rand         = $fakeIdIntervention;
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();
      $item         = new PluginManageentitiesCriPrice();
      $dbu          = new DbUtils();
      $parent       = $dbu->getItemForItemtype($options['parent']);
      if ($idIntervention != -1) {
         $parent->getFromDB($idIntervention);
      }

      if ($idCriPrice > 0) {
         $item->check($idCriPrice, 'r');
      } else {
         // Create item
         $options['plugin_manageentities_contractdays_id'] = $parent->getField('id');
         $item->check(-1, UPDATE, $options);
      }

      $config = PluginManageentitiesConfig::getInstance();

      $data = $item->getItems($parent->getField('id'));

      //      $used_critypes = [];
      //      if(!empty($data)){
      //         foreach($data as $field){
      //            $used_critypes[] = $field['plugin_manageentities_critypes_id'];
      //         }
      //      }

      $item->showFormHeader($options);
      echo "<tr class='tab_bg_1'>";
      // Cri Type
      echo "<td>";
      echo PluginManageentitiesCriType::getTypeName() . '&nbsp;' . $this->pModel->getMessage("mandatory_field");
      echo "<input type='hidden' name='id_criprice" . $item->fields['id'] . "' id='id_criprice" . $item->fields['id'] . "' value='" . $item->fields['id'] . "' />";
      echo "</td>";

      echo "<td>";
      $idDpCriType = Dropdown::show('PluginManageentitiesCriType', ['name'      => 'plugin_manageentities_critypes_id',
                                                                    'value'     => $item->fields['plugin_manageentities_critypes_id'],
                                                                    'entity'    => $parent->getField('entities_id'),
                                                                    //         'used'      => $used_critypes,
                                                                    'rand'      => $rand,
                                                                    'on_change' => 'updateCriPriceInput' . $fakeIdIntervention . '()'
      ]);
      echo "</td>";


      // Price
      echo "<td>";
      // Display for hourly or daily price title
      if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
         echo __('Daily rate', 'manageentities');
      } elseif ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
         echo __('Hourly rate', 'manageentities');
      }
      echo $this->pModel->getMessage("mandatory_field");
      echo "</td>";
      echo "<td>";
      //      Html::autocompletionTextField($item, "price", ['value' => $item->fields['price'], 'rand' => $rand]);

      echo "<input type='text' name='textfield_price" . $rand . "' id='textfield_price" . $rand . "' value='" . $item->fields['price'] . "' size='40' class='x-form-text x-form-field x-form-focus'/>";

      echo "</td>";
      echo "</tr>";
      echo "<tr class='tab_bg_1'>";
      // Is default
      echo "<td>";
      echo __('Is default', 'manageentities') . '&nbsp;';
      echo "</td>";
      echo "<td>";
      $idDpIsdefault = Dropdown::showYesNo('is_default', $item->fields['is_default']);
      echo "</td>";
      echo "<td colspan='2'></td>";
      echo "</tr>";

      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' style='text-align: center'>";
      echo "<input type='button' class='vsubmit' name='btn_add_cprice_" . $rand . "' id='btn_add_cprice_" . $rand . "'";
      if (isset($item->fields['id']) && $item->fields['id'] > 0) {
         echo "   value='" . _n('Update this price', 'Update these prices', 1, 'manageentities') . "'";
      } else {
         echo "   value='" . _n('Add this price', 'Add these prices', 1, 'manageentities') . "'";
      }
      echo "   onclick='addCriPrice" . $rand . "();'/>";

      if (isset($item->fields['id']) && $item->fields['id'] > 0) {
         echo "<input type='button' class='vsubmit' style='margin-left:16px;' name='btn_delete_cprice_" . $rand . "' id='btn_delete_cprice_" . $rand . "'";
         echo "   value='" . _n('Delete this price', 'Delete these prices', 1, 'manageentities') . "'";
         echo "   onclick='deleteCriPrice" . $rand . "();'/>";
      }

      echo "</td>";
      echo "</tr>";

      echo "</table>";


      //      $item->showFormButtons($options);

      $idDivAjax = "viewaddcriprice" . "_" . $rand;
      echo "<div id='" . $idDivAjax . "'></div>\n";

      $params = ["id_div_ajax"                           => "viewcriprice_" . $fakeIdIntervention,
                 "plugin_manageentities_contractdays_id" => $parent->getField('id'),
                 "entities_id"                           => $parent->getField('entities_id'),
                 "parent"                                => "PluginManageentitiesContractDay",
                 "fakeid_new_intervention"               => $fakeIdIntervention
      ];

      $params['action'] = Action::ADD_CRI_PRICE;

      // Variables to ajax add entity
      $listId = ["dropdown_is_default" . $idDpIsdefault               => ["dropdown", "new_criprice_is_default"],
                 "textfield_price" . $rand                            => ["text", "new_criprice_pricefield"],
                 "id_criprice" . $item->fields['id']                  => ["hidden", "id_criprice"],
                 "dropdown_plugin_manageentities_critypes_id" . $rand => ["dropdown", "new_criprice_critypes"],
      ];

      $this->showJSfunction("addCriPrice" . $rand, $idDivAjax, $this->pModel->getUrl(), $listId, $params);

      $params['action'] = Action::DELETE_CRI_PRICE;
      $this->showJSfunction("deleteCriPrice" . $rand, $idDivAjax, $this->pModel->getUrl(), $listId, $params);


      $params = ['action'                                => Action::UPDATE_CRI_PRICE_FROM_TYPE,
                 "id_div_ajax"                           => "viewcriprice_" . $fakeIdIntervention,
                 "plugin_manageentities_contractdays_id" => $parent->getField('id'),
                 "entities_id"                           => $parent->getField('entities_id'),
                 "parent"                                => "PluginManageentitiesContractDay",
                 "id_criprice"                           => isset($item->fields['id']) && $item->fields['id'] > 0 ? $item->fields['id'] : 0,
                 "fakeid_new_intervention"               => $fakeIdIntervention,
                 "inputfield"                            => "textfield_price" . $rand
      ];

      // Variables to ajax add entity
      $listId = [
         "dropdown_plugin_manageentities_critypes_id" . $idDpCriType => ["dropdown", "new_criprice_critype"],
         "id_criprice" . $item->fields['id']                         => ["hidden", "id_criprice"]
      ];

      $this->showJSfunction("updateCriPriceInput" . $rand, $idDivAjax, $this->pModel->getUrl(), $listId, $params);
   }

   /**
    * @param $entitiesId
    * @param $previousEntity
    * @param $idIntervention
    */
   public function changeContractList($entitiesId, $previousEntity, $idIntervention) {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();
      $realRand     = mt_rand();
      $dbu          = new DbUtils();
      $ids          = $dbu->getSonsOf("glpi_entities", $entitiesId);

      $condition = $dbu->getEntitiesRestrictCriteria("glpi_contracts", "entities_id", $ids, true);

      $idDpContract = Dropdown::show($dbu->getItemTypeForTable(Contract::getTable()), [
         'name'       => 'intervention_contracts_id',
         'emptylabel' => __("New contract", "manageentities"),
         'condition'  => $condition,
         'rand'       => $_POST['id_dropdown_contract']
      ]);

      $listId = [
         "fakeid_new_intervention" . $idIntervention            => ["hidden", "fakeid_new_intervention"],
         "dropdown_intervention_contracts_id" . $idDpContract   => ["dropdown", "new_intervention_entity_id"],
         "previous_contract_for_intervention" . $idIntervention => ["checkbox", "previous_entity_for_intervention"],
      ];

      $params = ['action'               => Action::UPDATE_CONTRACT_LIST,
                 "id_div_ajax"          => "div_select_contract_for_intervention" . $idIntervention,
                 "id_intervention"      => $idIntervention,
                 "id_dropdown_entity"   => $_POST['id_dropdown_entity'],
                 "id_dropdown_contract" => $_POST['id_dropdown_contract']
      ];

      $this->showJSfunction("updateContractList" . $realRand, "div_select_contract_for_intervention" . $idIntervention, $this->pModel->getUrl(), $listId, $params);
   }

   // ----------------------------------------------------------------------------------------------------------------------------------------------------------
   // JS functions
   // ----------------------------------------------------------------------------------------------------------------------------------------------------------

   /**
    * @param $doc
    * @param $addHeaderList
    */
   public function addPDFContractToView($doc, $addHeaderList) {
      global $CFG_GLPI;
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

      $docItem = new Document_Item();
      $docItem->getFromDBByCrit(['documents_id' => $doc->fields['id']]);

      $docCategory = new DocumentCategory();
      $docCategory->getFromDB($doc->fields['documentcategories_id']);
      if (!isset($docCategory->fields['name']) || $docCategory->fields['name'] == null) {
         $docCategory->fields['name'] = "";
      }

      $entity = new Entity();
      $entity->getFromDB($doc->fields['entities_id']);

      $link         = $doc->getLinkURL();
      $downloadlink = $CFG_GLPI["root_doc"] . "/front/document.send.php?docid=" . $doc->fields['id'];

//      $icon = $this->pModel->getIconSrcFromExtension($this->pModel->getFileExtension($doc->fields['filename']));

      $this->showHeaderJS();

      echo "var tbl = document.getElementById('tbl_list_pdf_contract');\n";
      if ($addHeaderList) {
         echo "var rowTH = tbl.insertRow(-1);";
         echo "rowTH.setAttribute('class','tab_bg_1');\n";

         echo "var tmpTH = document.createElement('th');";
         echo "tmpTH.setAttribute('colspan','6');\n";
         echo "tmpTH.setAttribute('class','center');\n";
         echo "tmpTH.innerHTML=\"";
         echo __("Document list", "manageentities");
         echo "\";";
         echo "rowTH.appendChild(tmpTH);";

         echo "rowTH = tbl.insertRow(-1);";
         echo "rowTH.setAttribute('class','tab_bg_1');\n";
         $columns = ['name'      => __('Name'),
                     'entity'    => __('Entity'),
                     'filename'  => __('File'),
                     'headings'  => __('Heading'),
                     'mime'      => __('MIME type'),
                     'assocdate' => __('Date')];
         $i       = 0;
         foreach ($columns as $key => $val) {
            echo "tmpTH = rowTH.insertCell(" . $i . ");\n";
            echo "tmpTH.setAttribute('class','center');\n";
            echo "tmpTH.setAttribute('style','font-weight:bold;width:16%;');\n";

            echo "tmpTH.innerHTML=\"";
            echo $val;
            echo "\";\n";
            $i++;
         }
      }

      echo "row=tbl.insertRow(-1);\n";
      echo "row.setAttribute('class','tab_bg_1');\n";

      // Nom
      echo "var tmpCell=row.insertCell(0);\n";
      echo "tmpCell.setAttribute('class','center');\n";
      echo "var tmpHref = document.createElement('a');";
      echo "tmpHref.innerHTML=\"";
      echo $doc->fields['name'];
      echo "\";\n";
      echo "tmpHref.href=\"";
      echo $link;
      echo "\";\n";
      echo "tmpCell.appendChild(tmpHref);";

      // Entite
      echo "tmpCell=row.insertCell(1);";
      echo "tmpCell.setAttribute('class','center');\n";
      echo "tmpCell.innerHTML=\"";
      echo $entity->fields['completename'];
      echo "\";";

      // Fichier
      echo "tmpCell=row.insertCell(2);\n";
      echo "tmpCell.setAttribute('class','left');\n";
      echo "tmpCell.setAttribute('style','padding-left:12px;');\n";

//      echo "tmpImg = document.createElement('img');";
//      echo "tmpImg.src=\"" . $CFG_GLPI["typedoc_icon_dir"] . "/" . $icon . "\";";
//      echo "tmpCell.appendChild(tmpImg);";


      echo "tmpHref = document.createElement('a');";
      echo "tmpHref.setAttribute('style','padding-left:5px;');\n";
      echo "tmpHref.innerHTML=\"";
      echo $doc->fields['filename'];
      echo "\";\n";
      echo "tmpHref.href=\"";
      echo $downloadlink;
      echo "\";\n";
      echo "tmpHref.target=\"_blank\"; ";
      echo "tmpCell.appendChild(tmpHref);";

      // Rubrique
      echo "tmpCell=row.insertCell(3);";
      echo "tmpCell.setAttribute('class','center');\n";
      echo "tmpCell.innerHTML=\"";
      echo $docCategory->fields['name'];
      echo "\";";

      // Type MIME
      echo "tmpCell=row.insertCell(4);";
      echo "tmpCell.setAttribute('class','center');\n";
      echo "tmpCell.innerHTML=\"";
      echo $doc->fields['mime'];
      echo "\";";

      // Date
      echo "tmpCell=row.insertCell(5);";
      echo "tmpCell.setAttribute('class','center');\n";
      echo "tmpCell.innerHTML=\"";
      echo Html::convDateTime($docItem->fields['date_mod']);
      echo "\";";

      $this->closeFormJS();
   }

   /**
    * @param $object
    * @param $text
    * @param $fakeId
    *
    * @return string
    */
   private function showImgSaved($object, $text, $fakeId) {
      if (isset($object->fields['id']) && $object->fields['id'] > 0) {
         return "&nbsp;&nbsp;<i class='fas fa-save' id='img_" . $object->getType() . ($fakeId) . "' title='" . $text . "'></i>";
      } else {
         return "";
      }
   }

   /**
    * @param      $nbContact
    * @param      $strTitleTab
    * @param      $tabId
    * @param      $object
    * @param      $text
    * @param bool $addImg
    */
   public function updateTabTitle($nbContact, $strTitleTab, $tabId, $object, $text, $addImg = false) {
      $this->showHeaderJS();
      echo "
            $('$tabId ul:first li:eq(" . ($nbContact - 1) . ") a').html(\"" . $strTitleTab . "&nbsp;&nbsp;" . $this->showImgSaved($object, $text, $nbContact) . "\");
         ";

      $this->closeFormJS();
   }

   /**
    * @param $price
    * @param $post
    */
   public function updateCriPriceFromType($price, $post) {

      if ($price == null && $post == null) {
         // Reinit view
         $this->showHeaderJS();
         echo "document.getElementById('viewcriprice_" . $_POST['fakeid_new_intervention'] . "').innerHTML = '';";
         $this->closeFormJS();

         $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

         if ($this->pModel->getNbCriPrice() == 0) {
            $this->showHeaderJS();
            echo "document.getElementById('contentListCriPrices" . $_POST['fakeid_new_intervention'] . "').innerHTML = '';";
            echo "document.getElementById('tablelistcriprices" . $post['fakeid_new_intervention'] . "').style.visibility='hidden';";
            $this->closeFormJS();
            $this->updateCPriceID(0);
         }
      } else {
         if ($post['new_criprice_critype'] <= 0) {
            $this->showHeaderJS();
            echo "$(" . $post['inputfield'] . ").val('');";
            $this->updateCPriceID(0);
            $this->closeFormJS();
         } else {
            $this->showHeaderJS();
            if (null == $price) {
               echo "$(" . $post['inputfield'] . ").val('');";
               echo "document.getElementById('id_criprice').value = 0;";
            } else {
               echo "$(" . $post['inputfield'] . ").val('" . $price->fields['price'] . "');";
               if ($price->fields['id'] > 0) {
                  echo "document.getElementById('id_criprice').value = " . $price->fields['id'] . ";";
               }
            }
            $this->closeFormJS();

            if (null == $price) {
               $this->changeBtnName("btn_add_cprice_" . $post['fakeid_new_intervention'], __("Add this price", "manageentities"));
               $this->changeElementVisibility("btn_delete_cprice_" . $post['fakeid_new_intervention'], false);
            } else {
               $this->changeBtnName("btn_add_cprice_" . $post['fakeid_new_intervention'], __("Update this price", "manageentities"));
               $this->changeElementVisibility("btn_delete_cprice_" . $post['fakeid_new_intervention'], true);
            }
         }
      }
   }

   /**
    * @param bool $toEdit
    * @param      $imgId
    * @param      $text
    */
   public function updateImgTabTitle($toEdit = false, $imgId, $text) {
      if ($toEdit) {
         echo "if ($($imgId)){";
         echo "$($imgId).attr('src', '../pics/asterisk.png');";
         echo "$($imgId).attr('title', '" . str_ireplace("'", "&quot;", $text) . "');";
         echo "}";
      } else {
         $this->showHeaderJS();
         echo "if ($($imgId)){";
         echo "$($imgId).attr('src', '../pics/database_save.png');";
         echo "$($imgId).attr('title', '" . str_ireplace("'", "&quot;", $text) . "');";
         echo "}";
         $this->closeFormJS();
      }
   }

   /**
    * @param $cprice
    * @param $inputId
    */
   public function updateCriPrice($cprice, $inputId) {
      $this->showHeaderJS();
      if (null != $cprice && $cprice != false) {
         echo " if ($('#" . $inputId . "')){
                  $('#" . $inputId . "').prop('disabled', true);
                  $('#" . $inputId . "').val(" . $cprice->fields['price'] . ");
                }";
      } else {
         echo " if ($('#" . $inputId . "')){
                  $('#" . $inputId . "').prop('disabled', false);
                  $('#" . $inputId . "').val('');
                }";
      }
      $this->closeFormJS();
   }

   private function showBtnRAZ() {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

      echo "<form name='form_raz' id='form_raz' method='post' action='" . $this->pModel->getUrl() . "' >";
      echo "<input type='hidden' name='action' id='action_raz' value='" . Action::REINIT_FORMS . "' />";
      echo "<div class='center' >";
      echo "<input type='submit' class='submit' name='btnAddAll' id='btnAddAll' ";

      echo "value='" . __("Reinitialize forms", 'manageentities') . "' />";
      echo "</div>";
      Html::closeForm();
   }

   /**
    * @return array
    */
   private function showBtnAddAll() {
      echo "<table class='tab_cadre_fixe' >";
      echo "<tr class='tab_bg_2'>";
      echo "<td colspan='4' class='center'>";
      echo "<input type='submit' class='submit' name='btnAddAll' id='btnAddAll' ";

      if ($this->isWizardAlreadySaveInDB()) {
         echo "value='" . __("Update all previous elements", "manageentities") . "' ";
         echo "onclick='javascript:confirmUpdateAllElements();'/>";
      } else {
         echo "value='" . __("Add all previous elements", "manageentities") . "' ";
         echo "onclick='javascript:confirmAddAllElements();'/>";
      }

      echo "</td>";
      echo "</tr>";
      echo "</table>";

      $idDivAjax = "tabaddallajax";

      echo "<div id='" . $idDivAjax . "' style='text-align:center;'></div>";

      // Entity
      $listId = [];

      $params = ['action'      => Action::ADD_ALL_ELEMENT,
                 'id_div_ajax' => $idDivAjax
      ];

      $allContent = ['idDivAjax' => $idDivAjax,
                     'listIds'   => $listId,
                     'params'    => $params
      ];

      return $allContent;
   }

   /**
    * @param        $message
    * @param        $messageType
    * @param int    $with
    * @param int    $height
    * @param string $htmlInput
    */
   public function showMessage($message, $messageType, $with = -1, $height = -1, $htmlInput = '') {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();;
      $srcImg     = "";
      $alertTitle = "";
      switch ($messageType) {
         case Messages::MESSAGE_ERROR:
            $srcImg     = "fas fa-exclamation-triangle";
            $color      = "orange";
            $alertTitle = $this->pModel->getMessage("message_error");
            break;
         case Messages::MESSAGE_INFO:
         default:
            $srcImg     = "fas fa-info-circle";
            $color      = "forestgreen";
            $alertTitle = $this->pModel->getMessage("message_info");
            break;
      }

      $this->showHeaderJS();
      echo " if ($('#alert-message').val()){
               $('#alert-message').val('');
            }";
      $this->closeFormJS();

      echo "<div id='alert-message' class='tab_cadre_navigation_center' style='display:none;'>" . $message . "</div>";

      $this->showHeaderJS();
      echo "var mTitle =  \"<i class='" . $srcImg . " fa-1x' style='color:" . $color . "'></i>&nbsp;" . $alertTitle . " \";";
      echo "$( '#alert-message' ).dialog({
        autoOpen: false,
        height: " . ($height > 0 ? $height : 150) . ",
        width: " . ($with > 0 ? $with : 300) . ",
        modal: true,
        open: function (){
         $(this)
            .parent()
            .children('.ui-dialog-titlebar')
            .html(mTitle);
      },
        buttons: {
         'ok': function() {
            $( this ).dialog( 'close' );
         }
      },
      beforeClose: function(event) {
            $('#alert-message').remove();";

      if ($htmlInput != "") {
         $this->bordersOnError($htmlInput, true, true);
      }

      echo "      return false;
      }
    });
    
    $( '#alert-message' ).dialog( 'open' );";


      $this->closeFormJS();
   }

   /**
    * @param $title
    * @param $text
    */
   public function showDialog($title, $text) {

      echo "<table id='custom-dialog' class='tab_cadre_navigation_center' style='display:none;' >";
      echo "<tr>";
      echo "<td id='content-message'>";
      echo $text;
      echo "</td>";
      echo "</tr>";
      echo "</table>";


      $this->showHeaderJS();
      echo "showDialog(\"" . $title . "\", \"" . __("OK") . "\",\"" . $text . "\");";
      $this->closeFormJS();
   }

   /**
    * @param $idDivAjax
    * @param $function
    * @param $text
    */
   public function showAlertsJQ($idDivAjax, $function, $text) {

      echo "<table id='alert-create-entity' class='tab_cadre_navigation_center' style='display:none;' >";
      echo "<tr>";
      echo "<td id='content-message'>";
      echo $text;
      echo "</td>";
      echo "</tr>";

      echo "</table>";


      $this->showHeaderJS();
      echo "alertCreateEntity('" . __("Yes") . "','" . __("No") . "','" . __("Warning") . "','" . $function . "');";
      $this->closeFormJS();
   }

   public function updateDropdownsContactManager() {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

      $this->showHeaderJS();
      if ($this->pModel->getNbContact() > 0) {
         for ($i = 1; $i <= $this->pModel->getNbContact(); $i++) {
            if ($i != $this->pModel->getContactManager()) {
               echo "document.getElementById('dropdown_is_manager_yn" . $i . "').value=0;";
            }
         }
      }
      $this->closeFormJS();
   }

   /**
    * @param      $tableId
    * @param      $newRowId
    * @param      $cells
    * @param null $onclick
    */
   public function addRowOnTable($tableId, $newRowId, $cells, $onclick = null) {
      $this->showHeaderJS();
      echo "var tbl = document.getElementById('" . $tableId . "');\n";

      echo "   row=tbl.insertRow(-1);\n";
      echo "   row.setAttribute('class','tab_bg_2');\n";
      echo "   row.id='" . $newRowId . "';";

      if (null != $onclick) {
         echo "   row.setAttribute('onclick','" . $onclick . "');";
         echo "   row.setAttribute('style','cursor:pointer');";
      }

      echo "var tmpCell=null;";


      //      "entity" => Dropdown::getDropdownName("glpi_entities", $criPrice->fields['entities_id']),
      //               "cprice" => Html::formatNumber($criPrice->fields['price'], true),
      //               "critype" => $criTypeName,
      //               "is_default" => $isDefault

      echo "   tmpCell=row.insertCell(0);\n";
      echo "   tmpCell.innerHTML=\"" . $cells['entity'] . "\";";

      echo "   tmpCell=row.insertCell(1);\n";
      echo "   tmpCell.innerHTML=\"" . $cells['critype'] . "\";";
      echo "   tmpCell.id=\"critypes_" . $cells['id'] . "\";";

      echo "   tmpCell=row.insertCell(2);\n";
      echo "   tmpCell.innerHTML=\"" . $cells['cprice'] . "\";";
      echo "   tmpCell.id=\"price_" . $cells['id'] . "\";";

      echo "   tmpCell=row.insertCell(3);\n";
      echo "   tmpCell.innerHTML=\"" . $cells['is_default'] . "\";";
      echo "   tmpCell.id=\"is_default_" . $cells['id'] . "\";";


      $this->closeFormJS();
   }

   /**
    * @param $cpriceId
    */
   public function updateCPriceID($cpriceId) {
      $this->showHeaderJS();
      echo "
         document.getElementById('id_criprice').value=" . $cpriceId . ";
      ;\n";
      $this->closeFormJS();
   }

   /**
    * @param $row
    */
   public function deleteRowOnTable($row) {
      $this->showHeaderJS();
      echo "var row = document.getElementById('" . $row . "');";
      echo "row.parentNode.removeChild(row);";

      $this->closeFormJS();
      //      $cd = $dbu->getAllDataFromTable($this->getTable(),$condition);
      //      if (sizeof($cd) == 0){
      //         echo "if(document.getElementById('empty_skateholders".$idToUse."') != null){";
      //         echo "   tbl.deleteRow(-1);";
      //         echo "}else{";
      //         echo "   row=tbl.insertRow(-1);\n";
      //         echo "   row=tbl.insertRow(-1);\n";
      //         echo "   row.setAttribute('class','tab_bg_1');\n";
      //         echo "   row.id='empty_skateholders".$idToUse."';";
      //         echo "   var tmpCell=row.insertCell(0);\n";
      //         echo "   tmpCell.innerHTML=\"".__("No skateholders have been affected yet.","manageentities")."\";";
      //         echo "}";
      //      }
   }

   /**
    * @param $cellId
    * @param $value
    */
   public function updateCellOnTable($cellId, $value) {
      $this->showHeaderJS();
      echo "   document.getElementById('" . $cellId . "').innerHTML =\"" . $value . "\";\n";
      $this->closeFormJS();
   }

   /**
    * @param int $nbContact
    * @param     $idDivAjax
    * @param     $tabId
    * @param     $type
    */
   public function updateTabs($nbContact = 0, $idDivAjax, $tabId, $type) {

      $this->showHeaderJS();
      echo "var num_tabs = $('$tabId ul li').length + 1;";

      echo "$('$tabId ul').append(
               \"<li><a href='#tabs-" . $nbContact . "' >";

      switch ($type) {
         case ElementType::CONTACT:
            echo __("New contact", "manageentities");
            break;

         case ElementType::INTERVENTION:
            echo __("New intervention", "manageentities");
            break;
      }

      echo "</a></li>\"
            );";

      echo "$('$tabId').tabs('option', 'active', " . $nbContact . ");";
      echo "$('$tabId').tabs('refresh');";

      echo "$('#" . $idDivAjax . "').html('');";

      $this->closeFormJS();
   }

   /**
    * @param $div
    * @param $id
    */
   public function selectTab($div, $id) {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();
      $this->showHeaderJS();

      echo "
           $('#" . $div . "').tabs('option', 'active', " . ($id - 1) . " );
           
       ";
      $this->closeFormJS();
   }

   public function selectTabNewContact() {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();
      $this->showHeaderJS();

      echo "
           $('#mytabscontacts').tabs('option', 'active', " . $this->pModel->getNbContact() . " );
           
       ";
      $this->closeFormJS();
   }

   // ----------------------------------------------------------------------------------------------------------------------------------
   //   Utilities
   // ----------------------------------------------------------------------------------------------------------------------------------

   /**
    * @return bool
    */
   private function isWizardAlreadySaveInDB() {
      $this->pModel = PluginManageentitiesAddElementsModel::getInstance();

      return ((isset($this->pModel->getEntity()->fields['id']) && $this->pModel->getEntity()->fields['id'] > 0) ||
              (isset($this->pModel->getContacts(1)->fields['id']) && $this->pModel->getContacts(1)->fields['id'] > 0) ||
              (isset($this->pModel->getContract()->fields['id']) && $this->pModel->getContract()->fields['id'] > 0) ||
              (isset($this->pModel->getContractDay(1)->fields['id']) && $this->pModel->getContractDay(1)->fields['id'] > 0));
   }

   /**
    * @param $formIds
    */
   private function activateForms($formIds) {
      $this->showHeaderJS();

      echo "$(document).ready(function() {
         $('#mytabsaddelement').tabs();";

      if ($formIds['contacts'] == 1) {
         echo "$('#mytabscontacts').tabs({
               collapsible: true
            });";
      } else {
         echo "$('#mytabscontacts').tabs({
               collapsible: true,
               active : false
            });";
      }

      echo "$('#mytabsentity').tabs({
               collapsible: true
            });";


      if ($formIds['contract'] == 1) {
         echo "$('#mytabscontract').tabs({
               collapsible: true
            });";
      } else {
         echo "$('#mytabscontract').tabs({
               collapsible: true,
               active : false
            });";
      }


      if ($formIds['interventions'] == 1) {
         echo "$('#mytabsinterventions').tabs({
               collapsible: true
            });";
      } else {
         echo "$('#mytabsinterventions').tabs({
               collapsible: true,
               active : false
            });";
      }

      echo "});";

      $this->closeFormJS();
   }

   /**
    * @param $typeField
    * @param $pModel
    *
    * @return bool
    */
   public function checkFields($typeField, $pModel) {
      switch ($typeField) {
         case ElementType::ENTITY:
            if (isset($_POST['new_entity_name']) && $_POST['new_entity_name'] != "") {
               $this->bordersOnError("entity_name", false, false);
               return true;
            } else {
               $this->showMessage($pModel->getMessage(ElementType::ENTITY, Errors::ERROR_FIELDS), Messages::MESSAGE_ERROR, -1, -1, "entity_name");
               $pModel->addError(Errors::ERROR_ENTITY, Errors::ERROR_ADD, 'new_entity_name');
            }
            break;
         case ElementType::CONTACT:
            if (isset($_POST['new_contact_name']) && $_POST['new_contact_name'] != "") {
               $this->bordersOnError("contact_name" . $_POST['fakeid_new_contact'], false, false);
               return true;
            } else {
               $this->showMessage($pModel->getMessage(ElementType::CONTACT, Errors::ERROR_FIELDS), Messages::MESSAGE_ERROR, -1, -1, "contact_name" . $_POST['fakeid_new_contact']);
               $pModel->addError(Errors::ERROR_CONTACT, Errors::ERROR_ADD, 'true');
            }
            break;
         case ElementType::CONTRACT:
            if (isset($_POST['new_contract_name']) && $_POST['new_contract_name'] != "") {
               $this->bordersOnError("contract_name", false, false);
               return true;
            } else {
               $this->showMessage($pModel->getMessage(ElementType::CONTRACT, Errors::ERROR_FIELDS), Messages::MESSAGE_ERROR, -1, -1, "contract_name");
               $pModel->addError(Errors::ERROR_CONTRACT, Errors::ERROR_ADD, 'true');
            }
            break;
         case ElementType::INTERVENTION:
            // contract name
            if (isset($_POST['new_intervention_name']) && $_POST['new_intervention_name'] != "") {
               $this->bordersOnError("intervention_name" . $_POST['fakeid_new_intervention'], false, false);
               // contract type
               if (isset($_POST['contract_type']) && $_POST['contract_type'] != "0") {
                  $this->bordersOnError("div_select_contract_type" . $_POST['fakeid_new_intervention'], false, false);
                  // begin date
                  if (isset($_POST['new_intervention_begin_date']) && $_POST['new_intervention_begin_date'] != "") {
                     $this->bordersOnError("intervention_begin_date" . $_POST['fakeid_new_intervention'], false, false);


                     // contract statut
                     if (isset($_POST['new_intervention_contractstate_id']) && $_POST['new_intervention_contractstate_id'] > 0) {
                        $this->bordersOnError("div_select_contractstate" . $_POST['fakeid_new_intervention'], false, false);
                        // default intervention type
                        if ((isset($_POST['new_intervention_contract_id']) && $_POST['new_intervention_contract_id'] > 0) ||
                            (isset($_POST['previous_contract_for_intervention']) && $_POST['previous_contract_for_intervention'] == "true")) {
                           $this->bordersOnError("cell_select_contract_for_intervention" . $_POST['fakeid_new_intervention'], false, false);
                           return true;
                        } else {
                           $this->showMessage($pModel->getMessage(ElementType::INTERVENTION, Errors::ERROR_FIELDS), Messages::MESSAGE_ERROR, -1, -1, "cell_select_contract_for_intervention" . $_POST['fakeid_new_intervention']);
                           $pModel->addError(Errors::ERROR_INTERVENTION, Errors::ERROR_ADD, 'true', $_POST['fakeid_new_intervention']);

                        }
                     } else {
                        $this->showMessage($pModel->getMessage(ElementType::INTERVENTION, Errors::ERROR_FIELDS), Messages::MESSAGE_ERROR, -1, -1, "div_select_contractstate" . $_POST['fakeid_new_intervention']);
                        $pModel->addError(Errors::ERROR_INTERVENTION, Errors::ERROR_ADD, 'true', $_POST['fakeid_new_intervention']);

                     }
                  } else {
                     $this->showMessage($pModel->getMessage(ElementType::INTERVENTION, Errors::ERROR_FIELDS), Messages::MESSAGE_ERROR, -1, -1, "intervention_begin_date" . $_POST['fakeid_new_intervention']);
                     $pModel->addError(Errors::ERROR_INTERVENTION, Errors::ERROR_ADD, 'true', $_POST['fakeid_new_intervention']);

                  }
               } else {
                  $this->showMessage($pModel->getMessage(ElementType::INTERVENTION, Errors::ERROR_FIELDS), Messages::MESSAGE_ERROR, -1, -1, "div_select_contract_type" . $_POST['fakeid_new_intervention']);
                  $pModel->addError(Errors::ERROR_INTERVENTION, Errors::ERROR_ADD, 'true', $_POST['fakeid_new_intervention']);

               }
            } else {
               $this->showMessage($pModel->getMessage(ElementType::INTERVENTION, Errors::ERROR_FIELDS), Messages::MESSAGE_ERROR, -1, -1, "intervention_name" . $_POST['fakeid_new_intervention']);
               $pModel->addError(Errors::ERROR_INTERVENTION, Errors::ERROR_ADD, 'true', $_POST['fakeid_new_intervention']);

            }
            break;
         case ElementType::CONTRACT_MANAGEMENT_TYPE:
            return true;
            break;
         case ElementType::CRIPRICE:
            if (isset($_POST['new_criprice_pricefield']) && $_POST['new_criprice_pricefield'] != "") {
               $this->bordersOnError("textfield_price" . $_POST['fakeid_new_intervention'], false, false);
               if (isset($_POST['new_criprice_critypes']) && $_POST['new_criprice_critypes'] > 0) {
                  $this->bordersOnError("dropdown_plugin_manageentities_critypes_id" . $_POST['fakeid_new_intervention'], false, false);
                  return true;
               } else {
                  $this->showMessage($pModel->getMessage(ElementType::CONTACT, Errors::ERROR_FIELDS), Messages::MESSAGE_ERROR, -1, -1, "dropdown_plugin_manageentities_critypes_id" . $_POST['fakeid_new_intervention']);
                  $pModel->addError(Errors::ERROR_CRIPRICE, Errors::ERROR_ADD, 'true');
               }
               break;
            } else {
               $this->showMessage($pModel->getMessage(ElementType::CONTACT, Errors::ERROR_FIELDS), Messages::MESSAGE_ERROR, -1, -1, "textfield_price" . $_POST['fakeid_new_intervention']);
               $pModel->addError(Errors::ERROR_CRIPRICE, Errors::ERROR_ADD, 'true');
            }
            break;
         default:
            break;
      }
      return false;
   }

   /**
    * @param $content
    */
   public function showResults($content) {
      $pModel = PluginManageentitiesAddElementsModel::getInstance();
      switch ($content['result']) {
         case Status::UPDATED:
         case Status::DELETED:
         case Status::ADDED:
            $this->showMessage($content['message'], Messages::MESSAGE_INFO);
            break;

         case Status::NOT_UPDATED:
         case Status::NOT_DELETED:
         case Errors::ERROR_FIELDS:
         case Status::NOT_ADDED:
            $this->showMessage($content['message'], Messages::MESSAGE_ERROR);
            break;

         case Action::ADD_ONLY_ENTITY:
            $idToSend = "";
            switch ($content['from']) {
               case Contact::getType():
                  $idToSend = "entity-contact";
                  break;
               case Contract::getType():
                  $idToSend = "entity-contract";
                  break;
               case PluginManageentitiesContractDay::getType():
                  $idToSend = "entity-intervention";
                  break;
               default:
                  ;
                  break;
            }

            $this->showAlertsJQ($_POST['id_div_ajax'], $idToSend, __("The previous entity will be added, continue ?", "manageentities"));
            break;
         case Action::ADD_ONLY_CONTRACT:
            $this->showAlertsJQ($_POST['id_div_ajax'], "intervention-contract", __("The previous contract will be added, continue ?", "manageentities"));
            break;
         case Action::ADD_ENTITY_AND_CONTRACT:
            $this->showAlertsJQ($_POST['id_div_ajax'], "entity-intervention-contract", __("The previous entity and contract will be added, continue ?", "manageentities"));
            break;
         case Action::ADD_ALL_ELEMENT:
            $this->showAlertsJQ($_POST['id_div_ajax'], "add-all-element", __("All previous elements will be added, continue ?", "manageentities"));
            break;
         case Action::UPDATE_ALL_ELEMENT:
            $this->showAlertsJQ($_POST['id_div_ajax'], "update-all-element", __("All previous elements will be updated, continue ?", "manageentities"));
            break;
         case Action::DELETE_CONTRACT_MANAGEMENT_TYPE:
            $this->showAlertsJQ($_POST['id_div_ajax'], "delete-management-type", $pModel->getMessage("irreversible_action"));
            break;
         default:
            break;
      }
   }

   /**
    * La meme que celle du coeur (dans Dropdown) mais qui renvoi le $rand utilise necessaire
    * pour les besoins du plugin.
    *
    * @param $name             string   HTML select name
    * @param $value            integer  HTML select selected value
    * @param $limit_planning            limit planning to the configuration range (default 0)
    *
    * @return Nothing (display)
    * */
   private function showHours($name, $value, $limit_planning = 0, $rnd = -1) {
      global $CFG_GLPI;

      if ($rnd == -1) {
         $rand = mt_rand();
      } else {
         $rand = $rnd;
      }
      $begin = 0;
      $end   = 24;
      $step  = $CFG_GLPI["time_step"];
      // Check if the $step is Ok for the $value field
      $split = explode(":", $value);

      // Valid value XX:YY ou XX:YY:ZZ
      if ((count($split) == 2) || (count($split) == 3)) {
         $min = $split[1];

         // Problem
         if (($min % $step) != 0) {
            // set minimum step
            $step = 5;
         }
      }

      if ($limit_planning) {
         $plan_begin = explode(":", $CFG_GLPI["planning_begin"]);
         $plan_end   = explode(":", $CFG_GLPI["planning_end"]);
         $begin      = (int)$plan_begin[0];
         $end        = (int)$plan_end[0];
      }
      echo "<select name=\"" . $name . $rand . "\" id=\"" . $name . $rand . "\">";

      for ($i = $begin; $i < $end; $i++) {
         if ($i < 10) {
            $tmp = "0" . $i;
         } else {
            $tmp = $i;
         }

         for ($j = 0; $j < 60; $j += $step) {
            if ($j < 10) {
               $val = $tmp . ":0$j";
            } else {
               $val = $tmp . ":$j";
            }

            echo "<option value='$val' " . (($value == $val . ":00") || ($value == $val) ? " selected " : "") .
                 ">$val</option>";
         }
      }
      // Last item
      $val = $end . ":00";
      echo "<option value='$val' " . (($value == $val . ":00") || ($value == $val) ? " selected " : "") .
           ">$val</option>";
      echo "</select>";

      return $rand;
   }

}