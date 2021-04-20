<?php
include ('../../../inc/includes.php');

Session::checkRight(PluginTagTag::$rightname, UPDATE);

$plugin = new Plugin();
if (! $plugin->isInstalled("tag")
    || ! $plugin->isActivated("tag")) {
   Html::displayNotFoundError();
}

if (isset($_POST['add'])) {
   $item = new PluginTagTagItem();

   // Check unicity :
   if (isset($_REQUEST['plugin_tag_tags_id'])) {
      $found = $item->find(['plugin_tag_tags_id' => $_REQUEST['plugin_tag_tags_id'],
                            'items_id' => $_REQUEST['items_id'],
                            'itemtype' => $_REQUEST['itemtype']]);

      if (count($found) == 0) {
         $item->add($_REQUEST);
      }
   } else {
      $item->add($_REQUEST);
   }
}

$dropdown = new PluginTagTag();
include (GLPI_ROOT . "/front/dropdown.common.form.php");
