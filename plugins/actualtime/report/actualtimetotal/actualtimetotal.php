<?php

$USEDBREPLICATE= 1;
$DBCONNECTION_REQUIRED= 0;

include ("../../../../inc/includes.php");

$report= new PluginReportsAutoReport(__('ActualTimeTotal'));
//Filtro fecha
new PluginReportsDateIntervalCriteria(
   $report,
   'glpi_tickets.closedate',
   __("Close date")
);
//Filtro usuario
new PluginReportsDropdownCriteria(
   $report,
   "glpi_tickets_users.users_id",
   "glpi_users",
   __("Requester")
);

$report->displayCriteriasForm();
$report->setColumns([
   new PluginReportsColumnLink(
      'tickets_id',
      __('Ticket'),
      'Ticket',
      [
         'with_navigate' => true
      ]
   ),
   new PluginReportsColumnTimestamp(
      'duration',
      __("Total duration")
   ),
   new PluginReportsColumnTimestamp(
      'totalduration',
      "ActualTime - ".__("Total duration")
   ),
   new PluginReportsColumnTimestamp(
      'diff',
      __("Duration Diff", "actiontime")
   ),
   new PluginReportsColumn(
      'diffpercent',
      __("Duration Diff", "actiontime")." (%)"
   )
]);
$query="SELECT glpi_tickets.id AS tickets_id,
   sum(glpi_tickettasks.actiontime) AS duration,
   sum(actual_actiontime) AS totalduration,
   (sum(glpi_tickettasks.actiontime) - sum(actual_actiontime)) AS diff,
   concat(round(((sum(glpi_tickettasks.actiontime) - sum(actual_actiontime)) / sum(actual_actiontime) * 100 ),2),'%') AS diffpercent
FROM glpi_plugin_actualtime_tasks
   RIGHT JOIN glpi_tickettasks ON glpi_tickettasks.id = glpi_plugin_actualtime_tasks.tasks_id
   INNER JOIN glpi_tickets ON glpi_tickets.id = glpi_tickettasks.tickets_id
   INNER JOIN glpi_tickets_users ON glpi_tickets_users.tickets_id = glpi_tickets.id
WHERE status = 6 AND glpi_tickets_users.type = 1
";
$query .= $report->addSqlCriteriasRestriction();
$query .= "
GROUP BY glpi_tickets.id";
$report->setSqlRequest($query);
$report->execute();
