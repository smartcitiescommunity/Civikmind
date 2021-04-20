<?php

use Glpi\Api\API;

class PluginActualtimeApirest extends API {
	protected $request_uri;
	protected $url_elements;
	protected $verb;
	protected $parameters;
	protected $debug = 0;
	protected $format = "json";

	public static function getTypeName($nb = 0) {
		return __('ActualTime rest API','actualtime');
	}

	public function manageUploadedFiles() {
		foreach (array_keys($_FILES) as $filename) {
			$upload_result = GLPIUploadHandler::uploadFiles(['name' => $filename, 'print_response' => false]);
			foreach ($upload_result as $uresult) {
				$this->parameters['input']->_filename[] = $uresult[0]->name;
				$this->parameters['input']->_prefix_filename[] = $uresult[0]->prefix;
			}
			$this->parameters['upload_result'][] = $upload_result;
		}
	}

	public function parseIncomingParams($is_inline_doc = false) {

		$parameters = [];

		// first of all, pull the GET vars
		if (isset($_SERVER['QUERY_STRING'])) {
			parse_str($_SERVER['QUERY_STRING'], $parameters);
		}

		// now how about PUT/POST bodies? These override what we got from GET
		$body = trim($this->getHttpBody());
		if (strlen($body) > 0 && $this->verb == "GET") {
			// GET method requires an empty body
			$this->returnError("GET Request should not have json payload (http body)", 400, "ERROR_JSON_PAYLOAD_FORBIDDEN");
		}

		$content_type = "";
		if (isset($_SERVER['CONTENT_TYPE'])) {
			$content_type = $_SERVER['CONTENT_TYPE'];
		} else if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
			$content_type = $_SERVER['HTTP_CONTENT_TYPE'];
		} else {
			if (!$is_inline_doc) {
				$content_type = "application/json";
			}
		}

		if (strpos($content_type, "application/json") !== false) {
			if ($body_params = json_decode($body)) {
				foreach ($body_params as $param_name => $param_value) {
					$parameters[$param_name] = $param_value;
				}
			} else if (strlen($body) > 0) {
				$this->returnError("JSON payload seems not valid", 400, "ERROR_JSON_PAYLOAD_INVALID", false);
			}
			$this->format = "json";

		} else if (strpos($content_type, "multipart/form-data") !== false) {
			if (count($_FILES) <= 0) {
				// likely uploaded files is too big so $_REQUEST will be empty also.
				// see http://us.php.net/manual/en/ini.core.php#ini.post-max-size
				$this->returnError("The file seems too big", 400, "ERROR_UPLOAD_FILE_TOO_BIG_POST_MAX_SIZE", false);
			}

			// with this content_type, php://input is empty... (see http://php.net/manual/en/wrappers.php.php)
			if (!$uploadManifest = json_decode(stripcslashes($_REQUEST['uploadManifest']))) {
				$this->returnError("JSON payload seems not valid", 400, "ERROR_JSON_PAYLOAD_INVALID", false);
			}
			foreach ($uploadManifest as $field => $value) {
				$parameters[$field] = $value;
			}
			$this->format = "json";

			// move files into _tmp folder
			$parameters['upload_result'] = [];
			$parameters['input']->_filename = [];
			$parameters['input']->_prefix_filename = [];

		} else if (strpos($content_type, "application/x-www-form-urlencoded") !== false) {
			/** @var array $postvars */
			parse_str($body, $postvars);
			foreach ($postvars as $field => $value) {
				$parameters[$field] = $value;
			}
			$this->format = "html";

		} else {
			$this->format = "html";
		}

