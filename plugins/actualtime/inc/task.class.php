<?php
if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

include_once('config.class.php');
/**
*
*/
class PluginActualtimeTask extends CommonDBTM{

   public static $rightname = 'task';
   const AUTO=1;
   const WEB=2;
   const ANDROID=3;

   static function getTypeName($nb = 0) {
      return __('ActualTime', 'Actualtime');
   }

   static public function rawSearchOptionsToAdd(){

      $tab[]=[
         'id'=>'7000',
         'table'=>self::getTable(),
         'field'=>'actual_actiontime',
         'name'=>__('Total duration'),
         'datatype' => 'specific',
         'joinparams'=>[
            'beforejoin'=>[
               'table'=>'glpi_tickettasks',
               'joinparams' => [
                  'jointype' => 'child'
               ]
            ],
            'jointype' => 'child',
            'linkfield'=>'tasks_id'
         ],
         'type'=>'total'
      ];
      $tab[]=[
         'id'=>'7001',
         'table'=>self::getTable(),
         'field'=>'actual_actiontime',
         'name'=>__("Duration Diff", "actiontime"),
         'datatype' => 'specific',
         'joinparams'=>[
            'beforejoin'=>[
               'table'=>'glpi_tickettasks',
               'joinparams' => [
                  'jointype' => 'child'
               ]
            ],
            'jointype' => 'child',
            'linkfield'=>'tasks_id'
         ],
         'type'=>'diff'
      ];
      $tab[]=[
         'id'=>'7002',
         'table'=>self::getTable(),
         'field'=>'actual_actiontime',
         'name'=>__("Duration Diff", "actiontime")." (%)",
         'datatype' => 'specific',
         'joinparams'=>[
            'beforejoin'=>[
               'table'=>'glpi_tickettasks',
               'joinparams' => [
                  'jointype' => 'child'
               ]
            ],
            'jointype' => 'child',
            'linkfield'=>'tasks_id'
         ],
         'type'=>'diff%'
      ];

      return $tab;
   }

