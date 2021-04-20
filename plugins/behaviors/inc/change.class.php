<?php
/**
 * @version $Id:  yllen $
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
 @copyright Copyright (c) 2019-2021 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2010

 --------------------------------------------------------------------------
*/

class PluginBehaviorsChange {


   static function beforeAdd(Change $change) {
      global $DB;

      if (!is_array($change->input) || !count($change->input)) {
         // Already cancel by another plugin
         return false;
      }

      $dbu = new DbUtils();

      $config = PluginBehaviorsConfig::getInstance();

      if ($config->getField('changes_id_format')) {
         $max = 0;
         $sql = ['SELECT' => ['MAX' => 'id AS max'],
                 'FROM'   => 'glpi_changes'];
         foreach ($DB->request($sql) as $data) {
            $max = $data['max'];
         }
         $want = date($config->getField('changes_id_format'));
         if ($max < $want) {
            $DB->query("ALTER TABLE `glpi_changes` AUTO_INCREMENT=$want");
         }
      }
   }

}
