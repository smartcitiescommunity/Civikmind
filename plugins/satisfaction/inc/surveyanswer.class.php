<?php

/**
 * Class PluginSatisfactionSurveyAnswer
 */
class PluginSatisfactionSurveyAnswer extends CommonDBChild {

   static $rightname = "plugin_satisfaction";
   public $dohistory = true;

   // From CommonDBChild
   public static $itemtype = 'PluginSatisfactionSurvey';
   public static $items_id = 'plugin_satisfaction_surveys_id';

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {
      return _n('Answer', 'Answers', $nb, 'satisfaction');
   }


   /**
    * Get Tab Name used for itemtype
    *
    * NB : Only called for existing object
    *      Must check right on what will be displayed + template
    *
    * @param $item                     CommonDBTM object for which the tab need to be displayed
    * @param $withtemplate    boolean  is a template object ? (default 0)
    *
    * @return string tab name
    **@since version 0.83
    *
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      // can exists for template
      if ($item->getType() == 'PluginSatisfactionSurvey') {
         return __('Preview', 'satisfaction');
      }

      return '';
   }


   /**
    * show Tab content
    *
    * @param $item                  CommonGLPI object for which the tab need to be displayed
    * @param $tabnum       integer  tab number (default 1)
    * @param $withtemplate boolean  is a template object ? (default 0)
    *
    * @return true
    **@since version 0.83
    *
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      if ($item->getType() == 'PluginSatisfactionSurvey') {
         self::showSurvey($item, true);

      }
      return true;
   }

   /**
    * Print survey
    *
    * @param \CommonGLPI $item
    * @param bool        $preview
    *
    * @return bool
    */
   static function showSurvey(CommonGLPI $item, $preview = false) {
      //find existing answer
      $sanswer_obj = new self();

      if ($item instanceof TicketSatisfaction) {
         if ($sanswer_obj->getFromDBByCrit(["ticketsatisfactions_id" => $item->getField('id')])) {
            $survey = new PluginSatisfactionSurvey();
            $survey->getFromDB($sanswer_obj->fields['plugin_satisfaction_surveys_id']);

            $plugin_satisfaction_surveys_id = $survey->getID();
         } else {
            $ticket = new Ticket();
            $ticket->getFromDB($item->getField('tickets_id'));
            $entities_id = Session::getActiveEntity();
            if (isset($ticket->fields['entities_id'])) {
               $entities_id = $ticket->fields['entities_id'];
            }
            $plugin_satisfaction_surveys_id = PluginSatisfactionSurvey::getObjectForEntity($entities_id);
         }

      } else if ($item instanceof PluginSatisfactionSurvey) {
         $plugin_satisfaction_surveys_id = $item->getID();
      } else {
         return false;
      }

      if (!isset($plugin_satisfaction_surveys_id)
          || $plugin_satisfaction_surveys_id === false) {
         return false;
      }

      if (!empty($sanswer_obj->fields['answer'])) {
         $dbu = new DbUtils();
         //get answer in array form
         $sanswer_obj->fields['answer'] = $dbu->importArrayFromDB($sanswer_obj->fields['answer']);
      }

      echo "<input type='hidden' name='plugin_satisfaction_surveys_id' value='$plugin_satisfaction_surveys_id'>";

      if ($preview) {
         echo "<div class='spaced' id='tabsbody'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='2'>" . __('Satisfaction', 'satisfaction') . "&nbsp;:</th></tr>";
      }

      //list survey questions
      $squestion_obj = new PluginSatisfactionSurveyQuestion;
      foreach ($squestion_obj->find([PluginSatisfactionSurveyQuestion::$items_id => $plugin_satisfaction_surveys_id]) as $question) {
         echo "<tr class=\"tab_bg_2\">";

         $name = $question['name'];
         if (PluginSatisfactionSurveyTranslation::hasTranslation($question["plugin_satisfaction_surveys_id"], $question["id"])) {
            $name = PluginSatisfactionSurveyTranslation::getTranslation($question["plugin_satisfaction_surveys_id"], $question["id"]);
         }

         echo "<td>" . nl2br($name) . "</td>";
         echo "<td>";
         if (isset($sanswer_obj->fields['answer'][$question['id']])) {
            $value = $sanswer_obj->fields['answer'][$question['id']];
         } else {
            if ($question['type'] == PluginSatisfactionSurveyQuestion::TEXTAREA) {
               $value = '';
            } else if ($question['type'] == PluginSatisfactionSurveyQuestion::NOTE) {
               $value = $question['default_value'];
            } else {
               $value = 0;
            }
         }
         self::displayAnswer($question, $value);
         echo "</td>";
         echo "</tr>";
      }

      if ($preview) {
         echo "</table>";
         echo "</div>";
      }
   }


