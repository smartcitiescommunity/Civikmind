<?php
include ('../../../inc/includes.php');

$plugin = new Plugin();
if ($plugin->isInstalled("tag")
    && $plugin->isActivated("tag")) {

   $dropdown = new PluginTagTag();
   include (GLPI_ROOT . "/front/dropdown.common.php");
}
