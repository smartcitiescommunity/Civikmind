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
 * Class PluginResourcesDirectory
 */
class PluginResourcesDirectory extends CommonDBTM {

   static $rightname = 'plugin_resources';
   static protected $notable = true;
   private $table = "glpi_users";

   /**
    * Return the localized name of the current Type
    * Should be overloaded in each new class
    *
    * @param integer $nb Number of items
    *
    * @return string
    **/
   static function getTypeName($nb = 0) {

      return _n('Directory', 'Directories', $nb, 'resources');
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
         'id'                 => '1',
         'table'              => $this->table,
         'field'              => 'registration_number',
         'name'               => __('Administrative number'),
         'datatype'           => 'string'
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->table,
         'field'              => 'id',
         'name'               => __('ID'),
         'massiveaction'      => false,
         'datatype'           => 'number'
      ];

      $tab[] = [
         'id'                 => '34',
         'table'              => $this->table,
         'field'              => 'realname',
         'name'               => __('Surname'),
         'datatype'           => 'string'
      ];

      $tab[] = [
         'id'                 => '9',
         'table'              => $this->table,
         'field'              => 'firstname',
         'name'               => __('First name'),
         'datatype'           => 'string'
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => 'glpi_useremails',
         'field'              => 'email',
         'name'               => _n('Email', 'Emails', 2),
         'datatype'           => 'email',
         'joinparams'         => ['jointype' => 'child'],
         'forcegroupby'       => true,
         'massiveaction'      => false
      ];

