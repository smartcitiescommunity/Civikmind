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

/**
 * Class PluginResourcesDashboard
 */
class PluginSatisfactionDashboard extends CommonGLPI {

   // Widget identifiers
   const SATISFACTION_SURVEY = 0;

   // Icons
   const ICON_CIRCLE = 0;

   // Periods
   const EMPTY_PERIOD = 0;
   const FIRST_TRIMESTER_PERIOD = 1;
   const SECOND_TRIMESTER_PERIOD = 2;
   const THIRD_TRIMESTER_PERIOD = 3;
   const FOURTH_TRIMESTER_PERIOD = 4;
   const YEAR_PERIOD = 5;

   const PERIOD_SELECTOR_HTML_ID = "period-selector";

   /**
    * PluginResourcesDashboard constructor.
    *
    * @param array $options
    */
   function __construct($options = []) {
      $this->options = $options;
      $this->interfaces = ["central"];
   }

   /**
    * @return array
    */
   function getWidgetsForItem() {
      return [
         $this->getType().self::SATISFACTION_SURVEY =>
            self::getWidgetTitle($this->getType().self::SATISFACTION_SURVEY)
            ."&nbsp;".self::getIcon(self::ICON_CIRCLE)
      ];
   }

   function getWidgetTitle($widgetId) {
      $result = "";
      switch ($widgetId) {
         case $this->getType().self::SATISFACTION_SURVEY:
            $result = __('Satisfaction survey', 'satisfaction');
            break;
      }
      return $result;
   }

   function getIcon($icon) {
      switch ($icon) {
         case self::ICON_CIRCLE:
            return "<i class='fas fa-info-circle'></i>";
      }
   }

   /**
    * Give name of period by id
    * Or give all list if id is null
    *
    * @param null $idPeriod
    * @return array|mixed|null
    */
   function getPeriodNames($idPeriod = null){
      $titles = [
         self::EMPTY_PERIOD => "--",
         self::FIRST_TRIMESTER_PERIOD => __('First Trimester', 'satisfaction'),
         self::SECOND_TRIMESTER_PERIOD => __('Second Trimester', 'satisfaction'),
         self::THIRD_TRIMESTER_PERIOD => __('Third Trimester', 'satisfaction'),
         self::FOURTH_TRIMESTER_PERIOD => __('Fourth Trimester', 'satisfaction'),
         self::YEAR_PERIOD => __('Year', 'satisfaction'),
      ];

      if(is_null($idPeriod)){
         return $titles;
      }
      else{
         return $titles[$idPeriod];
      }
   }

   /**
    * Give the period interval of date
    *
    * @param $idPeriod
    * @param null $year
    * @return array
    */
   function getDateIntervalForPeriod($idPeriod, $year){

      $interval = [];

      switch($idPeriod){
         case self::FIRST_TRIMESTER_PERIOD:
            $interval['begin'] = $year.'-01-01 00:00:00';
            $interval['end'] = $year.'-03-31 00:00:00';
            break;
         case self::SECOND_TRIMESTER_PERIOD:
            $interval['begin'] = $year.'-04-01 00:00:00';
            $interval['end'] = $year.'-06-30 00:00:00';
            break;
         case self::THIRD_TRIMESTER_PERIOD:
            $interval['begin'] = $year.'-07-01 00:00:00';
            $interval['end'] = $year.'-09-30 00:00:00';
            break;
         case self::FOURTH_TRIMESTER_PERIOD:
            $interval['begin'] = $year.'-10-01 00:00:00';
            $interval['end'] = $year.'-12-31 00:00:00';
            break;
         case self::YEAR_PERIOD:
         case null:
            $interval['begin'] = $year.'-01-01 00:00:00';
            $interval['end'] = $year.'-12-31 00:00:00';
      }
      return $interval;
   }

   /**
    * @param $widgetId
    *
    * @return \PluginMydashboardDatatable
    */
   function getWidgetContentForItem($widgetId, $opt = []) {
      switch ($widgetId) {
         case $this->getType().self::SATISFACTION_SURVEY :
            return self::satisfactionSurvey($widgetId, $opt);
            break;
      }
   }

