<?php

include('../../../inc/includes.php');

Session::checkLoginUser();

$question = new PluginSatisfactionSurveyQuestion();

if (isset($_POST["add"])) {
   $question->check(-1, CREATE, $_POST);
   $question->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {
   $question->check($_POST['id'], UPDATE);
   $question->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {
   $question->check($_POST['id'], PURGE);
   $question->delete($_POST);
   Html::back();

}

Html::displayErrorAndDie('Lost');
