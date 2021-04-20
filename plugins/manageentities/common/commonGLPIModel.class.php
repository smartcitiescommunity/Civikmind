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

if (!defined('GLPI_ROOT')) {
   die ("Sorry. You can't access directly to this file");
}

/**
 * Enumeration like for common status
 */
abstract class CommonGLPIStatus {
   const ADDED       = 'added';
   const NOT_ADDED   = 'not_added';
   const UPDATED     = 'updated';
   const NOT_UPDATED = 'not_updated';
   const DELETED     = 'deleted';
   const NOT_DELETED = 'not_deleted';
   const SAVED       = 'saved';
   const NOT_SAVED   = 'not_saved';
}

/**
 * Enumeration like for common database operations
 */
abstract class CommonGLPIDBOperation {
   const ADD    = 'add';
   const READ   = 'read';
   const UPDATE = 'update';
   const DELETE = 'delete';
}

/**
 * Enumeration like for common database operations
 */
abstract class CommonGLPIMessages {
   const MESSAGE_INFO  = 0;
   const MESSAGE_ERROR = 1;
}

/**
 * Enumeration like for common errors
 */
abstract class CommonGLPIErrors {
   const ERROR_ADD    = 'error_add';
   const ERROR_READ   = 'error_read';
   const ERROR_UPDATE = 'error_update';
   const ERROR_DELETE = 'error_delete';
   const ERROR_ALL    = 'error_all';
}

/**
 * Enumeration like for common element type
 */
abstract class CommonGLPIElementType {

}

/**
 * Common model for GLPI classes
 */
abstract class CommonGLPIModel {
   public $messages;               // Message list
   public $url;                    // Controller URL
   public $errors;                 // Array containing errors code if there is some


   /**
    * Private constructor. Initialise the contextual variables.
    *
    * @param $url : URL of the listener
    * @param $messages : array containing different messages
    * @param $errors : array containing error codes
    */
   private function __construct($url, $messages, $errors) {
      $this->url      = $url;
      $this->messages = $messages;
      $this->errors   = $errors;
   }

   /* ------------------------------------------------------------------------------------
    *    Abstract functions
    * ------------------------------------------------------------------------------------ */

   public abstract function serializeInSession();


   /* ------------------------------------------------------------------------------------
    *    Utility
    * ------------------------------------------------------------------------------------ */

   /**
    * This function allows to get a message from the message variable.
    *
    * @param String $message : message to get
    * @param String $type : if set, return the message from the given type
    */
   public function getMessage($message, $type = null) {
      $messages = $this->messages;
      if ($type == null) {
         if (isset($messages[$message])) {
            return $messages[$message];
         }
      } else {
         if (isset($messages[$message][$type])) {
            return $messages[$message][$type];
         }
      }

      return $message . " -> NOT FOUND";
   }

