<?php

class PluginProjectbridgeState extends CommonDBTM
{
   public static $table_name = 'glpi_plugin_projectbridge_states';

    /**
     * Get the GLPI projectstates_id by status name
     *
     * @param string $status
     * @return int|null
     */
   public static function getProjectStateIdByStatus($status) {
       $allowed_states = [
           'in_progress',
           'closed',
           'renewal',
       ];

       $project_state_id = null;

       if (in_array($status, $allowed_states)) {
           $state = new PluginProjectbridgeState();
           $state->getFromDBByCrit(['status' => $status]);

          if (isset($state->fields['projectstates_id'])) {
              $project_state_id = $state->fields['projectstates_id'];
            }
         }

         return $project_state_id;
   }
}
