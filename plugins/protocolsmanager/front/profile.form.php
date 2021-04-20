<?php
include ('../../../inc/includes.php');
$PluginProtocolsmanagerProfile = new PluginProtocolsmanagerProfile();

if (isset($_REQUEST['update'])) {
	$PluginProtocolsmanagerProfile::updateRights();
	Html::back();
}

?>