   static function getSpecificValueToDisplay($field, $values, array $options = []) {
      global $DB;
      if (!is_array($values)) {
         $values = [$field => $values];
      }

      switch ($field) {
         case 'actual_actiontime' :
            $actual_totaltime=0;
            $ticket=new Ticket();
            $ticket->getFromDB($options['raw_data']['id']);
            $total_time=$ticket->getField('actiontime');
            $query=[
               'SELECT'=>[
                  'glpi_tickettasks.id',
               ],
               'FROM'=>'glpi_tickettasks',
               'WHERE'=>[
                  'tickets_id'=>$options['raw_data']['id'],
               ]
            ];
            foreach ($DB->request($query) as $id => $row) {
               $actual_totaltime+=self::totalEndTime($row['id']);
            }
            switch ($options['searchopt']['type']) {
               case 'diff':
                  $diff=$total_time-$actual_totaltime;
                  return HTML::timestampToString($diff);
                  break;

               case 'diff%':
                  if ($total_time==0) {
                     $diffpercent=0;
                  } else {
                     $diffpercent=100*($total_time-$actual_totaltime)/$total_time;
                  }
                  return round($diffpercent, 2)."%";
                  break;
               
               default:
                  return HTML::timestampToString($actual_totaltime);
                  break;
            }
         break;
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   static public function postForm($params) {
      global $CFG_GLPI;

      $item = $params['item'];

      switch ($item->getType()) {
         case 'TicketTask':
            if ($item->getID()) {

               $config = new PluginActualtimeConfig;

               $task_id = $item->getID();
               $rand = mt_rand();
               $buttons = (self::checkTech($task_id) && $item->can($task_id, UPDATE));
               $time = self::totalEndTime($task_id);
               $text_restart = __('Restart', 'actualtime');
               $text_pause = __('Pause', 'actualtime');
               $html = '';
               $script = <<<JAVASCRIPT
$(document).ready(function() {
JAVASCRIPT;

               // Only task user
               $timercolor = 'black';
               if ($buttons) {

                  $value1 = __('Start');
                  $action1 = '';
                  $color1 = 'gray';
                  $disabled1 = 'disabled';
                  $action2 = '';
                  $color2 = 'gray';
                  $disabled2 = 'disabled';

                  if ($item->getField('state')==1) {

                     if (self::checkTimerActive($task_id)) {

                        $value1 = $text_pause;
                        $action1 = 'pause';
                        $color1 = 'orange';
                        $disabled1 = '';
                        $action2 = 'end';
                        $color2 = 'red';
                        $disabled2 = '';
                        $timercolor = 'red';

                     } else {

                        if ($time > 0) {
                           $value1 = $text_restart;
                           $action2 = 'end';
                           $color2 = 'red';
                           $disabled2 = '';
                        }

                        $action1 = 'start';
                        $color1 = 'green';
                        $disabled1 = '';

                     }

                  }

                  $html = "<tr class='tab_bg_2'>";
                  $html .= "<td colspan='2'></td>";
                  $html .= "<td colspan='2'>";
                  // Objects of the same task have the same id beginning
                  // as they all should be changed on actions in case multiple
                  // windows of the same task is opened (list of tasks + modal)
                  $html .= "<div><input type='button' id='actualtime_button_{$task_id}_1_{$rand}' action='$action1' value='$value1' class='x-button x-button-main' style='background-color:$color1;color:white' $disabled1 data-track-changes=''></div>";
                  $html .= "<div><input type='button' id='actualtime_button_{$task_id}_2_{$rand}' action='$action2' value='".__('End')."' class='x-button x-button-main' style='background-color:$color2;color:white' $disabled2 data-track-changes=''></div>";
                  $html .= "</td></tr>";

                  // Only task user have buttons
                  $script .= <<<JAVASCRIPT
   $("#actualtime_button_{$task_id}_1_{$rand}").click(function(event) {
      window.actualTime.pressedButton($task_id, $(this).attr('action'));
   });

   $("#actualtime_button_{$task_id}_2_{$rand}").click(function(event) {
      window.actualTime.pressedButton($task_id, $(this).attr('action'));
   });

JAVASCRIPT;

               }

               // Task user (always) or Standard interface (always)
               // or Helpdesk inteface (only if config allows)
               if ($buttons
                  || (Session::getCurrentInterface() == "central")
                  || $config->showInHelpdesk()) {

                  $html .= "<tr class='tab_bg_2'>";
                  $html .= "<td class='center'>" . __("Start date") . "</td><td class='center'>" . __("Partial actual duration", 'actualtime') . "</td>";
                  $html .= "<td>" . __('Actual Duration', 'actualtime') . " </td><td id='actualtime_timer_{$task_id}_{$rand}' style='color:{$timercolor}'></td>";
                  $html .= "</tr>";
                  $html .= "<tr id='actualtime_segment_{$task_id}_{$rand}'>";
                  $html .= self::getSegment($item->getID());
                  $html .= "</tr>";

                  echo $html;

                  // Finally, fill the actual total time in all timers
                  $script .= <<<JAVASCRIPT

   window.actualTime.fillCurrentTime($task_id, $time);

});
JAVASCRIPT;
                  echo Html::scriptBlock($script);

               }
            }else{
               //echo Html::scriptBlock('');
               $div= "<div id='actualtime_autostart' class='fa-label'><i class='fas fa-stopwatch fa-fw' title='".__('Autostart')."'></i><span class='switch pager_controls'><label for='autostart' title='".__('Autostart')."'><input type='hidden' name='autostart' value='0'><input type='checkbox' id='autostart' name='autostart' value='1'><span class='lever'></span></label></span></div>";
               $script=<<<JAVASCRIPT
               $(document).ready(function() {
                  if($("#actualtime_autostart").length==0){
                     $("div.ajax_box #mainformtable tr.tab_bg_1 td").last().append("{$div}");
                  }
               });
JAVASCRIPT;
               echo Html::scriptBlock($script);
            }
            break;
      }

   }

   static function checkTech($task_id) {
      global $DB;

      $query=[
         'FROM'=>'glpi_tickettasks',
         'WHERE'=>[
            'id'=>$task_id,
            'users_id_tech'=>Session::getLoginUserID(),
         ]
      ];
      $req=$DB->request($query);
      if ($row=$req->next()) {
         return true;
      } else {
         return false;
      }
   }

   static function checkTimerActive($task_id) {
      global $DB;

      $query=[
         'FROM'=>self::getTable(),
         'WHERE'=>[
            'tasks_id'=>$task_id,
            [
               'NOT' => ['actual_begin' => null],
            ],
            'actual_end'=>null,
         ]
      ];
      $req=$DB->request($query);
      if ($row=$req->next()) {
         return true;
      } else {
         return false;
      }
   }

   static function totalEndTime($task_id) {
      global $DB;

      $query=[
         'FROM'=>self::getTable(),
         'WHERE'=>[
            'tasks_id'=>$task_id,
            [
               'NOT' => ['actual_begin' => null],
            ],
            [
               'NOT' => ['actual_end' => null],
            ],
         ]
      ];

      $seconds=0;
      foreach ($DB->request($query) as $id => $row) {
         $seconds+=$row['actual_actiontime'];
      }

      $querytime=[
         'FROM'=>self::getTable(),
         'WHERE'=>[
            'tasks_id'=>$task_id,
            [
               'NOT' => ['actual_begin' => null],
            ],
            'actual_end'=>null,
         ]
      ];

      $req=$DB->request($querytime);
      if ($row=$req->next()) {
         $seconds+=(strtotime("now")-strtotime($row['actual_begin']));
      }

      return $seconds;
   }

   static function checkUser($task_id, $user_id) {
      global $DB;

      $query=[
         'FROM'=>self::getTable(),
         'WHERE'=>[
            'tasks_id'=>$task_id,
            [
               'NOT' => ['actual_begin' => null],
            ],
            'actual_end'=>null,
            'users_id'=>$user_id,
         ]
      ];
      $req=$DB->request($query);
      if ($row=$req->next()) {
         return true;
      } else {
         return false;
      }
   }

   /**
    * Check if the technician is free (= not active in any task)
    *
    * @param $user_id  Long  ID of technitian logged in
    *
    * @return Boolean (true if technitian IS NOT ACTIVE in any task)
    * (opposite behaviour from original version until 1.1.0)
   **/
   static function checkUserFree($user_id) {
      global $DB;

      $query=[
         'FROM'=>self::getTable(),
         'WHERE'=>[
            [
               'NOT' => ['actual_begin' => null],
            ],
            'actual_end'=>null,
            'users_id'=>$user_id,
         ]
      ];
      $req=$DB->request($query);
      if ($row=$req->next()) {
         return false;
      } else {
         return true;
      }
   }

   static function getTicket($user_id) {
      if ($task_id=self::getTask($user_id)) {
         $task=new TicketTask();
         $task->getFromDB($task_id);
         return $task->fields['tickets_id'];
      }
      return false;
   }

   static function getTask($user_id) {
      global $DB;

      $query=[
         'FROM'=>self::getTable(),
         'WHERE'=>[
            [
               'NOT' => ['actual_begin' => null],
            ],
            'actual_end'=>null,
            'users_id'=>$user_id,
         ]
      ];
      $req=$DB->request($query);
      $row=$req->next();
      return $row['tasks_id'];
   }

   static function getActualBegin($task_id) {
      global $DB;

      $query=[
         'FROM'=>self::getTable(),
         'WHERE'=>[
            'tasks_id'=>$task_id,
            'actual_end'=>null,
         ]
      ];
      $req=$DB->request($query);
      $row=$req->next();
      return $row['actual_begin'];
   }

   static public function showStats(Ticket $ticket) {
      global $DB;

      $config = new PluginActualtimeConfig;
      if ((Session::getCurrentInterface() == "central")
         || $config->showInHelpdesk()) {

         $total_time=$ticket->getField('actiontime');
         $ticket_id=$ticket->getID();
         $actual_totaltime=0;
         $query=[
            'SELECT'=>[
               'glpi_tickettasks.id',
            ],
            'FROM'=>'glpi_tickettasks',
            'WHERE'=>[
               'tickets_id'=>$ticket_id,
            ]
         ];
         foreach ($DB->request($query) as $id => $row) {
            $actual_totaltime+=self::totalEndTime($row['id']);
         }
         $html="<table class='tab_cadre_fixe'>";
         $html.="<tr><th colspan='2'>ActualTime</th></tr>";

         $html.="<tr class='tab_bg_2'><td>".__("Total duration")."</td><td>".HTML::timestampToString($total_time)."</td></tr>";
         $html.="<tr class='tab_bg_2'><td>ActualTime - ".__("Total duration")."</td><td>".HTML::timestampToString($actual_totaltime)."</td></tr>";

         $diff=$total_time-$actual_totaltime;
         if ($diff<0) {
            $color='red';
         } else {
            $color='black';
         }
         $html.="<tr class='tab_bg_2'><td>".__("Duration Diff", "actiontime")."</td><td style='color:".$color."'>".HTML::timestampToString($diff)."</td></tr>";
         if ($total_time==0) {
            $diffpercent=0;
         } else {
            $diffpercent=100*($total_time-$actual_totaltime)/$total_time;
         }
         $html.="<tr class='tab_bg_2'><td>".__("Duration Diff", "actiontime")." (%)</td><td style='color:".$color."'>".round($diffpercent, 2)."%</td></tr>";

         $html.="</table>";

         $html.="<table class='tab_cadre_fixe'>";
         $html.="<tr><th colspan='5'>ActualTime - ".__("Technician")."</th></tr>";
         $html.="<tr><th>".__("Technician")."</th><th>".__("Total duration")."</th><th>ActualTime - ".__("Total duration")."</th><th>".__("Duration Diff", "actiontime")."</th><th>".__("Duration Diff", "actiontime")." (%)</th></tr>";

         $query=[
            'SELECT'=>[
               'glpi_tickettasks.actiontime',
               'glpi_tickettasks.id AS task_id',
               'glpi_users.name',
               'glpi_users.id',
            ],
            'FROM'=>'glpi_tickettasks',
            'INNER JOIN'=>[
               'glpi_users'=>[
                  'FKEY'=>[
                     'glpi_users'=>'id',
                     'glpi_tickettasks'=>'users_id_tech',
                  ]
               ]
            ],
            'WHERE'=>[
               'glpi_tickettasks.tickets_id'=>$ticket_id,
            ],
            'ORDER'=>'glpi_users.id',
         ];
         $list=[];
         foreach ($DB->request($query) as $id => $row) {
            $list[$row['id']]['name']=$row['name'];
            if (self::findKey($list[$row['id']], 'total')) {
               $list[$row['id']]['total']+=$row['actiontime'];
            } else {
               $list[$row['id']]['total']=$row['actiontime'];
            }
            $qtime=[
               'SELECT'=>['SUM'=>'actual_actiontime AS actual_total'],
               'FROM'=>self::getTable(),
               'WHERE'=>[
                  'tasks_id'=>$row['task_id'],
               ],
            ];
            $req = $DB->request($qtime);
            if ($time = $req->next()) {
               if (self::findKey($list[$row['id']], 'actual_total')) {
                  $list[$row['id']]['actual_total']+=$time['actual_total'];
               } else {
                   $list[$row['id']]['actual_total']=$time['actual_total'];
               }
            }
         }

         foreach ($list as $key => $value) {
            $html.="<tr class='tab_bg_2'><td>".$value['name']."</td>";

            $html.="<td>".HTML::timestampToString($value['total'])."</td>";

            $html.="<td>".HTML::timestampToString($value['actual_total'])."</td>";
            if (($value['total']-$value['actual_total'])<0) {
               $color='red';
            } else {
               $color='black';
            }
            $html.="<td style='color:".$color."'>".HTML::timestampToString($value['total']-$value['actual_total'])."</td>";
            if ($value['total']==0) {
               $html.="<td style='color:".$color."'>0%</td></tr>";
            } else {
               $html.="<td style='color:".$color."'>".round(100*($value['total']-$value['actual_total'])/$value['total'])."%</td></tr>";
            }
         }
         $html.="</table>";

         $script=<<<JAVASCRIPT
$(document).ready(function(){
   $("div.dates_timelines:last").append("{$html}");
});
JAVASCRIPT;
         echo Html::scriptBlock($script);
      }
   }

   static function findKey($array, $keySearch) {
      foreach ($array as $key => $item) {
         if ($key == $keySearch) {
            return true;
         } else if (is_array($item) && self::findKey($item, $keySearch)) {
            return true;
         }
      }
      return false;
   }

   static function getSegment($task_id) {
      global $DB;

      $query=[
         'FROM'=>self::getTable(),
         'WHERE'=>[
            'tasks_id'=>$task_id,
            [
               'NOT' => ['actual_begin' => null],
            ],
            [
               'NOT' => ['actual_end' => null],
            ],
         ]
      ];
      $html="<td colspan='2'><table class='tab_cadre_fixe'>";
      foreach ($DB->request($query) as $id => $row) {
         $html.="<tr class='tab_bg_2'><td>".$row['actual_begin']."</td><td>".HTML::timestampToString($row['actual_actiontime'])."</td></tr>";
      }
      $html.="</table></td>";
      return $html;
   }

   static function afterAdd(TicketTask $item) {
      global $DB;
      $config = new PluginActualtimeConfig;
      $plugin=new Plugin();
      if($item->input['autostart']){
         if($item->getField('state')==1 && $item->getField('users_id_tech')==Session::getLoginUserID() && $item->fields['id']){
            $task_id=$item->fields['id'];
            if ($plugin->isActivated('tam')) {
               if(PluginTamLeave::checkLeave(Session::getLoginUserID())){
                  $result['mensage']=__("Today is marked as absence you can not initialize the timer",'tam');
                  Session::addMessageAfterRedirect(
                     __("Today is marked as absence you can not initialize the timer",'tam'),
                     true,
                     WARNING
                  );
                  return;
               }else{
                  $timer_id=PluginTamTam::checkWorking(Session::getLoginUserID());
                  if ($timer_id==0) {
                     Session::addMessageAfterRedirect(
                        "<a href='".$CFG_GLPI['root_doc']."/front/preference.php?forcetab=PluginTamTam$1'>" .__("Timer has not been initialized", 'tam')."</a>",
                        true,
                        WARNING
                     );
                     return;
                  }
               }
            }
            if (PluginActualtimeTask::checkTimerActive($task_id)) {

               // action=start, timer=on
               $result=[
                  'mensage' => __("A user is already performing the task", 'actualtime'),
                  'type'   => WARNING,
               ];

            } else {

               // action=start, timer=off
               if (! PluginActualtimeTask::checkUserFree(Session::getLoginUserID())) {

                  // action=start, timer=off, current user is alerady using timer
                  $ticket_id = PluginActualtimeTask::getTicket(Session::getLoginUserID());
                  $result=[
                     'mensage' => __("You are already doing a task", 'actualtime')." <a onclick='window.actualTime.showTaskForm(event)' href='/front/ticket.form.php?id=" . $ticket_id . "'>" . __("Ticket") . "$ticket_id</a>",
                     'type'   => WARNING,
                  ];

               } else {

                  // action=start, timer=off, current user is free
                  $DB->insert(
                     'glpi_plugin_actualtime_tasks', [
                        'tasks_id'     => $task_id,
                        'actual_begin' => date("Y-m-d H:i:s"),
                        'users_id'     => Session::getLoginUserID(),
                        'origin_start' => PluginActualtimetask::WEB,
                     ]
                  );
                  $result=[
                     'mensage'   => __("Timer started", 'actualtime'),
                     'title'     => __('Information'),
                     'class'     => 'info_msg',
                     'ticket_id' => PluginActualtimetask::getTicket(Session::getLoginUserID()),
                     'time'      => abs(PluginActualtimeTask::totalEndTime($task_id)),
                     'type'      => INFO
                  ];

                  if ($plugin->isActivated('gappextended')) {
                     PluginGappextendedPush::sendActualtime(PluginActualtimetask::getTicket(Session::getLoginUserID()),$task_id,$result,Session::getLoginUserID(),true);
                  }

               }
            }
            Session::addMessageAfterRedirect(
               $result['mensage'],
               true,
               $result['type']
            );
         }
      }else{
         if ($config->autoOpenNew()) {
            if ($item->getField('state')==1 && $item->getField('users_id_tech')==Session::getLoginUserID() && $item->fields['id']) {
               // Empty record means just added task (for postShowItem)
               $DB->insert(
                  'glpi_plugin_actualtime_tasks', [
                     'tasks_id' => $item->fields['id'],
                     'users_id' => Session::getLoginUserID(),
                  ]
               );
            }
         }
      }
   }

   static function preUpdate(TicketTask $item) {
      global $DB,$CFG_GLPI;
      if(array_key_exists('state',$item->input)){
         if ($item->input['state']!=1) {
            if (self::checkTimerActive($item->input['id'])) {
               $actual_begin=self::getActualBegin($item->input['id']);
               $seconds=(strtotime(date("Y-m-d H:i:s"))-strtotime($actual_begin));
               $DB->update(
                  'glpi_plugin_actualtime_tasks', [
                     'actual_end'      => date("Y-m-d H:i:s"),
                     'actual_actiontime'      => $seconds,
                  ], [
                     'tasks_id'=>$item->input['id'],
                     [
                        'NOT' => ['actual_begin' => null],
                     ],
                     'actual_end'=>null,
                  ]
               );
               $config = new PluginActualtimeConfig;
               if ($config->autoUpdateDuration()) {
                  $item->input['actiontime']=ceil(self::totalEndTime($item->input['id'])/($CFG_GLPI["time_step"]*MINUTE_TIMESTAMP))*($CFG_GLPI["time_step"]*MINUTE_TIMESTAMP);
               }
            }
         }
      }
      if (array_key_exists('users_id_tech',$item->input)){
         if ($item->input['users_id_tech']!=$item->fields['users_id_tech']) {
            if (self::checkTimerActive($item->input['id'])) {
               $actual_begin=self::getActualBegin($item->input['id']);
               $seconds=(strtotime(date("Y-m-d H:i:s"))-strtotime($actual_begin));
               $DB->update(
                  'glpi_plugin_actualtime_tasks', [
                     'actual_end'      => date("Y-m-d H:i:s"),
                     'actual_actiontime'      => $seconds,
                  ], [
                     'tasks_id'=>$item->input['id'],
                     [
                        'NOT' => ['actual_begin' => null],
                     ],
                     'actual_end'=>null,
                  ]
               );
               $config = new PluginActualtimeConfig;
               if ($config->autoUpdateDuration()) {
                  $item->input['actiontime']=ceil(self::totalEndTime($item->input['id'])/($CFG_GLPI["time_step"]*MINUTE_TIMESTAMP))*($CFG_GLPI["time_step"]*MINUTE_TIMESTAMP);
               }
            }
         }
      }
   }

   static function postShowTab($params) {
      if ($ticket_id = PluginActualtimetask::getTicket(Session::getLoginUserID())) {
         $script=<<<JAVASCRIPT
$(document).ready(function(){
   window.actualTime.showTimerPopup($ticket_id);
});
JAVASCRIPT;
         echo Html::scriptBlock($script);
      }
   }

   static function postShowItem($params) {
      global $DB;

      $item = $params['item'];
      if (! is_object($item) || ! method_exists($item, 'getType')) {
         // Sometimes, params['item'] is just an array, like 'Solution'
         return;
      }
      switch ($item->getType()) {
         case 'TicketTask':

            $config = new PluginActualtimeConfig;
            $task_id = $item->getID();
            // Auto open needs to use correct item randomic number
            $rand = $params['options']['rand'];

            // Show timer in closed task box in:
            // Standard interface (always)
            // or Helpdesk inteface (only if config allows)
            if ($config->showTimerInBox() &&
               ((Session::getCurrentInterface() == "central")
               || $config->showInHelpdesk())
            ) {

               $time = self::totalEndTime($task_id);
               $fa_icon = ($time > 0 ? ' fa-clock-o' : '');
               $timercolor = (self::checkTimerActive($task_id) ? 'red' : 'black');
               // Anchor to find correct span, even when user has no update
               // right on status checkbox
               echo "<div id='actualtime_anchor_{$task_id}_{$rand}'></div>";
               $script = <<<JAVASCRIPT
$(document).ready(function() {
   if ($("[id^='actualtime_faclock_{$task_id}_']").length == 0) {
      $("#actualtime_anchor_{$task_id}_{$rand}").prev().find("span.state")
         .after("<i id='actualtime_faclock_{$task_id}_{$rand}' class='fa{$fa_icon}' style='color:{$timercolor}; padding:3px; vertical-align:middle;'></i><span id='actualtime_timer_{$task_id}_box_{$rand}' style='color:{$timercolor}; vertical-align:middle;'></span>");
      if ($time > 0) {
         window.actualTime.fillCurrentTime($task_id, $time);
      }
   }
});
JAVASCRIPT;
               echo Html::scriptBlock($script);
            }

            // Verify if this is a new task just created now
            $autoopennew = false;
            if ($config->autoOpenNew() && $item->fields['state']==1 && $task_id) {
               // New created task opens automatically
               $query=[
                  'FROM'=>self::getTable(),
                  'WHERE'=>[
                     'tasks_id'     => $task_id,
                     'actual_begin' => null,
                     'actual_end'   => null,
                     'users_id'     => Session::getLoginUserID(),
                  ]
               ];
               $req = $DB->request($query);
               if ($row = $req->next()) {
                  $autoopennew = true;
               }
            }
            if ($autoopennew || ($config->autoOpenRunning() && self::checkUser($task_id, Session::getLoginUserID()))) {
               // New created task or user has running timer on this task
               // Open edit window automatically
               $ticket_id = $item->fields['tickets_id'];
               $div = "<div id='actualtime_autoEdit_{$task_id}_{$rand}' onclick='javascript:viewEditSubitem$ticket_id$rand(event, \"TicketTask\", $task_id, this, \"viewitemTicketTask$task_id$rand\")'></div>";
               echo $div;
               $script=<<<JAVASCRIPT
$(document).ready(function(){
   $("#actualtime_autoEdit_{$task_id}_{$rand}").click();
   if ($("[id^='actualtime_autoEdit_']").attr('id') == "actualtime_autoEdit_{$task_id}_{$rand}") {
      // Only scroll the first task if two (first=newly opened, second=timer running)
      function waitForFormLoad(i){
         if ($("#viewitemTicketTask$task_id$rand textarea[name='content']").length) {
            $([document.documentElement, document.body]).animate({
               scrollTop: $("#viewitemTicketTask$task_id$rand").siblings("div.h_info").offset().top
            }, 1000);
            $("#viewitemTicketTask$task_id$rand textarea[name='content']").focus();
         } else if (i > 10) {
            return;
         } else {
            setTimeout(function() {
               waitForFormLoad(++i)
            }, 500);
         }
      }
      waitForFormLoad(0);
   }
});
JAVASCRIPT;

               print_r(Html::scriptBlock($script));
               if ($autoopennew) {
                  // Remove empty record
                  $DB->delete(
                     'glpi_plugin_actualtime_tasks', [
                        'id'           => $row['id'],
                        'actual_begin' => null,
                        'actual_end'   => null,
                        'users_id'     => Session::getLoginUserID(),
                     ]
                  );
               }
            }
            break;
      }
   }

   static function install(Migration $migration) {
      global $DB;

      $table = self::getTable();

      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");

         $query = "CREATE TABLE IF NOT EXISTS $table (
            `id` int(11) NOT NULL auto_increment,
            `tasks_id` int(11) NOT NULL,
            `actual_begin` TIMESTAMP NULL DEFAULT NULL,
            `actual_end` TIMESTAMP NULL DEFAULT NULL,
            `users_id` int(11) NOT NULL,
            `actual_actiontime` int(11) NOT NULL DEFAULT 0,
            `origin_start` INT(11) NOT NULL,
            `latitude_start` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `longitude_start` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `origin_end` INT(11) NOT NULL,
            `latitude_end` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            `longitude_end` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `tasks_id` (`tasks_id`),
            KEY `users_id` (`users_id`)
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
         $DB->query($query) or die($DB->error());
      } else {
         $fields = $DB->list_fields($table, false);
         if ($fields["users_id"]["Type"] != "int(11)") {
            $query = "ALTER TABLE $table MODIFY `users_id` int(11) NOT NULL";
            $DB->query($query) or die($DB->error());
         }
         $migration->addField($table,'origin_start',"INT(11) NOT NULL");
         $migration->addField($table,'latitude_start',"varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($table,'longitude_start',"varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($table,'origin_end',"INT(11) NOT NULL");
         $migration->addField($table,'latitude_end',"varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         $migration->addField($table,'longitude_end',"varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL");
         
         $migration->changeField($table,'actual_begin','actual_begin','TIMESTAMP NULL DEFAULT NULL');
         $migration->changeField($table,'actual_end','actual_end','TIMESTAMP NULL DEFAULT NULL');
         
         $migration->migrationOneTable($table);
      }
   }

   static function uninstall(Migration $migration) {

      $table = self::getTable();
      $migration->displayMessage("Uninstalling $table");
      $migration->dropTable($table);
   }
}
