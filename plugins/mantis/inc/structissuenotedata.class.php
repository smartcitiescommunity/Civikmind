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

class PluginMantisStructissuenotedata {

   /**
    * The id
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var integer
    */
   public $id;

   /**
    * The reporter
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var MantisStructAccountData
    */
   public $reporter;

   /**
    * The text
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var string
    */
   public $text;

   /**
    * The view_state
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var MantisStructObjectRef
    */
   public $view_state;

   /**
    * The date_submitted
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var dateTime
    */
   public $date_submitted;

   /**
    * The last_modified
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var dateTime
    */
   public $last_modified;

   /**
    * The time_tracking
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var integer
    */
   public $time_tracking;

   /**
    * The note_type
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var integer
    */
   public $note_type;

   /**
    * The note_attr
    * Meta information extracted from the WSDL
    * - minOccurs : 0
    *
    * @var string
    */
   public $note_attr;

   /**
    * Constructor method for IssueNoteData
    *
    * @see parent::__construct()
    * @param integer $_id
    * @param MantisStructAccountData $_reporter
    * @param string $_text
    * @param MantisStructObjectRef $_view_state
    * @param dateTime $_date_submitted
    * @param dateTime $_last_modified
    * @param integer $_time_tracking
    * @param integer $_note_type
    * @param string $_note_attr
    * @return MantisStructIssueNoteData
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
    * Get reporter value
    *
    * @return MantisStructAccountData|null
    */
   public function getReporter() {
      return $this->reporter;
   }

   /**
    * Set reporter value
    *
    * @param MantisStructAccountData $_reporter the reporter
    * @return MantisStructAccountData
    */
   public function setReporter($_reporter) {
      return ($this->reporter = $_reporter);
   }

   /**
    * Get text value
    *
    * @return string|null
    */
   public function getText() {
      return $this->text;
   }

   /**
    * Set text value
    *
    * @param string $_text the text
    * @return string
    */
   public function setText($_text) {
      return ($this->text = $_text);
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
    * Get date_submitted value
    *
    * @return dateTime|null
    */
   public function getDate_submitted() {
      return $this->date_submitted;
   }

   /**
    * Set date_submitted value
    *
    * @param dateTime $_date_submitted the date_submitted
    * @return dateTime
    */
   public function setDate_submitted($_date_submitted) {
      return ($this->date_submitted = $_date_submitted);
   }

   /**
    * Get last_modified value
    *
    * @return dateTime|null
    */
   public function getLast_modified() {
      return $this->last_modified;
   }

   /**
    * Set last_modified value
    *
    * @param dateTime $_last_modified the last_modified
    * @return dateTime
    */
   public function setLast_modified($_last_modified) {
      return ($this->last_modified = $_last_modified);
   }

   /**
    * Get time_tracking value
    *
    * @return integer|null
    */
   public function getTime_tracking() {
      return $this->time_tracking;
   }

   /**
    * Set time_tracking value
    *
    * @param integer $_time_tracking the time_tracking
    * @return integer
    */
   public function setTime_tracking($_time_tracking) {
      return ($this->time_tracking = $_time_tracking);
   }

   /**
    * Get note_type value
    *
    * @return integer|null
    */
   public function getNote_type() {
      return $this->note_type;
   }

   /**
    * Set note_type value
    *
    * @param integer $_note_type the note_type
    * @return integer
    */
   public function setNote_type($_note_type) {
      return ($this->note_type = $_note_type);
   }

   /**
    * Get note_attr value
    *
    * @return string|null
    */
   public function getNote_attr() {
      return $this->note_attr;
   }

   /**
    * Set note_attr value
    *
    * @param string $_note_attr the note_attr
    * @return string
    */
   public function setNote_attr($_note_attr) {
      return ($this->note_attr = $_note_attr);
   }

   /**
    * Method called when an object has been exported with var_export() functions
    * It allows to return an object instantiated with the values
    *
    * @see MantisWsdlClass::__set_state()
    * @uses MantisWsdlClass::__set_state()
    * @param array $_array the exported values
    * @return MantisStructIssueNoteData
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
