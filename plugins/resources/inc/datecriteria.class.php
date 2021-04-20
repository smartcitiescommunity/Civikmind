<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

//Criteria which allows to select a date

/**
 * Class PluginResourcesDateCriteria
 */
class PluginResourcesDateCriteria extends PluginReportsAutoCriteria {

   /**
    * Contructor
    * @param report              the report in which the criteria is added
    * @param $name               the criteria's name
    * @param $sql_field          the sql field associated with the criteria
    *                            (can be set later with setSqlField).(default '')
    *          - Sql_field can be prefixed with table name
    *          - if sql_field=='' then sql_field=name
    * @param $label     string   (default NULL)
    **/
   function __construct($report, $name = 'date', $sql_field = '', $label = '') {

      parent::__construct($report, $name, $sql_field, $label);

      $this->addCriteriaLabel($this->getName(), ($label ? $label : __('Date')));
   }


   /**
    * @param $date
    */
   public function setDate($date) {
      $this->addParameter($this->getName(), $date);
   }


   /**
    * @return \the
    */
   public function getDate() {

      $date = $this->getParameter($this->getName());

      return $date;
   }


   public function setDefaultValues() {

      $this->setDate(date("Y-m-d"));
   }


   public function displayCriteria() {

      $this->getReport()->startColumn();
      echo $this->getCriteriaLabel($this->getName()).'&nbsp;:';
      $this->getReport()->endColumn();

      $this->getReport()->startColumn();
      Html::showDateField($this->getName(), ['value'      => $this->getDate(),
                                             'maybeempty' => false]);
      $this->getReport()->endColumn();

   }

   /**
    * Get the criteria's title
    **/
   function getSubName() {
      //TODO
      global $LANG;

      $date = $this->getDate();
      $title = $this->getCriteriaLabel($this->getName());

      if (empty($title) && isset($LANG['plugin_reports']['subname'][$this->getName()])) {
         $title = $LANG['plugin_reports']['subname'][$this->getName()];
      }

      return $title . ' (' . Html::convDate($date) . ')';
   }

}
