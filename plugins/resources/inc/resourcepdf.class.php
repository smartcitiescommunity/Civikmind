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

/**
 * Class PluginResourcesResourcePDF
 */
class PluginResourcesResourcePDF extends PluginPdfCommon {


   /**
    * PluginResourcesResourcePDF constructor.
    *
    * @param \CommonGLPI|null $obj
    */
   function __construct(CommonGLPI $obj = null) {

      $this->obj = ($obj ? $obj : new PluginResourcesResource());
   }


   /**
    * @param \PluginPdfSimplePDF      $pdf
    * @param \PluginResourcesResource $res
    *
    * @return bool
    */
   static function pdfMain(PluginPdfSimplePDF $pdf, PluginResourcesResource $res) {

      $ID = $res->getField('id');
      if (!$res->can($ID, READ)) {
         return false;
      }

      $pdf->setColumnsSize(50, 50);
      $col1 = '<b>'.__('ID').' '.$res->fields['id'].'</b>';
      if (isset($res->fields["date_declaration"])) {
         $users_id_recipient=new User();
         $users_id_recipient->getFromDB($res->fields["users_id_recipient"]);
         $col2 = __('Request date').' : '.Html::convDateTime($res->fields["date_declaration"]).' '.__('Requester').' '.$users_id_recipient->getName();
      } else {
         $col2 = '';
      }
      $pdf->displayTitle($col1, $col2);

      $pdf->displayLine(
         '<b><i>'.__('Surname').' :</i></b> '.$res->fields['name'],
         '<b><i>'.__('First name').' :</i></b> '.$res->fields['firstname']);
      $pdf->displayLine(
         '<b><i>'.__('Location').' :</i></b> '.Html::clean(Dropdown::getDropdownName('glpi_locations', $res->fields['locations_id'])),
         '<b><i>'.PluginResourcesContractType::getTypeName(1).' :</i></b> '.Html::clean(Dropdown::getDropdownName('glpi_plugin_resources_contracttypes', $res->fields['plugin_resources_contracttypes_id'])));

      $dbu = new DbUtils();
      $pdf->displayLine(
         '<b><i>'.__('Resource manager', 'resources').' :</i></b> '.Html::clean($dbu->getUserName($res->fields["users_id"])),
         '<b><i>'.PluginResourcesDepartment::getTypeName(1).' :</i></b> '.Html::clean(Dropdown::getDropdownName('glpi_plugin_resources_departments', $res->fields["plugin_resources_departments_id"])));

      $pdf->displayLine(
         '<b><i>'.__('Arrival date', 'resources').' :</i></b> '.Html::convDate($res->fields["date_begin"]),
         '<b><i>'.__('Departure date', 'resources').' :</i></b> '.Html::convDate($res->fields["date_end"]));

      $pdf->setColumnsSize(100);

      $pdf->displayText('<b><i>'.__('Description').' :</i></b>', $res->fields['comment']);

      $pdf->displaySpace();
   }

   /**
    * @param array $options
    *
    * @return mixed
    */
   function defineAllTabs($options = []) {

      $onglets = parent::defineAllTabs($options);
      unset($onglets['PluginResourcesChoice####1']);
      unset($onglets['PluginResourcesReportConfig####1']);
       unset($onglets['Item_Problem$1']); // TODO add method to print linked Problems
      return $onglets;
   }

   /**
    * @param \PluginPdfSimplePDF $pdf
    * @param \CommonGLPI         $item
    * @param                     $tab
    *
    * @return bool
    */
   static function displayTabContentForPDF(PluginPdfSimplePDF $pdf, CommonGLPI $item, $tab) {

      switch ($tab) {
         case '_main_' :
            $item->show_PDF($pdf);
            break;

         default :
            return false;
      }
      return true;
   }
}
