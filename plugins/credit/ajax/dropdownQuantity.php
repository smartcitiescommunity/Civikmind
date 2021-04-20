<?php

include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_POST["entity"])) {

   $entity_query = [
      'SELECT' => ['overconsumption_allowed', 'quantity'],
      'FROM'   => 'glpi_plugin_credit_entities',
      'WHERE'  => [
         'id' => $_POST['entity'],
      ],
   ];
   $entity_result = $DB->request($entity_query)->next();

   $overconsumption_allowed = $entity_result['overconsumption_allowed'];
   $quantity_sold           = (int)$entity_result['quantity'];

   if (0 !== $quantity_sold && !$overconsumption_allowed) {
      $ticket_query = [
         'SELECT' => [
            'SUM' => 'consumed AS consumed_total',
         ],
         'FROM'   => 'glpi_plugin_credit_tickets',
         'WHERE'  => [
            'plugin_credit_entities_id' => $_POST['entity'],
         ],
      ];
      $ticket_result = $DB->request($ticket_query)->next();

      $consumed = (int)$ticket_result['consumed_total'];
      $max      = max(0, $quantity_sold - $consumed);

      Dropdown::showNumber("plugin_credit_quantity", ['value'   => '',
                                                      'min'     => 0,
                                                      'max'     => $max,
                                                      'step'    => 1,]);
   } else {
      Dropdown::showNumber("plugin_credit_quantity", ['value'   => '',
                                                      'min'     => 0,
                                                      'max'     => 1000000,
                                                      'step'    => 1,]);
   }

}
