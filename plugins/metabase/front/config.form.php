<?php

include ("../../../inc/includes.php");

if (isset($_REQUEST["create_database"])) {
   PluginMetabaseConfig::createGLPIDatabase();
   Html::back();

} else if (isset($_REQUEST["set_database"])) {
   PluginMetabaseConfig::setExistingDatabase((int) $_REQUEST['db_id']);
   Html::back();

} else if (isset($_REQUEST["push_json"])) {
   PluginMetabaseConfig::pushReports();
   PluginMetabaseConfig::pushDashboards();
   Html::back();

} else if (isset($_REQUEST["push_datamodel"])) {
   PluginMetabaseConfig::createDataModel((int) $_REQUEST['glpi_db_id']);
   Html::back();

} else {
   Html::redirect($CFG_GLPI["root_doc"]."/front/config.form.php?forcetab=PluginMetabaseConfig\$1");
}