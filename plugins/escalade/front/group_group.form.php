<?php
include ("../../../inc/includes.php");

Html::header("escalade", $_SERVER["PHP_SELF"], "plugins", "escalade", "group_group");

if (Session::haveRight('group', UPDATE)) {
   if (isset($_POST['addgroup'])) {
      $PluginEscaladeGroup_Group = new PluginEscaladeGroup_Group();
      $PluginEscaladeGroup_Group->add($_POST);
   }

   if (isset($_POST['deleteitem'])) {
      $PluginEscaladeGroup_Group = new PluginEscaladeGroup_Group();
      foreach ($_POST['delgroup'] as $id) {
         $PluginEscaladeGroup_Group->delete(['id' => $id]);
      }
   }
}

Html::back();
Html::footer();
