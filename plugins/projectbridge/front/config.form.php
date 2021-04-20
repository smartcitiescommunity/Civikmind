<?php

include('../../../inc/includes.php');

function getPostDataFromField($post_field) {
    $value = null;

    if (isset($_POST[$post_field])) {
        if (!is_array($_POST[$post_field])) {
            $_POST[$post_field] = trim($_POST[$post_field]);
        }

        $value = $_POST[$post_field];
    }

    return $value;
}

function getPostDataFromFields(array $post_fields) {
    $post_data = [];

    foreach ($post_fields as $post_field) {
        $post_data[$post_field] = getPostDataFromField($post_field);
    }

    return $post_data;
}

$can_update = false;

if (class_exists('PluginProjectbridgeConfig')) {
    $plugin = new Plugin();

    if ($plugin->isActivated(PluginProjectbridgeConfig::NAMESPACE)) {
        $config = new Config();

        if ($config->canView() && $config->canUpdate()
        ) {
            $can_update = true;
        }
    }
}

global $CFG_GLPI;

Html::header(__('ProjectBridge Configuration', 'projectbridge'), $_SERVER['PHP_SELF'], 'config', 'plugins');
echo '<div align="center">' . "\n";

echo '<h1>';
echo __('ProjectBridge Configuration', 'projectbridge');
echo '</h1>' . "\n";

echo '<hr />' . "\n";

