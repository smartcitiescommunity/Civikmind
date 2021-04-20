<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Html::header_nocache();
Session::checkLoginUser();
header("Content-Type: text/html; charset=UTF-8");

$config = PluginMetademandsConfig::getInstance();
if (isset($_POST['action'])) {
   $options = ['root_doc' => $CFG_GLPI['root_doc']];

   switch ($_POST['action']) {
      case "load" :
         if (Session::getCurrentInterface()
               && Session::getCurrentInterface() == "central"
               && strpos($_SERVER['HTTP_REFERER'], "ticket.form.php") !== false
               && strpos($_SERVER['HTTP_REFERER'], 'id=') !== false) {
//            $options['lang']['create_link'] = __('Create a linked ticket', 'metademands');
//            echo "<script type='text/javascript'>";
//            echo "var metademandTicketLink = $(document).metademandTicketLink(".json_encode($options).");";
//            echo "metademandTicketLink.metademand_ticketlink()";
//            echo "</script>";
         }

         if (Session::getCurrentInterface()
               && (strpos($_SERVER['HTTP_REFERER'], "ticket.form.php") !== false
                     || strpos($_SERVER['HTTP_REFERER'], "helpdesk.public.php?create_ticket=1") !== false
                     || strpos($_SERVER['HTTP_REFERER'], "tracking.injector.php") !== false)) {
            $options['config'] = $config;
            $options['lang']   = ['category' => __('Category'),
                                       'source' => _n('Request source', 'Request sources', 1),
                                       'approval' => _n('Approval', 'Approvals', 1),
                                       'location' => _n('Location', 'Locations', 1),
                                       'element' => _n('Associated element', 'Associated elements', 2)];
            echo "<script type='text/javascript'>";
            echo "var metademandAdditionalFields = $(document).metademandAdditionalFields(".json_encode($options).");";
            echo "metademandAdditionalFields.metademand_getAdditionalFields()";
            echo "</script>";
         }
         break;
   }
}