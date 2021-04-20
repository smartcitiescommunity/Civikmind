<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Metademands plugin for GLPI
 Copyright (C) 2018-2019 by the Metademands Development Team.

 https://github.com/InfotelGLPI/metademands
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Metademands.

 Metademands is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Metademands is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Metademands. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/**
 * @return bool
 * @throws \GlpitestSQLError
 */
function plugin_metademands_install() {
   global $DB;

   include_once(GLPI_ROOT . "/plugins/metademands/inc/profile.class.php");

   if (!$DB->tableExists("glpi_plugin_metademands_metademands")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/empty-2.7.5.sql");
   }

   if ($DB->tableExists("glpi_plugin_metademands_profiles")
       && !$DB->fieldExists("glpi_plugin_metademands_profiles", "requester")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.0.1.sql");
   }

   if (!$DB->tableExists("glpi_plugin_metademands_configs")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.0.2.sql");
   }

   if (!$DB->fieldExists("glpi_plugin_metademands_fields", "order")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.0.3.sql");
   }

   if (!$DB->fieldExists("glpi_plugin_metademands_configs", "create_pdf")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.1.4.sql");
   }

   if (!$DB->fieldExists("glpi_plugin_metademands_fields", "color")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.3.1.sql");
   }

   //version 2.3.2
   if (!$DB->fieldExists("glpi_plugin_metademands_fields", "parent_field_id")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.3.2.sql");
   }

   //version 2.4.1
   if (!$DB->fieldExists("glpi_plugin_metademands_fields", "comment_values")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.4.1.sql");
   }
   //version 2.5.2
   if (!$DB->fieldExists("glpi_plugin_metademands_configs", "childs_parent_content")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.5.2.sql");
   }

   //version 2.6.2
   if (!$DB->fieldExists("glpi_plugin_metademands_fields", "row_display")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.6.2.sql");
   }

   //version 2.6.3
   if (!$DB->fieldExists("glpi_plugin_metademands_configs", "display_type")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.6.3.sql");
   }

   //version 2.7.1
   if (!$DB->fieldExists("glpi_plugin_metademands_fields", "is_basket") &&
       !$DB->fieldExists("glpi_plugin_metademands_metademands", "is_order")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.7.1.sql");

      include(GLPI_ROOT . "/plugins/metademands/install/update270_271.php");
      update270_271();

      $field  = new PluginMetademandsField();
      $fields = $field->find();
      foreach ($fields as $f) {
         if (!empty($f["hidden_link"])) {
            $array                 = [];
            $array[]               = $f["hidden_link"];
            $update["id"]          = $f["id"];
            $update["hidden_link"] = json_encode($array);
            $field->update($update);
         }
      }
   }

   //version 2.7.2
   if (!$DB->fieldExists("glpi_plugin_metademands_metademands", "create_one_ticket")) {

      $sql    = "SHOW COLUMNS FROM `glpi_plugin_metademands_metademands`";
      $result = $DB->query($sql);
      while ($data = $DB->fetchArray($result)) {
         if ($data['Field'] == 'itilcategories_id' && $data['Type'] == 'int(1)') {
            include(GLPI_ROOT . "/plugins/metademands/install/update270_271.php");
            update270_271();
         }
      }

      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.7.2.sql");
   }

   //version 2.7.4
   if (!$DB->fieldExists("glpi_plugin_metademands_fields", "hidden_block")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.7.4.sql");

      $field  = new PluginMetademandsField();
      $fields = $field->find(['type' => "dropdown", "item" => "user"]);
      foreach ($fields as $f) {
         $f["item"] = "User";
         $f["type"] = "dropdown_object";
         $field->update($f);
      }
      $fields = $field->find(['type' => "dropdown", "item" => "usertitle"]);
      foreach ($fields as $f) {
         $f["item"] = "UserTitle";
         $field->update($f);
      }
      $fields = $field->find(['type' => "dropdown", "item" => "usercategory"]);
      foreach ($fields as $f) {
         $f["item"] = "UserCategory";
         $field->update($f);
      }
      $fields = $field->find(['type' => "dropdown", "item" => "group"]);
      foreach ($fields as $f) {
         $f["item"] = "Group";
         $f["type"] = "dropdown_object";
         $field->update($f);
      }
      $fields = $field->find(['type' => "dropdown", "item" => "location"]);
      foreach ($fields as $f) {
         $f["item"] = "Location";
         $field->update($f);
      }
      $fields = $field->find(['type' => "dropdown", "item" => "appliance"]);
      foreach ($fields as $f) {
         $f["item"] = "Appliance";
         $f["type"] = "dropdown_object";
         $field->update($f);
      }
      $fields = $field->find(['type' => "dropdown", "item" => "itilcategory"]);
      foreach ($fields as $f) {
         $f["item"] = "ITILCategory_Metademands";
         $f["type"] = "dropdown_meta";
         $field->update($f);
      }
      $fields = $field->find(['type' => "dropdown", "item" => "other"]);
      foreach ($fields as $f) {
         $f["item"] = "other";
         $f["type"] = "dropdown_meta";
         $field->update($f);
      }
      $fields = $field->find(['type' => "dropdown", "item" => "PluginResourcesResource"]);
      foreach ($fields as $f) {
         $f["type"] = "dropdown_object";
         $field->update($f);
      }
   }

   //version 2.7.5
   if (!$DB->fieldExists("glpi_plugin_metademands_fields", "display_type")) {
      $DB->runFile(GLPI_ROOT . "/plugins/metademands/install/sql/update-2.7.5.sql");
      $sql    = "SHOW COLUMNS FROM `glpi_plugin_metademands_fields`";
      $result = $DB->query($sql);
      while ($data = $DB->fetchArray($result)) {
         if ($data['Field'] == 'fields_link') {
            include(GLPI_ROOT . "/plugins/metademands/install/update274_275.php");
            update274_275();
         }
      }
      include(GLPI_ROOT . "/plugins/metademands/install/migrateExistingMetaWithNewStatus.php");
      migrateAllExistingMetademandsWithNewStatus();
   }

   PluginMetademandsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
   PluginMetademandsProfile::initProfile();
   $DB->query("DROP TABLE IF EXISTS `glpi_plugin_metademands_profiles`;");

   return true;
}