   /**
    * Print survey
    *
    * @param \CommonGLPI $item
    * @param bool        $preview
    *
    * @return bool
    */
   static function showResponsiveSurvey(CommonGLPI $item, $preview = false) {
      //find existing answer
      $sanswer_obj = new self();

      if ($item instanceof TicketSatisfaction) {
         if ($sanswer_obj->getFromDBByCrit(["ticketsatisfactions_id" => $item->getField('id')])) {
            $survey = new PluginSatisfactionSurvey();
            $survey->getFromDB($sanswer_obj->fields['plugin_satisfaction_surveys_id']);

            $plugin_satisfaction_surveys_id = $survey->getID();
         } else {
            $ticket = new Ticket();
            $ticket->getFromDB($item->getField('tickets_id'));
            $entities_id = Session::getActiveEntity();
            if (isset($ticket->fields['entities_id'])) {
               $entities_id = $ticket->fields['entities_id'];
            }
            $plugin_satisfaction_surveys_id = PluginSatisfactionSurvey::getObjectForEntity($entities_id);
         }

      } else if ($item instanceof PluginSatisfactionSurvey) {
         $plugin_satisfaction_surveys_id = $item->getID();
      } else {
         return false;
      }

      if (!isset($plugin_satisfaction_surveys_id)
          || $plugin_satisfaction_surveys_id === false) {
         return false;
      }

      if (!empty($sanswer_obj->fields['answer'])) {
         $dbu = new DbUtils();
         //get answer in array form
         $sanswer_obj->fields['answer'] = $dbu->importArrayFromDB($sanswer_obj->fields['answer']);
      }

      echo "<input type='hidden' name='plugin_satisfaction_surveys_id' value='$plugin_satisfaction_surveys_id'>";

      //list survey questions
      $squestion_obj = new PluginSatisfactionSurveyQuestion;
      foreach ($squestion_obj->find([PluginSatisfactionSurveyQuestion::$items_id => $plugin_satisfaction_surveys_id]) as $question) {
         echo "<div class=\"form-row\">";

         $name = $question['name'];
         if (PluginSatisfactionSurveyTranslation::hasTranslation($question["plugin_satisfaction_surveys_id"], $question["id"])) {
            $name = PluginSatisfactionSurveyTranslation::getTranslation($question["plugin_satisfaction_surveys_id"], $question["id"]);
         }

         echo "<div class=\"form-group col-md-11\">";
         echo nl2br($name);
         echo "</div>";

         echo "<div class=\"form-group col-md-11\">";

         if (isset($sanswer_obj->fields['answer'][$question['id']])) {
            $value = $sanswer_obj->fields['answer'][$question['id']];
         } else {
            if ($question['type'] == PluginSatisfactionSurveyQuestion::TEXTAREA) {
               $value = '';
            } else if ($question['type'] == PluginSatisfactionSurveyQuestion::NOTE) {
               $value = $question['default_value'];
            } else {
               $value = 0;
            }
         }
         self::displayAnswer($question, $value);
         echo "</div>";

         echo "</div>";
      }
   }

   /**
    * Display answer by type
    *
    * @param     $question
    * @param int $value
    */
   static function displayAnswer($question, $value = 0) {
      $questions_id = $question['id'];

      switch ($question['type']) {
         case PluginSatisfactionSurveyQuestion::YESNO :
            Dropdown::showYesNo("answer[$questions_id]", $value);
            break;

         case PluginSatisfactionSurveyQuestion::TEXTAREA :
            $value = Html::cleanPostForTextArea($value);
            echo "<textarea cols='60' rows='6' name='answer[$questions_id]' >" . $value . "</textarea>";
            break;

         case PluginSatisfactionSurveyQuestion::NOTE :
            self::showStarAnswer($question, $value);
            break;

      }
   }

