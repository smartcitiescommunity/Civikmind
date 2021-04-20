<?php

include ("../../../inc/includes.php");

//change mimetype
header("Content-type: application/javascript");

if (!$plugin->isInstalled("xivo")
    || !$plugin->isActivated("xivo")) {
   exit;
}

// retrieve plugin config
$xivoconfig = json_encode(PluginXivoConfig::getConfig(), JSON_NUMERIC_CHECK);

//check constants to disable builtin features
$enable_presence = PLUGIN_XIVO_ENABLE_PRESENCE;
$enable_callcenter = PLUGIN_XIVO_ENABLE_CALLCENTER;

$JS = <<<JAVASCRIPT

// pass php xivo config to javascript
var xivo_config = $xivoconfig;

// disable features from constants
xivo_config.enable_presence &= $enable_presence;
xivo_config.enable_callcenter &= $enable_callcenter;

$(function() {
   // call xuc integration
   xuc_obj = new Xuc();
   xuc_obj.init(xivo_config);

   // append 'callto:' links to domready events and also after tabs change
   if (xivo_config.enable_click2call) {
      xuc_obj.click2Call();
      $(".glpi_tabs").on("tabsload", function(event, ui) {
         xuc_obj.click2Call();
      });
   }
});

JAVASCRIPT;
echo $JS;
