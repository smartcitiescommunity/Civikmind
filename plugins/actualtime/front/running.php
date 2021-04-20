<?php
include('../../../inc/includes.php');

global $CFG_GLPI;

$plugin = new Plugin();
if (!$plugin->isInstalled('actualtime') || !$plugin->isActivated('actualtime')) {
   Html::displayNotFoundError();
}
Session::checkRight('user', READ);

Html::header(PluginActualtimeRunning::getTypeName(Session::getPluralNumber()), '', "admin", "pluginactualtimerunning");

PluginActualtimeRunning::show();

Html::footer();