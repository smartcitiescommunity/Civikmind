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
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

$resource_change = new PluginResourcesResource_Change();

if (isset($_POST['plugin_resources_clients_id'])) {
   if (PluginResourcesClient::isSecurityCompliance($_POST['plugin_resources_clients_id'])) {
      $img = "<i style='color:green' class='fas fa-check-circle' alt=\"" . __('OK') . "\"></i>";
      $color = "color: green;";
   } else {
      $img = "<i style='color:red' class='fas fa-times-circle' alt=\"" . __('KO') . "\"></i>";
      $color = "color: red;";
   }
   echo "<span style='$color'>";
   echo __('Security compliance', 'resources')."&nbsp;";
   echo $img;
   echo "</span>";

}
