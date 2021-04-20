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
$AJAX_INCLUDE = 1;

// Send UTF8 Headers
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_REQUEST["table"]) && isset($_REQUEST["value"])) {
   // Security
   if (!$DB->tableExists($_REQUEST['table'])) {
      exit();
   }

   switch ($_REQUEST["table"]) {
      case "glpi_plugin_resources_resources" :
         if ($_REQUEST['value']==0) {
            $tmpname['link']    = $CFG_GLPI['root_doc']."/plugins/resources/front/resource.php";
            $tmpname['comment'] = "";
         } else {
            $tmpname = PluginResourcesResource::getResourceName($_REQUEST["value"], 2);
         }
         echo $tmpname["comment"];

         if (isset($_REQUEST['withlink'])) {
            echo "<script type='text/javascript' >\n";
            echo Html::jsGetElementbyID($_REQUEST['withlink']).".attr('href', '".$tmpname['link']."');";
            echo "</script>\n";
         }
         break;

      default :
         if ($_REQUEST["value"]>0) {
            $tmpname = Dropdown::getDropdownName($_REQUEST["table"], $_REQUEST["value"], 1);
            echo $tmpname["comment"];
         }
   }
}
