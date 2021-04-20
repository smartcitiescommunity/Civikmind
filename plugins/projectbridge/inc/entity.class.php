<?php

class PluginProjectbridgeEntity extends CommonDBTM
{
   private $_entity;
   private $_contract_id;

   public static $table_name = 'glpi_plugin_projectbridge_entities';

    /**
     * Constructor
     *
     * @param Entity $entity
     */
   public function __construct(Entity $entity = null) {
       $this->_entity = $entity;
   }

    /**
     * Get the id of the default contract linked to the entity
     *
     * @param void
     * @return integer|null
     */
   public function getContractId($entityId = null) {
      if ($this->_contract_id === null) {
          if(!$entityId) {
              $entityId = $this->_entity->getId();
          }
          $result = $this->getFromDBByCrit(['entity_id' => $entityId]);

         if ($result) {
            $this->_contract_id = (int) $this->fields['contract_id'];
         }
      }

         return $this->_contract_id;
   }

    /**
     * Display HTML after entity has been shown
     *
     * @param  Entity $entity
     * @return void
     */
   public static function postShow(Entity $entity) {
       $bridge_entity = new PluginProjectbridgeEntity($entity);
       $contract_id = $bridge_entity->getContractId();

       $contract_config = [
           'value' => $contract_id,
           'name' => 'projectbridge_contract_id',
           'display' => false,
           'entity' => $entity->getId(),
           'entity_sons'  => (!empty($_SESSION['glpiactive_entity_recursive'])) ? true : false,
           'nochecklimit' => true,
           'expired' => true,
       ];

       $html_parts = [];
       $html_parts[] = '<div style="display: none;">' . "\n";
       $html_parts[] = '<table class="tab_cadre_fixe">' . "\n";
       $html_parts[] = '<tr id="projectbridge_config" class="tab_bg_1">' . "\n";

       $html_parts[] = '<td>';
       $html_parts[] = __('Default contract', 'projectbridge');
       $html_parts[] = '</td>' . "\n";

       $html_parts[] = '<td colspan="2">' . "\n";
       $html_parts[] = Contract::dropdown($contract_config);

       global $CFG_GLPI;

       if (!empty($contract_id)) {
           $html_parts[] = '<a href="' . $CFG_GLPI['root_doc'] . '/front/contract.form.php?id=' . $contract_id . '" style="margin-left: 5px;" target="_blank">';
           $html_parts[] = __('Default contract access', 'projectbridge');
           $html_parts[] = '</a>' . "\n";
         } else {
            $html_parts[] = '<a href="' . $CFG_GLPI['root_doc'] . '/front/setup.templates.php?itemtype=Contract&add=1" style="margin-left: 5px;" target="_blank">';
            $html_parts[] = __('Create a new contract', 'projectbridge').' ?';
            $html_parts[] = '</a>' . "\n";

            $html_parts[] = '<small>';
            $html_parts[] = __('Remember to refresh this page after creating the contract', 'projectbridge');
            $html_parts[] = '</small>' . "\n";
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
            $("#mainformtable .footerRow").before(projectbridge_config.clone());
            projectbridge_config.remove();

            $("#projectbridge_config .select2-container").remove();
            $("#projectbridge_config select").select2({
                width: \'\',
                dropdownAutoWidth: true
            });
            $("#projectbridge_config .select2-container").show();
        });');
   }
}
