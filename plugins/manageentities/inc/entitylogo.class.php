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

class PluginManageentitiesEntityLogo extends CommonDBTM {

   static $rightname = 'plugin_manageentities';


   function getFromDBByEntity($entities_id) {
      global $DB;

      $query = "SELECT *
                FROM `" . $this->getTable() . "`
                WHERE `entities_id` = '$entities_id' ";

      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetchAssoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         }
         return false;
      }
      return false;
   }

   /**
    * Add a logo for entity
    *
    * @param type $values
    *
    * @return boolean
    */
   function addLogo($values) {

      if (isset($values["_filename"])) {
         $tmp       = explode(".", $values["_filename"][0]);
         $extension = array_pop($tmp);
         if (!in_array($extension, ['jpg', 'jpeg'])) {
            Session::addMessageAfterRedirect(__('The format of the image must be in JPG or JPEG', 'manageentities'), false, ERROR);
            return false;
         }
      }

      if ($values["entities_id"]) {
         if ($this->getFromDBByEntity($values["entities_id"])) {

            $values['id'] = $this->fields['id'];
            $doc          = new Document();
            $img          = ["id" => $this->fields["logos_id"]];
            $doc->delete($img, 1);
            $logo = $this->addFilesCRI(0, -1, $values);
            foreach ($logo as $key => $name) {
               $this->add(['entities_id' => $values["entities_id"],
                           'logos_id'    => $key]);
            }

         } else {

            $logo = $this->addFilesCRI(0, -1, $values);

            foreach ($logo as $key => $name) {

               $this->add(['entities_id' => $values["entities_id"],
                           'logos_id'    => $key]);

            }
         }
      }
   }

   static function cleanForItem(CommonDBTM $item) {

      $temp = new self();
      $temp->deleteByCriteria(
         ['logos_id' => $item->getField('id')]
      );
   }

   function addFilesCRI($donotif = 0, $disablenotif = 1, $values) {
      global $CFG_GLPI;

      if (!isset($values['_filename']) || (count($values['_filename']) == 0)) {
         return [];
      }
      $docadded = [];


      foreach ($values['_filename'] as $key => $file) {
         $doc     = new Document();
         $docitem = new Document_Item();

         $docID    = 0;
         $filename = GLPI_TMP_DIR . "/" . $file;
         $input2   = [];

         // Crop/Resize image file if needed
         if (isset($values['_coordinates']) && !empty($values['_coordinates'][$key])) {
            $image_coordinates = json_decode(urldecode($values['_coordinates'][$key]), true);
            Toolbox::resizePicture($filename, $filename, $image_coordinates['img_w'], $image_coordinates['img_h'], $image_coordinates['img_y'], $image_coordinates['img_x'], $image_coordinates['img_w'], $image_coordinates['img_h'], 0);
         } else {
            Toolbox::resizePicture($filename, $filename, 0, 0, 0, 0, 0, 0, 0);
         }

         //If file tag is present
         if (isset($values['_tag_filename']) && !empty($values['_tag_filename'][$key])) {
            $values['_tag'][$key] = $values['_tag_filename'][$key];
         }

         // Check for duplicate
         if ($doc->getFromDBbyContent($values['entities_id'], $filename)) {
            if (!$doc->fields['is_blacklisted']) {
               $docID = $doc->fields["id"];
            }
            // File already exist, we replace the tag by the existing one
            if (isset($values['_tag'][$key]) && ($docID > 0) && isset($values['content'])) {

               $values['content']       = preg_replace('/' . Document::getImageTag($values['_tag'][$key]) . '/', Document::getImageTag($doc->fields["tag"]), $values['content']);
               $docadded[$docID]['tag'] = $doc->fields["tag"];
            }
         } else {
            $entity = new Entity();
            $entity->getFromDB($values['entities_id']);
            $name = __('Logo', 'manageentities') . " " . $entity->fields['name'];
            //TRANS: Default document to files attached to tickets : %d is the ticket id
            $input2["name"] = addslashes($name);

            $input2["entities_id"]             = $values['entities_id'];
            $input2["_only_if_upload_succeed"] = 1;
            $input2["_filename"]               = [$file];

            $docID = $doc->add($input2);
         }

         if ($docID > 0) {
            if ($docitem->add(['documents_id'  => $docID,
                               '_do_notif'     => $donotif,
                               '_disablenotif' => $disablenotif,
                               'itemtype'      => 'Entity',
                               'items_id'      => $values['entities_id']])) {
               $docadded[$docID]['data'] = sprintf(__('%1$s - %2$s'), stripslashes($doc->fields["name"]), stripslashes($doc->fields["filename"]));

               if (isset($input2["tag"])) {
                  $docadded[$docID]['tag'] = $input2["tag"];
                  unset($values['_filename'][$key]);
                  unset($values['_tag'][$key]);
               }
               if (isset($values['_coordinates'][$key])) {
                  unset($values['_coordinates'][$key]);
               }
            }
         }
         // Only notification for the first New doc
         $donotif = 0;
      }
      return $docadded;
   }

   /**
    * Return logo
    *
    * @param type $entities_id
    *
    * @return boolean
    */
   function getLogo($entities_id) {

      $logo = $this->find(['entities_id' => $entities_id]);
      $logo = reset($logo);
      $doc  = new Document();
      if (is_array($logo) && count($logo) > 0) {
         if ($doc->getFromDB($logo["logos_id"])) {
            return $doc->fields['filepath'];
         }
      }
      return false;
   }
}