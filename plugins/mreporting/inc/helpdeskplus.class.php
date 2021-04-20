<?php

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginMreportingHelpdeskplus Extends PluginMreportingBaseclass {

   protected   $sql_group_assign,
               $sql_group_request,
               $sql_user_assign,
               $sql_type,
               $sql_itilcat,
               $sql_join_cat,
               $sql_join_g,
               $sql_join_u,
               $sql_join_tt,
               $sql_join_tu,
               $sql_join_gt,
               $sql_join_gtr,
               $sql_select_sla;


   function __construct($config = []) {
      global $LANG;
      $this->sql_group_assign  = "1=1";
      $this->sql_group_request = "1=1";
      $this->sql_user_assign   = "1=1";
      $this->sql_type          = "glpi_tickets.type IN (".Ticket::INCIDENT_TYPE.", ".Ticket::DEMAND_TYPE.")";
      $this->sql_itilcat       = "1=1";
      $this->sql_join_cat      = "LEFT JOIN glpi_itilcategories cat
                              ON glpi_tickets.itilcategories_id = cat.id";
      $this->sql_join_g        = "LEFT JOIN glpi_groups g
                              ON gt.groups_id = g.id";
      $this->sql_join_u        = "LEFT JOIN glpi_users u
                              ON tu.users_id = u.id";
      $this->sql_join_tt       = "LEFT JOIN glpi_tickettasks tt
                              ON tt.tickets_id  = glpi_tickets.id";
      $this->sql_join_tu       = "LEFT JOIN glpi_tickets_users tu
                              ON tu.tickets_id = glpi_tickets.id
                              AND tu.type = ".Ticket_User::ASSIGN;
      $this->sql_join_gt       = "LEFT JOIN glpi_groups_tickets gt
                              ON gt.tickets_id  = glpi_tickets.id
                              AND gt.type = ".Group_Ticket::ASSIGN;
      $this->sql_join_gtr      = "LEFT JOIN glpi_groups_tickets gtr
                              ON gtr.tickets_id = glpi_tickets.id
                              AND gtr.type = ".Group_Ticket::REQUESTER;
      $this->sql_select_sla    = "CASE WHEN glpi_slas.definition_time = 'day'
                                             AND glpi_tickets.solve_delay_stat <= glpi_slas.number_time * 86400
                                       THEN 'ok'
                                       WHEN glpi_slas.definition_time = 'hour'
                                             AND glpi_tickets.solve_delay_stat <= glpi_slas.number_time * 3600
                                       THEN 'ok'
                                       WHEN glpi_slas.definition_time = 'minute'
                                             AND glpi_tickets.solve_delay_stat <= glpi_slas.number_time * 60
                                       THEN 'ok'
                                 ELSE 'nok'
                                 END AS respected_sla";

      parent::__construct($config);

      $this->lcl_slaok = $LANG['plugin_mreporting']['Helpdeskplus']['slaobserved'];
      $this->lcl_slako = $LANG['plugin_mreporting']['Helpdeskplus']['slanotobserved'];

      $mr_values = $_SESSION['mreporting_values'];

      if (isset($mr_values['groups_assign_id'])) {
         if (is_array($mr_values['groups_assign_id'])) {
            $this->sql_group_assign = "gt.groups_id IN (".
                                       implode(',', $mr_values['groups_assign_id']).")";
         } else if ($mr_values['groups_assign_id'] > 0) {
            $this->sql_group_assign = "gt.groups_id = ".$mr_values['groups_assign_id'];
         }
      }

      if (isset($mr_values['groups_request_id'])) {
         if (is_array($mr_values['groups_request_id'])) {
            $this->sql_group_request = "gtr.groups_id IN (".
                                          implode(',', $mr_values['groups_request_id']).")";
         } else if ($mr_values['groups_request_id'] > 0) {
            $this->sql_group_request = "gt.groups_id = ".$mr_values['groups_request_id'];
         }
      }

      if (isset($mr_values['users_assign_id'])
          && $mr_values['users_assign_id'] > 0) {
         $this->sql_user_assign = "tu.users_id = ".$mr_values['users_assign_id'];
      }

      if (isset($mr_values['type'])
          && $mr_values['type'] > 0) {
         $this->sql_type = "glpi_tickets.type = ".$mr_values['type'];
      }

      if (isset($mr_values['itilcategories_id'])
          && $mr_values['itilcategories_id'] > 0) {
         $this->sql_itilcat = "glpi_tickets.itilcategories_id = ".$mr_values['itilcategories_id'];
      }
   }

   function reportGlineBacklogs($config = []) {
      global $DB, $LANG;

      $_SESSION['mreporting_selector']['reportGlineBacklogs'] =
         ['dateinterval', 'period', 'backlogstates', 'multiplegrouprequest',
          'userassign', 'category', 'multiplegroupassign'];

      $tab   = [];
      $datas = [];

      $search_new       = (!isset($_SESSION['mreporting_values']['show_new'])
                           || ($_SESSION['mreporting_values']['show_new'] == '1'))     ?true:false;
      $search_solved    = (!isset($_SESSION['mreporting_values']['show_solved'])
                           || ($_SESSION['mreporting_values']['show_solved'] == '1'))  ?true:false;
      $search_backlogs  = (!isset($_SESSION['mreporting_values']['show_backlog'])
                           || ($_SESSION['mreporting_values']['show_backlog'] == '1')) ?true:false;
      $search_closed    = (isset($_SESSION['mreporting_values']['show_closed'])
                           && ($_SESSION['mreporting_values']['show_closed'] == '1'))  ?true:false;

      if ($search_new) {
         $sql_create = "SELECT
                  DISTINCT DATE_FORMAT(date, '{$this->period_sort}') as period,
                  DATE_FORMAT(date, '{$this->period_label}') as period_name,
                  COUNT(DISTINCT glpi_tickets.id) as nb
               FROM glpi_tickets
               {$this->sql_join_tu}
               {$this->sql_join_gt}
               {$this->sql_join_gtr}
               WHERE {$this->sql_date_create}
                  AND glpi_tickets.entities_id IN ({$this->where_entities})
                  AND glpi_tickets.is_deleted = '0'
                  AND {$this->sql_date_create}
                  AND {$this->sql_type}
                  AND {$this->sql_group_assign}
                  AND {$this->sql_group_request}
                  AND {$this->sql_user_assign}
                  AND {$this->sql_itilcat}
               GROUP BY period
               ORDER BY period";
         foreach ($DB->request($sql_create) as $data) {
            $tab[$data['period']]['open'] = $data['nb'];
            $tab[$data['period']]['period_name'] = $data['period_name'];
         }
      }

      if ($search_solved) {
         $sql_solved = "SELECT
                  DISTINCT DATE_FORMAT(solvedate, '{$this->period_sort}') as period,
                  DATE_FORMAT(solvedate, '{$this->period_label}') as period_name,
                  COUNT(DISTINCT glpi_tickets.id) as nb
               FROM glpi_tickets
               {$this->sql_join_tu}
               {$this->sql_join_gt}
               {$this->sql_join_gtr}
               WHERE {$this->sql_date_solve}
                  AND glpi_tickets.entities_id IN ({$this->where_entities})
                  AND glpi_tickets.is_deleted = '0'
                  AND {$this->sql_type}
                  AND {$this->sql_group_assign}
                  AND {$this->sql_group_request}
                  AND {$this->sql_user_assign}
                  AND {$this->sql_itilcat}
               GROUP BY period
               ORDER BY period";
         foreach ($DB->request($sql_solved) as $data) {
            $tab[$data['period']]['solved'] = $data['nb'];
            $tab[$data['period']]['period_name'] = $data['period_name'];
         }
      }

      /**
       * Backlog : Tickets Ouverts à la date en cours...
       */
      if ($search_backlogs) {
         $date_array1=explode("-", $_SESSION['mreporting_values']['date1'.$config['randname']]);
         $time1=mktime(0, 0, 0, $date_array1[1], $date_array1[2], $date_array1[0]);

         $date_array2=explode("-", $_SESSION['mreporting_values']['date2'.$config['randname']]);
         $time2=mktime(0, 0, 0, $date_array2[1], $date_array2[2], $date_array2[0]);

         //if data inverted, reverse it
         if ($time1 > $time2) {
            list($time1, $time2) = [$time2, $time1];
            list($_SESSION['mreporting_values']['date1'.$config['randname']], $_SESSION['mreporting_values']['date2'.$config['randname']]) = [
               $_SESSION['mreporting_values']['date2'.$config['randname']],
               $_SESSION['mreporting_values']['date1'.$config['randname']]
            ];
         }

         $sql_itilcat_backlog = isset($_SESSION['mreporting_values']['itilcategories_id'])
                                && $_SESSION['mreporting_values']['itilcategories_id'] > 0
                                ? " AND tic.itilcategories_id = ".$_SESSION['mreporting_values']['itilcategories_id']
                                : "";

         $begin=strftime($this->period_sort_php, $time1);
         $end=strftime($this->period_sort_php, $time2);
         $sql_date_backlog =  "DATE_FORMAT(list_date.period_l, '{$this->period_sort}') >= '$begin'
                               AND DATE_FORMAT(list_date.period_l, '{$this->period_sort}') <= '$end'";
         $sql_list_date2 = str_replace('date', 'solvedate', $this->sql_list_date);
         $sql_backlog = "SELECT
            DISTINCT(DATE_FORMAT(list_date.period_l, '$this->period_sort')) as period,
            DATE_FORMAT(list_date.period_l, '$this->period_label') as period_name,
            COUNT(DISTINCT(glpi_tickets.id)) as nb
         FROM (
            SELECT DISTINCT period_l
            FROM (
               SELECT
                  {$this->sql_list_date}
               FROM glpi_tickets
               UNION
               SELECT
                  $sql_list_date2
               FROM glpi_tickets
            ) as list_date_union
         ) as list_date
         LEFT JOIN glpi_tickets
            ON glpi_tickets.date <= list_date.period_l
            AND (glpi_tickets.solvedate > list_date.period_l OR glpi_tickets.solvedate IS NULL)
         {$this->sql_join_tu}
         {$this->sql_join_gt}
         {$this->sql_join_gtr}
         WHERE glpi_tickets.entities_id IN ({$this->where_entities})
               AND glpi_tickets.is_deleted = '0'
               AND {$this->sql_type}
               AND {$this->sql_group_assign}
               AND {$this->sql_group_request}
               AND {$this->sql_user_assign}
               AND {$this->sql_itilcat}
               AND $sql_date_backlog
         GROUP BY period";
         foreach ($DB->request($sql_backlog) as $data) {
            $tab[$data['period']]['backlog'] = $data['nb'];
            $tab[$data['period']]['period_name'] = $data['period_name'];
         }

      }

      if ($search_closed) {
         $sql_closed = "SELECT
                  DISTINCT DATE_FORMAT(closedate, '{$this->period_sort}') as period,
                  DATE_FORMAT(closedate, '{$this->period_label}') as period_name,
                  COUNT(DISTINCT glpi_tickets.id) as nb
               FROM glpi_tickets
               {$this->sql_join_tu}
               {$this->sql_join_gt}
               {$this->sql_join_gtr}
               WHERE {$this->sql_date_closed}
                  AND glpi_tickets.entities_id IN ({$this->where_entities})
                  AND glpi_tickets.is_deleted = '0'
                  AND {$this->sql_type}
                  AND {$this->sql_group_assign}
                  AND {$this->sql_group_request}
                  AND {$this->sql_user_assign}
                  AND {$this->sql_itilcat}
               GROUP BY period
               ORDER BY period";
         foreach ($DB->request($sql_closed) as $data) {
            $tab[$data['period']]['closed'] = $data['nb'];
            $tab[$data['period']]['period_name'] = $data['period_name'];
         }
      }

      ksort($tab);

      foreach ($tab as $period => $data) {
         if ($search_new) {
            $datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['opened']][] = (isset($data['open'])) ? $data['open'] : 0;
         }
         if ($search_solved) {
            $datas['datas'][_x('status', 'Solved')][] = (isset($data['solved'])) ? $data['solved'] : 0;
         }
         if ($search_closed) {
            $datas['datas'][_x('status', 'Closed')][] = (isset($data['closed'])) ? $data['closed'] : 0;
         }
         if ($search_backlogs) {
            $datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['backlogs']][] = (isset($data['backlog'])) ? $data['backlog'] : 0;
         }
         $datas['labels2'][] = $data['period_name'];
      }

      return $datas;
   }



   function reportVstackbarLifetime($config = []) {
      global $DB;

      $tab = $datas = $labels2 = [];
      $_SESSION['mreporting_selector']['reportVstackbarLifetime']
         = ['dateinterval', 'period', 'allstates', 'multiplegrouprequest',
            'multiplegroupassign', 'userassign', 'category'];

      if (!isset($_SESSION['mreporting_values']['date2'.$config['randname']])) {
         $_SESSION['mreporting_values']['date2'.$config['randname']] = strftime("%Y-%m-%d");
      }

      foreach ($this->status as $current_status) {
         if ($_SESSION['mreporting_values']['status_'.$current_status] == '1') {
            $status_name = Ticket::getStatus($current_status);
            $sql_status = "SELECT
                     DISTINCT DATE_FORMAT(date, '{$this->period_sort}') as period,
                     DATE_FORMAT(date, '{$this->period_label}') as period_name,
                     COUNT(DISTINCT glpi_tickets.id) as nb
                  FROM glpi_tickets
                  {$this->sql_join_tu}
                  {$this->sql_join_gt}
                  {$this->sql_join_gtr}
                  WHERE {$this->sql_date_create}
                     AND glpi_tickets.entities_id IN ({$this->where_entities})
                     AND glpi_tickets.is_deleted = '0'
                     AND glpi_tickets.status = $current_status
                     AND {$this->sql_type}
                     AND {$this->sql_itilcat}
                     AND {$this->sql_group_assign}
                     AND {$this->sql_group_request}
                     AND {$this->sql_user_assign}
                  GROUP BY period
                  ORDER BY period";
            $res = $DB->query($sql_status);
            while ($data = $DB->fetchAssoc($res)) {
               $tab[$data['period']][$status_name] = $data['nb'];
               $labels2[$data['period']] = $data['period_name'];
            }
         }
      }

      //ascending order of datas by date
      ksort($tab);

      //fill missing datas with zeros
      $datas = $this->fillStatusMissingValues($tab, $labels2);

      return $datas;
   }



   function reportVstackbarTicketsgroups($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportVstackbarTicketsgroups'] =
         ['dateinterval', 'allstates', 'multiplegroupassign', 'category'];

      $tab = [];
      $datas = [];

      if (!isset($_SESSION['mreporting_values']['date2'.$config['randname']])) {
         $_SESSION['mreporting_values']['date2'.$config['randname']] = strftime("%Y-%m-%d");
      }

      foreach ($this->status as $current_status) {
         if ($_SESSION['mreporting_values']['status_'.$current_status] == '1') {
            $status_name = Ticket::getStatus($current_status);
            $sql_status = "SELECT
                     DISTINCT g.completename AS group_name,
                     COUNT(DISTINCT glpi_tickets.id) AS nb
                  FROM glpi_tickets
                  {$this->sql_join_gt}
                  {$this->sql_join_g}
                  WHERE {$this->sql_date_create}
                     AND glpi_tickets.entities_id IN ({$this->where_entities})
                     AND glpi_tickets.is_deleted = '0'
                     AND glpi_tickets.status = $current_status
                     AND {$this->sql_type}
                     AND {$this->sql_itilcat}
                     AND {$this->sql_group_assign}
                  GROUP BY group_name
                  ORDER BY group_name";
            $res = $DB->query($sql_status);
            while ($data = $DB->fetchAssoc($res)) {
               if (empty($data['group_name'])) {
                  $data['group_name'] = __("None");
               }
               $tab[$data['group_name']][$status_name] = $data['nb'];
            }
         }
      }

      //ascending order of datas by date
      ksort($tab);

      //fill missing datas with zeros
      $datas = $this->fillStatusMissingValues($tab);

      return $datas;
   }



   function reportVstackbarTicketstech($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportVstackbarTicketstech']
         = ['dateinterval', 'multiplegroupassign', 'allstates', 'category'];

      $tab = [];
      $datas = [];

      if (!isset($_SESSION['mreporting_values']['date2'.$config['randname']])) {
         $_SESSION['mreporting_values']['date2'.$config['randname']] = strftime("%Y-%m-%d");
      }

      foreach ($this->status as $current_status) {
         if ($_SESSION['mreporting_values']['status_'.$current_status] == '1') {
            $status_name = Ticket::getStatus($current_status);

            $sql_create = "SELECT
                     DISTINCT CONCAT(u.firstname, ' ', u.realname) AS completename,
                     u.name as name,
                     u.id as u_id,
                     COUNT(DISTINCT glpi_tickets.id) AS nb
                  FROM glpi_tickets
                  {$this->sql_join_tu}
                  {$this->sql_join_gt}
                  {$this->sql_join_gtr}
                  {$this->sql_join_u}
                  WHERE {$this->sql_date_create}
                     AND glpi_tickets.entities_id IN ({$this->where_entities})
                     AND glpi_tickets.is_deleted = '0'
                     AND glpi_tickets.status = $current_status
                     AND {$this->sql_group_assign}
                     AND {$this->sql_group_request}
                     AND {$this->sql_type}
                     AND {$this->sql_itilcat}
                  GROUP BY name
                  ORDER BY name";
            $res = $DB->query($sql_create);
            while ($data = $DB->fetchAssoc($res)) {
               $data['name'] = empty($data['completename']) ? __("None") : $data['completename'];

               if (!isset($tab[$data['name']][$status_name])) {
                  $tab[$data['name']][$status_name] = 0;
               }

               $tab[$data['name']][$status_name]+= $data['nb'];
            }
         }
      }

      //ascending order of datas by date
      ksort($tab);

      //fill missing datas with zeros
      $datas = $this->fillStatusMissingValues($tab);

      return $datas;
   }

   function reportHbarTopcategory($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHbarTopcategory']
         = ['dateinterval', 'limit', 'userassign', 'multiplegrouprequest', 'multiplegroupassign', 'type'];

      $tab = [];
      $datas = [];

      $sql_create = "SELECT DISTINCT glpi_tickets.itilcategories_id,
                  COUNT(DISTINCT glpi_tickets.id) as nb,
                  cat.completename
               FROM glpi_tickets
               {$this->sql_join_cat}
               {$this->sql_join_tu}
               {$this->sql_join_gt}
               {$this->sql_join_gtr}
               WHERE {$this->sql_date_create}
                  AND glpi_tickets.entities_id IN ({$this->where_entities})
                  AND glpi_tickets.is_deleted = '0'
                  AND {$this->sql_type}
                  AND {$this->sql_group_assign}
                  AND {$this->sql_group_request}
                  AND {$this->sql_user_assign}
               GROUP BY cat.completename
               ORDER BY nb DESC
               LIMIT 0, ";
      $sql_create .= (isset($_SESSION['mreporting_values']['glpilist_limit']))
                     ? $_SESSION['mreporting_values']['glpilist_limit'] : 20;

      $res = $DB->query($sql_create);
      while ($data = $DB->fetchAssoc($res)) {
         if (empty($data['completename'])) {
            $data['completename'] = __("None");
         }
         $datas['datas'][$data['completename']] = $data['nb'];
      }

      return $datas;
   }

   function reportHbarTopapplicant($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportHbarTopapplicant'] = ['dateinterval', 'limit', 'type'];

      $tab = [];
      $datas = [];

      $sql_create = "SELECT DISTINCT gt.groups_id,
                  COUNT(DISTINCT glpi_tickets.id) AS nb,
                  g.completename
               FROM glpi_tickets
               {$this->sql_join_gt}
               {$this->sql_join_g}
               WHERE {$this->sql_date_create}
                  AND {$this->sql_type}
                  AND glpi_tickets.entities_id IN ({$this->where_entities})
                  AND glpi_tickets.is_deleted = '0'
               GROUP BY g.completename
               ORDER BY nb DESC
               LIMIT 0, ";
      $sql_create .= (isset($_SESSION['mreporting_values']['glpilist_limit']))
                     ? $_SESSION['mreporting_values']['glpilist_limit'] : 20;

      $res = $DB->query($sql_create);
      while ($data = $DB->fetchAssoc($res)) {
         if (empty($data['completename'])) {
            $data['completename'] = __("None");
         }
         $datas['datas'][$data['completename']] = $data['nb'];
      }

      return $datas;
   }

   function reportVstackbarGroupChange($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportVstackbarGroupChange']
         = ['dateinterval', 'userassign', 'category',
            'multiplegrouprequest', 'multiplegroupassign'];

      $datas = [];

      $query = "SELECT COUNT(DISTINCT ticc.id) as nb_ticket,
            ticc.nb_add_group - 1 as nb_add_group
         FROM (
            SELECT
               glpi_tickets.id,
               COUNT(glpi_tickets.id) as nb_add_group
            FROM glpi_tickets
            LEFT JOIN glpi_logs logs_tic
               ON  logs_tic.itemtype = 'Ticket'
               AND logs_tic.items_id = glpi_tickets.id
               AND logs_tic.itemtype_link = 'Group'
               AND logs_tic.linked_action = 15 /* add action */
            {$this->sql_join_cat}
            {$this->sql_join_tu}
            {$this->sql_join_gt}
            {$this->sql_join_gtr}
            WHERE {$this->sql_date_create}
               AND glpi_tickets.entities_id IN ({$this->where_entities})
               AND glpi_tickets.is_deleted = '0'
               AND {$this->sql_type}
               AND {$this->sql_group_assign}
               AND {$this->sql_group_request}
               AND {$this->sql_user_assign}
               AND {$this->sql_itilcat}
            GROUP BY glpi_tickets.id
            HAVING nb_add_group > 0
         ) as ticc
         GROUP BY nb_add_group";

      $result = $DB->query($query);

      $datas['datas'] = [];
      while ($ticket = $DB->fetchAssoc($result)) {
         $datas['labels2'][$ticket['nb_add_group']] = $ticket['nb_add_group'];
         $datas['datas'][__("Number of tickets")][$ticket['nb_add_group']] = $ticket['nb_ticket'];
      }

      return $datas;
   }


   function reportLineActiontimeVsSolvedelay($config = []) {
      global $DB;

      $_SESSION['mreporting_selector']['reportLineActiontimeVsSolvedelay'] =
         ['dateinterval', 'period', 'multiplegrouprequest',
          'userassign', 'category', 'multiplegroupassign'];

      $query = "SELECT
         DATE_FORMAT(glpi_tickets.date, '{$this->period_sort}')  as period,
         DATE_FORMAT(glpi_tickets.date, '{$this->period_label}') as period_name,
         ROUND(AVG(actiontime_vs_solvedelay.time_percent), 1) as time_percent
       FROM glpi_tickets
         LEFT JOIN (
            SELECT
               glpi_tickets.id AS tickets_id,
               (SUM(tt.actiontime) * 100) / glpi_tickets.solve_delay_stat as time_percent
            FROM glpi_tickets
            {$this->sql_join_tt}
            {$this->sql_join_tu}
            {$this->sql_join_gt}
            {$this->sql_join_gtr}
            WHERE glpi_tickets.solve_delay_stat > 0
               AND tt.actiontime IS NOT NULL
               AND glpi_tickets.entities_id IN ({$this->where_entities})
               AND glpi_tickets.is_deleted = '0'
               AND {$this->sql_date_create}
               AND {$this->sql_type}
               AND {$this->sql_group_assign}
               AND {$this->sql_group_request}
               AND {$this->sql_user_assign}
               AND {$this->sql_itilcat}
            GROUP BY glpi_tickets.id
         ) AS actiontime_vs_solvedelay
            ON actiontime_vs_solvedelay.tickets_id = glpi_tickets.id
         WHERE {$this->sql_date_create}
         GROUP BY period
         ORDER BY period";
      $data = [];
      foreach ($DB->request($query) as $result) {
         $data['datas'][$result['period_name']] = floatval($result['time_percent']);
         $data['labels2'][$result['period_name']] = $result['period_name'];
      }

      return $data;
   }



   function reportGlineNbTicketBySla($config = []) {
      global $DB;

      $area = false;
      $datas = [];

      $_SESSION['mreporting_selector']['reportGlineNbTicketBySla']
         = ['dateinterval', 'period', 'allSlasWithTicket'];

      if (isset($_SESSION['mreporting_values']['slas'])
          && !empty($_SESSION['mreporting_values']['slas'])) {
         //get dates used in this period
         $query_date = "SELECT DISTINCT DATE_FORMAT(`glpi_tickets`.`date`, '{$this->period_sort}') AS period,
            DATE_FORMAT(`glpi_tickets`.`date`, '{$this->period_label}') AS period_name
         FROM `glpi_tickets`
         INNER JOIN `glpi_slas`
            ON `glpi_tickets`.slas_id_ttr = `glpi_slas`.id
         WHERE {$this->sql_date_create}
            AND `glpi_tickets`.status IN (" . implode(
                  ',',
                  array_merge(Ticket::getSolvedStatusArray(), Ticket::getClosedStatusArray())
               ) . ")
            AND `glpi_tickets`.`entities_id` IN (" . $this->where_entities . ")
            AND `glpi_tickets`.`is_deleted` = '0'
            AND `glpi_slas`.id IN (".implode(',', $_SESSION['mreporting_values']['slas']).")
         ORDER BY `glpi_tickets`.`date` ASC";
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
            DATE_FORMAT(`glpi_tickets`.`date`, '{$this->period_sort}') AS period,
            DATE_FORMAT(`glpi_tickets`.`date`, '{$this->period_label}') AS period_name,
            count(`glpi_tickets`.id) AS nb,
            `glpi_slas`.name,
            {$this->sql_select_sla}
         FROM `glpi_tickets`
         INNER JOIN `glpi_slas`
            ON `glpi_tickets`.slas_id_ttr = `glpi_slas`.id
         WHERE {$this->sql_date_create}
         AND `glpi_tickets`.status IN (" . implode(
               ',',
               array_merge(Ticket::getSolvedStatusArray(), Ticket::getClosedStatusArray())
            ) . ")
         AND `glpi_tickets`.entities_id IN (" . $this->where_entities . ")
         AND `glpi_tickets`.is_deleted = '0'";
         if (isset($_SESSION['mreporting_values']['slas'])) {
            $query .= " AND `glpi_slas`.id IN (".implode(',', $_SESSION['mreporting_values']['slas']).") ";
         }
         $query .= "GROUP BY `glpi_slas`.name, period, respected_sla";

         $result = $DB->query($query);
         while ($data = $DB->fetchAssoc($result)) {
            $datas['labels2'][$data['period']] = $data['period_name'];
            if ($data['respected_sla'] == 'ok') {
               $value = $this->lcl_slaok;
            } else {
               $value = $this->lcl_slako;
            }
            $datas['datas'][$data['name'] . ' ' . $value][$data['period']] = $data['nb'];
         }

         if (isset($datas['datas'])) {
            foreach ($datas['datas'] as &$data) {
               $data = $data + array_fill_keys($tmp_date, 0);
            }
         }
      }

      return $datas;
   }


   public function reportHgbarRespectedSlasByTopCategory($config = []) {
      global $DB;

      $area = false;

      $_SESSION['mreporting_selector']['reportHgbarRespectedSlasByTopCategory']
         = ['dateinterval', 'limit', 'categories'];

      $datas = [];
      $categories = [];

      if (isset($_POST['categories']) && $_POST['categories'] > 0) {
         $category = $_POST['categories'];
      } else {
         $category = false;
      }

      $category_limit = isset($_POST['glpilist_limit']) ? $_POST['glpilist_limit'] : 10;

      $_SESSION['glpilist_limit'] = $category_limit;

      if (!$category) {
         $query_categories = "SELECT
            count(`glpi_tickets`.id) as nb,
            `glpi_itilcategories`.id
         FROM `glpi_tickets`
         INNER JOIN `glpi_slas`
            ON `glpi_tickets`.slas_id_ttr = `glpi_slas`.id
         INNER JOIN `glpi_itilcategories`
            ON `glpi_tickets`.itilcategories_id = `glpi_itilcategories`.id
         WHERE " . $this->sql_date_create . "
         AND `glpi_tickets`.entities_id IN (" . $this->where_entities . ")
         AND `glpi_tickets`.is_deleted = '0'
         GROUP BY `glpi_itilcategories`.id
         ORDER BY nb DESC
         LIMIT " . $category_limit;

         $result_categories = $DB->query($query_categories);
         while ($data = $DB->fetchAssoc($result_categories)) {
            $categories[] = $data['id'];
         }
      }

      $query = "SELECT COUNT(`glpi_tickets`.id) as nb,
            {$this->sql_select_sla},
            `glpi_itilcategories`.id,
            `glpi_itilcategories`.name
         FROM `glpi_tickets`
         INNER JOIN `glpi_slas`
            ON `glpi_tickets`.slas_id_ttr = `glpi_slas`.id
         INNER JOIN `glpi_itilcategories`
            ON `glpi_tickets`.itilcategories_id = `glpi_itilcategories`.id
         WHERE " . $this->sql_date_create . "
         AND `glpi_tickets`.entities_id IN (" . $this->where_entities . ")
         AND `glpi_tickets`.is_deleted = '0'";
      if ($category) {
         $query .= " AND `glpi_itilcategories`.id = " . $category;
      } else if (!empty($categories)) {
         $query .= " AND `glpi_itilcategories`.id IN (" . implode(',', $categories) . ")";
      }
         $query .= " GROUP BY respected_sla, `glpi_itilcategories`.id
         ORDER BY nb DESC";

      $result = $DB->query($query);
      while ($data = $DB->fetchAssoc($result)) {
         $value = ($data['respected_sla'] == 'ok') ? $this->lcl_slaok
                                                   : $this->lcl_slako;
         $datas['datas'][$data['name']][$value] = $data['nb'];
      }
      $datas['labels2'] = [$this->lcl_slaok => $this->lcl_slaok,
                           $this->lcl_slako => $this->lcl_slako];

      if (isset($datas['datas'])) {
         foreach ($datas['datas'] as &$data) {
            $data = $data + array_fill_keys($datas['labels2'], 0);
         }
      }

      return $datas;
   }

   public function reportHgbarRespectedSlasByTechnician($config = []) {
      global $DB;

      $area = false;
      $datas = [];

      $_SESSION['mreporting_selector']['reportHgbarRespectedSlasByTechnician'] = ['dateinterval'];

      $query = "SELECT
            CONCAT(`glpi_users`.firstname, ' ', `glpi_users`.realname) as fullname,
            `glpi_users`.id,
            COUNT(`glpi_tickets`.id) as nb,
            {$this->sql_select_sla}
         FROM `glpi_tickets`
         INNER JOIN `glpi_slas`
            ON `glpi_tickets`.slas_id_ttr = `glpi_slas`.id
         INNER JOIN `glpi_tickets_users`
            ON `glpi_tickets_users`.tickets_id = `glpi_tickets`.id
            AND `glpi_tickets_users`.type = " . Ticket_User::ASSIGN . "
         INNER JOIN `glpi_users`
            ON `glpi_users`.id = `glpi_tickets_users`.users_id
         WHERE " . $this->sql_date_create . "
         AND `glpi_tickets`.entities_id IN ({$this->where_entities})
         AND `glpi_tickets`.is_deleted = '0'
         GROUP BY respected_sla, `glpi_users`.id
         ORDER BY nb DESC";

      $result = $DB->query($query);
      while ($data = $DB->fetchAssoc($result)) {
         if ($data['respected_sla'] == 'ok') {
            $value = $this->lcl_slaok;
         } else {
            $value = $this->lcl_slako;
         }
         $datas['datas'][$data['fullname']][$value] = $data['nb'];
      }
      $datas['labels2'] = [$this->lcl_slaok => $this->lcl_slaok,
                           $this->lcl_slako => $this->lcl_slako];

      if (isset($datas['datas'])) {
         foreach ($datas['datas'] as &$data) {
            $data = $data + array_fill_keys($datas['labels2'], 0);
         }
      }

      return $datas;
   }

   function fillStatusMissingValues($tab, $labels2 = []) {
      $datas = [];
      foreach ($tab as $name => $data) {
         foreach ($this->status as $current_status) {
            if (!isset($_SESSION['mreporting_values']['status_'.$current_status])
               || ($_SESSION['mreporting_values']['status_'.$current_status] == '1')) {

               $status_name = Ticket::getStatus($current_status);
               if (isset($data[$status_name])) {
                  $datas['datas'][$status_name][] = $data[$status_name];
               } else {
                  $datas['datas'][$status_name][] = 0;
               }
            }
         }
         if (empty($labels2)) {
            $datas['labels2'][] = $name;
         } else {
            $datas['labels2'][] = $labels2[$name];
         }
      }
      return $datas;
   }

   static function selectorBacklogstates() {
      global $LANG;

      echo "<br /><b>".$LANG['plugin_mreporting']['Helpdeskplus']['backlogstatus']." : </b><br />";

      // Opened
      echo '<label>';
      echo '<input type="hidden" name="show_new" value="0" /> ';
      echo '<input type="checkbox" name="show_new" value="1"';
      echo (!isset($_SESSION['mreporting_values']['show_new'])
            || ($_SESSION['mreporting_values']['show_new'] == '1')) ? ' checked="checked"' : '';
      echo ' /> ';
      echo $LANG['plugin_mreporting']['Helpdeskplus']['opened'];
      echo '</label>';

      // Solved
      echo '<label>';
      echo '<input type="hidden" name="show_solved" value="0" /> ';
      echo '<input type="checkbox" name="show_solved" value="1"';
      echo (!isset($_SESSION['mreporting_values']['show_solved'])
            || ($_SESSION['mreporting_values']['show_solved'] == '1')) ? ' checked="checked"' : '';
      echo ' /> ';
      echo _x('status', 'Solved');
      echo '</label>';

      echo "<br />";

      // Backlog
      echo '<label>';
      echo '<input type="hidden" name="show_backlog" value="0" /> ';
      echo '<input type="checkbox" name="show_backlog" value="1"';
      echo (!isset($_SESSION['mreporting_values']['show_backlog'])
            || ($_SESSION['mreporting_values']['show_backlog'] == '1')) ? ' checked="checked"' : '';
      echo ' /> ';
      echo $LANG['plugin_mreporting']['Helpdeskplus']['backlogs'];
      echo '</label>';

      // Closed
      echo '<label>';
      echo '<input type="hidden" name="show_closed" value="0" /> ';
      echo '<input type="checkbox" name="show_closed" value="1"';
      echo (isset($_SESSION['mreporting_values']['show_closed'])
            && ($_SESSION['mreporting_values']['show_closed'] == '1')) ? ' checked="checked"' : '';
      echo ' /> ';
      echo _x('status', 'Closed');
      echo '</label>';
   }


   function reportVstackbarRespectedSlasByGroup($config = []) {
      global $DB, $LANG;

      $datas = [];

      $_SESSION['mreporting_selector']['reportVstackbarRespectedSlasByGroup']
         = ['dateinterval', 'allSlasWithTicket'];

      $this->sql_date_create = PluginMreportingCommon::getSQLDate("`glpi_tickets`.date",
                                                                  $config['delay'],
                                                                  $config['randname']);

      if (isset($_SESSION['mreporting_values']['slas'])
          && !empty($_SESSION['mreporting_values']['slas'])) {

         $query = "SELECT COUNT(`glpi_tickets`.id) AS nb,
               `glpi_groups_tickets`.groups_id as groups_id,
               `glpi_slas`.name,
               {$this->sql_select_sla}
            FROM `glpi_tickets`
            INNER JOIN `glpi_groups_tickets`
               ON `glpi_groups_tickets`.tickets_id = `glpi_tickets`.id
               AND `glpi_groups_tickets`.type = ".CommonITILActor::ASSIGN."
            INNER JOIN `glpi_slas`
               ON `glpi_tickets`.slas_id_ttr = `glpi_slas`.id
            WHERE {$this->sql_date_create}
               AND `glpi_tickets`.status IN (" . implode(
                           ',',
                           array_merge(Ticket::getSolvedStatusArray(), Ticket::getClosedStatusArray())
                     ) . ")
               AND `glpi_tickets`.entities_id IN ({$this->where_entities})
               AND `glpi_tickets`.is_deleted = '0'
               AND `glpi_slas`.id IN (".implode(',', $_SESSION['mreporting_values']['slas']).")
            GROUP BY `glpi_groups_tickets`.groups_id, respected_sla;";
         $result = $DB->query($query);

         while ($data = $DB->fetchAssoc($result)) {
            $gp = new Group();
            $gp->getFromDB($data['groups_id']);

            $datas['labels2'][$gp->fields['name']] = $gp->fields['name'];

            if ($data['respected_sla'] == 'ok') {
               $datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['slaobserved']][$gp->fields['name']] = $data['nb'];
            } else {
               $datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['slanotobserved']][$gp->fields['name']] = $data['nb'];
            }

         }

         // Ajout des '0' manquants :
         $gp = new Group();
         $gp_found = $gp->find([], "name"); //Tri précose qui n'est pas utile

         foreach ($gp_found as $group) {
             $group_name = $group['name'];
            if (!isset($datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['slaobserved']][$group_name])) {
               $datas['labels2'][$group_name] = $group_name;
               $datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['slaobserved']][$group_name] = 0;
            }
            if (!isset($datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['slanotobserved']][$group_name])) {
               $datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['slanotobserved']][$group_name] = 0;
            }
         }

         //Flip array to have observed SLA first
         arsort($datas['datas']);

         //Array alphabetic sort
         //For PNG mode, it is important to sort by date on each item
         ksort($datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['slaobserved']]);
         ksort($datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['slanotobserved']]);

         //For SVG mode, labels2 sort is ok
         asort($datas['labels2']);

         $datas['unit'] = '%';
      }

      return $datas;
   }

   function reportVstackbarNbTicketBySla($config = []) {
      global $DB, $LANG;

      $area = false;

      $_SESSION['mreporting_selector']['reportVstackbarNbTicketBySla'] = ['dateinterval', 'allSlasWithTicket'];

      $datas = [];
      $tmp_datas = [];

      $this->sql_date_create = PluginMreportingCommon::getSQLDate("`glpi_tickets`.date",
                                                                  $config['delay'],
                                                                  $config['randname']);

      if (isset($_SESSION['mreporting_values']['slas'])
          && !empty($_SESSION['mreporting_values']['slas'])) {

         $query = "SELECT count(`glpi_tickets`.id) AS nb, `glpi_slas`.name,
                     {$this->sql_select_sla}
                     FROM `glpi_tickets`
                     INNER JOIN `glpi_slas`
                        ON `glpi_tickets`.slas_id_ttr = `glpi_slas`.id
                     WHERE {$this->sql_date_create}
                     AND `glpi_tickets`.status IN (" . implode(',',
                              array_merge(Ticket::getSolvedStatusArray(), Ticket::getClosedStatusArray())
                           ) . ")
                     AND `glpi_tickets`.entities_id IN ({$this->where_entities})
                     AND `glpi_tickets`.is_deleted = '0'
                     AND `glpi_slas`.id IN (".implode(',', $_SESSION['mreporting_values']['slas']).")
                     GROUP BY `glpi_slas`.name, respected_sla;";

         $result = $DB->query($query);
         while ($data = $DB->fetchAssoc($result)) {
            $tmp_datas[$data['name']][$data['respected_sla']] = $data['nb'];
         }

         foreach ($tmp_datas as $key => $value) {
            $datas['labels2'][$key] = $key;
            $datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['slaobserved']][$key]
               = !empty($value['ok']) ? $value['ok'] : 0;
            $datas['datas'][$LANG['plugin_mreporting']['Helpdeskplus']['slanotobserved']][$key]
               = !empty($value['nok']) ? $value['nok'] : 0;
         }
      }

      return $datas;
   }


   private function _getPeriod() {
      if (isset($_REQUEST['period']) && !empty($_REQUEST['period'])) {
         switch ($_REQUEST['period']) {
            case 'day':
               $this->_period_sort = '%y%m%d';
               $this->_period_label = '%d %b %Y';
               break;
            case 'week':
               $this->_period_sort = '%y%u';
               $this->_period_label = 'S-%u %Y';
               break;
            case 'month':
               $this->_period_sort = '%y%m';
               $this->_period_label = '%b %Y';
               break;
            case 'year':
               $this->_period_sort = '%Y';
               $this->_period_label = '%Y';
               break;
            default :
               $this->_period_sort = '%y%m';
               $this->_period_label = '%b %Y';
               break;
         }
      } else {
         $this->_period_sort = '%y%m';
         $this->_period_label = '%b %Y';
      }
   }
}
