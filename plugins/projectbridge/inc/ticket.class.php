<?php

class PluginProjectbridgeTicket extends CommonDBTM {

    private $_ticket;
    private $_project_id;
    public static $table_name = 'glpi_plugin_projectbridge_tickets';

    /**
     * Constructor
     *
     * @param Ticket $ticket
     */
    public function __construct(Ticket $ticket = null) {
        $this->_ticket = $ticket;
    }

    /**
     * Get the id of the contract linked to the ticket
     *
     * @param void
     * @return integer|null
     */
    public function getProjectId() {
        if ($this->_project_id === null) {
            $result = $this->getFromDBByCrit(['ticket_id' => $this->_ticket->getId()]);

            if ($result) {
                $this->_project_id = (int) $this->fields['project_id'];
            }
        }

        return $this->_project_id;
    }

    /**
     * Show HTML after ticket's link with project tasks has been shown
     *
     * @param  Ticket $ticket
     * @return void
     */
    public static function postShow(Ticket $ticket) {
        


        global $CFG_GLPI;

        $project_list = PluginProjectbridgeTicket::_getProjectList();

        $bridge_ticket = new PluginProjectbridgeTicket($ticket);
        $project_id = $bridge_ticket->getProjectId();

        if (!$project_id) {
            // no link between ticket and project in DB, get the contract for the current entity

            $entity = new Entity();
            $entity->getFromDB($_SESSION['glpiactive_entity']);
            $bridge_entity = new PluginProjectbridgeEntity($entity);
            $contract_id = $bridge_entity->getContractId();

            if ($contract_id) {
                // default contract found, let's find the linked project

                $contract = new Contract();
                $contract->getFromDB($contract_id);
                $bridge_contract = new PluginProjectbridgeContract($contract);
                $project_id = $bridge_contract->getProjectId();

                if (!isset($project_list[$project_id])) {
                    // project does not exist anymore
                    $project_id = null;
                }
            } else {
                $project_id = null;
            }
        }

        if (empty($project_id) || !isset($project_list[$project_id])
        ) {
            $project_id = null;
        }

        $project_config = [
          'value' => $project_id,
          'values' => $project_list,
          'display' => false,
        ];
        
        $html_parts = [];
        $html_parts[] = '<table>' . "\n";
        $html_parts[] = '<tr id="projectbridge_config">' . "\n";

        $html_parts[] = '<th>';
        $html_parts[] = __('Related project', 'projectbridge');
        $html_parts[] = '</th>' . "\n";

        $html_parts[] = '<th colspan="5">' . "\n";
        $html_parts[] = '<form method="post" action="' . $CFG_GLPI['root_doc'] . '/front/ticket.form.php?id=' . $ticket->getId() . '">' . "\n";
        $html_parts[] = Dropdown::showFromArray('projectbridge_project_id', $project_list, $project_config);

        if (!empty($project_id)) {
            $html_parts[] = '<a href="' . $CFG_GLPI['root_doc'] . '/front/project.form.php?id=' . $project_id . '" style="margin-left: 10px" target="_blank">';
            $html_parts[] = __('Access to linked project', 'projectbridge');
            $html_parts[] = '</a>' . "\n";
        }

        $html_parts[] = '<input type="submit" name="update" value="' . __('Make the connection', 'projectbridge') . '" class="submit" style="float: right; margin-left: 10px" />' . "\n";
        $html_parts[] = '<input type="hidden" name="id" value="' . $ticket->getId() . '" />' . "\n";

        $html_parts[] = Html::closeForm(false);
        $html_parts[] = '</th>' . "\n";


        $html_parts[] = '<th colspan="4">&nbsp;</th>' . "\n";

        $html_parts[] = '</tr>' . "\n";
        $html_parts[] = '</table>' . "\n";

        $html_parts[] = Html::scriptBlock('$(document).ready(function() {
            var
                projectbridge_config = $("#projectbridge_config"),
                tab = $("#ui-tabs-8"),
                table = $(".tab_cadre_fixehov tr:last", tab)
            ;

            if (table.length == 0) {
                table = $("#ui-tabs-8 form[id^=projecttaskticket_form]");
            }

            table.after(projectbridge_config.clone());
            projectbridge_config.remove();

            $("#projectbridge_config .select2-container").remove();
            $("#projectbridge_config select").select2({
                width: \'\',
                dropdownAutoWidth: true
            });
            $("#projectbridge_config .select2-container").show();
            $("#projectbridge_config").before("<tr><td colspan=\'10\'>&nbsp;</td></tr>");

            // remove the GLPI "add to project" default
            $("form[id^=projecttaskticket_form]", tab).remove();

            projectbridge_config = $("#projectbridge_config");

            if (!projectbridge_config.parent().is("table, tbody")) {
                projectbridge_config.wrap("<table class=\"tab_cadre_fixehov\"></table>");
            }
        });');

