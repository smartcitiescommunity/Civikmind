<?php

/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 webapplications plugin for GLPI
 Copyright (C) 2009-2016 by the webapplications Development Team.

 https://github.com/InfotelGLPI/webapplications
 -------------------------------------------------------------------------

 LICENSE

 This file is part of webapplications.

 webapplications is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 webapplications is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with webapplications. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginWebapplicationsAppliance extends CommonDBTM {

   static function addFields($params) {

      $item             = $params['item'];
      $webapp_appliance = new self();
      if ($item->getType() == 'Appliance') {

         if ($item->getID()) {
            $webapp_appliance->getFromDBByCrit(['appliances_id' => $item->getID()]);
         }

         echo "<tr class='tab_bg_1'>";
         //url of webapplications
         echo "<td>" . __('URL') . "</td>";
         echo "<td  colspan='3'>";
         Html::autocompletionTextField($webapp_appliance, "address", ['option' => "size='65'"]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         //backoffice of webapplications
         echo "<td>" . __('Backoffice URL', 'webapplications') . "</td>";
         echo "<td colspan='3'>";
         Html::autocompletionTextField($webapp_appliance, "backoffice", ['option' => "size='65'"]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         //type of webapplications
         echo "<td>" . PluginWebapplicationsWebapplicationType::getTypeName(1) . "</td>";
         echo "<td>";
         Dropdown::show('PluginWebapplicationsWebapplicationType',
                        ['value'  => $webapp_appliance->fields["webapplicationtypes_id"],
                         'entity' => $item->fields["entities_id"]]);
         echo "</td>";
         //server type of webapplications
         echo "<td>" . PluginWebapplicationsWebapplicationServerType::getTypeName(1) . "</td>";
         echo "<td>";
         Dropdown::show('PluginWebapplicationsWebapplicationServerType',
                        ['value' => $webapp_appliance->fields["webapplicationservertypes_id"]]);
         echo "</td>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         //manufacturer of webapplications
         echo "<td>" . __('Editor', 'webapplications') . "</td>";
         echo "<td>";
         Dropdown::show('Manufacturer',
                        ['value'  => $item->fields["manufacturers_id"],
                         'entity' => $item->fields["entities_id"]]);
         echo "</td>";
         echo "</td>";
         //language of webapplications
         echo "<td>" . PluginWebapplicationsWebapplicationTechnic::getTypeName(1) . "</td>";
         echo "<td>";
         Dropdown::show('PluginWebapplicationsWebapplicationTechnic',
                        ['value' => $webapp_appliance->fields["webapplicationtechnics_id"]]);
         echo "</td>";
         echo "</tr>";

      }

   }

   static function applianceAdd(Appliance $item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }
      self::setAppliance($item);
   }


   static function applianceUpdate(Appliance $item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }
      self::setAppliance($item);
   }

   static function setAppliance(Appliance $item) {
      $appliance = new PluginWebApplicationsAppliance();
      if (!empty($item->fields)) {
         $appliance->getFromDBByCrit(['appliances_id' => $item->getID()]);
         $address    = isset($item->input['address']) ? $item->input['address'] : "";
         $backoffice = isset($item->input['backoffice']) ? $item->input['backoffice'] : "";
         if (is_array($appliance->fields) && count($appliance->fields) > 0) {
            $appliance->update(['id'                           => $appliance->fields['id'],
                                'address'                      => $address,
                                'backoffice'                   => $backoffice,
                                'webapplicationtypes_id'       => $item->input['plugin_webapplications_webapplicationtypes_id'],
                                'webapplicationservertypes_id' => $item->input['plugin_webapplications_webapplicationservertypes_id'],
                                'webapplicationtechnics_id'    => $item->input['plugin_webapplications_webapplicationtechnics_id']]);
         } else {
            $appliance->add(['webapplicationtypes_id'       => $item->input['plugin_webapplications_webapplicationtypes_id'],
                             'webapplicationservertypes_id' => $item->input['plugin_webapplications_webapplicationservertypes_id'],
                             'webapplicationtechnics_id'    => $item->input['plugin_webapplications_webapplicationtechnics_id'],
                             'address'                      => $address,
                             'appliances_id'                => $item->getID(),
                             'backoffice'                   => $backoffice]);
         }
      }
   }


   /**
    * @param $ID
    */
   static function cleanRelationToAppliance($item) {

      $temp = new self();
      $temp->deleteByCriteria(['appliances_id' => $item->getID()]);

   }
}