   function satisfactionSurvey($widgetId, $opt = []) {
      global $DB;

      $criterias = ['begin', 'end', 'year', self::PERIOD_SELECTOR_HTML_ID];
      $params    = ["criterias"   => $criterias, "opt"=> $opt];
      $options   = PluginMydashboardHelper::manageCriterias($params);

      $period = isset($opt[self::PERIOD_SELECTOR_HTML_ID]) ? $opt[self::PERIOD_SELECTOR_HTML_ID] : null ;
      $year = isset($options['opt']['year']) ? $options['opt']['year'] : date("Y");

      // When period is chosen we set the interval of date with the year
      if(is_null($period) || intval($period) !== self::EMPTY_PERIOD){

         $interval = self::getDateIntervalForPeriod($period, $year);

         $options['opt']['begin'] = $interval['begin'];
         $options['opt']['end'] = $interval['end'];

         $options['opt'][self::PERIOD_SELECTOR_HTML_ID] = self::EMPTY_PERIOD;
      }

      $opt       = $options['opt'];

      $widget = new PluginMydashboardHtml();
      $widget->setWidgetTitle(self::getWidgetTitle($widgetId));

      $content = "";

      // Recover survey associed to current entity
      $pluginSatisfactionSurvey = new PluginSatisfactionSurvey();
      if (!$pluginSatisfactionSurvey->getFromDBByCrit([
         'entities_id' => $_SESSION['glpiactive_entity'],
         'is_active' => 1
      ])) {
         $content.= '<div class="center">';
         $content.= '<br><br>';
         $content.= '<h4>'.__("There are no survey for current entity", "satisfaction").'</h4>';
         $content.= '</div>';
      } else {

         // Values
         $numberOfSurveys = 0;
         $numberOfImpactedTickets = 0;
         $numberSurveyNotAnswered = 0;
         $numberSurveyAnswered = 0;
         $globalSatisfaction = 0;

         // Datetime to date conversion
         function addDateCriteria(&$query, $dateBegin, $dateEnd){
            if(!empty($dateBegin)){
               $query.= " AND date(date_begin) >= '".$dateBegin."'";
            }
            if(!empty($dateEnd)){
               $query.= " AND date(date_begin) < '".$dateEnd."'";
            }
         }

         // Number of satisfaction survey
         $query = "SELECT count(*) as nb FROM " . TicketSatisfaction::getTable();
         $query .= " WHERE 1=1";
         addDateCriteria($query, $opt['begin'], $opt['end']);

         $result = $DB->query($query);

         if ($DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               $numberOfSurveys = $data['nb'];
            }
         }

         // Number of concerned tickets
         $query = "SELECT count(DISTINCT tickets_id) as nb FROM " . TicketSatisfaction::getTable();
         $query .= " WHERE 1=1";
         addDateCriteria($query, $opt['begin'], $opt['end']);

         $result = $DB->query($query);

         if ($DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               $numberOfImpactedTickets = $data['nb'];
            }
         }

         // Survey not answered
         $query = "SELECT count(*) as nb FROM " . TicketSatisfaction::getTable();
         $query .= " WHERE 1=1";
         $query .= " AND date_answered IS NULL";
         addDateCriteria($query, $opt['begin'], $opt['end']);

         $result = $DB->query($query);