        echo implode('', $html_parts);
    }

    /**
     * Get list of projects
     *
     * @return array
     */
    private static function _getProjectList() {
        $search_filters = ['is_deleted' => 0];

        if (!empty($_SESSION['glpiactiveentities'])) {
            $search_filters['entities_id'] = $_SESSION['glpiactiveentities'];
        }

        $project = new Project();
        $project_results = $project->find( $search_filters);
        $project_list = [
          null => Dropdown::EMPTY_VALUE,
        ];

        foreach ($project_results as $project_data) {
            if (PluginProjectbridgeContract::getProjectTaskOject($project_data['id'])) {
                $project_list[$project_data['id']] = $project_data['name'] . ' (' . $project_data['id'] . ')';
            }
        }

        return $project_list;
    }

    /**
     * Show HTML after tickets linked to a task have been shown
     *
     * @param  ProjectTask $project_task
     * @return void
     */
    public static function postShowTask(ProjectTask $project_task) {
        global $CFG_GLPI;
        $onlypublicTasks = PluginProjectbridgeConfig::getConfValueByName('CountOnlyPublicTasks');

        $get_tickets_actiontime_url = PLUGIN_PROJECTBRIDGE_WEB_DIR . '/ajax/get_tickets_actiontime.php';
        $js_block = '
            //debugger;
            var
                current_table_cell,
                table_parent,
                ticket_id,
                ticket_ids = []
            ;

            $(".tab_cadre_fixehov tr", "form[id^=massProjectTask_Ticket]").each(function() {
                
                current_table_cell = $("td.left:nth-child(2)", this);
                console.log(current_table_cell);

                if (current_table_cell.length) {
                    if (table_parent === undefined) {
                        table_parent = current_table_cell.parents("table");
                    }

                    ticket_id = getTicketIdFromCell(current_table_cell);

                    if (ticket_id) {
                        ticket_ids.push(ticket_id);
                    }
                }
            });

            if (ticket_ids.length) {
                $.ajax("' . $get_tickets_actiontime_url . '", {
                    method: "POST",
                    cache: false,
                    data: {
                        ticket_ids: ticket_ids
                    }
                }).done(function(response, status) {
                    if (
                        status == "success"
                        && response.length
                    ) {
                        try {
                            var
                                tickets_actiontime = $.parseJSON(response),
                                current_row,
                                current_table_cell,
                                current_ticket_id,
                                current_action_time
                            ;

                            $("tr", table_parent).each(function(idx, elm) {
                                current_row = $(elm);

                                if (idx > 1) {
                                    current_table_cell = $("td.left:nth-child(2)", current_row);
                                    current_ticket_id = getTicketIdFromCell(current_table_cell);
                                    current_action_time = 0;
                                    private_action_time = 0;

                                    if (tickets_actiontime[current_ticket_id] !== undefined) {
                                        current_action_time = tickets_actiontime[current_ticket_id]["totalDuration"];
                                        private_action_time = tickets_actiontime[current_ticket_id]["privateDuration"];
                                    }
                                    if(private_action_time > 0) {
                                        current_row.append("<td>" + current_action_time + " heure(s) dont " + private_action_time + " heures privées</td>");
                                    }else {
                                        current_row.append("<td>" + current_action_time + " heure(s) </td>");
                                    }
                                } else if (idx == 0) {
                                    current_table_cell = $("th", current_row);
                                    current_table_cell.attr("colspan", parseInt(current_table_cell.attr("colspan")) + 1);
                                } else if (idx == 1) {
                                    current_row.append("<th>Durée</th>");
                                }
                            });
                        } catch (e) {
                        }
                    }
                });
            }

            /**
             * Get the ticket ID contained in the table table_cell
             *
             * @param jQueryObject table_cell
             * @return void
             */
            function getTicketIdFromCell(table_cell)
            {
                return parseInt($.trim(table_cell.text()).replace("ID : ", ""));
            };
        ';

        echo Html::scriptBlock($js_block);
    }

    /**
     * Delete project links from ticket
     *
     * @param  int $ticket_id
     * @return void
     */
    public static function deleteProjectLinks($ticket_id) {
        global $DB;

        // use a query as ProjectTask_Ticket can only get one item and does not return the number
        $get_nb_links_query = "
            SELECT
                COUNT(1) AS nb_links
            FROM
                glpi_projecttasks_tickets
            WHERE tickets_id = " . $ticket_id . "
        ";

        $result = $DB->query($get_nb_links_query);

        if ($result && $DB->numrows($result)
        ) {
            $results = $DB->fetch_assoc($result);
            $nb_links = (int) $results['nb_links'];
        } else {
            $nb_links = 0;
        }

        if ($nb_links != 0) {
            // todo: use a ProjectTask_Ticket method
            $delete_links_query = "
                DELETE FROM
                    glpi_projecttasks_tickets
                WHERE tickets_id = " . $ticket_id . "
            ";

            $DB->query($delete_links_query);
            Log::history($ticket_id, 'Ticket', [0, '', __('Link(s) with project task(s) deleted', 'projectbridge')], 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);
        }

        // todo: use a native method
        $delete_bridge_links_query = "
            DELETE FROM
                " . PluginProjectbridgeTicket::$table_name . "
            WHERE ticket_id = " . $ticket_id . "
        ";

        $DB->query($delete_bridge_links_query);
    }

    /**
     * Show form for given massive action
     *
     * @param  MassiveAction $ma
     * @return boolean
     */
    public static function showMassiveActionsSubForm(MassiveAction $ma) {
        
        $return = false;
        global $DB;
        
        switch ($ma->getAction()) {
            case 'deleteProjectLink':
                echo '&nbsp;';
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                $return = true;
                break;

            case 'addProjectLink':
                $project_list = PluginProjectbridgeTicket::_getProjectList();
                $project_config = [
                  'value' => null,
                  'values' => $project_list,
                ];

                Dropdown::showFromArray('projectbridge_project_id', $project_list, $project_config);
                echo '<br />';
                echo '<br />';
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                $return = true;
                break;

            case 'addProjectTaskLink':
                $search_filters = [];

                if (!empty($_SESSION['glpiactiveentities'])) {
                    $search_filters['entities_id'] =  $_SESSION['glpiactiveentities'];
                }

                $project_task = new ProjectTask();
                $project_task_results = $project_task->find($search_filters, 'entities_id ASC');
                $project_task_list = [
                  null => Dropdown::EMPTY_VALUE,
                ];

                foreach ($project_task_results as $project_task_data) {
                    $project = new Project();

                    if ($project->getFromDB($project_task_data['projects_id'])) {
                        $entry_name_parts = [
                          $project->fields['name'] . ' (' . $project->getId() . ')',
                          $project_task_data['name'] . ' (' . $project_task_data['id'] . ')'
                        ];

                        $project_task_list[$project_task_data['id']] = implode(' - ', $entry_name_parts);
                    }
                }

                $project_task_config = [
                  'value' => null,
                  'values' => $project_task_list,
                ];
                
                // pre selection auto contrat par defaut entité en cours
                $criteria = ['entity_id' =>$_SESSION['glpiactive_entity']];
                $req = $DB->request(PluginProjectbridgeEntity::$table_name, $criteria);
                if ($row = $req->next()) {
                    $contract_id = $row['contract_id'];
                    
                    $pluginProjectbridgeContract = new PluginProjectbridgeContract();
                   
                    $req2 = $DB->request(PluginProjectbridgeContract::$table_name, ['contract_id' => $contract_id]);
                    if ($row = $req2->next()) {
                        $projectId= $row['project_id'];
                        $tasks = $pluginProjectbridgeContract::getAllActiveProjectTasksForProject($projectId);
                        if(count($tasks)){
                            $projectTaskId = $tasks[0]['id'];
                            $project_task_config['value'] = $projectTaskId;
                          }
                    }
                    
                }
                
                Dropdown::showFromArray('projectbridge_projecttask_id', $project_task_list, $project_task_config);
                echo '<br />';
                echo '<br />';
                echo Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                $return = true;
                break;

            default:
            // nothing to do
        }
        if(!$return) {
            return parent::showMassiveActionsSubForm($ma);
        }
        
        return $return;
    }

    /**
     * Process a massive action
     *
     * @param  MassiveAction $ma
     * @param  CommonDBTM    $item
     * @param  array         $ids Item ids
     * @return void
     */
    public static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
        $caseFinded = false;
        switch ($ma->getAction()) {
            case 'deleteProjectLink':
                if ($item->getType() == 'Ticket') {
                    foreach ($ids as $ticket_id) {
                        $ticket = new Ticket();

                        if ($ticket->getFromDB($ticket_id)) {
                            PluginProjectbridgeTicket::deleteProjectLinks($ticket_id);
                            $ma->itemDone($item->getType(), $ticket_id, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $ticket_id, MassiveAction::ACTION_KO);
                        }
                    }
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }

                $caseFinded = true;
                break;

            case 'addProjectLink':
                if ($item->getType() == 'Ticket' && !empty($ma->POST['projectbridge_project_id'])
                ) {
                    $project_id = (int) trim($ma->POST['projectbridge_project_id']);
                    $project = new Project();

                    if ($project->getFromDB($project_id) && PluginProjectbridgeContract::getProjectTaskOject($project_id)) {

                        $task_id = PluginProjectbridgeContract::getProjectTaskFieldValue($project_id, false, 'id');

                        foreach ($ids as $ticket_id) {
                            $ticket = new Ticket();

                            if ($ticket->getFromDB($ticket_id)) {
                                PluginProjectbridgeTicket::deleteProjectLinks($ticket_id);

                                // link the task to the ticket
                                $project_task_link_ticket = new ProjectTask_Ticket();
                                $project_task_link_ticket->add([
                                  'projecttasks_id' => $task_id,
                                  'tickets_id' => $ticket_id,
                                ]);

                                $ma->itemDone($item->getType(), $ticket_id, MassiveAction::ACTION_OK);
                            } else {
                                $ma->itemDone($item->getType(), $ticket_id, MassiveAction::ACTION_KO);
                            }
                        }
                    } else {
                        $ma->itemDone($item->getType(), $ticket_id, MassiveAction::ACTION_KO);
                    }
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }

                $caseFinded = true;
                break;

            case 'addProjectTaskLink':
                if ($item->getType() == 'Ticket' && !empty($ma->POST['projectbridge_projecttask_id'])
                ) {
                    $project_task_id = (int) trim($ma->POST['projectbridge_projecttask_id']);
                    $project_task = new ProjectTask();

                    if ($project_task->getFromDB($project_task_id)) {
                        foreach ($ids as $ticket_id) {
                            $ticket = new Ticket();

                            if ($ticket->getFromDB($ticket_id)) {
                                PluginProjectbridgeTicket::deleteProjectLinks($ticket_id);

                                // link the task to the ticket
                                $project_task_link_ticket = new ProjectTask_Ticket();
                                $project_task_link_ticket->add([
                                  'projecttasks_id' => $project_task_id,
                                  'tickets_id' => $ticket_id,
                                ]);

                                $ma->itemDone($item->getType(), $ticket_id, MassiveAction::ACTION_OK);
                            } else {
                                $ma->itemDone($item->getType(), $ticket_id, MassiveAction::ACTION_KO);
                            }
                        }
                    } else {
                        $ma->itemDone($item->getType(), $ticket_id, MassiveAction::ACTION_KO);
                    }
                } else {
                    $ma->itemDone($item->getType(), $ids, MassiveAction::ACTION_KO);
                }

                $caseFinded = true;
                break;

            default:
            // nothing to do
        }
        if(!$caseFinded) {
            parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
        }
        return;
    }

}
