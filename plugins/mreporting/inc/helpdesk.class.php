<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Mreporting plugin for GLPI
 Copyright (C) 2003-2011 by the mreporting Development Team.

 https://forge.indepnet.net/projects/mreporting
 -------------------------------------------------------------------------

 LICENSE

 This file is part of mreporting.

 mreporting is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 mreporting is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with mreporting. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

class PluginMreportingHelpdesk Extends PluginMreportingBaseclass {

   function reportPieTicketNumberByEntity($config = []) {
      $_SESSION['mreporting_selector']['reportPieTicketNumberByEntity'] = ['dateinterval'];

      return $this->reportHbarTicketNumberByEntity($config);
   }

   function reportHbarTicketNumberByEntity($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHbarTicketNumberByEntity'] = ['dateinterval',
                                                                            'limit'];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("`glpi_tickets`.`date`",
                                                           $config['delay'],
                                                           $config['randname']);

      $datas = [];

      $query = "SELECT COUNT(glpi_tickets.id) as count,
         glpi_entities.name as name
      FROM glpi_tickets
      LEFT JOIN glpi_entities
         ON (glpi_tickets.entities_id = glpi_entities.id)
      WHERE {$this->sql_date} ";

      if (Session::isMultiEntitiesMode()) {
         $query.= "AND glpi_entities.id IN (".$this->where_entities.") ";
      }
      $query.= "AND glpi_tickets.is_deleted = '0'
      GROUP BY glpi_entities.name
      ORDER BY count DESC
      LIMIT 0, ";
      $query .= (isset($_REQUEST['glpilist_limit'])) ? $_REQUEST['glpilist_limit'] : 20;

      $result = $DB->query($query);

      while ($ticket = $DB->fetchAssoc($result)) {
         if (empty($ticket['name'])) {
            $label = __("Root entity");
         } else {
            $label = $ticket['name'];
         }
         $datas['datas'][$label] = $ticket['count'];
      }

