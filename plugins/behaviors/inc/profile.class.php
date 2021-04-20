<?php
/**
 * @version $Id: profile.class.php 338 2021-03-30 12:36:31Z yllen $
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Behaviors plugin for GLPI.

 Behaviors is free software: you can redistribute it and/or modify
 it under the terms of the GNU Affero General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Behaviors is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU Affero General Public License for more details.

 You should have received a copy of the GNU Affero General Public License
 along with Behaviors. If not, see <http://www.gnu.org/licenses/>.

 @package   behaviors
 @author    Remi Collet, Nelly Mahu-Lasson
 @copyright Copyright (c) 2010-2021 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     version 0.83.4

 --------------------------------------------------------------------------
*/

class PluginBehaviorsProfile extends  PluginBehaviorsCommon {


   static function preClone(Profile $srce, Array $input) {

      // decode array
      if (isset($input['helpdesk_item_type'])
          && !is_array($input['helpdesk_item_type'])) {

         $input['helpdesk_item_type'] = importArrayFromDB($input['helpdesk_item_type']);
      }

      // Empty/NULL case
      if (!isset($input['helpdesk_item_type'])
          || !is_array($input['helpdesk_item_type'])) {

         $input['helpdesk_item_type'] = [];
      }

      return $input;
   }


   /**
    * @since version 0.90.1
    *
    * @param $clone      Profile item
    * @param $oldid
    */
   static function postClone(Profile $clone, $oldid) {
      global $DB;

      $rights = ProfileRight::getProfileRights($oldid);
      $pright = new ProfileRight();
      $pright->updateProfileRights($clone->getID(), $rights);
   }
}
