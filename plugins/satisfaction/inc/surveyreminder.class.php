<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginSatisfactionSurveyReminder extends CommonDBChild {

   static $rightname = "plugin_satisfaction";
   public $dohistory = true;

   // From CommonDBChild
   public static $itemtype = PluginSatisfactionSurvey::class;
   public static $items_id = 'plugin_satisfaction_surveys_id';

   // Durations
   const DURATION_DAY   = 0;
   const DURATION_MONTH = 1;

   // Is active
   const ACTIVE_OFF = 0;
   const ACTIVE_ON  = 1;

   // Columns names
   const COLUMN_NAME          = 'name';
   const COLUMN_DURATION_TYPE = 'duration_type';
   const COLUMN_DURATION      = 'duration';
   const COLUMN_IS_ACTIVE     = 'is_active';
   const COLUMN_COMMENT       = 'comment';

   // Predefined reminders
   const PREDEFINED_1_WEEK               = 0;
   const PREDEFINED_2_WEEK               = 1;
   const PREDEFINED_1_MONTH              = 2;
   const PREDEFINED_REMINDER_OPTION_NAME = 'presetreminder';


   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {
      return _n('Reminder', 'Reminders', $nb, 'satisfaction');
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
      if ($item->getType() == PluginSatisfactionSurvey::class) {
         return _n('Reminder', 'Reminders', 2, 'satisfaction');
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
      if ($item->getType() == PluginSatisfactionSurvey::class) {
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
   static function showSurvey(PluginSatisfactionSurvey $survey, $preview = false) {

      global $CFG_GLPI;

      $surveyReminder = new self();
      $sID            = $survey->fields['id'];
      $rand_survey    = mt_rand();

      $canadd   = Session::haveRight(self::$rightname, CREATE);
      $canedit  = Session::haveRight(self::$rightname, UPDATE);
      $canpurge = Session::haveRight(self::$rightname, PURGE);

      echo "<div id='viewreminder" . $sID . "$rand_survey'></div>\n";
      if ($canadd) {
         echo "<script type='text/javascript' >\n";

         // Add reminder ajax action
         echo "function viewAddReminder$sID$rand_survey() {\n";
         $params = [
            'type'          => __CLASS__,
            'parenttype'    => PluginSatisfactionSurvey::class,
            self::$items_id => $sID,
            'id'            => -1
         ];
         Ajax::updateItemJsCode("viewreminder$sID$rand_survey",
                                $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php", $params);
         echo "};";

         // Add predefined reminder ajax action
         // Add reminder ajax action
         echo "function viewAddPredefinedReminder$sID$rand_survey() {\n";
         $params = [
            'type'                                => __CLASS__,
            'parenttype'                          => PluginSatisfactionSurvey::class,
            self::$items_id                       => $sID,
            'id'                                  => -1,
            self::PREDEFINED_REMINDER_OPTION_NAME => 1
         ];
         Ajax::updateItemJsCode("viewreminder$sID$rand_survey",
                                 Plugin::getWebDir('satisfaction') . "/ajax/viewsubitem_reminder.php", $params);
         echo "};";

         echo "</script>\n";
         echo "<div class='center'>";
         // Add a reminder
         echo "<a href='javascript:viewAddReminder$sID$rand_survey();'>";
         echo __('Add a reminder', 'satisfaction') . "</a>\n";
         echo "<br>";
         // Add a preset reminder
         echo "<a href='javascript:viewAddPredefinedReminder$sID$rand_survey();'>";
         echo __('Add a predefined reminder', 'satisfaction') . "</a>\n";
         echo "</div><br>";
      }

      // Dispaly an option to setup 
      echo "<form name='form' method='post'>";
      echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'>";
      echo "<th class='b' colspan='2'>" . __('Setup maximum number of days to send reminder', 'satisfaction') . "</th>";
      echo "<tr class='tab_bg_1'><td>" . __('Maximum number of days to send reminder', 'satisfaction') . "</td>";
      echo "<td>";
      Dropdown::showNumber('reminders_days', ['value' => $survey->fields["reminders_days"],
                                              'min'   => 1,
                                              'max'   => 365]);
      echo "</td></tr>";
      echo "<tr>";
      echo "<td class='tab_bg_2 center' colspan='4'>";
      echo Html::hidden('id', ['value' => $sID]);
      echo "<input type='submit' name='update' class='submit' value='" . _sx('button', 'Save') . "' >";
      echo "</td>";
      echo "</tr></table>";
      Html::closeForm();

      // Display existing questions
      $remminders = $surveyReminder->find([self::$items_id => $sID], 'id');
      if (count($remminders) == 0) {
         echo "<table class='tab_cadre_fixe'><tr class='tab_bg_2'>";
         echo "<th class='b'>" . __('No reminders for this survey', 'satisfaction') . "</th>";
         echo "</tr></table>";
      } else {

         $rand = mt_rand();
         if ($canpurge) {
            //TODO : Detect delete to update history
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);
         }

         echo "<table class='tab_cadre_fixehov'>";
         echo "<tr>";
         if ($canpurge) {
            echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
         }
         echo "<th>" . $surveyReminder->getColumnTitles(self::COLUMN_NAME) . "</th>";
         echo "<th>" . $surveyReminder->getColumnTitles(self::COLUMN_DURATION_TYPE) . "</th>";
         echo "<th>" . $surveyReminder->getColumnTitles(self::COLUMN_DURATION) . "</th>";
         echo "<th>" . $surveyReminder->getColumnTitles(self::COLUMN_IS_ACTIVE) . "</th>";

         echo "</tr>";

         foreach ($remminders as $reminder) {
            if ($surveyReminder->getFromDB($reminder['id'])) {
               $surveyReminder->showOne($canedit, $canpurge, $rand_survey);
            }
         }
         echo "</table>";

         if ($canpurge) {
            $paramsma['ontop'] = false;
            Html::showMassiveActions($paramsma);
            Html::closeForm();
         }
      }

   }

   /**
    * @param       $ID
    * @param array $options
    *
    * @return bool
    */
   function showForm($ID, $options = []) {
      if (isset($options['parent']) && !empty($options['parent'])) {
         $survey = $options['parent'];
      }

      $surveyReminder = new self();
      if ($ID <= 0) {
         $surveyReminder->getEmpty();
      } else {
         $surveyReminder->getFromDB($ID);
      }

      if (!$surveyReminder->canView()) {
         return false;
      }

      $displayPresetReminderForm = isset($options[self::PREDEFINED_REMINDER_OPTION_NAME])
                                   && $options[self::PREDEFINED_REMINDER_OPTION_NAME];

      echo "<form name='form' method='post' action='" . Toolbox::getItemTypeFormURL(self::getType()) . "'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";

      echo "<tr>";
      if ($displayPresetReminderForm) {
         echo "<th colspan='4'>" . __('Choose a predefined reminder', 'satisfaction') . "</th>";
      } else {
         echo "<th colspan='4'>" . __('Add a reminder', 'satisfaction') . "</th>";
      }

      echo "</tr>";

      if ($displayPresetReminderForm) {

         echo "<tr class='tab_bg_1' rowspan='10'>";

         echo "<td>" . __('Predefined Reminders', "satisfaction") . "</td>";
         echo "<td>" . self::getPresetReminderDropdown(self::PREDEFINED_REMINDER_OPTION_NAME) . "</td>";
         echo "</tr>";

      } else {
         echo "<tr class='tab_bg_1'>";

         // Name line 1
         echo "<td>" . self::getColumnTitles(self::COLUMN_NAME) . "</td>";
         echo "<td><textarea name='" . self::COLUMN_NAME . "' cols='50' rows='4'>";
         echo $surveyReminder->fields["name"] . "</textarea></td>";

         echo "<input type='hidden' name='" . self::$items_id . "' value='" . $surveyReminder->fields[self::$items_id] . "'>";

         // Comment line 1
         echo "<td rowspan='2'>" . self::getColumnTitles(self::COLUMN_COMMENT) . "</td>";
         echo "<td rowspan='2'>";
         echo "<textarea cols='60' rows='6' name='" . self::COLUMN_COMMENT . "' >" . $surveyReminder->fields[self::COLUMN_COMMENT] . "</textarea>";
         echo "</td>";

         echo "</tr>";

         // Duration type line 2
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . self::getColumnTitles(self::COLUMN_DURATION_TYPE) . "</td>";
         echo "<td>" . self::getDurationDropdown(self::COLUMN_DURATION_TYPE, $surveyReminder->fields[self::COLUMN_DURATION_TYPE]) . "</td>";
         echo "</tr>";

         // Duration line 3
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . self::getColumnTitles(self::COLUMN_DURATION) . "</td>";
         echo "<td colspan='3'>";
         Dropdown::showNumber(self::COLUMN_DURATION, ['value' => $surveyReminder->fields[self::COLUMN_DURATION],
                                                      'min'   => 1,
                                                      'max'   => 365]);
         echo "</td>";
         echo "</tr>";

         // Active line 4
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . self::getColumnTitles(self::COLUMN_IS_ACTIVE) . "</td>";
         echo "<td>";
         Dropdown::showYesNo(self::COLUMN_IS_ACTIVE, $surveyReminder->fields[self::COLUMN_IS_ACTIVE]);
         echo "</td><td colspan='2'></td></tr>";
      }

      echo "<tr>";
      echo "<td class='tab_bg_2 center' colspan='4'>";
      if ($ID <= 0) {
         echo Html::hidden(self::$items_id, ['value' => $survey->getField('id')]);
         echo "<input type='submit' name='add' class='submit' value='" . _sx('button', 'Add') . "' >";
      } else {
         echo Html::hidden('id', ['value' => $ID]);
         echo "<input type='submit' name='update' class='submit' value='" . _sx('button', 'Save') . "' >";
      }
      echo "</td>";
      echo "</tr>";

      echo "</table>";

      Html::closeForm();
   }

   /**
    * Display line with name & type
    *
    * @param $canedit
    * @param $rand
    */
   function showOne($canedit, $canpurge, $rand) {
      global $CFG_GLPI;

      $style = '';
      if ($canedit) {
         $style = "style='cursor:pointer' onClick=\"viewEditReminder" .
                  $this->fields[self::$items_id] .
                  $this->fields['id'] . "$rand();\"" .
                  " id='viewquestion" . $this->fields[self::$items_id] . $this->fields["id"] . "$rand'";
      }
      echo "<tr class='tab_bg_2' $style>";

      if ($canpurge) {
         echo "<td width='10'>";
         Html::showMassiveActionCheckBox(__CLASS__, $this->fields["id"]);
         echo "</td>";
      }

      if ($canedit) {
         echo "\n<script type='text/javascript' >\n";
         echo "function viewEditReminder" . $this->fields[self::$items_id] . $this->fields["id"] . "$rand() {\n";
         $params = ['type'          => __CLASS__,
                    'parenttype'    => self::$itemtype,
                    self::$items_id => $this->fields[self::$items_id],
                    'id'            => $this->fields["id"]];
         Ajax::updateItemJsCode("viewreminder" . $this->fields[self::$items_id] . "$rand",
                                $CFG_GLPI["root_doc"] . "/ajax/viewsubitem.php", $params);
         echo "};";
         echo "</script>\n";
      }

      echo "<td class='center'>" . nl2br($this->fields[self::COLUMN_NAME]) . "</td>";
      echo "<td class='center'>" . nl2br($this->getDurationTitles($this->fields[self::COLUMN_DURATION_TYPE])) . "</td>";
      echo "<td class='center'>" . nl2br($this->fields[self::COLUMN_DURATION]) . "</td>";
      echo "<td class='center'>" . nl2br($this->getActiveTitles($this->fields[self::COLUMN_IS_ACTIVE])) . "</td>";
      echo "</tr>";
   }

   /**
    * Get the standard massive actions which are forbidden
    *
    * @return an array of massive actions
    **@since version 0.84
    *
    */
   public function getForbiddenStandardMassiveAction() {

      $forbidden   = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }

   public function getActiveTitles($id) {
      $titles = [
         self::ACTIVE_OFF => __('No'),
         self::ACTIVE_ON  => __('Yes'),
      ];
      return $titles[$id];
   }

   public function getDurationTitles($id = null) {

      $titles = [
         self::DURATION_DAY   => __('Day'),
         self::DURATION_MONTH => __('Month', 'satisfaction')
      ];

      if (is_null($id)) {
         return $titles;
      } else {
         return $titles[$id];
      }
   }

   public function getPresetReminderTitles($id = null) {
      $yolo = 2;

      $titles = [
         self::PREDEFINED_1_WEEK  => __('One Week', 'satisfaction'),
         self::PREDEFINED_2_WEEK  => __('Two Week', 'satisfaction'),
         self::PREDEFINED_1_MONTH => __('One Month', 'satisfaction'),
      ];

      if (is_null($id)) {
         return $titles;
      } else {
         return $titles[$id];
      }
   }

   public function getColumnTitles($id) {
      $titles = [
         self::COLUMN_NAME          => __('Name'),
         self::COLUMN_DURATION_TYPE => __("Duration Type", "satisfaction"),
         self::COLUMN_DURATION      => __("Duration", "satisfaction"),
         self::COLUMN_COMMENT       => __("Comments"),
         self::COLUMN_IS_ACTIVE     => __("Active"),
      ];

      return $titles[$id];
   }

   public function getDurationDropdown($name, $defaultValue) {

      $params = [
         'display' => false,
         'value'   => $defaultValue
      ];

      return Dropdown::showFromArray($name, self::getDurationTitles(), $params);
   }

   public function getPresetReminderDropdown($name) {
      $params = [
         'display' => false
      ];

      return Dropdown::showFromArray($name, self::getPresetReminderTitles(), $params);
   }

   public function generatePredefinedReminderForAdd($postValues) {

      $namePrefix = __('Reminder', 'satisfaction');
      $comment    = __('Preset Reminder');

      switch (intval($postValues[self::PREDEFINED_REMINDER_OPTION_NAME])) {
         case self::PREDEFINED_1_WEEK:
            $postValues[self::COLUMN_NAME]          = $namePrefix . " " . self::getPresetReminderTitles(self::PREDEFINED_1_WEEK);
            $postValues[self::COLUMN_COMMENT]       = $comment;
            $postValues[self::COLUMN_DURATION_TYPE] = self::DURATION_DAY;
            $postValues[self::COLUMN_DURATION]      = 7;
            break;
         case self::PREDEFINED_2_WEEK:
            $postValues[self::COLUMN_NAME]          = $namePrefix . " " . self::getPresetReminderTitles(self::PREDEFINED_2_WEEK);
            $postValues[self::COLUMN_COMMENT]       = $comment;
            $postValues[self::COLUMN_DURATION_TYPE] = self::DURATION_DAY;
            $postValues[self::COLUMN_DURATION]      = 14;
            break;
         case self::PREDEFINED_1_MONTH:
            $postValues[self::COLUMN_NAME]          = $namePrefix . " " . self::getPresetReminderTitles(self::PREDEFINED_1_MONTH);
            $postValues[self::COLUMN_COMMENT]       = $comment;
            $postValues[self::COLUMN_DURATION_TYPE] = self::DURATION_MONTH;
            $postValues[self::COLUMN_DURATION]      = 1;
            break;
      }
      return $postValues;
   }

   /**
    * Verify this survey does not have a reminder with exact same duration type and duration
    *
    * @param $input
    *
    * @return array|bool
    */
   function prepareInputForAdd($input) {
      $crit = [
         self::COLUMN_DURATION_TYPE => $input[self::COLUMN_DURATION_TYPE],
         self::COLUMN_DURATION      => $input[self::COLUMN_DURATION],
      ];

      $items = $this->find($crit);

      if (count($items)) {

         $item = array_pop($items);

         $errorMessage = __('You already have a reminder with the same duration type and duration named : %s', 'satisfaction');

         Session::addMessageAfterRedirect(sprintf($errorMessage, $item['name']), false, ERROR);
         return false;
      }
      return $input;
   }

   /**
    * Verify survey remindr can be updated only if a column is different
    *
    * @param $input
    *
    * @return array|bool
    */
   function prepareInputForUpdate($input) {
      $crit = [
         self::COLUMN_DURATION_TYPE => $input[self::COLUMN_DURATION_TYPE],
         self::COLUMN_DURATION      => $input[self::COLUMN_DURATION],
         self::COLUMN_IS_ACTIVE     => $input[self::COLUMN_IS_ACTIVE],
         self::COLUMN_NAME          => $input[self::COLUMN_NAME],
         self::COLUMN_COMMENT       => $input[self::COLUMN_COMMENT]
      ];

      $items = $this->find($crit);

      if (count($items)) {

         $item = array_pop($items);

         $errorMessage = __('There are nothing to save', 'satisfaction');

         Session::addMessageAfterRedirect(sprintf($errorMessage, $item['name']), false, ERROR);
         return false;
      }
      return $input;
   }
}
