<?php

include ("../../../inc/includes.php");
use Glpi\Event;
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

global $CFG_GLPI;

if (isset($_POST["action"])) {
	echo PluginActualtimeRunning::listRunning();
}