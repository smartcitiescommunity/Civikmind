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

include ('../../../inc/includes.php');

$mantis = new PluginMantisMantis();

if (isset($_GET['action']) && $_GET['action'] == 'linkToIssue') {

   Html::popHeader('Mantis', $_SERVER['PHP_SELF']);
   $mantis->getFormForLinkGlpiTicketToMantisTicket($_GET['idTicket'], $_GET['itemType']);
   Html::popFooter();
} else if (isset($_GET['action']) && $_GET['action'] == 'linkToProject') {

   Html::popHeader('Mantis', $_SERVER['PHP_SELF']);
   $mantis->getFormForLinkGlpiTicketToMantisProject($_GET['idTicket'], $_GET['itemType']);
   Html::popFooter();
} else if (isset($_GET['action']) && $_GET['action'] == 'deleteIssue') {
   Html::popHeader('Mantis', $_SERVER['PHP_SELF']);

   $id_link = $_GET['id'];
   $id_ticket = $_GET['idTicket'];
   $id_mantis = $_GET['idMantis'];

   $mantis->getFormToDelLinkOrIssue($id_link, $id_ticket, $id_mantis, $_GET['itemType']);
   Html::popFooter();
}
