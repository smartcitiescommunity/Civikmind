<?php

include ("../../../inc/includes.php");

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkLoginUser();

if (!isset($_REQUEST['id'])
    || !isset($_REQUEST['type'])) {
   exit;
}

switch ($_REQUEST['type']) {
   case 'question':
      PluginMetabaseConfig::displayQuestionJson((int) $_REQUEST['id']);
      break;
   case 'dashboard':
      PluginMetabaseConfig::displayDashboardJson((int) $_REQUEST['id']);
      break;
}