   /**
    * Star display
    *
    * @param     $question
    * @param int $value
    */
   static function showStarAnswer($question, $value = 0) {
      echo Html::css('public/lib/jquery.rateit.css');
      Html::requireJs('rateit');

      $questions_id = $question['id'];
      $number       = $question['number'];

      echo "<select id='satisfaction_data_$questions_id' name='answer[$questions_id]'>";

      for ($i = 0; $i <= $number; $i++) {
         echo "<option value='$i' " . (($i == $value) ? 'selected' : '') . ">$i</option>";
      }
      echo "</select>";

      echo "<div class='rateit' id='stars_$questions_id'></div>";
      echo "<script type='text/javascript'>\n";
      echo "$(function() {";
      echo "$('#stars_$questions_id').rateit({value: " . $value . ",
                                   min : 0,
                                   max : $number,
                                   step: 1,
                                   backingfld: '#satisfaction_data_$questions_id',
                                   ispreset: true,
                                   resetable: false});";
      echo "});</script>";
   }

   /**
    * Get answer by type
    *
    * @param     $question
    * @param int $value
    *
    * @return \clean|int|string
    */
   static function getAnswer($question, $value = 0) {

      switch ($question['type']) {
         case PluginSatisfactionSurveyQuestion::YESNO :
            return Dropdown::getYesNo($value);

         case PluginSatisfactionSurveyQuestion::TEXTAREA :
            return Html::cleanPostForTextArea($value);

         case PluginSatisfactionSurveyQuestion::NOTE :
            return $value;
      }
   }

   /**
    * Updates with answers
    *
    * @param \TicketSatisfaction $ticketSatisfaction
    */
   static function preUpdateSatisfaction(TicketSatisfaction $ticketSatisfaction) {

      $surveyanswer = new self();
      $dbu          = new DbUtils();
      if ($surveyanswer->getFromDBByCrit(["ticketsatisfactions_id" => $ticketSatisfaction->getField('id')])) {

         $input = ['id'     => $surveyanswer->getID(),
                   'answer' => addslashes($dbu->exportArrayToDB($ticketSatisfaction->input['answer']))];

         $surveyanswer->update($input);
      } else {
         $input = ['plugin_satisfaction_surveys_id' => $ticketSatisfaction->input['plugin_satisfaction_surveys_id'],
                   'ticketsatisfactions_id'         => $ticketSatisfaction->getField('id'),
                   'answer'                         => addslashes($dbu->exportArrayToDB($ticketSatisfaction->input['answer']))];

         $surveyanswer->add($input);
      }

   }


   /**
    * Displaying questions in GLPI's ticket satisfaction
    *
    * @param $params
    *
    * @return bool
    */
   static function displaySatisfaction($params) {

      if (isset($params['item'])) {
         $item = $params['item'];
         if ($item->getType() == 'TicketSatisfaction') {

            self::showSurvey($item);
         }
      }
   }

   /**
    * Adding two tags to satisfaction notifications
    *
    * @param \NotificationTarget $target
    */
   static function addNotificationDatas(NotificationTargetTicket $target) {

      $event = $target->raiseevent;
      if (isset($target->obj->fields['id'])) {
         $tickets_id  = $target->obj->fields['id'];
         $entities_id = $target->obj->fields['entities_id'];

         $ticket_satisfaction = new TicketSatisfaction();
         if ($ticket_satisfaction->getFromDBByRequest(['WHERE' =>
                                                          ["tickets_id" => $tickets_id]])) {

            $sanswer_obj = new self();
            if ($sanswer_obj->getFromDBByCrit(["ticketsatisfactions_id" => $ticket_satisfaction->getField('id')])) {
               $dbu                           = new DbUtils();
               $sanswer_obj->fields['answer'] = $dbu->importArrayFromDB($sanswer_obj->fields['answer']);

               $plugin_satisfaction_surveys_id = $sanswer_obj->getField('plugin_satisfaction_surveys_id');
            } else {

               if (($survey = PluginSatisfactionSurvey::getObjectForEntity($entities_id)) !== false) {

                  $plugin_satisfaction_surveys_id = $survey;
               }
            }

            if (isset($plugin_satisfaction_surveys_id)) {
               $squestion_obj = new PluginSatisfactionSurveyQuestion;
               $questions     = $squestion_obj->find([PluginSatisfactionSurveyQuestion::$items_id => $plugin_satisfaction_surveys_id]);

               switch ($event) {
                  case 'satisfaction':
                     $data = '';
                     foreach ($questions as $question) {
                        $data .= $question['name'] . "\n\n";
                     }
                     $target->data['##satisfaction.question##'] = $data;
                     break;

                  case 'replysatisfaction':

                     $data = '';
                     foreach ($questions as $question) {

                        if (isset($sanswer_obj->fields['answer'][$question['id']])) {
                           $value = $sanswer_obj->fields['answer'][$question['id']];
                        } else {
                           if ($question['type'] == PluginSatisfactionSurveyQuestion::TEXTAREA) {
                              $value = '';
                           } else if ($question['type'] == PluginSatisfactionSurveyQuestion::NOTE) {
                              $value = $question['default_value'];
                           } else {
                              $value = 0;
                           }
                        }
                        $data .= $question['name'] . " : " . self::getAnswer($question, $value) . "\n\n";
                     }
                     $target->data['##satisfaction.answer##'] = $data;

                     break;
               }
            }
         }
      }
   }
}
