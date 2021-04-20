<?php

include('../../../inc/includes.php');

Session::checkLoginUser();
if (!isset($_GET["type"])) {
   $_GET["type"] = 0;
}

//central or helpdesk access
if (Session::getCurrentInterface() == 'central') {
   Html::header(PluginResourcesMenu::getTypeName(2), '', "admin", "pluginresourcesmenu");
} else {
   Html::helpHeader(PluginResourcesMenu::getTypeName(2));
}

$import = new PluginResourcesImport();
$import->checkGlobal(READ);

$importResource = new PluginResourcesImportResource();

if ($import->canView()) {

   $params = [
      "type" => $_GET['type'],
      "start"=> 0
   ];

   if(isset($_GET['start'])){
      $params['start'] = $_GET['start'];
   }

   if(isset($_POST['glpilist_limit'])){
      $params['limit'] = $_POST['glpilist_limit'];
   }else if(isset($_SESSION['glpilist_limit'])){
      $params['limit'] = $_SESSION['glpilist_limit'];
   }else {
      $params['limit'] = PluginResourcesImportResource::DEFAULT_LIMIT;
   }

   if(isset($_POST['_file_to_compare']) && count($_POST['_file_to_compare']) == 1){
      $params['filename'] = $_POST['_file_to_compare'][0];
   }else if(isset($_GET['_file_to_compare']) && count($_GET['_file_to_compare']) == 1){
      $params['filename'] = $_GET['_file_to_compare'][0];
   }

   $dropdownName = PluginResourcesImportResource::SELECTED_FILE_DROPDOWN_NAME;

   if(isset($_POST[$dropdownName]) && !empty($_POST[$dropdownName])){
      $params[$dropdownName] = $_POST[$dropdownName];
   } else if(isset($_GET[$dropdownName]) && !empty($_GET[$dropdownName])){
      $params[$dropdownName] = $_GET[$dropdownName];
   }

   $dropdownName = PluginResourcesImportResource::SELECTED_IMPORT_DROPDOWN_NAME;

   if(isset($_POST[$dropdownName]) && !empty($_POST[$dropdownName])){
      $params[$dropdownName] = $_POST[$dropdownName];
   } else if(isset($_GET[$dropdownName]) && !empty($_GET[$dropdownName])){
      $params[$dropdownName] = $_GET[$dropdownName];
   }

   $importResource->displayPageByType($params);

} else {
   Html::displayRightError();
}

Html::footer();