      $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->table,
         'field'              => 'phone',
         'name'               => __('Phone'),
         'datatype'           => 'string'
      ];

      $tab[] = [
         'id'                 => '10',
         'table'              => $this->table,
         'field'              => 'phone2',
         'name'               => __('Phone 2'),
         'datatype'           => 'string'
      ];

      $tab[] = [
         'id'                 => '11',
         'table'              => $this->table,
         'field'              => 'mobile',
         'name'               => __('Mobile phone'),
         'datatype'           => 'string'
      ];

      $tab[] = [
         'id'                 => '4313',
         'table'              => 'glpi_plugin_resources_employers',
         'field'              => 'completename',
         'name'               => PluginResourcesEmployer::getTypeName(1),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '4314',
         'table'              => 'glpi_plugin_resources_clients',
         'field'              => 'name',
         'name'               => PluginResourcesClient::getTypeName(1),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '4315',
         'table'              => 'glpi_plugin_resources_contracttypes',
         'field'              => 'name',
         'name'               => PluginResourcesContractType::getTypeName(1),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '4316',
         'table'              => 'glpi_plugin_resources_managers',
         'field'              => 'name',
         'name'               => __('Resource manager', 'resources'),
         'searchtype'         => 'contains',
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '4317',
         'table'              => 'glpi_plugin_resources_resources',
         'field'              => 'date_begin',
         'name'               => __('Arrival date', 'resources'),
         'datatype'           => 'date'
      ];

      $tab[] = [
         'id'                 => '4318',
         'table'              => 'glpi_plugin_resources_resources',
         'field'              => 'date_end',
         'name'               => __('Departure date', 'resources'),
         'datatype'           => 'date'
      ];

      $tab[] = [
         'id'                 => '4319',
         'table'              => 'glpi_plugin_resources_departments',
         'field'              => 'name',
         'name'               => PluginResourcesDepartment::getTypeName(1),
         'datatype'           => 'dropdown'
      ];

      $tab[] = [
         'id'                 => '4320',
         'table'              => 'glpi_plugin_resources_resourcestates',
         'field'              => 'name',
         'name'               => PluginResourcesResourceState::getTypeName(1),
         'datatype'           => 'dropdown'
      ];
      return $tab;
   }

      /**
    * Display result table for search engine for an type
    *
    * @param $itemtype item type to manage
    * @param $params search params passed to prepareDatasForSearch function
    *
    * @return nothing
   **/
   static function showList($itemtype, $params) {

      $data = Search::prepareDatasForSearch($itemtype, $params);
      self::constructSQL($data);
      Search::constructData($data);
      Search::displayData($data);
   }

   /**
    * Construct SQL request depending of search parameters
    *
    * add to data array a field sql containing an array of requests :
    *      search : request to get items limited to wanted ones
    *      count : to count all items based on search criterias
    *                    may be an array a request : need to add counts
    *                    maybe empty : use search one to count
    *
    * @since version 0.85
    *
    * @param $data    array of search datas prepared to generate SQL
    *
    * @return nothing
   **/
   static function constructSQL(array &$data) {
      global $CFG_GLPI;

      if (!isset($data['itemtype'])) {
         return false;
      }

      $data['sql']['count']  = [];
      $data['sql']['search'] = '';

      $searchopt        = &Search::getOptions($data['itemtype']);
      $dbu = new DbUtils();
      $blacklist_tables = [];

      $itemtable = $dbu->getTableForItemType("User");

      $PluginResourcesResource = new PluginResourcesResource();

      $entity_restrict = $PluginResourcesResource->isEntityAssign();

      // Construct the request

      //// 1 - SELECT
      // request currentuser for SQL supervision, not displayed
      $SELECT = " SELECT ".Search::addDefaultSelect("User");

      // Add select for all toview item
      foreach ($data['toview'] as $key => $val) {
         $SELECT .= Search::addSelect($data['itemtype'], $val, 0);
      }

      //// 2 - FROM AND LEFT JOIN
      // Set reference table
      $FROM = " FROM `".$itemtable."`";

      // Init already linked tables array in order not to link a table several times
      $already_link_tables = [];
      // Put reference table
      array_push($already_link_tables, $itemtable);

      // Add default join
      $COMMONLEFTJOIN = Search::addDefaultJoin($data['itemtype'], $itemtable, $already_link_tables);

      // Add all table for toview items
      foreach ($data['tocompute'] as $key => $val) {
         if (!in_array($searchopt[$val]["table"], $blacklist_tables)) {
            $COMMONLEFTJOIN .= self::addLeftJoin($data['itemtype'], $itemtable, $already_link_tables,
                                       $searchopt[$val]["table"],
                                       $searchopt[$val]["linkfield"], 0, 0,
                                       $searchopt[$val]["joinparams"],
                                       $searchopt[$val]["field"]);
         }
      }

      // Search all case :
      if ($data['search']['all_search']) {
         foreach ($searchopt as $key => $val) {
            // Do not search on Group Name
            if (is_array($val)) {
               if (!in_array($searchopt[$key]["table"], $blacklist_tables)) {
                  $COMMONLEFTJOIN .= self::addLeftJoin($data['itemtype'], $itemtable, $already_link_tables,
                                             $searchopt[$key]["table"],
                                             $searchopt[$key]["linkfield"], 0, 0,
                                             $searchopt[$key]["joinparams"],
                                             $searchopt[$key]["field"]);
               }
            }
         }
      }
      $FROM          .= $COMMONLEFTJOIN;

      $ASSIGN=" `glpi_plugin_resources_resources`.`is_leaving` = 0 AND `glpi_users`.`is_active` = 1 AND ";
      //// 3 - WHERE

      // default string
      $COMMONWHERE = Search::addDefaultWhere($data['itemtype']);
      $first       = empty($COMMONWHERE);

      // Add deleted if item have it
      if ($data['item'] && $data['item']->maybeDeleted()) {
         $LINK = " AND ";
         if ($first) {
            $LINK  = " ";
            $first = false;
         }
         $COMMONWHERE .= $LINK."`$itemtable`.`is_deleted` = '".$data['search']['is_deleted']."' ";
      }

      // Remove template items
      if ($data['item'] && $data['item']->maybeTemplate()) {
         $LINK = " AND ";
         if ($first) {
            $LINK  = " ";
            $first = false;
         }
         $COMMONWHERE .= $LINK."`$itemtable`.`is_template` = '0' ";
      }

      // Add Restrict to current entities
      if ($entity_restrict) {
         $LINK = " AND ";
         if ($first) {
            $LINK  = " ";
            $first = false;
         }

         $COMMONWHERE .= " `glpi_profiles_users`.`entities_id` IN ('".implode("','", $_SESSION['glpiactiveentities'])."')";
      }
      $WHERE  = "";
      $HAVING = "";

      // Add search conditions
      // If there is search items
      if (count($data['search']['criteria'])) {
         foreach ($data['search']['criteria'] as $key => $criteria) {
            // if real search (strlen >0) and not all and view search
            if (isset($criteria['value']) && (strlen($criteria['value']) > 0)) {
               // common search
               if (($criteria['field'] != "all") && ($criteria['field'] != "view")) {
                  $LINK    = " ";
                  $NOT     = 0;
                  $tmplink = "";
                  if (isset($criteria['link'])) {
                     if (strstr($criteria['link'], "NOT")) {
                        $tmplink = " ".str_replace(" NOT", "", $criteria['link']);
                        $NOT     = 1;
                     } else {
                        $tmplink = " ".$criteria['link'];
                     }
                  } else {
                     $tmplink = " AND ";
                  }

                  if (isset($searchopt[$criteria['field']]["usehaving"])) {
                     // Manage Link if not first item
                     if (!empty($HAVING)) {
                        $LINK = $tmplink;
                     }
                     // Find key
                     $item_num = array_search($criteria['field'], $data['tocompute']);
                     $HAVING  .= Search::addHaving($LINK, $NOT, $data['itemtype'],
                                                 $criteria['field'], $criteria['searchtype'],
                                                 $criteria['value'], 0, $item_num);
                  } else {
                     // Manage Link if not first item
                     if (!empty($WHERE)) {
                        $LINK = $tmplink;
                     }
                     $WHERE .= Search::addWhere($LINK, $NOT, $data['itemtype'], $criteria['field'],
                                              $criteria['searchtype'], $criteria['value']);
                  }

                  // view and all search
               } else {
                  $LINK       = " OR ";
                  $NOT        = 0;
                  $globallink = " AND ";

                  if (isset($criteria['link'])) {
                     switch ($criteria['link']) {
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
                     $tmplink =" AND ";
                  }

                  // Manage Link if not first item
                  if (!empty($WHERE)) {
                     $WHERE .= $globallink;
                  }
                  $WHERE .= " ( ";
                  $first2 = true;

                  $items = [];

                  if ($criteria['field'] == "all") {
                     $items = $searchopt;

                  } else { // toview case : populate toview
                     foreach ($data['toview'] as $key2 => $val2) {
                        $items[$val2] = $searchopt[$val2];
                     }
                  }

                  foreach ($items as $key2 => $val2) {
                     if (isset($val2['nosearch']) && $val2['nosearch']) {
                        continue;
                     }
                     if (is_array($val2)) {
                        // Add Where clause if not to be done in HAVING CLAUSE
                        if (!isset($val2["usehaving"])) {
                           $tmplink = $LINK;
                           if ($first2) {
                              $tmplink = " ";
                              $first2  = false;
                           }
                           $WHERE .= Search::addWhere($tmplink, $NOT, $data['itemtype'], $key2,
                                                    $criteria['searchtype'], $criteria['value']);
                        }
                     }
                  }
                  $WHERE .= " ) ";
               }
            }
         }
      }

      //// 4 - ORDER
      $ORDER = " ORDER BY `id` ";
      foreach ($data['tocompute'] as $key => $val) {
         if ($data['search']['sort'] == $val) {
            $ORDER = Search::addOrderBy($data['itemtype'], $data['search']['sort'],
                                      $data['search']['order'], $key);
         }
      }

      //// 5 - META SEARCH
      // Preprocessing
      if (count($data['search']['metacriteria'])) {
         // Already link meta table in order not to linked a table several times
         $already_link_tables2 = [];
         $metanum              = count($data['toview'])-1;

         foreach ($data['search']['metacriteria'] as $key => $metacriteria) {
            if (isset($metacriteria['itemtype']) && !empty($metacriteria['itemtype'])
                && isset($metacriteria['value']) && (strlen($metacriteria['value']) > 0)) {

               $metaopt = &Search::getOptions($metacriteria['itemtype']);
               $sopt    = $metaopt[$metacriteria['field']];
               $metanum++;

                // a - SELECT
               $SELECT .= Search::addSelect($metacriteria['itemtype'], $metacriteria['field'],
                                          1, $metacriteria['itemtype']);

               // b - ADD LEFT JOIN
               // Link reference tables
               if (!in_array($dbu->getTableForItemType($metacriteria['itemtype']),
                                                 $already_link_tables2)) {
                  $FROM .= Search::addMetaLeftJoin($data['itemtype'], $metacriteria['itemtype'],
                                                 $already_link_tables2,
                                                 (($metacriteria['value'] == "NULL")
                                                  || (strstr($metacriteria['link'], "NOT"))));
               }

               // Link items tables
               if (!in_array($sopt["table"]."_".$metacriteria['itemtype'],
                             $already_link_tables2)) {

                  $FROM .= self::addLeftJoin($metacriteria['itemtype'],
                                             $dbu->getTableForItemType($metacriteria['itemtype']),
                                             $already_link_tables2, $sopt["table"],
                                             $sopt["linkfield"], 1, $metacriteria['itemtype'],
                                             $sopt["joinparams"], $sopt["field"]);
               }
               // Where
               $LINK = "";
               // For AND NOT statement need to take into account all the group by items
               if (strstr($metacriteria['link'], "AND NOT")
                   || isset($sopt["usehaving"])) {

                  $NOT = 0;
                  if (strstr($metacriteria['link'], "NOT")) {
                     $tmplink = " ".str_replace(" NOT", "", $metacriteria['link']);
                     $NOT     = 1;
                  } else {
                     $tmplink = " ".$metacriteria['link'];
                  }
                  if (!empty($HAVING)) {
                     $LINK = $tmplink;
                  }
                  $HAVING .= Search::addHaving($LINK, $NOT, $metacriteria['itemtype'],
                                             $metacriteria['field'], $metacriteria['searchtype'],
                                             $metacriteria['value'], 1, $metanum);
               } else { // Meta Where Search
                  $LINK = " ";
                  $NOT  = 0;
                  // Manage Link if not first item
                  if (isset($metacriteria['link'])
                      && strstr($metacriteria['link'], "NOT")) {

                     $tmplink = " ".str_replace(" NOT", "", $metacriteria['link']);
                     $NOT     = 1;

                  } else if (isset($metacriteria['link'])) {
                     $tmplink = " ".$metacriteria['link'];

                  } else {
                     $tmplink = " AND ";
                  }

                  if (!empty($WHERE)) {
                     $LINK = $tmplink;
                  }
                  $WHERE .= Search::addWhere($LINK, $NOT, $metacriteria['itemtype'],
                                           $metacriteria['field'], $metacriteria['searchtype'],
                                           $metacriteria['value'], 1);
               }
            }
         }
      }

      //// 6 - Add item ID
      // Add ID to the select
      if (!empty($itemtable)) {
         $SELECT .= "`$itemtable`.`id` AS id ";
      }

      //// 7 - Manage GROUP BY
      $GROUPBY = "";
      // Meta Search / Search All / Count tickets
      if ((count($data['search']['metacriteria']))
          || !empty($HAVING)
          || $data['search']['all_search']) {
         $GROUPBY = " GROUP BY `$itemtable`.`id`";
      }

      if (empty($GROUPBY)) {
         foreach ($data['toview'] as $key2 => $val2) {
            if (!empty($GROUPBY)) {
               break;
            }
            if (isset($searchopt[$val2]["forcegroupby"])) {
               $GROUPBY = " GROUP BY `$itemtable`.`id`";
            }
         }
      }

      $LIMIT   = "";

      // If export_all reset LIMIT condition
      if ($data['search']['export_all']) {
         $LIMIT = "";
      }

      if (!empty($WHERE) || !empty($COMMONWHERE)) {
         if (!empty($COMMONWHERE)) {
            $WHERE = ' WHERE '.$ASSIGN.' '.$COMMONWHERE.(!empty($WHERE)?' AND ( '.$WHERE.' )':'');
         } else {
            $WHERE = ' WHERE '.$ASSIGN.' '.$WHERE.' ';
         }
         $first = false;
      }

      if (!empty($HAVING)) {
         $HAVING = ' HAVING '.$HAVING;
      }

      // Create QUERY
      if (isset($CFG_GLPI["union_search_type"][$data['itemtype']])) {
         $first = true;
         $QUERY = "";
         foreach ($CFG_GLPI[$CFG_GLPI["union_search_type"][$data['itemtype']]] as $ctype) {
            $ctable = $dbu->getTableForItemType($ctype);
            if (($citem = $dbu->getItemForItemtype($ctype))
                && $citem->canView()) {
               if ($first) {
                  $first = false;
               } else {
                  $QUERY .= " UNION ";
               }
               $tmpquery = "";
               // AllAssets case
               if ($data['itemtype'] == 'AllAssets') {
                  $tmpquery = $SELECT.", '$ctype' AS TYPE ".
                              $FROM.
                              $WHERE;

                  $tmpquery .= " AND `$ctable`.`id` IS NOT NULL ";

                  // Add deleted if item have it
                  if ($citem && $citem->maybeDeleted()) {
                     $tmpquery .= " AND `$ctable`.`is_deleted` = 0 ";
                  }

                  // Remove template items
                  if ($citem && $citem->maybeTemplate()) {
                     $tmpquery .= " AND `$ctable`.`is_template` = 0 ";
                  }

                  $tmpquery.= $GROUPBY.
                              $HAVING;

                  $tmpquery = str_replace($CFG_GLPI["union_search_type"][$data['itemtype']],
                                          $ctable, $tmpquery);
                  $tmpquery = str_replace($data['itemtype'], $ctype, $tmpquery);

               } else {// Ref table case
                  $reftable = $dbu->getTableForItemType($data['itemtype']);

                  $tmpquery = $SELECT.", '$ctype' AS TYPE,
                                      `$reftable`.`id` AS refID, "."
                                      `$ctable`.`entities_id` AS ENTITY ".
                              $FROM.
                              $WHERE;
                  if ($data['item']->maybeDeleted()) {
                     $tmpquery = str_replace("`".$CFG_GLPI["union_search_type"][$data['itemtype']]."`.
                                                `is_deleted`",
                                             "`$reftable`.`is_deleted`", $tmpquery);
                  }

                  $replace = "FROM `$reftable`"."
                              INNER JOIN `$ctable`"."
                                 ON (`$reftable`.`items_id`=`$ctable`.`id`"."
                                     AND `$reftable`.`itemtype` = '$ctype')";
                  $tmpquery = str_replace("FROM `".
                                             $CFG_GLPI["union_search_type"][$data['itemtype']]."`",
                                          $replace, $tmpquery);
                  $tmpquery = str_replace($CFG_GLPI["union_search_type"][$data['itemtype']],
                                          $ctable, $tmpquery);
               }
               $tmpquery = str_replace("ENTITYRESTRICT",
                                       $dbu->getEntitiesRestrictRequest('', $ctable, '', '',
                                                                  $citem->maybeRecursive()),
                                       $tmpquery);

               // SOFTWARE HACK
               if ($ctype == 'Software') {
                  $tmpquery = str_replace("`glpi_softwares`.`serial`", "''", $tmpquery);
                  $tmpquery = str_replace("`glpi_softwares`.`otherserial`", "''", $tmpquery);
               }
               $QUERY .= $tmpquery;
            }
         }
         if (empty($QUERY)) {
            echo Search::showError($data['display_type']);
            return;
         }
         $QUERY .= str_replace($CFG_GLPI["union_search_type"][$data['itemtype']].".", "", $ORDER) .
                   $LIMIT;
      } else {
         $QUERY = $SELECT.
                  $FROM.
                  $WHERE.
                  $GROUPBY.
                  $HAVING.
                  $ORDER.
                  $LIMIT;
      }
      $data['sql']['search'] = $QUERY;
   }

   /**
    * Generic Function to add left join to a request
    *
    * @param $itemtype                    item type
    * @param $ref_table                   reference table
    * @param $already_link_tables  array  of tables already joined
    * @param $new_table                   new table to join
    * @param $linkfield                   linkfield for LeftJoin
    * @param $meta                        is it a meta item ? (default 0)
    * @param $meta_type                   meta type table (default 0)
    * @param $joinparams           array  join parameters (condition / joinbefore...)
    * @param $field                string field to display (needed for translation join) (default '')
    *
    * @return Left join string
   **/
   static function addLeftJoin($itemtype, $ref_table, array &$already_link_tables, $new_table,
                               $linkfield, $meta = 0, $meta_type = 0, $joinparams = [], $field = '') {
      global $CFG_GLPI;

      // Rename table for meta left join
      $AS      = "";
      $nt      = $new_table;
      $cleannt = $nt;
      $dbu     = new DbUtils();

      // Virtual field no link
      if (strpos($linkfield, '_virtual') === 0) {
         return false;
      }

      // Multiple link possibilies case
      //       if ($new_table=="glpi_users"
      //           || $new_table=="glpi_groups"
      //           || $new_table=="glpi_users_validation") {
      if (!empty($linkfield) && ($linkfield != $dbu->getForeignKeyFieldForTable($new_table))) {
         $nt .= "_".$linkfield;
         $AS  = " AS `$nt`";
      }

      $complexjoin = Search::computeComplexJoinID($joinparams);

      if (!empty($complexjoin)) {
         $nt .= "_".$complexjoin;
         $AS  = " AS `$nt`";
      }

      //       }

      $addmetanum = "";
      $rt         = $ref_table;
      $cleanrt    = $rt;
      if ($meta) {
         $addmetanum = "_".$meta_type;
         $AS         = " AS `$nt$addmetanum`";
         $nt         = $nt.$addmetanum;
      }

      // Auto link
      if (($ref_table == $new_table)
          && empty($complexjoin)) {
         return "";
      }

      // Do not take into account standard linkfield
      $tocheck = $nt.".".$linkfield;
      if ($linkfield == $dbu->getForeignKeyFieldForTable($new_table)) {
         $tocheck = $nt;
      }

      if (in_array($tocheck, $already_link_tables)) {
         return "";
      }
      array_push($already_link_tables, $tocheck);

      $specific_leftjoin = '';

      // Plugin can override core definition for its type
      if ($plug = isPluginItemType($itemtype)) {
         $function = 'plugin_'.$plug['plugin'].'_addLeftJoin';
         if (function_exists($function)) {
            $specific_leftjoin = $function($itemtype, $ref_table, $new_table, $linkfield,
                                           $already_link_tables);
         }
      }

      // Link with plugin tables : need to know left join structure
      if (empty($specific_leftjoin)
          && preg_match("/^glpi_plugin_([a-z0-9]+)/", $new_table, $matches)) {
         if (count($matches) == 2) {
            $function = 'plugin_'.$matches[1].'_addLeftJoin';
            if (function_exists($function)) {
               $specific_leftjoin = $function($itemtype, $ref_table, $new_table, $linkfield,
                                              $already_link_tables);
            }
         }
      }

      if (!empty($linkfield)) {
         $before = '';

         if (isset($joinparams['beforejoin']) && is_array($joinparams['beforejoin'])) {

            if (isset($joinparams['beforejoin']['table'])) {
               $joinparams['beforejoin'] = [$joinparams['beforejoin']];
            }

            foreach ($joinparams['beforejoin'] as $tab) {
               if (isset($tab['table'])) {
                  $intertable = $tab['table'];
                  if (isset($tab['linkfield'])) {
                     $interlinkfield = $tab['linkfield'];
                  } else {
                     $interlinkfield = $dbu->getForeignKeyFieldForTable($intertable);
                  }

                  $interjoinparams = [];
                  if (isset($tab['joinparams'])) {
                     $interjoinparams = $tab['joinparams'];
                  }
                  $before .= self::addLeftJoin($itemtype, $rt, $already_link_tables, $intertable,
                                               $interlinkfield, $meta, $meta_type, $interjoinparams);
               }

               // No direct link with the previous joins
               if (!isset($tab['joinparams']['nolink']) || !$tab['joinparams']['nolink']) {
                  $cleanrt     = $intertable;
                  $complexjoin = Search::computeComplexJoinID($interjoinparams);
                  if (!empty($complexjoin)) {
                     $intertable .= "_".$complexjoin;
                  }
                  $rt = $intertable.$addmetanum;
               }
            }
         }

         $addcondition = '';
         if (isset($joinparams['condition'])) {
            $from         = ["`REFTABLE`", "REFTABLE", "`NEWTABLE`", "NEWTABLE"];
            $to           = ["`$rt`", "`$rt`", "`$nt`", "`$nt`"];
            $addcondition = str_replace($from, $to, $joinparams['condition']);
            $addcondition = $addcondition." ";
         }

         if (!isset($joinparams['jointype'])) {
            $joinparams['jointype'] = 'standard';
         }

         if (empty($specific_leftjoin)) {
            switch ($new_table) {
               // No link
               case "glpi_auth_tables" :
                     $user_searchopt     = Search::getOptions('User');

                     $specific_leftjoin  = self::addLeftJoin($itemtype, $rt, $already_link_tables,
                                                             "glpi_authldaps", 'auths_id', 0, 0,
                                                             $user_searchopt[30]['joinparams']);
                     $specific_leftjoin .= self::addLeftJoin($itemtype, $rt, $already_link_tables,
                                                             "glpi_authmails", 'auths_id', 0, 0,
                                                             $user_searchopt[31]['joinparams']);
                     break;
            }
         }

         if (empty($specific_leftjoin)) {
            switch ($joinparams['jointype']) {
               case 'child' :
                  $linkfield = $dbu->getForeignKeyFieldForTable($cleanrt);
                  if (isset($joinparams['linkfield'])) {
                     $linkfield = $joinparams['linkfield'];
                  }

                  // Child join
                  $specific_leftjoin = " LEFT JOIN `$new_table` $AS
                                             ON (`$rt`.`id` = `$nt`.`$linkfield`
                                                 $addcondition)";
                  break;

               case 'item_item' :
                  // Item_Item join
                  $specific_leftjoin = " LEFT JOIN `$new_table` $AS
                                          ON ((`$rt`.`id`
                                                = `$nt`.`".$dbu->getForeignKeyFieldForTable($cleanrt)."_1`
                                               OR `$rt`.`id`
                                                 = `$nt`.`".$dbu->getForeignKeyFieldForTable($cleanrt)."_2`)
                                              $addcondition)";
                  break;

               case 'item_item_revert' :
                  // Item_Item join reverting previous item_item
                  $specific_leftjoin = " LEFT JOIN `$new_table` $AS
                                          ON ((`$nt`.`id`
                                                = `$rt`.`".$dbu->getForeignKeyFieldForTable($cleannt)."_1`
                                               OR `$nt`.`id`
                                                 = `$rt`.`".$dbu->getForeignKeyFieldForTable($cleannt)."_2`)
                                              $addcondition)";
                  break;

               case "mainitemtype_mainitem" :
                  $addmain = 'main';

               case "itemtype_item" :
                  if (!isset($addmain)) {
                     $addmain = '';
                  }
                  $used_itemtype = $itemtype;
                  if (isset($joinparams['specific_itemtype'])
                      && !empty($joinparams['specific_itemtype'])) {
                     $used_itemtype = $joinparams['specific_itemtype'];
                  }
                  // Itemtype join
                  $specific_leftjoin = " LEFT JOIN `$new_table` $AS
                                          ON (`$rt`.`id` = `$nt`.`".$addmain."items_id`
                                              AND `$nt`.`".$addmain."itemtype` = '$used_itemtype'
                                              $addcondition) ";
                  break;

               case "itemtypeonly" :
                  $used_itemtype = $itemtype;
                  if (isset($joinparams['specific_itemtype'])
                      && !empty($joinparams['specific_itemtype'])) {
                     $used_itemtype = $joinparams['specific_itemtype'];
                  }
                  // Itemtype join
                  $specific_leftjoin = " LEFT JOIN `$new_table` $AS
                                          ON (`$nt`.`itemtype` = '$used_itemtype'
                                              $addcondition) ";
                  break;

               default :
                  // Standard join
                  $specific_leftjoin = "LEFT JOIN `$new_table` $AS
                                          ON (`$rt`.`$linkfield` = `$nt`.`id`
                                              $addcondition)";
                  $transitemtype = $dbu->getItemTypeForTable($new_table);
                  if (Session::haveTranslations($transitemtype, $field)) {
                     $transAS            = $nt.'_trans';
                     $specific_leftjoin .= "LEFT JOIN `glpi_dropdowntranslations` AS `$transAS`
                                             ON (`$transAS`.`itemtype` = '$transitemtype'
                                                 AND `$transAS`.`items_id` = `$nt`.`id`
                                                 AND `$transAS`.`language` = '".
                                                       $_SESSION['glpilanguage']."'
                                                 AND `$transAS`.`field` = '$field')";
                  }
                  break;
            }
         }

         return $before.$specific_leftjoin;
      }
   }

   /**
    * @since version 0.84
    * */
   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      $forbidden[] = 'purge';
      return $forbidden;
   }

   //Massive action

   /**
    * Get the specific massive actions
    *
    * @since 0.84
    *
    * This should be overloaded in Class
    *
    * @param object $checkitem link item to check right (default NULL)
    *
    * @return array an array of massive actions
    **/
   function getSpecificMassiveActions($checkitem = null) {

      $actions = [];
      if (Session::getCurrentInterface() == "central") {
         $actions['PluginResourcesDirectory'.MassiveAction::CLASS_ACTION_SEPARATOR.'Send'] = __('Send a notification');
      }
      return $actions;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    * */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {

      $input    = $ma->getInput();

      switch ($ma->getAction()) {
         case "Send" :
            if ($item->sendEmail($ids)) {
               $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_OK);
            } else {
               $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
            }
            break;

         default :
            return parent::doSpecificMassiveActions($input);
      }
   }

   /**
    * @param $items
    *
    * @return bool
    */
   function sendEmail($items) {
      $User  = new User();
      $mail  = "";
      $first = true;
      foreach ($items as $key => $val) {
         if ($User->getFromDB($key)) {
            $email = $User->getDefaultEmail();
            if (!empty($email)) {
               if (!$first) {
                  $mail.=";";
               } else {
                  $first = false;
               }
               $mail.= $email;
            }
         }
      }

      $send = "<a href='mailto:$mail'>".__('Click here to send your email', 'resources')."</a>";
      Session::addMessageAfterRedirect($send);

      return true;
   }

}

