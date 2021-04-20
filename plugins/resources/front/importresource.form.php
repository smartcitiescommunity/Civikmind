<?php
include('../../../inc/includes.php');

Session::checkLoginUser();

$importResource = new PluginResourcesImportResource();
if (isset($_POST["add"])) {
   $importResource->add($_POST);
   Html::back();
} else if (isset($_POST["purge"])) {
   $importResource->delete($_POST);
   Html::back();
} else if (isset($_POST["update"])) {
   $importResource->update($_POST);
   Html::back();
} else if (isset($_POST["import-file"])){
   $importResource->importFileToVerify($_POST);
   Html::back();
} else if (isset($_GET["reset-imports"])){
   $importResource->purgeDatabase();

   $importResourceDataDBTM = new PluginResourcesImportResourceData();
   $importResourceDataDBTM->purgeDatabase();
   Html::back();
}
Html::displayErrorAndDie('Lost');