         if ($DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               $numberSurveyNotAnswered = $data['nb'];
            }
         }

         // Survey answered
         $query = "SELECT count(DISTINCT tickets_id) as nb FROM " . TicketSatisfaction::getTable();
         $query .= " WHERE 1=1";
         $query .= " AND date_answered IS NOT NULL";
         addDateCriteria($query, $opt['begin'], $opt['end']);

         $result = $DB->query($query);

         if ($DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               $numberSurveyAnswered = $data['nb'];
            }
         }

         // Global satisfaction
         $query = "SELECT AVG(satisfaction) as nb FROM " . TicketSatisfaction::getTable();
         $query .= " WHERE 1=1";
         $query .= " AND date_answered IS NOT NULL";
         addDateCriteria($query, $opt['begin'], $opt['end']);

         $result = $DB->query($query);

         if ($DB->numrows($result)) {
            while ($data = $DB->fetchAssoc($result)) {
               $globalSatisfaction = $data['nb'];
            }
         }

         $globalSatisfaction = round($globalSatisfaction, 1);

         function displayElement($color, $icon, $title, $value){
            $elem = '<div class="nb" style="color:'.$color.'">';
            //$elem.= '<a style="color:'.$color.'" target="_blank" href="" title="'.$title.'">';
            $elem.= '<i style="color:'.$color.';font-size:34px" class="fa '.$icon.' fa-3x fa-border"></i>';
            $elem.= '<h3>';
            $elem.= '<span class="counter count-number">'.$value.'</span>';
            $elem.= '</h3>';
            $elem.= '<p class="count-text ">'.$title.'</p>';
            //$elem.= '</a>';
            $elem.= '</div>';

            return $elem;
         }

         // Add css and javascript to display stars with rateit
         $content = Html::css('public/lib/jquery.rateit.css');
         Html::requireJs('rateit');

         $content.= '<div class="tickets-stats">';
         $content.= displayElement(
            "grey",
            "fa-exclamation-circle",
            __("Number of surveys", "satisfaction"),
            $numberOfSurveys);
         $content.= displayElement(
            "grey",
            "fa-id-card",
            __("Number of concerned tickets", "satisfaction"),
            $numberOfImpactedTickets);
         $content.= displayElement(
            "indianred",
            "fa-times",
            __("Survey not answered", "satisfaction"),
            $numberSurveyNotAnswered);
         $content.= displayElement(
            "green",
            "fa-check",
            __("Survey answered", "satisfaction"),
            $numberSurveyAnswered);

         $content.= '<div>';
         $content.= '<h3 style="color:grey">';
         $content.= '<span>'.__("Global satisfaction", "satisfaction").'</span>';
         $content.= '</h3>';
         $content.= '<h3>'.$globalSatisfaction.'</h3>';
         $content.= '<div class="rateit" data-rateit-value="'.$globalSatisfaction.'" data-rateit-ispreset="true" data-rateit-readonly="true"></div>';
         $content.= "</div>";
         $content.= "</div>";

         $params = ["widgetId"  => $widgetId,
            "name"      => str_replace(' ', '', self::getWidgetTitle($widgetId)),
            "onsubmit"  => true,
            "opt"       => $opt,
            "criterias" => $criterias,
            "export"    => false,
            "canvas"    => false,
            "nb"        => 1];

         $graphHeader = PluginMydashboardHelper::getGraphHeader($params);

         self::addPeriodCriteriaToGraphHeader($graphHeader);

         $widget->setWidgetHeader($graphHeader);
      }

      $widget->setWidgetHtmlContent($content);
      $widget->toggleWidgetRefresh();
      return $widget;
   }

   /**
    * Only works with submit button in $graphHeader
    *
    * @param $graphHeader
    */
   function addPeriodCriteriaToGraphHeader(&$graphHeader){

      $submitPos = strpos($graphHeader, "<input type='submit'");
      $graphBeforeSubmit = substr($graphHeader, 0, $submitPos);
      $graphAfterSubmit = substr($graphHeader, $submitPos, strlen($graphHeader) - 1);

      $dropdown = Dropdown::showFromArray(self::PERIOD_SELECTOR_HTML_ID, self::getPeriodNames(), ['display' => false]);

      $period = "<span class='md-widgetcrit'>";
      $period.= __('Periods', 'satisfaction');
      $period.= "&nbsp;";
      $period.= $dropdown;
      $period.= "</span>";
      $period.= "<br><br>";

      $graphHeader = $graphBeforeSubmit.$period.$graphAfterSubmit;
   }
}


