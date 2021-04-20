<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginMetademandsMetademandTask
 */
class PluginMetademandsMetademandTask extends CommonDBTM {

   static $rightname = 'plugin_metademands';

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    *
    * @param int $nb
    *
    * @return string
    */
   static function getTypeName($nb = 0) {
      return __('Task creation', 'metademands');
   }

   /**
    * @return bool|int
    */
   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * @return bool
    */
   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   /**
    * @param $ID
    *
    * @throws \GlpitestSQLError
    */
   static function showMetademandTaskForm($ID) {

      // Avoid select of parent metademands
      $used   = PluginMetademandsMetademandTask::getAncestorOfMetademandTask($ID);
      $used[] = $ID;

      echo PluginMetademandsMetademand::getTypeName(1) . "&nbsp;:&nbsp;";
      Dropdown::show('PluginMetademandsMetademand',
                     ['name' => 'link_metademands_id',
                      'used' => $used,
                      'condition' => ['is_order' => 0]
                     ]);

      unset($used[array_search($ID, $used)]);

      foreach ($used as $metademands_id) {
         echo "<br><span style='color:red'>" . __('This demand is already used in', 'metademands') . "&nbsp;:&nbsp;" .
              Dropdown::getDropdownName('glpi_plugin_metademands_metademands', $metademands_id) . "</span>";
      }

   }

   /**
    * @param $tasks_id
    *
    * @return mixed
    * @throws \GlpitestSQLError
    */
   static function getMetademandTaskName($tasks_id) {
      global $DB;

      if ($tasks_id > 0) {
         $query  = "SELECT `glpi_plugin_metademands_metademands`.`name`
               FROM `glpi_plugin_metademands_metademands`
               LEFT JOIN `glpi_plugin_metademands_metademandtasks`
                  ON (`glpi_plugin_metademands_metademandtasks`.`plugin_metademands_metademands_id` = `glpi_plugin_metademands_metademands`.`id`)
               WHERE `glpi_plugin_metademands_metademandtasks`.`plugin_metademands_tasks_id` = " . $tasks_id;
         $result = $DB->query($query);

         if ($DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               return $data['name'];
            }
         }
      }
   }

   /**
    * @param $metademands_id
    *
    * @return mixed
    * @throws \GlpitestSQLError
    */
   static function getSonMetademandTaskId($metademands_id) {
      global $DB;

      $res = [];
      $query = "SELECT `glpi_plugin_metademands_metademandtasks`.`plugin_metademands_tasks_id` as tasks_id,
                       `glpi_plugin_metademands_metademandtasks`.`plugin_metademands_metademands_id` as metademands_id
               FROM `glpi_plugin_metademands_metademandtasks`
               LEFT JOIN `glpi_plugin_metademands_tasks`
                  ON (`glpi_plugin_metademands_tasks`.`id` = `glpi_plugin_metademands_metademandtasks`.`plugin_metademands_tasks_id`)
               WHERE `glpi_plugin_metademands_tasks`.`plugin_metademands_metademands_id` = " . $metademands_id;
      $result = $DB->query($query);

      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $res[$data['metademands_id']] = $data['tasks_id'];
         }
         return $res;
      }
   }

   /**
    * @param $metademands_id
    *
    * @return mixed
    * @throws \GlpitestSQLError
    */
   static function getMetademandTask_TaskId($metademands_id) {
      global $DB;

      $return = [];

      $query  = "SELECT `glpi_plugin_metademands_metademandtasks`.`plugin_metademands_tasks_id` as tasks_id
               FROM `glpi_plugin_metademands_metademandtasks`
               WHERE `glpi_plugin_metademands_metademandtasks`.`plugin_metademands_metademands_id` = " . $metademands_id;
      $result = $DB->query($query);

      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $return['tasks_id'][] = $data['tasks_id'];
         }
      }
      return $return['tasks_id'];
   }

   /**
    * @param       $metademands_id
    * @param array $id_found
    *
    * @return array
    * @throws \GlpitestSQLError
    */
   static function getAncestorOfMetademandTask($metademands_id, $id_found = []) {
      global $DB;

      $metademandtask = new self();

      // Get next elements
      $query  = "SELECT `glpi_plugin_metademands_tasks`.`plugin_metademands_metademands_id` as parent_metademands_id,
                       `glpi_plugin_metademands_tasks`.`id` as tasks_id
          FROM `glpi_plugin_metademands_tasks`
          LEFT JOIN `glpi_plugin_metademands_metademandtasks`
              ON (`glpi_plugin_metademands_metademandtasks`.`plugin_metademands_tasks_id` = `glpi_plugin_metademands_tasks`.`id`)
          WHERE `glpi_plugin_metademands_metademandtasks`.`plugin_metademands_metademands_id` = '$metademands_id'";
      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $id_found[] = $data['parent_metademands_id'];
            $id_found   = $metademandtask->getAncestorOfMetademandTask($data['parent_metademands_id'], $id_found);
         }
      }

      return $id_found;
   }

   function post_deleteFromDB() {
      $metademands_id = $this->fields['plugin_metademands_metademands_id'];

      // list of parents
      $metademands_parent = PluginMetademandsMetademandTask::getAncestorOfMetademandTask($metademands_id);

      $field  = new PluginMetademandsField();
      $fields = $field->find(['type' => 'parent_field', 'plugin_metademands_metademands_id' => $metademands_id]);

      //delete of the metademand fields in the present child requests as father fields
      foreach ($fields as $data) {
         if ($field->getFromDB($data['parent_field_id'])) {
            if (!in_array($field->fields['plugin_metademands_metademands_id'], $metademands_parent)) {
               $field->delete(['id' => $field->getID()]);
            }
         }
      }
   }
}