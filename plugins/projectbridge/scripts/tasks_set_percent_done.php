<?php

chdir(__DIR__);
require_once('../../../inc/includes.php');
require_once('../hook.php');

$ticket_task = new TicketTask();
$ticket_tasks = $ticket_task->find("TRUE AND actiontime > 0");

echo 'Trouvé ' . count($ticket_tasks) . ' tâches avec du temps' . "\n";

foreach ($ticket_tasks as $ticket_task_data) {
    echo 'Re-calcul pour la tâche liée au ticket ' . $ticket_task_data['tickets_id'] . "\n";

    // use the existing time to force an update of the percent_done in the tasks linked to the tickets
    PluginProjectbridgeTask::updateProgressPercent($ticket_task_data['tickets_id']);
}
