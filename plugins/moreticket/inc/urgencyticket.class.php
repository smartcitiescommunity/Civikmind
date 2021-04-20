<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 moreticket plugin for GLPI
 Copyright (C) 2013-2016 by the moreticket Development Team.

 https://github.com/InfotelGLPI/moreticket
 -------------------------------------------------------------------------

 LICENSE

 This file is part of moreticket.

 moreticket is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 moreticket is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with moreticket. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginMoreticketUrgencyTicket
 */
class PluginMoreticketUrgencyTicket extends CommonDBTM {

   static $types     = ['Ticket'];
   var    $dohistory = true;
   static $rightname = "plugin_moreticket_justification";

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    **/
   static function canCreate() {

      if (static::$rightname) {
         return Session::haveRight(static::$rightname, UPDATE);
      }
      return false;
   }

   /**
    * Check the mandatory values of forms
    *
    * @param      $values
    * @param bool $add
    *
    * @return bool
    */
   static function checkMandatory($values, $add = false) {
      $checkKo = [];

      $mandatory_fields                  = [];
      $mandatory_fields['justification'] = __('Justification', 'moreticket');

      $msg = [];

      foreach ($mandatory_fields as $key => $value) {
         if (!array_key_exists($key, $values) && empty($values[$key])) {
            $msg[]     = $value;
            $checkKo[] = 1;
         }
      }

      foreach ($values as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               $msg[]     = $mandatory_fields[$key];
               $checkKo[] = 1;
            }
         }
         $_SESSION['glpi_plugin_moreticket_urgency'][$key] = $value;
      }

      if (in_array(1, $checkKo)) {
         if (!$add) {
            $errorMessage = __('Urgency ticket cannot be saved', 'moreticket') . "<br>";
         } else {
            $errorMessage = __('Ticket cannot be saved', 'moreticket') . "<br>";
         }

         if (count($msg)) {
            $errorMessage .= _n('Mandatory field', 'Mandatory fields', 2) . " : " . implode(', ', $msg);
         }

         Session::addMessageAfterRedirect($errorMessage, false, ERROR);

         return false;
      }

      return true;
   }

   /**
    * Print the urgency ticket form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return Nothing (display)
    * */
   function showForm($ID, $options = []) {

      // validation des droits
      if (!$this->canView()) {
         return false;
      }

      if ($ID > 0) {
         if (!$this->fields = self::getUrgencyTicketFromDB($ID)) {
            $this->getEmpty();
         }
      } else {
         // Create item
         $this->getEmpty();
      }

      // If values are saved in session we retrieve it
      if (isset($_SESSION['glpi_plugin_moreticket_urgency'])) {
         foreach ($_SESSION['glpi_plugin_moreticket_urgency'] as $key => $value) {
            switch ($key) {
               case 'justification':
                  $this->fields[$key] = stripslashes($value);
                  break;
               default :
                  $this->fields[$key] = $value;
                  break;
            }
         }
      }

      unset($_SESSION['glpi_plugin_moreticket_urgency']);

      echo "<div class='spaced' id='moreticket_urgency_ticket'>";
      echo "</br>";
      echo "<table align='left' class='moreticket_waiting_ticket' id='cl_menu'>";
      echo "<tr><td>";
      echo __('Justification', 'moreticket');
      echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;</br>";
      echo "</td></tr>";
      echo "<tr><td>";
      echo "<textarea cols='30' rows='5' name='justification'>" . $this->fields['justification'] . "</textarea>";
      echo "</td></tr>";
      echo "</table>";
      echo "</div>";
   }

   /**
    * Get last urgencyTicket
    *
    * @param       $tickets_id
    * @param array $options
    *
    * @return array|bool|mixed
    */
   static function getUrgencyTicketFromDB($tickets_id, $options = []) {
      $dbu = new DbUtils();
      if (sizeof($options) == 0) {
         $data_Urgency = $dbu->getAllDataFromTable("glpi_plugin_moreticket_urgencytickets",
                                              ['tickets_id' => $tickets_id]);
      } else {
         $data_Urgency = $dbu->getAllDataFromTable("glpi_plugin_moreticket_urgencytickets",
                                                   ['tickets_id' => $tickets_id],
                                              false,
                                              ' LIMIT ' . intval($options['start']) . "," . intval($options['limit']));
      }

      if (sizeof($data_Urgency) > 0) {
         if (sizeof($options) == 0) {
            $data_Urgency = reset($data_Urgency);
         }

         return $data_Urgency;
      }

      return false;
   }

   /**
    * @param $item
    */
   static function preUpdateUrgencyTicket($item) {
      $config = new PluginMoreticketConfig();
      if ($config->useUrgency()) {
         $urgency_ticket = new self();

         // Then we add tickets informations
         if (isset($item->fields['id'])
             && isset($item->fields['urgency'])
             && isset($item->input['urgency'])
             && isset($item->input['justification'])
         ) {

            $urgency_ids = $config->getUrgency_ids();

            if (in_array($item->input['urgency'], $urgency_ids)) {

               if (self::checkMandatory($item->input)) {
                  if ($urgency_ticket_data = self::getUrgencyTicketFromDB($item->fields['id'])) {
                     // UPDATE
                     $urgency_ticket->update(['id'            => $urgency_ticket_data['id'],
                                                   'justification' => $item->input['justification']]);

                  } else {
                     // ADD
                     // Then we add tickets informations
                     if ($urgency_ticket->add(['justification' => (isset($item->input['justification'])) ? $item->input['justification'] : "",
                                                    'tickets_id'    => $item->fields['id']])
                     ) {

                        unset($_SESSION['glpi_plugin_moreticket_urgency']);
                     }
                  }

               } else {
                  unset($item->input['urgency']);
               }

            }
         }
      }
   }

   /**
    * @param $item
    */
   static function postUpdateUrgencyTicket($item) {
      $config = new PluginMoreticketConfig();

      if ($config->useUrgency()) {
         $urgency_ticket = new self();
         // Then we add tickets informations
         if (isset($item->fields['id'])) {
            if (isset($item->oldvalues['urgency']) && (isset($item->input['urgency']))
                && $item->input['urgency'] != $item->oldvalues['urgency']
            ) {

               $urgency_ticket_data = self::getUrgencyTicketFromDB($item->fields['id']);

               $urgency_ids = $config->getUrgency_ids();

               if (!in_array($item->input['urgency'], $urgency_ids)) {
                  $urgency_ticket->update(['id'            => $urgency_ticket_data['id'],
                                                'justification' => ""]);
               }

               unset($_SESSION['glpi_plugin_moreticket_urgency']);
            }
         }
      }
   }

   /**
    * Hook done on before add ticket - checkMandatory
    *
    * @param $item
    *
    * @return bool
    */
   static function preAddUrgencyTicket($item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      $config = new PluginMoreticketConfig();
      if ($config->useUrgency()) {
         $urgency_ids = $config->getUrgency_ids();
         // Then we add tickets informations
         if (isset($item->input['urgency']) && in_array($item->input['urgency'], $urgency_ids)) {
            if (!self::checkMandatory($item->input, true)) {
               $_SESSION['saveInput'][$item->getType()] = $item->input;
               $item->input                             = [];
            }

         }
      }
      return true;
   }

   /**
    * Hook done on after add ticket - add urgencytickets
    *
    * @param $item
    *
    * @return bool
    */
   static function postAddUrgencyTicket($item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      $config = new PluginMoreticketConfig();
      if ($config->useUrgency()) {
         $urgency_ticket = new self();
         $urgency_ids    = $config->getUrgency_ids();
         // Then we add tickets informations
         if (in_array($item->input['urgency'], $urgency_ids)) {
            if (self::checkMandatory($item->input)) {
               // Then we add tickets informations
               if ($urgency_ticket->add(['justification' => $item->input['justification'],
                                              'tickets_id'    => $item->fields['id']])
               ) {

                  unset($_SESSION['glpi_plugin_moreticket_urgency']);
               }
            } else {
               $item->input['id']                       = $item->fields['id'];
               $_SESSION['saveInput'][$item->getType()] = $item->input;
               unset($item->input['urgency']);
            }
         }
      }
      return true;
   }

   /**
    * Type than could be linked to a typo
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
    * */
   static function getTypes($all = false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      $dbu = new DbUtils();
      foreach ($types as $key => $type) {
         if (!($item = $dbu->getItemForItemtype($type))) {
            continue;
         }

         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }

}
