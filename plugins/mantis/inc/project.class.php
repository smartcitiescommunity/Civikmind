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

class PluginMantisProject {

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
    * The status
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var MantisStructObjectRef
    */
   public $status;

   /**
    * The enabled
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var boolean
    */
   public $enabled;

   /**
    * The view_state
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var MantisStructObjectRef
    */
   public $view_state;

   /**
    * The access_min
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var MantisStructObjectRef
    */
   public $access_min;

   /**
    * The file_path
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var string
    */
   public $file_path;

   /**
    * The description
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var string
    */
   public $description;

   /**
    * The subprojects
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    * - from schema : var/wsdltophp.com/storage/wsdls/a80caff3c8dd52f94a68432974b9ab45/wsdl.xml
    *
    * @var Array
    */
   public $subprojects;

   /**
    * The inherit_global
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var boolean
    */
   public $inherit_global;

   /**
    * Constructor method for ProjectData
    *
    * @see parent::__construct()
    * @param integer $_id
    * @param string $_name
    * @param MantisStructObjectRef $_status
    * @param boolean $_enabled
    * @param MantisStructObjectRef $_view_state
    * @param MantisStructObjectRef $_access_min
    * @param string $_file_path
    * @param string $_description
    * @param Array $_subprojects
    * @param boolean $_inherit_global
    * @return MantisStructProjectData
    */
   public function __construct() {
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
    * Get status value
    *
    * @return MantisStructObjectRef|null
    */
   public function getStatus() {
      return $this->status;
   }

   /**
    * Set status value
    *
    * @param MantisStructObjectRef $_status the status
    * @return MantisStructObjectRef
    */
   public function setStatus($_status) {
      return ($this->status = $_status);
   }

   /**
    * Get enabled value
    *
    * @return boolean|null
    */
   public function getEnabled() {
      return $this->enabled;
   }

   /**
    * Set enabled value
    *
    * @param boolean $_enabled the enabled
    * @return boolean
    */
   public function setEnabled($_enabled) {
      return ($this->enabled = $_enabled);
   }

   /**
    * Get view_state value
    *
    * @return MantisStructObjectRef|null
    */
   public function getView_state() {
      return $this->view_state;
   }

   /**
    * Set view_state value
    *
    * @param MantisStructObjectRef $_view_state the view_state
    * @return MantisStructObjectRef
    */
   public function setView_state($_view_state) {
      return ($this->view_state = $_view_state);
   }

   /**
    * Get access_min value
    *
    * @return MantisStructObjectRef|null
    */
   public function getAccess_min() {
      return $this->access_min;
   }

   /**
    * Set access_min value
    *
    * @param MantisStructObjectRef $_access_min the access_min
    * @return MantisStructObjectRef
    */
   public function setAccess_min($_access_min) {
      return ($this->access_min = $_access_min);
   }

   /**
    * Get file_path value
    *
    * @return string|null
    */
   public function getFile_path() {
      return $this->file_path;
   }

   /**
    * Set file_path value
    *
    * @param string $_file_path the file_path
    * @return string
    */
   public function setFile_path($_file_path) {
      return ($this->file_path = $_file_path);
   }

   /**
    * Get description value
    *
    * @return string|null
    */
   public function getDescription() {
      return $this->description;
   }

   /**
    * Set description value
    *
    * @param string $_description the description
    * @return string
    */
   public function setDescription($_description) {
      return ($this->description = $_description);
   }

   /**
    * Get subprojects value
    *
    * @return Array|null
    */
   public function getSubprojects() {
      return $this->subprojects;
   }

   /**
    * Set subprojects value
    *
    * @param Array $_subprojects the subprojects
    * @return Array
    */
   public function setSubprojects($_subprojects) {
      return ($this->subprojects = $_subprojects);
   }

   /**
    * Get inherit_global value
    *
    * @return boolean|null
    */
   public function getInherit_global() {
      return $this->inherit_global;
   }

   /**
    * Set inherit_global value
    *
    * @param boolean $_inherit_global the inherit_global
    * @return boolean
    */
   public function setInherit_global($_inherit_global) {
      return ($this->inherit_global = $_inherit_global);
   }

   /**
    * Method called when an object has been exported with var_export() functions
    * It allows to return an object instantiated with the values
    *
    * @see MantisWsdlClass::__set_state()
    * @uses MantisWsdlClass::__set_state()
    * @param array $_array the exported values
    * @return MantisStructProjectData
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