   /**
    * This function allows to know the current state (error or not) of the model in terms of an
    * error type. It checks from the array variable errors if is set the error in temrs of the
    * error type. For instance :
    *    $errors = array(
    *       CommonGLPIErrors::ERROR_ENTITY   => CommonGLPIErrors::ERROR_ADD,
    * // In case of an error when trying to insert a new entity CommonGLPIErrors::ERROR_CONTRACT =>
    * CommonGLPIErrors::ERROR_UPDATE,                    // In case of an error when trying to
    * update a contract CommonGLPIErrors::ERROR_PERIOD   => array (CommonGLPIErrors::ERROR_UPDATE
    * => 2),       // In case of an error when trying to update the period
    *                                                                                              //
    * identified by the id number 2
    *    )
    *
    *
    *
    * @param CommonGLPIErrors $error : the error to get
    * @param CommonGLPIErrors $type : the type of error to get
    * @param int              $opt : if set, check if a special element of the array is on error
    */
   public function isOnError($error, $type, $opt = null) {
      $isOnError = false;
      switch ($type) {
         case CommonGLPIErrors::ERROR_ALL:
            if ($opt != null) {
               $isOnError = isset($this->errors[$error][CommonGLPIErrors::ERROR_ADD][$opt]) && !empty($this->errors[$error][CommonGLPIErrors::ERROR_ADD][$opt]) ||
                            isset($this->errors[$error][CommonGLPIErrors::ERROR_UPDATE][$opt]) && !empty($this->errors[$error][CommonGLPIErrors::ERROR_UPDATE][$opt]);
            } else {
               $isOnError = isset($this->errors[$error][CommonGLPIErrors::ERROR_ADD]) && !empty($this->errors[$error][CommonGLPIErrors::ERROR_ADD]) ||
                            isset($this->errors[$error][CommonGLPIErrors::ERROR_UPDATE]) && !empty($this->errors[$error][CommonGLPIErrors::ERROR_UPDATE]);
            }
            break;
         case CommonGLPIErrors::ERROR_UPDATE:
         case CommonGLPIErrors::ERROR_ADD:
            if ($opt != null) {
               $isOnError = isset($this->errors[$error][$type][$opt]) && !empty($this->errors[$error][$type][$opt]);
            } else {
               $isOnError = isset($this->errors[$error][$type]) && !empty($this->errors[$error][$type]);
            }
            break;

         default:
            $isOnError = true;
            break;
      }

      return $isOnError;
   }

   /**
    * This function allows to add an error to the model.
    *
    * @param CommonGLPIError $id : the error to add
    * @param CommonGLPIError $type : the type of error to add
    * @param boolean         $val : the value (true for an error, otherwise false)
    * @param int             $opt : if set, used to specified an error on a special element
    *    identified by the id $opt
    */
   public function addError($id, $type, $val, $opt = null) {
      $this->errors = [];
      if ($opt == null) {
         $this->errors[$id][$type] = $val;
      } else {
         $this->errors[$id][$type][$opt] = $val;
      }

      $this->serializeInSession();
   }

   /**
    * This function allows to delete an error from the model.
    *
    * @param CommonGLPIError $id : the error to delete
    * @param CommonGLPIError $type : the error type to delete
    * @param int             $opt : if set, delete the error of the special element identified by
    *    the id $opt
    */
   public function deleteError($id, $type, $opt = null) {
      if (isset($this->errors[$id][$type])) {
         if ($opt != null) {
            if (isset($this->errors[$id][$type][$opt])) {
               unset($this->errors[$id][$type][$opt]);
            }
         } else {
            unset($this->errors[$id][$type]);
         }
         $this->serializeInSession();
      }
   }


   /* ------------------------------------------------------------------------------------
    *    Getters and setters
    * ------------------------------------------------------------------------------------ */

   /**
    * This function allows to get the errors array of the model.
    *
    * @return array : array of errors
    */
   public function getErrors() {
      return $this->errors;
   }

   /**
    * This function allows to set the error array of the model
    *
    * @param array $value : the new array of errors
    */
   public function setErrors($value) {
      $this->errors = $value;
      $this->serializeInSession();
   }

   /**
    * This function allows to get the URL of the listene
    *
    * @return String : the URL of the listener
    */
   public function getUrl() {
      return $this->url;
   }

   /**
    * This function allows to se the URL of the listener
    *
    * @param String $value : the URL of the listener
    */
   public function setUrl($value) {
      $this->url = $value;
      $this->serializeInSession();
   }

   /**
    * This function allows to get the array of messages from the model.
    *
    * @return array $messages : the array of messages
    *
    */
   public function getMessages() {
      return $this->messages;
   }

   /**
    * This function allows to set the array of messages of the model.
    * Moreover this function store the new array of messages in session using the function
    * serializeInSession()
    *
    * @param array $value : the array of messages
    */
   public function setMessages($value) {
      $this->messages = $value;
      $this->serializeInSession();
   }

}