<?php
/**
 * @version $Id: notificationtemplate.class.php 338 2021-03-30 12:36:31Z yllen $
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

class PluginBehaviorsNotificationTemplate extends PluginBehaviorsCommon {


   static function postClone(NotificationTemplate $clone, $oldid) {
      global $DB;

      $trad = new NotificationTemplateTranslation();
      $dbu  = new DbUtils();
      $fkey = $dbu->getForeignKeyFieldForTable($clone->getTable());
      $crit = [$fkey => $oldid];

      foreach ($DB->request($trad->getTable(), $crit) as $data) {
         unset($data['id']);
         $data[$fkey] = $clone->getID();
         $trad->add(Toolbox::addslashes_deep($data));
      }
   }
}
