<?php
include('../../../inc/includes.php');
Html::header_nocache();

Session::checkLoginUser();

if (isset($_REQUEST['action'])) {
   switch ($_REQUEST['action']) {
      case 'removeReportFromDashboard':
         PluginMreportingDashboard::removeReportFromDashboard($_REQUEST['id']);
         break;

      case 'updateWidget':
         PluginMreportingDashboard::updateWidget($_REQUEST['id']);
         break;

      case 'getConfig':
         PluginMreportingDashboard::getConfig();
         break;

      case 'centralDashboard' :
         Html::includeHeader();
         echo "<body>";
         $dashboard = new PluginMreportingDashboard();
         $dashboard->showDashboard(false);

         //load protovis lib for dashboard render
         $version = Plugin::getInfo('mreporting', 'version');
         $php_dir = Plugin::getPhpDir('mreporting', false);
         echo Html::script($php_dir . "/lib/protovis/protovis.js", ['version' => $version]);

         Html::popFooter();
         break;

      default:
         echo 0;
   }
} else {
   echo 'No action defined';
}
