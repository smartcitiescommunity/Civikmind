<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

include(dirname(__FILE__)."/surveytranslation.dao.php");

/**
 * PluginSatisfactionSurveyTranslation Class
 **/
class PluginSatisfactionSurveyTranslation extends CommonDBChild {

   static public $itemtype = 'itemtype';
   static public $items_id = 'items_id';
   public $dohistory       = true;
   static $rightname       = 'plugin_satisfaction';

   static function getTypeName($nb = 0) {
      return _n('Translation', 'Translations', $nb);
   }

   /**
    * @see CommonGLPI::getTabNameForItem()
    **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (self::canBeTranslated($item)) {
         $nb = 0;
         if ($_SESSION['glpishow_count_on_tabs']) {
            $nb = self::getNumberOfTranslationsForItem($item);
         }
         return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
      }
      return '';
   }

   /**
    * Get the standard massive actions which are forbidden
    *
    * @since version 0.84
    *
    * @return an array of massive actions
    **/
   public function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }

   /**
    * Check if an item can be translated
    * It be translated if translation if globally on and item is an instance of CommonDropdown
    * or CommonTreeDropdown and if translation is enabled for this class
    *
    * @param $item the item to check
    *
    * @return true if item can be translated, false otherwise
    **/
   static function canBeTranslated(CommonGLPI $item) {
      return $item instanceof PluginSatisfactionSurvey && $item->maybeTranslated();
   }

   /**
    * Return the number of translations for an item
    *
    * @param item
    *
    * @return the number of translations for this item
    **/
   static function getNumberOfTranslationsForItem($item) {
      return PluginSatisfactionSurveyTranslationDAO::countSurveyTranslationByCrit(["plugin_satisfaction_surveys_id" => $item->getID()]);
   }

   /**
    * @param $item            CommonGLPI object
    * @param $tabnum          (default 1)
    * @param $withtemplate    (default 0)
    **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if (PluginSatisfactionSurveyTranslation::canBeTranslated($item)) {
         PluginSatisfactionSurveyTranslation::showTranslations($item);
      }
      return true;
   }

   /**
    * Display all translated field for a dropdown
    *
    * @param $item a Dropdown item
    *
    * @return true;
    **/
   static function showTranslations(PluginSatisfactionSurvey $item) {
      global $CFG_GLPI;

      // Get all translation from database
      $items = PluginSatisfactionSurveyTranslationDAO::getSurveyTranslationByCrit(["plugin_satisfaction_surveys_id" => $item->getID()]);

      $rand    = mt_rand();
      $canedit = $item->can($item->getID(), UPDATE);
      $target = Plugin::getWebDir('satisfaction')."/ajax/surveytranslation.form.php";

      if ($canedit) {
         echo "<div id='viewtranslation" . $item->getType().$item->getID() . "$rand'></div>\n";

         echo "<script type='text/javascript' >\n";
         echo "function addTranslation" . $item->getType().$item->getID() . "$rand() {\n";
         $params = [
            'id' => -1,
            'survey_id' => $item->getID(),
            'action' => 'GET'
         ];
         Ajax::updateItemJsCode("viewtranslation" . $item->getType().$item->getID() . "$rand",
            $target,
            $params);
         echo "};";
         echo "</script>\n";
         echo "<div class='center'>".
            "<a class='vsubmit' href='javascript:addTranslation".
            $item->getType().$item->getID()."$rand();'>". __('Add a new translation').
            "</a></div><br>";
      }

      if (count($items)) {

         // ** MASS ACTION **
         // TODO Remove edit action
         if ($canedit) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass'.__CLASS__.$rand];
            Html::showMassiveActions($massiveactionparams);
         }
         // ** MASS ACTION **

         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixehov'><tr class='tab_bg_2'>";

         // ** HEADER **
         echo "<th colspan='4'>".__("List of translations")."</th></tr><tr>";
         if ($canedit) {
            echo "<th width='10'>";
            echo Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            echo "</th>";
         }
         echo "<th>".__("Language")."</th>";
         echo "<th>".__("Question")."</th>";
         echo "<th>".__("Value")."</th></tr>";
         // ** HEADER **

         // ** ROWS **
         foreach ($items as $data) {
            $tdAttributes = '';
            if ($canedit) {
               $tdAttributes =
                  "style='cursor:pointer;text-align:center;' ".
                  //"onClick='viewEditTranslation".$data['itemtype'].$data['id']."$rand();'";
                  "onClick='viewEditTranslation".$data['id']."$rand();'";
            }
            echo "<tr class='tab_bg_1'>";

            // ** MASS ACTION **
            if ($canedit) {
               echo "<td class='center'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
               echo "</td>";
            }
            // ** MASS ACTION **

            echo "<td $tdAttributes>";
            if ($canedit) {
               echo "\n<script type='text/javascript' >\n";
               echo "function viewEditTranslation".$data['id']."$rand() {\n";
               $params = [
                  'id' => $data["id"],
                  'survey_id' => $item->getID(),
                  'action' => 'GET'
               ];
               Ajax::updateItemJsCode("viewtranslation" . $item->getType().$item->getID() . "$rand",
                  Plugin::getWebDir('satisfaction')."/ajax/surveytranslation.form.php",
                  $params);
               echo "};";
               echo "</script>\n";
            }
            echo Dropdown::getLanguageName($data['language']);
            echo "</td>";

            $surveyQuestion = new PluginSatisfactionSurveyQuestion();
            $surveyQuestion->getFromDB($data['glpi_plugin_satisfaction_surveyquestions_id']);

            echo "<td $tdAttributes>".$surveyQuestion->getName()."</td>";
            echo "<td $tdAttributes>".$data['value']."</td>";
            echo "</tr>";
         }
         // ** ROWS **
         echo "</table>";

         // ** MASS ACTION **
         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
         // ** MASS ACTION **

      } else {
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'>";
         echo "<th class='b'>" . __("No translation found", "satisfaction")."</th></tr></table>";
      }
      return true;

   }

   function showForm($options){
      global $CFG_GLPI;
      $surveyId = Toolbox::cleanInteger($options['survey_id']);

      $item = new PluginSatisfactionSurvey();
      $item->getFromDB($surveyId);

      if ($options['id'] > 0) {
         $item->check($surveyId, READ);
      } else {
         // Create item
         $item->check(-1, CREATE);
      }

      $tdBaseStyle="style='text-align:center;'";
      $rand = mt_rand();
      echo $this->getFormHeader($options['id'], $surveyId);

      echo "<tr>";
      // Edit Translation
      if ($options['id'] > 0) {
         echo "<input type='hidden' name='action' value='EDIT'>";

         $surveyTranslationData = PluginSatisfactionSurveyTranslationDAO::getSurveyTranslationByID($options['id']);

         $surveyQuestion = new PluginSatisfactionSurveyQuestion();
         $surveyQuestion->getFromDB($surveyTranslationData['glpi_plugin_satisfaction_surveyquestions_id']);

         // Language
         echo "<td width='10%' $tdBaseStyle>";
         echo "<input type='hidden' name='language' value='".$surveyTranslationData['language']."'>";
         echo "<input type='hidden' name='id' value='".$options['id']."'>";
         echo "<input type='hidden' name='question_id' value='".$surveyQuestion->getID()."'>";

         echo Dropdown::getLanguageName($surveyTranslationData['language']);
         echo "</td>";
         // Question
         echo "<td width='45%' $tdBaseStyle>".$surveyQuestion->getName()."</td>";
         // Value
         echo "<td width='45%' $tdBaseStyle><textarea style='position:relative; width:90%; height:60px' type='textarea' name='value'>";
         echo $surveyTranslationData['value']."</textarea></td>";
         echo "</tr>";

         // Save button
         echo "<tr><td class='center' colspan='3'>\n";
         echo Html::submit(_x('button', 'Save'), ['name' => 'update']);
         echo "</tr>";
      }
      // New translation
      else{
         echo "<input type='hidden' name='action' value='NEW'>";

         // Language
         echo "<td width='10%' $tdBaseStyle>";
         $rand   = Dropdown::showLanguages(
            "language",
            ['display_none' => true, 'value' => $_SESSION['glpilanguage']]);

         $params = [
            'language' => '__VALUE__',
            'itemtype' => get_class($item),
            'items_id' => $item->getID()
         ];

         Ajax::updateItemOnSelectEvent("dropdown_language$rand",
            "span_fields",
            $CFG_GLPI["root_doc"]."/ajax/updateTranslationFields.php",
            $params);

         echo "</td>";

         // Question
         echo "<td width='30%' $tdBaseStyle>".$this->getQuestionDropdown($surveyId)."</td>";

         // Value

         echo "<td width='60%' $tdBaseStyle><textarea style='position:relative; width:90%; height:60px' type='textarea' name='value'>";
         echo "</textarea></td>";

         echo "</tr>";

         // Add button
         echo "<tr><td class='center' colspan='3'>\n";
         echo Html::submit(_x('button', 'Add'), ['name' => 'update']);
         echo "</tr>";
      }

      // Close for Form
      echo "</table></div>";
      echo Html::closeForm(false);
   }

   function getQuestionDropdown($surveyId){

      $item = new PluginSatisfactionSurveyQuestion();
      $datas = $item->find(['plugin_satisfaction_surveys_id' => $surveyId]);

      $temp = [];
      foreach($datas as $data){
         $temp[$data['id']] = $data['name'];
      }

      $params = [
         "name"=> 'question_id',
         "display"=>false,
         "width"=> '200px',
         'display_emptychoice' => true
      ];

      return Dropdown::showFromArray($params['name'], $temp, $params);
   }

   function getFormHeader($translationID, $surveyID){

      global $CFG_GLPI;
      $target = Plugin::getWebDir('satisfaction')."/ajax/surveytranslation.form.php";

      $result = "<form name='form' method='post' action='$target' enctype='multipart/form-data'>";
      $result.= "<input type='hidden' name='survey_id' value='$surveyID'>";
      $result.= "<div class='spaced' id='tabsbody'>";
      $result.= "<table class='tab_cadre_fixe' id='mainformtable'>";
      $result.= "<tbody>";

      // First Title Line
      $result.= "<tr class='headerRow'><th colspan='3'>";
      $result.= $translationID > 0 ? __("Edit") : __("Add") ;
      $result.= " ".__("Translation");
      $result.= "</th></tr>";

      // Second title line
      $result.= "<tr class='headerRow'>";
      $result.= "<th>".__("Language")."</th>";
      $result.= "<th>".__("Question")."</th>";
      $result.= "<th>".__("Value")."</th></tr>";
      $result.= "</tr>";

      return $result;
   }

   function newSurveyTranslation($options){
      global $CFG_GLPI;
      $crit = [
         'plugin_satisfaction_surveys_id' => $options['survey_id'],
         'glpi_plugin_satisfaction_surveyquestions_id' => $options['question_id'],
         'language' => $options['language']
      ];

      // Translation already exist
      if(PluginSatisfactionSurveyTranslationDAO::countSurveyTranslationByCrit($crit)){
         Session::addMessageAfterRedirect(
            sprintf(__("An %s translation for this Question already exist.", "satisfaction"), $CFG_GLPI['languages'][$options["language"]][0]),
            true,
            WARNING);
      }
      // Translation ready to insert
      else{
         $newInsertId = PluginSatisfactionSurveyTranslationDAO::newSurveyTranslation(
            $options['survey_id'],
            $options['question_id'],
            $options['language'],
            $options['value']
         );
         if($newInsertId != null){
            Session::addMessageAfterRedirect(__("Translation successfully created.", "satisfaction"), true, INFO);

            if ($this->dohistory) {
               $changes = [
                  $newInsertId,
                  '',
                  $options['value']
               ];
               Log::history($options['survey_id'], PluginSatisfactionSurvey::class, $changes, $this->getType(),
                  static::$log_history_add);
            }
         }else{
            Session::addMessageAfterRedirect(__("Translation creation failed", "satisfaction"), true, ERROR);
         }
      }
   }

   function editSurveyTranslation($options){
      global $CFG_GLPI;
      $crit = [
         'id' => $options['id']
      ];

      // Translation doesn't exist
      if(!PluginSatisfactionSurveyTranslationDAO::countSurveyTranslationByCrit($crit)){
         Session::addMessageAfterRedirect(
            __("The translation you want to edit does not exist.", "satisfaction"),
            true,
            WARNING);
      }
      // Translation ready to update
      else{
         $surveyTranslationData = PluginSatisfactionSurveyTranslationDAO::getSurveyTranslationByID($options['id']);

         PluginSatisfactionSurveyTranslationDAO::editSurveyTranslation($options['id'],$options['value']);

         Session::addMessageAfterRedirect(__("Translation successfully edited.", "satisfaction"), true, INFO);

         if ($this->dohistory) {

            $changes = [
               $options['id'],
               $surveyTranslationData['value'],
               $options['value']
            ];
            Log::history($options['survey_id'], PluginSatisfactionSurvey::class, $changes, $this->getType(),
               static::$log_history_update);
         }
      }
   }

   static function hasTranslation($surveyId, $questionId){
      return PluginSatisfactionSurveyTranslationDAO::countSurveyTranslationByCrit([
         'plugin_satisfaction_surveys_id' => $surveyId,
         'glpi_plugin_satisfaction_surveyquestions_id' => $questionId,
         'language' => $_SESSION['glpilanguage']
      ]);
   }

   static function getTranslation($surveyId, $questionId){

      $crit = [
         'plugin_satisfaction_surveys_id' => $surveyId,
         'glpi_plugin_satisfaction_surveyquestions_id' => $questionId,
         'language' => $_SESSION['glpilanguage']
      ];

      $translationList = PluginSatisfactionSurveyTranslationDAO::getSurveyTranslationByCrit($crit);
      $translation = array_pop($translationList);
      
      return $translation['value'];
   }
}