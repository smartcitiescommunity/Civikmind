<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * Class PluginSatisfactionSurvey
 *
 * Used to store reminders to send automatically
 */
class PluginSatisfactionReminder extends CommonDBTM {

   static $rightname = "plugin_satisfaction";
   public $dohistory = true;

   public static $itemtype = TicketSatisfaction::class;
   public static $items_id = 'ticketsatisfactions_id';

   const CRON_TASK_NAME = 'SatisfactionReminder';


   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {
      return _n('Satisfaction reminder', 'Satisfaction reminders', $nb, 'satisfaction');
   }

   ////// CRON FUNCTIONS ///////

   /**
    * @param $name
    *
    * @return array
    */
   static function cronInfo($name) {

      switch ($name) {
         case self::CRON_TASK_NAME:
            return ['description' => __('Send automaticaly survey reminders', 'satisfaction')];   // Optional
            break;
      }
      return [];
   }

   public static function deleteItem(Ticket $ticket) {
      $reminder = new Self;
      if ($reminder->getFromDBByCrit(['tickets_id' => $ticket->fields['id']])) {
         $reminder->delete(['id' => $reminder->fields["id"]]);
      }
   }

   /**
    * Cron action
    *
    * @param  $task for log
    *
    * @global $CFG_GLPI
    *
    * @global $DB
    */
   static function cronSatisfactionReminder($task = NULL) {

      $CronTask = new CronTask();
      if ($CronTask->getFromDBbyName(PluginSatisfactionReminder::class, PluginSatisfactionReminder::CRON_TASK_NAME)) {
         if ($CronTask->fields["state"] == CronTask::STATE_DISABLE) {
            return 0;
         }
      } else {
         return 0;
      }

      self::sendReminders();
   }

   /**
    * @param $date_begin
    * @param $date_answered
    * @param $entities_id
    *
    * @return array
    * @throws \GlpitestSQLError
    */
   static function getTicketSatisfaction($date_begin, $date_answered, $entities_id) {
      global $DB;

      $ticketSatisfactions = [];

      $query = "SELECT ts.* FROM " . TicketSatisfaction::getTable() . " as ts";
      $query .= " INNER JOIN " . Ticket::getTable() . " as t";
      $query .= " ON ts.tickets_id = t.id";
      $query .= " WHERE t.entities_id = " . $entities_id;
      $query .= " AND ts.date_begin > DATE('" . $date_begin . "')";
      $query .= " AND ts.date_answered " . (($date_answered == null) ? " IS NULL" : " = DATE('" . $date_answered . "')");

      $result = $DB->query($query);

      if ($DB->numrows($result)) {
         while ($data = $DB->fetchAssoc($result)) {
            $ticketSatisfactions[] = $data;
         }
      }
      return $ticketSatisfactions;
   }

   static function sendReminders() {

      $entityDBTM = new Entity();

      $pluginSatisfactionSurveyDBTM         = new PluginSatisfactionSurvey();
      $pluginSatisfactionSurveyReminderDBTM = new PluginSatisfactionSurveyReminder();
      $pluginSatisfactionReminderDBTM       = new PluginSatisfactionReminder();

      $surveys = $pluginSatisfactionSurveyDBTM->find(['is_active' => true]);

      foreach ($surveys as $survey) {

         // Entity
         $entityDBTM->getFromDB($survey['entities_id']);

         // Don't get tickets satisfaction with date older than max_close_date
//                           $max_close_date = date('Y-m-d', strtotime($entityDBTM->getField('max_closedate')));
         $nb_days = $survey['reminders_days'];
         $dt             = date("Y-m-d");
         $max_close_date = date('Y-m-d', strtotime("$dt - ".$nb_days." day"));

         // Ticket Satisfaction
         $ticketSatisfactions = self::getTicketSatisfaction($max_close_date, null, $survey['entities_id']);


         foreach ($ticketSatisfactions as $k => $ticketSatisfaction) {

            // Survey Reminders
            $surveyReminderCrit = [
               'plugin_satisfaction_surveys_id' => $survey['id'],
               'is_active'                      => 1,
            ];
            $surveyReminders    = $pluginSatisfactionSurveyReminderDBTM->find($surveyReminderCrit);

            $potentialReminderToSendDates = [];

            // Calculate the next date of next reminders
            foreach ($surveyReminders as $surveyReminder) {

               $reminders = null;
               $reminders = $pluginSatisfactionReminderDBTM->find(['tickets_id' => $ticketSatisfaction['tickets_id'],
                                                                   'type'       => $surveyReminder['id']]);

               if (count($reminders)) {
                  continue;
               } else {

                  $lastSurveySendDate = date('Y-m-d', strtotime($ticketSatisfaction['date_begin']));

                  // Date when glpi satisfaction was sended for the first time
                  $reminders_to_send = $pluginSatisfactionReminderDBTM->find(['tickets_id' => $ticketSatisfaction['tickets_id']]);
                  if (count($reminders_to_send)) {
                     $reminder           = array_pop($reminders_to_send);
                     $lastSurveySendDate = date('Y-m-d', strtotime($reminder['date']));
                  }

                  $date = null;

                  switch ($surveyReminder[PluginSatisfactionSurveyReminder::COLUMN_DURATION_TYPE]) {

                     case PluginSatisfactionSurveyReminder::DURATION_DAY:
                        $add  = " +" . $surveyReminder[PluginSatisfactionSurveyReminder::COLUMN_DURATION] . " day";
                        $date = strtotime(date("Y-m-d", strtotime($lastSurveySendDate)) . $add);
                        $date = date('Y-m-d', $date);
                        break;

                     case PluginSatisfactionSurveyReminder::DURATION_MONTH:
                        $add  = " +" . $surveyReminder[PluginSatisfactionSurveyReminder::COLUMN_DURATION] . " month";
                        $date = strtotime(date("Y-m-d", strtotime($lastSurveySendDate)) . $add);
                        $date = date('Y-m-d', $date);
                        break;
                     default:
                        $date = null;
                  }

                  if (!is_null($date)) {
                     $potentialReminderToSendDates[] = ["tickets_id" => $ticketSatisfaction['tickets_id'],
                                                        "type"       => $surveyReminder['id'],
                                                        "date"       => $date];
                  }
               }
            }
            // Order dates
            if (!function_exists("date_sort")) {
               function date_sort($a, $b) {
                  return strtotime($a["date"]) - strtotime($b["date"]);
               }
            }
            usort($potentialReminderToSendDates, "date_sort");
            $dateNow = date("Y/m/d");

            if (isset($potentialReminderToSendDates[0])) {

               $potentialTimestamp = strtotime($potentialReminderToSendDates[0]['date']);
               $nowTimestamp       = strtotime($dateNow);
               //
               if ($potentialTimestamp <= $nowTimestamp) {
                  // Send notification
                  PluginSatisfactionNotificationTargetTicket::sendReminder($ticketSatisfaction['tickets_id']);
                  $self = new self();
                  $self->add([
                                'type'       => $potentialReminderToSendDates[0]['type'],
                                'tickets_id' => $ticketSatisfaction['tickets_id'],
                                'date'       => $dateNow
                             ]);
               }
            }
         }
      }
   }
}