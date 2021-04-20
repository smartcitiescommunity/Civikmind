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

class PluginManageentitiesCompany extends CommonDBTM {

   static $rightname = 'plugin_manageentities';
   // From CommonDBTM
   public $dohistory = true;

   static function getTypeName($nb = 0) {
      return _n('Company', 'Companies', $nb, 'manageentities');
   }

   function rawSearchOptions() {
      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'       => '2',
         'table'    => $this->getTable(),
         'field'    => 'id',
         'name'     => __('ID'),
         'datatype' => 'number'
      ];

      $tab[] = [
         'id'            => '9',
         'table'         => $this->getTable(),
         'field'         => 'address',
         'name'          => __('Address'),
         'massiveaction' => false,
         'datatype'      => 'text'
      ];

      return $tab;
   }

   /**
    * Display the company form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return boolean item found
    * */
   function showForm($ID, $options = []) {
      global $CFG_GLPI, $DB;

      if ($ID > 0) {
         $this->check($ID, READ);
      } else {
         // Create item
         $this->check(-1, CREATE);
      }

      // Set session saved if exists
      $this->setSessionValues();

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . PluginManageentitiesCompany::getTypeName(1) . "</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "name", ['value' => $this->fields["name"]]);
      echo "</td>";
      echo "<td></td><td></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Address') . "</td>";
      echo "<td>";
      echo "<textarea cols='40' rows='5' name='address'>" . $this->fields["address"] . "</textarea>";
      echo "</td>";
      echo "<td></td><td></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Comments') . "</td>";
      echo "<td>";
      echo "<textarea cols='40' rows='5' name='comment'>" . $this->fields["comment"] . "</textarea>";
      echo "</td>";
      echo "<td></td><td></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Logo (format JPG or JPEG)', 'manageentities') . "</td>";
      if ($this->fields["logo_id"] != 0) {
         echo "<td>";
         echo "<div  id='picture'>";
         echo "<img height='50px' alt=\"" . __s('Picture') . "\" src='" . $CFG_GLPI["root_doc"] . "/front/document.send.php?docid=" . $this->fields["logo_id"] . "'>";
         echo "</div></td>";
      }
      echo "<td>";
      echo Html::file(['multiple' => false, 'onlyimages' => true]);
      echo "</td>";
      if ($this->fields["logo_id"] == 0) {
         echo "<td></td>";
      }
      echo "<td></td></tr>";


      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Entity') . "</td>";
      echo "<td>";


      Entity::dropdown(['name' => 'entity_id', 'value' => $this->fields['entity_id'], 'right' => 'all']);
      echo "&nbsp;" . __('Recursive') . "&nbsp";
      Dropdown::showYesNo("recursive", $this->fields["recursive"]);
      echo "</td>";
      echo "<td></td><td></td></tr>";

      $this->showFormButtons($options);

      return true;
   }

   /**
    * Menu with button add new company
    *
    * @param type $options
    */
   static function addNewCompany($options = []) {

      $addButton = "";

      if (Session::haveRight('plugin_manageentities', UPDATE)) {
         $rand = mt_rand();

         $addButton = "<form method='post' name='company_form'.$rand.'' id='company_form" . $rand . "'
               action='" . Toolbox::getItemTypeFormURL('PluginManageentitiesCompany') . "'>
               <input type='hidden' name='company_id' value='company'>
               <input type='hidden' name='id' value=''>
               <input type='submit' name='addperiod' value='" . _sx('button', 'Add') . "' class='submit'>";
      }

      if (isset($options['title'])) {
         echo '<table class="tab_cadre_fixe">';
         echo '<tr><th>' . $options['title'] . '</th></tr>';
         echo '<tr class="tab_bg_1">
               <td class="center">';
         echo $addButton;
         Html::closeForm();
         echo '</td></tr></table>';
      } else {
         echo '<tr class="tab_bg_1">
               <td class="center" colspan="' . $options['colspan'] . '">';
         echo $addButton;
         Html::closeForm();
         echo '</td></tr>';
      }
   }

   function setSessionValues() {
      if (isset($_SESSION['plugin_manageentities']['company']) && !empty($_SESSION['plugin_manageentities']['company'])) {
         foreach ($_SESSION['plugin_manageentities']['company'] as $key => $val) {
            $this->fields[$key] = $val;
         }
      }
      unset($_SESSION['plugin_manageentities']['company']);
   }

   function prepareInputForUpdate($input) {

      if (isset($input["_filename"])) {
         $plugin_company = new PluginManageentitiesCompany();
         $company        = $plugin_company->find(['id' => $input['id']]);
         $company        = reset($company);

         $tmp       = explode(".", $input["_filename"][0]);
         $extension = array_pop($tmp);
         if (!in_array($extension, ['jpg', 'jpeg'])) {
            Session::addMessageAfterRedirect(__('The format of the image must be in JPG or JPEG', 'manageentities'), false, ERROR);
            unset($input);
         } elseif ($company['logo_id'] != 0) {
            $doc = new Document();
            $img = $doc->find(['id' => $company["logo_id"]]);
            $img = reset($img);
            $doc->delete($img, 1);
         }
      }
      return $input;
   }

   function prepareInputForAdd($input) {

      if (isset($input["_filename"])) {
         $tmp       = explode(".", $input["_filename"][0]);
         $extension = array_pop($tmp);
         if (!in_array($extension, ['jpg', 'jpeg'])) {
            Session::addMessageAfterRedirect(__('The format of the image must be in JPG or JPEG', 'manageentities'), false, ERROR);
            return [];
         }
      }
      return $input;
   }

   function post_addItem($history = 1) {
      $img = $this->addFiles($this->input);
      foreach ($img as $key => $name) {
         $this->fields['logo_id'] = $key;
         $this->updateInDB(['logo_id']);
      }
   }

   function post_updateItem($history = 1) {
      $img = $this->addFiles($this->input);
      foreach ($img as $key => $name) {
         $this->fields['logo_id'] = $key;
         $this->updateInDB(['logo_id']);
      }
   }

   /**
    *
    * @param int   $donotif
    * @param type  $disablenotif
    *
    * @return int
    * @global type $CFG_GLPI
    *
    */
   function addFiles(array $input, $options = []) {
      global $CFG_GLPI;

      $default_options = [
         'force_update'  => false,
         'content_field' => 'content',
      ];
      $options         = array_merge($default_options, $options);

      if (!isset($input['_filename'])
          || (count($input['_filename']) == 0)) {
         return $input;
      }
      $docadded     = [];
      $donotif      = isset($input['_donotif']) ? $input['_donotif'] : 0;
      $disablenotif = isset($input['_disablenotif']) ? $input['_disablenotif'] : 0;


      foreach ($this->input['_filename'] as $key => $file) {
         $doc      = new Document();
         $docitem  = new Document_Item();
         $docID    = 0;
         $filename = GLPI_TMP_DIR . "/" . $file;
         $input2   = [];

         // Crop/Resize image file if needed
         if (isset($this->input['_coordinates']) && !empty($this->input['_coordinates'][$key])) {
            $image_coordinates = json_decode(urldecode($this->input['_coordinates'][$key]), true);
            Toolbox::resizePicture($filename, $filename, $image_coordinates['img_w'], $image_coordinates['img_h'], $image_coordinates['img_y'], $image_coordinates['img_x'], $image_coordinates['img_w'], $image_coordinates['img_h'], 0);
         } else {
            Toolbox::resizePicture($filename, $filename, 0, 0, 0, 0, 0, 0, 0);
         }

         //If file tag is present
         if (isset($input['_tag_filename'])
             && !empty($input['_tag_filename'][$key])) {
            $input['_tag'][$key] = $input['_tag_filename'][$key];
         }

         //retrieve entity
         $entities_id = isset($this->fields["entities_id"])
            ? $this->fields["entities_id"]
            : $_SESSION['glpiactive_entity'];

         // Check for duplicate
         if ($doc->getFromDBbyContent($entities_id, $filename)) {
            if (!$doc->fields['is_blacklisted']) {
               $docID = $doc->fields["id"];
            }
            // File already exist, we replace the tag by the existing one
            if (isset($input['_tag'][$key])
                && ($docID > 0)
                && isset($input[$options['content_field']])) {

               $input[$options['content_field']]
                                        = preg_replace('/' . Document::getImageTag($input['_tag'][$key]) . '/',
                                                       Document::getImageTag($doc->fields["tag"]),
                                                       $input[$options['content_field']]);
               $docadded[$docID]['tag'] = $doc->fields["tag"];
            }

         } else {
            //TRANS: Default document to files attached to tickets : %d is the ticket id
            $input2["name"]                    = addslashes(sprintf(__('Logo %d', 'manageentities'), $this->getID()));
            $input2["entity_id"]               = $this->fields["entity_id"];
            $input2["_only_if_upload_succeed"] = 1;
            $input2["_filename"]               = [$file];
            $input2["is_recursive"]            = 1;
            $docID                             = $doc->add($input2);
         }

         if ($docID > 0) {
            if ($docitem->add(['documents_id'  => $docID,
                               '_do_notif'     => $donotif,
                               '_disablenotif' => $disablenotif,
                               'itemtype'      => $this->getType(),
                               'items_id'      => $this->getID()])) {
               $docadded[$docID]['data'] = sprintf(__('%1$s - %2$s'), stripslashes($doc->fields["name"]), stripslashes($doc->fields["filename"]));

               if (isset($input2["tag"])) {
                  $docadded[$docID]['tag'] = $input2["tag"];
                  unset($this->input['_filename'][$key]);
                  unset($this->input['_tag'][$key]);
               }
               if (isset($this->input['_coordinates'][$key])) {
                  unset($this->input['_coordinates'][$key]);
               }
            }
         }
         // Only notification for the first New doc
         $donotif = 0;
      }
      return $docadded;
   }

   /**
    * Returns the company's address
    *
    * @param type $obj
    *
    * @return string address
    */
   static function getAddress($obj) {
      $plugin_company = new PluginManageentitiesCompany();
      $company        = $plugin_company->find(['entity_id' => $obj->entite[0]->fields['id']]);
      $company        = reset($company);
      $dbu            = new DbUtils();
      if ($company == false) {
         $companies = $plugin_company->find();
         foreach ($companies as $data) {
            if ($data['recursive'] == 1) {
               $sons = $dbu->getSonsOf("glpi_entities", $data['entity_id']);
               foreach ($sons as $son) {
                  if ($son == $obj->entite[0]->fields['id']) {
                     return $data['address'];
                  }
               }
            }
         }
      } else {
         return $company['address'];
      }
   }

   /**
    * Returns the company logo
    *
    * @param type $obj
    *
    * @return type
    */
   static function getLogo($obj) {
      $plugin_company = new PluginManageentitiesCompany();
      $company        = $plugin_company->find(['entity_id' => $obj->entite[0]->fields['id']]);
      $company        = reset($company);
      $doc            = new Document();
      $dbu            = new DbUtils();
      if ($company == false) {
         $companies = $plugin_company->find();
         foreach ($companies as $data) {
            if ($data['recursive'] == 1) {
               $sons = $dbu->getSonsOf("glpi_entities", $data['entity_id']);
               foreach ($sons as $son) {
                  if ($son == $obj->entite[0]->fields['id']) {
                     if ($doc->getFromDB($data["logo_id"])) {
                        return $doc->fields['filepath'];
                     }
                  }
               }
            }
         }

      } else {
         if ($company["logo_id"] != 0) {
            $doc->getFromDB($company["logo_id"]);
            return $doc->fields['filepath'];
         }
      }
      return null;
   }

   /**
    * Returns company comments
    *
    * @param type $obj
    *
    * @return type
    */
   static function getComment($obj) {
      $plugin_company = new PluginManageentitiesCompany();
      $company        = $plugin_company->find(['entity_id' => $obj->entite[0]->fields['id']]);
      $company        = reset($company);
      $dbu            = new DbUtils();
      if ($company == false) {
         $companies = $plugin_company->find();
         foreach ($companies as $data) {
            if ($data['recursive'] == 1) {
               $sons = $dbu->getSonsOf("glpi_entities", $data['entity_id']);
               foreach ($sons as $son) {
                  if ($son == $obj->entite[0]->fields['id']) {
                     return $data['comment'];
                  }
               }
            }
         }
      } else {
         return $company['comment'];
      }
      return null;
   }

}