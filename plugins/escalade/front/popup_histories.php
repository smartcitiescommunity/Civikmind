<?php
include ("../../../inc/includes.php");

Html::popHeader(__("full assignation history", "escalade"), $_SERVER['PHP_SELF']);
echo "<div class='center'><br><a href='javascript:window.close()'>".__("Close")."</a>";
echo "</div>";

echo "<div id='page'>";
PluginEscaladeHistory::getHistory($_REQUEST['tickets_id'], true);
echo "</div>";

Html::popFooter();