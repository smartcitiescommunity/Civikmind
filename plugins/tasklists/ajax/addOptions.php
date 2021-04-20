<?php

$AJAX_INCLUDE = 1;

include ('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
   // Get AJAX input and load it into $_REQUEST
   $input = file_get_contents('php://input');
   parse_str($input, $_REQUEST);
}

if (!isset($_REQUEST['action'])) {
   Toolbox::logError("Missing action parameter");
   http_response_code(400);
   return;
}
$action = $_REQUEST['action'];


if ($_REQUEST['action'] == 'addArchived') {


   header("Content-Type: application/json; charset=UTF-8", true);
   $states = [];
   $states[0] = __("Not archived",'tasklists');
   $states[1] = __("Archived",'tasklists');


//
   if(!isset( $_SESSION["archive"][Session::getLoginUserID()])){
      $_SESSION["archive"][Session::getLoginUserID()] = json_encode([0]);
   }
   if($_SESSION["archive"][Session::getLoginUserID()] != "" && $_SESSION["archive"][Session::getLoginUserID()] != "null" ){
      $arch = Dropdown::showFromArray("archive", $states, array('id'=> 'archive','multiple' => true, 'values' => json_decode($_SESSION["archive"][Session::getLoginUserID()],true),"display" => false));
   }else{
      $arch = Dropdown::showFromArray("archive", $states, array('id'=> 'archive','multiple' => true, 'value' => 0,"display" => false));

   }

   echo json_encode($arch, JSON_FORCE_OBJECT);
} else if ($_REQUEST['action'] == 'changeArchive') {
   if(!empty($_REQUEST['vals']))
      $_SESSION["archive"][Session::getLoginUserID()] = json_encode($_REQUEST['vals']);

}if ($_REQUEST['action'] == 'addUsers') {


   header("Content-Type: application/json; charset=UTF-8", true);
   $users = PluginTasklistsTaskType::findUsers($_REQUEST['context']);


//
   if(!isset( $_SESSION["usersKanban"][Session::getLoginUserID()])){
      $_SESSION["usersKanban"][Session::getLoginUserID()] = json_encode([-1]);
   }
   if($_SESSION["usersKanban"][Session::getLoginUserID()] != "" && isset($_SESSION["archive"]) && $_SESSION["archive"][Session::getLoginUserID()] != "null" ){
      $arch = Dropdown::showFromArray("usersKanban", $users, array('id'=> 'users','multiple' => true, 'values' => json_decode($_SESSION["usersKanban"][Session::getLoginUserID()],true),"display" => false));
   }else{
      $arch = Dropdown::showFromArray("usersKanban", $users, array('id'=> 'users','multiple' => true, 'value' => -1,"display" => false));
   }

   echo json_encode($arch, JSON_FORCE_OBJECT);
}else if ($_REQUEST['action'] == 'changeUsers') {
   if(!empty($_REQUEST['vals']))
      $_SESSION["usersKanban"][Session::getLoginUserID()] = json_encode($_REQUEST['vals']);

}