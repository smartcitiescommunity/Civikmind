<?php

class PluginActualtimeRunning extends CommonGLPI {
	
	static function getMenuName(){
		return __("Actualtime","actualtime");
	}

	static function getMenuContent(){
		$menu=[
			'title'=>self::getMenuName(),
			'page'=>self::getSearchURL(false),
			'icon'=>'fas fa-stopwatch'
		];

		return $menu;
	}

	static public function show(){
		global $DB;

		$rand=mt_rand();
		echo "<div class='center'>";
		echo "<h1>".__("Running timers","actualtime")."</h1>";
		echo "</div>";

		echo "<div class='right' style='padding:10px;max-width: 950px;margin: 0px auto 5px auto;'>";

		echo "<label style='padding:2px'>".__("Update every (s)","actualtime")." </label>";
		Dropdown::showNumber('interval',['value'=>5,'min'=>5,'max'=>MINUTE_TIMESTAMP,'step'=>10,'rand'=>$rand]);
		echo "<label style='padding:2px'>".__("Disable")." </label>";
		Dropdown::showYesNo('disable',0,-1,['use_checkbox'=>true,'rand'=>$rand]);
		echo "<i id='refresh' class='fa fa-sync pointer' style='margin-left: 10px;font-size: 15px'></i>";

		echo "</div>";

		echo "<div id='running'>";
		echo "<div>";
		$script=<<<JAVASCRIPT
		$(document).ready(function() {
			var loading=setInterval(loadRunning,5000);
			var interval=5000;

			function loadRunning(){
				$.ajax({
					type:'POST',
					url:CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.actualtime+"/ajax/running.php",
					data:{
						action:'getlist'
					},
					success:function(data){
						$('#running').html(data);
					}
				});
			}
			loadRunning();
			$('#refresh').click(function(){
				loadRunning();
			});

			$('#dropdown_interval{$rand}').on('change',function(){
				clearInterval(loading);
				interval=(this.value*1000);
				loading=setInterval(loadRunning,(this.value*1000));
			});
			$('#dropdown_disable{$rand}').change(function(){
				if (this.checked) {
					clearInterval(loading);
				} else {
					loading=setInterval(loadRunning,interval);
				}
			});
		});
JAVASCRIPT;
		 echo Html::scriptBlock($script);
	}

	static function listRunning(){
		global $DB;

		if (countElementsInTable(PluginActualtimeTask::getTable(),[['NOT' => ['actual_begin' => null],],'actual_end'=>null,])>0) {
			$query=[
				'FROM'=>PluginActualtimeTask::getTable(),
				'WHERE'=>[
					[
						'NOT' => ['actual_begin' => null],
					],
					'actual_end'=>null,
				]
			];
			$html= "<table class='tab_cadre_fixehov'>";
			$html.= "<tr>";
			$html.= "<th class='center'>".__("Technician")."</th>";
			$html.= "<th class='center'>".__("Entity")."</th>";
			$html.= "<th class='center'>".__("Ticket")." - ".__("Task")."</th>";
			$html.= "<th class='center'>".__("Time")."</th>";
			$html.= "</tr>";

			foreach ($DB->request($query) as $key => $row) {
				$html.= "<tr class='tab_bg_2'>";
				$user=new User();
				$user->getFromDB($row['users_id']);
				$html.= "<td class='center'><a href='".$user->getLinkURL()."'>".$user->getFriendlyName()."</a></td>";
				$task_id=$row['tasks_id'];
				$task=new TicketTask();
				$task->getFromDB($row['tasks_id']);
				$ticket=new Ticket();
				$ticket->getFromDB($task->fields['tickets_id']);
				$html.= "<td class='center'>".Entity::getFriendlyNameById($ticket->fields['entities_id'])."</td>";
				$html.= "<td class='center'><a href='".$ticket->getLinkURL()."'>".$ticket->getID()." - ".$task->getID()."</a></td>";
				$html.= "<td class='center'>".HTML::timestampToString(PluginActualtimeTask::totalEndTime($row['tasks_id']))."</td>";
				$html.= "</tr>";
			}
			$html.= "</table>";
		} else {
			$html= "<div><p class='center b'>".__('No timer active')."</p></div>";
		}
		return $html;
	}
}