      return $datas;

   }

   function reportHgbarTicketNumberByCatAndEntity($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHgbarTicketNumberByCatAndEntity']
         = ['dateinterval'];

      $datas = [];
      $tmp_datas = [];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("glpi_tickets.date",
                                                           $config['delay'],
                                                           $config['randname']);

      //get categories used in this period
      $query_cat = "SELECT DISTINCT(glpi_tickets.itilcategories_id) as itilcategories_id,
         glpi_itilcategories.completename as category
      FROM glpi_tickets
      LEFT JOIN glpi_itilcategories
         ON glpi_tickets.itilcategories_id = glpi_itilcategories.id
      WHERE {$this->sql_date} ";

      if (Session::isMultiEntitiesMode()) {
         $query_cat.= "AND glpi_tickets.entities_id IN (".$this->where_entities.") ";
      }

      $query_cat.= "AND glpi_tickets.is_deleted = '0'
      ORDER BY glpi_itilcategories.id ASC";

      $res_cat = $DB->query($query_cat);

      $categories = [];
      while ($data = $DB->fetchAssoc($res_cat)) {
         if (empty($data['category'])) {
            $data['category'] = __("None");
         }
         $categories[$data['category']] = $data['itilcategories_id'];
      }

      $labels2 = array_keys($categories);

      $tmp_cat = [];
      foreach (array_values($categories) as $id) {
         $tmp_cat[] = "cat_$id";
      }
      $cat_str = "'".implode("', '", array_values($categories))."'";

      //count ticket by entity and categories previously selected
      $query = "SELECT
         COUNT(glpi_tickets.id) as nb,
         glpi_entities.name as entity,
         glpi_tickets.itilcategories_id as cat_id
      FROM glpi_tickets
      LEFT JOIN glpi_entities
         ON glpi_tickets.entities_id = glpi_entities.id
      WHERE glpi_tickets.itilcategories_id IN ($cat_str) ";

      if (Session::isMultiEntitiesMode()) {
         $query.= "AND glpi_tickets.entities_id IN (".$this->where_entities.")";
      }

      $query.= "AND ".$this->sql_date."
         AND glpi_tickets.is_deleted = '0'
      GROUP BY glpi_entities.name, glpi_tickets.itilcategories_id
      ORDER BY glpi_entities.name ASC, glpi_tickets.itilcategories_id ASC";
      $res = $DB->query($query);
      while ($data = $DB->fetchAssoc($res)) {
         if (empty($data['entity'])) {
            $data['entity'] = __("Root entity");
         }
         $tmp_datas[$data['entity']]["cat_".$data['cat_id']] = $data['nb'];
      }

      //merge missing datas (0 ticket for a category)
      foreach ($tmp_datas as &$data) {
         $data = $data + array_fill_keys($tmp_cat, 0);
      }

      //replace cat_id by labels2
      foreach ($tmp_datas as $entity => &$subdata) {
         $tmp = [];
         $i = 0;
         foreach ($subdata as $value) {
            $cat_label = $labels2[$i];
            $tmp[$cat_label] = $value;
            $i++;
         }
         $subdata = $tmp;
      }

      $datas['datas'] = $tmp_datas;

      foreach ($categories as $key => $value) {
         $datas['labels2'][$key] = $key;
      }

      return $datas;
   }

   function reportPieTicketOpenedAndClosed($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportPieTicketOpenedAndClosed']
         = ['dateinterval'];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("glpi_tickets.date",
                                                           $config['delay'],
                                                           $config['randname']);

      $datas = [];
      foreach ($this->filters as $filter) {

         $query = "SELECT COUNT(*)
            FROM glpi_tickets
            WHERE ".$this->sql_date." ";

         if (Session::isMultiEntitiesMode()) {
            $query.= "AND glpi_tickets.entities_id IN (".$this->where_entities.")";
         }

         $query.= "AND glpi_tickets.is_deleted = '0'
            AND glpi_tickets.status IN('".implode("', '", array_keys($filter['status']))."')";
         $result = $DB->query($query);
         $datas[$filter['label']] = $DB->result($result, 0, 0);
      }

      return ['datas' => $datas];
   }

   function reportPieTicketOpenedbyStatus($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportPieTicketOpenedbyStatus']
         = ['dateinterval', 'allstates'];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("glpi_tickets.date",
                                                           $config['delay'],
                                                           $config['randname']);

      // Get status to show
      if (isset($_POST['status_1'])) {
         foreach ($_POST as $key => $value) {
            if ((substr($key, 0, 7) == 'status_') && ($value == 1)) {
               $status_to_show[] = substr($key, 7, 1);
            }
         }
      } else {
         $status_to_show = ['1', '2', '3', '4'];
      }

      $datas = [];
      $status = $this->filters['open']['status'] + $this->filters['close']['status'];
      foreach ($status as $key => $val) {
         if (in_array($key, $status_to_show)) {
            $query = "SELECT COUNT(glpi_tickets.id) as count
               FROM glpi_tickets
               WHERE {$this->sql_date}
                  AND glpi_tickets.is_deleted = '0'
                  AND glpi_tickets.entities_id IN ({$this->where_entities})
                  AND glpi_tickets.status ='{$key}'";
            $result = $DB->query($query);

            while ($ticket = $DB->fetchAssoc($result)) {
               $datas['datas'][$val] = $ticket['count'];
            }
         }
      }

      return $datas;
   }

   function reportPieTopTenAuthor($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportPieTopTenAuthor']
         = ['dateinterval'];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("glpi_tickets.date",
                                                           $config['delay'],
                                                           $config['randname']);
      $this->sql_closedate = PluginMreportingCommon::getSQLDate("glpi_tickets.closedate",
                                                                $config['delay'],
                                                                $config['randname']);

      $datas = [];
      $query = "SELECT COUNT(glpi_tickets.id) as count, glpi_tickets_users.users_id as users_id
         FROM glpi_tickets
         LEFT JOIN glpi_tickets_users
            ON (glpi_tickets_users.tickets_id = glpi_tickets.id AND glpi_tickets_users.type =1)
         WHERE {$this->sql_date}
            AND {$this->sql_closedate}
            AND glpi_tickets.entities_id IN ({$this->where_entities})
            AND glpi_tickets.is_deleted = '0'
         GROUP BY glpi_tickets_users.users_id
         ORDER BY count DESC
         LIMIT 10";
      $result = $DB->query($query);
      while ($ticket = $DB->fetchAssoc($result)) {
         if ($ticket['users_id']==0) {
            $label = __("Undefined", 'mreporting');
         } else {
            $label = getUserName($ticket['users_id']);
         }
         $datas['datas'][$label] = $ticket['count'];
      }

      return $datas;
   }


   function reportHgbarOpenTicketNumberByCategoryAndByType($config = []) {
      $_SESSION['mreporting_selector']['reportHgbarOpenTicketNumberByCategoryAndByType']
         = ['dateinterval'];
      return $this->reportHgbarTicketNumberByCategoryAndByType($config, 'open');
   }

   function reportHgbarCloseTicketNumberByCategoryAndByType($config = []) {
      $_SESSION['mreporting_selector']['reportHgbarCloseTicketNumberByCategoryAndByType']
         = ['dateinterval'];
      return $this->reportHgbarTicketNumberByCategoryAndByType($config, 'close');
   }

   private function reportHgbarTicketNumberByCategoryAndByType(array $config, $filter) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHgbarTicketNumberByCategoryAndByType']
         = ['dateinterval'];

      $datas = [];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("glpi_tickets.date",
                                                           $config['delay'],
                                                           $config['randname']);

      $query = "SELECT glpi_itilcategories.id as category_id,
            glpi_itilcategories.completename as category_name,
            glpi_tickets.type as type,
            COUNT(glpi_tickets.id) as count
         FROM glpi_tickets
         LEFT JOIN glpi_itilcategories
            ON glpi_itilcategories.id = glpi_tickets.itilcategories_id
         WHERE {$this->sql_date}
            AND glpi_tickets.entities_id IN ({$this->where_entities})
            AND glpi_tickets.status IN('".implode(
               "', '", array_keys($this->filters[$filter]['status']))."')
            AND glpi_tickets.is_deleted = '0'
         GROUP BY glpi_itilcategories.id, glpi_tickets.type
         ORDER BY glpi_itilcategories.name";
      $result = $DB->query($query);

      $datas['datas'] = [];
      while ($ticket = $DB->fetchAssoc($result)) {
         if (is_null($ticket['category_id'])) {
            $ticket['category_id'] = 0;
            $ticket['category_name'] = __("None");
         }
         if ($ticket['type']==0) {
            $type = __("Undefined", 'mreporting');
         } else {
            $type = Ticket::getTicketTypeName($ticket['type']);
         }
         $datas['labels2'][$type] = $type;
         $datas['datas'][$ticket['category_name']][$type] = $ticket['count'];
      }

      return $datas;
   }

   function reportHgbarTicketNumberByService($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHgbarTicketNumberByService']
         = ['dateinterval'];

      $datas = [];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("glpi_tickets.date",
                                                           $config['delay'],
                                                           $config['randname']);

      foreach ($this->filters as $class => $filter) {

         $datas['labels2'][$filter['label']] = $filter['label'];
         $query = "SELECT COUNT(*)
            FROM glpi_tickets
            WHERE id NOT IN (
               SELECT tickets_id
               FROM glpi_groups_tickets
               WHERE glpi_groups_tickets.type = 1
            )
               AND glpi_tickets.entities_id IN (".$this->where_entities.")
               AND {$this->sql_date}
               AND status IN('".implode("', '", array_keys($filter['status']))."')";
         $result = $DB->query($query);

         $datas['datas'][__("None")][$filter['label']] = $DB->result($result, 0, 0);

         $query = "SELECT glpi_groups.name as group_name,
               COUNT(glpi_tickets.id) as count
            FROM glpi_tickets, glpi_groups_tickets, glpi_groups
            WHERE glpi_tickets.id = glpi_groups_tickets.tickets_id
               AND glpi_tickets.entities_id IN ({$this->where_entities})
               AND glpi_groups_tickets.groups_id = glpi_groups.id
               AND glpi_groups_tickets.type = 1
               AND glpi_tickets.is_deleted = '0'
               AND {$this->sql_date}
               AND glpi_tickets.status IN('".implode("', '", array_keys($filter['status']))."')
            GROUP BY glpi_groups.id
            ORDER BY glpi_groups.name";
         $result = $DB->query($query);

         while ($ticket = $DB->fetchAssoc($result)) {
            $datas['datas'][$ticket['group_name']][$filter['label']] = $ticket['count'];
         }

      }

      return $datas;
   }

   function reportHgbarOpenedTicketNumberByCategory($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHgbarOpenedTicketNumberByCategory']
         = ['dateinterval', 'allstates'];

      $datas = [];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("glpi_tickets.date",
                                                           $config['delay'],
                                                           $config['randname']);

      // Get status to show
      if (isset($_POST['status_1'])) {
         foreach ($_POST as $key => $value) {
            if (substr($key, 0, 7) == 'status_' && $value == 1) {
               $status_to_show[] = substr($key, 7, 1);
            }
         }
      } else {
         $status_to_show = ['1', '2', '3', '4'];
      }

      $status = $this->filters['open']['status'] + $this->filters['close']['status'];
      $status_keys = array_keys($status);

      $query = "SELECT glpi_tickets.status,
            glpi_itilcategories.completename as category_name,
            COUNT(glpi_tickets.id) as count
         FROM glpi_tickets
         LEFT JOIN glpi_itilcategories
            ON glpi_itilcategories.id = glpi_tickets.itilcategories_id
         WHERE {$this->sql_date}
            AND glpi_tickets.entities_id IN (".$this->where_entities.")
            AND glpi_tickets.status IN('".implode("', '", $status_keys)."')
            AND glpi_tickets.is_deleted = '0'
            AND status IN (".implode(',', $status_to_show).")
         GROUP BY glpi_itilcategories.id, glpi_tickets.status
         ORDER BY glpi_itilcategories.name";
      $result = $DB->query($query);

      while ($ticket = $DB->fetchAssoc($result)) {
         if (is_null($ticket['category_name'])) {
            $ticket['category_name'] = __("None");
         }

         if (!isset($datas['datas'][$ticket['category_name']])) {
            foreach ($status as $statusKey => $statusLabel) {
               if (in_array($statusKey, $status_to_show)) {
                  $datas['datas'][$ticket['category_name']][$statusLabel] = 0;
               }
            }
         }

         $datas['datas'][$ticket['category_name']][$status[$ticket['status']]] = $ticket['count'];
      }

      //Define legend for all ticket status available in GLPI
      foreach ($status as $key => $label) {
         if (in_array($key, $status_to_show)) {
            $datas['labels2'][$label] = $label;
         }
      }

      return $datas;
   }

   function reportLineNbTicket($config = []) {
      $_SESSION['mreporting_selector']['reportLineNbTicket'] = ['dateinterval'];

      return $this->reportAreaNbTicket($config, false);
   }

   function reportAreaNbTicket($config = [], $area = true) {
      global $DB;

      $_SESSION['mreporting_selector']['reportAreaNbTicket'] = ['dateinterval', 'period'];

      $datas = [];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("glpi_tickets.date",
                                                           $config['delay'],
                                                           $config['randname']);

      $query = "SELECT
         DISTINCT DATE_FORMAT(date, '".$this->period_sort."') as period,
         DATE_FORMAT(date, '".$this->period_label."') as period_name,
         COUNT(id) as nb
      FROM glpi_tickets
      WHERE {$this->sql_date}
         AND glpi_tickets.entities_id IN ({$this->where_entities})
         AND glpi_tickets.is_deleted = '0'
      GROUP BY period
      ORDER BY period";
      $res = $DB->query($query);
      while ($data = $DB->fetchAssoc($res)) {
         $datas['datas'][$data['period_name']] = $data['nb'];
      }

      return $datas;
   }

   function reportVstackbarNbTicket($config = []) {
      $_SESSION['mreporting_selector']['reportVstackbarNbTicket'] = ['dateinterval'];
      return $this->reportGlineNbTicket($config, false);
   }

   function reportGareaNbTicket($config = []) {
      $_SESSION['mreporting_selector']['reportGareaNbTicket'] = ['dateinterval'];
      return $this->reportGlineNbTicket($config, true);
   }

   function reportGlineNbTicket($config = [], $area = false) {
      global $DB;

      $_SESSION['mreporting_selector']['reportGlineNbTicket']
         = ['dateinterval', 'period', 'allstates'];

      $datas = [];
      $tmp_datas = [];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("glpi_tickets.date",
                                                           $config['delay'],
                                                           $config['randname']);

      // Get status to show
      if (isset($_POST['status_1'])) {
         foreach ($_POST as $key => $value) {
            if ((substr($key, 0, 7) == 'status_') && ($value == 1)) {
               $status_to_show[] = substr($key, 7, 1);
            }
         }
      } else {
         $status_to_show = ['1', '2', '3', '4'];
      }

      //get dates used in this period
      $query_date = "SELECT DISTINCT
         DATE_FORMAT(`date`, '".$this->period_sort."') AS period,
         DATE_FORMAT(`date`, '".$this->period_label."') AS period_name
      FROM `glpi_tickets`
      WHERE ".$this->sql_date."
      AND `glpi_tickets`.`entities_id` IN (".$this->where_entities.")
      AND `glpi_tickets`.`is_deleted` = '0'
      AND status IN(".implode(',', $status_to_show).")
      ORDER BY `date` ASC";
      $res_date = $DB->query($query_date);
      $dates = [];
      while ($data = $DB->fetchAssoc($res_date)) {
         $dates[$data['period']] = $data['period'];
      }

      $tmp_date = [];
      foreach (array_values($dates) as $id) {
         $tmp_date[] = $id;
      }

      $query = "SELECT DISTINCT
         DATE_FORMAT(date, '".$this->period_sort."') as period,
         DATE_FORMAT(date, '".$this->period_label."') as period_name,
         status,
         COUNT(id) as nb
      FROM glpi_tickets
      WHERE ".$this->sql_date."
         AND glpi_tickets.entities_id IN (".$this->where_entities.")
         AND glpi_tickets.is_deleted = '0'
         AND status IN(".implode(',', $status_to_show).")
      GROUP BY period, status
      ORDER BY period, status";
      $res = $DB->query($query);
      while ($data = $DB->fetchAssoc($res)) {
         $status =Ticket::getStatus($data['status']);
         $datas['labels2'][$data['period']] = $data['period_name'];
         $datas['datas'][$status][$data['period']] = $data['nb'];
      }

      //merge missing datas (not defined status for a month)
      if (isset($datas['datas'])) {
         foreach ($datas['datas'] as &$data) {
            $data = $data + array_fill_keys($tmp_date, 0);
         }
      }

      //fix order of datas
      if (count($datas) > 0) {
         foreach ($datas['datas'] as &$data) {
            ksort($data);
         }
      }
      return $datas;
   }

   function reportSunburstTicketByCategories($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportSunburstTicketByCategories'] = ['dateinterval'];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("glpi_tickets.date",
                                                           $config['delay'],
                                                           $config['randname']);

      $flat_datas = [];
      $datas = [];

      $query = "SELECT glpi_tickets.itilcategories_id as id,
            glpi_itilcategories.name as name,
            glpi_itilcategories.itilcategories_id as parent,
            COUNT(glpi_tickets.id) as count
         FROM glpi_tickets
         LEFT JOIN glpi_itilcategories
            ON glpi_itilcategories.id = glpi_tickets.itilcategories_id
         WHERE {$this->sql_date}
            AND glpi_tickets.entities_id IN ({$this->where_entities})
            AND glpi_tickets.is_deleted = '0'
         GROUP BY glpi_itilcategories.id
         ORDER BY glpi_itilcategories.name";
      $res = $DB->query($query);
      while ($data = $DB->fetchAssoc($res)) {
         $flat_datas[$data['id']] = $data;
      }

      //get full parent list
      krsort($flat_datas);
      $itilcategory = new ITILCategory;
      foreach ($flat_datas as $cat_id => $current_datas) {
         if (!isset($flat_datas[$current_datas['parent']])) {

            if ($current_datas['parent'] != 0
            && $itilcategory->getFromDB($current_datas['parent'])) {
               $flat_datas[$current_datas['parent']] = [
                'id'     => $current_datas['parent'],
                'name'   => $itilcategory->fields['name'],
                'parent' => $itilcategory->fields['itilcategories_id'],
                'count'  => 0
               ];
            }
         }
      }

      $tree_datas['datas'] = PluginMreportingCommon::buildTree($flat_datas);

      return $tree_datas;
   }


   function reportVstackbarTicketStatusByTechnician($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportVstackbarTicketStatusByTechnician'] = ['dateinterval'];

      $datas = [];
      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("glpi_tickets.date",
                                                           $config['delay'],
                                                           $config['randname']);

      $status = $this->filters['open']['status'] + $this->filters['close']['status'];
      $status_keys = array_keys($status);

      //get technician list
      $technicians = [];
      $query = "SELECT
            CONCAT(glpi_users.firstname, ' ', glpi_users.realname) as fullname,
            glpi_users.name as username
         FROM glpi_tickets
         INNER JOIN glpi_tickets_users
            ON glpi_tickets_users.tickets_id = glpi_tickets.id
            AND glpi_tickets_users.type = 2
         INNER JOIN glpi_users
            ON glpi_users.id = glpi_tickets_users.users_id
         WHERE {$this->sql_date}
         AND glpi_tickets.entities_id IN ({$this->where_entities})
         AND glpi_tickets.is_deleted = '0'
         ORDER BY fullname, username";
      $result = $DB->query($query);

      while ($technician = $DB->fetchAssoc($result)) {
         $technicians[] = ['username' => $technician['username'],
                           'fullname' => $technician['fullname'],
                           ];
      }

      //prepare empty values with technician list
      foreach ($status as $key_status => $current_status) {
         foreach ($technicians as $technician) {
            $datas['datas'][$current_status][$technician['username']] = 0;

            $fullname = trim($technician['fullname']);
            if (!empty($fullname)) {
               $datas['labels2'][$technician['username']] = $fullname;
            } else {
               $datas['labels2'][$technician['username']] = $technician['username'];
            }
         }
      }

      $query = "SELECT glpi_tickets.status,
            CONCAT(glpi_users.firstname, ' ', glpi_users.realname) as technician,
            glpi_users.name as username,
            COUNT(glpi_tickets.id) as count
         FROM glpi_tickets
         INNER JOIN glpi_tickets_users
            ON glpi_tickets_users.tickets_id = glpi_tickets.id
            AND glpi_tickets_users.type = 2
         INNER JOIN glpi_users
            ON glpi_users.id = glpi_tickets_users.users_id
         WHERE {$this->sql_date}
            AND glpi_tickets.entities_id IN ({$this->where_entities})
            AND glpi_tickets.is_deleted = '0'
         GROUP BY status, technician
         ORDER BY technician, username";
      $result = $DB->query($query);

      while ($ticket = $DB->fetchAssoc($result)) {
         if (is_null($ticket['technician'])) {
            $ticket['technician'] = __("None");
         }
         $datas['datas'][$status[$ticket['status']]][$ticket['username']] = $ticket['count'];
      }

      return $datas;
   }

   function reportHbarTicketNumberByLocation($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHbarTicketNumberByLocation']
         = ['dateinterval', 'limit'];

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("`glpi_tickets`.`date`",
                                                           $config['delay'],
                                                           $config['randname']);

      $datas = [];

      $query = "SELECT COUNT(glpi_tickets.id) as count,
         glpi_locations.name as name
      FROM glpi_tickets
      LEFT JOIN glpi_tickets_users
         ON (glpi_tickets.id = glpi_tickets_users.tickets_id
               AND glpi_tickets_users.type = 1)
      LEFT JOIN glpi_users
         ON (glpi_tickets_users.users_id = glpi_users.id)
      LEFT JOIN glpi_locations
         ON (glpi_locations.id = glpi_users.locations_id)
      WHERE {$this->sql_date}
         AND glpi_tickets.is_deleted = '0'
      GROUP BY glpi_locations.name
      ORDER BY count DESC
      LIMIT 0, ";
      $query .= (isset($_REQUEST['glpilist_limit'])) ? $_REQUEST['glpilist_limit'] : 20;

      $result = $DB->query($query);

      while ($ticket = $DB->fetchAssoc($result)) {
         if (empty($ticket['name'])) {
            $label = __("None");
         } else {
            $label = $ticket['name'];
         }
         $datas['datas'][$label] = $ticket['count'];
      }

      return $datas;

   }


   /**
   * Custom dates for allodt export
   * You can configure your dates for the Allodt export
   *
   * @param array $opt : contains the dates
   * @param type $functionname
   * @return $opt
   */
   function customExportDates(array $opt, $functionname) {
      $config = PluginMreportingConfig::initConfigParams($functionname, __CLASS__);

      $opt['date1'] = date('Y-m-j', strtotime($opt['date2'].' -'.$config['delay'].' days'));

      return $opt;
   }

   /**
   * Preconfig datas with your values when init config is done
   *
   * @param type $funct_name
   * @param type $classname
   * @param PluginMreportingConfig $config
   * @return $config
   */
   function preconfig($funct_name, $classname, PluginMreportingConfig $config) {

      if ($funct_name != -1 && $classname) {

         $ex_func = preg_split('/(?<=\\w)(?=[A-Z])/', $funct_name);
         if ($ex_func[0] != 'report') {
            return false;
         }
         $gtype = strtolower($ex_func[1]);

         switch ($gtype) {
            case 'pie':
               $config->fields["name"]=$funct_name;
               $config->fields["classname"]=$classname;
               $config->fields["is_active"]="1";
               $config->fields["show_label"]="hover";
               $config->fields["spline"]="0";
               $config->fields["show_area"]="0";
               $config->fields["show_graph"]="1";
               $config->fields["default_delay"]="30";
               $config->fields["show_label"]="hover";
               break;
            default :
               $config->preconfig($funct_name, $classname);
               break;

         }

      }
      return $config->fields;
   }
}
