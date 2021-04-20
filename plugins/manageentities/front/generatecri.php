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

$plugin = new Plugin();
if (Session::getCurrentInterface() == 'central') {
   Html::header(__('Entities portal', 'manageentities'), '', "helpdesk", "pluginmanageentitiesgeneratecri");
} else {
   if ($plugin->isActivated('servicecatalog')) {
      PluginServicecatalogMain::showDefaultHeaderHelpdesk(__('Entities portal', 'manageentities'));
      echo "<br>";
   } else {
      Html::helpHeader(__('Entities portal', 'manageentities'));
   }
}
if (Session::haveRight("ticket", CREATE)) {
   $generatecri = new PluginManageentitiesGenerateCRI();
   $generatecri->showWizard($ticket = new Ticket(), $_SESSION['glpiactive_entity']);
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
