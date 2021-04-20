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
 * Class PluginResourcesHabilitationLevel
 */
class PluginResourcesHabilitationLevel extends CommonDropdown {

   // From CommonDBTM
   public $dohistory          = true;
   public $can_be_translated  = true;

   /**
    * @since version 0.85
    *
    * @param $nb
    **/
   static function getTypeName($nb = 0) {

      return _n('Habilitation level', 'Habilitations level', $nb, 'resources');
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
      return Session::haveRight('plugin_resources', READ);
   }

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    **/
   static function canCreate() {
      return Session::haveRightsOr('dropdown', [CREATE, UPDATE, DELETE]);
   }

   /**
    * Return Additional Fields for this type
    *
    * @return array
    **/
   function getAdditionalFields() {

      $tab = [['name'  => 'is_mandatory_creating_resource',
               'label' => __('Mandatory when creating the resource', 'resources'),
               'type'  => 'bool',
               'list'  => true],
              ['name'  => "number",
               'label' => __('Unlimited number of selectable habilitations ', 'resources'),
               'type'  => 'bool',
               'list'  => true]
      ];

      return $tab;
   }


   /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'       => '15',
         'table'    => $this->getTable(),
         'field'    => 'is_mandatory_creating_resource',
         'name'     => __('Mandatory when creating the resource', 'resources'),
         'datatype' => 'bool'
      ];
      $tab[] = [
         'id'       => '14',
         'table'    => $this->getTable(),
         'field'    => 'number',
         'name'     => __('Unlimited number of selectable habilitations ', 'resources'),
         'datatype' => 'bool'
      ];

      return $tab;
   }

   /**
    * Transfer
    *
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
                   FROM `glpi_plugin_resources_habilitationlevels`
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

}
