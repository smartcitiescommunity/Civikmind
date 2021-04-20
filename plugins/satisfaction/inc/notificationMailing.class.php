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
 * Class PluginResourcesNotification
 */
class PluginSatisfactionNotificationMailing extends CommonDBTM {

   static $rightname = 'plugin_satisfaction';

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {

      return __('Notification satisfaction reminder', 'satisfaction');
   }

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    **/
   static function canCreate() {
      return Session::haveRight(self::$rightname, [CREATE, UPDATE, DELETE]);
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
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * Function list items
    *
    * @param type $ID
    */
   public function listItems($ID) {

      $rand = mt_rand();

      // Start
      $start = 0;
      if (isset($_REQUEST["start"])) {
         $start = $_REQUEST["start"];
      }

      // Get data
      $data = $this->getItems($ID, $start);
      if (!empty($data)) {
         echo "<div class='center'>";
         $dbu = new DbUtils();
         Html::printAjaxPager(self::getTypeName(2), $start, $dbu->countElementsInTable($this->getTable()));
         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr class='tab_bg_1'>";
         echo "<th colspan='3'>".self::getTypeName(1)."</th>";
         echo "</tr>";
         echo "<tr class='tab_bg_1'>";
         echo "<th>".__('User')."</th>";
         echo "<th>".__('Date')."</th>";
         echo "<th>".__('Type')."</th>";
         echo "</tr>";

         $dbu = new DbUtils();

         foreach ($data as $field) {
            echo "<tr class='tab_bg_2'>";
//            // User
//            echo "<td>".$dbu->formatUserName($field['users_id'], $field['name'], $field['realname'], $field['firstname'])."</td>";
//            echo "<td>".Html::convDateTime($field['date_mod'])."</td>";
//            echo "<td>".self::getStatus($field['type'])."</td>";
//            echo "</tr>";
            // Ticket
            // TODO
         }
         echo "</table>";
         echo "</div>";
      }
   }

   /**
    * Function get items for resource
    *
    * @global type $DB
    * @param type $recordmodels_id
    * @param type $start
    * @return type
    */
   function getItems($resources_id, $start = 0) {
      global $DB;

      $output = [];

      $query = "SELECT `".$this->getTable()."`.`id`, 
                       `glpi_users`.`realname`,
                       `glpi_users`.`firstname`,
                       `glpi_users`.`name`,
                       `".$this->getTable()."`.`type`,
                       `".$this->getTable()."`.`users_id`,
                       `".$this->getTable()."`.`date_mod`,
                       `".$this->getTable()."`.`plugin_resources_resources_id`
          FROM ".$this->getTable()."
          LEFT JOIN `glpi_users` ON (`".$this->getTable()."`.`users_id` = `glpi_users`.`id`)
          WHERE `".$this->getTable()."`.`plugin_resources_resources_id` = ".Toolbox::cleanInteger($resources_id)."
          ORDER BY `".$this->getTable()."`.`date_mod` DESC
          LIMIT ".intval($start).",".intval($_SESSION['glpilist_limit']);

      $result = $DB->query($query);
      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $output[$data['id']] = $data;
         }
      }

      return $output;
   }

   /**
    * Function get the Status
    *
    * @return an array
    */
   static function getStatus($value) {
      $data = self::getAllStatusArray();
      return $data[$value];
   }

   /**
    * Get the SNMP Status list
    *
    * @return an array
    */
   static function getAllStatusArray() {

      // To be overridden by class
      $tab = ['report'  => __('Resource creation', 'resources'),
         'other'   => __('Other', 'resources')];

      return $tab;
   }

   /**
    * if profile deleted
    *
    * @param \Ticket $resource
    */
   static function purgeNotification(Ticket $ticket) {
      $temp = new self();
      $temp->deleteByCriteria(['tickets_id' => $ticket->getField("id")]);
   }
}

