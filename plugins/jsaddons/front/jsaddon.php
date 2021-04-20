<?php

include ('../../../inc/includes.php');

$plugin=new Plugin();
if (!$plugin->isInstalled('jsaddons') || !$plugin->isActivated('jsaddons')) {
	Html::displayNotFoundError();
}

Html::header(
	PluginJsaddonsJsaddon::getTypeName(2),
	'',
	'config',
	'pluginjsaddonsjsaddon'
);

$jsaddons= new PluginJsaddonsJsaddon();
$jsaddons->checkGlobal(READ);
if ($jsaddons->canView()) {
	Search::show('PluginJsaddonsJsaddon');
}else{
	Html::displayRightError();
}

Html::footer();
