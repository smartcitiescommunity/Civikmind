<?php

include ('../../../inc/includes.php');

$plugin=new Plugin();
if (!$plugin->isInstalled('jsaddons') || !$plugin->isActivated('jsaddons')) {
	Html::displayNotFoundError();
}
$jsaddons= new PluginJsaddonsJsaddon();
if (isset($_POST['update'])) {

   //Check UPDATE
   $jsaddons->check($_POST['id'], UPDATE);
   //Do object update
   $jsaddons->update($_POST);
   //Redirect to object form
   Html::back();

} else {

   $jsaddons->checkGlobal(READ);

   Html::header(
		PluginJsaddonsJsaddon::getTypeName(2),
		'',
		'config',
		'pluginjsaddonsjsaddon'
	);
   $jsaddons->display($_GET);
   Html::footer();
}
