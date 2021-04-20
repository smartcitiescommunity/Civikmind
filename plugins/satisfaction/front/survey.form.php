<?php

include('../../../inc/includes.php');

Session::checkLoginUser();

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$survey = new PluginSatisfactionSurvey();

if (isset($_POST["add"])) {
   $survey->check(-1, CREATE, $_POST);
   $survey->add($_POST);
   Html::back();

} else if (isset($_POST["purge"])) {
   $survey->check($_POST['id'], PURGE);
   $survey->delete($_POST);
   $survey->redirectToList();

} else if (isset($_POST["update"])) {
   $survey->check($_POST['id'], UPDATE);
   $survey->update($_POST);
   Html::back();

} else {

   $survey->checkGlobal(READ);

   Html::header(PluginSatisfactionSurvey::getTypeName(2), '', "admin", "pluginsatisfactionmenu", "survey");

   $survey->display(['id' => $_GET['id']]);

   Html::footer();
}
