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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginResourcesResourceCard
 */
class PluginResourcesResourceCard extends CommonDBTM {

   static $rightname = 'plugin_resources';

   static $types = ['Computer', 'Peripheral', 'Phone', 'Printer', 'PluginSimcardSimcard', 'PluginBadgesBadge'];

   /**
    * @param $ID
    */
   static function resourceCard($ID) {
      global $CFG_GLPI;

      $resource = new PluginResourcesResource();
      $resource->getFromDB($ID);

      $resource_item = new PluginResourcesResource_Item();
      $data          =  $resource_item->find(['itemtype' => 'User',
                                              'plugin_resources_resources_id' => $ID], [], [1]);

      $data     = reset($data);
      $users_id = $data['items_id'];

      $user = new User();
      if ($user->getFromDB($users_id)) {

         echo "<div id='plugin_resources_container'>";

         echo "<div id='plugin_resources_card'>";
         echo "<div id='plugin_resources_card-header'>";
         echo "<div id='plugin_resources_card-header-button' data-download='" . $CFG_GLPI["root_doc"] .
              "/front/user.form.php?getvcard=1&amp;id=" . $user->getID() . "'
                           class='download mouse-events'></div>";
         echo "</div>"; //end plugin_resources_card-header

         echo "<div id='plugin_resources_card-content'>";
         echo "<div id='plugin_resources_card-content-frame'>";

         echo "<div id='plugin_resources_card-content-wrap'>";
         self::showIdentity($resource, $user);
         echo "</div>"; //end plugin_resources_card-content-wrap

         self::showItems($user);

         echo "</div>"; //end plugin_resources_card-content-frame
         echo "</div>"; //end plugin_resources_card-content
         echo "</div>"; //end plugin_resources_card

         echo "<div id='plugin_resources_card-footer'></div>";

         echo "</div>"; //end plugin_resources_container

         ///navigation
         echo "<nav>";
         echo "<ul class='plugin_resources_clearfix'>";
         echo "<li class='active'><a href='" . $CFG_GLPI["root_doc"] . "/plugins/resources/front/resource.card.form.php#about'>" . __('About', 'resources') . "</a></li>";
         echo "<li><a href='" . $CFG_GLPI["root_doc"] . "/plugins/resources/front/resource.card.form.php#inventory'>" . __('Inventory', 'resources') . "</a></li>";
         echo "</ul>";
         echo "</nav>";
      } else {

         echo "<div id='plugin_resources_container'>";

         echo "<div id='plugin_resources_card'>";
         echo "<div id='plugin_resources_card-header'>";
         echo "</div>"; //end plugin_resources_card-header

         echo "<div id='plugin_resources_card-content'>";
         echo "<div id='plugin_resources_card-content-frame'>";

         echo "<div id='plugin_resources_card-content-wrap'>";
         self::showIdentity($resource);
         echo "</div>"; //end plugin_resources_card-content-wrap

         echo "</div>"; //end plugin_resources_card-content-frame
         echo "</div>"; //end plugin_resources_card-content
         echo "</div>"; //end plugin_resources_card

         echo "<div id='plugin_resources_card-footer'></div>";

         echo "</div>"; //end plugin_resources_container

      }
   }

