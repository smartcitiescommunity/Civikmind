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

if (Session::getCurrentInterface() == 'central') {
   Html::header(PluginResourcesResource::getTypeName(2), '', "admin", PluginResourcesMenu::getType());
} else {
   Html::helpHeader(PluginResourcesResource::getTypeName(2));
}

if (isset($_POST['plugin_resources_resources_id'])) {
   $plugin_resources_resources_id = $_POST['plugin_resources_resources_id'];
} else {
   $resource_item = new PluginResourcesResource_Item();
   $resource = $resource_item->find(['itemtype' => 'User', 
                                     'items_id' => $_SESSION['glpiID']],
                                    [],
                                    [1]);

   $resource = reset($resource);
   $plugin_resources_resources_id = $resource['plugin_resources_resources_id'];
}

if (Session::haveRight("plugin_resources", UPDATE)) {
   echo "<div align='center'>";
   echo "<form name='main' action=\"./resource.card.form.php\" method=\"post\">";
   echo "<table class='tab_cadre' width='31%'>";
   echo "<tr class='tab_bg_2 center'>";
   echo "<td>";
   PluginResourcesResource::dropdown(['name'      => 'plugin_resources_resources_id',
                                      'display'   => true,
                                           'entity' => $_SESSION['glpiactiveentities'],
                                           'value'     => $plugin_resources_resources_id,
                                           'on_change' => 'main.submit();']);
   echo "</td>";
   echo "</tr>";
   echo "</table>";
   Html::closeForm();
   echo "</div>";
}

if ($plugin_resources_resources_id > 0) {
   PluginResourcesResourceCard::resourceCard($plugin_resources_resources_id);
} else {
   echo "<div class='center'><br><br>".
        "<i  class='fas fa-info-circle' alt='information'></i>";
   echo "&nbsp;<b>".__('Please select a user', 'resources')."</b></div>";
}
if (Session::getCurrentInterface() == 'central') {
   Html::footer();
} else {
   Html::helpFooter();
}
