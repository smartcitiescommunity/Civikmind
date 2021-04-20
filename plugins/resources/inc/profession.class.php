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
 * Class PluginResourcesProfession
 */
class PluginResourcesProfession extends CommonDropdown {

   /**
    * @since 0.85
    *
    * @param $nb
    **/
   static function getTypeName($nb = 0) {

      return _n('Profession', 'Professions', $nb, 'resources');
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

      return [['name'  => 'code',
                         'label' => __('Code', 'resources'),
                         'type'  => 'text',
                         'list'  => true],
                  ['name'  => 'short_name',
                        'label' => __('Short name', 'resources'),
                        'type'  => 'text',
                        'list'  => true],
                  ['name'  => 'plugin_resources_professionlines_id',
                        'label' => __('Profession line', 'resources'),
                        'type'  => 'dropdownValue',
                        'list'  => true],
                  ['name'  => 'plugin_resources_professioncategories_id',
                        'label' => __('Profession category', 'resources'),
                        'type'  => 'dropdownValue',
                        'list'  => true],
                  ['name'  => 'begin_date',
                        'label' => __('Begin date'),
                        'type'  => 'date',
                        'list'  => false],
                  ['name'  => 'end_date',
                        'label' => __('End date'),
                        'type'  => 'date',
                        'list'  => false],
                  ['name'  => 'is_active',
                        'label' => __('Active'),
                        'type'  => 'bool',
                        'list'  => true],
                  ];
   }

   /**
    * During resource or employment transfer
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
                   FROM `glpi_plugin_resources_professions`
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

               //transfer of the linked line
               $line = PluginResourcesProfessionLine::transfer($temp->fields["plugin_resources_professionlines_id"], $entity);
               if ($line > 0) {
                  $values["id"] = $newID;
                  $values["plugin_resources_professionlines_id"] = $line;
                  $temp->update($values);
               }

               //transfer of the linked category
               $category = PluginResourcesProfessionCategory::transfer($temp->fields["plugin_resources_professioncategories_id"], $entity);
               if ($category > 0) {
                  $values["id"] = $newID;
                  $values["plugin_resources_professioncategories_id"] = $category;
                  $temp->update($values);
               }

               return $newID;
            }
         }
      }
      return 0;
   }

   /**
    * When a profession is deleted -> deletion of the linked ranks
    *
    * @return nothing|void
    */
   function cleanDBonPurge() {

      $temp = new PluginResourcesRank();
      $temp->deleteByCriteria(['plugin_resources_professions_id' => $this->fields['id']]);

   }

   /**
    * @return array
    */
   function rawSearchOptions() {

      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'       => '14',
         'table'    => $this->getTable(),
         'field'    => 'code',
         'name'     => __('Code', 'resources')
      ];
      $tab[] = [
         'id'       => '15',
         'table'    => $this->getTable(),
         'field'    => 'short_name',
         'name'     => __('Short name', 'resources')
      ];
      $tab[] = [
         'id'       => '17',
         'table'    => 'glpi_plugin_resources_professionlines',
         'field'    => 'name',
         'name'     => __('Profession line', 'resources'),
         'datatype'      => 'dropdown'
      ];
      $tab[] = [
         'id'       => '18',
         'table'    => 'glpi_plugin_resources_professioncategories',
         'field'    => 'name',
         'name'     => __('Profession category', 'resources'),
         'datatype'      => 'dropdown'
      ];
      $tab[] = [
         'id'       => '19',
         'table'    => $this->getTable(),
         'field'    => 'is_active',
         'name'     => __('Active'),
         'datatype'      => 'bool'
      ];
      $tab[] = [
         'id'       => '20',
         'table'    => $this->getTable(),
         'field'    => 'begin_date',
         'name'     => __('Begin date'),
         'datatype'      => 'date'
      ];
      $tab[] = [
         'id'       => '21',
         'table'    => $this->getTable(),
         'field'    => 'end_date',
         'name'     => __('End date'),
         'datatype'      => 'date'
      ];

      return $tab;
   }

   /**
    * is_active = 1 during a creation
    *
    * @return nothing|void
    */
   function post_getEmpty() {

      $this->fields['is_active'] = 1;
   }

}

