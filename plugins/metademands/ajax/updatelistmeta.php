<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include('../../../inc/includes.php');
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkRight("plugin_metademands", READ);

if (isset($_POST["type"])) {
   global $CFG_GLPI;
   if (isset($_POST["action"]) && $_POST["action"] == "dropdown") {

      $meta   = new PluginMetademandsMetademand();
      $config = PluginMetademandsConfig::getInstance();
      $return = "<div class=\"bt-feature bt-col-sm-12 bt-col-md-12 \">";
      // METADEMAND list
      $return                 .= Ticket::getTicketTypeName($_POST["type"]);
      $return                 .= "&nbsp;";
      $options['empty_value'] = true;
      $options['type']        = $_POST["type"];
      $data                   = $meta->listMetademands(false, $options, $_POST["type"]);

      $return .= Dropdown::showFromArray('metademands_id', $data, ['width' => 250, 'display' => false]);
      $return .= "</div>";
      echo $return;
   } else if (isset($_POST["action"])
              && $_POST["action"] == "icon") {
      $return      = "";
      $metademands = PluginMetademandsWizard::selectMetademands("", $_POST["type"]);
      if (count($metademands) > 0) {
         foreach ($metademands as $id => $name) {

            $meta = new PluginMetademandsMetademand();
            if ($meta->getFromDB($id)) {

               $return .= "<a class='bt-buttons' href='" . $CFG_GLPI['root_doc'] . "/plugins/metademands/front/wizard.form.php?metademands_id=" . $id . "&step=2'>";
               $return .= '<div class="btnsc-normal" >';
               $fasize = "fa-6x";
               $return .= "<div class='center'>";
               $icon   = "fa-share-alt";
               if (!empty($meta->fields['icon'])) {
                  $icon = $meta->fields['icon'];
               }
               $return .= "<i class='bt-interface fa-menu-md fas $icon $fasize'></i>";//$style
               $return .= "</div>";
               $return .= "<br><p>";
               $return .= $meta->getName();
               $return .= "<br><em><span style=\"font-weight: normal;font-size: 11px;padding-left:5px\">";
               $return .= $meta->fields['comment'];
               $return .= "</span></em>";
               $return .= "</p></div></a>";
            }
         }
      } else {
         $return = __('No item found');
      }

      echo $return;
   }
}

Html::ajaxFooter();
