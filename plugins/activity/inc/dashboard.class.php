<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

class PluginActivityDashboard extends CommonGLPI {

   public  $widgets = [];
   private $options;
   private $datas, $form;

   function __construct($options = []) {
      $this->options    = $options;
      $this->interfaces = ["central"];
   }

   function init() {
      global $CFG_GLPI;

      //      $mois_courant   = intval(strftime("%m"));
      //      $annee_courante = strftime("%Y");
      //
      //      if (isset($this->options['users_id']) && Session::haveRight("plugin_activity_all_users", 1)) {
      //         $users_id = $this->options['users_id'];
      //      } else {
      //         $users_id = $_SESSION['glpiID'];
      //      }
      //
      //      if (isset($this->options["month"])
      //          && $this->options["month"] > 0) {
      //         $mois_courant = $this->options["month"];
      //      }
      //
      //      $this->datas = array(
      //         "year"     => $annee_courante,
      //         "month"    => $mois_courant,
      //         "users_id" => $users_id
      //      );
      //
      //
      //      $this->form = "<label>";
      //      $this->form .= PluginActivityReport::monthDropdown("month", $mois_courant);
      //      $this->form .= "</label>";
      //
      //
      //      //This form will show a choice of User
      //      $this->form .= "<label>" . __("User") . " :";
      //      $this->form .= User::dropdown(array('name'     => "users_id",
      //                                          'value'    => $users_id,
      //                                          'right'    => "interface",
      //                                          'comments' => 1,
      //                                          'entity'   => $_SESSION["glpiactiveentities"],
      //                                          'width'    => '50%',
      //                                          'display'  => false));
      //      $this->form .= "</label>";
      //      $this->form .= "</form>";
   }

   function getWidgetsForItem() {
      return array(
         $this->getType() . "1" => __('Activity in the month', 'activity') . "&nbsp;<i class='fa fa-pie-chart'></i>",
         //         $this->getType()."2" => __('Planning access', 'activity'),
         $this->getType() . "3" => __("Activity Menu", 'activity') . "&nbsp;<i class='fa fa-info-circle'></i>",
         $this->getType() . "4" => __("Interventions not in CRA", "activity") . "&nbsp;<i class='fa fa-table'></i>",
      );
   }

