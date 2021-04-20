<?php
include('../../../inc/includes.php');

Session::checkLoginUser();
if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
$import = new PluginResourcesImportColumn();
if (isset($_POST["add"])) {
   $import->check(-1, CREATE, $_POST);
   $import->add($_POST);
   Html::back();
} else if (isset($_POST["purge"])) {
   $import->check($_POST['id'], PURGE);
   $import->delete($_POST);
   $import->redirectToList();
} else if (isset($_POST["update"])) {
   $import->check($_POST['id'], UPDATE);
   $import->update($_POST);
   Html::back();
}
Html::displayErrorAndDie('Lost');