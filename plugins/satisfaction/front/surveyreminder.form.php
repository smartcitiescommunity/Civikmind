<?php

include('../../../inc/includes.php');

Session::checkLoginUser();

$reminder = new PluginSatisfactionSurveyReminder();

if (isset($_POST["add"])) {

   $input = $_POST;

   if(isset($input[$reminder::PREDEFINED_REMINDER_OPTION_NAME])){
      $input = $reminder->generatePredefinedReminderForAdd($input);
   }

   $reminder->check(-1, CREATE, $input);
   $reminder->add($input);
   Html::back();

} else if (isset($_POST["update"])) {
   $reminder->check($_POST['id'], UPDATE);
   $reminder->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $reminder->check($_POST['id'], PURGE);
   $reminder->delete($_POST);
   Html::back();

}

Html::displayErrorAndDie('Lost');
