<?php
/*
 -------------------------------------------------------------------------
 Costs plugin for GLPI
 Copyright (C) 2018 by the TICgal Team.

 https://github.com/ticgal/costs
 -------------------------------------------------------------------------

 LICENSE

 This file is part of the Costs plugin.

 Costs plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 Costs plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Costs. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   Costs
 @author    the TICgal team
 @copyright Copyright (c) 2018 TICgal team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://tic.gal
 @since     2018
 ----------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginCostsEntity extends CommonDBTM {

   public static $rightname = 'entity';

   static function getTypeName($nb = 0) {
      return __('Costs', 'Costs');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      switch ($item::getType()) {
         case Entity::getType():
            return self::getTypeName();
         break;
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item::getType()) {
         case Entity::getType():
            self::displayTabForEntity($item);
            break;
      }
   }

   public function getFromDBByEntity($entities_id) {
      global $DB;

      $req=$DB->request(['FROM' => self::getTable(),'WHERE' => ['entities_id' => $entities_id]]);
      if (count($req)) {
         $this->fields=$req->next($req);
         return true;
      } else {
         $DB->insert(self::getTable(), ['entities_id'=>$entities_id]);
         $this->fields=['fixed_cost'=>0,'time_cost'=>0,'cost_private'=>0];
         return false;
      }
   }

   static function displayTabForEntity(Entity $entity) {
      global $DB, $CFG_GLPI;

      $ID = $entity->getField('id');
      if (!$entity->can($ID, READ)) {
         return false;
      }
      $cost_config=new self();
      $cost_config->getFromDBByEntity($ID);

      $rand = mt_rand();
      $out= "<form name='costentity_form$rand' id='costentity_form$rand' method='post' action='";
      $out.= self::getFormUrl()."'>";
      $out.= "<table class='tab_cadre_fixe'>";
      $out.="<tr class='tab_bg_1'>";
      $out.="<td>".__('Fixed cost')."</td><td>";
      $out.="<input size='5' step='".PLUGIN_COSTS_NUMBER_STEP."' type='number' name='fixed_cost' value='".$cost_config->fields['fixed_cost']."'>";
      $out.="</td></tr>\n";

      $out.="<tr class='tab_bg_1'>";
      $out.="<td>".__('Time cost')."</td><td>";
      $out.="<input size='5' step='".PLUGIN_COSTS_NUMBER_STEP."' type='number' name='time_cost' value='".$cost_config->fields['time_cost']."'>";
      $out.="</td></tr>\n";

      $out.="<tr class='tab_bg_1'>";
      $out.="<td>".__('Private task')."</td><td>";
      $out .= Dropdown::showYesNo("cost_private", $cost_config->fields['cost_private'], -1, ['display' => false]);
      $out.="</td></tr>\n";

      $out.="<tr><td>";
      $out.="<input type='hidden' name='entities_id' value='$ID'>";
      $out.="</td></tr>\n";

      $out.= "<tr><td class='tab_bg_2 right'>";
      $out.= "<input type='submit' name='update' value='"._sx('button', 'Update')."' class='submit'>";
      $out.= "</td></tr>";
      $out.= "</table>";
      $out.= Html::closeForm(false);

      echo $out;

      return false;
   }

   static function updateCost($entity_id, $fixed_cost, $time_cost, $cost_private) {
      global $DB;
      $DB->update(self::getTable(), ['fixed_cost'=>$fixed_cost,'time_cost'=>$time_cost,'cost_private'=>$cost_private], ['entities_id'=>$entity_id]);
   }

   static function install(Migration $migration) {
      global $DB;

      $table=self::getTable();

      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         $query="CREATE TABLE IF NOT EXISTS $table (
         			id int(11) NOT NULL auto_increment,
         			entities_id int(11) NOT NULL DEFAULT '0',
         			fixed_cost float NOT NULL default '0',
         			time_cost float NOT NULL default '0',
                  cost_private tinyint(1) NOT NULL DEFAULT '0',
         			PRIMARY KEY (id),
         			KEY entities_id (entities_id)
         		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query) or die($DB->error());
      }
   }

   static function unistall(Migration $migration) {
      $table=self::getTable();
      $migration->displayMessage("Uninstalling $table");
      $migration->dropTable($table);
   }
}