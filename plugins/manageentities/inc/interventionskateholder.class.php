<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginManageentitiesInterventionSkateholder extends CommonDBTM {

   static $rightname = 'plugin_manageentities';

   static function getTypeName($nb = 0) {
      return _n('User affected', 'Users affected', $nb, 'manageentities');
   }

   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   static function countForItem(CommonDBTM $item) {

      $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_manageentities_interventionskateholders',
                                        ["plugin_manageentities_contractdays_id" => $item->fields['id']]);
   }

   function defineTabs($options = []) {

      $ong = [];
      $this->addStandardTab(__CLASS__, $ong, $options);

      return $ong;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$withtemplate) {
         switch ($item->getType()) {
            case 'PluginManageentitiesContractDay' :
               if ($_SESSION['glpishow_count_on_tabs']) {
                  return self::createTabEntry(PluginManageentitiesInterventionSkateholder::getTypeName(self::countForItem($item)), self::countForItem($item));
               } else {
                  return PluginManageentitiesInterventionSkateholder::getTypeName($item);
               }
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $interventionSkateholder = new PluginManageentitiesInterventionSkateholder();
      if ($item->getType() == 'PluginManageentitiesContractDay') {
         $options = [];
         if (isset($item->fields['id']) && $item->fields['id'] > 0) {
            $options['rand']                               = $item->fields['id'];
            $_SESSION['glpi_plugin_manageentities_nbdays'] = $item->fields['nbday'];
         } else {
            $options['rand']                               = 0;
            $_SESSION['glpi_plugin_manageentities_nbdays'] = 0;
         }
         $interventionSkateholder->showForm($item, $options);
         echo "<div id='divAjaxDisplay" . $item->fields['id'] . "'></div>";
      }
      return true;
   }


   public function reinitValuesNbDays($idDpNbdays, $contractdaysId) {
      $nbDays = $this->getNbAvailiableDay($contractdaysId);

      $this->showHeaderJS();
      for ($i = 0; $i <= $nbDays; $i += 0.5) {
         $data[] = ['id' => $i, 'text' => "$i"];
      }
      echo "$('input[name=\"nb_days\"]').select2({width : '100', data:" . json_encode($data) . "});";
      $this->closeFormJS();
   }

   public function reinitListSkateholders($item, $idDpNbdays = null, $contractdaysId, $toDelete = false) {
      global $CFG_GLPI;

      if ($item->getType() == PluginManageentitiesInterventionSkateholder::getType()) {
         $idToUse   = $item->fields['plugin_manageentities_contractdays_id'];
         $idDivAjax = "divAjaxDisplay" . $item->fields['plugin_manageentities_contractdays_id'];
      } else {
         $idToUse   = $item->fields['id'];
         $idDivAjax = "divAjaxDisplay" . $item->fields['id'];
      }

      $user = new User();
      $user->getFromDB($item->fields['users_id']);
      $condition = ["`plugin_manageentities_contractdays_id`" => $item->fields['plugin_manageentities_contractdays_id']];

      $this->showHeaderJS();

      echo "var tbl = document.getElementById('list_skateholders" . $idToUse . "');\n";

      $dbu = new DbUtils();
      if ($toDelete) {
         echo "var row = document.getElementById('row_" . $item->fields['id'] . "');";
         echo "row.parentNode.removeChild(row);";
         $cd = $dbu->getAllDataFromTable($this->getTable(), $condition);
         if (sizeof($cd) == 0) {
            echo "if(document.getElementById('empty_skateholders" . $idToUse . "') != null){";
            echo "   tbl.deleteRow(-1);";
            echo "}else{";
            echo "   row=tbl.insertRow(-1);\n";
            echo "   row=tbl.insertRow(-1);\n";
            echo "   row.setAttribute('class','tab_bg_1');\n";
            echo "   row.id='empty_skateholders" . $idToUse . "';";
            echo "   var tmpCell=row.insertCell(0);\n";
            echo "   tmpCell.innerHTML=\"" . __("No stakeholders have been affected yet.", "manageentities") . "\";";
            echo "}";
         }

      } else {
         echo "if (document.getElementById('td_user_id" . $item->fields['id'] . "') != null){\n";
         echo "   document.getElementById('td_user_id" . $item->fields['id'] . "').innerHTML = '" . $item->fields['number_affected_days'] . " " . _n("Day", "Days", 2) . "';\n";
         echo "}else{\n";
         echo "   if (document.getElementById('empty_skateholders" . $idToUse . "') != null){";
         echo "      tbl.deleteRow(-1);";
         echo "   }";

         echo "row=tbl.insertRow(-1);\n";
         echo "row.id='row_" . $item->fields['id'] . "';\n";
         echo "row.setAttribute('class','tab_bg_1');\n";

         // UserTitle
         echo "var tmpCell=row.insertCell(0);\n";
         echo "tmpCell.innerHTML=\"";
         echo __("User");
         echo "\";\n";
         $link = $user->getLinkURL();

         // User name
         echo "tmpCell=row.insertCell(1);";
         echo "tmpCell.innerHTML=\"";
         echo "<a href='" . $link . "' target='_blank'>" . $dbu->formatUserName($user->fields['id'], $user->fields['name'], $user->fields['realname'], $user->fields['firstname']) . "</a>";
         echo "\";";

         // NbDays title
         echo "tmpCell=row.insertCell(2);\n";
         echo "tmpCell.innerHTML=\"";
         echo __("Affected to", "manageentities");

         echo "\";\n";

         // NbDays
         echo "tmpCell=row.insertCell(3);";
         echo "tmpCell.id='td_user_id" . $item->fields['id'] . "';";
         echo "tmpCell.innerHTML=\"";
         echo $item->fields['number_affected_days'] . "&nbsp;" . _n("Day", "Days", 2);
         echo "\";";

         // Delete
         echo "tmpCell=row.insertCell(4);";
         echo "tmpCell.innerHTML=\"";
         echo "<span class='pointer'>";
         echo "<i title=\"" . __("Delete", "manageentities") . "\" class=\"far fa-trash-alt\" id='delete_" . $user->fields['id'] . "'></i>";
         echo "</span>";
         echo "\";";
         echo "}";

         echo "document.getElementById('delete_" . $user->fields['id'] . "').onclick= function () {if (confirm('" . __("This action is irreversible. Continue ?", 'manageentities') . "')){deleteSkateholder" . $idToUse . $item->fields['id'] . "();}};";
      }

      $this->closeFormJS();

      if (!$toDelete) {
         $params['action']          = "delete_user_datas";
         $params['id_div_ajax']     = $idDivAjax;
         $params['id_dp_nbdays']    = "nb_days" . $item->fields['plugin_manageentities_contractdays_id'];
         $params['contractdays_id'] = $item->fields['plugin_manageentities_contractdays_id'];
         $params['skateholder_id']  = $item->fields['id'];
         $url                       = $CFG_GLPI ['root_doc'] . "/plugins/manageentities/ajax/interventionskateholderactions.php";

         $this->showJSfunction("deleteSkateholder" . $idToUse . $item->fields['id'], $idDivAjax, $url, [], $params);
      }

      if ($idDpNbdays != null) {
         $this->reinitValuesNbDays('nb_days2', $contractdaysId);
      }
   }

   private function listSkateholders($item, $options = []) {
      global $CFG_GLPI;
      $ID = $item->fields['id'];

      if ($item->getType() == PluginManageentitiesInterventionSkateholder::getType()) {
         $idToUse   = $item->fields['plugin_manageentities_contractdays_id'];
         $idDivAjax = "divAjaxDisplay" . $item->fields['plugin_manageentities_contractdays_id'];
      } else {
         $idToUse   = $item->fields['id'];
         $idDivAjax = "divAjaxDisplay" . $item->fields['id'];
      }

      $contractday = new PluginManageentitiesContractDay();

      if ($ID > 0) {
         $contractday->getFromDB($ID);
      } else {
         // Create iteml
         $canedit = $contractday->can($item->fields['id'], UPDATE);
         $contractday->getEmpty();
         $this->getEmpty();
         $this->fields["plugin_manageentities_contractdays_id"] = $item->fields['id'];
      }


      if ($item->fields['id'] > 0) {
         $condition        = ["`plugin_manageentities_contractdays_id`" => $item->fields['id']];
         $dbu              = new DbUtils();
         $listSkateholders = $dbu->getAllDataFromTable($this->getTable(), $condition);
         echo "<div class='center first-bloc'>";
         echo "<table class='tab_cadre_fixe' id='list_skateholders" . $idToUse . "'>";
         //         echo "<input type='hidden' name='skateholder_id' id='skateholder_id' value='-1' />";

         echo "<tr class='tab_bg_1'>";

         if (sizeof($listSkateholders) > 1) {
            if ($this->canCreate()) {
               echo "<th colspan='5'>" . _n('Current stakeholder', 'Current stakeholders', 2, 'manageentities') . "</th>";
            } else {
               echo "<th colspan='4'>" . _n('Current stakeholder', 'Current stakeholders', 2, 'manageentities') . "</th>";
            }
         } else {
            if ($this->canCreate()) {
               echo "<th colspan='5'>" . _n('Current stakeholder', 'Current stakeholders', 1, 'manageentities') . "</th>";
            } else {
               echo "<th colspan='4'>" . _n('Current stakeholder', 'Current stakeholders', 1, 'manageentities') . "</th>";
            }
         }
         echo "</tr>";

         if (sizeof($listSkateholders) > 0) {

            foreach ($listSkateholders as $skateholder) {
               $user = new User();

               $user->getFromDB($skateholder['users_id']);
               if (isset($user->fields['id'])) {
                  $link = $user->getLinkURL();
                  echo "<tr class='tab_bg_1' id='row_" . $skateholder['id'] . "'>";
                  echo "<td>" . __("User") . "</td>";
                  echo "<td>";
                  echo "<a href='" . $link . "' target='_blank'>" . $dbu->formatUserName($user->fields['id'], $user->fields['name'], $user->fields['realname'], $user->fields['firstname']) . "</a>";
                  echo "</td>";

                  echo "<td>" . __("Affected to", "manageentities") . "</td>";
                  echo "<td id='td_user_id" . $skateholder['id'] . "'>";
                  echo $skateholder['number_affected_days'] . "&nbsp;" . _n("Day", "Days", 2);
                  echo "</td>";

                  if ($this->canCreate()) {
                     echo "<td>";
                     echo "<span class='pointer'>";
                     echo "<i title=\"" . __("Delete", "manageentities") . "\" class=\"far fa-trash-alt\" id='delete_" . $user->fields['id'] . "' 
                     onclick=\"javascript:if (confirm('" . __("This action is irreversible. Continue ?", 'manageentities') . "')){deleteSkateholder" . $idToUse . $skateholder['id'] . "();}\"></i>";
                     echo "&nbsp;</span>";
                     echo "</td>";
                  }
                  echo "</tr>";


                  $params['action']          = "delete_user_datas";
                  $params['id_div_ajax']     = $idDivAjax;
                  $params['id_dp_nbdays']    = "nb_days" . $skateholder['plugin_manageentities_contractdays_id'];
                  $params['contractdays_id'] = $item->fields['id'];
                  $params['skateholder_id']  = $skateholder['id'];
                  $url                       = $CFG_GLPI ['root_doc'] . "/plugins/manageentities/ajax/interventionskateholderactions.php";

                  $this->showJSfunction("deleteSkateholder" . $idToUse . $skateholder['id'], $idDivAjax, $url, [], $params);

               }
            }
         } else {
            echo "<tr class='tab_bg_1' id='empty_skateholders" . $idToUse . "'><td>" . __("No skateholders have been affected yet.", "manageentities") . "</td></tr>";
         }
         echo "</table>";
         echo "</div>";
      }
   }


   public function hideAddForm($idToUse) {
      $this->showHeaderJS();
      echo "var tbl = $('#global_form_content" . $idToUse . "').hide();";
      $this->closeFormJS();
   }

   public function showAddForm($idToUse) {
      $this->showHeaderJS();
      echo "var tbl = $('#global_form_content" . $idToUse . "').show();";
      $this->closeFormJS();
   }


   /**
    * Print the field form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return Nothing (display)
    */
   public function showForm($item = [], $options = []) {
      global $CFG_GLPI;

      if ($item->getType() == PluginManageentitiesInterventionSkateholder::getType()) {
         $idToUse   = $item->fields['plugin_manageentities_contractdays_id'];
         $idDivAjax = "tabskateholderajax" . $item->fields['plugin_manageentities_contractdays_id'];
      } else {
         $idToUse   = $item->fields['id'];
         $idDivAjax = "tabskateholderajax" . $item->fields['id'];
      }

      if (!isset($options['display_list']) || $options['display_list'] != "false") {
         $this->listSkateholders($item);
      }

      if ($this->canCreate()) {
         $rand = 0;
         if (isset($options['rand'])) {
            $rand = $options['rand'];
         }

         $ID                                            = $item->fields['id'];
         $contractday                                   = new PluginManageentitiesContractDay();
         $nbDays                                        = $this->getNbAvailiableDay($item->fields['id']);
         $url                                           = $CFG_GLPI ['root_doc'] . "/plugins/manageentities/ajax/interventionskateholderactions.php";
         $_SESSION['glpi_plugin_manageentities_nbdays'] -= $nbDays;

         if ($ID > 0) {
            $contractday->getFromDB($ID);
         } else {
            // Create item
            $contractday->getEmpty();
            $this->getEmpty();
            $this->fields["plugin_manageentities_contractdays_id"] = $item->fields['id'];
         }

         echo "<div class='center first-bloc' " . (($nbDays > 0) ? "" : "style='display:none") . " id='global_form_content" . $idToUse . "'>";
         echo "<table class='tab_cadre_fixe' id='tbl_add_skateholder" . $idToUse . "'>";
         echo "<input type='hidden' name='action' id='action' value='add_user_datas' />";

         echo "<tr class='tab_bg_1'>";
         echo "<th colspan='6'>" . __('Add skateholders', 'manageentities') . "</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         // User
         echo "<td>" . __('User') . "<span style='color:red;'>&nbsp;*&nbsp;</span></td>";
         echo "<td>";
         $idUser = User::dropdown(['name'  => 'users_id_tech' . $idToUse,
                                   'right' => 'interface']);
         echo "</td>";
         // Nb days
         echo "<td>" . __('Affected to', 'manageentities') . "<span style='color:red;'>&nbsp;*&nbsp;</span></td>";
         echo "<td id='nb_days_container'>";
         PluginManageentitiesDropdown::showNumber("nb_days", ["width" => 100, "min" => 0, "max" => $nbDays, "step" => "0.5", "rand" => $rand]);
         $config = PluginManageentitiesConfig::getInstance();
         if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
            echo "&nbsp;" . _n("Day", "Days", 2);
         } else {
            echo "&nbsp;" . _n("Hour", "Hours", 2);
         }
         echo "</td>";

         echo "<td>";
         echo "<input type='hidden' name='id_user' id='id_user' value='dropdown_users_id_tech" . $idUser . "' />";
         echo "<input type='button' class='submit' name='add_skateholder' id='add_skateholder' value='" . _sx("button", "Add") . "' onclick='addSkateholder" . $idToUse . "();' />";
         echo "</td>";
         echo "</tr>";
         echo "</table>";
         echo "<div id='" . $idDivAjax . "' style='text-align:center;'></div>";
         echo "</div>";

         $listIds = [
            "dropdown_nb_days" . $idToUse                 => ["dropdown", "nb_days"],
            "dropdown_users_id_tech" . $idToUse . $idUser => ["dropdown", "users_id_tech"],
         ];

         $params = [
            'action'          => "add_user_datas",
            'id_dp_nbdays'    => "dropdown_nb_days" . $idToUse,
            'id_div_ajax'     => $idDivAjax,
            "contractdays_id" => $item->fields['id']
         ];

         $this->showJSfunction("addSkateholder" . $idToUse, $idDivAjax, $url, $listIds, $params);
      }
   }

   public function showJSfunction($functionName, $idDivAjax, $url, $listId, $params, $additionalDiv = null) {
      $this->showHeaderJS();
      echo "function " . $functionName . "() {\n";

      $divReturned = "";
      if ($additionalDiv != null) {
         $divReturned = $additionalDiv;
      } else {
         $divReturned = $idDivAjax;
      }

      echo Html::jsGetElementbyID($divReturned) . ".load(\n
            '" . $url . "'\n";

      echo ",{";
      $first = true;
      foreach ($listId as $key => $val) {
         if ($first) {
            $first = false;
         } else {
            echo ",";
         }
         switch ($val[0]) {
            case "checkbox":
               echo $val[1] . ":" . Html::jsGetElementbyID(Html::cleanId($key)) . ".is(':checked')";
               break;
            case "dropdown":
               echo $val[1] . ":" . Html::jsGetElementbyID(Html::cleanId($key)) . ".val()";
               break;
            case "text":
            default:
               echo $val[1] . ":" . Html::jsGetElementbyID(Html::cleanId($key)) . ".val()";
               break;
         }
      }

      foreach ($params as $key => $val) {
         if ($first) {
            $first = false;
         } else {
            echo ",";
         }
         echo $key . ":'" . $val . "'";
      }
      echo "}\n";
      echo ");";
      echo "}";
      $this->closeFormJS();
   }


   public function getNbAvailiableDay($contractdays_id) {
      $contractDay = new PluginManageentitiesContractDay();
      $contractDay->getFromDB($contractdays_id);
      $nbMaxDays = $contractDay->fields['nbday'];

      $condition            = ["plugin_manageentities_contractdays_id" => $contractdays_id];
      $dbu                  = new DbUtils();
      $listInterventionDays = $dbu->getAllDataFromTable($this->getTable(), $condition);

      if (sizeof($listInterventionDays) == 0) {
         return $nbMaxDays;
      } else {
         foreach ($listInterventionDays as $intervention) {
            $nbMaxDays -= $intervention['number_affected_days'];
         }
      }
      return $nbMaxDays;
   }


   public function showMessage($message, $messageType, $with = -1, $height = -1) {
      $srcImg     = "";
      $alertTitle = "";
      switch ($messageType) {
         case ERROR:
            $srcImg     = "fas fa-exclamation-triangle";
            $color      = "orange";
            $alertTitle = __("Warning");
            break;
         case INFO:
         default:
            $srcImg     = "fas fa-info-circle";
            $color      = "forestgreen";
            $alertTitle = _n("Information", "Informations", 1);
            break;
      }

      $this->showHeaderJS();
      echo " if ($('#alert-message').val()){
               $('#alert-message').val('');
            }";
      $this->closeFormJS();

      echo "<div id='alert-message' class='tab_cadre_navigation_center' style='display:none;'>" . $message . "</div>";

      $this->showHeaderJS();
      echo "var mTitle =  \"<i class='" . $srcImg . " fa-1x' style='color:" . $color . "'></i>&nbsp;" . $alertTitle . " \";";
      echo "$( '#alert-message' ).dialog({
        autoOpen: false,
        height: " . ($height > 0 ? $height : 150) . ",
        width: " . ($with > 0 ? $with : 250) . ",
        modal: true,
        open: function (){
         $(this)
            .parent()
            .children('.ui-dialog-titlebar')
            .html(mTitle);
      },
        buttons: {
         'ok': function() {
            $( this ).dialog( 'close' );
         }
      },
      beforeClose: function(event) {
            $('#alert-message').remove();";


      echo "      return false;
      }
    });
    
    $( '#alert-message' ).dialog( 'open' );";


      $this->closeFormJS();

   }

   private function showHeaderJS() {
      echo "\n<script type='text/javascript'>\n";
   }

   private function closeFormJS() {
      echo "</script>\n";
   }

}