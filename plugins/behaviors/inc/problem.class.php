<?php
/**
 * @version $Id: $
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
 @author    Nelly Mahu-Lasson
 @copyright Copyright (c) 2010-2021 Behaviors plugin team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://forge.glpi-project.org/projects/behaviors
 @link      http://www.glpi-project.org/
 @since     2014

 --------------------------------------------------------------------------
*/

class PluginBehaviorsProblem {


   static function beforeUpdate(Problem $problem) {
      global $DB;

      if (!is_array($problem->input) || !count($problem->input)) {
         // Already cancel by another plugin
         return false;
      }

      $config = PluginBehaviorsConfig::getInstance();

      // Check is the connected user is a tech
      if (!is_numeric(Session::getLoginUserID(false))
          || !Session::haveRight('problem', UPDATE)) {
         return false; // No check
      }

      if (isset($problem->input['status'])
          && in_array($problem->input['status'], array_merge(Problem::getSolvedStatusArray(),
                                                             Problem::getclosedStatusArray()))) {

         $soluce = $DB->request('glpi_itilsolutions',
                                ['itemtype'   => 'Problem',
                                 'items_id'   => $problem->input['id']]);

         if ($config->getField('is_problemsolutiontype_mandatory')
             && !count($soluce)) {
            unset($problem->input['status']);
            Session::addMessageAfterRedirect(__("Type of solution is mandatory before problem is solved/closed",
                                                'behaviors'), true, ERROR);
         }
      }
   }

}