// Uninstall process for plugin : need to return true if succeeded
/**
 * @return bool
 * @throws \GlpitestSQLError
 */
function plugin_metademands_uninstall() {
   global $DB;

   // Plugin tables deletion
   $tables = ["glpi_plugin_metademands_metademands_resources",
              "glpi_plugin_metademands_configs",
              "glpi_plugin_metademands_tickets_itilenvironments",
              "glpi_plugin_metademands_tickets_itilapplications",
              "glpi_plugin_metademands_itilenvironments",
              "glpi_plugin_metademands_itilapplications",
              "glpi_plugin_metademands_groups",
              "glpi_plugin_metademands_metademandtasks",
              "glpi_plugin_metademands_tickets_metademands",
              "glpi_plugin_metademands_tickets_tasks",
              "glpi_plugin_metademands_tickettasks",
              "glpi_plugin_metademands_ticketfields",
              "glpi_plugin_metademands_tickets_fields",
              "glpi_plugin_metademands_fields",
              "glpi_plugin_metademands_tasks",
              "glpi_plugin_metademands_metademands",
              "glpi_plugin_metademands_basketlines",
              "glpi_plugin_metademands_fieldtranslations",
              "glpi_plugin_metademands_metademandtranslations",
              "glpi_plugin_metademands_metademandvalidations"];
   foreach ($tables as $table) {
      $DB->query("DROP TABLE IF EXISTS `$table`;");
   }

   include_once(GLPI_ROOT . "/plugins/metademands/inc/profile.class.php");

   PluginMetademandsProfile::removeRightsFromSession();
   PluginMetademandsProfile::removeRightsFromDB();

   return true;
}

// Define Dropdown tables to be manage in GLPI
/**
 * @return array
 */
//function plugin_metademands_getDropdown() {
//
//   $plugin = new Plugin();
//
//   if ($plugin->isActivated("metademands")) {
//      return ['PluginMetademandsMetademand'      => PluginMetademandsMetademand::getTypeName(2),
//      ];
//   } else {
//      return [];
//   }
//}

// Hook done on purge item case
/**
 * @param $item
 */
