<?php
/*
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2003-2012 by the Manageentities Development Team.

 https://forge.indepnet.net/projects/manageentities
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
// */

define('GLPI_ROOT', '../../..');
include(GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

$entity                        = new Entity();
$PluginManageentitiesEntity    = new PluginManageentitiesEntity();
$PluginManageentitiesContact   = new PluginManageentitiesContact();
$PluginManageentitiesContract  = new PluginManageentitiesContract();
$PluginManageentitiesCri       = new PluginManageentitiesCri();
$PluginManageentitiesCriDetail = new PluginManageentitiesCriDetail();
$followUp                      = new PluginManageentitiesFollowUp();

if (!isset($_POST['plugin_manageentities_tab']))
   $_POST['plugin_manageentities_tab'] = $_SESSION['glpi_plugin_manageentities_tab'];

switch ($_POST['plugin_manageentities_tab']) {
   case "follow-up" :
      $_SESSION['glpi_plugin_manageentities_tab'] = "follow-up";
      $followUp->showCriteriasForm($_POST);
      $followUp->showFollowUp($_SESSION["glpiactive_entity"], $_POST);
      break;
   case "description" :
      $_SESSION['glpi_plugin_manageentities_tab'] = "description";
      $PluginManageentitiesEntity->showDescription($_SESSION["glpiactive_entity"]);
      $PluginManageentitiesContact->showContacts($_SESSION["glpiactive_entity"]);
      break;
   case "tickets" :
      $_SESSION['glpi_plugin_manageentities_tab'] = "tickets";
      $PluginManageentitiesEntity->showTickets($_SESSION["glpiactive_entity"]);
      break;
   case "reports":
      $_SESSION['glpi_plugin_manageentities_tab'] = "reports";
      $PluginManageentitiesCriDetail->showReports(0, 0, $_SESSION["glpiactive_entity"]);
      break;
   case "documents":
      $_SESSION['glpi_plugin_manageentities_tab'] = "documents";
      if (Session::haveRight("Document", READ) && $entity->can($_SESSION["glpiactive_entity"], READ)) {
         Document::showAssociated($entity);
      }
      break;
   case "contract":
      $_SESSION['glpi_plugin_manageentities_tab'] = "contract";
      if (Session::haveRight("Contract", READ)) {
         $PluginManageentitiesContract->showContracts($_SESSION["glpiactive_entity"]);
      }
      break;
   case "accounts":
      $_SESSION['glpi_plugin_manageentities_tab'] = "accounts";
      $PluginAccountsAccount_Item                 = new PluginAccountsAccount_Item;
      $PluginAccountsAccount_Item->showPluginFromItems('Entity', $_SESSION["glpiactive_entity"], "");
      break;
   case "all":
      $_SESSION['glpi_plugin_manageentities_tab'] = "all";
      $PluginManageentitiesEntity->showDescription($_SESSION["glpiactive_entity"]);
      $PluginManageentitiesContact->showContacts($_SESSION["glpiactive_entity"]);
      $PluginManageentitiesEntity->showTickets($_SESSION["glpiactive_entity"]);
      if ($PluginManageentitiesCri->canView())
         $PluginManageentitiesCriDetail->showReports(0, 0, $_SESSION["glpiactive_entity"]);
      if (Session::haveRight("Document", READ) && $entity->can($_SESSION["glpiactive_entity"], READ)) {
         Document::showAssociated($entity);
      }
      if (Session::haveRight("Contract", READ)) {
         $PluginManageentitiesContract->showContracts($_SESSION["glpiactive_entity"]);
      }

      break;
   default :
      break;
}

Html::ajaxFooter();

?>