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
 * Class PluginResourcesTicketCategory
 */
class PluginResourcesTicketCategory extends CommonDBTM {

   /**
    * @param $category
    *
    * @return bool
    */
   function getFromDBbyCategory($category) {
      global $DB;

      $query = "SELECT * FROM `".$this->getTable()."` ".
              "WHERE `ticketcategories_id` = '".$category."' ";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
   }

   /**
    * @param $category
    */
   function addTicketCategory($category) {

      if ($this->getFromDBbyCategory($category)) {

         $this->update([
             'id' => $this->fields['id'],
             'ticketcategories_id' => $category]);
      } else {

         $this->add([
             'id' => 1,
             'ticketcategories_id' => $category]);
      }
   }

   /**
    * @param $target
    */
   function showForm($target) {

      $dbu = new DbUtils();
      $categories = $dbu->getAllDataFromTable($this->getTable());
      if (!empty($categories)) {
         echo "<div align='center'>";
         echo "<form method='post' action='".$target."'>";
         echo "<table class='tab_cadre_fixe' cellpadding='5'>";
         echo "<tr>";
         echo "<th colspan='2'>".__('Category of created tickets', 'resources')."</th>";
         echo "</tr>";
         $categorie = reset($categories);
         $ID        = $categorie["id"];
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . Dropdown::getDropdownName("glpi_itilcategories", $categorie["ticketcategories_id"]) . "</td>";
         echo "<td class='center'>";
         echo "<input type='hidden' name='id' value='$ID'>";
         echo "<input type='submit' class='submit' name='delete_ticket' value='" . __('Delete permanently') . "'>";
         echo "</td>";
         echo "</tr>";

         echo "</table>";
         Html::closeForm();
         echo "</div>";
      } else {
         echo "<div align='center'><form method='post'  action='".$target."'>";
         echo "<table class='tab_cadre_fixe' cellpadding='5'><tr ><th colspan='2'>";
         echo __('Category of created tickets', 'resources')."</th></tr>";
         echo "<tr class='tab_bg_1'><td>";
         Dropdown::show('ITILCategory', ['name' => "ticketcategories_id"]);
         echo "</td>";
         echo "<td>";
         echo "<div align='center'>";
         echo "<input type='submit' name='add_ticket' value=\""._sx('button', 'Add')."\" 
                                                                                 class='submit'>";
         echo "</div></td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }
   }

}

