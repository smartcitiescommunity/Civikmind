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
 * Class PluginMetademandsTicket_Field
 */
class PluginMetademandsTicket_Field extends CommonDBTM {

   public $itemtype = 'PluginMetademandsMetademand';

   static $types = ['PluginMetademandsMetademand'];

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
      return __('Wizard creation', 'metademands');
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
    * @param $parent_fields
    * @param $values
    * @param $tickets_id
    */
   function setTicketFieldsValues($parent_fields, $values, $tickets_id) {

      if (count($parent_fields)) {
         foreach ($parent_fields as $fields_id => $field) {
            $field['value'] = '';
            if (isset($values[$fields_id]) && !is_array($values[$fields_id])) {
               $field['value'] = $values[$fields_id];
            } else if (isset($values[$fields_id]) && is_array($values[$fields_id])) {
               $field['value'] = json_encode($values[$fields_id]);
            }
            $this->add(['value'                        => $field['value'],
                        'tickets_id'                   => $tickets_id,
                        'plugin_metademands_fields_id' => $fields_id]);
         }
      }
   }

   /**
    * @param $tasks_id
    * @param $parent_tickets_id
    *
    * @return bool
    * @throws \GlpitestSQLError
    */
   static function checkTicketCreation($tasks_id, $parent_tickets_id) {
      global $DB;

      $check  = [];
      $tasks  = is_array($tasks_id) ? implode(",", $tasks_id) : $tasks_id;
      $query  = "SELECT `glpi_plugin_metademands_fields`.`check_value`,
                       `glpi_plugin_metademands_fields`.`type`,
                       `glpi_plugin_metademands_fields`.`plugin_metademands_tasks_id`,
                       `glpi_plugin_metademands_tickets_fields`.`plugin_metademands_fields_id`,
                       `glpi_plugin_metademands_tickets_fields`.`value` as field_value
               FROM `glpi_plugin_metademands_tickets_fields`
               RIGHT JOIN `glpi_plugin_metademands_fields`
                  ON (`glpi_plugin_metademands_fields`.`id` = `glpi_plugin_metademands_tickets_fields`.`plugin_metademands_fields_id`)
               AND `glpi_plugin_metademands_tickets_fields`.`tickets_id` = " . $parent_tickets_id;
      $result = $DB->query($query);

      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {

            $plugin_metademands_tasks_id = PluginMetademandsField::_unserialize($data['plugin_metademands_tasks_id']);
            $check_values = PluginMetademandsField::_unserialize($data['check_value']);
            if(is_array($tasks_id)) {
               foreach ($tasks_id as $task) {
                  if (is_array($plugin_metademands_tasks_id) && is_array($check_values) && in_array($task, $plugin_metademands_tasks_id)) {
                     foreach ($plugin_metademands_tasks_id as $key => $task_id){
                        if($task == $task_id){
                           $test    = self::isCheckValueOKFieldsLinks(PluginMetademandsField::_unserialize($data['field_value']), $check_values[$key], $data['type']);
                           $check[] = ($test == false) ? 0 : 1;
                        }
                     }

                  }
               }
            }else{
               if (is_array($plugin_metademands_tasks_id) && is_array($check_values) && !empty($check_values) && in_array($tasks_id, $plugin_metademands_tasks_id)) {
                  foreach ($plugin_metademands_tasks_id as $key => $task_id) {
                     if ($tasks_id == $task_id) {
                        $test    = self::isCheckValueOKFieldsLinks(PluginMetademandsField::_unserialize($data['field_value']), $check_values[$key], $data['type']);
                        $check[] = ($test == false) ? 0 : 1;
                     }
                  }

               }
            }

         }
      }

      if (in_array(1, $check)) {
         return true;
      }else if(in_array(0, $check)){
         return false;
      }

