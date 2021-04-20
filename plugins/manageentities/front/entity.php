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

$PluginManageentitiesContract        = new PluginManageentitiesContract();
$PluginManageentitiesContact         = new PluginManageentitiesContact();
$PluginManageentitiesEntity          = new PluginManageentitiesEntity();
$PluginManageentitiesBusinessContact = new PluginManageentitiesBusinessContact();

if (!isset($_POST["entities_id"]))
   $_POST["entities_id"] = "";

$plugin = new Plugin();
if (Session::getCurrentInterface() == 'central') {
   Html::header(__('Entities portal', 'manageentities'), '', "management", "pluginmanageentitiesentity");
} else {
   if ($plugin->isActivated('servicecatalog')) {
      PluginServicecatalogMain::showDefaultHeaderHelpdesk(__('Entities portal', 'manageentities'));
      echo Html::css('public/lib/jquery-gantt.css');
   } else {
      Html::helpHeader(__('Entities portal', 'manageentities'));
   }
}

if ($PluginManageentitiesEntity->canView()
    || Session::haveRight("config", UPDATE)) {

   if (isset($_POST["addcontracts"])) {
      if ($PluginManageentitiesEntity->canCreate())
         $PluginManageentitiesContract->add($_POST);
      Html::back();

   } else if (isset($_POST["deletecontracts"])) {
      if ($PluginManageentitiesEntity->canCreate())
         $PluginManageentitiesContract->delete(['id' => $_POST["id"]]);
      Html::back();

   } else if (isset($_POST["contractbydefault"])) {
      if ($PluginManageentitiesEntity->canCreate())
         $PluginManageentitiesContract->addContractByDefault($_POST["myid"], $_POST["entities_id"]);
      Html::back();

   } else if (isset($_POST["addcontacts"])) {
      if ($PluginManageentitiesEntity->canCreate())
         $PluginManageentitiesContact->add($_POST);
      Html::back();

   } else if (isset($_POST["deletecontacts"])) {
      if ($PluginManageentitiesEntity->canCreate())
         $PluginManageentitiesContact->delete(['id' => $_POST["id"]]);
      Html::back();

   } else if (isset($_POST["addbusiness"])) {
      if ($PluginManageentitiesEntity->canCreate())
         $PluginManageentitiesBusinessContact->add($_POST);
      Html::back();

   } else if (isset($_POST["deletebusiness"])) {
      if ($PluginManageentitiesEntity->canCreate())
         $PluginManageentitiesBusinessContact->delete(['id' => $_POST["id"]]);
      Html::back();

   } else if (isset($_POST["contactbydefault"])) {
      if ($PluginManageentitiesEntity->canCreate())
         $PluginManageentitiesContact->addContactByDefault($_POST["contacts_id"], $_POST["entities_id"]);
      Html::back();

   } else {
      // Manage entity change
      if (isset($_GET["active_entity"])) {
         if (!isset($_GET["is_recursive"])) {
            $_GET["is_recursive"] = 0;
         }
         Session::changeActiveEntities($_GET["active_entity"], $_GET["is_recursive"]);
         if ($_GET["active_entity"] == $_SESSION["glpiactive_entity"]) {
            Html::redirect(preg_replace("/entities_id.*/", "", $CFG_GLPI["root_doc"] . "/plugins/manageentities/front/entity.php"));
         }

      } else if (isset($_POST["choice_entity"]) && $_POST["entities_id"] != 0) {
         Html::redirect($CFG_GLPI["root_doc"] . "/plugins/manageentities/front/entity.php?active_entity=" . $_POST["entities_id"] . "");

      } else {
         if (Session::getCurrentInterface() == 'central') {
            $dateYear = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y") - 1));
         } else {
            $dateYear = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y") - 10));
         }
         $lastday = cal_days_in_month(CAL_GREGORIAN, date("m"), date("Y"));

         if (date("d") == $lastday) {
            $dateMonthend   = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y")));
            $dateMonthbegin = date("Y-m-d", mktime(0, 0, 0, date("m"), 1, date("Y")));
         } else {
            $month   = date("m");
            $lastday = $month == 1 ? 31 : cal_days_in_month(CAL_GREGORIAN, $month - 1, date("Y"));
            //$lastday = cal_days_in_month(CAL_GREGORIAN, date("m") - 1, date("Y"));
            $dateMonthend   = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, $lastday, date("Y")));
            $dateMonthbegin = date("Y-m-d", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
         }
         $options = ["begin_date_after"  => isset($_POST['begin_date_after']) ? $_POST['begin_date_after'] : $dateYear,
                     "begin_date_before" => isset($_POST['begin_date_before']) ? $_POST['begin_date_before'] : "NULL",
                     "begin_date"        => isset($_POST['begin_date']) ? $_POST['begin_date'] : $dateMonthbegin,
                     "end_date"          => isset($_POST['end_date']) ? $_POST['end_date'] : $dateMonthend,
                     "end_date_after"    => isset($_POST['end_date_after']) ? $_POST['end_date_after'] : "NULL",
                     "end_date_before"   => isset($_POST['end_date_before']) ? $_POST['end_date_before'] : "NULL",
                     "contract_states"   => isset($_POST['contract_states']) ? $_POST['contract_states'] : 0,
                     "entities_id"       => (isset($_POST['entities_id']) && (!empty($_POST['entities_id']))) ? $_POST['entities_id'] : -1,
                     "business_id"       => isset($_POST['business_id']) ? $_POST['business_id'] : 0,
                     "company_id"        => isset($_POST['company_id']) ? $_POST['company_id'] : 0,
                     "year_current"      => isset($_POST['year_current']) ? $_POST['year_current'] : 0];
         Html::requireJs('gantt');
         $entity = new PluginManageentitiesEntity();
         $entity->display($options);
      }
   }

} else {
   Html::displayRightError();
}

if (Session::getCurrentInterface() != 'central'
    && $plugin->isActivated('servicecatalog')) {

   PluginServicecatalogMain::showNavBarFooter('manageentities');
}

if (Session::getCurrentInterface() == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
