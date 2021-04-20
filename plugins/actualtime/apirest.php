<?php

define('GLPI_ROOT',  __DIR__.'/../../');
define('DO_NOT_CHECK_HTTP_REFERER', 1);
ini_set('session.use_cookies', 0);

include_once (GLPI_ROOT . "/inc/based_config.php");
include_once(Plugin::getPhpDir('actualtime')."/inc/apirest.class.php");

$GLPI_CACHE = Config::getCache('cache_db');

$api = new PluginActualtimeApirest();
$api->call();