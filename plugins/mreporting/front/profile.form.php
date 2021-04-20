<?php
include ("../../../inc/includes.php");

Session::checkRight("profile", READ);

$config = new PluginMreportingConfig();
$res = $config->find();
$profil = new PluginMreportingProfile();

//Save profile
if (isset ($_REQUEST['update'])) {
   foreach ($res as $report) {
      if (class_exists($report['classname'])) {
         $access = $_REQUEST[$report['id']];

         $profil->getFromDBByCrit(
            [
               'profiles_id' => $_REQUEST['profile_id'],
               'reports'     => $report['id'],
            ]
         );
         $profil->fields['right'] = $access;
         $profil->update($profil->fields);
      }
   }

} else if (isset ($_REQUEST['add'])) {
   $query = "SELECT `id`, `name`
   FROM `glpi_profiles` WHERE `interface` = 'central'
   ORDER BY `name`";

   foreach ($DB->request($query) as $profile) {
      $access = $_REQUEST[$profile['id']];

      $profil->getFromDBByCrit(
         [
            'profiles_id' => $profile['id'],
            'reports'     => $_REQUEST['report_id'],
         ]
      );
      $profil->fields['right'] = $access;
      $profil->update($profil->fields);
   }

} else if (isset($_REQUEST['giveReadAccessForAllReport'])) {
   foreach ($res as $report) {
      $profil->getFromDBByCrit(
         [
            'profiles_id' => $_REQUEST['profile_id'],
            'reports'     => $report['id'],
         ]
      );
      $profil->fields['right'] = READ;
      $profil->update($profil->fields);
   }

} else if (isset($_REQUEST['giveNoneAccessForAllReport'])) {
   foreach ($res as $report) {
      $profil->getFromDBByCrit(
         [
            'profiles_id' => $_REQUEST['profile_id'],
            'reports'     => $report['id'],
         ]
      );
      $profil->fields['right'] = 'NULL';
      $profil->update($profil->fields);
   }

} else if (isset($_REQUEST['giveNoneAccessForAllProfile'])) {
   $query = "SELECT `id`, `name`
   FROM `glpi_profiles`
   ORDER BY `name`";

   foreach ($DB->request($query) as $profile) {
      $profil->getFromDBByCrit(
         [
            'profiles_id' => $profile['id'],
            'reports'     => $_REQUEST['report_id'],
         ]
      );
      $profil->fields['right'] = 'NULL';
      $profil->update($profil->fields);
   }

} else if (isset($_REQUEST['giveReadAccessForAllProfile'])) {
   $query = "SELECT `id`, `name`
   FROM `glpi_profiles`
   ORDER BY `name`";

   foreach ($DB->request($query) as $profile) {
      $profil->getFromDBByCrit(
         [
            'profiles_id' => $profile['id'],
            'reports'     => $_REQUEST['report_id'],
         ]
      );
      $profil->fields['right'] = READ;
      $profil->update($profil->fields);
   }

}
Html::back();
