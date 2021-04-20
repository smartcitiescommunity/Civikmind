<?php
include('../../../inc/includes.php');

Session::checkLoginUser();
if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}
$import = new PluginResourcesImport();
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
} else {
   $import->checkGlobal(READ);

   //central or helpdesk access
   if (Session::getCurrentInterface() == 'central') {
      Html::header(PluginResourcesMenu::getTypeName(2), '', "admin", "pluginresourcesmenu");
   } else {
      Html::helpHeader(PluginResourcesMenu::getTypeName(2));
   }

   if ($import->canView()) {
      $import->showTitle(false);
      $import->display(['id' => $_GET['id']]);

   } else {
      Html::displayRightError();
   }
   Html::footer();
}