function plugin_pre_item_purge_metademands($item) {
   switch (get_class($item)) {
      case 'PluginMetademandsMetademand' :
         $temp = new PluginMetademandsTask();
         $temp->deleteByCriteria(['plugin_metademands_metademands_id' => $item->getField('id')], 1);

         $temp = new PluginMetademandsGroup();
         $temp->deleteByCriteria(['plugin_metademands_metademands_id' => $item->getField('id')], 1);

         $temp = new PluginMetademandsField();
         $temp->deleteByCriteria(['plugin_metademands_metademands_id' => $item->getField('id')], 1);

         $temp = new PluginMetademandsTicketField();
         $temp->deleteByCriteria(['plugin_metademands_metademands_id' => $item->getField('id')], 1);

         $temp = new PluginMetademandsMetademandValidation();
         $temp->deleteByCriteria(['plugin_metademands_metademands_id' => $item->getField('id')], 1);
         break;

      case 'PluginMetademandsTask' :
         $temp = new PluginMetademandsTicketTask();
         $temp->deleteByCriteria(['plugin_metademands_tasks_id' => $item->getField('id')], 1);

         $temp = new PluginMetademandsMetademandTask();
         $temp->deleteByCriteria(['plugin_metademands_tasks_id' => $item->getField('id')], 1);
         break;

      case 'PluginMetademandsField' :
         $temp = new PluginMetademandsTicket_Field();
         $temp->deleteByCriteria(['plugin_metademands_fields_id' => $item->getField('id')], 1);
         break;

      case 'Ticket' :
         $temp = new PluginMetademandsTicket_Task();
         $temp->deleteByCriteria(['tickets_id' => $item->getField('id')], 1);

         $temp = new PluginMetademandsTicket_Metademand();
         $temp->deleteByCriteria(['tickets_id' => $item->getField('id')], 1);

         $temp = new PluginMetademandsTicket_Field();
         $temp->deleteByCriteria(['tickets_id' => $item->getField('id')], 1);

         break;

      case 'Group' :
         $temp = new PluginMetademandsGroup();
         $temp->deleteByCriteria(['groups_id' => $item->getField('id')], 1);
         break;

      case 'PluginResourcesContractType' :
         $temp = new PluginMetademandsMetademand_Resource();
         $temp->deleteByCriteria(['plugin_resources_contracttypes_id' => $item->getField('id')], 1);
         break;
   }
}

// How to display specific actions ?
// options contain at least itemtype and and action
/**
 * @param array $options
 */
function plugin_metademands_MassiveActionsDisplay($options = []) {

   switch ($options['itemtype']) {
      case 'PluginMetademandsMetademand':
         switch ($options['action']) {
            case "plugin_metademands_duplicate":
               echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" 
                     class=\"submit\" value=\"" . _sx('button', 'Post') . "\" >";
               break;
         }
         break;
   }
}

// Define dropdown relations
/**
 * @return array|\string[][]
 */
function plugin_metademands_getDatabaseRelations() {

   $plugin = new Plugin();
   if ($plugin->isActivated("metademands")) {
      return ["glpi_entities" => ["glpi_plugin_metademands_metademands"           => "entities_id",
                                  "glpi_plugin_metademands_fields"                => "entities_id",
                                  "glpi_plugin_metademands_metademands_resources" => "entities_id",
                                  "glpi_plugin_metademands_ticketfields"          => "entities_id",
                                  "glpi_plugin_metademands_tasks"                 => "entities_id"],

              "glpi_plugin_metademands_metademands" => ["glpi_plugin_metademands_fields"                => "plugin_metademands_metademands_id",
                                                        "glpi_plugin_metademands_tickets_metademands"   => "plugin_metademands_metademands_id",
                                                        "glpi_plugin_metademands_metademandtasks"       => "plugin_metademands_metademands_id",
                                                        "glpi_plugin_metademands_ticketfields"          => "plugin_metademands_metademands_id",
                                                        "glpi_plugin_metademands_tasks"                 => "plugin_metademands_metademands_id",
                                                        "glpi_plugin_metademands_metademands_resources" => "plugin_metademands_metademands_id"],

              "glpi_tickets" => ["glpi_plugin_metademands_tickets_fields"      => "tickets_id",
                                 "glpi_plugin_metademands_tickets_tasks"       => "tickets_id",
                                 "glpi_plugin_metademands_tickets_metademands" => "tickets_id",
              ],

              "glpi_plugin_metademands_fields" => ["glpi_plugin_metademands_tickets_fields" => "plugin_metademands_fields_id"],

              "glpi_plugin_metademands_tasks" => ["glpi_plugin_metademands_fields"          => "plugin_metademands_tasks_id",
                                                  "glpi_plugin_metademands_tickettasks"     => "plugin_metademands_tasks_id",
                                                  "glpi_plugin_metademands_tickets_tasks"   => "plugin_metademands_tasks_id",
                                                  "glpi_plugin_metademands_metademandtasks" => "plugin_metademands_tasks_id"],
      ];
   } else {
      return [];
   }
}

