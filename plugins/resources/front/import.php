<?php

include('../../../inc/includes.php');

//central or helpdesk access
if (Session::getCurrentInterface() == 'central') {
   Html::header(PluginResourcesMenu::getTypeName(2), '', "admin", "pluginresourcesmenu");
} else {
   Html::helpHeader(PluginResourcesMenu::getTypeName(2));
}

$import = new PluginResourcesImport();
$import->checkGlobal(READ);

if ($import->canView()) {

   $import->showTitle();
   Search::show('PluginResourcesImport');

} else {
   Html::displayRightError();
}

Html::footer();
