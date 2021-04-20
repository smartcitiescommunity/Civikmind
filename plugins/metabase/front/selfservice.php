<?php

include ("../../../inc/includes.php");
Session::checkLoginUser();

Html::helpHeader(__('Metbase'), $_SERVER["PHP_SELF"]);
$central = new Central;
PluginMetabaseDashboard::showForCentral($central, 0, true);
Html::helpFooter();
