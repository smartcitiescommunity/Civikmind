<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginManageentitiesCri extends CommonDBTM {

   static $rightname = 'plugin_manageentities_cri_create';

   static function getTypeName($nb = 0) {
      return _n('Intervention report', 'Intervention reports', $nb, 'manageentities');
   }

   function showForm($ID, $options = []) {
      global $DB, $CFG_GLPI;

      $config = PluginManageentitiesConfig::getInstance();
      $width  = 200;
      $job    = new Ticket();
      $job->getfromDB($ID);

      $params = ['job'        => $ID,
                 'form'       => 'formReport',
                 'root_doc'   => $CFG_GLPI['root_doc'],
                 'toupdate'   => $options['toupdate'],
                 'pdf_action' => $options['action']];

      PluginManageentitiesEntity::showManageentitiesHeader(__('Interventions reports', 'manageentities'));

      echo "<div class='red styleContractTitle' style='display:none' id='manageentities_cri_error'></div>";

      echo "<form action=\"" . $CFG_GLPI["root_doc"] .
           "/plugins/manageentities/front/cri.form.php\" method=\"post\" name=\"formReport\">";

      // Champ caché pour l'identifiant du ticket.
      echo "<input type='hidden' name='REPORT_ID' value='$ID' />";
      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe'>";

      /* Information complémentaire déterminant si sous contrat ou non. */
      echo "<tr class='tab_bg_1'>";
      echo "<th>";
      echo _n('Contract', 'Contracts', 1);
      echo "</th>";
      echo "<td colspan='2'>";
      $restrict   = ["`glpi_plugin_manageentities_cridetails`.`entities_id`" => $job->fields['entities_id'],
                     "`glpi_plugin_manageentities_cridetails`.`tickets_id`"  => $job->fields['id']];
      $dbu        = new DbUtils();
      $cridetails = $dbu->getAllDataFromTable("glpi_plugin_manageentities_cridetails", $restrict);
      $cridetail  = reset($cridetails);
      if (isset($cridetail['withcontract'])) {
         $contractSelected = PluginManageentitiesCriDetail::showContractLinkDropdown($cridetail, $job->fields['entities_id'], 'cri');
      } else {
         echo "<table class='tab_cadre' style='margin:0px'>";
         echo "<tr class='tab_bg_1'>";
         echo "<th>" . __('Out of contract', 'manageentities') . "</th>";
         echo "</tr></table>";
         $contractSelected = ['contractSelected' => 0,
                              'contractdaySelected' => 0,
                              'is_contract' => 0];
      }
      echo "</td>";
      echo "</tr>";

      /* Information complémentaire déterminant les intervenants si plusieurs. */
      $PluginManageentitiesCriTechnician = new PluginManageentitiesCriTechnician();
      $technicians_id                    = $PluginManageentitiesCriTechnician->getTechnicians($ID, true);

      echo "<tr class='tab_bg_1'>";
      echo "<th>";
      echo __('Technicians', 'manageentities');
      echo "</th>";

      echo "<td>";

      if (self::isTask($ID)) {
         if (!empty($technicians_id)) {
            $techs = [];
            foreach ($technicians_id as $remove => $data) {
               foreach ($data as $users_id => $users_name) {
                  if ($remove == 'remove') {
                     $params['tech_id'] = $users_id;
                     $techs[]           = $users_name . "&nbsp;" .
                                          "<a class='pointer' onclick='manageentities_loadCriForm(\"deleteTech\", \"" . $options['modal'] . "\", " . json_encode($params) . ");'>
                  <i class=\"far fa-trash-alt\" title=\"" . _sx('button', 'Delete permanently') . "\"></i>
                  </a>";
                  } else {
                     $techs[] = $users_name;
                  }
               }
            }
            echo implode('<br>', $techs);
         } else {
            echo "<span style=\"font-weight:bold; color:red\">" . __('Please assign a technician to your tasks', 'manageentities') . "</span>";
         }
      }

      echo "</td>";
      echo "<td>";
      $used = [];
      if (!empty($technicians_id)) {
         foreach ($technicians_id as $data) {
            foreach ($data as $users_id => $users_name) {
               $used[] = $users_id;
            }
         }
      }

      $userRand = User::dropdown(['name'   => "users_id",
                                       'entity' => $job->fields["entities_id"],
                                       'used'   => $used,
                                       'right'  => 'all',
                                       'width'  => $width]);

      echo "&nbsp;<input type='button' name='add_tech' value=\"" .
           __('Add a technician', 'manageentities') . "\" class='submit' onclick='manageentities_loadCriForm(\"addTech\", \"" . $options['modal'] . "\", " . json_encode($params) . ");'>";
      echo "</td>";
      echo "</tr>";

      if ($contractSelected['contractSelected'] && $contractSelected['contractdaySelected']) {
         echo "<input type='hidden' name='CONTRAT' value='" . $contractSelected['contractSelected'] . "' />";
         echo "<input type='hidden' name='CONTRACTDAY' value='" . $contractSelected['contractdaySelected'] . "' />";

         if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
            /* Information complémentaire pour le libellés des activités. */
            echo "<tr class='tab_bg_1'>";
            echo "<th>";
            echo __('Intervention type', 'manageentities');
            echo "</th>";

            echo "<td colspan='2'>";
            $PluginManageentitiesCriPrice = new PluginManageentitiesCriPrice();
            $critypes                     = $PluginManageentitiesCriPrice->getItems($contractSelected['contractdaySelected']);
            $critypes_data                = [Dropdown::EMPTY_VALUE];
            $critypes_default             = 0;
            foreach ($critypes as $value) {
               $critypes_data[$value['plugin_manageentities_critypes_id']] = $value['critypes_name'];
               if ($value['is_default']) {
                  $critypes_default = $value['plugin_manageentities_critypes_id'];
               }
            }

            Dropdown::showFromArray('REPORT_ACTIVITE', $critypes_data, ['value' => $critypes_default,
                                                                        'width' => $width]);
            echo "</td>";
            echo "</tr>";

            //configuration do not use price
         } else {
            echo "<input type='hidden' name='REPORT_ACTIVITE' value='noprice' />";
         }

         $contract = new PluginManageentitiesContract();
         if ($contract->getFromDBByCrit(['contracts_id' => $contractSelected['contractSelected']])) {
            if ($contract->fields['moving_management']) {
               echo "<tr class='tab_bg_1'>";
               echo "<th>";
               echo __('Number of moving', 'manageentities');
               echo "</th>";
               echo "<td colspan='2'>";
               Dropdown::showNumber('number_moving', ['value' => $cridetail['number_moving'],
                                                      'width' => $width]);
               echo "</td>";
               echo "</tr>";
            }
         }
      } elseif (!$cridetail['withcontract']) {
         echo "<input type='hidden' name='WITHOUTCONTRACT' value='1' />";
      }

      if (self::isTask($ID)) {
         /*
          * Information complémentaire pour la description globale du CRI.
          * Préremplissage avec les informations des suivis non privés.
          */
         $desc = "";
         $join = "";
         $and  = "";

         if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
            $join = " LEFT JOIN `glpi_plugin_manageentities_taskcategories`
                        ON (`glpi_plugin_manageentities_taskcategories`.`taskcategories_id` =
                        `glpi_tickettasks`.`taskcategories_id`)";
            $and  = " AND `glpi_plugin_manageentities_taskcategories`.`is_usedforcount` = 1";
         }

         if ($config->fields['use_publictask'] == PluginManageentitiesConfig::HOUR) {
            $query = "SELECT `content`, `begin`, `end`
                   FROM `glpi_tickettasks` $join
                   WHERE `tickets_id` = '" . $ID . "'
                   AND `is_private` = 0 $and";
         } else {
            $query = "SELECT `content`, `begin`, `end`
                   FROM `glpi_tickettasks` $join
                   WHERE `tickets_id` = '" . $ID . "' $and";
         }

         $result = $DB->query($query);
         $number = $DB->numrows($result);
         if ($number) {
            while ($data = $DB->fetchArray($result)) {
               $desc .= $data["content"] . "\n\n";
            }
            $desc = substr($desc, 0, strlen($desc) - 2); // Suppression des retours chariot pour le dernier suivi...
            echo "<tr class='tab_bg_1'>";
            echo "<th>";
            echo __('Detail of the realized works', 'manageentities');
            echo "</th>";

            echo "<td colspan='2'>";
            //echo "<textarea name=\"REPORT_DESCRIPTION\" cols='120' rows='22'>$desc</textarea>";
            $rand_text  = mt_rand();
            $content_id = "comment$rand_text";
            $cols       = 120;
            $rows       = 22;

            echo "<script src='../../../public/lib/tinymce.js'>";
            $desc = Html::setRichTextContent(
               $content_id,
               $desc,
               $rand_text
            );
            Html::textarea(['name'            => 'REPORT_DESCRIPTION',
                            'value'           => $desc,
                            'rand'            => $rand_text,
                            'editor_id'       => $content_id,
                            'enable_richtext' => true,
                            'cols'            => $cols,
                            'rows'            => $rows]);

            echo "</td>";
            echo "</tr>";

            /* Bouton de génération du rapport. */
            echo "<tr class='tab_bg_2'>";
            echo "<td class='center' colspan='3'>";
            // action empty : add cri
            if (empty($options['action'])) {
               if (!empty($technicians_id)) {
                  echo "<input type='button' name='add_cri' value=\"" .
                       __('Generation of the intervention report', 'manageentities') . "\" class='submit' 
                  onClick='manageentities_loadCriForm(\"addCri\", \"" . $options['modal'] . "\", " . json_encode($params) . ");'>";
               }
               // action not empty : update cri
            } elseif ($options['action'] == 'update_cri') {
               if (!empty($technicians_id)) {
                  echo "<input type='button' name='update_cri' class='manageentities_button' value=\"" .
                       __('Regenerate the intervention report', 'manageentities') . "\" 
                  onClick='manageentities_loadCriForm(\"updateCri\", \"" . $options['modal'] . "\", " . json_encode($params) . ");'>";
               }
            }
         } else {
            echo "<tr class='tab_bg_1'>";
            echo "<td class='center red'>";
            if ($config->fields['hourorday'] != PluginManageentitiesConfig::HOUR) {
               echo __("Impossible generation, you didn't create a scheduled task", 'manageentities');
            } else {
               echo __('No tasks whose category can be used', 'manageentities');
            }
            echo "</td>";
            echo "</tr>";
         }
      } else {
         echo "<tr class='tab_bg_1'>";
         echo "<td class='center red' >";
         echo __('No tasks', 'manageentities');
         echo "</td>";
         echo "</tr>";
      }
      echo "</td>";
      echo "</tr>";
      echo "</table></div>";
      Html::closeForm();
   }

   function isTask($tickets_id) {
      $tickettask = new TicketTask();
      $tasks      = $tickettask->find(['tickets_id' => $tickets_id]);
      if (count($tasks)) {
         return true;
      } else {
         return false;
      }
   }

   /**
    * Récupération des données et génération du document. Il sera enregistré suivant le paramétre
    * enregistrement.
    *
    * @param type                        $params
    * @param type                        $options
    *
    * @return boolean
    * @global PluginManageentitiesCriPDF $PDF
    * @global type                       $DB
    * @global type                       $CFG_GLPI
    *
    */
   function generatePdf($params, $options = []) {
      global $PDF, $DB, $CFG_GLPI;

      $p['CONTRACTDAY']     = 0;
      $p['WITHOUTCONTRACT'] = 0;
      $p['CONTRAT']         = 0;
      $p['REPORT_ACTIVITE'] = '';
      $p['documents_id']    = 0;
      $p['number_moving']   = 0;

      foreach ($params as $key => $val) {
         $p[$key] = $val;
         if($key == 'REPORT_DESCRIPTION'){
            $p[$key] = urldecode($val);
         }
      }

      // ajout de la configuration du plugin
      $config = PluginManageentitiesConfig::getInstance();

      $PDF = new PluginManageentitiesCriPDF('P', 'mm', 'A4');

      /* Initialisation du document avec les informations saisies par l'utilisateur. */
      $criType_id = $p['REPORT_ACTIVITE'];
      $typeCri = new PluginManageentitiesCriType();
      if($typeCri->getFromDB($criType_id)) {
         $p['REPORT_ACTIVITE'] = $typeCri->getField('name');
      }
      if ($config->fields['useprice'] == PluginManageentitiesConfig::NOPRICE || $config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
         $p['REPORT_ACTIVITE'] = [];
         $criType_id           = 0;
      }
      //$PDF->SetDescriptionCri(Toolbox::unclean_cross_side_scripting_deep($p['REPORT_DESCRIPTION']));
      $p['REPORT_DESCRIPTION'] = Toolbox::unclean_cross_side_scripting_deep($p['REPORT_DESCRIPTION']);
      $p['REPORT_DESCRIPTION'] = str_replace("’", "'", $p['REPORT_DESCRIPTION']);
      $PDF->SetDescriptionCri($p['REPORT_DESCRIPTION']);

      $job = new Ticket();
      if ($job->getfromDB($p['REPORT_ID'])) {

         /* Récupération des informations du ticket et initialisation du rapport. */
         $PDF->SetDemandeAssociee($p['REPORT_ID']); // Demande / ticket associée au rapport.
         // Set intervenants
         $critechnicians = new PluginManageentitiesCriTechnician();
         $intervenants   = implode(',', $critechnicians->getTechnicians($p['REPORT_ID']));
         $PDF->SetIntervenant($intervenants);

         if ($p['WITHOUTCONTRACT']) {
            $sous_contrat = false;
            $PDF->SetSousContrat(0);

            /* Information de l'entité active et son contrat. */
            $infos_entite = [];
            $entite       = new Entity();
            $entite->getFromDB($job->fields["entities_id"]);
            $infos_entite[0] = $entite;
            $PDF->SetEntite($infos_entite);

            /* Année et mois de l'intervention (post du ticket). */
            $infos_date    = [];
            $infos_date[0] = $job->fields["date"];

            /* Du ... au ... */
            //configuration only public task
            $where = "";
            $join  = "";

            if ($config->fields['use_publictask'] == '1') {
               $where = " AND `is_private` = 0";
            }

            $join = " LEFT JOIN `glpi_plugin_manageentities_taskcategories`
                        ON (`glpi_plugin_manageentities_taskcategories`.`taskcategories_id` =
                        gf.taskcategories_id)";

            $query = "SELECT MAX(max_date) AS max_date, MIN(min_date) AS min_date
                 FROM (
                       SELECT MAX(gf.end) AS max_date, MIN(gf.begin) AS min_date
                         FROM glpi_tickettasks gf $join
                        WHERE gf.tickets_id = '" . $p['REPORT_ID'] . "' $where
                        UNION 
                       SELECT MAX(gf.date) AS max_date, MIN(gf.date) AS min_date
                            FROM glpi_tickettasks gf $join
                           WHERE gf.tickets_id = '" . $p['REPORT_ID'] . "' $where
                      ) t";

            $result = $DB->query($query);
            $number = $DB->numrows($result);
            if ($number) {
               while ($data = $DB->fetchArray($result)) {
                  $infos_date[1] = $data["min_date"];
                  $infos_date[2] = $data["max_date"];
               }
            }
            $PDF->SetDateIntervention($infos_date);

            // Forfait

            $temps_passes = [];
            //configuration by day
            if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
               $nbhour    = $config->fields["hourbyday"];
               $condition = "";
            } else {
               //configuration by hour
               $nbhour    = 1;
               $condition = "AND `glpi_plugin_manageentities_taskcategories`.`is_usedforcount` = 1";
            }
            $result  = self::getTempsPasses($join, $where, $p, $condition, $nbhour);
            $cpt_tps = 0;
            while ($data = $DB->fetchArray($result)) {
               $un_temps_passe = [];

               if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
                  // If the category of the task is not used and is hourly for count we set value to 0
                  if ($data["is_usedforcount"] == 0 && $config->fields['hourorday'] == PluginManageentitiesConfig::HOUR)
                     $un_temps_passe[4] = 0;
                  else
                     $un_temps_passe[4] = round($data["tps_passes"], 2);
               } else {
                  // If the category of the task is not used and is hourly for count we set value to 0
                  if ($data["is_usedforcount"] == 0 && $config->fields['hourorday'] == PluginManageentitiesConfig::HOUR)
                     $un_temps_passe[4] = 0;
                  else
                     $un_temps_passe[4] = $PDF->TotalTpsPassesArrondis(round($data["tps_passes"], 2)); //arrondir au quart
               }
               if ($data["date_debut"] == NULL && $data["date_fin"] == NULL) {
                  $un_temps_passe[0] = substr($data["date"], 8, 2) . "/" . substr($data["date"], 5, 2) . "/" . substr($data["date"], 0, 4);
                  $un_temps_passe[1] = ($data["date"] == "-") ? "-" : substr($data["date"], 11, 2) . ":" . substr($data["date"], 14, 2);
                  //calculating the end date
                  if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
                     $date = date('Y-m-d H:i:s', strtotime($data["date"] . " + " . (($un_temps_passe[4]) * 3600) . " seconds"));
                  } else {
                     //daily
                     $date = date('Y-m-d H:i:s', strtotime($data["date"] . " + " . $un_temps_passe[4] * $nbhour . " hours"));
                  }

                  $un_temps_passe[2] = substr($date, 8, 2) . "/" . substr($date, 5, 2) . "/" . substr($date, 0, 4);
                  $un_temps_passe[3] = ($date == "-") ? "-" : substr($date, 11, 2) . ":" . substr($date, 14, 2);
               } else {
                  $un_temps_passe[0] = substr($data["date_debut"], 8, 2) . "/" . substr($data["date_debut"], 5, 2) . "/" . substr($data["date_debut"], 0, 4);
                  $un_temps_passe[1] = ($data["heure_debut"] == "-") ? "-" : substr($data["heure_debut"], 11, 2) . ":" . substr($data["heure_debut"], 14, 2);
                  $un_temps_passe[2] = substr($data["date_fin"], 8, 2) . "/" . substr($data["date_fin"], 5, 2) . "/" . substr($data["date_fin"], 0, 4);
                  $un_temps_passe[3] = ($data["heure_fin"] == "-") ? "-" : substr($data["heure_fin"], 11, 2) . ":" . substr($data["heure_fin"], 14, 2);
               }

               $temps_passes[$cpt_tps] = $un_temps_passe;


               if ($config->fields['useprice'] == PluginManageentitiesConfig::NOPRICE && $config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
                  $p['REPORT_ACTIVITE'][$cpt_tps] = Dropdown::getDropdownName('glpi_taskcategories', $data['taskcat']);
               }

               $cpt_tps++;
            }

            $PDF->SetLibelleActivite($p['REPORT_ACTIVITE']);
            $PDF->SetTempsPasses($temps_passes);
         } else {
            $manageentities_contract      = new PluginManageentitiesContract();
            $manageentities_contract_data = $manageentities_contract->find(['contracts_id' => $p['CONTRAT']]);
            $manageentities_contract_data = array_shift($manageentities_contract_data);
            $contract_days                = new PluginManageentitiesContractDay();
            $contract_days->getFromDB($p['CONTRACTDAY']);

            if ($contract_days->fields['begin_date'] == "" && $contract_days->fields['end_date'] == "") {
               Session::addMessageAfterRedirect(__('Please fill the contract period begin and end dates.', 'manageentities'), true, ERROR);
               Html::back();
               return false;

            } else if ($contract_days->fields['end_date'] == "") {
               Session::addMessageAfterRedirect(__('Please fill the contract period end date.', 'manageentities'), true, ERROR);
               Html::back();
               return false;

            } else if ($contract_days->fields['begin_date'] == "") {
               Session::addMessageAfterRedirect(__('Please fill the contract period begin date.', 'manageentities'), true, ERROR);
               Html::back();
               return false;
            }

            /* Année et mois de l'intervention (post du ticket). */
            $infos_date    = [];
            $infos_date[0] = $job->fields["date"];

            // Not Forfait
            if (($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) ||
                (isset($contract_days->fields['contract_type']) && $contract_days->fields['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_FORFAIT)) {
               /* Du ... au ... */
               //configuration only public task
               $where = "";
               $join  = "";

               if ($config->fields['use_publictask'] == '1') {
                  $where = " AND `is_private` = 0";
               }

               $join = " LEFT JOIN `glpi_plugin_manageentities_taskcategories`
                        ON (`glpi_plugin_manageentities_taskcategories`.`taskcategories_id` =
                        gf.taskcategories_id)";

               $query = "SELECT MAX(max_date) AS max_date, MIN(min_date) AS min_date
                 FROM (
                       SELECT MAX(gf.end) AS max_date, MIN(gf.begin) AS min_date
                         FROM glpi_tickettasks gf $join
                        WHERE gf.tickets_id = '" . $p['REPORT_ID'] . "' $where
                        UNION 
                       SELECT MAX(gf.date) AS max_date, MIN(gf.date) AS min_date
                            FROM glpi_tickettasks gf $join
                           WHERE gf.tickets_id = '" . $p['REPORT_ID'] . "' $where
                      ) t";

               $result = $DB->query($query);
               $number = $DB->numrows($result);
               if ($number) {
                  while ($data = $DB->fetchArray($result)) {
                     $infos_date[1] = $data["min_date"];
                     $infos_date[2] = $data["max_date"];
                  }
               }

               $PDF->SetDateIntervention($infos_date);

               // Forfait
            } else {
               $infos_date[1] = $contract_days->fields['begin_date'];
               $infos_date[2] = $contract_days->fields['end_date'];

               $PDF->SetDateIntervention($infos_date);
            }

            /* Information de l'entité active et son contrat. */
            $infos_entite = [];
            $entite       = new Entity();
            $entite->getFromDB($job->fields["entities_id"]);
            $infos_entite[0] = $entite;

            if ($p['CONTRAT']) {
               $contract = new contract;
               $contract->getFromDB($p['CONTRAT']);
               $infos_entite[1] = $contract->fields["num"];
               $sous_contrat    = true;
            } else {
               $infos_entite[1] = "";
               $sous_contrat    = false;
            }
            $PDF->SetSousContrat($sous_contrat);

            $PDF->SetEntite($infos_entite);

            //type of contract Intervention entitled the total change
            if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR && (isset($manageentities_contract_data['contract_type']) && $manageentities_contract_data['contract_type'] == PluginManageentitiesContract::CONTRACT_TYPE_INTERVENTION)) {
               $PDF->setIntervention();
            }

            //configuration by day
            if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
               $nbhour    = $config->fields["hourbyday"];
               $condition = "";
            } else {
               //configuration by hour
               $nbhour    = 1;
               $condition = "AND `glpi_plugin_manageentities_taskcategories`.`is_usedforcount` = 1";
            }

            /* Récupération des suivis du ticket pour la gestion des temps passés. */
            // Not Forfait
            if (($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) ||
                (isset($contract_days->fields['contract_type']) && $contract_days->fields['contract_type'] != PluginManageentitiesContract::CONTRACT_TYPE_FORFAIT)) {
               $result       = self::getTempsPasses($join, $where, $p, $condition, $nbhour);
               $temps_passes = [];
               $cpt_tps      = 0;
               while ($data = $DB->fetchArray($result)) {
                  $un_temps_passe = [];
                  if (($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) && (isset($manageentities_contract_data['contract_type']) && $manageentities_contract_data['contract_type'] == PluginManageentitiesContract::CONTRACT_TYPE_INTERVENTION)) {
                     $un_temps_passe[4] = 1;
                  } else {

                     if ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
                        // If the category of the task is not used and is hourly for count we set value to 0
                        if ($data["is_usedforcount"] == 0 && $config->fields['hourorday'] == PluginManageentitiesConfig::HOUR)
                           $un_temps_passe[4] = 0;
                        else
                           $un_temps_passe[4] = round($data["tps_passes"], 2);
                     } else {
                        // If the category of the task is not used and is hourly for count we set value to 0
                        if ($data["is_usedforcount"] == 0 && $config->fields['hourorday'] == PluginManageentitiesConfig::HOUR)
                           $un_temps_passe[4] = 0;
                        else
                           $un_temps_passe[4] = $PDF->TotalTpsPassesArrondis(round($data["tps_passes"], 2)); //arrondir au quart
                     }

                  }
                  if ($data["date_debut"] == NULL && $data["date_fin"] == NULL) {
                     $un_temps_passe[0] = substr($data["date"], 8, 2) . "/" . substr($data["date"], 5, 2) . "/" . substr($data["date"], 0, 4);
                     $un_temps_passe[1] = ($data["date"] == "-") ? "-" : substr($data["date"], 11, 2) . ":" . substr($data["date"], 14, 2);
                     //calculating the end date
                     if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
                        $date = date('Y-m-d H:i:s', strtotime($data["date"] . " + " . (($un_temps_passe[4]) * 3600) . " seconds"));
                     } else {
                        //daily
                        $date = date('Y-m-d H:i:s', strtotime($data["date"] . " + " . $un_temps_passe[4] * $nbhour . " hours"));
                     }

                     $un_temps_passe[2] = substr($date, 8, 2) . "/" . substr($date, 5, 2) . "/" . substr($date, 0, 4);
                     $un_temps_passe[3] = ($date == "-") ? "-" : substr($date, 11, 2) . ":" . substr($date, 14, 2);
                  } else {
                     $un_temps_passe[0] = substr($data["date_debut"], 8, 2) . "/" . substr($data["date_debut"], 5, 2) . "/" . substr($data["date_debut"], 0, 4);
                     $un_temps_passe[1] = ($data["heure_debut"] == "-") ? "-" : substr($data["heure_debut"], 11, 2) . ":" . substr($data["heure_debut"], 14, 2);
                     $un_temps_passe[2] = substr($data["date_fin"], 8, 2) . "/" . substr($data["date_fin"], 5, 2) . "/" . substr($data["date_fin"], 0, 4);
                     $un_temps_passe[3] = ($data["heure_fin"] == "-") ? "-" : substr($data["heure_fin"], 11, 2) . ":" . substr($data["heure_fin"], 14, 2);
                  }

                  $temps_passes[$cpt_tps] = $un_temps_passe;


                  if ($config->fields['useprice'] == PluginManageentitiesConfig::NOPRICE || $config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
                     $p['REPORT_ACTIVITE'][$cpt_tps] = Dropdown::getDropdownName('glpi_taskcategories', $data['taskcat']);
                  }

                  $cpt_tps++;
               }

               $PDF->SetLibelleActivite($p['REPORT_ACTIVITE']);
               $PDF->SetTempsPasses($temps_passes);

               // Forfait
            } else {
               $PDF->SetForfait();
               $un_temps_passe[0] = Html::convDate($contract_days->fields['begin_date']);
               $un_temps_passe[1] = '';
               $un_temps_passe[2] = Html::convDate($contract_days->fields['end_date']);
               $un_temps_passe[3] = '';
               $un_temps_passe[4] = $PDF->TotalTpsPassesArrondis(round($contract_days->fields['nbday'], 2));
               $temps_passes[]    = $un_temps_passe;

               if ($config->fields['useprice'] == PluginManageentitiesConfig::NOPRICE) {
                  $tickettasks            = new TicketTask();
                  $tasks_data             = $tickettasks->find(['tickets_id' => $p['REPORT_ID']]);
                  $tasks_data             = array_shift($tasks_data);
                  $p['REPORT_ACTIVITE'][] = Dropdown::getDropdownName('glpi_taskcategories', $tasks_data['taskcategories_id']);
               }
               $PDF->SetLibelleActivite($p['REPORT_ACTIVITE']);

               $PDF->SetTempsPasses($temps_passes);
            }

            //Déplacement
            if ($manageentities_contract_data['moving_management']) {
               $PDF->SetDeplacement(true);
               if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
                  $time_in_sec      = $manageentities_contract_data['duration_moving'];
                  $time_deplacement = ($time_in_sec * $p['number_moving']) / HOUR_TIMESTAMP;
                  $PDF->SetNombreDeplacement($time_deplacement);
               } else {
                  $time_in_sec      = $manageentities_contract_data['duration_moving'];
                  $time_deplacement = (($time_in_sec * $p['number_moving'] / HOUR_TIMESTAMP) / $nbhour);
                  $PDF->SetNombreDeplacement($PDF->TotalTpsPassesArrondis($time_deplacement));

               }
            }
         }
      }

      // On dessine le document.
      $PDF->DrawCri();

      //for insert into table cridetails
      $totaltemps_passes = $PDF->TotalTpsPassesArrondis($_SESSION["glpi_plugin_manageentities_total"]);

      /* Génération du fichier et enregistrement de la liaisons en base. */

      $name         = "CRI - " . $PDF->GetNoCri();
      $filename     = $name . ".pdf";
      $savepath     = GLPI_TMP_DIR . "/";
      $seepath      = GLPI_PLUGIN_DOC_DIR . "/manageentities/";
      $savefilepath = $savepath . $filename;
      $seefilepath  = $seepath . $filename;

      if ($config->fields["backup"] == 1 && $p['enregistrement']) {
         $PDF->Output($savefilepath, 'F');

         $input                          = [];
         $input["entities_id"]           = $job->fields["entities_id"];
         $input["name"]                  = addslashes($name);
         $input["filename"]              = addslashes($filename);
         $input["_filename"][0]          = addslashes($filename);
         $input["upload_file"]           = $filename;
         $input["documentcategories_id"] = $config->fields["documentcategories_id"];
         $input["mime"]                  = "application/pdf";
         $input["date_mod"]              = date("Y-m-d H:i:s");
         $input["users_id"]              = Session::getLoginUserID();
         $input["tickets_id"]            = $p['REPORT_ID'];

         $doc = new document;
         if (empty($p['documents_id'])) {
            $newdoc = $doc->add($input);
         } else {
            $doc->getFromDB($p['documents_id']);
            $input['current_filepath'] = $filename;
            $input['id']               = $p['documents_id'];
            $newdoc                    = $p['documents_id'];

            // If update worked, delete old file and directory
            $doc->update($input);
         }

         $withcontract = 0;
         if ($sous_contrat == true)
            $withcontract = 1;

         $values                                      = [];
         $values["entities_id"]                       = $job->fields["entities_id"];
         $values["date"]                              = $infos_date[2];
         $values["documents_id"]                      = $newdoc;
         $values["plugin_manageentities_critypes_id"] = $criType_id;
         $values["withcontract"]                      = $withcontract;
         $values["contracts_id"]                      = $p['CONTRAT'];
         $values["realtime"]                          = $totaltemps_passes;
         $values["technicians"]                       = $intervenants;
         $values["tickets_id"]                        = $p['REPORT_ID'];
         $values["number_moving"]                     = $p['number_moving'];

         $restrict   = ["`glpi_plugin_manageentities_cridetails`.`entities_id`" => $job->fields['entities_id'],
                        "`glpi_plugin_manageentities_cridetails`.`tickets_id`"  => $job->fields['id']];
         $dbu        = new DbUtils();
         $cridetails = $dbu->getAllDataFromTable("glpi_plugin_manageentities_cridetails", $restrict);
         $cridetail  = reset($cridetails);

         $PluginManageentitiesCriDetail = new PluginManageentitiesCriDetail();
         if (empty($cridetail)) {
            $newID = $PluginManageentitiesCriDetail->add($values);
         } else {
            $values["id"] = $cridetail['id'];
            $PluginManageentitiesCriDetail->update($values);
         }

//         if(isset($p['download']) && $p['download'] == 1){
//            echo "<IFRAME style='width:100%;height:90%' src='" . $CFG_GLPI['root_doc'] . "/plugins/manageentities/front/cri.send.php?file=_plugins/manageentities/$filename&seefile=1' scrolling=none frameborder=1></IFRAME>";

//         $doc = new Document();
//         $doc->getFromDB( $values["documents_id"]);
//         $this->send($doc);
//         }

         $this->CleanFiles($seepath);
      } else {
         //Sauvegarde du PDF dans le fichier 
         $PDF->Output($seefilepath, 'F');

         if ($config->fields["backup"] == 1) {
            echo "<form method='post' name='formReport'>";
            echo "<input type='hidden' name='REPORT_ID' value='" . $p['REPORT_ID'] . "' >";
            echo "<input type='hidden' name='REPORT_SOUS_CONTRAT' value='$sous_contrat' >";
            if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
               echo "<input type='hidden' name='REPORT_ACTIVITE' value='hour' >";
            } elseif ($config->fields['useprice'] == PluginManageentitiesConfig::PRICE) {
               echo "<input type='hidden' name='REPORT_ACTIVITE' value='" . $p['REPORT_ACTIVITE'] . "' />";
            } else {
               echo "<input type='hidden' name='REPORT_ACTIVITE' value='noprice' />";
            }
            $p['REPORT_DESCRIPTION'] = stripcslashes($p['REPORT_DESCRIPTION']);
            $p['REPORT_DESCRIPTION'] = str_replace("\\\\", "\\", $p['REPORT_DESCRIPTION']);
            $p['REPORT_DESCRIPTION'] = str_replace("\\'", "'", $p['REPORT_DESCRIPTION']);
            $p['REPORT_DESCRIPTION'] = str_replace("<br>", " ", $p['REPORT_DESCRIPTION']);
            $p['REPORT_DESCRIPTION'] = str_replace("’", "'", $p['REPORT_DESCRIPTION']);

            echo "<textarea style='display:none;' name='REPORT_DESCRIPTION' cols='100' rows='8'>" . $p['REPORT_DESCRIPTION'] . "</textarea>";
            echo "<input type='hidden' name='INTERVENANTS' value='$intervenants' >";
            echo "<input type='hidden' name='documents_id' value='" . $p['documents_id'] . "' >";
            echo "<input type='hidden' name='CONTRAT' value='" . $p['CONTRAT'] . "' >";
            echo "<input type='hidden' name='CONTRACTDAY' value='" . $p['CONTRACTDAY'] . "' >";
            echo "<input type='hidden' name='WITHOUTCONTRACT' value='" . $p['WITHOUTCONTRACT'] . "' >";
            echo "<input type='hidden' name='number_moving' value='" . $p['number_moving'] . "' >";

            $params = ['job'      => $job->fields['id'],
                            'form'     => 'formReport',
                            'root_doc' => $CFG_GLPI['root_doc'],
                            'toupdate' => $options['toupdate']];
            echo "<p><input type='button' name='save_cri' value=\"" .
                 __('Save the intervention report', 'manageentities') . "\" class='submit' onClick='manageentities_loadCriForm(\"saveCri\", \"" . $options['modal'] . "\", " . json_encode($params) . ");'></p>";
            Html::closeForm();
         }

         echo "<IFRAME style='width:100%;height:90%' src='" . $CFG_GLPI['root_doc'] . "/plugins/manageentities/front/cri.send.php?file=_plugins/manageentities/$filename&seefile=1' scrolling=none frameborder=1></IFRAME>";


         //         if(empty($p['documents_id'])){
         //         echo "<IFRAME src='".$CFG_GLPI['root_doc']."/plugins/manageentities/front/cri.send.php?file=_plugins/manageentities/$filename&seefile=1' width='1000' height='1000' scrolling=auto frameborder=1></IFRAME>";
         //         } else {
         //            echo "<IFRAME src='".$CFG_GLPI['root_doc']."/front/document.send.php?docid=$p['documents_id']&tickets_id=$p['REPORT_ID']' width='1000' height='1000' scrolling=auto frameborder=1></IFRAME>";
         //         }
      }
   }

   /**
    * Request: task with time spent
    *
    * @param type  $join
    * @param type  $where
    * @param type  $p
    * @param type  $config
    *
    * @global type $DB
    *
    */
   function getTempsPasses($join, $where, $p, $condition, $nbhour) {
      global $DB;

      $query  = "SELECT taskcat,
                             date,
                             date_debut,
                             date_fin,
                             heure_debut,
                             heure_fin,
                             actiontime,
                             tps_passes,
                             is_usedforcount
                 FROM (
                       SELECT gf.id,
                              gf.taskcategories_id AS taskcat,
                              gf.date AS date,
                              gf.begin AS date_debut,
                              gf.end AS date_fin,
                              gf.begin AS heure_debut,
                              gf.end AS heure_fin,
                              gf.actiontime as actiontime,
                              ((gf.actiontime/3600) / " .
                $nbhour . ") AS tps_passes,
                              `glpi_plugin_manageentities_taskcategories`.`is_usedforcount` AS is_usedforcount
                     FROM glpi_tickettasks gf $join
                        WHERE gf.tickets_id = " . $p['REPORT_ID'] . " $where
                        $condition
                        
                          UNION
                       SELECT gf2.id,
                              gf2.taskcategories_id AS taskcat,
                              gf2.date AS date,
                              gf2.date AS date_debut,
                              gf2.date AS date_fin,
                              '-' AS heure_debut,
                              '-' AS heure_fin,
                              '-' AS actiontime,
                              ((gf2.actiontime/3600) / " .
                $nbhour . ") AS tps_passes,
                              '-' AS is_usedforcount
                       FROM glpi_tickettasks gf2
                        WHERE gf2.tickets_id = " . $p['REPORT_ID'] . " 
                         
                          AND gf2.id NOT IN (SELECT DISTINCT id FROM glpi_tickettasks gtp2)
                      ) t
                  ORDER BY t.date_debut ASC";
      $result = $DB->query($query);
      return $result;
   }

   function CleanFiles($dir) {
      //Efface les fichiers temporaires
      $t = time();
      $h = opendir($dir);
      while ($file = readdir($h)) {
         if (substr($file, 0, 3) == 'CRI' and substr($file, -4) == '.pdf') {
            $path = $dir . '/' . $file;
            //if ($t-filemtime($path)>3600)
            @unlink($path);
         }
      }
      closedir($h);
   }

   function send($doc) {

      $file = GLPI_DOC_DIR . "/" . $doc->fields['filepath'];

      if (!file_exists($file)) {
         die("Error file " . $file . " does not exist");
      }
      // Now send the file with header() magic
      header("Expires: Mon, 26 Nov 1962 00:00:00 GMT");
      header('Pragma: private'); /// IE BUG + SSL
      header('Cache-control: private, must-revalidate'); /// IE BUG + SSL
      header("Content-disposition: filename=\"" . $doc->fields['filename'] . "\"");
      header("Content-type: " . $doc->fields['mime']);

      readfile($file) or die ("Error opening file $file");
   }

}