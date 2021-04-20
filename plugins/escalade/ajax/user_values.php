<?php

$AJAX_INCLUDE = 1;
include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();
Session::checkLoginUser();

$ticket_id = (isset($_REQUEST['ticket_id'])) ? $_REQUEST['ticket_id'] : 0;

$PluginEscaladeGroup_Group = new PluginEscaladeGroup_Group();
$groups_id_filtred         = $PluginEscaladeGroup_Group->getGroups($ticket_id, false);

$groups = [];
if (count($groups_id_filtred) > 0) {
   foreach ($groups_id_filtred as $groups_id => $groups_name) {
      $groups[] = $groups_id;
   }
}

$_SESSION['glpigroups']  = $groups;
$_GET['right']           = 'groups';

require ("../../../ajax/getDropdownUsers.php");