/**
 * @param $data
 *
 * @return mixed
 */
/**
 * @param $data
 *
 * @return mixed
 */
function plugin_metademands_MassiveActionsProcess($data) {
   $metademand = new PluginMetademandsMetademand();
   $res        = $metademand->doSpecificMassiveActions($data);

   return $res;
}


function plugin_metademands_registerMethods() {
   global $WEBSERVICES_METHOD;

   $WEBSERVICES_METHOD['metademands.addMetademands']
      = ['PluginMetademandsMetademand', 'methodAddMetademands'];
   $WEBSERVICES_METHOD['metademands.listMetademands']
      = ['PluginMetademandsMetademand', 'methodListMetademands'];
   $WEBSERVICES_METHOD['metademands.listMetademandsfields']
      = ['PluginMetademandsField', 'methodListMetademandsfields'];
   $WEBSERVICES_METHOD['metademands.listTasktypes']
      = ['PluginMetademandsTask', 'methodListTasktypes'];
   $WEBSERVICES_METHOD['metademands.showMetademands']
      = ['PluginMetademandsMetademand', 'methodShowMetademands'];
   $WEBSERVICES_METHOD['metademands.showTicketForm']
      = ['PluginMetademandsTicket', 'methodShowTicketForm'];
   $WEBSERVICES_METHOD['metademands.isMandatoryFields']
      = ['PluginMetademandsTicket', 'methodIsMandatoryFields'];

}