   function getWidgetContentForItem($widgetId, $opt = []) {
      global $CFG_GLPI, $DB;

      $dbu = new DbUtils();
      if (empty($this->form))
         $this->init();
      switch ($widgetId) {


         case $this->getType() . "1" :
            //            $widget = new PluginMydashboardPieChart();
            //            $widget->setWidgetId($widgetId);
            //            $widget->setWidgetTitle(__('Activity in the month', 'activity')." (".__(strftime("%B")).")");
            //            $widget->setOption("mouse", array("trackDecimals" => 2));
            //            $widget->setOption("mouse", array("trackFormatter" => PluginMydashboardChart::getTrackFormatter()));
            //            $widget->setOption("legend", array("show" => false));

            //            $widget->setTabDatas($this->showActivityGraph($this->datas));

            $mois_courant   = intval(strftime("%m"));
            $annee_courante = strftime("%Y");

            if (isset($opt['users_id']) && Session::haveRight("plugin_activity_all_users", 1)) {
               $users_id = $opt['users_id'];
            } else {
               $users_id = $_SESSION['glpiID'];
            }

            if (isset($opt["month"])
                && $opt["month"] > 0) {
               $mois_courant = $opt["month"];
            }

            if (isset($opt["year"])
                && $opt["year"] > 0) {
               $annee_courante = $opt["year"];
            }

            $params["month"] = $mois_courant;
            $params["users_id"] = $users_id;
            $params["year"] = $annee_courante;

            $this->datas = [
               "year"     => $annee_courante,
               "month"    => $mois_courant,
               "users_id" => $users_id
            ];

            $activities = $this->showActivityGraph($this->datas);
            $widget     = new PluginMydashboardHtml();
            $title      = __('Activity in the month', 'activity') . " (" . __(strftime("%B")) . ")";
            $widget->setWidgetTitle($title);
            $widget->setWidgetComment(__("Display of activity by month for a user (tickets, activity, holidays, others)", "activity"));
            $datas = [];
            $name  = [];
            $i     = 0;
            foreach ($activities as $actname => $times) {
               $i++;
               $datas[] = $times;
               $name[]  = $actname;
            }

            $palette = PluginMydashboardColor::getColors($i);

            $dataPieset         = json_encode($datas);
            $backgroundPieColor = json_encode($palette);
            $labelsPie          = json_encode($name);


            $graph = "<script type='text/javascript'>
         
            var dataPieActivity = {
              datasets: [{
                data: $dataPieset,
                backgroundColor: $backgroundPieColor
              }],
              labels: $labelsPie
            };
            
            $(document).ready(
              function() {
                var isChartRendered = false;
                var canvas = document.getElementById('MonthActivityPieChart');
                var ctx = canvas.getContext('2d');
                ctx.canvas.width = 700;
                ctx.canvas.height = 400;
                var myNewChart = new Chart(ctx, {
                  type: 'pie',
                  data: dataPieActivity,
                  options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    animation: {
                        onComplete: function() {
                          isChartRendered = true
                        }
                      }
                   }
                });
            
      //          canvas.onclick = function(evt) {
      //            var activePoints = myNewChart.getElementsAtEvent(evt);
      //            if (activePoints[0]) {
      //              var chartData = activePoints[0]['_chart'].config.data;
      //              var idx = activePoints[0]['_index'];
      //      
      //              var label = chartData.labels[idx];
      //              var value = chartData.datasets[0].data[idx];
      //      
      //              var url = \"http://example.com/?label=\" + label + \"&value=\" + value;
      //              console.log(url);
      //              alert(url);
      //            }
      //          };
              }
            );
                
             </script>";

            $criterias = ['users_id', 'month', 'year'];
            $params    = ["widgetId"  => $widgetId,
                          "name"      => 'MonthActivityPieChart',
                          "onsubmit"  => false,
                          "opt"       => $params,
                          "criterias" => $criterias,
                          "export"    => true,
                          "canvas"    => true,
                          "nb"        => $i];
            $graph     .= PluginMydashboardHelper::getGraphHeader($params);
            $widget->setWidgetHtmlContent(
               $graph
            );

            return $widget;


         case $this->getType() . "2" :
            $widget = new PluginMydashboardHtml();
            $widget->setWidgetId($widgetId);
            $lang_month = array_values(Toolbox::getMonthsOfYearArray());
            $lang_days  = array_values(Toolbox::getDaysOfWeekArray());
            foreach ($lang_month as $month) {
               $lang_month_short[] = substr($month, 0, 4);
            }
            foreach ($lang_days as $day) {
               $lang_days_short[] = substr($day, 0, 3);
            }
            $widget->setWidgetTitle("<a href='" . $CFG_GLPI['root_doc'] . "/plugins/activity/front/activity.form.php'>" . __('Planning access', 'activity') . "</a>");

            $activities = "{}";
            $activities = json_encode($this->getActivities($this->datas['users_id']));
            $rand       = mt_rand();
            $html       = '<script type="text/javascript">$("#calendarwidget' . $rand . '").fullCalendar({header: {
                                                                                                   left:"",
                                                                                                   center: "",
                                                                                                   right:""
                                                                                               },
                                                                                               monthNames: ' . json_encode($lang_month) . ',
                                                                                               monthNamesShort: ' . json_encode($lang_month_short) . ',
                                                                                               dayNames: ' . json_encode($lang_days) . ',
                                                                                               dayNamesShort: ' . json_encode($lang_days_short) . ',
                                                                                               minTime: "7:00am",
                                                                                               maxTime: "10:00pm",
                                                                                               axisFormat: "H:mm",
                                                                                               defaultView:"agendaDay",
                                                                                               contentHeight:2500,
                                                                                               allDaySlot: true,
                                                                                               allDayText:"' . __('All day', 'activity') . '",
                                                                                               slotMinutes:60,
                                                                                               columnFormat: {
                                                                                                   day: "dddd dd/MM"
                                                                                               },
                                                                                               timeFormat: {agenda: "H:mm"},
                                                                                               editable:false,
                                                                                               eventRender: function(event, element) {
                                                                                                   if (event.description) {
                                                                                                      element.find(".fc-event-title").append("<br/>" + $.fullCalendar.formatDate(event.end, "H:mm"));
                                                                                                   }
                                                                                               },
                                                                                               eventMouseover: function(event, jsEvent, view) {
                                                                                                   $(".fc-event-inner", this).append("<div id="+event.id+">"+event.description+"</div>");
                                                                                               },
                                                                                               eventMouseout: function(event, jsEvent, view) {
                                                                                                   $("#"+event.id).remove();
                                                                                               },'
                          . 'eventClick: function(event, jsEvent, view){
                                                                                                  if(event.editable) {
                                                                                                     callActivityModalUpdate(event, event.start, event.end, event.allDay, calendar);
                                                                                                  }
                                                                                               },'
                          . 'events:' . $activities . '});';
            $html       .= "onMaximize['" . $widgetId . "'] = function() { $('#calendarwidget" . $rand . "').fullCalendar('render'); }; ";
            $html       .= "onMinimize['" . $widgetId . "'] = onMaximize['" . $widgetId . "'];";
            $html       .= "</script>
                               <div id='calendarwidget" . $rand . "' ></div>";
            $widget->setWidgetHtmlContent($html);
            //                    Form to choose user, and then see user's planning, toggleRefresh to enable the automatic refresh
            if (Session::haveRight("plugin_activity_all_users", 1)) {
               $widget->appendWidgetHtmlContent(PluginMydashboardHelper::getFormHeader($this->getType() . "2") . $this->form);
               $widget->toggleWidgetRefresh();
            }
            return $widget;
            break;
         case $this->getType() . "3":
            $widgetHTML = new PluginMydashboardHtml();
            $widgetHTML->setWidgetTitle(__("Activity Menu", 'activity'));
            $listActions = array_merge(PluginActivityPlanningExternalEvent::getActionsOn(), PluginActivityPlanningExternalEvent::getActionsOn());
            $widgetHTML->setWidgetHtmlContent(PluginActivityPlanningExternalEvent::menu("PluginActivityPlanningExternalEvent", $listActions, true));
            return $widgetHTML;
            break;
         case $this->getType() . "4":


            $widget = new PluginMydashboardHtml();

            $mois  = intval(strftime("%m") - 6);
            $annee = intval(strftime("%Y") - 1);

            if ($mois > 0) {
               $annee = strftime("%Y");
            } else {
               $mois = 12;
            }
            $entity = $_SESSION['glpiactive_entity'];
            $query  = "SELECT `glpi_tickettasks`.`date`, 
                              `glpi_tickets`.`entities_id`,
                              `glpi_tickettasks`.`tickets_id`, 
                              `glpi_tickets`.`name` AS tickets_name, 
                              `glpi_tickettasks`.`content`, 
                              `glpi_tickettasks`.`actiontime` 
                              FROM `glpi_tickettasks`
                        LEFT JOIN `glpi_tickets`
                                             ON (`glpi_tickets`.`id` = `glpi_tickettasks`.`tickets_id`)
                        LEFT JOIN `glpi_plugin_activity_tickettasks` 
                                             ON (`glpi_tickettasks`.`id` = `glpi_plugin_activity_tickettasks`.`tickettasks_id`)
                        WHERE `glpi_plugin_activity_tickettasks`.`is_oncra` = 0 
                        AND `glpi_tickettasks`.`is_private` = 1 
                        AND `glpi_tickets`.`status` NOT IN ('5', '6') AND `glpi_tickettasks`.`date` >= '$annee-$mois-01 00:00:01' 
                        AND `glpi_tickets`.`entities_id` IN  (" . implode(",", $dbu->getSonsOf("glpi_entities", $entity)) . ") ORDER BY `glpi_tickettasks`.`date` ";


            $widget  = PluginMydashboardHelper::getWidgetsFromDBQuery('table', $query);
            $datas   = [];
            $headers = [__('Creation date'), __('Client'), __('Ticket'), __('Description'), __('Total duration')];
            $widget->setTabNames($headers);

            $result      = $DB->query($query);
            $nb          = $DB->numrows($result);
            $link_ticket = Toolbox::getItemTypeFormURL("Ticket");

            $i = 0;
            if ($nb) {
               while ($data = $DB->fetchAssoc($result)) {


                  $datas[$i]["date"] = Html::convDateTime($data['date']);

                  $datas[$i]["entity"] = Dropdown::getDropdownName("glpi_entities",
                                                                   $data['entities_id']);


                  $name_ticket               = "<a href='" . $link_ticket . "?id=" . $data['tickets_id'] . "' target='_blank'>";
                  $name_ticket               .= $data['tickets_name'] . "</a>";
                  $datas[$i]["tickets_name"] = $name_ticket;

                  $datas[$i]["content"] = $data['content'];

                  $datas[$i]["actiontime"] = Html::timestampToString($data["actiontime"], 0);
                  $i++;
               }
            }
            $widget->setTabDatas($datas);
            $widget->setOption("bSort", false);
            $widget->toggleWidgetRefresh();

            $widget->setWidgetTitle(__("Interventions not in CRA", "activity"));

            return $widget;
            break;
      }
   }

   function showActivityGraph($input) {
      global $CFG_GLPI, $DB;

      $dbu    = new DbUtils();
      $AllDay = PluginActivityReport::getAllDay();

      $holiday = new PluginActivityHoliday();
      $holiday->setHolidays();
      //      $input["month"] = "02";
      $crit["begin"]             = $input["year"] . "-" . $input["month"] . "-01 00:00:00";
      $lastday                   = cal_days_in_month(CAL_GREGORIAN, $input["month"], $input["year"]);
      $crit["end"]               = $input["year"] . "-" . $input["month"] . "-" . $lastday . " 23:59:59";
      $crit["users_id"]          = $input["users_id"];
      $crit["global_validation"] = PluginActivityCommonValidation::ACCEPTED;

      # 1.1 Plugin Activity
      $query  = PluginActivityPlanningExternalEvent::queryAllExternalEvents($crit);
      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $total  = 0;

      $query1 = "SELECT SUM(`glpi_plugin_activity_planningexternalevents`.`actiontime`) AS total 
                  FROM `glpi_plugin_activity_planningexternalevents`
                   LEFT JOIN `glpi_planningexternalevents` 
                     ON (`glpi_plugin_activity_planningexternalevents`.`planningexternalevents_id` = `glpi_planningexternalevents`.`id`)";
      $query1.= " WHERE (`begin` >= '".$crit["begin"]."' 
                           AND `begin` <= '".$crit["end"]."')
                              AND `users_id` = '".$crit["users_id"]."'";
      if ($result1 = $DB->query($query1)) {
         $data1 = $DB->fetchArray($result1);
         $total = $data1["total"];
      }


      # 1.2 Plugin Manageentities
      $plugin  = new Plugin();
      $numberm = 0;
      if ($plugin->isActivated('manageentities')) {
         $config = new PluginManageentitiesConfig();
         $config->GetFromDB(1);

         $crit["documentcategories_id"] = $config->fields["documentcategories_id"];

         $manage  = PluginActivityPlanningExternalEvent::queryManageentities($crit);
         $resultm = $DB->query($manage);
         $numberm = $DB->numrows($resultm);
      }

      # 1.3 Tickets
      $tickets  = PluginActivityPlanningExternalEvent::queryTickets($crit);
      $resultt1 = $DB->query($tickets);
      $numbert  = $DB->numrows($resultt1);

      # 1.1 Plugin holiday
      $queryh  = "SELECT SUM(actiontime) AS total 
                  FROM `glpi_plugin_activity_holidays`";
      $queryh  .= " WHERE (`begin` >= '" . $crit["begin"] . "' 
                           AND `begin` <= '" . $crit["end"] . "')
                        AND `users_id` = '" . $crit["users_id"] . "'";
      $resulth = $DB->query($queryh);
      $numberh = $DB->numrows($resulth);

      $pie = [];

      if ($number != "0" || $numberm != "0" || $numbert != "0" || $numberh != "0") {

         # 2.2 Details
         $title  = [];
         $values = [];

         # 2.3 Plugin Activity
         if ($number != "0") {

            while ($data = $DB->fetchArray($result)) {
               if ($data["total_actiontime"] > 0) {
                  $percent = $data["total_actiontime"] * 100 / $total;
               } else {
                  $percent = 0;
               }

               $parents = $dbu->getAncestorsOf("glpi_plugin_activity_activitytypes", $data["type"]);
               $last    = end($parents);

               if (empty($data["type"])) {
                  $type = $data["entity"] . " > " . __('No defined type', 'activity');
               } else {
                  $dropdown = new PlanningEventCategory();
                  if (count($parents) > 1) {
                     $dropdown->getFromDB($last);
                     $type = $dropdown->fields['name'];
                  } else {
                     $dropdown->getFromDB($data["type"]);
                     $type = $dropdown->fields['name'];
                  }
               }

               $values[$type][] = $data["total_actiontime"] / $AllDay;
            }
         }

         foreach ($values as $k => $v) {
            $pie[$k] = array_sum($v);
         }

         # 2.3 Plugin Activity holidays
         if (Session::haveRight("plugin_activity_can_requestholiday", 1)) {
            $opt["is_usedbycra"] = true;
            $opt                 = array_merge($crit, $opt);

            $queryh = PluginActivityHoliday::queryUserHolidays($opt);


            $resulth = $DB->query($queryh);
            if ($DB->numrows($resulth)) {
               $tmp = [];
               while ($datah = $DB->fetchArray($resulth)) {
                  if (empty($datah["type"])) {
                     $type = $datah["entity"] . " > " . __('No defined type', 'activity');
                  } else {
                     $type = $datah["type"];
                  }
                  if (!isset($tmp[$type]))
                     $tmp[$type] = 0;

                  $value = $datah['actiontime'];

                  $tmp[$type] += $value;
               }
               foreach ($tmp as $type => $value) {
                  $pie[$type] = /* ($value * 100) / $total */
                     $value / $AllDay;
               }
            }
         }

         //         # 1.3 Tickets
         if ($numbert != "0") {
            $values = [];
            $sums   = [];
            $sum    = 0;
            $report = new PluginActivityReport();
            while ($datat = $DB->fetchArray($resultt1)) {

               $mtitle   = strtoupper($datat["entity"]) . " > " . _n('Ticket', 'Tickets', 2);
               $internal = PluginActivityConfig::getConfigFromDB($datat['entities_id']);
               if ($internal) {
                  foreach ($internal as $field) {
                     $mtitle = strtoupper($datat["entity"]) . " > " . $field["name"];
                  }
               }
               if (!empty($datat["begin"]) && !empty($datat["end"])) {
                  $values = $report->timeRepartition($datat['actiontime'] / $AllDay, $datat["begin"], $values, PluginActivityReport::$WORK, $mtitle, $holiday->getHolidays());
               } else {
                  $values = $report->timeRepartition($datat['actiontime'] / $AllDay, $datat["date"], $values, PluginActivityReport::$WORK, $mtitle, $holiday->getHolidays());
               }
            }
            $intern = $values[0];
            foreach ($intern as $name => $times) {
               foreach ($times as $date => $nb) {
                  $formatdate = date('Y-m-d', strtotime($date));
                  if (isset($sums[$formatdate])) {
                     $sums[$formatdate] += $nb;
                  } else {
                     $sums[$formatdate] = $nb;
                  }
               }
            }
            foreach ($sums as $k => $cnt) {
               $sum += PluginActivityReport::TotalTpsPassesArrondis($cnt);
            }
            $pie[$mtitle] = $sum;
         }


         //         # 2.4 Plugin Manageentities
         if ($plugin->isActivated('manageentities')) {
            if ($numberm != "0") {
               while ($datam = $DB->fetchArray($resultm)) {

                  $queryTask = "SELECT `glpi_tickettasks`.*
                                 FROM `glpi_tickettasks`
                                 LEFT JOIN `glpi_plugin_manageentities_cridetails`
                                    ON (`glpi_plugin_manageentities_cridetails`.`tickets_id` = `glpi_tickettasks`.`tickets_id`)
                                 LEFT JOIN `glpi_plugin_activity_tickettasks`
                                    ON (`glpi_plugin_activity_tickettasks`.`tickettasks_id` = `glpi_tickettasks`.`id`)
                                 WHERE `glpi_tickettasks`.`tickets_id` = '" . $datam['tickets_id'] . "'
                                       AND (`glpi_tickettasks`.`begin` >= '" . $crit["begin"] . "' 
                                       AND `glpi_tickettasks`.`end` <= '" . $crit["end"] . "')
                                       AND `glpi_plugin_activity_tickettasks`.`is_oncra` = 1
                                       AND `glpi_tickettasks`.`users_id_tech` = '" . $crit["users_id"] . "'";

                  $resultTask = $DB->query($queryTask);
                  $numberTask = $DB->numrows($resultTask);
                  if ($numberTask != "0") {
                     while ($dataTask = $DB->fetchArray($resultTask)) {

                        $mtitle = $datam["entity"] . " > ";
                        if ($datam["withcontract"]) {
                           $contract = new Contract();
                           $contract->getFromDB($datam["contracts_id"]);
                           $mtitle .= $contract->fields["num"];
                        }
                        $values       = $dataTask['actiontime'] / $AllDay;
                        $pie[$mtitle] = $values;
                     }
                  }
               }
            }
         }
      }

      return $pie;
   }

   function getActivities($users_id, $activities_id = 0) {
      global $DB, $CFG_GLPI;

      $AllDay = PluginActivityReport::getAllDay();

      $default_view = PluginActivityPlanningExternalEvent::$DAY;

      $report  = new PluginActivityReport();
      $holiday = new PluginActivityHoliday();
      $holiday->setHolidays();

      $month = strftime("%m");

      $queryMinDate = "SELECT MIN(`date`)
                  FROM `glpi_tickettasks`";

      if ($resultMinDate = $DB->query($queryMinDate)) {
         if ($DB->numrows($resultMinDate)) {
            $MinDate = ($DB->result($resultMinDate, 0, 0));
         }
      }

      $queryMinBeginDate = "SELECT MIN(`begin`)
                  FROM `glpi_tickettasks`";

      if ($resultMinBeginDate = $DB->query($queryMinBeginDate)) {
         if ($DB->numrows($resultMinBeginDate)) {
            $MinBeginDate = ($DB->result($resultMinBeginDate, 0, 0));
         }
      }

      $first = $MinDate;
      if ($MinBeginDate < $MinDate) {
         $first = $MinBeginDate;
      }
      $crit["begin"]        = date("Y-m-d") . " 00:00:00";
      $lastday              = cal_days_in_month(CAL_GREGORIAN, $month, date("Y"));
      $crit["end"]          = date("Y") . "-" . $month . "-" . date("d") . " 23:59:59";
      $crit["users_id"]     = $users_id;
      $crit["is_usedbycra"] = false;

      $use_pairs = 0;
      $opt       = new PluginActivityOption();
      $opt->getFromDB(1);
      if ($opt) {
         $use_pairs = $opt->fields['use_pairs'];
      }
      // ACTIVITIES
      $activities    = [];
      $activity      = new PlanningExternalEvent();
      $dbu           = new DbUtils();
      $allActivities = $dbu->getAllDataFromTable($activity->getTable());

      $query  = PluginActivityPlanningExternalEvent::queryUserExternalEvents($crit);
      $result = $DB->query($query);

      $number = $DB->numrows($result);
      $values = [];
      if ($DB->numrows($result)) {
         while ($data = DBmysql::fetchArray($result)) {
            $values = $report->timeRepartition($data['actiontime'] / $AllDay, $data["begin"], $values, PluginActivityReport::$WORK, $data['id'], $holiday->getHolidays(), true);
         }
         $currentime = date("Y-m-d H:i:s");

         foreach ($values as $k => $v) {
            foreach ($v as $id => $data) {
               if (count($data) > 1) {
                  current($data);
               }
               foreach ($data as $date => $duration) {

                  $activity = $allActivities[$id];
                  $begin    = $date;

                  if ($use_pairs == 1) {
                     $end = date("Y-m-d H:i:s", strtotime($begin) + (PluginActivityReport::TotalTpsPassesArrondis($duration) * $AllDay));
                  } else {
                     $end = date("Y-m-d H:i:s", strtotime($begin) + ($duration * $AllDay));
                  }

                  $iscurrent = ($currentime >= $begin && $currentime <= $end);
                  $content   = $activity['comment'];
                  $nb        = count($data);
                  if ($nb > 1) {
                     $content .= "</br>";
                     $content .= Html::timestampToString($activity['actiontime'] / $nb, false);
                  } else {
                     $content .= "</br>";
                     $content .= Html::timestampToString($activity['actiontime'], false);
                  }
                  $isallday = false;
                  if ($activity['allDay'] == 1) {
                     $isallday = true;
                  }

                  $activities[] = ['id'              => $id,
                                        'title'           => $activity['name'],
                                        'description'     => $content,
                                        'start'           => $begin,
                                        'end'             => $end,
                                        'editable'        => false,
                                        'allDay'          => $isallday,
                                        'color'           => PluginActivityPlanningExternalEvent::$ACTIVITY_COLOR,
                                        'backgroundColor' => ($iscurrent) ? 'rgb(136, 218, 99)' : ''
                  ];
               }
            }
         }
      }

      // HOLIDAYS
      $crit['global_validation'] = PluginActivityCommonValidation::ACCEPTED;
      $queryh                    = PluginActivityHoliday::queryUserHolidays($crit);
      $resulth                   = $DB->query($queryh);
      $numberh                   = $DB->numrows($resulth);

      $values       = [];
      $holidaytypes = [];
      if ($DB->numrows($resulth)) {
         while ($datah = DBmysql::fetchArray($resulth)) {

            //$isallday = false;
            //if ($datah['allDay'] == 1) {
            //   $isallday = true;
            //}

            $values = $report->timeRepartition($datah['actiontime'] / $AllDay, $datah["begin"], $values, PluginActivityReport::$WORK, $datah["id"], $holiday->getHolidays());

            //if (empty($datah["type"])) {
            //   $holidaytypes[$datah["id"]] = $datah["entity"]." > ".__('No defined type', 'activity');
            //} else {
            $holidaytypes[$datah["id"]] = $datah["type"];
            //}
         }

         foreach ($values as $k => $v) {
            foreach ($v as $id => $data) {
               foreach ($data as $date => $duration) {
                  $action = PluginActivityReport::TotalTpsPassesArrondis($duration) * $AllDay;
                  $end    = date("Y-m-d H:i:s", strtotime($date) + $action);

                  $isallday = false;
                  if ($action == $AllDay) {
                     $isallday = true;
                  }

                  if ($action > 0) {
                     $activities[] = ['id'          => $id,
                                           'title'       => $holidaytypes[$id],
                                           'description' => '',
                                           'start'       => $date,
                                           'end'         => $end,
                                           'editable'    => false,
                                           'allDay'      => $isallday,
                                           'color'       => PluginActivityPlanningExternalEvent::$HOLIDAY_COLOR
                     ];
                  }
               }
            }
         }
      }

      // TICKETS
      $tickets = PluginActivityPlanningExternalEvent::queryTickets($crit);
      $resultt = $DB->query($tickets);
      $numbert = $DB->numrows($resultt);

      if ($numbert != "0") {

         $mtitle     = "";
         $begin      = "";
         $end        = 0;
         $alltickets = [];
         $all        = [];
         $tickets    = [];
         while ($datat = $DB->fetchArray($resultt)) {
            $mtitle   = strtoupper($datat["entity"]) . " > " . __('Ticket');
            $internal = PluginActivityConfig::getConfigFromDB($datat['entities_id']);
            if ($internal) {
               foreach ($internal as $field) {
                  $mtitle = strtoupper($datat["entity"]) . " > " . $field["name"];
               }
            }

            $use_timerepartition = 0;
            $opt                 = new PluginActivityOption();
            $opt->getFromDB(1);
            if ($opt) {
               $use_timerepartition = $opt->fields['use_timerepartition'];
            }

            if ($use_timerepartition > 0) {
               if (!empty($datat["begin"]) && !empty($datat["end"])) {
                  $alltickets = $report->timeRepartition($datat['actiontime'] / $AllDay, $datat["begin"], $alltickets, PluginActivityReport::$WORK, $mtitle, $holiday->getHolidays());
               } else {
                  $alltickets = $report->timeRepartition($datat['actiontime'] / $AllDay, $datat["date"], $alltickets, PluginActivityReport::$WORK, $mtitle, $holiday->getHolidays());
               }
            } else {
               if (!empty($datat["begin"]) && !empty($datat["end"])) {
                  $tickets[$mtitle][$datat["begin"]] = $datat['actiontime'] / $AllDay;
               } /* else {
                 //NOT PLANIFIED TASKS
                 $tickets[$datat["date"]] = $datat['actiontime']/$AllDay;

                 } */
            }
         }

         if ($use_timerepartition < 1) {
            //TODO end it
            $alltickets[0] = $tickets;
         }
         $y = 1;

         foreach ($alltickets as $k => $v) {
            foreach ($v as $title => $data) {
               foreach ($data as $date => $duration) {

                  if ($use_timerepartition > 0) {
                     $action = PluginActivityReport::TotalTpsPassesArrondis($duration) * $AllDay;
                  } else {
                     $action = $duration * $AllDay;
                  }
                  $content = Html::timestampToString($action, false);
                  $end     = date("Y-m-d H:i:s", strtotime($date) + $action);

                  $y++;
                  if ($action > 0) {
                     $activities[] = ['id'          => $y,
                                           'title'       => $title,
                                           'description' => $content,
                                           'start'       => $date,
                                           'end'         => $end,
                                           'editable'    => false,
                                           'allDay'      => false,
                                           'color'       => PluginActivityPlanningExternalEvent::$TICKET_COLOR];
                  }
               }
            }
         }
      }
      return $activities;
   }
}