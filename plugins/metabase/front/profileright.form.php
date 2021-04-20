<?php

include ("../../../inc/includes.php");

if (isset($_REQUEST['update'])) {
   Session::checkRight('profile', UPDATE);

   if (!array_key_exists('profiles_id', $_REQUEST)
      || empty($_REQUEST['profiles_id'])
      || !array_key_exists('dashboard', $_REQUEST)
      || !is_array($_REQUEST['dashboard'])) {
      Session::addMessageAfterRedirect(
         __('Invalid request.', 'metabase'),
         false,
         ERROR
      );
      Html::back();
   }

   $viewableDashboardsUuids = [];
   foreach ($_REQUEST['dashboard'] as $dashboardUuid => $rights) {
      PluginMetabaseProfileright::setDashboardRightsForProfile(
         $_REQUEST['profiles_id'],
         $dashboardUuid,
         $rights
      );

      if ($rights & READ) {
         $viewableDashboardsUuids[] = $dashboardUuid;
      }
   }

   $apiclient = new PluginMetabaseAPIClient();
   $apiclient->enableDashboardsEmbeddedDisplay($viewableDashboardsUuids);
} else if (isset($_REQUEST['set_rights_to_all'])) {
   Session::checkRight('profile', UPDATE);

   if (!array_key_exists('profiles_id', $_REQUEST) || empty($_REQUEST['profiles_id'])) {
      Session::addMessageAfterRedirect(
         __('Invalid request.', 'metabase'),
         false,
         ERROR
      );
      Html::back();
   }

   $apiclient = new PluginMetabaseAPIClient();

   $viewableDashboardsUuids = [];
   foreach ($apiclient->getDashboards() as $dashboard) {
      PluginMetabaseProfileright::setDashboardRightsForProfile(
         $_REQUEST['profiles_id'],
         $dashboard['id'],
         $_REQUEST['set_rights_to_all']
      );

      $viewableDashboardsUuids[] = $dashboard['id'];
   }

   $apiclient->enableDashboardsEmbeddedDisplay($viewableDashboardsUuids);
}

Html::back();
