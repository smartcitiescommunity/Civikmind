<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/


/**
 * Warning !
 *
 * This is a temporary file, which should not be used in 0.85 version of GLPI (replaced by CommonItilValidation).
 * It's a simple copy/paste of some functions present in CommonItilValidation (GLPI 0.85) used to match for
 * compatibility purpose.
 */

class PluginActivityCommonValidation extends CommonDBTM{
   // STATUS
   const NONE      = 1; // none
   const WAITING   = 2; // waiting
   const ACCEPTED  = 3; // accepted
   const REFUSED   = 4; // rejected

   /**
    * get the Ticket validation status list
    *
    * @param $withmetaforsearch  boolean (false by default)
    * @param $global             boolean (true for global status, with "no validation" option)
    *                                    (false by default)
    *
    * @return an array
    **/
   static function getAllStatusArray($withmetaforsearch = false, $global = false) {

      $tab = [self::WAITING  => __('Waiting for approval'),
         self::REFUSED  => __('Refused'),
         self::ACCEPTED => __('Granted')];
      if ($global) {
         $tab[self::NONE] = __('Not subject to approval');

         if ($withmetaforsearch) {
            $tab['can'] = __('Granted + Not subject to approval');
         }
      }

      if ($withmetaforsearch) {
         $tab['all'] = __('All');
      }
      return $tab;
   }


   /**
    * Dropdown of validation status
    *
    * @param $name          select name
    * @param $options array of possible options:
    *      - value   : default value (default waiting)
    *      - all     : boolean display all (default false)
    *      - global  : for global validation (default false)
    *      - display : boolean display or get string ? (default true)
    *
    * @return nothing (display)
    **/
   static function dropdownStatus($name, $options = []) {

      $p['value']   = self::WAITING;
      $p['global']  = false;
      $p['all']     = false;
      $p['display'] = true;

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      $tab = self::getAllStatusArray($p['all'], $p['global']);
      unset($p['all']);
      unset($p['global']);

      return Dropdown::showFromArray($name, $tab, $p);
   }


   /**
    * Get Ticket validation status Name
    *
    * @param $value status ID
    *
    * @return status
    */
   static function getStatus($value) {

      $tab = self::getAllStatusArray(true, true);
      // Return $value if not define
      return (isset($tab[$value]) ? $tab[$value] : $value);
   }


   /**
    * Get Ticket validation status Color
    *
    * @param $value status ID
    *
    * @return string
    */
   static function getStatusColor($value) {

      switch ($value) {
         case self::WAITING :
            $style = "#FFC65D";
            break;

         case self::REFUSED :
            $style = "#cf9b9b";
            break;

         case self::ACCEPTED :
            $style = "#9BA563";
            break;

         default :
            $style = "#cf9b9b";
      }
      return $style;
   }


   /**
    * @param $field
    * @param $values
    * @param $options   array
    *
    * @return return|status|string
    */
   static function getSpecificValueToDisplay($field, $values, array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'global_validation':
            return self::getStatus($values[$field]);
         case 'actiontime':
            $AllDay = PluginActivityReport::getAllDay();
            return PluginActivityReport::TotalTpsPassesArrondis($values[$field]/$AllDay)." "._n('Day', 'Days', 2);
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }


   /**
    * @param $field
    * @param $name              (default '')
    * @param $values            (default '')
    * @param $options   array
    *
    * @return nothing|return
    */
   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'status' :
            $options['value'] = $values[$field];
            return self::dropdownStatus($name, $options);
      }
      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }



   /**
    * Dropdown of validator
    *
    * @param $options   array of options
    *  - name                    : select name
    *  - id                      : ID of object > 0 Update, < 0 New
    *  - entity                  : ID of entity
    *  - right                   : validation rights
    *  - groups_id               : ID of group validator
    *  - users_id_validate       : ID of user validator
    *  - applyto
    *
    * @return nothing (display)
    **/
   static function dropdownValidator(array $options = []) {
      global $CFG_GLPI;

      $params['name']               = '';
      $params['id']                 = 0;
      $params['entity']             = $_SESSION['glpiactive_entity'];
      $params['right']              = ['validate_request', 'validate_incident'];
      $params['groups_id']          = 0;
      $params['users_id_validate']  = [];
      $params['applyto']            = 'show_validator_field';

      foreach ($options as $key => $val) {
         $params[$key] = $val;
      }

      $types = [0       => Dropdown::EMPTY_VALUE,
         'user'  => __('User'),
         'group' => __('Group')];

      $type  = '__VALUE__';
      if (!empty($params['users_id_validate'])) {
         $type = 'list_users';
      }

      if ($params['id'] > 0) {
         unset($types['group']);
      }
      $rand = Dropdown::showFromArray("validatortype", $types, ['value' => $type]);

      if ($params['id'] > 0) {
         $params['validatortype'] = $type;
         Ajax::updateItem($params['applyto'], $CFG_GLPI["root_doc"]."/ajax/dropdownValidator.php",
            $params);
      }
      $params['validatortype'] = '__VALUE__';
      Ajax::updateItemOnSelectEvent("dropdown_validatortype$rand", $params['applyto'],
         $CFG_GLPI["root_doc"]."/ajax/dropdownValidator.php", $params);

      if (!isset($options['applyto'])) {
         echo "<br><span id='".$params['applyto']."'>&nbsp;</span>\n";
      }
   }
}
