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

$plugin = new Plugin();
if ($plugin->isActivated("useditemsexport")) {

   if (isset($_POST['plugin_resources_resources_id'])) {
      $resource_item = new PluginResourcesResource_Item();
      $resource      = $resource_item->find(['itemtype'                      => 'User',
                                             'plugin_resources_resources_id' => $_POST['plugin_resources_resources_id']],
                                            [],
                                            [1]);
      if (count($resource) == 1) {
         $resource = reset($resource);
         $users_id = $resource['items_id'];

         $type_user  = $CFG_GLPI['linkuser_types'];
         $field_user = 'users_id';

         $total_numrows = 0;
         $dbu           = new DbUtils();

         foreach ($type_user as $itemtype) {
            if (!($item = $dbu->getItemForItemtype($itemtype))) {
               continue;
            }

            $itemtable = $dbu->getTableForItemType($itemtype);
            $query     = "SELECT *
                      FROM `$itemtable`
                      WHERE `" . $field_user . "` = '$users_id'";

            if ($item->maybeTemplate()) {
               $query .= " AND `is_template` = 0 ";
            }
            if ($item->maybeDeleted()) {
               $query .= " AND `is_deleted` = 0 ";
            }
            $result        = $DB->query($query);
            $total_numrows += $DB->numrows($result);
         }

         if ($total_numrows > 0) {

            $rand = mt_rand();

            $url = $CFG_GLPI["root_doc"] . "/plugins/resources/front/export.pdf.php";
            echo __('Please ensure that the return form is signed by the employee', 'resources') . "<br><br>";
            echo "<span class='red'>" .
                 __("The sales manager is responsible for the complete return of the company's equipment held by the outgoing employee (badge, PC, smartphone, etc.)", 'resources') .
                 "</span><br><br>";
            echo "<a class='vsubmit' href='$url?generate_pdf&users_id=$users_id' target=\"_blank\">" . __('Download the restitution form', 'resources') . "</a>";

            Html::closeForm();
         }
      }
   }
}
