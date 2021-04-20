<?php

function plugin_version_protocolsmanager() {
	return array('name'           => "Protocols manager",
                'version'        => '1.4.2',
                'author'         => 'Mateusz Nitka',
                'license'        => 'GPLv3+',
                'homepage'       => 'https://github.com/mateusznitka/protocolsmanager',
                'minGlpiVersion' => '9.3');
}

function plugin_protocolsmanager_check_config() {
    return true;
}
 

function plugin_protocolsmanager_check_prerequisites() { 
		if (GLPI_VERSION>=9.3){
                return true;
        } else {
                echo "GLPI version NOT compatible. Requires GLPI 9.3";
        }
}

function plugin_init_protocolsmanager() {
	global $PLUGIN_HOOKS;

	$PLUGIN_HOOKS['csrf_compliant']['protocolsmanager'] = true;
	
	$PLUGIN_HOOKS['config_page']['protocolsmanager'] = 'front/config.form.php';
   
	Plugin::registerClass('PluginProtocolsmanagerGenerate', array('addtabon' => array('User')));
	
	Plugin::registerClass('PluginProtocolsmanagerProfile', array('addtabon' => array('Profile')));
	
	Plugin::registerClass('PluginProtocolsmanagerConfig', array('addtabon' => array('Config')));
	
	$PLUGIN_HOOKS['add_css']['protocolsmanager'] = 'css/styles.css';
	
}

?>