<?php

class PluginProjectbridgeContract extends CommonDBTM
{
    private $_contract;
    private $_project_id;
    private $_nb_hours;
    public static $table_name = 'glpi_plugin_projectbridge_contracts';

    /**
     * Constructor
     *
     * @param Contract|null $contract
     */
    public function __construct($contract = null)
    {
        if ($contract !== null || $contract instanceof Contract) {
            $this->_contract = $contract;
        }
    }

    /**
     * Get the id of the project linked to the contract
     *
     * @param void
     * @return integer|null
     */
    public function getProjectId()
    {
        if ($this->_project_id === null) {
            $this->_project_id = 0;
            $result = $this->getFromDBByCrit(['contract_id' => $this->_contract->getId()]);

            if ($result) {
                $this->_project_id = (int) $this->fields['project_id'];
            }
        }

        return $this->_project_id;
    }

    /**
     * Get number of hours for this contract
     *
     * @return integer|null
     */
    public function getNbHours()
    {
        if ($this->_nb_hours === null) {
            $result = $this->getFromDBByCrit(['contract_id' => $this->_contract->getId()]);

            if ($result) {
                $this->_nb_hours = (int) $this->fields['nb_hours'];
            }
        }

        return $this->_nb_hours;
    }

    /**
     * Display HTML after contract has been shown
     *
     * @param  Contract $contract
     * @return void
     */
    public static function postShow(Contract $contract)
    {
        $contract_id = $contract->getId();

        $html_parts = [];
        $html_parts[] = '<div style="display: none;">' . "\n";
        $html_parts[] = '<table class="tab_cadre_fixe">' . "\n";
        $html_parts[] = '<tr id="projectbridge_config" class="tab_bg_1">' . "\n";
        $html_parts[] = '<td>';
        $html_parts[] = __('linking Project', 'projectbridge');
        $html_parts[] = '</td>' . "\n";
        $html_parts[] = '<td colspan="2">' . "\n";
        if (empty($contract_id)) {
            // create
            $html_parts[] = PluginProjectbridgeContract::_getPostShowCreateHtml($contract);
        } else {
            // update
            $html_parts[] = PluginProjectbridgeContract::_getPostShowUpdateHtml($contract);
        }
        $html_parts[] = '</td>' . "\n";
        $html_parts[] = '<td>';
        $html_parts[] = '&nbsp;';
        $html_parts[] = '</td>' . "\n";
        $html_parts[] = '</tr>' . "\n";
        $html_parts[] = '</table>' . "\n";
        $html_parts[] = '</div>' . "\n";

        echo implode('', $html_parts);
        echo Html::scriptBlock('$(document).ready(function() {
            var projectbridge_config = $("#projectbridge_config");
            var target = $("#mainformtable tr.footerRow").next();

            if (!target.length) {
                target = $("#mainformtable tr:last");
            }

            target.before(projectbridge_config.clone());
            projectbridge_config.remove();
            projectbridge_config = $("#projectbridge_config");

            $(".select2-container", projectbridge_config).remove();
            $("select", projectbridge_config).select2({
                width: \'\',
                dropdownAutoWidth: true
            });
            $(".select2-container", projectbridge_config).show();
        });');
    }

    /**
     * Get HTML to create a contract
     *
     * @param  Contract $contract
     * @return string HTML
     */
    private static function _getPostShowCreateHtml(Contract $contract)
    {
        $html_parts = [];
        $html_parts[] = __('Create project', 'projectbridge') . ' :';
        $html_parts[] = '&nbsp;';
        $html_parts[] = Dropdown::showYesNo('projectbridge_create_project', 1, -1, ['display' => false]);
        $html_parts[] = PluginProjectbridgeContract::_getPostShowHoursHtml(0);

        return implode('', $html_parts);
    }

    /**
     * Get HTML to update a contract
     *
     * @param  Contract $contract
     * @return string HTML
     */
    private static function _getPostShowUpdateHtml(Contract $contract)
    {
        $search_filters = [          
          'is_deleted' => 0,
        ];

        $haveToBeRenewed = false;
        if (!empty($_SESSION['glpiactiveentities'])) {
            $search_filters['entities_id'] =  ['IN', implode(', ', $_SESSION['glpiactiveentities'])] ;
        }

        $bridge_contract = new PluginProjectbridgeContract($contract);
        $project_id = $bridge_contract->getProjectId();

        $project = new Project();
        $project_results = $project->find($search_filters);
        $project_list = [
          null => Dropdown::EMPTY_VALUE,
        ];

        foreach ($project_results as $project_data) {
            $project_list[$project_data['id']] = $project_data['name'] . ' (' . $project_data['id'] . ')';
        }

        $project_config = [
          'value' => $project_id,
          'display' => false,
          'values' => $project_list,
        ];

        $html_parts = [];
        $html_parts[] = Dropdown::showFromArray('projectbridge_project_id', $project_list, $project_config);

        global $CFG_GLPI;

        if (!empty($project_id) && isset($project_list[$project_id]) ) 
        {
            $html_parts[] = '<a href="' . $CFG_GLPI['root_doc'] . '/front/project.form.php?id=' . $project_id . '" style="margin-left:5px;" target="_blank">';
            $html_parts[] = __('Access to linked project', 'projectbridge');
            $html_parts[] = '</a>' . "\n";

            $html_parts[] = PluginProjectbridgeContract::_getPostShowHoursHtml($bridge_contract->getNbHours(), false);

            $html_parts[] = '<br />';
            $html_parts[] = '<br />';

            
            if (self::getProjectTaskOject($project_id) ) {
                $search_closed = false;
            } else {
                $search_closed = true;
            }
            
            

            $consumption_ratio = 0;
            $nb_hours = $bridge_contract->getNbHours();

            if ($nb_hours) {
                
                // get all activ projectTask
                $activeProjectTask = PluginProjectbridgeContract::getAllActiveProjectTasksForProject($project_id);
                
                if ( !count($activeProjectTask) ) {
                    $haveToBeRenewed = true;
                    $html_parts[] = '<span class="red">'.__('Warning ! No associate projectTask with "In progress" status exist', 'projectbridge') .' </span><br/>';
                    $projectTaskID = null;
                }else{
                    $projectTaskID = $activeProjectTask[0]['id'];
                }
               
                $consumption = PluginProjectbridgeContract::getTicketsTotalActionTime($projectTaskID);        
                if ($consumption) {
                    $consumption = $consumption / 3600;
                    $consumption_ratio = $consumption / $nb_hours;
                }
                
                $classRation = '';
                if ( $consumption >= $nb_hours) {
                    $haveToBeRenewed = true;
                    $classRation = 'red';
                }
                $html_parts[] = '<span class="'.$classRation.'">';
                if( $activeProjectTask ) {
                    $html_parts[] = __('Comsuption', 'projectbridge') . ' : ';
                    $html_parts[] = round($consumption, 2) . '/' . $nb_hours . ' ' . _n('Hour', 'Hours', $nb_hours);
                    $html_parts[] = '&nbsp;';
                    $html_parts[] = '(' . round($consumption_ratio * 100) . '%)';
                } else{
                    //$html_parts[] = '0/'. $nb_hours . ' ' . _n('Hour', 'Hours', $nb_hours).'&nbsp; (0%)';
                }
                $html_parts[] = '</span>';
            }


            $planEndDate = PluginProjectbridgeContract::getContractPlanEndDate($contract);
            $plan_end_date = $planEndDate->format('Y-m-d');
            $end_date_reached = false;

            if (!empty($plan_end_date)) {
                $datediff = strtotime($plan_end_date) - time();
                $date_delta = $datediff / (60 * 60 * 24);
                $end_date_delta = floor($date_delta);

                if ($nb_hours) {
                    $html_parts[] = '&nbsp;';
                    $html_parts[] = '-';
                    $html_parts[] = '&nbsp;';
                }

                if ($end_date_delta == 0) {
                    $end_date_reached = true;
                    $html_parts[] = '<span class="red">'.__('Expired in less than 24h', 'projectbridge').' ! </span>';
                } elseif ($end_date_delta > 0) {
                    $html_parts[] = __('Expired in', 'projectbridge') . ' ' . $end_date_delta . ' ' . _n('Day', 'Days', abs($end_date_delta));
                } else {
                    $end_date_reached = true;

                    if ($date_delta > -1) {
                        $html_parts[] = '<span class="red">'.__('Expired today', 'projectbridge').' ! </span>';
                    } else {
                        $html_parts[] = '<span class="red">'.__('Expired since', 'projectbridge') . ' ' . (abs($end_date_delta)) . ' ' . _n('Day', 'Days', abs($end_date_delta)).' ! </span>';
                        $haveToBeRenewed = true;
                    }
                }
            }

            if ($haveToBeRenewed) {
                $html_parts[] = '&nbsp;-&nbsp;';

                $html_parts[] = '<input type="submit" value="' . __('Renew the contract', 'projectbridge') . '" class="submit projectbridge-renewal-trigger" />' . "\n";

                $renewal_data = $bridge_contract->getRenewalData();
                $html_parts[] = '<table class="projectbridge-renewal-data" style="display: none; margin-top: 15px; padding: 15px; background: #cacaca;">' . "\n";

                $html_parts[] = '<tr>' . "\n";
                $html_parts[] = '<td>';
                $html_parts[] = __('Start date');
                $html_parts[] = '</td>' . "\n";
                $html_parts[] = '<td>';
                $html_parts[] = Html::showDateField('projecttask_begin_date', [
                          'value' => $renewal_data['begin_date'],
                          'maybeempty' => false,
                          'display' => false,
                ]);
                $html_parts[] = '</td>' . "\n";
                $html_parts[] = '</tr>' . "\n";
               
                
                $html_parts[] = '<tr>' . "\n";
                $html_parts[] = '<td>';
                $html_parts[] = __('Duration').' ('._n('Month', 'Months', 2).')';
                $html_parts[] = '</td>' . "\n";
                $html_parts[] = '<td>';
                $html_parts[] = '<input type="number" min="0" max="12" name="projectbridge_duration" value="' . $renewal_data['duration'] . '" style="width: 50px" step="any" />';
               
                $html_parts[] = '</td>' . "\n";
                $html_parts[] = '</tr>' . "\n";

                $html_parts[] = '<tr>' . "\n";
                $html_parts[] = '<td>';
                $html_parts[] = __('Number of hours', 'projectbridge');
                $html_parts[] = '</td>' . "\n";
                $html_parts[] = '<td>';
                $html_parts[] = '<input type="number" min="0" max="99999" name="projectbridge_nb_hours_to_use" value="' . $renewal_data['nb_hours_to_use'] . '" style="width: 50px" step="any" />';
                $html_parts[] = '</td>' . "\n";
                $html_parts[] = '</tr>' . "\n";

                $html_parts[] = '<tr>' . "\n";
                $html_parts[] = '<td>';
                $html_parts[] = '<input type="submit" name="update" value="' . __('Confirm renewal', 'projectbridge') . '" class="submit projectbridge-renewal-tickets" />';
                $html_parts[] = '</td>' . "\n";
                $html_parts[] = '<td>';
                $html_parts[] = '<input type="submit" name="update" value="' . __('Cancel') . '" class="submit projectbridge-renewal-cancel" />';
                $html_parts[] = '</td>' . "\n";
                $html_parts[] = '</tr>' . "\n";

                $html_parts[] = '</table>' . "\n";

                $modal_url = PLUGIN_PROJECTBRIDGE_WEB_DIR.'/ajax/get_renewal_tickets.php';
                $html_parts[] = Ajax::createModalWindow('renewal_tickets_modal', $modal_url, [
                          'display' => false,
                          'extraparams' => [
                            //'task_id' => PluginProjectbridgeContract::getProjectTaskDataByProjectId($project_id, 'task_id', $search_closed),
                            'task_id' => self::getProjectTaskFieldValue($project_id, $search_closed, 'id'),
                            'contract_id' => $contract->getId(),
                            'project_id' => $project_id
                          ],
                ]);
                
                $date_format = Toolbox::getDateFormat('js');

                $js_block = '
                    window.projectbridge_datepicker_init = true;
                    window._renewal_modal_js = undefined;

                    /**
                     * Trigger a timeout until a modal is open
                     *
                     * @param jQueryObject modal
                     * @param function callback
                     */
                    function timeoutUntilModalOpen(modal, callback)
                    {
                        if ($("form", modal).length) {
                            callback();
                        } else {
                            window.setTimeout(function() {
                                timeoutUntilModalOpen(modal, callback);
                            }, 300);
                        }
                    }
                    
                    function add_months(dt, n) 
                    {
                      return new Date(dt.setMonth(dt.getMonth() + parseInt(n)));      
                    }

                    $(document).on("click", ".projectbridge-renewal-trigger", function(e) {
                        e.preventDefault();
                        var fieldwithFlatPicket = $("input[name=projecttask_begin_date]");
                        var fieldwithFlatPicketParent = $("input[name=projecttask_begin_date]").parent();
                        
                        const fp = fieldwithFlatPicketParent.flatpickr({
                            defaultDate: fieldwithFlatPicket.val(),
                            altInput: true,
                            altFormat: \''.$date_format.'\',
                            dateFormat: \'Y-m-d\',
                            wrap: true,
                            weekNumbers: true,
                            locale: "'.$CFG_GLPI['languages'][$_SESSION['glpilanguage']][3].'",
                        });
                        if($("table.projectbridge-renewal-data").find("input.flatpickr").length > 1){
                            $("table.projectbridge-renewal-data").find("input.flatpickr").last().remove();
                        }
                        
                        $(".projectbridge-renewal-data").show();
                        $(this).hide();
                        return false;
                    })
                    .on("click", ".projectbridge-renewal-cancel", function(e) {
                        e.preventDefault();
                        $(".projectbridge-renewal-data").hide();
                        $(".projectbridge-renewal-trigger").show();
                        $("input[name=projecttask_begin_date]").prop("type", "text");
                        
                        return false;
                    })
                    .on("click", ".projectbridge-renewal-tickets", function(e) {
                        e.preventDefault();

                        if (renewal_tickets_modal === undefined) {
                            if (window._renewal_modal_js === undefined) {
                                window._renewal_modal_js = new Function($(".projectbridge-renewal-data").next().html() + "return renewal_tickets_modal;");
                            }
                            renewal_tickets_modal = window._renewal_modal_js();
                        }

                        renewal_tickets_modal.dialog("open");
                        var strDate = $("input[name=projecttask_begin_date]").val().split("-");
                        var begin_Date = new Date(parseInt(strDate[0]), parseInt(strDate[1])-1, parseInt(strDate[2]));
                        var end_date = add_months(begin_Date, $("input[name=projectbridge_duration]").val()).toISOString().slice(0,10);
                        var data_to_add_to_modal = {
                            projectbridge_project_id: $("[id^=dropdown_projectbridge_project_id]").val(),
                            _projecttask_begin_date: $("input[name=projecttask_begin_date]").val(),
                            //_projecttask_end_date: $("input[name=_projecttask_end_date]").val(),
                            _projecttask_end_date: end_date,
                            projectbridge_duration: $("input[name=projectbridge_duration]").val(),
                            projectbridge_nb_hours_to_use: $("input[name=projectbridge_nb_hours_to_use]").val()
                        };

                        var html_to_add_to_modal = "";

                        for (var data_name in data_to_add_to_modal) {
                            html_to_add_to_modal += "<input type=\"hidden\" name=\"" + data_name + "\" value=\"" + data_to_add_to_modal[data_name] + "\" />";
                        }

                        timeoutUntilModalOpen(renewal_tickets_modal, function() {
                            $("form", renewal_tickets_modal).prepend(html_to_add_to_modal);
                            renewal_tickets_modal = undefined;
                        });

                        return false;
                    });
                ';
                $html_parts[] = Html::scriptBlock($js_block);
            }
        } else {
            $html_parts[] = '<a href="' . $CFG_GLPI['root_doc'] . '/front/setup.templates.php?itemtype=Project&add=1" style="margin-left: 5px;" target="_blank">';
            $html_parts[] = __('Create project', 'projectbridge') . ' ?';
            $html_parts[] = '</a>' . "\n";

            $html_parts[] = '<small>';
            $html_parts[] = __('Remember to refresh this page after creating the project', 'projectbridge');
            $html_parts[] = '</small>' . "\n";

            $html_parts[] = PluginProjectbridgeContract::_getPostShowHoursHtml($bridge_contract->getNbHours());
        }

        return implode('', $html_parts);
    }
    
    public static function getTicketsTotalActionTime($projecttasks_id) {
      global $DB;
      
      $whereConditionsArray = ['projecttasks_id' => $projecttasks_id];
      
      $onlypublicTasks = PluginProjectbridgeConfig::getConfValueByName('CountOnlyPublicTasks');
      if($onlypublicTasks) {
          $whereConditionsArray['is_private'] = 0;
      }

      $iterator = $DB->request([
         'SELECT'       => new QueryExpression('SUM('.TicketTask::getTable().'.actiontime) AS duration'),
         'FROM'         => ProjectTask_Ticket::getTable(),
         'INNER JOIN'   => [
            Ticket::getTable() => [
               'FKEY'   => [
                  ProjectTask_Ticket::getTable()  => 'tickets_id',
                  Ticket::getTable()    => 'id'
               ]
            ],
           TicketTask::getTable() => [
               'FKEY'   => [                  
                  Ticket::getTable()    => 'id',
                  TicketTask::getTable()  => 'tickets_id'
               ]
            ],
         ],
         'WHERE' => $whereConditionsArray
      ]);
      

      if ($row = $iterator->next()) {
         return $row['duration'];
      }
      return 0;
   }

    /**
     * Get HTML to manage hours
     *
     * @param  integer $nb_hours
     * @return string HTML
     */
    private static function _getPostShowHoursHtml($nb_hours, $withInput = true)
    {
        $html_parts = [];

        if ($withInput) {
            $html_parts[] = '<br />';
            $html_parts[] = '<br />';
            $html_parts[] = __('Number of hours', 'projectbridge') . ' :';
            $html_parts[] = '&nbsp;';
            $html_parts[] = '<input type="number" min="0" max="99999" step="1" name="projectbridge_project_hours" value="' . $nb_hours . '" style="width: 50px" />';
        } else {
            $html_parts[] = '<input type="hidden" name="projectbridge_project_hours" value="' . $nb_hours . '" />';
        }

        return implode('', $html_parts);
    }

       
    /**
     * search list of projectTask by criterias
     * @param array $criteria
     * @param string $order
     * @param integer $limit
     */
    public static function getProjectTaskBy($criteria, $order='', $limit= '') {
        $project_tasks = new ProjectTask();
        $tasks = $project_tasks->find($criteria, $order, $limit);
        
        return $tasks;
    }
    
    /**
     * get projectTask object bu projectId
     * @param integer $project_id
     * @param boolean $search_closed
     * @return object
     */
    public static function getProjectTaskOject($project_id, $search_closed = false) {
        $state_closed_value = PluginProjectbridgeState::getProjectStateIdByStatus('closed');
        $criteria = [
                    'projects_id' => $project_id,
                    'projectstates_id' => [$search_closed?'=':'!=', $state_closed_value]
                  ];
        $order = 'plan_end_date DESC';
        
        $projectTaskObject = null;
        $projectTaskFinded = self::getProjectTaskBy($criteria, $order, 1);
        
        if (count($projectTaskFinded)) {
            $firstElement = reset($projectTaskFinded);
            $projectTaskId = $firstElement['id'];
            $projectTask = new ProjectTask();
            $projectTaskObject = $projectTask->getById($projectTaskId);
        }
        
        return $projectTaskObject;
    }
    
    /**
     * get all closed projecttask associate to one project
     * 
     * @param integer $project_id id of the project
     * @param string $status the search status ( closed / in_progress
     * @param integer $limit
     * @return type
     */
    public static function getProjectTasksForProjectByStatus($project_id, $status, $operator= '=', $limit = '') 
    {

        $state_value = PluginProjectbridgeState::getProjectStateIdByStatus($status);

        if (empty($state_value)) {
            global $CFG_GLPI;
            $redirect_url = PLUGIN_PROJECTBRIDGE_WEB_DIR . '/front/config.form.php';

            Session::addMessageAfterRedirect(__('Please define the correspondence of the "'.ucfirst($status).'" status.', 'projectbridge'), false, ERROR);
            Html::redirect($redirect_url);
            return null;
        }
        
        $project_tasks = new ProjectTask();

        $where = [
          'projects_id' => $project_id,
          'projectstates_id' => [$operator, $state_value]
        ];

        $order = 'plan_end_date DESC';

        $tasks = $project_tasks->find($where, $order, $limit);
        
        return $tasks;

    }
    
    /**
     * get all active projecttask associate to one project
     * @global object $DB
     * @param integer $project_id
     * @return type
     */
    public static function getAllActiveProjectTasksForProject($project_id)
    {
        global $DB;
      
        $state_in_progress_value = PluginProjectbridgeState::getProjectStateIdByStatus('in_progress');
        $state_closed_value = PluginProjectbridgeState::getProjectStateIdByStatus('closed');
        $state_renewal_value = PluginProjectbridgeState::getProjectStateIdByStatus('renewal');

        $tasks = [];
        foreach ($DB->request(
            'glpi_projecttasks',
            [
                                "projects_id" => $project_id,
                                "projectstates_id" => [$state_in_progress_value, $state_renewal_value],
                                'ORDER'       => ['plan_start_date DESC']
                                ]
        ) as $data) {
              $tasks[] = $data;
        }
        return $tasks;
    }
    
    /**
     * get projectTask consumption
     * @param integer $project_id
     * @param boolean $search_closed
     * @return object
     */
    public static function getProjectTaskConsumption($project_id, $search_closed) {
        $return = 0;
        $projectTaskId = self::getProjectTaskFieldValue($project_id, $search_closed, 'id');
        if($projectTaskId) {
            $action_time = ProjectTask_Ticket::getTicketsTotalActionTime($projectTaskId);
            if ($action_time > 0) {
                        $return = $action_time / 3600;
                    }
        }
        return $return;          
    }
    
    /**
     * get projectTask duration
     * @param integer $project_id
     * @param boolean $search_closed
     * @return object
     */
    public static function getProjectTaskPlannedDuration($project_id, $search_closed){
        $return = 0;
        $plannedDuration = self::getProjectTaskFieldValue($project_id, $search_closed, 'planned_duration');
        if($plannedDuration){
            $return = $plannedDuration / 3600;
        }
        
        return $return;        
    } 
    
    /**
     * get projectTask value of one field
     * @param integer $project_id
     * @param boolean $search_closed
     * @return object
     */
    public static function getProjectTaskFieldValue($project_id, $search_closed, $field){
        $return = '';

        $projectTaskObject = self::getProjectTaskOject($project_id, $search_closed);
        if($projectTaskObject) {
            $return = $projectTaskObject->getField($field);
        }
        
        return $return;
        
    } 

    /**
     * calcul the end date of one contract
     * @param object $contract
     * @return \DateTime
     */
    public static function getContractPlanEndDate($contract)
    {
        $beginDate = $contract->getField('begin_date');
        $duration = $contract->getField('duration');
        $planEndDate = new DateTime($beginDate);
        $planEndDate->add(new DateInterval('P' . $duration . 'M'));

        return $planEndDate;
    }

    /**
     * Renew the task of the project linked to this contract
     *
     * @param void
     * @return void
     */
    public function renewProjectTask()
    {
        global $DB;
        $project_id = $this->getProjectId();
        $newTicketIds = [];
        
        if ($project_id <= 0) {
            return;
        }

        $state_in_progress_value = PluginProjectbridgeState::getProjectStateIdByStatus('in_progress');
        

        if (empty($state_in_progress_value)) {
            Session::addMessageAfterRedirect(__('The match for the status "In progress" has not been defined. The contract could not be renewed.', 'projectbridge'), false, ERROR);
            return false;
        }
        
        // récupération des tâches de projets ouvertes avant la création de la nouvelle
        $allActiveTasks = self::getAllActiveProjectTasksForProject($project_id);
        
        // close previous active project taks
        if($allActiveTasks) {
            // call crontask function ( projectTask ) to close previous project task and create a new tikcet with exeed time if necessary
            $pluginProjectbridgeTask = new PluginProjectbridgeTask();
            $newTicketIds = $pluginProjectbridgeTask->closeTaskAndCreateExcessTicket($allActiveTasks, false);
        }
        
        $renewal_data = $this->getRenewalData($use_input_data = true);
        $plan_end_date = date('Y-m-d  H:i:s', strtotime($renewal_data['begin_date'] . ' + ' . $renewal_data['duration'] . ' months - 1 days'));

        $project_task_data = [
          // data from contract
          //'name' => date('Y-m'),
          'name' => date('Y-m', strtotime($renewal_data['begin_date'])),
          'entities_id' => $this->_contract->fields['entities_id'],
          'is_recursive' => $this->_contract->fields['is_recursive'],
          'projects_id' => $project_id,
          'content' => addslashes($this->_contract->fields['comment']),
          'comment' => '',
          'plan_start_date' => date('Y-m-d H:i:s', strtotime($renewal_data['begin_date'])),
          'plan_end_date' => $plan_end_date,
          'planned_duration' => $renewal_data['nb_hours_to_use'] * 3600, // in seconds
          'projectstates_id' => $state_in_progress_value, // "in progress"
          // standard data to bootstrap task
          'projecttasktemplates_id' => 0,
          'projecttasks_id' => 0,
          'projecttasktypes_id' => 0,
          'percent_done' => 0,
          'is_milestone' => 0,
          'real_start_date' => '',
          'real_end_date' => '',
          'effective_duration' => 0,
        ];

        // create the new project's task
        $project_task = new ProjectTask();
        $task_id = $project_task->add($project_task_data);
        
        // associate selected tickets
        if ($task_id && !empty($this->_contract->input['ticket_ids']) && is_array($this->_contract->input['ticket_ids'])) {
            // link selected tickets
            foreach ($this->_contract->input['ticket_ids'] as $ticket_id => $selected) {
                if ($selected) {
                    $project_task_ticket = new ProjectTask_Ticket();
                    $project_task_ticket->add([
                      'tickets_id' => $ticket_id,
                      'projecttasks_id' => $task_id,
                    ]);
                }
            }
        }
        // associate new tickets created from old tickets
        foreach( $newTicketIds as $ticket_id ) {
            $project_task_ticket = new ProjectTask_Ticket();
                    $project_task_ticket->add([
                      'tickets_id' => $ticket_id,
                      'projecttasks_id' => $task_id,
                    ]);
        }
        
        // mise a jour date de début contrat et durée       
        $this->_contract->input['begin_date'] = $renewal_data['begin_date'];
        $this->_contract->input['duration'] = $renewal_data['planned_duration'];
        
        $DB->update(
                $this->getTable(), [
                    'nb_hours' => $renewal_data['nb_hours_to_use']
                ],[
                    'id' =>$this->getID()
                ] 
                );
        

        
    }

    /**
     * Get data used to renew the contract
     *
     * @param boolean $use_input_data
     * @return array
     */
    public function getRenewalData($use_input_data = false)
    {        
        $project_id = $this->getProjectId();
        $open_exists = self::getProjectTasksForProjectByStatus($project_id, 'closed', '!=', 1);
        $closed_exists = self::getProjectTasksForProjectByStatus($project_id, 'closed', '=', 1);
        $use_closed = false;

        if (!$use_input_data && $closed_exists && !$open_exists ) {
            $use_closed = true;

            $previous_task_start = self::getProjectTaskFieldValue($project_id, true, 'plan_start_date');
            $previous_task_end = self::getProjectTaskFieldValue($project_id, true, 'plan_end_date');

            $datediff = ceil((strtotime($previous_task_end) - strtotime($previous_task_start)) / 3600 / 24);
            $task_start_date = date('Y-m-d', strtotime($previous_task_end . ' + 1 day'));
            $task_end_date = date('Y-m-d', strtotime($task_start_date . ' + ' . $datediff . ' days'));
        } else {
            if($open_exists) {
                $previous_task_start = self::getProjectTaskFieldValue($project_id, false, 'plan_start_date');
                $previous_task_end = self::getProjectTaskFieldValue($project_id, false, 'plan_end_date');
                $task_start_date = date('Y-m-d', strtotime($previous_task_end . ' + 1 day'));
                $datediff = ceil((strtotime($previous_task_end) - strtotime($previous_task_start)) / 3600 / 24);
                $task_end_date = date('Y-m-d', strtotime($task_start_date . ' + ' . $datediff . ' days'));
                $use_closed = false;
            } else {
                if (empty($this->_contract->input['_projecttask_begin_date'])) {
                    $task_start_date = date('Y-m-d');
                } else {
                    $task_start_date = date('Y-m-d', strtotime($this->_contract->input['_projecttask_begin_date']));

                    $use_closed = false;
                }
            }

            if (empty($this->_contract->input['_projecttask_end_date'])) {
                $task_end_date = (
                    !empty($this->_contract->fields['duration']) ? Infocom::getWarrantyExpir(date('Y-m-d', strtotime($task_start_date)), $this->_contract->fields['duration']) : ''
                );
                //$use_closed = true;
            } else {
                $task_end_date = $this->_contract->input['_projecttask_end_date'];
            }
        }
        if($use_input_data && empty($this->_contract->input['_projecttask_begin_date']) ) {
             $task_start_date = date('Y-m-d', strtotime($this->_contract->input['_projecttask_begin_date']));
        }

        $nb_hours = $this->getNbHours();
        $nb_hours_to_use = $nb_hours;
        $delta_hours_to_use = 0;
        $consumption = self::getProjectTaskConsumption($this->getProjectId(), $use_closed);

        if ($consumption > $nb_hours) {
            $delta_hours_to_use = $consumption - $nb_hours;
            // cas ou le temps est dépassé mais pas la date
            if($open_exists && !$use_input_data) {
                $now = new \DateTime();
                $previous_task_end_object = new \DateTime($previous_task_end);
                if($now < $previous_task_end_object) {
                   $task_start_date = date('Y-m-d'); 
                }
            }
            
        }

        if (!empty($this->_contract->input['projectbridge_nb_hours_to_use'])) {
            $nb_hours_to_use = $this->_contract->input['projectbridge_nb_hours_to_use'];
        }
        
        $duration = $this->_contract->getField('duration');

        $renewal_data = [
          'begin_date' => $task_start_date,
          'end_date' => $task_end_date,
          'nb_hours_to_use' => $nb_hours_to_use,
          'delta_hours_to_use' => $delta_hours_to_use,
          'duration' => $duration,
          'consumption' => $consumption
        ];

        return $renewal_data;
    }
    

    /**
     * Type name for cron
     *
     * @param  integer $nb
     * @return string
     */
    public static function getTypeName($nb = 0)
    {
        return 'ProjectBridge';
    }
   

    /**
     * Get the contracts to renew
     *
     * @return array
     */
    public static function getContractsToRenew()
    {
        global $DB;

        // todo: use Contract::find()
        $get_contracts_query = "
            SELECT
                id
            FROM
                glpi_contracts
            WHERE TRUE
                AND is_deleted = 0
                AND is_template = 0
        ";

        $result = $DB->query($get_contracts_query);
        $contracts = [];

        if ($result) {
            while ($row = $DB->fetch_assoc($result)) {
                $contract = new Contract();
                $contract->getFromDB($row['id']);
                
                $bridge_contract = new PluginProjectbridgeContract($contract);
                $project_id = $bridge_contract->getProjectId();
                $project = new Project();
                $state_closed_value = PluginProjectbridgeState::getProjectStateIdByStatus('closed');
                $project->getFromDB($project_id);
                //if ($project && $project->fields['projectstates_id'] != $state_closed_value && !self::getProjectTaskOject($project_id, false) && self::getProjectTaskOject($project_id, true) ) { 
                if ($project && $project->fields['projectstates_id'] != $state_closed_value) {
                    $now =  new DateTime();
                    $planEndDate = self::getContractPlanEndDate($contract);
                    $nb_hours = $bridge_contract->getNbHours();
                    
                    
                    // search open projectTask
                    $projectTask = self::getProjectTaskOject($project_id, false);
                    // search close projecttask
                    if(!$projectTask){
                        $projectTask = self::getProjectTaskOject($project_id, true);
                    }
                    $consumption = 0;
                    if($projectTask){
                        $consumption = self::getTicketsTotalActionTime($projectTask->getField('id'))/3600;
                    }
                    if($consumption>=$nb_hours || $planEndDate <= $now)
                    {
                        $contracts[$contract->getId()] = [
                          'contract' => $contract,
                        ];
                    }
                }
            }
        }

        return $contracts;
    }

    /**
     * Display HTML after project has been shown
     *
     * @param  Project $project
     * @return void
     */
    public static function postShowProject(Project $project)
    {
        $project_id = $project->getId();

        if (!empty($project_id)) {
            $bridge_contract = new PluginProjectbridgeContract();
            $contract_bridges = $bridge_contract->find(['project_id' => $project_id]);

            $html_parts = [];
            $html_parts[] = '<div class="spaced">' . "\n";

            if (!empty($contract_bridges)) {
                global $CFG_GLPI;
                $contract_url = rtrim($CFG_GLPI['root_doc'], '/') . '/front/contract.form.php?id=';

                foreach ($contract_bridges as $contract_bridge_data) {
                    $contract = new Contract();

                    if ($contract->getFromDB($contract_bridge_data['contract_id'])) {
                        $html_parts[] = '<a href="' . $contract_url . $contract->getId() . '" target="_blank">';
                        $html_parts[] = __('Access to linked contract', 'projectbridge') . ' "' . $contract->fields['name'] . '"';
                        $html_parts[] = '</a>';
                    } else {
                        $html_parts[] = __('Link to contract nonexistent', 'projectbridge') . ' : ' . __('Access to linked contract', 'projectbridge') . ' n°' . $contract->getId();
                    }
                }
            } else {
                $html_parts[] = __('No linked contract', 'projectbridge');
            }

            $html_parts[] = '</div>' . "\n";

            echo implode(' ', $html_parts);
        }
    }
    
    /**
     * fonction retournant le format utilisé dans la configuration de GLPI pour les affichages de dates
     * @return string
     */
    private function getDateFormat() {

        switch ($_SESSION['glpidate_format']) {
                case "0":
                default:    
                    $dataf = 'Y-m-d'; 
                    break;
                case "1": 
                    $dataf = 'd-m-Y'; 
                    break;
                case "2": 
                    $dataf = 'm-d-Y'; 
                    break;    
        }
        
        return $dataf;
       
    }
    

    
}
