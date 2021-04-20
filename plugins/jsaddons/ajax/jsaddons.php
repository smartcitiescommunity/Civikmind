<?php

include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

if (isset($_POST['list'])) {
	$script=PluginJsaddonsJsaddon::getScript();
	echo json_encode($script);
}
