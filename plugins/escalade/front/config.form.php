<?php
include ("../../../inc/includes.php");

if (! isset($_GET["id"])) {
   $_GET["id"] = 0;
}

$plugin = new Plugin();
if (! $plugin->isInstalled('escalade') || ! $plugin->isActivated('escalade')) {
   echo "Plugin not installed or activated";
   return;
}

$config = new PluginEscaladeConfig();

if (isset($_POST["add"])) {

   Session::checkRight("config", CREATE);
   $newID=$config->add($_POST);
   Html::back();

} else if (isset($_POST["update"])) {

   Session::checkRight("config", UPDATE);
   $config->update($_POST);
   Html::back();

} else if (isset($_POST["delete"])) {

   Session::checkRight("config", DELETE);
   $config->delete($_POST, 1);
   Html::redirect("./config.form.php");

} else {

   Html::header(__("Escalation", "escalade"), '', "plugins", "escalade", "config");
   $config->showForm(1);
   Html::footer();

}
