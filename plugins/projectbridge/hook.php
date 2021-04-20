<?php

/**
 * Install the plugin
 *
 * @return boolean
 */
function plugin_projectbridge_install() {
    global $DB;

    if (!$DB->tableExists(PluginProjectbridgeEntity::$table_name)) {
        $create_table_query = "
            CREATE TABLE IF NOT EXISTS `" . PluginProjectbridgeEntity::$table_name . "`
            (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `entity_id` INT(11) NOT NULL,
                `contract_id` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                INDEX (`entity_id`)
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB
        ";
        $DB->query($create_table_query) or die($DB->error());
    }

    if (!$DB->tableExists(PluginProjectbridgeContract::$table_name)) {
        $create_table_query = "
            CREATE TABLE IF NOT EXISTS `" . PluginProjectbridgeContract::$table_name . "`
            (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `contract_id` INT(11) NOT NULL,
                `project_id` INT(11) NOT NULL,
                `nb_hours` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                INDEX (`contract_id`)
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB
        ";
        $DB->query($create_table_query) or die($DB->error());
    }

    if (!$DB->tableExists(PluginProjectbridgeTicket::$table_name)) {
        $create_table_query = "
            CREATE TABLE IF NOT EXISTS `" . PluginProjectbridgeTicket::$table_name . "`
            (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `ticket_id` INT(11) NOT NULL,
                `project_id` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                INDEX (`ticket_id`)
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB
        ";
        $DB->query($create_table_query) or die($DB->error());
    }

    // configs datatable
    $create_tableConfig_query = "
            CREATE TABLE IF NOT EXISTS `" . PluginProjectbridgeConfig::$table_name . "`
            (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(50) NOT NULL ,
                `value` VARCHAR(50) NOT NULL,
                PRIMARY KEY (`id`)
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB
        ";
    if (!$DB->tableExists(PluginProjectbridgeConfig::$table_name)) {
        $DB->query($create_tableConfig_query) or die($DB->error());
        $insert_table_query = "INSERT INTO `" . PluginProjectbridgeConfig::$table_name . "` (`id`, `name`, `value`) VALUES
            (1, 'RecipientIds', '[]'),
            (2, 'CountOnlyPublicTasks', '1');";
        $DB->query($insert_table_query) or die($DB->error());
    } else {
        // test if old version of glpi_plugin_projectbridge_configs      
        $fields = $DB->list_fields(PluginProjectbridgeConfig::$table_name);
        if (array_key_exists('user_id', $fields)) {
            // save old values of user_id
            
            $userIds = [];
            $req = $DB->request([
              'SELECT' => ['user_id'],
              'FROM' => PluginProjectbridgeConfig::$table_name,
            ]);
            foreach ($req as $row) {
                $userIds[] = (int) $row['user_id'];
            }
            // delete old table
            $DB->queryOrDie(
                    "DROP TABLE `" . PluginProjectbridgeConfig::$table_name . "`",
                    $DB->error()
            );
            // create table with new format
            $DB->query($create_tableConfig_query) or die($DB->error());
            // insert values
            $insert_table_query = "INSERT INTO `" . PluginProjectbridgeConfig::$table_name . "` (`id`, `name`, `value`) VALUES
            (1, 'RecipientIds', '" . json_encode(array_unique($userIds)) . "'),
            (2, 'CountOnlyPublicTasks', '1');";
            $DB->query($insert_table_query) or die($DB->error());
        }
    }

    if (!$DB->tableExists(PluginProjectbridgeState::$table_name)) {
        $create_table_query = "
            CREATE TABLE IF NOT EXISTS `" . PluginProjectbridgeState::$table_name . "`
            (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `status` VARCHAR(250) NOT NULL,
                `projectstates_id` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                INDEX (`status`)
            )
            COLLATE='utf8_unicode_ci'
            ENGINE=InnoDB
        ";
        $DB->query($create_table_query) or die($DB->error());
    }
    
    // clean old crontask
    if (version_compare(PLUGIN_PROJECTBRIDGE_VERSION, '2.2.3', '<')) {
        //$update_crontask_table = "UPDATE ".Crontask::getTable()." SET itemtype='PluginProjectbridgeTask' WHERE itemtype='PluginProjectbridgeContract' AND name='AlertContractsToRenew'";
        //$DB->query($update_crontask_table) or die($DB->error());
        $delete_crontask_table = "DELETE FROM ".Crontask::getTable()."  WHERE itemtype='PluginProjectbridgeContract' AND name='AlertContractsToRenew'";
        $DB->query($delete_crontask_table) or die($DB->error());
    }

    // cron for alerts
    CronTask::Register('PluginProjectbridgeTask', 'AlertContractsToRenew', DAY_TIMESTAMP);

    // cron to process tasks (expired, quota reached, ...)
    CronTask::Register('PluginProjectbridgeTask', 'ProcessTasks', DAY_TIMESTAMP);

    // cron to update the percent_done counter in tasks
    CronTask::Register('PluginProjectbridgeTask', 'UpdateProgressPercent', DAY_TIMESTAMP);

    return true;
}

/**
 * Uninstall the plugin
 *
 * @return boolean
 */
function plugin_projectbridge_uninstall() {
    global $DB;
    
    // clean crontasks infos
    $clear_crontaksInfos_query = "DELETE FROM ".CronTask::getTable()." WHERE itemtype LIKE 'PluginProjectbridge%'";
    $DB->query($clear_crontaksInfos_query) or die($DB->error());
    //Crontask::unregister('Projectbridge');

    $tables_to_drop = [
      PluginProjectbridgeEntity::$table_name,
      PluginProjectbridgeContract::$table_name,
      PluginProjectbridgeTicket::$table_name,
      PluginProjectbridgeConfig::$table_name,
      PluginProjectbridgeState::$table_name,
    ];

    $drop_table_query = "DROP TABLE IF EXISTS `" . implode('`, `', $tables_to_drop) . "`";

    return $DB->query($drop_table_query) or die($DB->error());
}

/**
 * Hook called after showing an item
 *
 * @param array $post_show_data
 * @return void
 */
function plugin_projectbridge_post_show_item(array $post_show_data) {
    if (!empty($post_show_data['item']) && is_object($post_show_data['item'])
    ) {
        switch (get_class($post_show_data['item'])) {
            case 'Entity':
                PluginProjectbridgeEntity::postShow($post_show_data['item']);
                break;

            case 'Contract':
                PluginProjectbridgeContract::postShow($post_show_data['item']);
                break;

            case 'Project':
                PluginProjectbridgeContract::postShowProject($post_show_data['item']);
                break;

            default:
            // nothing to do
        }
    }
}

/**
 * Hook called before the update of an entity
 *
 * @param Entity $entity
 * @param boolean $force (optional)
 * @return void|integer|boolean
 */
function plugin_projectbridge_pre_entity_update(Entity $entity, $force = false) {
    if ((
            $force === true || $entity->canUpdate()
            ) && isset($entity->input['projectbridge_contract_id'])
    ) {
        if (empty($entity->input['projectbridge_contract_id'])) {
            $selected_contract_id = 0;
        } else {
            $selected_contract_id = (int) $entity->input['projectbridge_contract_id'];
        }

        $bridge_entity = new PluginProjectbridgeEntity($entity);
        $contract_id = $bridge_entity->getContractId();

        $post_data = [
          'entity_id' => $entity->getId(),
          'contract_id' => $selected_contract_id,
        ];

        if ($contract_id === null) {
            return $bridge_entity->add($post_data);
        } else if ($selected_contract_id != $contract_id) {
            $post_data['id'] = $bridge_entity->getId();
            return $bridge_entity->update($post_data);
        }
    }
}

/**
 * Hook called before the update of a contract
 *
 * @param Contract $contract
 * @return void
 */
function plugin_projectbridge_pre_contract_update(Contract $contract) {
    if ($contract->canUpdate() && isset($contract->input['update']) && isset($contract->input['projectbridge_project_id'])
    ) {
        if ($contract->input['update'] != 'Lier les tickets au renouvellement') {
            // update contract

            $nb_hours = 0;

            if (empty($contract->input['projectbridge_project_id'])) {
                $selected_project_id = 0;
            } else {
                $selected_project_id = (int) $contract->input['projectbridge_project_id'];

                if (!empty($contract->input['projectbridge_project_hours']) && $contract->input['projectbridge_project_hours'] > 0
                ) {
                    $nb_hours = (int) $contract->input['projectbridge_project_hours'];
                }
            }

            if ($selected_project_id > 0) {
                $bridge_contract = new PluginProjectbridgeContract($contract);
                $project_id = $bridge_contract->getProjectId();

                $post_data = [
                  'contract_id' => $contract->getId(),
                  'project_id' => $selected_project_id,
                  'nb_hours' => $nb_hours,
                ];

                if (empty($project_id)) {
                    $bridge_contract->add($post_data);
                } else {
                    $post_data['id'] = $bridge_contract->getId();
                    $bridge_contract->update($post_data);
                }
            }
        } else {
            // renew the task of the project linked to the contract

            if (empty($contract->input['_projecttask_begin_date']) || empty($contract->input['_projecttask_end_date']) || empty($contract->input['projectbridge_nb_hours_to_use'])
            ) {
                Session::addMessageAfterRedirect('Veuillez remplir tous les champs de renouvellement.', false, ERROR);
                return false;
            }

            $bridge_contract = new PluginProjectbridgeContract($contract);
            $bridge_contract->renewProjectTask();
        }
    }
}

/**
 * Hook called after the creation of a contract
 *
 * @param Contract $contract
 * @param boolean $force (optional)
 * @return boolean|void
 */
function plugin_projectbridge_contract_add(Contract $contract, $force = false) {
    if ($force === true || (
            $contract->canUpdate() && isset($contract->input['projectbridge_create_project']) && $contract->input['projectbridge_create_project']
            )
    ) {
        $nb_hours = 0;

        if (!empty($contract->input['projectbridge_project_hours']) && $contract->input['projectbridge_project_hours'] > 0
        ) {
            $nb_hours = (int) $contract->input['projectbridge_project_hours'];
        }

        $date_creation = '';
        $begin_date = '';

        if (!empty($contract->fields['begin_date']) && $contract->fields['begin_date'] != 'NULL'
        ) {
            $begin_date = date('Y-m-d H:i:s', strtotime($contract->fields['begin_date']));
        }

        if (empty($begin_date)) {
            Session::addMessageAfterRedirect('Le contrat n\'a pas de date de début. Le projet n\'a pas pu être créé.', false, ERROR);
            return false;
        }

        if (!empty($contract->fields['date_creation']) && $contract->fields['date_creation'] != 'NULL'
        ) {
            $date_creation = $contract->fields['date_creation'];
        } else if (!empty($contract->fields['date']) && $contract->fields['date'] != 'NULL'
        ) {
            $date_creation = $contract->fields['date'];
        } else {
            $date_creation = $begin_date;
        }

        if (!empty($date_creation)) {
            $date_creation = date('Y-m-d H:i:s', strtotime($date_creation));
        }

        $project_data = [
          // data from contract
          'name' => $contract->input['name'],
          'entities_id' => $contract->fields['entities_id'],
          'is_recursive' => $contract->fields['is_recursive'],
          'content' => addslashes($contract->fields['comment']),
          'date' => $date_creation,
          'date_mod' => $date_creation,
          'date_creation' => $date_creation,
          'plan_start_date' => $begin_date,
          // standard data to bootstrap project
          'comment' => '',
          'code' => '',
          'priority' => 3,
          'projectstates_id' => 0,
          'projecttypes_id' => 0,
          'users_id' => 0,
          'groups_id' => 0,
          'plan_end_date' => '',
          'real_start_date' => '',
          'real_end_date' => '',
          'percent_done' => 0,
          'show_on_global_gantt' => 0,
          'is_deleted' => 0,
          'projecttemplates_id' => 0,
          'is_template' => 0,
          'template_name' => '',
        ];

        $state_in_progress_value = PluginProjectbridgeState::getProjectStateIdByStatus('in_progress');

        if (empty($state_in_progress_value)) {
            Session::addMessageAfterRedirect('La correspondance pour le statut "En cours" n\'a pas été définie. Le projet n\'a pas pu être créé.', false, ERROR);
            return false;
        }

        // create the project
        $project = new Project();
        $project_id = $project->add($project_data);

        if ($project_id) {
            $bridge_data = [
              'contract_id' => $contract->getId(),
              'project_id' => $project_id,
              'nb_hours' => $nb_hours,
            ];

            // link the project to the contract
            $bridge_contract = new PluginProjectbridgeContract($contract);
            $bridge_contract->add($bridge_data);

            $project_task_data = [
              // data from contract
              'name' => date('Y-m'),
              'entities_id' => $contract->fields['entities_id'],
              'is_recursive' => $contract->fields['is_recursive'],
              'projects_id' => $project_id,
              'content' => addslashes($contract->fields['comment']),
              'plan_start_date' => $begin_date,
              'plan_end_date' => (
              !empty($begin_date) && !empty($contract->fields['duration']) ? date('Y-m-d H:i:s', strtotime(
                              Infocom::getWarrantyExpir($begin_date, $contract->fields['duration']) . ' - 1 day'
              )) : ''
              ),
              'planned_duration' => $nb_hours * 3600, // in seconds
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
              'comment' => '',
            ];

            // create the project's task
            $project_task = new ProjectTask();
            $project_task->add($project_task_data);

            return true;
        }
    }
}

/**
 * Hook called before the update of a ticket
 * If possible, link the ticket to the project task of the entity's default contract
 * If requested link the ticket to a specific project's task and set the project as default
 *
 * @param  Ticket $ticket
 * @return void
 */
function plugin_projectbridge_ticket_update(Ticket $ticket) {
    if (!empty($ticket->input['update']) && $ticket->input['update'] == 'Faire la liaison' && !empty($ticket->input['projectbridge_project_id'])
    ) {
        $is_project_link_update = true;
        $contract_id = null;
    } else {
        $is_project_link_update = false;

        $entity = new Entity();
        $entity->getFromDB($ticket->fields['entities_id']);

        $bridge_entity = new PluginProjectbridgeEntity($entity);
        $contract_id = $bridge_entity->getContractId();
    }

    if ($is_project_link_update || $contract_id
    ) {
        // default contract for the entity found or update

        if (!$is_project_link_update) {
            $contract = new Contract();
            $contract->getFromDB($contract_id);

            $contract_bridge = new PluginProjectbridgeContract($contract);
            $project_id = $contract_bridge->getProjectId();
        } else {
            $project_id = (int) $ticket->input['projectbridge_project_id'];
        }

        if ($project_id && PluginProjectbridgeContract::getProjectTaskOject($project_id)
        ) {
            // project linked to contract found & task exists

            PluginProjectbridgeTicket::deleteProjectLinks($ticket->getId());

            $task_id = PluginProjectbridgeContract::getProjectTaskFieldValue($project_id, false, 'id');

            // link the task to the ticket
            $project_task_link_ticket = new ProjectTask_Ticket();
            $project_task_link_ticket->add([
              'projecttasks_id' => $task_id,
              'tickets_id' => $ticket->getId(),
            ]);

            if ($is_project_link_update) {
                $bridge_ticket = new PluginProjectbridgeTicket($ticket);

                if ($bridge_ticket->getProjectId() > 0) {
                    $bridge_ticket->update([
                      'id' => $bridge_ticket->getId(),
                      'project_id' => $project_id,
                    ]);
                } else {
                    $bridge_ticket->add([
                      'ticket_id' => $ticket->getId(),
                      'project_id' => $project_id,
                    ]);
                }
            }
        }
    }
}

/**
 * Hook called after the creation of a ticket task
 * If possible, update the linked project task's progress percentage
 *
 * @param  TicketTask $ticket_task
 * @return void
 */
function plugin_projectbridge_ticketask_add(TicketTask $ticket_task) {
    if (isset($ticket_task->fields['actiontime'])) {
        // no timediff needed because it's already in DB
        PluginProjectbridgeTask::updateProgressPercent((int) $ticket_task->fields['tickets_id']);
    }
}

/**
 * Hook called before the update of a ticket task
 * If possible, update the linked project task's progress percentage
 *
 * @param  TicketTask $ticket_task
 * @return void
 */
function plugin_projectbridge_ticketask_update(TicketTask $ticket_task) {
    if (isset($ticket_task->fields['actiontime']) && isset($ticket_task->input['actiontime'])
    ) {
        $timediff = $ticket_task->input['actiontime'] - $ticket_task->fields['actiontime'];
        PluginProjectbridgeTask::updateProgressPercent((int) $ticket_task->fields['tickets_id'], (int) $timediff);
    }
}

/**
 * Hook called after showing a tab
 *
 * @param  array $tab_data
 * @return void
 */
function plugin_projectbridge_post_show_tab(array $tab_data) {
    if (!empty($tab_data['item']) && is_object($tab_data['item']) && !empty($tab_data['options']['itemtype'])
    ) {
        if ($tab_data['options']['itemtype'] == 'Projecttask_Ticket' || $tab_data['options']['itemtype'] == 'ProjectTask_Ticket'
        // naming is not uniform: https://github.com/glpi-project/glpi/issues/4177
        ) {
            if ($tab_data['item'] instanceof Ticket) {
                // add a line to allow linking ticket to a project task
                PluginProjectbridgeTicket::postShow($tab_data['item']);
            } else if ($tab_data['item'] instanceof ProjectTask) {
                // add data to the list of tickets linked to a project task
                PluginProjectbridgeTicket::postShowTask($tab_data['item']);
            }
        } else if ($tab_data['options']['itemtype'] == 'ProjectTask' && $tab_data['item'] instanceof Project
        ) {
            // add a link to the linked contract after showing the list of tasks in a project
            PluginProjectbridgeContract::postShowProject($tab_data['item']);

            // customize the duration columns
            PluginProjectbridgeTask::customizeDurationColumns($tab_data['item']);
        }
    }
}

/**
 * Add new search options
 *
 * @param string $itemtype
 * @return array
 */
function plugin_projectbridge_getAddSearchOptionsNew($itemtype) {
    $options = [];

    switch ($itemtype) {
        case 'Entity':
            $options[] = [
              'id' => 4200,
              'name' => 'ProjectBridge',
            ];

            $options[] = [
              'id' => 4201,
              'table' => PluginProjectbridgeEntity::$table_name,
              // trick GLPI search into thinking we want the contract id so the addSelect function is called
              'field' => 'contract_id',
              'name' => 'Contrat par défaut',
              'massiveaction' => false,
            ];

            $options[] = [
              'id' => 4202,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Temps non affecté à une tâche (heures)',
              'massiveaction' => false,
            ];
            break;

        case 'Ticket':
            $options[] = [
              'id' => 4210,
              'name' => 'ProjectBridge',
            ];

            $options[] = [
              'id' => 4211,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Projet',
              'massiveaction' => false,
            ];

            $options[] = [
              'id' => 4212,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Tâche de projet',
              'massiveaction' => false,
            ];

            $options[] = [
              'id' => 4213,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Statut de tâche',
              'massiveaction' => false,
            
            ];

            $options[] = [
              'id' => 4202,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Temps non affecté à une tâche (heures)',
              'massiveaction' => false,
            ];
            break;

        case 'Ticket':
            $options[] = [
              'id' => 4210,
              'name' => 'ProjectBridge',
            ];

            $options[] = [
              'id' => 4211,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Projet',
              'massiveaction' => false,
            ];

            $options[] = [
              'id' => 4212,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Tâche de projet',
              'massiveaction' => false,
            ];

            $options[] = [
              'id' => 4213,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Statut de tâche',
              'massiveaction' => false,
            ];

            $options[] = [
              'id' => 4214,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Lié à une tâche ?',
              'massiveaction' => false,
            ];

            break;

        case 'Contract':
            $options[] = [
              'id' => 4220,
              'name' => 'ProjectBridge',
            ];

            $options[] = [
              'id' => 4221,
              'table' => PluginProjectbridgeContract::$table_name,
              'field' => 'project_id',
              'name' => 'Nom du projet',
              'massiveaction' => false,
            ];

            $options[] = [
              'id' => 4222,
              'table' => PluginProjectbridgeContract::$table_name,
              'field' => 'project_id',
              'name' => 'Tâche de projet',
              'massiveaction' => false,
            ];
            break;

        case 'projecttask':
            $options[] = [
              'id' => 4230,
              'name' => 'ProjectBridge',
            ];

            $options[] = [
              'id' => 4231,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Durée effective (heures)',
              'massiveaction' => false,
            ];

            $options[] = [
              'id' => 4232,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Durée planifiée (heures)',
              'massiveaction' => false,
            ];

            $options[] = [
              'id' => 4233,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Dernière tâche ?',
              'massiveaction' => false,
            ];

            $options[] = [
              'id' => 4234,
              'table' => PluginProjectbridgeTicket::$table_name,
              'field' => 'project_id',
              'name' => 'Statut du projet',
              'massiveaction' => false,
            ];

            break;

        case 'Project':
            $options[] = [
              'id' => 4230,
              'name' => 'ProjectBridge',
            ];

            $options[] = [
              'id' => 4231,
              'table' => PluginProjectbridgeContract::$table_name,
              'field' => 'project_id',
              'name' => 'Nombre de tâches',
              'massiveaction' => false,
            ];

            break;

        default:
        // nothing to do
    }

    return $options;
}

/**
 * Add a custom select part to search
 *
 * @param string $itemtype
 * @param string $key
 * @param integer $offset
 * @return string
 */
function plugin_projectbridge_addSelect($itemtype, $key, $offset) {
    global $CFG_GLPI;
    $select = "";

    switch ($itemtype) {
        case 'Entity':
            if ($key == 4201) {
                $contract_link = rtrim($CFG_GLPI['root_doc'], '/') . '/front/contract.form.php?id=';

                $select = "
                    (CASE
                        WHEN `" . PluginProjectbridgeEntity::$table_name . "`.`contract_id` IS NOT NULL
                            THEN CONCAT(
                                '<!--',
                                `glpi_contracts`.`name`,
                                '-->',

                                '<a href=\"" . $contract_link . "',
                                `" . PluginProjectbridgeEntity::$table_name . "`.`contract_id`,
                                '\">',
                                `glpi_contracts`.`name`,
                                '</a>'
                            )
                        ELSE
                            NULL
                    END)
                    AS `ITEM_" . $offset . "`,
                ";
            } else if ($key == 4202) {
                // url to ticket search for tickets in the entity that are not linked to a task
                $ticket_search_link = rtrim($CFG_GLPI['root_doc'], '/') . '/front/ticket.php?is_deleted=0&criteria[0][field]=4214&criteria[0][searchtype]=contains&criteria[0][value]=Non&criteria[1][link]=AND&criteria[1][field]=80&criteria[1][searchtype]=equals&criteria[1][value]=';

                $select = "
                    CONCAT(
                        '<!--',
                        COALESCE(
                            ROUND(`unlinked_ticket_actiontimes`.`actiontime_sum`, 2),
                            0
                        ),
                        '-->',

                        '<a href=\"" . $ticket_search_link . "',
                        `glpi_entities`.`id`,
                        '\">',
                        COALESCE(
                            ROUND(`unlinked_ticket_actiontimes`.`actiontime_sum`, 2),
                            0
                        ),
                        '</a>'
                    )
                    AS `ITEM_" . $offset . "`,
                ";
            }

            break;

        case 'Ticket':
            if ($key == 4211) {
                // project name

                $project_link = rtrim($CFG_GLPI['root_doc'], '/') . '/front/project.form.php?id=';

                $select = "
                    GROUP_CONCAT(
                        DISTINCT CONCAT(
                            '<!--',
                            `glpi_projects`.`name`,
                            '-->',

                            '<a href=\"" . $project_link . "',
                            `glpi_projects`.`id`,
                            '\">',
                            `glpi_projects`.`name`,
                            '</a>'
                        )
                        SEPARATOR '$$##$$'
                    )
                    AS `ITEM_" . $offset . "`,
                ";
            } else if ($key == 4212) {
                // project task

                $task_link = rtrim($CFG_GLPI['root_doc'], '/') . '/front/projecttask.form.php?id=';

                $select = "
                    GROUP_CONCAT(
                        DISTINCT CONCAT(
                            '<!--',
                            `glpi_projecttasks`.`name`,
                            '-->',

                            '<a href=\"" . $task_link . "',
                            `glpi_projecttasks`.`id`,
                            '\">',
                            `glpi_projecttasks`.`name`,
                            '</a>'
                        )
                        SEPARATOR '$$##$$'
                    )
                    AS `ITEM_" . $offset . "`,
                ";
            } else if ($key == 4213) {
                // project task status

                $select = "
                    GROUP_CONCAT(DISTINCT `glpi_projectstates`.`name` SEPARATOR '$$##$$')
                    AS `ITEM_" . $offset . "`,
                ";
            } else if ($key == 4214) {
                // is the ticket linked to a task?

                $select = "
                    (CASE WHEN `glpi_projecttasks_tickets`.`tickets_id` = `glpi_tickets`.`id`
                    THEN
                        'Oui'
                    ELSE
                        'Non'
                    END)
                    AS `ITEM_" . $offset . "`,
                ";
            }

            break;

        case 'Contract':
            if ($key == 4222) {
                // last task's status

                $task_link = rtrim($CFG_GLPI['root_doc'], '/') . '/front/projecttask.form.php?id=';

                $select = "
                    (CASE WHEN `last_tasks`.`project_task_id` IS NOT NULL
                    THEN
                        CONCAT(
                            '<!--',
                            COALESCE(`last_tasks`.`project_state`, '" . NOT_AVAILABLE . "'),
                            '-->',

                            '<a href=\"" . $task_link . "',
                            `last_tasks`.`project_task_id`,
                            '\">',
                            COALESCE(`last_tasks`.`project_state`, '" . NOT_AVAILABLE . "'),
                            '</a>'
                        )
                    ELSE
                        NULL
                    END)
                    AS `ITEM_" . $offset . "`,
                ";
            } else if ($key == 4221) {
                // project's name

                $project_link = rtrim($CFG_GLPI['root_doc'], '/') . '/front/project.form.php?id=';

                $select = "
                    (CASE WHEN `last_tasks`.`project_name` IS NOT NULL
                    THEN
                        CONCAT(
                            '<!--',
                            COALESCE(`last_tasks`.`project_name`, '" . NOT_AVAILABLE . "'),
                            '-->',

                            '<a href=\"" . $project_link . "',
                            `last_tasks`.`project_id`,
                            '\">',
                            COALESCE(`last_tasks`.`project_name`, '" . NOT_AVAILABLE . "'),
                            '</a>'
                        )
                    ELSE
                        NULL
                    END)
                    AS `ITEM_" . $offset . "`,
                ";
            }

            break;

        case 'projecttask':
            if ($key == 4231) {
                // effective duration

                $select = "
                    COALESCE(
                        ROUND(`ticket_actiontimes`.`actiontime_sum`, 2),
                        0
                    )
                    AS `ITEM_" . $offset . "`,
                ";
            } else if ($key == 4232) {
                // planned duration

                $select = "
                    COALESCE(
                        ROUND(`glpi_projecttasks`.`planned_duration` / 3600, 2),
                        0
                    )
                    AS `ITEM_" . $offset . "`,
                ";
            } else if ($key == 4233) {
                // last task in the project?

                $select = "
                    (CASE WHEN `glpi_projecttasks`.`id` = `last_tasks`.`id`
                    THEN
                        'Oui'
                    ELSE
                        CASE WHEN `glpi_projecttasks`.`plan_end_date` IS NOT NULL
                        THEN
                            'Non'
                        ELSE
                            '" . NOT_AVAILABLE . "'
                        END
                    END)
                    AS `ITEM_" . $offset . "`,
                ";
            } else if ($key == 4234) {
                // project status

                $project_link = rtrim($CFG_GLPI['root_doc'], '/') . '/front/project.form.php?id=';

                $select = "
                    (CASE WHEN `glpi_projecttasks`.`projects_id` IS NOT NULL
                    THEN
                        CONCAT(
                            '<!--',
                            COALESCE(`states`.`name`, '" . NOT_AVAILABLE . "'),
                            '-->',

                            '<a href=\"" . $project_link . "',
                            `glpi_projecttasks`.`projects_id`,
                            '\">',
                            COALESCE(`states`.`name`, '" . NOT_AVAILABLE . "'),
                            '</a>'
                        )
                    ELSE
                        NULL
                    END)
                    AS `ITEM_" . $offset . "`,
                ";
            }

            break;

        case 'Project':
            if ($key == 4231) {
                $select = "
                    (
                        COALESCE(
                            `task_counter`.`nb_tasks`,
                            0
                        )
                    ) AS `ITEM_" . $offset . "`,
                ";
            }

            break;

        default:
        // nothing to do
    }

    return $select;
}

/**
 * Add a custom left join to search
 *
 * @param string $itemtype
 * @param string $ref_table Reference table (glpi_...)
 * @param integer $new_table Plugin table
 * @param integer $linkfield
 * @param array $already_link_tables
 * @return string
 */
function plugin_projectbridge_addLeftJoin($itemtype, $ref_table, $new_table, $linkfield, $already_link_tables) {
    $left_join = "";

    switch ($new_table) {
        case PluginProjectbridgeEntity::$table_name:
            $left_join = "
                LEFT JOIN `" . $new_table . "`
                    ON (`" . $new_table . "`.`entity_id` = `" . $ref_table . "`.`id`)
                LEFT JOIN `glpi_contracts`
                    ON (`" . $new_table . "`.`contract_id` = `glpi_contracts`.`id`)
            ";

            break;

        case PluginProjectbridgeTicket::$table_name:
            if ($itemtype == 'Entity') {
                $left_join = "
                    LEFT JOIN (
                        SELECT
                            `glpi_tickets`.`entities_id`,
                            SUM(`glpi_tickets`.`actiontime`) / 3600 AS `actiontime_sum`
                        FROM
                            `glpi_tickets`
                        LEFT OUTER JOIN `glpi_projecttasks_tickets`
                            ON (`glpi_tickets`.`id` = `glpi_projecttasks_tickets`.`tickets_id`)
                        WHERE TRUE
                            AND `glpi_tickets`.`is_deleted` = 0
                            AND `glpi_projecttasks_tickets`.`tickets_id` IS NULL
                        GROUP BY
                            `glpi_tickets`.`entities_id`
                    ) AS `unlinked_ticket_actiontimes`
                        ON (`unlinked_ticket_actiontimes`.`entities_id` = `" . $ref_table . "`.`id`)
                ";
            } else if ($itemtype == 'projecttask') {
                $left_join = "
                    LEFT JOIN (
                        SELECT
                            `glpi_projecttasks_tickets`.`projecttasks_id`,
                            SUM(`glpi_tickets`.`actiontime`) / 3600 AS `actiontime_sum`
                        FROM
                            `glpi_tickets`
                        INNER JOIN `glpi_projecttasks_tickets`
                            ON (`glpi_tickets`.`id` = `glpi_projecttasks_tickets`.`tickets_id`)
                        WHERE TRUE
                            AND `glpi_tickets`.`is_deleted` = 0
                        GROUP BY
                            `glpi_projecttasks_tickets`.`projecttasks_id`
                    ) AS `ticket_actiontimes`
                        ON (`ticket_actiontimes`.`projecttasks_id` = `" . $ref_table . "`.`id`)
                    LEFT JOIN (
                        SELECT
                            `glpi_projecttasks`.`id`,
                            `glpi_projecttasks`.`projects_id`,
                            `glpi_projecttasks`.`plan_end_date`
                        FROM
                            `glpi_projecttasks`
                        INNER JOIN
                        (
                            /*
                              Get last task for each project
                             */
                            SELECT
                                `glpi_projecttasks`.`projects_id`,
                                MAX(`glpi_projecttasks`.`plan_end_date`) AS `plan_end_date`
                            FROM
                                `glpi_projecttasks`
                            WHERE TRUE
                            GROUP BY
                                `glpi_projecttasks`.`projects_id`
                        ) AS `max_end_dates`
                            ON (
                                `max_end_dates`.`projects_id` = `glpi_projecttasks`.`projects_id`
                                AND `max_end_dates`.`plan_end_date` = `glpi_projecttasks`.`plan_end_date`
                            )
                        WHERE TRUE
                        GROUP BY
                            `glpi_projecttasks`.`projects_id`
                    ) AS `last_tasks`
                        ON (`last_tasks`.`id` = `glpi_projecttasks`.`id`)
                    LEFT JOIN `glpi_projects` AS `projects`
                        ON (`projects`.`id` = `glpi_projecttasks`.`projects_id`)
                    LEFT JOIN `glpi_projectstates` AS `states`
                        ON (`states`.`id` = `projects`.`projectstates_id`)
                ";
            } else {
                $left_join = "
                    LEFT JOIN `glpi_projecttasks_tickets`
                        ON (`glpi_projecttasks_tickets`.`tickets_id` = `" . $ref_table . "`.`id`)
                    LEFT JOIN `glpi_projecttasks`
                        ON (`glpi_projecttasks`.`id` = `glpi_projecttasks_tickets`.`projecttasks_id`)
                    LEFT JOIN `glpi_projects`
                        ON (`glpi_projecttasks`.`projects_id` = `glpi_projects`.`id`)
                    LEFT JOIN `glpi_projectstates`
                        ON (`glpi_projectstates`.`id` = `glpi_projecttasks`.`projectstates_id`)
                ";
            }

            break;

        case PluginProjectbridgeContract::$table_name:
            if ($itemtype == 'Project') {
                $left_join = "
                    LEFT JOIN (
                        SELECT
                            `glpi_projecttasks`.`projects_id`,
                            COUNT(1) AS `nb_tasks`
                        FROM
                            `glpi_projecttasks`
                        WHERE TRUE
                        GROUP BY
                            `glpi_projecttasks`.`projects_id`
                    ) AS `task_counter`
                        ON (`task_counter`.`projects_id` = `glpi_projects`.`id`)
                ";
            } else {
                $left_join = "
                    LEFT JOIN `" . $new_table . "`
                        ON (`" . $new_table . "`.`contract_id` = `" . $ref_table . "`.`id`)
                    LEFT JOIN `glpi_projects`
                        ON (`" . $new_table . "`.`project_id` = `glpi_projects`.`id`)
                    LEFT JOIN (
                        SELECT
                            `glpi_projecttasks`.`projects_id` AS `project_id`,
                            `glpi_projecttasks`.`id` AS `project_task_id`,
                            `glpi_projectstates`.`name` AS `project_state`,
                            `glpi_projects`.`name` AS `project_name`
                        FROM
                            `glpi_projecttasks`
                        INNER JOIN (
                            /*
                              Get last task for each project
                             */
                            SELECT
                                `glpi_projecttasks`.`projects_id`,
                                MAX(`glpi_projecttasks`.`plan_end_date`) AS `plan_end_date`
                            FROM
                                `glpi_projecttasks`
                            WHERE TRUE
                            GROUP BY
                                `glpi_projecttasks`.`projects_id`
                        ) AS `max_end_dates`
                            ON (
                                `max_end_dates`.`projects_id` = `glpi_projecttasks`.`projects_id`
                                AND `max_end_dates`.`plan_end_date` = `glpi_projecttasks`.`plan_end_date`
                            )
                        INNER JOIN `glpi_projects`
                            ON (`glpi_projecttasks`.`projects_id` = `glpi_projects`.`id`)
                        LEFT JOIN `glpi_projectstates`
                            ON (`glpi_projectstates`.`id` = `glpi_projecttasks`.`projectstates_id`)
                        WHERE TRUE
                        GROUP BY `glpi_projecttasks`.`projects_id`
                    ) AS `last_tasks`
                        ON (`last_tasks`.`project_id` = `glpi_projects`.`id`)
                ";
            }

            break;

        default:
        // nothing to do
    }

    return $left_join;
}

/**
 * Add a custom where to search
 *
 * @param  string $link
 * @param  string $nott
 * @param  string $itemtype
 * @param  string $key
 * @param  string $val        Search argument
 * @param  string $searchtype Type of search (contains, equals, ...)
 * @return string
 */
function plugin_projectbridge_addWhere($link, $nott, $itemtype, $key, $val, $searchtype) {
    $where = "";

    switch ($itemtype) {
        case 'Entity':
            if ($searchtype == 'contains') {
                if ($key == 4201) {
                    $where = $link . "`glpi_contracts`.`name` " . Search::makeTextSearch($val);
                } else {
                    $where = $link . "`unlinked_ticket_actiontimes`.`actiontime_sum` " . Search::makeTextSearch($val);
                }
            }

            break;

        case 'Ticket':
            if ($searchtype == 'contains') {
                if ($key == 4211) {
                    // project name
                    $where = $link . "`glpi_projects`.`name` " . Search::makeTextSearch($val);
                } else if ($key == 4212) {
                    // project task
                    $where = $link . "`glpi_projecttasks`.`name` " . Search::makeTextSearch($val);
                } else if ($key == 4213) {
                    // project task status
                    $where = $link . "`glpi_projectstates`.`name` " . Search::makeTextSearch($val);
                } else if ($key == 4214) {
                    // linked to a task?

                    $searching_yes = (stripos('Oui', $val) !== false);
                    $searching_no = (stripos('Non', $val) !== false);

                    $where_parts = [];

                    if ($searching_yes) {
                        $where_parts[] = "( `glpi_projecttasks_tickets`.`tickets_id` = `glpi_tickets`.`id` )";
                    }

                    if ($searching_no) {
                        $where_parts[] = "( `glpi_projecttasks_tickets`.`tickets_id` IS NULL )";
                    }

                    if (empty($where_parts)) {
                        $where_parts[] = "TRUE";
                    }

                    $where = $link . "(" . implode(' OR ', $where_parts) . ")";
                }
            }

            break;

        case 'Contract':
            if ($searchtype == 'contains') {
                if ($key == 4222) {
                    // project task status

                    $where_parts = [
                      "`last_tasks`.`project_state` " . Search::makeTextSearch($val),
                    ];

                    if (stripos(NOT_AVAILABLE, $val) !== false) {
                        $where_parts[] = "(
                            `last_tasks`.`project_task_id` IS NOT NULL
                            AND `last_tasks`.`project_state` IS NULL
                        )";
                    }

                    $where = $link . "(" . implode(' OR ', $where_parts) . ")";
                } else if ($key == 4221) {
                    // project task status

                    $where_parts = [
                      "`last_tasks`.`project_name` " . Search::makeTextSearch($val),
                    ];

                    if (stripos(NOT_AVAILABLE, $val) !== false) {
                        $where_parts[] = "(
                            `last_tasks`.`project_id` IS NOT NULL
                            AND `last_tasks`.`project_name` = ''
                        )";
                    }

                    $where = $link . "(" . implode(' OR ', $where_parts) . ")";
                }
            }

            break;

        case 'projecttask':
            if ($searchtype == 'contains') {
                if ($key == 4231) {
                    $where = $link . "`ticket_actiontimes`.`actiontime_sum` " . Search::makeTextSearch($val);
                } else if ($key == 4232) {
                    $where = $link . " ROUND(`glpi_projecttasks`.`planned_duration` / 3600, 2) " . Search::makeTextSearch($val);
                } else if ($key == 4233) {
                    $searching_yes = (stripos('Oui', $val) !== false);
                    $searching_no = (stripos('Non', $val) !== false);
                    $searching_not_available = (stripos(NOT_AVAILABLE, $val) !== false);

                    $where_parts = [];

                    if ($searching_yes) {
                        $where_parts[] = "( `glpi_projecttasks`.`id` = `last_tasks`.`id` )";
                    }

                    if ($searching_no) {
                        $where_parts[] = "(
                            `glpi_projecttasks`.`plan_end_date` IS NOT NULL
                            AND `last_tasks`.`id` IS NULL
                        )";
                    }

                    if ($searching_not_available) {
                        $where_parts[] = "( `glpi_projecttasks`.`plan_end_date` IS NULL )";
                    }

                    if (empty($where_parts)) {
                        $where_parts[] = "TRUE";
                    }

                    $where = $link . "(" . implode(' OR ', $where_parts) . ")";
                } else if ($key == 4234) {
                    $where_parts = [
                      "(
                            `glpi_projecttasks`.`projects_id` IS NOT NULL
                            AND `states`.`name` " . Search::makeTextSearch($val) . "
                        )",
                    ];

                    if (stripos(NOT_AVAILABLE, $val) !== false) {
                        $where_parts[] = "(
                            `glpi_projecttasks`.`projects_id` IS NOT NULL
                            AND `states`.`name` IS NULL
                        )";
                    }

                    $where = $link . "(" . implode(' OR ', $where_parts) . ")";
                }
            }

            break;

        case 'Project':
            if ($searchtype == 'contains') {
                if ($key == 4231) {
                    // number of projects

                    if ($val == 0) {
                        $where = $link . "`task_counter`.`nb_tasks` IS NULL";
                    } else {
                        $where = $link . "`task_counter`.`nb_tasks` " . Search::makeTextSearch($val);
                    }
                }
            }

            break;

        default:
        // nothing to do
    }

    return $where;
}

/**
 * Add massive action options
 *
 * @param  string $type
 * @return array
 */
function plugin_projectbridge_MassiveActions($type) {
    $massive_actions = [];

    switch ($type) {
        case 'Ticket':
            $massive_actions['PluginProjectbridgeTicket' . MassiveAction::CLASS_ACTION_SEPARATOR . 'deleteProjectLink'] = __('Delete the link with any project task', 'projectbridge');
//            $massive_actions['PluginProjectbridgeTicket' . MassiveAction::CLASS_ACTION_SEPARATOR . 'addProjectLink'] = __('Link to a project', 'projectbridge');
//            $massive_actions['PluginProjectbridgeTicket' . MassiveAction::CLASS_ACTION_SEPARATOR . 'addProjectTaskLink'] = __('Force link to a project task', 'projectbridge');
            $massive_actions['PluginProjectbridgeTicket' . MassiveAction::CLASS_ACTION_SEPARATOR . 'addProjectTaskLink'] = __('Force to a contract', 'projectbridge');
           
            break;

        default:
        // nothing to do
    }

    return $massive_actions;
}
