<?php
	
	include ('../../../inc/includes.php');
	
	Session::haveRight("config", UPDATE);
	
	Html::header(PluginProtocolsmanagerConfig::getTypeName(1),
               $_SERVER['PHP_SELF'], "plugins", "protocolsmanager", "config");
			   
	$PluginProtocolsmanagerConfig = new PluginProtocolsmanagerConfig();
	
	if (isset($_REQUEST['save'])) {
		$PluginProtocolsmanagerConfig::saveConfigs();
		$_SESSION['menu_mode'] = 't';
		Html::back();
		unset($_SESSION["menu_mode"]);
	}	
	
	if (isset($_REQUEST['delete'])) {
		$PluginProtocolsmanagerConfig::deleteConfigs();
		$_SESSION['menu_mode'] = 't';
		Html::back();
		unset($_SESSION["menu_mode"]);
	}

	if (isset($_REQUEST['save_email'])) {
		$PluginProtocolsmanagerConfig::saveEmailConfigs();
		$_SESSION['menu_mode'] = 'e';
		Html::back();
		unset($_SESSION["menu_mode"]);
	}	
	
	if (isset($_REQUEST['delete_email'])) {
		$PluginProtocolsmanagerConfig::deleteEmailConfigs();
		$_SESSION['menu_mode'] = 'e';
		Html::back();
		unset($_SESSION["menu_mode"]);
	}	
	
	if (isset($_REQUEST['cancel'])) {
		$_SESSION['menu_mode'] = 't';
		Html::back();
		unset($_SESSION["menu_mode"]);
	}	
	
	if (isset($_REQUEST['cancel_email'])) {
		$_SESSION['menu_mode'] = 'e';
		Html::back();
		unset($_SESSION["menu_mode"]);
	}
	

	
	$PluginProtocolsmanagerConfig->showFormProtocolsmanager();
	unset($_SESSION["menu_mode"]);
	
	
?>

<script>

/* $(function(){
	$("#template_button").click(function(){
		$("#template_settings").show();
		$("#show_configs").show();
		$("#email_settings").hide();
		$("#show_emailconfigs").hide();
	});	
	$("#email_button").click(function(){
		$("#template_settings").hide();
		$("#show_configs").hide();
		$("#email_settings").show();
		$("#show_emailconfigs").show();
	});
});	*/

</script>