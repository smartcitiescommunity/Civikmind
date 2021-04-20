<?php
/**
 * ------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of SCCM plugin.
 *
 * SCCM plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * SCCM plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * ------------------------------------------------------------------------
 * @author    François Legastelois <flegastelois@teclib.com>
 * @copyright Copyright (C) 2014-2018 by Teclib' and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/sccm
 * @link      https://pluginsglpi.github.io/sccm/
 * ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

Session::haveRight("config", UPDATE);

Html::header(__('SCCM - TEST', 'sccm'),
             $_SERVER["PHP_SELF"],
             "plugins",
             "sccm",
             "Test");

echo "<div class='center spaced'>";
echo "<form method=\"POST\" action=\"".$_SERVER["PHP_SELF"]."?task=test\">";
echo '<input type="hidden" name="go_test" />';
echo "<table class='tab_cadrehov' style='width:20%;'>";
echo "<tr class='tab_bg_1'><td colspan='2' class='center b'>";
   echo '<select name="type_test">';
      echo '<option value="view">Voir HTML</option>';
      echo '<option value="add">Injecter PC</option>';
      echo '<option value="nbPcToInject">Nombre de PC à injecter</option>';
      echo '<option value="viewList">Liste des PC à injecter</option>';
   echo '</select>';
echo "</td></tr>";
echo "<tr class='tab_bg_1'>";
echo "<td class='center b'>";
   echo 'Limit : <input type="text" name="limit" size="10" />';
echo "</td>";
echo "<td class='center b'>";
   echo 'Where : <input type="text" name="where" size="10" />';
echo "</td>";
echo "</tr>";
echo "<tr class='tab_bg_1'><td colspan='2' class='center b'>";
   echo '<input type="submit" class="submit" value="Lancer le test" name="submit" />';
echo "</td></tr>";
echo "</table>";
Html::closeForm();
echo "</div>";


echo "<div class='center spaced'>";
echo "<table class='tab_glpi'>";
echo "<tr class='tab_bg_1'><td class='center b'>";
if (isset($_POST["go_test"])) {

   $limit = isset($_POST['limit']) ? $_POST['limit'] : 99999999;
   $where = isset($_POST['where']) ? $_POST['where'] : 0;

   switch ($_POST["type_test"]) {
      case 'view'       : testViewHtml($limit, $where);   break;
      case 'add'        : testAdd($where);            break;
      case 'nbPcToInject' : nbPcToInject();           break;
      case 'viewList' : viewList();             break;
   }
}
echo "</td></tr>";
echo "</table>";
echo "</div>";

Html::footer();


function viewList() {
   global $PluginSccmSccm;

   $PluginSccmSccm->getDevices();

   echo "<table class='tab_glpi'>";
   foreach ($PluginSccmSccm->devices as $device_values) {
      echo "<tr><td>".$device_values['MD-SystemName']."</td></tr>";
   }
   echo "</table>";
}

function nbPcToInject() {
   global $PluginSccmSccm;

   $PluginSccmSccm->getDevices();

   echo count($PluginSccmSccm->devices);
}

function testViewHtml($limit, $where) {
   global $PluginSccmSccm;

   $PluginSccmSccm->getDevices($where);

   foreach ($PluginSccmSccm->devices as $device_values) {
      $PluginSccmSccmxml = new PluginSccmSccmxml($device_values);

      $PluginSccmSccmxml->setAccessLog();
      $PluginSccmSccmxml->setAccountInfos();
      $PluginSccmSccmxml->setHardware();
      $PluginSccmSccmxml->setOS();
      $PluginSccmSccmxml->setBios();
      $PluginSccmSccmxml->setProcessors();
      $PluginSccmSccmxml->setSoftwares();
      $PluginSccmSccmxml->setUsers();
      $PluginSccmSccmxml->setNetworks();
      $PluginSccmSccmxml->setDrives();

      $SXML = $PluginSccmSccmxml->sxml;

      Html::printCleanArray($PluginSccmSccmxml->object2array($SXML));
   }
}

function testAdd($where) {
   global $PluginSccmSccm,
         $PluginSccmConfig;

   $PluginSccmSccm->getDevices($where);

   $REP_XML = GLPI_PLUGIN_DOC_DIR.'/sccm/xml/';

   foreach ($PluginSccmSccm->devices as $device_values) {
      $PluginSccmSccmxml = new PluginSccmSccmxml($device_values);

      $PluginSccmSccmxml->setAccessLog();
      $PluginSccmSccmxml->setAccountInfos();
      $PluginSccmSccmxml->setHardware();
      $PluginSccmSccmxml->setOS();
      $PluginSccmSccmxml->setBios();
      $PluginSccmSccmxml->setProcessors();
      $PluginSccmSccmxml->setSoftwares();
      $PluginSccmSccmxml->setUsers();
      $PluginSccmSccmxml->setNetworks();
      $PluginSccmSccmxml->setDrives();

      $SXML = $PluginSccmSccmxml->sxml;

      $SXML->asXML($REP_XML.$PluginSccmSccmxml->device_id.".ocs");

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $PluginSccmConfig->getField('fusioninventory_url'));
      curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $SXML->asXML());
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
      curl_setopt($ch, CURLOPT_REFERER, $PluginSccmConfig->getField('fusioninventory_url'));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $ch_result = curl_exec($ch);
      curl_close($ch);

      echo "Ajout OK";
   }

}