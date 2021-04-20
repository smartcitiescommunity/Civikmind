<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 resources plugin for GLPI
 Copyright (C) 2009-2016 by the resources Development Team.

 https://github.com/InfotelGLPI/resources
 -------------------------------------------------------------------------

 LICENSE

 This file is part of resources.

 resources is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 resources is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with resources. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginResourcesResourceHoliday
 */
class PluginResourcesResourceHoliday extends CommonDBTM {

   static $rightname = 'plugin_resources_holiday';

   public $dohistory = true;

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {

      return _n('Holiday', 'Holidays', $nb, 'resources');
   }

   /**
    * Have I the global right to "view" the Object
    *
    * Default is true and check entity if the objet is entity assign
    *
    * May be overloaded if needed
    *
    * @return booleen
    **/
   static function canView() {
      return Session::haveRight(self::$rightname, READ);
   }

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    **/
   static function canCreate() {
      return Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, DELETE]);
   }

   /**
    * Prepare input datas for adding the item
    *
    * @param array $input datas used to add the item
    *
    * @return array the modified $input array
    **/
   function prepareInputForAdd($input) {

      if (!isset ($input["date_begin"]) || $input["date_begin"] == 'NULL') {
         Session::addMessageAfterRedirect(__('The begin date of the forced holiday period must be filled', 'resources'), false, ERROR);
         return [];
      }
      if (!isset ($input["date_end"]) || $input["date_end"] == 'NULL') {
         Session::addMessageAfterRedirect(__('The end date of the forced holiday period must be filled', 'resources'), false, ERROR);
         return [];
      }

      return $input;
   }

   function post_addItem() {
      global $CFG_GLPI;

      Session::addMessageAfterRedirect(__('Forced holiday declaration of a resource performed', 'resources'));

      $PluginResourcesResource = new PluginResourcesResource();
      if ($CFG_GLPI["notifications_mailing"]) {
         $options = ['holiday_id' => $this->fields["id"]];
         if ($PluginResourcesResource->getFromDB($this->fields["plugin_resources_resources_id"])) {
            NotificationEvent::raiseEvent("newholiday", $PluginResourcesResource, $options);
         }
      }
   }

   /**
    * Prepare input datas for updating the item
    *
    * @param array $input data used to update the item
    *
    * @return array the modified $input array
    **/
   function prepareInputForUpdate($input) {
      if (!isset ($input["date_begin"]) || $input["date_begin"] == 'NULL') {
         Session::addMessageAfterRedirect(__('The begin date of the forced holiday period must be filled', 'resources'), false, ERROR);
         return [];
      }
      if (!isset ($input["date_end"]) || $input["date_end"] == 'NULL') {
         Session::addMessageAfterRedirect(__('The end date of the forced holiday period must be filled', 'resources'), false, ERROR);
         return [];
      }

      //unset($input['picture']);
      $this->getFromDB($input["id"]);

      $input["_old_date_begin"] = $this->fields["date_begin"];
      $input["_old_date_end"]   = $this->fields["date_end"];
      $input["_old_comment"]    = $this->fields["comment"];

      return $input;
   }

   /**
    * Actions done after the UPDATE of the item in the database
    *
    * @param boolean $history store changes history ? (default 1)
    *
    * @return void
    **/
   function post_updateItem($history = 1) {
      global $CFG_GLPI;

      if ($CFG_GLPI["notifications_mailing"] && count($this->updates)) {
         $options                 = ['holiday_id' => $this->fields["id"],
                                     'oldvalues'  => $this->oldvalues];
         $PluginResourcesResource = new PluginResourcesResource();
         if ($PluginResourcesResource->getFromDB($this->fields["plugin_resources_resources_id"])) {
            NotificationEvent::raiseEvent("updateholiday", $PluginResourcesResource, $options);
         }
      }
   }

   /**
    * Actions done before the DELETE of the item in the database /
    * Maybe used to add another check for deletion
    *
    * @return boolean true if item need to be deleted else false
    **/
   function pre_deleteItem() {
      global $CFG_GLPI;

      if ($CFG_GLPI["notifications_mailing"]) {
         $PluginResourcesResource = new PluginResourcesResource();
         $options                 = ['holiday_id' => $this->fields["id"]];
         if ($PluginResourcesResource->getFromDB($this->fields["plugin_resources_resources_id"])) {
            NotificationEvent::raiseEvent("deleteholiday", $PluginResourcesResource, $options);
         }
      }
      return true;
   }

   /**
    * Provides search options configuration. Do not rely directly
    * on this, @see CommonDBTM::searchOptions instead.
    *
    * @since 9.3
    *
    * This should be overloaded in Class
    *
    * @return array a *not indexed* array of search options
    *
    * @see https://glpi-developer-documentation.rtfd.io/en/master/devapi/search.html
    **/
   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'   => 'common',
         'name' => self::getTypeName(2)
      ];

      $tab[] = [
         'id'            => '1',
         'table'         => 'glpi_plugin_resources_resources',
         'field'         => 'name',
         'name'          => __('Surname'),
         'datatype'      => 'itemlink',
         'itemlink_type' => $this->getType()
      ];

      if (!Session::haveRight("plugin_resources_all", READ)) {
         $tab[] = [
            'id'         => '1',
            'searchtype' => 'contains'
         ];
      }

      $tab[] = [
         'id'    => '2',
         'table' => 'glpi_plugin_resources_resources',
         'field' => 'firstname',
         'name'  => __('First name')
      ];

      $tab[] = [
         'id'       => '3',
         'table'    => $this->getTable(),
         'field'    => 'date_begin',
         'name'     => __('Begin date'),
         'datatype' => 'date'
      ];

      $tab[] = [
         'id'       => '4',
         'table'    => $this->getTable(),
         'field'    => 'date_end',
         'name'     => __('End date'),
         'datatype' => 'date'
      ];

      $tab[] = [
         'id'       => '5',
         'table'    => $this->getTable(),
         'field'    => 'comment',
         'name'     => __('Comments'),
         'datatype' => 'text'
      ];

      $tab[] = [
         'id'            => '30',
         'table'         => $this->getTable(),
         'field'         => 'id',
         'name'          => __('ID'),
         'datatype'      => 'number',
         'massiveaction' => false
      ];

      return $tab;
   }

   /**
    *Menu
    */
   function showMenu() {
      global $CFG_GLPI;

      echo "<div align='center'><table class='tab_cadre' width='30%' cellpadding='5'>";
      echo "<tr><th colspan='2'>" . __('Forced holiday management', 'resources') . "</th></tr>";

      $canholiday = Session::haveright('plugin_resources_holiday', UPDATE);

      echo "<tr class='tab_bg_1'>";
      if ($canholiday) {
         echo "<td class='center'>";
         echo "<a href=\"./resourceholiday.form.php\">";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/holidayresource.png' alt='" . __('Declare a forced holiday', 'resources') . "'>";
         echo "<br>" . __('Declare a forced holiday', 'resources') . "</a>";
         echo "</td>";
         echo "<td class='center'>";
         echo "<a href=\"./resourceholiday.php\">";
         echo "<img src='" . $CFG_GLPI["root_doc"] . "/plugins/resources/pics/holidaylist.png' alt='" . __('List of forced holidays', 'resources') . "'>";
         echo "<br>" . __('List of forced holidays', 'resources') . "</a>";
         echo "</td>";
      }
      echo "</tr></table>";
      Html::closeForm();

      echo "</div>";

   }

   //Show form from helpdesk to add holiday of a resource

   /**
    * @param       $ID
    * @param array $options
    */
   function showForm($ID, $options = []) {
      global $CFG_GLPI;

      $this->initForm($ID, $options);

      echo Html::css("/plugins/resources/css/style_bootstrap_main.css");
      echo Html::css("/plugins/resources/css/style_bootstrap_ticket.css");
      echo Html::script("/plugins/resources/lib/bootstrap/3.2.0/js/bootstrap.min.js");
      echo "<div id ='content'>";
      echo "<div class='bt-container resources_wizard_resp'> ";
      echo "<div class='bt-block bt-features' > ";

      echo "<form method='post' action=\"" . $CFG_GLPI["root_doc"] . "/plugins/resources/front/resourceholiday.form.php\">";

      echo "<div class=\"bt-row\">";
      echo "<div class=\"bt-feature bt-col-sm-12 bt-col-md-12 \" style='border-bottom: #CCC;border-bottom-style: solid;'>";
      echo "<h4 class=\"bt-title-divider\">";
      echo "<img class='resources_wizard_resp_img' src='" . $CFG_GLPI['root_doc'] . "/plugins/resources/pics/holidayresource.png' alt='holidayresource'/>&nbsp;";
      $title = __('Declare a forced holiday', 'resources');
      if ($ID > 0) {
         $title = __('Detail of the forced holiday', 'resources');
      }
      echo $title;
      echo "</h4></div></div>";

      echo "<div class=\"bt-row\">";
      echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
      echo PluginResourcesResource::getTypeName(1);
      echo "</div>";
      echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
      PluginResourcesResource::dropdown(['name'    => 'plugin_resources_resources_id',
                                         'display' => true,
                                         'value'   => $this->fields["plugin_resources_resources_id"],
                                         'entity'  => $_SESSION['glpiactiveentities']]);
      echo "</div>";
      echo "</div>";

      echo "<div class=\"bt-row\">";
      echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
      echo __('Begin date');
      echo "</div>";
      echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
      Html::showDateField("date_begin", ['value' => $this->fields["date_begin"]]);
      echo "</div>";
      echo "</div>";

      echo "<div class=\"bt-row\">";
      echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
      echo __('End date');
      echo "</div>";
      echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
      Html::showDateField("date_end", ['value' => $this->fields["date_end"]]);
      echo "</div>";
      echo "</div>";

      echo "<div class=\"bt-row\">";
      echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
      echo __('Comments');
      echo "</div>";
      echo "<div class=\"bt-feature bt-col-sm-4 bt-col-md-4 \">";
      echo "<textarea cols='70' rows='4' name='comment' >" . $this->fields["comment"] . "</textarea>";
      echo "</div>";
      echo "</div>";

      echo "<div class=\"bt-row\">";
      echo "<div class=\"bt-feature bt-col-sm-12 bt-col-md-12 \">";
      echo "<div class='preview'>";
      echo "<a href=\"./resourceholiday.form.php\">";
      echo __('Declare a forced holiday', 'resources');
      echo "</a>";
      echo "&nbsp;/&nbsp;<a href=\"./resourceholiday.php\">";
      echo __('List of forced holidays', 'resources');
      echo "</a>";
      echo "</div>";
      echo "</div></div>";

      echo "<div class=\"bt-row\">";
      echo "<div class=\"bt-feature bt-col-sm-12 bt-col-md-12 \">";
      echo "<div class='next'>";
      if ($ID > 0) {
         echo "<input type='hidden' name='id' value='" . $ID . "' />";
         echo Html::hidden('plugin_resources_resources_id', ['value' => $this->fields["plugin_resources_resources_id"]]);
         echo "<input type='submit' name='updateholidayresources' value=\"" . _sx('button', 'Update') . "\" class='submit' />";
         echo "&nbsp;&nbsp;<input type='submit' name='deleteholidayresources' value=\"" . _sx('button', 'Delete permanently') . "\" class='submit' />";
      } else {
         echo "<input type='submit' name='addholidayresources' value='" . _sx('button', 'Add') . "' class='submit' />";
      }
      echo "</div>";
      echo "</div></div>";

      Html::closeForm();

      echo "</div>";
      echo "</div>";
      echo "</div>";

   }

   /**
    * Print generic search form
    *
    * @param $itemtype type to display the form
    * @param $params parameters array may include field, contains, sort, is_deleted, link, link2, contains2, field2,
    *    type2
    *
    * @return nothing (displays)
    *
    **/
   function showGenericSearch($params) {
      global $CFG_GLPI;

      $itemtype  = $this->getType();
      $itemtable = $this->getTable();

      // Default values of parameters
      $p['link']        = [];//
      $p['field']       = [];
      $p['contains']    = [];
      $p['searchtype']  = [];
      $p['sort']        = '';
      $p['is_deleted']  = 0;
      $p['link2']       = '';//
      $p['contains2']   = '';
      $p['field2']      = '';
      $p['itemtype2']   = '';
      $p['searchtype2'] = '';

      foreach ($params as $key => $val) {
         $p[$key] = $val;
      }

      $options = Search::getCleanedOptions("PluginResourcesResourceHoliday");
      //$target = Toolbox::getItemTypeSearchURL($itemtype);
      $target = $CFG_GLPI["root_doc"] . "/plugins/resources/front/resourceholiday.php";
      // Instanciate an object to access method
      $item = null;
      if (class_exists($itemtype)) {
         $item = new $itemtype();
      }

      $linked = Search::getMetaItemtypeAvailable($itemtype);

      echo "<form name='searchform$itemtype' method='get' action=\"$target\">";
      echo "<table class='tab_cadre_fixe' >";
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo "<table>";

      // Display normal search parameters
      for ($i = 0; $i < $_SESSION["glpisearchcount"][$itemtype]; $i++) {
         echo "<tr><td class='left' width='50%'>";

         // First line display add / delete images for normal and meta search items
         if ($i == 0) {
            echo "<input type='hidden' disabled  id='add_search_count' name='add_search_count' value='1'>";
            echo "<a href='#' onClick = \"document.getElementById('add_search_count').disabled=false;document.forms['searchform$itemtype'].submit();\">";
            echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/plus.png\" alt='+' title='" .
                 __('Add a search criterion') . "'></a>&nbsp;&nbsp;&nbsp;&nbsp;";
            if ($_SESSION["glpisearchcount"][$itemtype] > 1) {
               echo "<input type='hidden' disabled  id='delete_search_count' name='delete_search_count' value='1'>";
               echo "<a href='#' onClick = \"document.getElementById('delete_search_count').disabled=false;document.forms['searchform$itemtype'].submit();\">";
               echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/moins.png\" alt='-' title='" .
                    __('Delete a search criterion') . "'></a>&nbsp;&nbsp;&nbsp;&nbsp;";
            }
            if (is_array($linked) && count($linked) > 0) {
               echo "<input type='hidden' disabled id='add_search_count2' name='add_search_count2' value='1'>";
               echo "<a href='#' onClick = \"document.getElementById('add_search_count2').disabled=false;document.forms['searchform$itemtype'].submit();\">";
               echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/meta_plus.png\" alt='+' title='" .
                    __('Add a global search criterion') . "'></a>&nbsp;&nbsp;&nbsp;&nbsp;";
               if ($_SESSION["glpisearchcount2"][$itemtype] > 0) {
                  echo "<input type='hidden' disabled  id='delete_search_count2' name='delete_search_count2' value='1'>";
                  echo "<a href='#' onClick = \"document.getElementById('delete_search_count2').disabled=false;document.forms['searchform$itemtype'].submit();\">";
                  echo "<img src=\"" . $CFG_GLPI["root_doc"] . "/pics/meta_moins.png\" alt='-' title='" .
                       __('Delete a global search criterion') . "'></a>&nbsp;&nbsp;&nbsp;&nbsp;";
               }
            }
            $dbu       = new DbUtils();
            $itemtable = $dbu->getTableForItemType($itemtype);
         }

         // Display link item
         if ($i > 0) {
            echo "<select name='link[$i]'>";
            echo "<option value='AND' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "AND") {
               echo "selected";
            }
            echo ">AND</option>\n";

            echo "<option value='OR' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "OR") {
               echo "selected";
            }
            echo ">OR</option>\n";

            echo "<option value='AND NOT' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "AND NOT") {
               echo "selected";
            }
            echo ">AND NOT</option>\n";

            echo "<option value='OR NOT' ";
            if (is_array($p["link"]) && isset($p["link"][$i]) && $p["link"][$i] == "OR NOT") {
               echo "selected";
            }
            echo ">OR NOT</option>";
            echo "</select>&nbsp;";
         }

         // display select box to define serach item
         echo "<select id='Search$itemtype$i' name=\"field[$i]\" size='1'>";
         echo "<option value='view' ";
         if (is_array($p['field']) && isset($p['field'][$i]) && $p['field'][$i] == "view") {
            echo "selected";
         }
         echo ">" . __('Items seen') . "</option>\n";

         reset($options);
         $first_group = true;
         $selected    = 'view';
         foreach ($options as $key => $val) {
            // print groups
            if (!is_array($val)) {
               if (!$first_group) {
                  echo "</optgroup>\n";
               } else {
                  $first_group = false;
               }
               echo "<optgroup label='$val'>";
            } else {
               if (!isset($val['nosearch']) || $val['nosearch'] == false) {
                  echo "<option title=\"" . Html::cleanInputText($val["name"]) . "\" value='$key'";
                  if (is_array($p['field']) && isset($p['field'][$i]) && $key == $p['field'][$i]) {
                     echo "selected";
                     $selected = $key;
                  }
                  echo ">" . Toolbox::substr($val["name"], 0, 28) . "</option>\n";
               }
            }
         }
         if (!$first_group) {
            echo "</optgroup>\n";
         }
         echo "<option value='all' ";
         if (is_array($p['field']) && isset($p['field'][$i]) && $p['field'][$i] == "all") {
            echo "selected";
         }
         echo ">" . __('All') . "</option>";
         echo "</select>&nbsp;\n";

         echo "</td><td class='left'>";
         echo "<div id='SearchSpan$itemtype$i'>\n";

         $_POST['itemtype']   = $itemtype;
         $_POST['num']        = $i;
         $_POST['field']      = $selected;
         $_POST['searchtype'] = (is_array($p['searchtype']) && isset($p['searchtype'][$i]) ? $p['searchtype'][$i] : "");
         $_POST['value']      = (is_array($p['contains']) && isset($p['contains'][$i]) ? stripslashes($p['contains'][$i]) : "");
         include(GLPI_ROOT . "/ajax/searchoption.php");
         echo "</div>\n";

         $params = ['field'      => '__VALUE__',
                    'itemtype'   => $itemtype,
                    'num'        => $i,
                    'value'      => $_POST["value"],
                    'searchtype' => $_POST["searchtype"]];
         Ajax::updateItemOnSelectEvent("Search$itemtype$i", "SearchSpan$itemtype$i",
                                       $CFG_GLPI["root_doc"] . "/ajax/searchoption.php", $params, false);

         echo "</td></tr>\n";
      }

      $metanames = [];

      if (is_array($linked) && count($linked) > 0) {
         for ($i = 0; $i < $_SESSION["glpisearchcount2"][$itemtype]; $i++) {
            echo "<tr><td class='left'>";
            $rand = mt_rand();

            // Display link item (not for the first item)
            echo "<select name='link2[$i]'>";
            echo "<option value='AND' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "AND") {
               echo "selected";
            }
            echo ">AND</option>\n";

            echo "<option value='OR' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "OR") {
               echo "selected";
            }
            echo ">OR</option>\n";

            echo "<option value='AND NOT' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "AND NOT") {
               echo "selected";
            }
            echo ">AND NOT</option>\n";

            echo "<option value='OR NOT' ";
            if (is_array($p['link2']) && isset($p['link2'][$i]) && $p['link2'][$i] == "OR NOT") {
               echo "selected";
            }
            echo ">OR NOT</option>\n";
            echo "</select>&nbsp;";

            // Display select of the linked item type available
            echo "<select name='itemtype2[$i]' id='itemtype2_" . $itemtype . "_" . $i . "_$rand'>";
            echo "<option value=''>" . Dropdown::EMPTY_VALUE . "</option>";
            foreach ($linked as $key) {
               if (!isset($metanames[$key])) {
                  $linkitem        = new $key();
                  $metanames[$key] = $linkitem->getTypeName();
               }
               echo "<option value='$key'>" . Toolbox::substr($metanames[$key], 0, 20) . "</option>\n";
            }
            echo "</select>&nbsp;";
            echo "</td><td>";
            // Ajax script for display search met& item
            echo "<span id='show_" . $itemtype . "_" . $i . "_$rand'>&nbsp;</span>\n";

            $params = ['itemtype'    => '__VALUE__',
                       'num'         => $i,
                       'field'       => (is_array($p['field2']) && isset($p['field2'][$i]) ? $p['field2'][$i] : ""),
                       'value'       => (is_array($p['contains2']) && isset($p['contains2'][$i]) ? $p['contains2'][$i] : ""),
                       'searchtype2' => (is_array($p['searchtype2']) && isset($p['searchtype2'][$i]) ? $p['searchtype2'][$i] : "")];

            Ajax::updateItemOnSelectEvent("itemtype2_" . $itemtype . "_" . $i . "_$rand", "show_" . $itemtype . "_" .
                                                                                          $i . "_$rand", $CFG_GLPI["root_doc"] . "/ajax/updateMetaSearch.php", $params, false);

            if (is_array($p['itemtype2']) && isset($p['itemtype2'][$i]) && !empty($p['itemtype2'][$i])) {
               $params['itemtype'] = $p['itemtype2'][$i];
               Ajax::updateItem("show_" . $itemtype . "_" . $i . "_$rand",
                                $CFG_GLPI["root_doc"] . "/ajax/updateMetaSearch.php", $params, false);
               echo "<script type='text/javascript' >";
               echo "window.document.getElementById('itemtype2_" . $itemtype . "_" . $i . "_$rand').value='" .
                    $p['itemtype2'][$i] . "';";
               echo "</script>\n";
            }
            echo "</td></tr></table>";
            echo "</td></tr>\n";
         }
      }
      echo "</table>\n";
      echo "</td>\n";

      echo "<td width='150px'>";
      echo "<table width='100%'>";

      // Display deleted selection

      echo "<tr>";

      // Display submit button
      echo "<td width='80' class='center'>";
      echo "<input type='submit' value=\"" . _sx('button', 'Search') . "\" class='submit' >";
      echo "</td><td>";
      //Bookmark::showSaveButton(Bookmark::SEARCH,$itemtype);
      echo "<a href='$target?reset=reset' >";
      echo "&nbsp;&nbsp;<img title=\"" . __s('Blank') . "\" alt=\"" . __s('Blank') . "\" src='" .
           $CFG_GLPI["root_doc"] . "/templates/infotel/pics/reset.png' class='calendrier'></a>";

      echo "</td></tr></table>\n";

      echo "</td></tr>";
      echo "</table>\n";

      // For dropdown
      echo "<input type='hidden' name='itemtype' value='$itemtype'>";

      // Reset to start when submit new search
      echo "<input type='hidden' name='start' value='0'>";
      Html::closeForm();
   }

   /**
    * @param $params
    */
   function showMinimalList($params) {
      global $DB, $CFG_GLPI;

      // Instanciate an object to access method
      $item = null;

      $itemtype  = $this->getType();
      $itemtable = $this->getTable();

      if (class_exists($itemtype)) {
         $item = new $itemtype();
      }

      // Default values of parameters
      $p['link']        = [];//
      $p['field']       = [];//
      $p['contains']    = [];//
      $p['searchtype']  = [];//
      $p['sort']        = '1'; //
      $p['order']       = 'ASC';//
      $p['start']       = 0;//
      $p['is_deleted']  = 0;
      $p['export_all']  = 0;
      $p['link2']       = '';//
      $p['contains2']   = '';//
      $p['field2']      = '';//
      $p['itemtype2']   = '';
      $p['searchtype2'] = '';

      foreach ($params as $key => $val) {
         $p[$key] = $val;
      }

      if ($p['export_all']) {
         $p['start'] = 0;
      }

      // Manage defautlt seachtype value : for bookmark compatibility

      if (count($p['contains'])) {
         foreach ($p['contains'] as $key => $val) {
            if (!isset($p['searchtype'][$key])) {
               $p['searchtype'][$key] = 'contains';
            }
         }
      }
      if (is_array($p['contains2']) && count($p['contains2'])) {
         foreach ($p['contains2'] as $key => $val) {
            if (!isset($p['searchtype2'][$key])) {
               $p['searchtype2'][$key] = 'contains';
            }
         }
      }

      //$target = Toolbox::getItemTypeSearchURL($itemtype);
      $target = $CFG_GLPI["root_doc"] . "/plugins/resources/front/resourceholiday.php";

      $limitsearchopt = Search::getCleanedOptions("PluginResourcesResourceHoliday");

      $LIST_LIMIT = $_SESSION['glpilist_limit'];

      // Set display type for export if define
      $output_type = Search::HTML_OUTPUT;
      if (isset($_GET['display_type'])) {
         $output_type = $_GET['display_type'];
         // Limit to 10 element
         if ($_GET['display_type'] == Search::GLOBAL_SEARCH) {
            $LIST_LIMIT = Search::GLOBAL_DISPLAY_COUNT;
         }
      }
      $PluginResourcesResource = new PluginResourcesResource();
      $entity_restrict         = $PluginResourcesResource->isEntityAssign();

      // Get the items to display
      $toview = Search::addDefaultToView($itemtype);

      // Add items to display depending of personal prefs
      $displaypref = DisplayPreference::getForTypeUser("PluginResourcesResourceHoliday", Session::getLoginUserID());
      if (count($displaypref)) {
         foreach ($displaypref as $val) {
            array_push($toview, $val);
         }
      }

      // Add searched items
      if (count($p['field']) > 0) {
         foreach ($p['field'] as $key => $val) {
            if (!in_array($val, $toview) && $val != 'all' && $val != 'view') {
               array_push($toview, $val);
            }
         }
      }

      // Add order item
      if (!in_array($p['sort'], $toview)) {
         array_push($toview, $p['sort']);
      }

      // Clean toview array
      $toview = array_unique($toview);
      foreach ($toview as $key => $val) {
         if (!isset($limitsearchopt[$val])) {
            unset($toview[$key]);
         }
      }

      $toview_count = count($toview);

      //// 1 - SELECT
      $query = "SELECT " . Search::addDefaultSelect($itemtype);

      // Add select for all toview item
      foreach ($toview as $key => $val) {
         $query .= Search::addSelect($itemtype, $val, 0);
      }

      $query .= "`" . $itemtable . "`.`id` AS id ";

      //// 2 - FROM AND LEFT JOIN
      // Set reference table
      $query .= " FROM `" . $itemtable . "`";

      // Init already linked tables array in order not to link a table several times
      $already_link_tables = [];
      // Put reference table
      array_push($already_link_tables, $itemtable);

      // Add default join
      $COMMONLEFTJOIN = Search::addDefaultJoin($itemtype, $itemtable, $already_link_tables);
      $query          .= $COMMONLEFTJOIN;

      $searchopt            = [];
      $searchopt[$itemtype] =& Search::getOptions($itemtype);
      // Add all table for toview items
      foreach ($toview as $key => $val) {
         $query .= Search::addLeftJoin($itemtype, $itemtable, $already_link_tables,
                                       $searchopt[$itemtype][$val]["table"],
                                       $searchopt[$itemtype][$val]["linkfield"]);
      }

      // Search all case :
      if (in_array("all", $p['field'])) {
         foreach ($searchopt[$itemtype] as $key => $val) {
            // Do not search on Group Name
            if (is_array($val)) {
               $query .= Search::addLeftJoin($itemtype, $itemtable, $already_link_tables,
                                             $searchopt[$itemtype][$key]["table"],
                                             $searchopt[$itemtype][$key]["linkfield"]);
            }
         }
      }

      //// 3 - WHERE

      // default string
      $COMMONWHERE = Search::addDefaultWhere($itemtype);
      $first       = empty($COMMONWHERE);

      // Add deleted if item have it
      if ($item && $item->maybeDeleted()) {
         $LINK = " AND ";
         if ($first) {
            $LINK  = " ";
            $first = false;
         }
         $COMMONWHERE .= $LINK . "`$itemtable`.`is_deleted` = '" . $p['is_deleted'] . "' ";
      }

      // Remove template items
      if ($item && $item->maybeTemplate()) {
         $LINK = " AND ";
         if ($first) {
            $LINK  = " ";
            $first = false;
         }
         $COMMONWHERE .= $LINK . "`$itemtable`.`is_template` = 0 ";
      }

      // Add Restrict to current entities
      if ($entity_restrict) {
         $LINK = " AND ";
         if ($first) {
            $LINK  = " ";
            $first = false;
         }
         $dbu = new DbUtils();
         if ($itemtype == 'Entity') {
            $COMMONWHERE .= $dbu->getEntitiesRestrictRequest($LINK, $itemtable, 'id', '', true);
         } else if (isset($CFG_GLPI["union_search_type"]["PluginResourcesResource"])) {

            // Will be replace below in Union/Recursivity Hack
            $COMMONWHERE .= $LINK . " ENTITYRESTRICT ";
         } else {
            $COMMONWHERE .= $dbu->getEntitiesRestrictRequest($LINK, "glpi_plugin_resources_resources", '', '', $PluginResourcesResource->maybeRecursive());
         }
      }

      ///R�cup�ration des groupes de l'utilisateur connect�
      $who = Session::getLoginUserID();

      if (!Session::haveRight("plugin_resources_all", READ)) {
         $LINK = " AND ";
         if ($first) {
            $LINK  = " ";
            $first = false;
         }
         $COMMONWHERE .= $LINK . "(`glpi_plugin_resources_resources`.`users_id_recipient` = '$who' OR `glpi_plugin_resources_resources`.`users_id` = '$who') ";
      }

      $WHERE  = "";
      $HAVING = "";

      // Add search conditions
      // If there is search items
      if ($_SESSION["glpisearchcount"][$itemtype] > 0 && count($p['contains']) > 0) {
         for ($key = 0; $key < $_SESSION["glpisearchcount"][$itemtype]; $key++) {
            // if real search (strlen >0) and not all and view search
            if (isset($p['contains'][$key]) && strlen($p['contains'][$key]) > 0) {
               // common search
               if ($p['field'][$key] != "all" && $p['field'][$key] != "view") {
                  $LINK    = " ";
                  $NOT     = 0;
                  $tmplink = "";
                  if (is_array($p['link']) && isset($p['link'][$key])) {
                     if (strstr($p['link'][$key], "NOT")) {
                        $tmplink = " " . str_replace(" NOT", "", $p['link'][$key]);
                        $NOT     = 1;
                     } else {
                        $tmplink = " " . $p['link'][$key];
                     }
                  } else {
                     $tmplink = " AND ";
                  }

                  if (isset($searchopt[$itemtype][$p['field'][$key]]["usehaving"])) {
                     // Manage Link if not first item
                     if (!empty($HAVING)) {
                        $LINK = $tmplink;
                     }
                     // Find key
                     $item_num = array_search($p['field'][$key], $toview);
                     $HAVING   .= Search::addHaving($LINK, $NOT, $itemtype, $p['field'][$key], $p['searchtype'][$key], $p['contains'][$key], 0, $item_num);
                  } else {
                     // Manage Link if not first item
                     if (!empty($WHERE)) {
                        $LINK = $tmplink;
                     }
                     $WHERE .= Search::addWhere($LINK, $NOT, $itemtype, $p['field'][$key], $p['searchtype'][$key], $p['contains'][$key]);
                  }

                  // view and all search
               } else {
                  $LINK       = " OR ";
                  $NOT        = 0;
                  $globallink = " AND ";
                  if (is_array($p['link']) && isset($p['link'][$key])) {
                     switch ($p['link'][$key]) {
                        case "AND" :
                           $LINK       = " OR ";
                           $globallink = " AND ";
                           break;

                        case "AND NOT" :
                           $LINK       = " AND ";
                           $NOT        = 1;
                           $globallink = " AND ";
                           break;

                        case "OR" :
                           $LINK       = " OR ";
                           $globallink = " OR ";
                           break;

                        case "OR NOT" :
                           $LINK       = " AND ";
                           $NOT        = 1;
                           $globallink = " OR ";
                           break;
                     }
                  } else {
                     $tmplink = " AND ";
                  }

                  // Manage Link if not first item
                  if (!empty($WHERE)) {
                     $WHERE .= $globallink;
                  }
                  $WHERE  .= " ( ";
                  $first2 = true;

                  $items = [];
                  if ($p['field'][$key] == "all") {
                     $items = $searchopt[$itemtype];
                  } else { // toview case : populate toview
                     foreach ($toview as $key2 => $val2) {
                        $items[$val2] = $searchopt[$itemtype][$val2];
                     }
                  }

                  foreach ($items as $key2 => $val2) {
                     if (is_array($val2)) {
                        // Add Where clause if not to be done in HAVING CLAUSE
                        if (!isset($val2["usehaving"])) {
                           $tmplink = $LINK;
                           if ($first2) {
                              $tmplink = " ";
                              $first2  = false;
                           }
                           $WHERE .= Search::addWhere($tmplink, $NOT, $itemtype, $key2, $p['searchtype'][$key], $p['contains'][$key]);
                        }
                     }
                  }
                  $WHERE .= " ) ";
               }
            }
         }
      }

      if (!empty($WHERE) || !empty($COMMONWHERE)) {
         if (!empty($COMMONWHERE)) {
            $WHERE = ' WHERE ' . $COMMONWHERE . (!empty($WHERE) ? ' AND ( ' . $WHERE . ' )' : '');
         } else {
            $WHERE = ' WHERE ' . $WHERE . ' ';
         }
         $first = false;
      }
      $query .= $WHERE;

      //// 7 - Manage GROUP BY
      $GROUPBY = "";
      // Meta Search / Search All / Count tickets
      if (in_array('all', $p['field'])) {
         $GROUPBY = " GROUP BY `" . $itemtable . "`.`id`";
      }

      if (empty($GROUPBY)) {
         foreach ($toview as $key2 => $val2) {
            if (!empty($GROUPBY)) {
               break;
            }
            if (isset($searchopt[$itemtype][$val2]["forcegroupby"])) {
               $GROUPBY = " GROUP BY `" . $itemtable . "`.`id`";
            }
         }
      }
      $query .= $GROUPBY;
      //// 4 - ORDER
      $ORDER = " ORDER BY `id` ";
      foreach ($toview as $key => $val) {
         if ($p['sort'] == $val) {
            $ORDER = Search::addOrderBy($itemtype, $p['sort'], $p['order'], $key);
         }
      }
      $query .= $ORDER;

      // Get it from database

      if ($result = $DB->query($query)) {
         $numrows = $DB->numrows($result);

         $globallinkto = Search::getArrayUrlLink("field", $p['field']) .
                         Search::getArrayUrlLink("link", $p['link']) .
                         Search::getArrayUrlLink("contains", $p['contains']) .
                         Search::getArrayUrlLink("field2", $p['field2']) .
                         Search::getArrayUrlLink("contains2", $p['contains2']) .
                         Search::getArrayUrlLink("itemtype2", $p['itemtype2']) .
                         Search::getArrayUrlLink("link2", $p['link2']);

         $parameters = "sort=" . $p['sort'] . "&amp;order=" . $p['order'] . $globallinkto;

         if ($output_type == Search::GLOBAL_SEARCH) {
            if (class_exists($itemtype)) {
               echo "<div class='center'><h2>" . $this->getTypeName();
               // More items
               if ($numrows > $p['start'] + Search::GLOBAL_DISPLAY_COUNT) {
                  echo " <a href='$target?$parameters'>" . __('All') . "</a>";
               }
               echo "</h2></div>\n";
            } else {
               return false;
            }
         }

         if ($p['start'] < $numrows) {

            // Pager
            if ($output_type == Search::HTML_OUTPUT) {
               Html::printPager($p['start'], $numrows, $target, $parameters, $itemtype);
            }

            //massive action
            $sel = "";
            if (isset($_GET["select"]) && $_GET["select"] == "all") {
               $sel = "checked";
            }

            // Add toview elements
            $nbcols = $toview_count;

            if ($output_type == Search::HTML_OUTPUT) { // HTML display - massive modif
               $nbcols++;
            }

            // Define begin and end var for loop
            // Search case
            $begin_display = $p['start'];
            $end_display   = $p['start'] + $LIST_LIMIT;

            // Export All case
            if ($p['export_all']) {
               $begin_display = 0;
               $end_display   = $numrows;
            }

            // Display List Header
            echo Search::showHeader($output_type, $end_display - $begin_display + 1, $nbcols);

            $header_num = 1;
            // Display column Headers for toview items
            echo Search::showNewLine($output_type);

            // Display column Headers for toview items
            foreach ($toview as $key => $val) {
               $linkto = '';
               if (!isset($searchopt[$itemtype][$val]['nosort'])
                   || !$searchopt[$itemtype][$val]['nosort']) {
                  $linkto = "$target?itemtype=$itemtype&amp;sort=" . $val . "&amp;order=" . ($p['order'] == "ASC" ? "DESC" : "ASC") .
                            "&amp;start=" . $p['start'] . $globallinkto;
               }
               echo Search::showHeaderItem($output_type, $searchopt[$itemtype][$val]["name"],
                                           $header_num, $linkto, $p['sort'] == $val, $p['order']);
            }

            // End Line for column headers
            echo Search::showEndLine($output_type);

            $DB->dataSeek($result, $p['start']);

            // Define begin and end var for loop
            // Search case
            $i = $begin_display;

            // Init list of items displayed
            if ($output_type == Search::HTML_OUTPUT) {
               Session::initNavigateListItems($itemtype);
            }

            // Num of the row (1=header_line)
            $row_num = 1;
            // Display Loop
            while ($i < $numrows && $i < ($end_display)) {

               $item_num = 1;
               $data     = $DB->fetchArray($result);
               $i++;
               $row_num++;

               echo Search::showNewLine($output_type, ($i % 2));

               Session::addToNavigateListItems($itemtype, $data['id']);

               foreach ($toview as $key => $val) {
                  echo Search::showItem($output_type, Search::giveItem($itemtype, $val, $data, $key), $item_num,
                                        $row_num,
                                        Search::displayConfigItem($itemtype, $val, $data, $key));
               }

               echo Search::showEndLine($output_type);
            }
            // Close Table
            $title = "";
            // Create title
            if ($output_type == Search::PDF_OUTPUT_PORTRAIT || $output_type == Search::PDF_OUTPUT_LANDSCAPE) {
               $title .= __('List of forced holidays', 'resources');
            }

            // Display footer
            echo Search::showFooter($output_type, $title);

            // Pager
            if ($output_type == Search::HTML_OUTPUT) {
               echo "<br>";
               Html::printPager($p['start'], $numrows, $target, $parameters);
            }
         } else {
            echo Search::showError($output_type);
         }
      }
   }
}

