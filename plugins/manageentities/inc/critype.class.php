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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginManageentitiesCriType extends CommonDropdown {

   static $rightname = 'plugin_manageentities';

   static function getTypeName($nb = 0) {
      return _n('Intervention type', 'Intervention types', $nb, 'manageentities');
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   static function canView() {
      $config = PluginManageentitiesConfig::getInstance();
      if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
         return Session::haveRight(self::$rightname, READ);
      }
   }

   function rawSearchOptions() {

      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'           => '12',
         'table'        => 'glpi_plugin_manageentities_contractdays',
         'field'        => 'name',
         'forcegroupby' => true,
         'name'         => PluginManageentitiesContractDay::getTypeName(),
         'datatype'     => 'itemlink',
         'joinparams'   => ['condition'  =>
                               "AND REFTABLE.`entities_id` IN ('" . implode("','", $_SESSION["glpiactiveentities"]) . "')",
                            'beforejoin' =>
                               ['table'      => 'glpi_plugin_manageentities_criprices',
                                'joinparams' => ['jointype' => "child"]
                               ]
         ]
      ];

      //TODO : Problème de table non unique : comment ajouter un allias ?
      $tab[] = [
         'id'           => '13',
         'table'        => 'glpi_plugin_manageentities_criprices',
         'field'        => 'price',
         'datatype'     => 'number',
         'forcegroupby' => true,
         'name'         => __('Daily rate', 'manageentities'),
         /*'joinparams'   =>  ['jointype'  => "child",
                             'condition' => "AND NEWTABLE.`entities_id` IN ('".implode("','", $_SESSION["glpiactiveentities"])."')"]*/
      ];
      /* OLD :

      $tab[13]['table']        = 'glpi_plugin_manageentities_criprices';
      $tab[13]['field']        = 'price';
      $tab[13]['datatype']     = 'number';
      $tab[13]['forcegroupby'] = true;
      $tab[13]['name']         = __('Daily rate', 'manageentities');
      $tab[13]['joinparams']   = ['jointype'  => "child",
                                       'condition' => "AND NEWTABLE.`entities_id` IN ('".implode("','", $_SESSION["glpiactiveentities"])."')"];
      */

      return $tab;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'PluginManageentitiesCriType' :
               return PluginManageentitiesCriType::getTypeName(1);
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $criprice = new PluginManageentitiesCriPrice();
      if ($item->getType() == 'PluginManageentitiesCriType') {
         $criprice->showForCriType($item);
      }
      return true;
   }
}
