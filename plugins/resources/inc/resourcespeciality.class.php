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
 * Class PluginResourcesResourceSpeciality
 */
class PluginResourcesResourceSpeciality extends CommonDropdown {

   var $can_be_translated  = true;

   /**
    * @since 0.85
    *
    * @param $nb
    **/
   static function getTypeName($nb = 0) {

      return _n('Speciality', 'Specialities', $nb, 'resources');
   }


   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    **/
   static function canCreate() {
      if (Session::haveRight('dropdown', UPDATE)
         && Session::haveRight('plugin_resources_dropdown_public', UPDATE)) {
         return true;
      }
      return false;
   }

   /**
    * Have I the global right to "view" the Object
    *
    * Default is true and check entity if the objet is entity assign
    *
    * May be overloaded if needed
    *
    * @return booleen
    **/
   static function canView() {
      if (Session::haveRight('plugin_resources_dropdown_public', READ)) {
         return true;
      }
      return false;
   }

   /**
    * Return Additional Fields for this type
    *
    * @return array
    **/
   function getAdditionalFields() {

      return [['name'  => 'plugin_resources_ranks_id',
                        'label' => __('Rank', 'resources'),
                        'type'  => 'dropdownValue',
                        'list'  => true],
                  ];
   }

   /**
    * Display list of specialities depending on rank
    *
    * @static
    * @param $options
    */
   static function showSpeciality($options) {

      $rankId = $options['plugin_resources_ranks_id'];
      $entity = $options['entity'];
      $rand = $options['rand'];

      if ($rankId>0) {

         $condition = " `plugin_resources_ranks_id` = '".$rankId."'";

         Dropdown::show('PluginResourcesResourceSpeciality', ['entity' => $entity,
                  'condition' => $condition]);

      } else {
         echo "<select name='plugin_resources_resourcespecialities_id'
                        id='dropdown_plugin_resources_resourcespecialities_id$rand'>";
         echo "<option value='0'>".Dropdown::EMPTY_VALUE."</option></select>";
      }
   }

   /**
    * During resource's transfer
    *
    * @static
    * @param $ID
    * @param $entity
    * @return ID|int|the
    */
   static function transfer($ID, $entity) {
      global $DB;

      if ($ID>0) {
         // Not already transfer
         // Search init item
         $query = "SELECT *
                   FROM `glpi_plugin_resources_resourcespecialities`
                   WHERE `id` = '$ID'";

         if ($result=$DB->query($query)) {
            if ($DB->numrows($result)) {
               $data = $DB->fetchAssoc($result);
               $data = Toolbox::addslashes_deep($data);
               $input['name'] = $data['name'];
               $input['entities_id']  = $entity;
               $temp = new self();
               $newID    = $temp->getID();

               if ($newID<0) {
                  $newID = $temp->import($input);
               }

               return $newID;
            }
         }
      }
      return 0;
   }

   /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'       => '17',
         'table'    => 'glpi_plugin_resources_ranks',
         'field'    => 'name',
         'name'     => __('Rank', 'resources'),
         'datatype' => 'dropdown'
      ];

      return $tab;
   }


}

