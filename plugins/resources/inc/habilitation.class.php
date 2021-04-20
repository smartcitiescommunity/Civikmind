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
 * Class PluginResourcesHabilitation
 */
class PluginResourcesHabilitation extends CommonTreeDropdown {

   // From CommonDBTM
   public $dohistory          = true;
   public $can_be_translated  = true;

   /**
    * @since version 0.85
    *
    * @param $nb
    **/
   static function getTypeName($nb = 0) {

      return _n('Habilitation', 'Habilitations', $nb, 'resources');
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
    * Return Additional Fileds for this type
    **/
   function getAdditionalFields() {

      $tab = [['name'  => $this->getForeignKeyField(),
               'label' => __('As child of'),
               'type'  => 'parent',
               'list'  => true],
              ['name'  => "plugin_resources_habilitationlevels_id",
               'label' => __('Habilitation level', 'resources'),
               'type'  => 'dropdownValue',
               'list'  => true]
      ];

      return $tab;
   }

   /**
    * Get search function for the class
    *
    * @return array of search option
    **/
   function rawSearchOptions() {

      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'       => '15',
         'table'    => 'glpi_plugin_resources_habilitationlevels',
         'field'    => 'name',
         'name'     => __('Habilitation level', 'resources'),
         'datatype' => 'dropdown'
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
                   FROM `glpi_plugin_resources_habilitations`
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
    * Returns habilitations according to level
    *
    * @param \PluginResourcesHabilitationLevel $habilitationlevels
    * @param                                   $entity
    *
    * @return array list of habilitations
    */
   function getHabilitationsWithLevel(PluginResourcesHabilitationLevel $habilitationlevels, $entity) {
      global $DB;

      $plugin_habilitation = new self();
      $plugin_resources_habilitationlevels_id = $habilitationlevels->getID();
      $habilitations = [];

      //add an empty value for the non multiple dropdown list
      if (!$habilitationlevels->getField('number')) {
         $habilitations[''] = Dropdown::EMPTY_VALUE;
      }
      $dbu   = new DbUtils();
      $query = "SELECT *
                FROM `" . $plugin_habilitation->getTable() . "`
                WHERE `plugin_resources_habilitationlevels_id` = '$plugin_resources_habilitationlevels_id'
                 " . $dbu->getEntitiesRestrictRequest("AND", $plugin_habilitation->getTable(), "entities_id",
                                                      $entity, $plugin_habilitation->maybeRecursive());

      foreach ($DB->request($query) as $habilitation) {
          $habilitations[$habilitation['id']] = $habilitation['name'];
         if (isset($habilitation['comment']) && !empty($habilitation['comment'])) {
            $habilitations[$habilitation['id']] .= " - " . $habilitation['comment'];
         }
      }

      return $habilitations;
   }
}