if ($can_update) {
    global $CFG_GLPI;
    $post_fields = [
      'projectbridge_state_in_progress',
      'projectbridge_state_closed',
      'projectbridge_state_renewal',
      'projectbridge_save_states',
      'projectbridge_delete_recipient',
      'projectbridge_add_recipient',
      'projectbridge_add_recipient_submit',
      'projectbridge_config_countOnlyPublicTasks'
    ];

    $post_data = getPostDataFromFields($post_fields);

    echo '<style>' . "\n";
    echo '    table td, table th { border-bottom: 1px solid #ccc; border-left: 1px solid #ccc; padding: 15px;}';
    echo '    table td:first-child, table th:first-child { border-left: 0px;}';
    echo '</style>' . "\n";

    echo '<a href="' . PLUGIN_PROJECTBRIDGE_WEB_DIR . '/front/projecttask.php">';
    echo __('Project Tasks', 'projectbridge');
    echo '</a>';

    // status config

    echo '<h2>';
    echo __('Status Configuration', 'projectbridge');
    echo '</h2>' . "\n";

    echo '<p>';
    echo __('Please match the status names and their values in GLPI', 'projectbridge') . '.';
    echo '</p>' . "\n";

    echo '<form method="post" action="">' . "\n";
    echo '<table>' . "\n";

    echo '<thead>' . "\n";

    echo '<tr>' . "\n";
    echo '<th>';
    echo __('Status name', 'projectbridge');
    echo '</th>' . "\n";
    echo '<th>';
    echo __('Status type', 'projectbridge');
    echo '</th>' . "\n";
    echo '<th>';
    echo __('Corresponding status', 'projectbridge');
    echo '</th>' . "\n";
    echo '</tr>' . "\n";

    echo '</thead>' . "\n";


    echo '<tbody>' . "\n";

    if (!empty($post_data['projectbridge_save_states'])) {
        $state_in_progress_value = PluginProjectbridgeState::getProjectStateIdByStatus('in_progress');

        if ($post_data['projectbridge_state_in_progress'] !== $state_in_progress_value) {
            $state = new PluginProjectbridgeState();
            $state_data = [
              'status' => 'in_progress',
              'projectstates_id' => (int) $post_data['projectbridge_state_in_progress'],
            ];

            if ($state_in_progress_value === null) {
                $state->add($state_data);
            } else {
                $state = new PluginProjectbridgeState();
                $state->getFromDBByCrit(['status' => 'in_progress']);
                $state_data['id'] = $state->fields['id'];
                $state->update($state_data);
            }
        }

        $state_closed_value = PluginProjectbridgeState::getProjectStateIdByStatus('closed');

        if ($post_data['projectbridge_state_closed'] !== $state_closed_value) {
            $state = new PluginProjectbridgeState();
            $state_data = [
              'status' => 'closed',
              'projectstates_id' => (int) $post_data['projectbridge_state_closed'],
            ];

            if ($state_closed_value === null) {
                $state->add($state_data);
            } else {
                $state = new PluginProjectbridgeState();
                $state->getFromDBByCrit(['status' => 'closed']);
                $state_data['id'] = $state->fields['id'];
                $state->update($state_data);
            }
        }

        $state_renewal_value = PluginProjectbridgeState::getProjectStateIdByStatus('renewal');

        if ($post_data['projectbridge_state_renewal'] !== $state_renewal_value) {
            $state = new PluginProjectbridgeState();
            $state_data = [
              'status' => 'renewal',
              'projectstates_id' => (int) $post_data['projectbridge_state_renewal'],
            ];

            if ($state_renewal_value === null) {
                $state->add($state_data);
            } else {
                $state = new PluginProjectbridgeState();
                $state->getFromDBByCrit(['status' => 'renewal']);
                $state_data['id'] = $state->fields['id'];
                $state->update($state_data);
            }
        }
    }

    $state_dropdown_conf = [
      'addicon' => false,
      'comments' => false,
    ];

    $state_in_progress_value = PluginProjectbridgeState::getProjectStateIdByStatus('in_progress');

    echo '<tr>' . "\n";
    echo '<td>';
    echo __('In progress', 'projectbridge');
    echo '</td>' . "\n";
    echo '<td>';
    echo __('Task');
    echo '</td>' . "\n";
    echo '<td>';
    ProjectState::dropdown($state_dropdown_conf + ['value' => $state_in_progress_value, 'name' => 'projectbridge_state_in_progress']);
    echo '</td>' . "\n";
    echo '</tr>' . "\n";

    $state_closed_value = PluginProjectbridgeState::getProjectStateIdByStatus('closed');

    echo '<tr>' . "\n";
    echo '<td>';
    echo __('Close');
    echo '</td>' . "\n";
    echo '<td>';
    echo __('Task');
    echo '</td>' . "\n";
    echo '<td>';
    ProjectState::dropdown($state_dropdown_conf + ['value' => $state_closed_value, 'name' => 'projectbridge_state_closed']);
    echo '</td>' . "\n";
    echo '</tr>' . "\n";

    $state_renewal_value = PluginProjectbridgeState::getProjectStateIdByStatus('renewal');

    echo '<tr>' . "\n";
    echo '<td>';
    echo __('Renewal', 'projectbridge');
    echo '</td>' . "\n";
    echo '<td>';
    echo __('Ticket');
    echo '</td>' . "\n";
    echo '<td>';
    RequestType::dropdown($state_dropdown_conf + ['value' => $state_renewal_value, 'name' => 'projectbridge_state_renewal']);
    echo '</td>' . "\n";
    echo '</tr>' . "\n";

    echo '<tr style="text-align: center">' . "\n";
    echo '<td colspan="3">';
    echo '<input type="submit" class="submit" name="projectbridge_save_states" value="' . __('Save') . '" />';
    echo '</td>' . "\n";
    echo '</tr>' . "\n";

    echo '</tbody>' . "\n";
    echo '</table>' . "\n";
    Html::closeForm();

    echo '<hr />' . "\n";

    // recipients config
    $recipientIds = PluginProjectbridgeConfig::getConfValueByName('RecipientIds');

    if (!empty($post_data['projectbridge_delete_recipient']) && is_array($post_data['projectbridge_delete_recipient'])
    ) {
        $row_id = key($post_data['projectbridge_delete_recipient']);

        if (in_array($row_id, $recipientIds)) {
            $recipientIds = array_diff($recipientIds, [$row_id]);
            PluginProjectbridgeConfig::updateConfValue('RecipientIds', $recipientIds);
        }
    } else if (!empty($post_data['projectbridge_add_recipient']) && !empty($post_data['projectbridge_add_recipient_submit']) && !isset($post_data[(int) $post_data['projectbridge_add_recipient']])
    ) {
        $recipient_user_id = (int) $post_data['projectbridge_add_recipient'];

        if (!in_array($recipient_user_id, $recipientIds)) {
            $recipientIds[] = $recipient_user_id;
            PluginProjectbridgeConfig::updateConfValue('RecipientIds', $recipientIds);
        }
    }
    $recipients = PluginProjectbridgeConfig::getRecipients();

    echo '<h2>';
    echo __('People receiving alerts', 'projectbridge');
    echo '</h2>' . "\n";

    echo '<form method="post" action="">' . "\n";
    echo '<table>' . "\n";

    echo '<thead>' . "\n";

    echo '<tr>' . "\n";
    echo '<th style="min-width: 200px">';
    echo __('Name');
    echo '</th>' . "\n";
    echo '<th>';
    echo __('Action');
    echo '</th>' . "\n";
    echo '</tr>' . "\n";

    echo '</thead>' . "\n";

    echo '<tbody>' . "\n";
    $recipient_user_ids = [];

    foreach ($recipients as $row_id => $recipient) {
        $recipient_user_ids[] = $recipient['user_id'];

        echo '<tr>' . "\n";
        echo '<th>' . "\n";
        echo '<a href="' . $CFG_GLPI['root_doc'] . '/front/user.form.php?id=' . $recipient['user_id'] . '" />';
        echo $recipient['name'];
        echo '</a>' . "\n";
        echo '</td>' . "\n";
        echo '<td>';
        echo '<input type="submit" class="submit" name="projectbridge_delete_recipient[' . $row_id . ']" value="' . __('Delete') . '" />';
        echo '</td>' . "\n";
        echo '</tr>' . "\n";
    }

    if (empty($recipients)) {
        echo '<tr>' . "\n";
        echo '<td colspan="2" style="text-align: center">';
        echo __('Nobody receive alerts', 'projectbridge');
        echo '</td>' . "\n";
        echo '</tr>' . "\n";
    }


    echo '<tr">' . "\n";
    echo '<td>' . "\n";
    echo User::dropdown([
      'name' => 'projectbridge_add_recipient',
      'used' => $recipient_user_ids,
      'right' => 'all',
      'comments' => false,
      'display' => false,
    ]);
    echo '</td>' . "\n";
    echo '<td>';
    echo '<input type="submit" class="submit" name="projectbridge_add_recipient_submit" value="' . __('Add') . '" />';
    echo '</td>' . "\n";
    echo '</tr>' . "\n";

    echo '</tbody>' . "\n";

    echo '</table>' . "\n";
    Html::closeForm();
    
    $countOnlyPublicTasks = PluginProjectbridgeConfig::getConfValueByName('CountOnlyPublicTasks');
    if (isset($post_data['projectbridge_config_countOnlyPublicTasks']) ){
        $countOnlyPublicTasks = $post_data['projectbridge_config_countOnlyPublicTasks'];
        PluginProjectbridgeConfig::updateConfValue('CountOnlyPublicTasks', $countOnlyPublicTasks);
    }
    echo '<h2>'.__('General config', 'projectbridge').'</h2>';
    echo '<form method="post" action="">' . "\n";
    echo '<table>' . "\n";
    echo '<thead>' . "\n";
    echo '<tr>' . "\n";
    echo '<th style="min-width: 200px">';
    echo __('Name');
    echo '</th>' . "\n";
    echo '<th>';
    echo __('Value');
    echo '</th>' . "\n";
    echo '</tr>' . "\n";
    echo '<tbody>' . "\n";
    echo '<tr">' . "\n";
    echo '<td>'.__('Count only public tasks in project', 'projectbridge').'' . "\n";
    echo '</td>' . "\n";
    echo '<td>' . "\n";
    Dropdown::showYesNo('projectbridge_config_countOnlyPublicTasks',$countOnlyPublicTasks,[]);
    echo '</td>' . "\n";
    echo '</tr>' . "\n";
    echo '</tbody>' . "\n";
    echo '</table>' . "\n";
    echo '<input type="submit" class="submit" name="projectbridge_save_general_config" value="' . __('Save') . '" />';
    Html::closeForm();
} else {
    echo '<br/><br/>';
    echo '<img src="' . $CFG_GLPI['root_doc'] . '/pics/warning.png" alt="warning" />';
    echo '<br/><br/>';
    echo '<b>';
    echo __('Please activate the plugin or get the right of access', 'projectbridge') . '.';
    echo '</b>';
}

echo '</div>' . "\n";
Html::footer();