      return true;
   }

   /**
    * @param $value
    * @param $check_values
    * @param $type
    *
    * @return bool
    */
   static function isCheckValueOK($value, $check_values, $type) {

      $check_values = PluginMetademandsField::_unserialize($check_values);
      if (isset($check_values) && is_array($check_values)) {
         foreach ($check_values as $check) {
            $check_value = $check;
         }
         if (isset($check_value)) {
            switch ($type) {
               case 'yesno':
               case 'dropdown':
               case 'dropdown_object':
               case 'dropdown_meta':
                  if (($check_value == PluginMetademandsField::$not_null || $check_value == 0) && empty($value)) {
                     return false;
                  } else if ($check_value != $value
                             && ($check_value != PluginMetademandsField::$not_null && $check_value != 0)) {
                     return false;
                  }
                  break;
               case 'radio':
                  if (empty($value) && $value != 0) {
                     return false;
                  } else if ($check_value != $value) {
                     return false;
                  }
                  break;

               case 'checkbox':
                  if (!empty($value)) {
                     $ok = false;
                     if($check_value == -1){
                        $ok = true;
                     }
                     if (is_array($value)) {
                        foreach ($value as $key => $v) {
                           //                     if ($key != 0) {
                           if ($check_value == $key) {
                              $ok = true;
                           }
                           //                     }
                        }
                     } else if (is_array(json_decode($value, true))) {
                        foreach (json_decode($value, true) as $key => $v) {
                           //                     if ($key != 0) {
                           if ($check_value == $key) {
                              $ok = true;
                           }
                           //                     }
                        }
                     }
                     if (!$ok) {
                        return false;
                     }
                  } else {
                     return false;
                  }
                  break;
               case 'link':
                  if ((($check_value == PluginMetademandsField::$not_null || $check_value == 0) && empty($value))) {
                     return false;
                  }
                  break;
               case 'text':
               case 'textarea':
                  if (($check_value == 2 && $value != "")) {
                     return false;
                  } elseif ($check_value == 1 && $value == "") {
                     return false;
                  }
                  break;
               case 'dropdown_multiple':
                  if (empty($value)) {
                     $value = [];
                  }
                  if ($check_value == PluginMetademandsField::$not_null && is_array($value) && count($value) == 0) {
                     return false;
                  }
                  break;

               default:
                  if ($check_value == PluginMetademandsField::$not_null && empty($value)) {
                     return false;
                  }
                  break;
            }
         }
      }
      return true;
   }

   static function isCheckValueOKFieldsLinks($value, $check_value, $type) {



         if (isset($check_value)) {
            switch ($type) {
               case 'yesno':
               case 'dropdown':
               case 'dropdown_object':
               case 'dropdown_meta':
                  if (($check_value == PluginMetademandsField::$not_null || $check_value == 0) && empty($value)) {
                     return false;
                  } else if ($check_value != $value
                             && ($check_value != PluginMetademandsField::$not_null && $check_value != 0)) {
                     return false;
                  }
                  break;
               case 'radio':
                  if (is_null($value)) {
                     return false;
                  } else if ($check_value !== strval($value)) {
                     return false;
                  }
                  break;

               case 'checkbox':
                  if (!empty($value)) {
                     $ok = false;
                     if($check_value == -1){
                        $ok = true;
                     }
                     if (is_array($value)) {
                        foreach ($value as $key => $v) {
                           //                     if ($key != 0) {
                           if ($check_value == $key) {
                              $ok = true;
                           }
                           //                     }
                        }
                     } else if (is_array(json_decode($value, true))) {
                        foreach (json_decode($value, true) as $key => $v) {
                           //                     if ($key != 0) {
                           if ($check_value == $key) {
                              $ok = true;
                           }
                           //                     }
                        }
                     }
                     if (!$ok) {
                        return false;
                     }
                  } else {
                     return false;
                  }
                  break;
               case 'link':
                  if ((($check_value == PluginMetademandsField::$not_null || $check_value == 0) && empty($value))) {
                     return false;
                  }
                  break;
               case 'text':
               case 'textarea':
                  if (($check_value == 2 && $value != "")) {
                     return false;
                  } elseif ($check_value == 1 && $value == "") {
                     return false;
                  }
                  break;
               case 'dropdown_multiple':
                  if (empty($value)) {
                     $value = [];
                  }
                  if ($check_value == 0 && is_array($value) && count($value) == 0) {
                     return false;
                  }
                  break;

               default:
                  if ($check_value == PluginMetademandsField::$not_null && empty($value)) {
                     return false;
                  }
                  break;
            }
         }

      return true;
   }

}
