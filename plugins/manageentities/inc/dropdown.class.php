<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
 * @brief
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginManageentitiesDropdown extends Dropdown {

   static $rightname = 'plugin_manageentities';

   //Empty value displayed in a dropdown
   const EMPTY_VALUE = '-----';

   /**
    * Dropdown numbers
    *
    * @param $myname          select name
    * @param $options   array of additionnal options :
    *     - value              default value (default 0)
    *     - rand               random value
    *     - min                min value (default 0)
    *     - max                max value (default 100)
    *     - step               step used (default 1)
    *     - toadd     array    of values to add at the beginning
    *     - unit      string   unit to used
    *     - display   boolean  if false get string
    *     - width              specific width needed (default 80%)
    *     - on_change string / value to transmit to "onChange"
    *     - used      array / Already used items ID: not to display in dropdown (default empty)
    **@since version 0.84
    *
    */
   static function showNumber($myname, $options = []) {
      global $CFG_GLPI;

      $p['value']     = 0;
      $p['rand']      = mt_rand();
      $p['min']       = 0;
      $p['max']       = 100;
      $p['step']      = 1;
      $p['toadd']     = [];
      $p['unit']      = '';
      $p['display']   = true;
      $p['width']     = '';
      $p['on_change'] = '';
      $p['used']      = [];

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }
      if (($p['value'] < $p['min']) && !isset($p['toadd'][$p['value']])) {
         $p['value'] = $p['min'];
      }

      $field_id = Html::cleanId("dropdown_" . $myname . $p['rand']);
      if (!isset($p['toadd'][$p['value']])) {
         $valuename = self::getValueWithUnit($p['value'], $p['unit']);
      } else {
         $valuename = $p['toadd'][$p['value']];
      }
      $param = ['value'     => $p['value'],
                'valuename' => $valuename,
                'width'     => $p['width'],
                'on_change' => $p['on_change'],
                'used'      => $p['used'],
                'unit'      => $p['unit'],
                'min'       => $p['min'],
                'max'       => $p['max'],
                'step'      => $p['step'],
                'toadd'     => $p['toadd']];

      $out = Html::jsAjaxDropdown($myname, $field_id,
                                  $CFG_GLPI['root_doc'] . "/plugins/manageentities/ajax/getDropdownNumber.php",
                                  $param);

      if ($p['display']) {
         echo $out;
         return $p['rand'];
      }
      return $out;
   }
}