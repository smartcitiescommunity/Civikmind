<?php

chdir(__DIR__);
require_once('../../../inc/includes.php');
require_once('../hook.php');

// number of hours for each type of contract
$nb_hours_per_contract_type = [
  // ContractType -> nbHours
  8 => 42,
  10 => 5,
  11 => 12,
  13 => 6,
  14 => 24,
  15 => 20,
  16 => 24,
  17 => 40,
  20 => 6,
  21 => 12,
  23 => 21,
  24 => 6,
  25 => 2,
  30 => 6,
  31 => 4,
  35 => 24,
  37 => 7,
  46 => 36,
  47 => 24,
  50 => 9999,
  51 => 12,
  53 => 90,
  55 => 12,
  60 => 84,
  61 => 24,
  62 => 10,
  64 => 16,
  65 => 9999,
  72 => 9999,
  76 => 10,
  77 => 56,
  84 => 30,
  93 => 60,
  112 => 30,
  113 => 30,
];


// all project names
$project = new Project();
$project_names = [];

foreach ($project->find("TRUE") as $project_id => $project) {
    $project_names[$project_id] = $project['name'];
}


// all contracts
$contract = new Contract();
$all_contracts = $contract->find("TRUE AND is_deleted = 0");

$entities_ids = [];
$success_contract_ids = [];
$nb_fails = 0;
$nb_ignored = 0;

foreach (array_keys($all_contracts) as $contract_id) {
    $contract = new Contract();
    $contract->getFromDB($contract_id);

    // was the contract in progress less than 3 months ago?
    $begin_time = strtotime($contract->fields['begin_date']);
    $end_time = strtotime('+' . $contract->fields['duration'] . ' months', $begin_time);
    $end_date = date('Y-m-d', $end_time);
    $end_date_datetime = new DateTime($end_date);
    $today_datetime = new DateTime("now");

   if ($end_date_datetime >= $today_datetime
    && isset($nb_hours_per_contract_type[$contract->fields['contracttypes_id']])
    && !in_array($contract->fields['name'], $project_names)
   ) {
       // contracts that where in progress less than 3 months ago AND
       // contracts with matching support hours AND
       // no existing project by that name

       $contract->input = [
      'projectbridge_project_hours' => $nb_hours_per_contract_type[$contract->fields['contracttypes_id']],
      'name' => $contract->fields['name'],
       ];

       if (plugin_projectbridge_contract_add($contract, true) === true) {
           echo 'Linked contract #' . $contract_id . ' "' . $contract->fields['name'] . '"' . "\n";
           $success_contract_ids[] = $contract_id;

          if (!in_array($contract->fields['entities_id'], $entities_ids)) {
              $entities_ids[] = (int) $contract->fields['entities_id'];
            }
         } else {
            echo 'FAILED linking contract #' . $contract_id . ' "' . $contract->fields['name'] . '"' . "\n";
            $nb_fails++;
         }
   } else {
       $nb_ignored++;
   }
}

$nb_entities_too_many_contracts = 0;
$nb_entities_success = 0;
$nb_entities_fails = 0;

// entities
if (!empty($entities_ids)) {
   foreach ($entities_ids as $entity_id) {
       $contract = new Contract();
       $contracts = $contract->find("
      TRUE
      AND entities_id = " . $entity_id . "
      AND is_deleted = 0
    ");

       $nb_contracts = count($contracts);

      if ($nb_contracts == 1) {
         $entity = new Entity();
         $entity->getFromDB($entity_id);
         $contract_id = key($contracts);

         $entity->input = [
         'projectbridge_contract_id' => $contract_id,
         ];

         if (plugin_projectbridge_pre_entity_update($entity, true)) {
             echo 'Linked entity #' . $entity_id . ' with default contract' . "\n";
             $nb_entities_success++;
         } else {
             echo 'FAILED linking entity #' . $entity_id . ' to contract #' . $contract_id . "\n";
             $nb_entities_fails++;
         }
      } else if ($nb_contracts > 1) {
          echo 'FAILED linking entity #' . $entity_id . ': too many contracts' . "\n";
          $nb_entities_too_many_contracts++;
      }
   }
}

echo '--------' . "\n";
echo '--------' . "\n";
echo 'Contracts: ' . (count($success_contract_ids)) . ' successes - ' . $nb_fails . ' fails - ' . $nb_ignored . ' ignored' . "\n";
echo 'Entities: ' . $nb_entities_success . ' successes - ' . $nb_entities_fails . ' fails - ' . $nb_entities_too_many_contracts . ' with too many contracts' . "\n";