function plugin_metademands_timeline_actions($data) {
   global $CFG_GLPI;
   $metaValidation = new PluginMetademandsMetademandValidation();
   if ($metaValidation->getFromDBByCrit(['tickets_id' => $data['item']->fields['id']]) && $_SESSION['glpiactiveprofile']['interface'] == 'central') {
      $rand = $data['rand'];
      echo "<script type='text/javascript' >\n
//      $(document).ready(function() {
//                $('.ajax_box').show();
//      });
      function viewAddMetaValidation" . $data['item']->fields['id'] . "$rand(itemtype) {\n";

      $params = ['action'     => 'viewsubitem',
                 'type'       => 'itemtype',
                 'tickets_id' => $data['item']->fields['id'],
                 'id'         => -1];
      $out    = Ajax::updateItemJsCode("viewitem" . $data['item']->fields['id'] . "$rand",
                                       $CFG_GLPI["root_doc"] . "/plugins/metademands/ajax/timeline.php",
                                       $params, "", false);
      echo str_replace("\"itemtype\"", "itemtype", $out);
      echo "};
      ";

      echo "</script>\n";
      echo "<li class='metavalidation' onclick='" .
           "javascript:viewAddMetaValidation" . $data['item']->fields['id'] . "$rand(\"Solution\");'>"
           . "<i class='fa fa-thumbs-up'></i>" . __('Metademand validation', 'metademands') . "</li>";
   }


}


// Define search option for types of the plugins
/**
 * @param $itemtype
 *
 * @return array
 */
function plugin_metademands_getAddSearchOptions($itemtype) {

   $sopt = [];
   if ($itemtype == "Ticket") {
      if (Session::haveRight("plugin_metademands", READ)) {
         $sopt[9500]['table']         = 'glpi_plugin_metademands_tickets_metademands';
         $sopt[9500]['field']         = 'status';
         $sopt[9500]['name']          = __('Metademand status', 'metademands');
         $sopt[9500]['datatype']      = "specific";
         $sopt[9500]['searchtype']    = "equals";
         $sopt[9500]['joinparams']    = ['jointype'  => 'child'];
         $sopt[9500]['massiveaction'] = false;

         $sopt[9501]['table']         = 'glpi_plugin_metademands_metademandvalidations';
         $sopt[9501]['field']         = 'validate';
         $sopt[9501]['name']          = PluginMetademandsMetademandValidation::getTypeName(1);
         $sopt[9501]['datatype']      = "specific";
         $sopt[9501]['searchtype']    = "equals";
         $sopt[9501]['joinparams']    = ['jointype'  => 'child'];
         $sopt[9501]['massiveaction'] = false;

         $sopt[9502]['table']         = 'glpi_plugin_metademands_tickets_tasks';
         $sopt[9502]['field']         = 'id';
         $sopt[9502]['name']          = __("Group child ticket",'metademands');
         $sopt[9502]['datatype']      = "specific";
         $sopt[9502]['searchtype']    = "equals";
         $sopt[9502]['forcegroupby']    = true;
         //         $sopt[9502]['linkfield']     = 'parent_tickets_id';
         $sopt[9502]['joinparams']    = ['jointype'  => 'child','linkfield'=>'parent_tickets_id'];
         $sopt[9502]['massiveaction'] = false;

         $sopt[9503]['table']         = 'glpi_plugin_metademands_tickets_tasks';
         $sopt[9503]['field']         = 'tickets_id';
         $sopt[9503]['name']          = __('Link to metademands','metademands');
         $sopt[9503]['datatype']      = "specific";
         $sopt[9503]['searchtype']    = "";
//         $sopt[9503]['forcegroupby']    = true;
         //         $sopt[9502]['linkfield']     = 'parent_tickets_id';
         $sopt[9503]['joinparams']    = ['jointype'  => 'child','linkfield'=>'parent_tickets_id'];
//         $sopt[9503]['joinparams']    = ['jointype'  => 'child'];
         $sopt[9503]['massiveaction'] = false;
      }
   }
   return $sopt;
}


function plugin_metademands_addWhere($link, $nott, $type, $ID, $val, $searchtype) {

   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   switch ($table.".".$field) {
      case "glpi_plugin_metademands_tickets_metademands.status":
         return $link." `glpi_plugin_metademands_tickets_metademands`.`status` = '$val'";
      case "glpi_plugin_metademands_metademandvalidations.validate":
         return $link." `glpi_plugin_metademands_metademandvalidations`.`validate` = '$val'";
      case "glpi_plugin_metademands_tickets_tasks.id":
         switch ($searchtype) {
            case 'equals' :
               if($val === '0'){
                  return " $link 1=1";
               }
               if($val == 'mygroups'){
                  return " $link (`glpi_groups_metademands`.`id` IN ('".implode("','",
                                                                                $_SESSION['glpigroups'])."')) ";
               }else{
                  return " $link (`glpi_groups_metademands`.`id` IN ('".$val."')) ";
               }
               break;


            case 'notequals' :
               return " $link (`glpi_groups_metademands`.`id` NOT IN ('".implode("','",
                                                                                 $_SESSION['glpigroups'])."')) ";
               break;
            case 'contains' :
               return " $link 1=1";
               break;
         }


         break;
      case "glpi_plugin_metademands_tickets_tasks.tickets_id":
         return " $link 1=1";

         break;
   }
   return "";
}

/**
 * @param $type
 * @param $ref_table
 * @param $new_table
 * @param $linkfield
 * @param $already_link_tables
 *
 * @return \Left|string
 */
function plugin_metademands_addLeftJoin($type, $ref_table, $new_table, $linkfield, &$already_link_tables) {

   // Rename table for meta left join
   $AS = "";
   $AS_device = "";
   $nt = "glpi_plugin_resources_resources";
   $nt_device = "glpi_plugin_resources_resources_items";
   // Multiple link possibilies case
   if ($new_table == "glpi_plugin_metademands_tickets_tasks" || $new_table == "glpi_plugin_resources_managers" || $new_table == "glpi_plugin_resources_recipients" || $new_table == "glpi_plugin_resources_recipients_leaving") {
      $AS = " AS ".$new_table;
      $AS_device = " AS glpi_plugin_resources_resources_items_".$linkfield;
      $nt.="_".$linkfield;
      $nt_device.="_".$linkfield;
   }

   switch ($new_table) {
      //
      case "glpi_plugin_metademands_tickets_tasks" :
         return "LEFT JOIN `glpi_plugin_metademands_tickets_tasks` $AS ON (`$ref_table`.`id` = `glpi_plugin_metademands_tickets_tasks`.`parent_tickets_id` )
          LEFT JOIN `glpi_groups_tickets` AS glpi_groups_tickets_metademands ON (`$new_table`.`tickets_id` = `glpi_groups_tickets_metademands`.`tickets_id` ) 
          LEFT JOIN `glpi_groups` AS glpi_groups_metademands ON (`glpi_groups_tickets_metademands`.`groups_id` = `glpi_groups_metademands`.`id` )";
         break;

   }
   return "";
}

function plugin_metademands_addSelect($type, $ID, $num) {
   $searchopt = &Search::getOptions($type);
   $table     = $searchopt[$ID]["table"];
   $field     = $searchopt[$ID]["field"];

   if ($table == "glpi_plugin_metademands_tickets_tasks"
       && $type == "Ticket") {
      return " GROUP_CONCAT(DISTINCT CONCAT(IFNULL(`glpi_groups_metademands`.`completename`, '__NULL__'), '$#$',`glpi_groups_metademands`.`id`)
   ORDER BY `glpi_groups_metademands`.`id` SEPARATOR '$$##$$') AS `ITEM_$num`, ";
      //      return "$table.$field, ";
   } else {
      return "";
   }
}

