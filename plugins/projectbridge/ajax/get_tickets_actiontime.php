<?php

require('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['ticket_ids']) && is_array($_POST['ticket_ids'])
) {
    $onlypublicTasks = PluginProjectbridgeConfig::getConfValueByName('CountOnlyPublicTasks');

    $tickets_actiontime = [];

    foreach ($_POST['ticket_ids'] as $ticketID) {
        $whereConditionsArray = [];
        $totalActiontime = 0;
        $whereConditionsArray = ['tickets_id' => $ticketID];
        //if ($onlypublicTasks) {
        if ( !Session::haveRight("task", CommonITILTask::SEEPRIVATE) || $onlypublicTasks) {    
            $whereConditionsArray['is_private'] = 0;
        }

        $iterator = $DB->request([
            'SELECT' => new QueryExpression('SUM(' . TicketTask::getTable() . '.actiontime) AS duration'),
            'FROM' => TicketTask::getTable(),
            'WHERE' => $whereConditionsArray
        ]);
        if ($row = $iterator->next()) {
            $totalActiontime = (int) $row['duration'];
        }
        if (!empty($totalActiontime)) {
            $totalActiontime = round($totalActiontime / 3600 * 100, 1) / 100;
        }

        $tickets_actiontime[$ticketID]['totalDuration'] = $totalActiontime;

        // récupération durée privée
        $privateActiontime = 0;
        if (Session::haveRight("task", CommonITILTask::SEEPRIVATE) && !$onlypublicTasks) {
            $whereConditionsArray['is_private'] = 1;
            $iterator = $DB->request([
                'SELECT' => new QueryExpression('SUM(' . TicketTask::getTable() . '.actiontime) AS duration'),
                'FROM' => TicketTask::getTable(),
                'WHERE' => $whereConditionsArray
            ]);
            if ($row = $iterator->next()) {
                $privateActiontime = (int) $row['duration'];
            }
            if (!empty($privateActiontime)) {
                $privateActiontime = round($privateActiontime / 3600 * 100, 1) / 100;
            }
        }

        $tickets_actiontime[$ticketID]['privateDuration'] = $privateActiontime;
    }




    echo json_encode($tickets_actiontime);
}