   /**
    * @param $user
    * @param $resource
    */
   static function showIdentity($resource, $user = false) {

      echo "<div id='plugin_resources_about' class='plugin_resources_content plugin_resources_clearfix'>";

      $dbu = new DbUtils();

      if ($user === false) {

         echo "<p>";
         echo "<span class='b red'>" . __('Information, this resource is not linked to a user', 'resources') . "</br>";
         echo "</p>";

         echo "<div id='plugin_resources_about-image'>";
         echo "<img src='" . User::getThumbnailURLForPicture('') . "' alt='' />";
         echo "</div>"; //end plugin_resources_about-image

         echo "<div id='plugin_resources_about-content' align='left'>";

         echo "<h1>" . sprintf(__('%1$s %2$s'), $resource->fields['firstname'], $resource->fields['name']) . "</h1>";
         echo "<div style='height:10px;'></div>";
         echo "<div class='scrollable' style='padding-right: 8px;height:420px;'>";
         echo "<p>";
         echo sprintf(__('%1$s: %2$s'), "<span class='b'>" . __('Location'), "</span>" .
                                                                             Dropdown::getDropdownName($dbu->getTableForItemType('Location'),
                                                                                                       $resource->fields['locations_id'])) . "</br>";

         echo "<p>" . sprintf(__('%1$s: %2$s'), "<span class='b'>" . __('Arrival date', 'resources'),
                              "</span>" . Html::convDate($resource->fields["date_begin"])) . "</br>";
         echo "</p>";

         if (PluginResourcesResourceHabilitation::canView()) {

            if ($count = PluginResourcesResourceHabilitation::countForResource($resource)) {
               echo "<h3>" . PluginResourcesResourceHabilitation::getTypeName($count) . "</h3>";

               $resourcehabilitation = new PluginResourcesResourceHabilitation();
               $datas                = $resourcehabilitation->find(['plugin_resources_resources_id' => $resource->getField('id')]);

               echo "<table class='tab_cadre_fixe'>";
               echo "<tr>";
               echo "<th style='text-align: center;'>" . __('Name') . "</th>";
               echo "</tr>";
               foreach ($datas as $data) {
                  echo "<tr class='tab_bg_1'>";
                  echo "<td class='center'>" . Dropdown::getDropdownName('glpi_plugin_resources_habilitations',
                                                                         $data['plugin_resources_habilitations_id']) . "</td>";
                  echo "</tr>";
               }
               echo "</table>";

            }

         }

         echo "</div>"; //end plugin_resources_scrollable
         echo "</div>"; //end plugin_resources_about-content

      } else {

         echo "<div id='plugin_resources_about-image'>";
         echo "<img src='" . PluginResourcesResource::getThumbnailURLForPicture($resource->fields['picture']) . "' alt='' />";
         echo "</div>"; //end plugin_resources_about-image

         echo "<div id='plugin_resources_about-content' align='left'>";

         echo "<h1>" . $dbu->getUsername($user->getID()) . "</h1>";
         echo "<h2>" . Dropdown::getDropdownName('glpi_usertitles', $user->getField('usertitles_id')) . "</h2>";
         echo "<div style='height:10px;'></div>";
         echo "<div class='scrollable' style='padding-right: 8px;height:420px;'>";
         echo "<p>" . sprintf(__('%1$s: %2$s'), "<span class='b'>" . __('Phone'), "</span>" . $user->fields['phone']) . "</br>";
         echo sprintf(__('%1$s: %2$s'), "<span class='b'>" . __('Phone 2'), "</span>" . $user->fields['phone2']) . "</br>";
         echo sprintf(__('%1$s: %2$s'), "<span class='b'>" . __('Mobile phone'), "</span>" . $user->fields['mobile']) . "</br>";
         echo sprintf(__('%1$s: %2$s'), "<span class='b'>" . __('Location'), "</span>" .
                                                                             Dropdown::getDropdownName($dbu->getTableForItemType('Location'),
                                                                                                       $user->fields['locations_id'])) . "</br>";

         $emails = $user->getAllEmails($user->getID());

         if (!empty($emails)) {
            $count = count($emails);
            echo "<span class='b'>" . _n('Email', 'Emails', $count) . "</span>&nbsp;:&nbsp;";
            $n = 1;
            foreach ($emails as $id => $email) {
               echo $email;
               if ($n < $count) {
                  $n++;
                  echo ", ";
               }
            }
         }
         echo "</p>";

         echo "<p>" . sprintf(__('%1$s: %2$s'), "<span class='b'>" . __('Arrival date', 'resources'),
                              "</span>" . Html::convDate($resource->fields["date_begin"])) . "</br>";
         echo "</p>";

         if (PluginResourcesResourceHabilitation::canView()) {

            if ($count = PluginResourcesResourceHabilitation::countForResource($resource)) {
               echo "<h3>" . PluginResourcesResourceHabilitation::getTypeName($count) . "</h3>";

               $resourcehabilitation = new PluginResourcesResourceHabilitation();
               $datas                = $resourcehabilitation->find(['plugin_resources_resources_id' => $resource->getField('id')]);

               echo "<table class='tab_cadre_fixe'>";
               echo "<tr>";
               echo "<th style='text-align: center;'>" . __('Name') . "</th>";
               echo "</tr>";
               foreach ($datas as $data) {
                  echo "<tr class='tab_bg_1'>";
                  echo "<td class='center'>" . Dropdown::getDropdownName('glpi_plugin_resources_habilitations',
                                                                         $data['plugin_resources_habilitations_id']) . "</td>";
                  echo "</tr>";
               }
               echo "</table>";

            }

         }

         echo "</div>"; //end plugin_resources_scrollable
         echo "</div>"; //end plugin_resources_about-content

      }
      echo "</div>"; //end plugin_resources_about

   }

