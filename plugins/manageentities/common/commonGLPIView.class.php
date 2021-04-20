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
 * Common model for GLPI classes
 */
abstract class CommonGLPIView {

   /**
    * Empty constructor.
    */
   private function __construct() {

   }

   /* ------------------------------------------------------------------------------------
    *    Utility
    * ------------------------------------------------------------------------------------ */

   /**
    * This function allows to add an AJAX function to the page.
    *
    * @param String $functionName : name of the function to be created
    * @param String $idDivAjax : ID of the div (or other) used for ajax purpose (where the result
    *    of treatment shoud be shown)
    * @param String $url : URL of the listener
    * @param array  $listId : list of ids of input element which have to be sent to the controller
    * @param array  $params : additionnal paramters
    *
    * @param String $additionalDiv : additionnal did id for ajax purpose
    */
   public function showJSfunction($functionName, $idDivAjax, $url, $listId, $params, $additionalDiv = null) {
      $this->showHeaderJS();
      echo "function " . $functionName . "() {\n";

      $divReturned = "";
      if ($additionalDiv != null) {
         $divReturned = $additionalDiv;
      } else {
         $divReturned = $idDivAjax;
      }

      echo Html::jsGetElementbyID($divReturned) . ".load(\n
            '" . $url . "'\n";

      echo ",{";
      $first = true;
      foreach ($listId as $key => $val) {
         if ($first) {
            $first = false;
         } else {
            echo ",";
         }
         switch ($val[0]) {
            case "checkbox":
               echo $val[1] . ":" . Html::jsGetElementbyID(Html::cleanId($key)) . ".is(':checked')";
               break;
            case "dropdown":
               echo $val[1] . ":" . Html::jsGetElementbyID(Html::cleanId($key)) . ".val()";
               break;
            case "text":
            default:
               echo $val[1] . ":" . Html::jsGetElementbyID(Html::cleanId($key)) . ".val()";
               break;
         }
      }

      foreach ($params as $key => $val) {
         if ($first) {
            $first = false;
         } else {
            echo ",";
         }
         echo $key . ":'" . $val . "'";
      }
      echo "}\n";
      echo ");";
      echo "}";
      $this->closeFormJS();
   }

   /**
    * This function allows to create a JQuery datepicker
    *
    * @param String $id : id of the input text to be transform as a datepicker
    */
   public function initDate($id) {
      $this->showHeaderJS();
      echo "$(function() {
         $( '#" . $id . "' ).flatpickr({
            dateFormat : 'd-m-Y' 
         });
      });";

      $this->closeFormJS();
   }

   /**
    * This function allows to change the value of an element
    *
    * @param String $idElement : id of the element to be change
    * @param String $title : text to be shown
    */
   public function changeBtnName($idElement, $title) {
      $this->showHeaderJS();
      echo "document.getElementById('" . $idElement . "').value='" . $title . "';";
      $this->closeFormJS();
   }


   /**
    * This function allows to remove an element
    *
    * @param String $idElement : id of the element to be change
    * @param String $title : text to be shown
    */
   public function removeBtn($idElement) {
      $this->showHeaderJS();
      echo "$('#" . $idElement . "').remove();";
      $this->closeFormJS();
   }

   /**
    * This function allows to change the action value of an element
    *
    * @param String $idElement : id of the element to be change
    * @param String $title : ths action
    */
   public function changeBtnAction($idElement, $title) {
      $this->showHeaderJS();
      echo "document.getElementById('" . $idElement . "').action='" . $title . "';";
      $this->closeFormJS();
   }


   /**
    * This function allows to remve an HTML element from the view.
    *
    * @param String $idElement : id of the HTML input element to be removed
    */
   public function removeElementFromView($idElement) {
      $this->showHeaderJS();
      echo "var element = document.getElementById('" . $idElement . "');";
      echo "var parent = element.parentElement;";
      echo "parent.removeChild(element);";
      $this->closeFormJS();
   }

   /**
    * This function allows to change the visibility of a HTML element
    *
    * @param String  $idElement :  id of the HTML element
    * @param boolean $toDisplay :  true to display the element, otherwise false
    */
   public function changeElementVisibility($idElement, $toDisplay) {
      $this->showHeaderJS();
      echo "if($('#" . $idElement . "').val()){";
      if ($toDisplay == true) {
         echo "document.getElementById('" . $idElement . "').style.visibility='visible';";
      } else {
         echo "document.getElementById('" . $idElement . "').style.visibility='hidden';";
      }
      echo "}";
      $this->closeFormJS();
   }

   /**
    * This function allows to show javascript headers, needed to javascript and ajax purpose.
    */
   public function showHeaderJS() {
      echo "\n<script type='text/javascript'>\n";
   }

   /**
    * This function close a javascript block (used after showHeaderJs() ).
    */
   public function closeFormJS() {
      echo "</script>\n";
   }

   /**
    * This function allows to set the borders of a HTML input on red (for errors purpose).
    *
    * @param String  $htmlInput : id of the HTML input
    * @param boolean $error : true to draw red borders, false tio reinitiate borders.
    * @param boolean $onJS : true if the calling method has not already sent header JS, false
    *    otherwise
    */
   public function bordersOnError($htmlInput, $error, $onJS = true) {
      if ($htmlInput != "") {
         if (!$onJS) {
            $this->showHeaderJS();
         }
         if ($error) {
            echo "$('#" . $htmlInput . "').focus();";
            echo "$('#" . $htmlInput . "').css('border' , '1px solid red');";
         } else {
            echo "$('#" . $htmlInput . "').css('border' , '');";
         }
         if (!$onJS) {
            $this->closeFormJS();
         }
      }
   }


   /* ------------------------------------------------------------------------------------
    *    Getters and setters
    * ------------------------------------------------------------------------------------ */


}