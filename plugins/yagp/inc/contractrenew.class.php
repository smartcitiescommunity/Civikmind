<?php

class PluginYagpContractrenew extends CommonDBTM {
   static function getTypeName($nb = 0) {
      return __('YagpContractRenew', 'yagp');
   }

   static function cronInfo($name) {
      switch ($name) {
         case 'renewContract':
            return ['description' => __('Renews tacit contracts', 'yagp')];
      }
      return [];
   }

   public static function cronRenewContract($task) {
      global $DB,$CFG_GLPI;

      $query=[
         'FROM'=>'glpi_contracts',
         'WHERE'=>[
            'renewal'=>1,
            [
               'NOT' => ['begin_date' => null],
            ],
            'RAW' => [
               'DATEDIFF(
            		ADDDATE(
            			' . DBmysql::quoteName('begin_date') . ',
            			INTERVAL ' . DBmysql::quoteName('duration') . ' MONTH
            		),
                  CURDATE()
               )' => ['<=', 1]
            ]
         ]
      ];
      $contract=new Contract();
      foreach ($DB->request($query) as $id => $row) {
         $contract->update(['id'=>$row["id"],'duration'=>($row['duration']+$row['periodicity'])]);
         $task->addVolume(1);
         $task->log("<a href='".Contract::getFormURLWithID($row["id"])."'>".sprintf(__("Renewed Contract id: %s", "yagp"), $row["id"])."</a>");
      }
      return true;
   }
}