function plugin_metademands_giveItem($type, $field, $data, $num, $linkfield = "") {
   global $CFG_GLPI;
   switch ($field) {
      case 9503:
         $out = $data['id'];
         $options['criteria'][0]['field']      = 50; // status
         $options['criteria'][0]['searchtype'] = 'equals';
         $options['criteria'][0]['value']      = $data['id'];
         $options['criteria'][0]['link']       = 'AND';

         $options['criteria'][1]['field']      = 8; // groups_id
         $options['criteria'][1]['searchtype'] = 'equals';
         $options['criteria'][1]['value']      = 'mygroups';
         $options['criteria'][1]['link']       = 'AND';
         $metademands = new PluginMetademandsTicket_Metademand();
         if($metademands->getFromDBByCrit(['tickets_id'=>$data['id']])){
            $DB  = DBConnection::getReadConnection();
            $dbu = new DbUtils();
            $get_running_parents_tickets_meta =
               "SELECT  COUNT( DISTINCT `glpi_plugin_metademands_tickets_metademands`.`id`) as 'total_running' FROM `glpi_tickets`
                        LEFT JOIN `glpi_plugin_metademands_tickets_metademands` ON `glpi_tickets`.`id` =  `glpi_plugin_metademands_tickets_metademands`.`tickets_id`
                         LEFT JOIN `glpi_plugin_metademands_tickets_tasks`  ON (`glpi_tickets`.`id` = `glpi_plugin_metademands_tickets_tasks`.`parent_tickets_id` )
                         LEFT JOIN `glpi_groups_tickets` AS glpi_groups_tickets_metademands ON (`glpi_plugin_metademands_tickets_tasks`.`tickets_id` = `glpi_groups_tickets_metademands`.`tickets_id` ) 
                         LEFT JOIN `glpi_groups` AS glpi_groups_metademands ON (`glpi_groups_tickets_metademands`.`groups_id` = `glpi_groups_metademands`.`id` ) WHERE
                            `glpi_tickets`.`is_deleted` = 0 AND `glpi_plugin_metademands_tickets_metademands`.`status` =  
                                    " . PluginMetademandsTicket_Metademand::RUNNING . " AND (`glpi_groups_metademands`.`id` IN ('".implode("','",
                                                                                                                                           $_SESSION['glpigroups'])."')) AND  `glpi_tickets`.`id` =  ".$data['id'] ." ".
               $dbu->getEntitiesRestrictRequest('AND', 'glpi_tickets');


            $total_running_parents_meta = $DB->query($get_running_parents_tickets_meta);

            $total_running = 0;
            while ($row = $DB->fetchArray($total_running_parents_meta)) {
               $total_running = $row['total_running'];
            }
            if ($total_running > 0) {
               $out = "<a href=\"".$CFG_GLPI["root_doc"]."/front/ticket.php?".
                      Toolbox::append_params($options, '&amp;')."\"><i class='center fas fa-share-alt fa-2x'></i></a>";
               return $out;
            } else {
               return " ";
            }
         }
         return " ";
         break;
   }

   return "";
}
