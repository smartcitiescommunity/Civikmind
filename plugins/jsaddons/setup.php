<?php

define('PLUGIN_JSADDONS_VERSION','1.0.0');

define('PLUGIN_JSADDONS_MIN_GLPI','9.5.0');
define('PLUGIN_JSADDONS_MAX_GLPI','9.6');


function plugin_version_jsaddons(){
	return [
		'name'=>'JS Addons',
		'version'=>PLUGIN_JSADDONS_VERSION,
		'author'=>'<a href="https://tic.gal">TICgal</a>',
		'homepage' => 'https://tic.gal/en/jsaddons',
		'requirements' => [
			'glpi' => [
				'min' => PLUGIN_JSADDONS_MIN_GLPI,
				'max' => PLUGIN_JSADDONS_MAX_GLPI,
			]
		]
	];
}

function plugin_init_jsaddons(){
	global $PLUGIN_HOOKS,$CFG_GLPI;
	$PLUGIN_HOOKS['csrf_compliant']['jsaddons']=true;
	$plugin=new Plugin();
	if ($plugin->isActivated('jsaddons')) {
		$PLUGIN_HOOKS['menu_toadd']['jsaddons'] = [
			'config' => 'PluginJsaddonsJsaddon',
		];
		$PLUGIN_HOOKS['add_javascript']['jsaddons'][]="js/jsaddons.js";
		$PLUGIN_HOOKS['display_login']['jsaddons']="plugin_jsaddons_login";
	}
}