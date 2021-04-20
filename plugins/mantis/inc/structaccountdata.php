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

class PluginMantisStructaccountdata {

   /**
    * The id
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var integer
    */
   public $id;

   /**
    * The name
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var string
    */
   public $name;

   /**
    * The real_name
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var string
    */
   public $real_name;

   /**
    * The email
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var string
    */
   public $email;

   /**
    * Constructor method for AccountData
    *
    * @see parent::__construct()
    * @param integer $_id
    * @param string $_name
    * @param string $_real_name
    * @param string $_email
    * @return MantisStructAccountData
    */
   public function __construct($_id = null, $_name = null, $_real_name = null, $_email = null) {
   }

   /**
    * Get id value
    *
    * @return integer|null
    */
   public function getId() {
      return $this->id;
   }

   /**
    * Set id value
    *
    * @param integer $_id the id
    * @return integer
    */
   public function setId($_id) {
      return ($this->id = $_id);
   }

   /**
    * Get name value
    *
    * @return string|null
    */
   public function getName() {
      return $this->name;
   }

   /**
    * Set name value
    *
    * @param string $_name the name
    * @return string
    */
   public function setName($_name) {
      return ($this->name = $_name);
   }

   /**
    * Get real_name value
    *
    * @return string|null
    */
   public function getReal_name() {
      return $this->real_name;
   }

   /**
    * Set real_name value
    *
    * @param string $_real_name the real_name
    * @return string
    */
   public function setReal_name($_real_name) {
      return ($this->real_name = $_real_name);
   }

   /**
    * Get email value
    *
    * @return string|null
    */
   public function getEmail() {
      return $this->email;
   }

   /**
    * Set email value
    *
    * @param string $_email the email
    * @return string
    */
   public function setEmail($_email) {
      return ($this->email = $_email);
   }

   /**
    * Method called when an object has been exported with var_export() functions
    * It allows to return an object instantiated with the values
    *
    * @see MantisWsdlClass::__set_state()
    * @uses MantisWsdlClass::__set_state()
    * @param array $_array the exported values
    * @return MantisStructAccountData
    */
   public static function __set_state(array $_array, $_className = __CLASS__) {
      return parent::__set_state($_array, $_className);
   }

   /**
    * Method returning the class name
    *
    * @return string __CLASS__
    */
   public function __toString() {
      return __CLASS__;
   }
}
