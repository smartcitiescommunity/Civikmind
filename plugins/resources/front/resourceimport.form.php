<?php
include('../../../inc/includes.php');

Session::checkLoginUser();

$import = new PluginResourcesImport();

$pluginResourcesResourceImport = new PluginResourcesResourceImport();

if (isset($_POST['save'])){

   foreach($_POST['select'] as $key=>$selected) {

      if ($selected) {

         // Update
         if($_POST['resource'][$key]){
            $input = [
               'resourceID' => $_POST['resource'][$key],
               'datas' => $_POST['import'][$key]
            ];

            $pluginResourcesResourceImport->update($input);
            $pluginResourcesImportResource = new PluginResourcesImportResource();
            $pluginResourcesImportResource->delete(['id' => $key]);
         }
         //New
         else{
            $import->check(-1, CREATE, $_POST);
            $input = [
               'importID' => $key,
               'datas' => $_POST['import'][$key]
            ];

            $pluginResourcesResourceImport->add($input);
            $pluginResourcesImportResource = new PluginResourcesImportResource();
            $pluginResourcesImportResource->delete(['id' => $key]);
         }
      }
   }
   redirectWithParameters(PluginResourcesImportResource::getIndexUrl(), $_GET);

} else if (isset($_POST["purge"])) {

   $import->check($_POST['id'], PURGE);
   $pluginResourcesResourceImport->delete($_POST);
   redirectWithParameters(PluginResourcesImportResource::getIndexUrl(), $_GET);

} else if (isset($_POST["delete"])){
   foreach($_POST['select'] as $key=>$selected){
      if($selected){
         $pluginResourcesImportResource = new PluginResourcesImportResource();

         $input = [
            PluginResourcesImportResource::getIndexName() => $key
         ];

         $pluginResourcesImportResource->delete($input);
      }
   }
   redirectWithParameters(PluginResourcesImportResource::getIndexUrl(), $_GET);
}
Html::displayErrorAndDie('Lost');

function redirectWithParameters($url, array $parameters){

   $params = "";
   if(count($parameters)){
      $iterator = 0;
      foreach($parameters as $key=>$parameter){
         if($iterator===0){
            $params.= "?$key=$parameter";
         }
         else{
            $params.= "&$key=$parameter";
         }
         $iterator++;
      }
   }
   Html::redirect($url.$params);
}