<?php
include ("../../../inc/includes.php");

Session::checkCentralAccess();

$level = new PluginItilcategorygroupsGroup_Level();

if (isset($_POST["add"])) {
   $level->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {
   $level->update($_POST);
   Html::back();

}
Html::displayErrorAndDie("lost");
