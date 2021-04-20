<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of mantis.
 *
 * mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2018 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/mantis
 * @link      https://pluginsglpi.github.io/mantis/
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMantisStructcustomField {

   /**
    * The field
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var MantisStructObjectRef
    */
   public $field;

   /**
    * The value
    * Meta informations extracted from the WSDL
    * - minOccurs : 0
    *
    * @var string
    */
   public $value;

   /**
    * Get field value
    *
    * @return MantisStructObjectRef|null
    */
   public function getField() {
      return $this->field;
   }

   /**
    * Set field value
    *
    * @param MantisStructObjectRef $_field the field
    * @return MantisStructObjectRef
    */
   public function setField($_field) {
      return ($this->field = $_field);
   }

   /**
    * Get value value
    *
    * @return string|null
    */
   public function getValue() {
      return $this->value;
   }

   /**
    * Set value value
    *
    * @param string $_value the value
    * @return string
    */
   public function setValue($_value) {
      return ($this->value = $_value);
   }
}