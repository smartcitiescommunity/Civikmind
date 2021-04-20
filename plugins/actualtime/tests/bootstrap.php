<?php
// fix empty CFG_GLPI on boostrap; see https://github.com/sebastianbergmann/phpunit/issues/325
global $CFG_GLPI;

//define plugin paths
define("PLUGINACTUALTIME_DOC_DIR", __DIR__ . "/generated_test_data");

define('GLPI_ROOT', dirname(__DIR__, 3));
define("GLPI_CONFIG_DIR", GLPI_ROOT . "/tests");
include GLPI_ROOT . "/inc/includes.php";
include_once GLPI_ROOT . '/tests/GLPITestCase.php';
include_once GLPI_ROOT . '/tests/DbTestCase.php';

require_once Plugin::getPhpDir('actualtime') . '/setup.php';

//install plugin
$plugin = new \Plugin();
//Glpi 9.4 does not initialize plugin table, so new plugins will not show
//until you navigate to Plugins menu (let's force it, then)
$plugin->checkStates(true);
$plugin->getFromDBbyDir('actualtime');

//check from prerequisites as Plugin::install() does not!
if (!plugin_actualtime_check_prerequisites()) {
   echo "\nPrerequisites are not met!";
   die(1);
}

if (!$plugin->isInstalled('actualtime')) {
   call_user_func([$plugin, 'install'], $plugin->getID());
}

if (!$plugin->isActivated('actualtime')) {
   call_user_func([$plugin, 'activate'], $plugin->getID());
}
