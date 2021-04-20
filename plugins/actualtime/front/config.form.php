<?php

include('../../../inc/includes.php');

global $CFG_GLPI;
// Check if plugin is activated...
$plugin = new Plugin();
if (!$plugin->isInstalled('actualtime') || !$plugin->isActivated('actualtime')) {
   Html::displayNotFoundError();
}

Session::checkRight('config', UPDATE);

$config = new PluginActualtimeConfig();

if (isset($_POST["update"])) {
   $config->update($_POST);

   PluginActualtimeConfig::getConfig(true);
   Html::back();

} else {
   Html::redirect($CFG_GLPI["root_doc"]."/front/config.form.php?forcetab=".
      urlencode('PluginActualtimeConfig$1'));
}
