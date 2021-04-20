<?php

function plugin_jsaddons_install(){
	$migration=new Migration(PLUGIN_JSADDONS_VERSION);

	foreach (glob(__DIR__ .'/inc/*') as $filepath) {
		if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
			$classname = 'PluginJsaddons' . ucfirst($matches[1]);
			include_once($filepath);
			if (method_exists($classname, 'install')) {
				$classname::install($migration);
			}
		}
	}

	$migration->executeMigration();

	return true;
}

function plugin_jsaddons_uninstall(){
	$migration=new Migration(PLUGIN_JSADDONS_VERSION);

	foreach (glob(__DIR__ .'/inc/*') as $filepath) {
		if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
			$classname = 'PluginJsaddons' . ucfirst($matches[1]);
			include_once($filepath);
			if (method_exists($classname, 'install')) {
				$classname::uninstall($migration);
			}
		}
	}

	$migration->executeMigration();

	return true;
}

function plugin_jsaddons_login(){
	$version = Plugin::getInfo('jsaddons', 'version');
	echo Html::script(Plugin::getWebDir('jsaddons', false)."/js/jsaddons.js", ['version' => $version]);
}