   /**
    * @param $user
    */
   static function showItems($user) {
      global $CFG_GLPI, $DB;

      echo "<div id='plugin_resources_inventory' class='plugin_resources_content' align='left'>";
      echo "<h1>" . __('Inventory', 'resources') . "</h1>";

      echo "<div class='scrollable'  style='padding-right: 8px;height:420px;'>";

      $type_user  = $CFG_GLPI['linkuser_types'];
      $field_user = 'users_id';

      $ID = $user->getID();

      $inv   = false;
      $datas = [];
      $dbu   = new DbUtils();
      foreach ($type_user as $itemtype) {

         if (!($item = $dbu->getItemForItemtype($itemtype)) || !in_array($itemtype, self::$types)) {
            continue;
         }
         $i         = 0;
         $itemtable = $dbu->getTableForItemType($itemtype);
         $query     = "SELECT *
                      FROM `$itemtable`
                      WHERE `" . $field_user . "` = '$ID'";

         if ($item->maybeTemplate()) {
            $query .= " AND `is_template` = 0 ";
         }
         if ($item->maybeDeleted()) {
            $query .= " AND `is_deleted` = 0 ";
         }
         $query  .= $dbu->getEntitiesRestrictRequest('AND', $itemtable, '', $item->maybeRecursive());
         $result = $DB->query($query);

         if ($DB->numrows($result) > 0) {
            $inv = true;
            while ($data = $DB->fetchAssoc($result)) {

               $datas[$itemtype][$i] = $data;
               $i++;
            }
         }
      }
      foreach ($datas as $type => $table) {

         echo "<table class='tab_cadre_fixe'>";

         $obj       = new $type();
         $count     = count($table);
         $type_name = $obj->getTypeName($count);
         echo "<tr><td colspan='3' class='center b'>$type_name</td></tr>";

         foreach ($table as $k => $values) {

            $cansee = $obj->can($values["id"], READ);
            $link   = $values["name"];
            if ($cansee && Session::getCurrentInterface() == 'central') {
               $link_item = Toolbox::getItemTypeFormURL($type);
               if ($_SESSION["glpiis_ids_visible"] || empty($link)) {
                  $link = sprintf(__('%1$s (%2$s)'), $link, $values["id"]);
               }
               $link = "<a href='" . $link_item . "?id=" . $values["id"] . "'>" . $link . "</a>";
            }

            echo "<tr class='tab_bg_1'>";
            echo "<td class='center'  width='100'>";
            if (file_exists("../pics/gallery/" . $type . ".jpg")) {
               echo "<img src='../pics/gallery/" . $type . ".jpg' width = '50%' alt='' />";
            } else {
               echo "<img src='../pics/gallery/nothing.png' width = '50%' alt='' />";
            }
            echo "</td>";

            echo "<td class='left'>$link</br>";
            if (Session::isMultiEntitiesMode()) {
               echo Dropdown::getDropdownName("glpi_entities", $values["entities_id"]) . "</br>";
            }
            if (isset($values["locations_id"]) && !empty($values["locations_id"])) {
               echo Dropdown::getDropdownName("glpi_locations", $values["locations_id"]) . "</br>";
            }
            if (isset($values["groups_id"]) && !empty($values["groups_id"])) {
               echo Dropdown::getDropdownName("glpi_groups", $values["groups_id"]) . "</br>";
            }
            if (isset($values["serial"]) && !empty($values["serial"])) {
               echo $values["serial"];
               echo "</br>";
            }
            if (isset($values["otherserial"]) && !empty($values["otherserial"])) {
               echo $values["otherserial"];
               echo "</br>";
            }
            //            if (isset($values["states_id"])) {
            //               echo Dropdown::getDropdownName("glpi_states", $values['states_id']);
            //            }
            echo "</td></tr>";
         }

         echo "</table>";
      }

      if ($inv == false) {
         echo "<div class='center'><br><br>" .
              "<i class='fas fa-exclamation-triangle fa-4x' style='color:orange'></i><br><br>";
         echo "<b>" . __('No item found') . "</b></div>";

      }

      echo "</div>";
      echo "</div>";

   }

}