		// retrieve HTTP headers
		$headers = [];
		if (function_exists('getallheaders')) {
			//apache specific
			$headers = getallheaders();
			if (false !== $headers && count($headers) > 0) {
				$fixedHeaders = [];
				foreach ($headers as $key => $value) {
					$fixedHeaders[ucwords(strtolower($key), '-')] = $value;
				}
				$headers = $fixedHeaders;
			}
		} else {
			// other servers
			foreach ($_SERVER as $server_key => $server_value) {
				if (substr($server_key, 0, 5) == 'HTTP_') {
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($server_key, 5)))))] = $server_value;
				}
			}
		}

		// try to retrieve basic auth
		if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
			$parameters['login']    = $_SERVER['PHP_AUTH_USER'];
			$parameters['password'] = $_SERVER['PHP_AUTH_PW'];
		}

		// try to retrieve user_token in header
		if (isset($headers['Authorization']) && (strpos($headers['Authorization'], 'user_token') !== false)) {
			$auth = explode(' ', $headers['Authorization']);
			if (isset($auth[1])) {
				$parameters['user_token'] = $auth[1];
			}
		}

		// try to retrieve session_token in header
		if (isset($headers['Session-Token'])) {
			$parameters['session_token'] = $headers['Session-Token'];
		}

		// try to retrieve app_token in header
		if (isset($headers['App-Token'])) {
			$parameters['app_token'] = $headers['App-Token'];
		}

		// check boolean parameters
		foreach ($parameters as $key => &$parameter) {
			if ($parameter === "true") {
				$parameter = true;
			}
			if ($parameter === "false") {
				$parameter = false;
			}
		}

		$this->parameters = $parameters;

		return "";
	}

	private function initEndpoint($unlock_session = true, $endpoint = "") {

		if ($endpoint === "") {
			$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
			$endpoint = $backtrace[1]['function'];
		}
		$this->checkAppToken();
		$this->logEndpointUsage($endpoint);
		self::checkSessionToken();
		if ($unlock_session) {
			self::unlockSessionIfPossible();
		}
	}

	/**
	* Check if the app_toke in case of config ask to
	*
	* @return void
	*/
	private function checkAppToken() {

		// check app token (if needed)
		if (!isset($this->parameters['app_token'])) {
			$this->parameters['app_token'] = "";
		}
		if (!$this->apiclients_id = array_search($this->parameters['app_token'], $this->app_tokens)) {
			if ($this->parameters['app_token'] != "") {
				$this->returnError(__("parameter app_token seems wrong"), 400, "ERROR_WRONG_APP_TOKEN_PARAMETER");
			} else {
				$this->returnError(__("missing parameter app_token"), 400, "ERROR_APP_TOKEN_PARAMETERS_MISSING");
			}
		}
	}

	/**
	* Log usage of the api into glpi historical or log files (defined by api config)
	*
	* It stores the ip and the username of the current session.
	*
	* @param string $endpoint function called by api to log (default '')
	*
	* @return void
	*/
	private function logEndpointUsage($endpoint = "") {

		$username = "";
		if (isset($_SESSION['glpiname'])) {
			$username = "(".$_SESSION['glpiname'].")";
		}

		$apiclient = new APIClient;
		if ($apiclient->getFromDB($this->apiclients_id)) {
			$changes = [
				0,
				"",
				"Enpoint '$endpoint' called by ".$this->iptxt." $username"
			];

			switch ($apiclient->fields['dolog_method']) {
				case APIClient::DOLOG_HISTORICAL:
					Log::history($this->apiclients_id, 'APIClient', $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);
					break;

				case APIClient::DOLOG_LOGS:
					Toolbox::logInFile("api", $changes[2]."\n");
					break;
			}
		}
	}

	/**
	* Unlock the current session (readonly) to permit concurrent call
	*
	* @return void
	*/
	private function unlockSessionIfPossible() {

		if (!$this->session_write) {
			session_write_close();
		}
	}

	/**
	* Retrieve in url_element the current id. If we have a multiple id (ex /Ticket/1/TicketFollwup/2),
	* it always find the second
	*
	* @return integer|boolean id of current itemtype (or false if not found)
	*/
	private function getId() {

		$id = isset($this->url_elements[1]) && is_numeric($this->url_elements[1]) ?intval($this->url_elements[1]) :false;
		$additional_id = isset($this->url_elements[3]) && is_numeric($this->url_elements[3]) ?intval($this->url_elements[3]) :false;

		if ($additional_id || isset($this->parameters['parent_itemtype'])) {
			$this->parameters['parent_id'] = $id;
			$id = $additional_id;
		}

		return $id;
	}

	private function pluginActivated(){

		$plugin=new Plugin();

		if (!$plugin->isActivated('actualtime')) {
			$this->returnError("Plugin disabled", 400, "ERROR_PLUGIN_DISABLED");
		}
	}

	public function call() {

		$this->request_uri  = $_SERVER['REQUEST_URI'];
		$this->verb         = $_SERVER['REQUEST_METHOD'];
		$path_info          = (isset($_SERVER['PATH_INFO'])) ? str_replace("api/", "", trim($_SERVER['PATH_INFO'], '/')) : '';
		$this->url_elements = explode('/', $path_info);

		// retrieve requested resource
		$resource      = trim(strval($this->url_elements[0]));
		$is_inline_doc = (strlen($resource) == 0) || ($resource == "api");

		// Add headers for CORS
		$this->cors($this->verb);

		// retrieve paramaters (in body, query_string, headers)
		$this->parseIncomingParams($is_inline_doc);

		// show debug if required
		if (isset($this->parameters['debug'])) {
			$this->debug = $this->parameters['debug'];
			if (empty($this->debug)) {
				$this->debug = 1;
			}

			if ($this->debug >= 2) {
				$this->showDebug();
			}
		}

		// retrieve session (if exist)
		$this->retrieveSession();
		$this->initApi();
		$this->manageUploadedFiles();

		// retrieve param who permit session writing
		if (isset($this->parameters['session_write'])) {
			$this->session_write = (bool)$this->parameters['session_write'];
		}

		$this->pluginActivated();

		switch ($resource) {
			case 'startTimer':
				return $this->returnResponse($this->startTimer($this->parameters));
			break;
			case 'pauseTimer':
				return $this->returnResponse($this->pauseTimer($this->parameters));
			break;
			case 'stopTimer':
				return $this->returnResponse($this->stopTimer($this->parameters));
			break;
			case 'statsTimer':
				return $this->returnResponse($this->statsTimer($this->parameters));
			break;
			case 'timerStatus':
				return $this->returnResponse($this->timerStatus($this->parameters));
			break;
			default:
				$this->messageLostError();
			break;
		}

	}

	public function returnResponse($response, $httpcode = 200, $additionalheaders = []) {
		if (empty($httpcode)) {
			$httpcode = 200;
		}

		foreach ($additionalheaders as $key => $value) {
			header("$key: $value");
		}

		http_response_code($httpcode);
		self::header($this->debug);

		if ($response !== null) {
			$json = json_encode($response, JSON_UNESCAPED_UNICODE
				| JSON_UNESCAPED_SLASHES
				| JSON_NUMERIC_CHECK
				| ($this->debug
					? JSON_PRETTY_PRINT
					: 0));
		} else {
			$json = '';
		}

		if ($this->debug) {
			echo "<pre>";
			var_dump($response);
			echo "</pre>";
		} else {
			echo $json;
		}
		exit;
	}

	protected function startTimer($params=[]){
		global $DB;

		$this->initEndpoint();
		$task_id=$this->getId();
		
		 $DB->delete(
			 'glpi_plugin_actualtime_tasks', [
				 'tasks_id'      => $task_id,
				 'actual_begin' => null,
				 'actual_end'   => null,
				 'users_id'     => Session::getLoginUserID(),
			 ]
		 );

		$plugin=new Plugin();
		if ($plugin->isActivated('tam')) {
			if(PluginTamLeave::checkLeave(Session::getLoginUserID())){
				$this->returnResponse(__("Today is marked as absence you can not initialize the timer",'tam'), 409);
			}else{
				$timer_id=PluginTamTam::checkWorking(Session::getLoginUserID());
				if ($timer_id==0) {
					$this->returnResponse(__("Timer no initialized",'tam'), 409);
				}
			}
		}
		$task=new TicketTask();
		if(!$task->getFromDB($task_id)){
			$this->returnError(__("Item not found"), 400,'ERROR_ITEM_NOT_FOUND');
		}
		if($task->getField('state')!=1){
			$this->returnResponse(__("Task completed."), 409);
		}

		if (PluginActualtimeTask::checkTimerActive($task_id)) {
			$this->returnResponse(__("A user is already performing the task",'actualtime'), 409);
		} else {
			if (! PluginActualtimeTask::checkUserFree(Session::getLoginUserID())) {
				$ticket_id = PluginActualtimeTask::getTicket(Session::getLoginUserID());
				$this->returnResponse(__("You are already doing a task",'actualtime')." ".__("Ticket") . "$ticket_id", 409);
			} else {
				$DB->insert(
					'glpi_plugin_actualtime_tasks', [
						'tasks_id'     => $task_id,
						'actual_begin' => date("Y-m-d H:i:s"),
						'users_id'     => Session::getLoginUserID(),
						'origin_start' => PluginActualtimetask::ANDROID,
						/*'latitude_start'=>$params['latitude'],
						'longitude_start'=>$params['longitude'],*/
					]
				);
				$result=[
					'message'   => __("Timer started", 'actualtime'),
					'time'      => abs(PluginActualtimeTask::totalEndTime($task_id)),
				];
			}
		}

		return $result;
	}

	protected function pauseTimer($params=[]){
		global $DB;

		$this->initEndpoint();
		$task_id=$this->getId();

		if (PluginActualtimeTask::checkTimerActive($task_id)) {
			if (PluginActualtimeTask::checkUser($task_id, Session::getLoginUserID())) {
				$actual_begin=PluginActualtimeTask::getActualBegin($task_id);
				$seconds=(strtotime(date("Y-m-d H:i:s"))-strtotime($actual_begin));
				$DB->update(
					'glpi_plugin_actualtime_tasks', [
						'actual_end'        => date("Y-m-d H:i:s"),
						'actual_actiontime' => $seconds,
						'origin_end' => PluginActualtimetask::ANDROID,
						/*'latitude_end'=>$params['latitude'],
						'longitude_end'=>$params['longitude'],*/
					], [
						'tasks_id' => $task_id,
						[
							'NOT' => ['actual_begin' => null],
						],
						'actual_end' => null,
					]
				);
				$result=[
					'message' => __("Timer completed", 'actualtime'),
					'title'   => __('Information'),
					'class'   => 'info_msg',
					'segment' => PluginActualtimeTask::getSegment($task_id),
					'time'    => abs(PluginActualtimeTask::totalEndTime($task_id)),
				];
			} else {
				$this->returnResponse(__("Only the user who initiated the task can close it",'actualtime'), 409);
			}
		} else {
			$this->returnResponse(__("The task had not been initialized",'actualtime'), 409);
		}
		return $result;
	}

	protected function stopTimer($params=[]){
		global $DB,$CFG_GLPI;

		$this->initEndpoint();
		$task_id=$this->getId();
		$config = new PluginActualtimeConfig;

		if (PluginActualtimeTask::checkTimerActive($task_id)) {
			if (PluginActualtimeTask::checkUser($task_id, Session::getLoginUserID())) {
				$actual_begin=PluginActualtimeTask::getActualBegin($task_id);
				$seconds=(strtotime(date("Y-m-d H:i:s"))-strtotime($actual_begin));
				$DB->update(
					'glpi_plugin_actualtime_tasks', [
						'actual_end'        => date("Y-m-d H:i:s"),
						'actual_actiontime' => $seconds,
						'origin_end' => PluginActualtimetask::ANDROID,
						/*'latitude_end'=>$params['latitude'],
						'longitude_end'=>$params['longitude'],*/
					], [
						'tasks_id' => $task_id,
						[
							'NOT' => ['actual_begin' => null],
						],
						'actual_end' => null,
					]
				);
				$task=new TicketTask();
				$task->getFromDB($task_id);
				$input['id']=$task_id;
				$input['tickets_id']=$task->fields['tickets_id'];
				$input['state']=2;
				if ($config->autoUpdateDuration()) {
					$input['actiontime']=ceil(PluginActualtimeTask::totalEndTime($task_id)/($CFG_GLPI["time_step"]*MINUTE_TIMESTAMP))*($CFG_GLPI["time_step"]*MINUTE_TIMESTAMP);
				}
				$task->update($input);

				$result=[
					'message' => __("Timer completed", 'actualtime'),
					'segment' => PluginActualtimeTask::getSegment($task_id),
					'time'    => abs(PluginActualtimeTask::totalEndTime($task_id)),
					'task_time'=> $task->getField('actiontime'),
				];
			} else {
				$this->returnResponse(__("Only the user who initiated the task can close it",'actualtime'), 409);
			}
		} else {
			$task=new TicketTask();
			$task->getFromDB($task_id);
			$input['id']=$task_id;
			$input['tickets_id']=$task->fields['tickets_id'];
			$input['state']=2;
			if ($config->autoUpdateDuration()) {
				$input['actiontime']=ceil(PluginActualtimeTask::totalEndTime($task_id)/($CFG_GLPI["time_step"]*MINUTE_TIMESTAMP))*($CFG_GLPI["time_step"]*MINUTE_TIMESTAMP);
			}
			$task->update($input);

			$result=[
				'message' => __("Timer completed", 'actualtime'),
				'segment' => PluginActualtimeTask::getSegment($task_id),
				'time'    => abs(PluginActualtimeTask::totalEndTime($task_id)),
				'task_time'=> $task->getField('actiontime'),
			];
		}
		return $result;
	}

	protected function statsTimer($params=[]){
		global $DB;

		$this->initEndpoint();
		$task_id=$this->getId();

		$query=[
			'FROM'=>'glpi_tickettasks',
			'WHERE'=>[
				'id'=>$task_id,
			]
		];
		$req = $DB->request($query);
		$actiontime=0;
		if ($row = $req->next()) {
			$actiontime=$row['actiontime'];
		}
		$actual_totaltime=abs(PluginActualtimeTask::totalEndTime($task_id));
		if ($actiontime==0) {
			$diffpercent=0;
		} else {
			$diffpercent=100*($actiontime-$actual_totaltime)/$actiontime;
		}
		$result=[
			'time' => $actual_totaltime,
			'actiontime'=>$actiontime,
			'diff'=>$actiontime-$actual_totaltime,
			'diffpercent'=>$diffpercent,
		];

		return $result;
	}
	
	protected function timerStatus($params=[]){
		
		$this->initEndpoint();
		if(PluginActualtimeTask::checkUserFree(Session::getLoginUserID())){
			$result=[
				'free'=>true,
			];
		}else{
			$result=[
				'free'=>false,
				'ticket_id'=>PluginActualtimeTask::getTicket(Session::getLoginUserID()),
				'task_id'=>PluginActualtimeTask::getTask(Session::getLoginUserID()),
				'time'=>abs(PluginActualtimeTask::totalEndTime(PluginActualtimeTask::getTask(Session::getLoginUserID())))
			];
		}
		return $result;
	}

}
