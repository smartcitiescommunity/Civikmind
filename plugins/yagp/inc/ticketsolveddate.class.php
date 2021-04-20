<?php

class PluginYagpTicketsolveddate extends CommonDBTM {
   static function getTypeName($nb = 0) {
      return __('YagpTicketSolvedDate', 'yagp');
   }

   static function cronInfo($name) {
      switch ($name) {
         case 'changeDate':
            return ['description' => __('Change date', 'yagp'),
                       'parameter'=>__('Number of tickets', 'yagp')];
      }
      return [];
   }

   public static function cronChangeDate($task) {
      global $DB;

      $config= PluginYagpConfig::getConfig();
      if ($config->fields['ticketsolveddate']) {
         $message="";
         $ticket=new Ticket();
         if ($task->fields['param']>0) {
            $limit=" LIMIT ".$task->fields['param'];
         } else {
            $limit="";
         }

         /*$sub_task=new QuerySubQuery([
            'SELECT'=>[
               'tickets_id',
               'MAX'=>'end AS last_task_end',
            ],
            'FROM'=>'glpi_tickettasks',
            'WHERE'=>[
               [
                  'NOT' => ['end' => null],
               ],
            ],
            'GROUPBY'=>'tickets_id',
            'AS task'
         ]);
         $sub_taskstart=new QuerySubQuery([
            'SELECT'=>[
               'tickets_id',
               'MIN'=>'begin AS first_task_begin',
            ],
            'FROM'=>'glpi_tickettasks',
            'WHERE'=>[
               [
                  'NOT' => ['begin' => null],
               ],
            ],
            'GROUPBY'=>'tickets_id',
            'AS taskstart'
         ]);
         $query=[
            'SELECT'=>[
               'id',
               'date',
               'solvedate',
            ],
            'FROM'=>'glpi_tickets',
            'INNER JOIN'=>[
               $sub_task=>[
                  'FKEY'=>[
                     'glpi_tickets'=>'id',
                     'task'=>'tickets_id',
                  ]
               ],
               $sub_taskstart=>[
                  'FKEY'=>[
                     'glpi_tickets'=>'id',
                     'taskstart'=>'tickets_id',
                  ]
               ]
            ],
            'WHERE'=>[
               'status'=>['>=','5'],
               'is_deleted'=>0,
               'solvedate'=>['!=','last_task_end']
            ]
         ];*/
         $query="SELECT id,date,solvedate,taskstart.first_task_begin,task.last_task_end 
         	FROM glpi_tickets as ticket
	         INNER JOIN (
	         	select tickets_id,CASE 
	         		WHEN max(end)>max(ADDDATE(date,INTERVAL actiontime SECOND)) THEN max(end)
	         		ELSE max(ADDDATE(date,INTERVAL actiontime SECOND))
	         		END as last_task_end
	         	from glpi_tickettasks
	         	group by tickets_id) as task
	         ON ticket.id=task.tickets_id
	         LEFT JOIN (
	         	SELECT tickets_id,min(begin)AS first_task_begin
	         	FROM glpi_tickettasks
	            WHERE begin IS NOT NULL
	            GROUP BY tickets_id) AS taskstart
	         ON ticket.id=taskstart.tickets_id
	         WHERE ticket.status=5
	         AND ticket.solvedate<>task.last_task_end
	         AND ticket.is_deleted=0".$limit;

         foreach ($DB->request($query) as $id => $row) {
         	if (!is_null($row["first_task_begin"])){
	            if ($row["date"]>$row["first_task_begin"]) {
	               $newdate = strtotime ( '-1 hour', strtotime ( $row["first_task_begin"] ) );
	               $newdate = date ( 'Y-m-d H:i', $newdate );
	               $ticket->update(['id' => $row["id"],'date' => $newdate]);
	               $task->addVolume(1);
	               $task->log("Updated Ticket open date id: ".$row["id"]);
	            }
         	}
            $ticket->update(['id' => $row["id"],'solvedate' => $row["last_task_end"]]);
            $task->addVolume(1);
            $task->log("<a href='".Ticket::getFormURLWithID($row["id"])."'>Updated Ticket id: ".$row["id"]."</a>");
         }
         return true;
      } else {
         return false;
      }
   }
}