<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of mantis.
 *
 * mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2018 Teclib
 * @license   AGPLv3+ http://www.gnu.org/licenses/agpl.txt
 * @link      https://github.com/pluginsGLPI/mantis
 * @link      https://pluginsglpi.github.io/mantis/
 * -------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginMantis -> general class of Mantis plugin
 */
class PluginMantisMantis extends CommonDBTM {

   public static $rightname = "plugin_mantis_use";

   static function getTypeName($nb = 0) {

      return __('MantisBT', 'mantis');
   }

   /**
    * @see CommonGLPI::getTabNameForItem()
   **/
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType()=='Ticket'
            || $item->getType()=='Problem'
              || $item->getType()=='Change') {

         if ($_SESSION['glpishow_count_on_tabs']) {
            return self::createTabEntry(self::getTypeName(), self::countForItem($item));
         }
         return self::getTypeName();
      }
      return '';
   }

   /**
    * @see CommonGLPI::displayTabContentForItem()
   **/
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      global $CFG_GLPI;

      if ($item->getType()=='Ticket'
            || $item->getType()=='Problem'
              || $item->getType()=='Change') {

         if (Session::haveRightsOr('plugin_mantis_use', [READ, UPDATE])) {
            $PluginMantisMantis = new self();
            $PluginMantisMantis->showForm($item);
         } else {
            echo "<div align='center'><br><br><img src=\"" . $CFG_GLPI["root_doc"] .
                     "/pics/warning.png\" alt=\"warning\"><br><br>";
            echo "<b>" . __("Access denied") . "</b></div>";
         }

      }
   }

   /**
    * @param $item    CommonDBTM object
   **/
   public static function countForItem(CommonDBTM $item) {
      return countElementsInTable(
         self::getTable(),
         [
            'items_id' => $item->getID(),
            'itemtype' => $item->getType(),
         ]
      );
   }

   /**
    * Install all necessary elements for the plugin
    *
    * @return boolean True if success
    */
   static function install(Migration $migration) {
      global $DB;

      $table = getTableForItemType(__CLASS__);

      if (!$DB->tableExists($table)) {

         $query = "CREATE TABLE `".$table."` (
                     `id` int(11) NOT NULL AUTO_INCREMENT,
                     `items_id` int(11) NOT NULL,
                     `idMantis` int(11) NOT NULL,
                     `dateEscalade` date NOT NULL,
                     `itemtype` varchar(255) NOT NULL,
                     `user` int(11) NOT NULL,
                    PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query) or die($DB->error());

      } else {

         if (!$DB->fieldExists($table, 'itemType') && !$DB->fieldExists($table, 'itemtype')) {
            $migration->addField($table, 'itemtype', 'string');
            $migration->executeMigration();
         }

         if ($DB->fieldExists($table, 'itemType') && !$DB->fieldExists($table, 'itemtype')) {
            $migration->changeField($table, 'itemType', 'itemtype', 'string', []);
            $migration->executeMigration();
         }

         if ($DB->fieldExists($table, 'idTicket') && !$DB->fieldExists($table, 'items_id')) {
            $migration->changeField($table, 'idTicket', 'items_id', 'integer', []);
            $migration->executeMigration();
         }
      }

      //Create CLI automated task
      $cron = new CronTask();
      if (! $cron->getFromDBbyName(__CLASS__, 'mantis')) {
         CronTask::Register(__CLASS__, 'mantis', 7 * DAY_TIMESTAMP, [
               'param' => 24,
               'mode' => CronTask::MODE_EXTERNAL
         ]);
      }
   }

   /**
    * Uninstall previously installed elements of the plugin
    *
    * @return boolean True if success
    */
   static function uninstall(Migration $migration) {
      global $DB;

      CronTask::Unregister(__CLASS__);

      $table = getTableForItemType(__CLASS__);

      if ($DB->tableExists($table)) {
         $migration->dropTable($table);
         $migration->executeMigration();
      }
   }

   /**
    * Task Execution
    *
    * @param $task
    * @return bool
    */
   static function cronMantis($task) {
      self::updateTicket();
      self::updateAttachment();
      return true;
   }

   /**
    * Name and Info of Cron Task
    *
    * @param $name
    * @return array
    */
   static function cronInfo($name) {
      return [
            'description' => __("MantisBT synchronization", "mantis")
      ];
   }

   /**
    * Function to check if for each glpi tickets linked , all of his Docuement exist in MantisBT
    * If not, the cron upload the documents to mantisBT
    */
   static function updateAttachment() {
      global $DB;

      // Log
      Toolbox::logInFile("mantis", __('Starting update attachments.', 'mantis'));

      $res = self::getItemWhichIsLinked();

      $ws = new PluginMantisMantisws();
      $ws->initializeConnection();

      while ($row = $res->fetch_assoc()) {

         $itemType = $row['itemtype'];

         $item = new $itemType();
         $item->getFromDB($row['items_id']);

         if (in_array($item->fields['status'], $item->getClosedStatusArray())
               || in_array($item->fields['status'], $item->getSolvedStatusArray())) {

            // Log
            $msg = sprintf(
               __('GLPi object [%1$s:%2$s] is solved or closed.', 'mantis'),
                     $itemType,
                     $row['items_id']
            );
            Toolbox::logInFile("mantis", $msg);

         } else {

            $list_link = self::getLinkBetweenItemGlpiAndTicketMantis($row['items_id'], $itemType);

            while ($line = $list_link->fetch_assoc()) {

               $issue = $ws->getIssueById($line['idMantis']);
               $attachmentsMantisBT = $issue->attachments;

               $documents = self::getDocumentFromItem($row['items_id'], $itemType);

               foreach ($documents as $doc) {

                  if (!self::existAttachmentInMantisBT($doc, $attachmentsMantisBT)) {

                     $path = GLPI_DOC_DIR . "/" . $doc->getField('filepath');
                     if (file_exists($path)) {
                        $data = file_get_contents($path);
                        if (!$data) {

                           // Log
                           $msg = sprintf(__('Can\'t load GLPi file [%1$s].', 'mantis'),
                                          $doc->getField('filename'));
                           Toolbox::logInFile("mantis", $msg);
                        } else {

                           $id_data = $ws->addAttachmentToIssue($line['idMantis'],
                                                                $doc->getField('filename'),
                                                                $doc->getField('mime'), $data);

                           if (!$id_data) {
                              $id_attachment[] = $id_data;

                              // Log
                              $msg = sprintf(__('Can\'t send GLPi file [%1$s] to MantisBD.', 'mantis'),
                                             $doc->getField('filename'));
                              Toolbox::logInFile("mantis", $msg);
                           }
                        }
                     } else {

                        // Log
                        $msg = sprintf(__('GLPi file [%1$s] doesn\'t exists.', 'mantis'),
                                       $doc->getField('filename'));
                        Toolbox::logInFile("mantis", $msg);
                     }
                  } else {

                     // Log
                     $msg = sprintf(__('GLPi file [%1$s] already exists in MantisBT issue.', 'mantis'),
                                    $doc->getField('filename'));
                     Toolbox::logInFile("mantis", $msg);

                  }
               }
            }
         }
      }
   }

   /**
    * this function check the status of mantis issue linked to a ticket
    * If status == status to close glpi ticket then the cron closes the ticket
    */
   static function updateTicket() {

      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      if ($conf->getField('etatMantis')) {

         $etat_mantis = $conf->getField('etatMantis');
         $ws = new PluginMantisMantisws();
         $ws->initializeConnection();

         $res = self::getItemWhichIsLinked();

         while ($row = $res->fetch_assoc()) {

            $itemType = $row['itemtype'];

            $item = new $itemType();
            $item->getFromDB($row['items_id']);

            if (in_array($item->fields['status'], $item->getClosedStatusArray())
                  || in_array($item->fields['status'], $item->getSolvedStatusArray())) {

               // Log
               $msg = sprintf(
                  __('GLPi object [%1$s:%2$s] is already solved or closed.', 'mantis'),
                        $itemType,
                        $row['items_id']
               );
               Toolbox::logInFile("mantis", $msg);

            } else {

               $list_link = self::getLinkBetweenItemGlpiAndTicketMantis($row['items_id'], $itemType);

               $list_ticket_mantis = [];
               while ($line = $list_link->fetch_assoc()) {
                  $mantis = $ws->getIssueById($line['idMantis']);
                  $list_ticket_mantis[] = $mantis;
               }

               if (self::checkAllMantisBTStatus($list_ticket_mantis, $etat_mantis)) {

                  $info_solved = self::getInfoSolved($list_ticket_mantis);

                  $solution = new ITILSolution();
                  $solution->add([
                     'itemtype'         => $item->getType(),
                     'items_id'         => $item->fields['id'],
                     'solutiontypes_id' => $conf->getField('solutiontypes_id'),
                     'content'          => Toolbox::addslashes_deep($info_solved),
                     '_from_mantis'     => true,
                  ]);
               }
            }
         }
      } else {
         $msg = __('Plugin configuration is not correct (MantisBT status is missing)', 'mantis');
         Toolbox::logInFile("mantis", $msg);
      }

   }

   /**
    * Function to check if $doc exists in MantisBT attachment
    *
    * @param $doc
    * @param $attachmentsMantisBT
    * @return bool
    */
   static function existAttachmentInMantisBT($doc, $attachmentsMantisBT) {
      if (!isset($doc->fields['filename'])) {
         return false;
      }

      foreach ($attachmentsMantisBT as $attachment) {
         if ($attachment->filename == $doc->fields['filename']) {
            return true;
         }
      }

      return false;
   }

   /**
    * Function to retrieve all document from ticket
    *
    * @param $idItem
    * @param $itemType
    * @return array
    */
   static function getDocumentFromItem($idItem, $itemType) {
      global $DB;

      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      $document = [];

      $query = "  SELECT `glpi_documents_items`.`documents_id`
                  FROM `glpi_documents_items`, `glpi_documents`
                  WHERE `glpi_documents`.`id` = `glpi_documents_items`.`documents_id`
                  AND `glpi_documents_items`.`itemtype` = '" . $itemType . "'
                  AND `glpi_documents_items`.`items_id` = '" . Toolbox::cleanInteger($idItem) . "'";

      if ($conf->fields['doc_categorie'] != 0) {
         $query.= " AND `glpi_documents`.`documentcategories_id` = '"
                        . Toolbox::cleanInteger($conf->fields['doc_categorie']) . "' ";
      }

      $res = $DB->query($query);

      while ($row = $res->fetch_assoc()) {
         $doc = new Document();
         $doc->getFromDB($row["documents_id"]);
         $document[] = $doc;
      }

      return $document;
   }

   /**
    * Function to extract issue from $list_tickket_mantis when they are the same status choice by user
    *
    * @param $list_ticket_mantis
    * @param $status
    * @return bool
    */
   private static function checkAllMantisBTStatus($list_ticket_mantis, $status) {

      if (count($list_ticket_mantis) == 0) {
         return false;
      }

      foreach ($list_ticket_mantis as $mantis_issue) {
         if ($mantis_issue->status->name != $status) {
            return false;
         }
      }

      return true;
   }

   /**
    * function to get id ticket Glpi which is linked
    *
    * @return Query
    */
   private static function getItemWhichIsLinked() {
      global $DB;

      $query = "SELECT  `glpi_plugin_mantis_mantis`.`items_id`, `glpi_plugin_mantis_mantis`.`itemtype`
               FROM `glpi_plugin_mantis_mantis`
               GROUP BY `glpi_plugin_mantis_mantis`.`items_id`,`glpi_plugin_mantis_mantis`.`itemtype`";

      return $DB->query($query);
   }

   /**
    * Function to get link between glpi ticket and mantisBT ticket for a glpi ticket
    *
    * @param $idItem
    * @param $itemType
    * @return Query
    */
   private static function getLinkBetweenItemGlpiAndTicketMantis($idItem, $itemType) {
      global $DB;

      $query = "SELECT `glpi_plugin_mantis_mantis`.*
               FROM `glpi_plugin_mantis_mantis`
               WHERE `glpi_plugin_mantis_mantis`.`items_id` = '" . Toolbox::cleanInteger($idItem) . "'
               AND `glpi_plugin_mantis_mantis`.`itemtype` = '" . $itemType . "'";

      return $DB->query($query);
   }

   /**
    * Function to get information in Note for each ticket MantisBT
    *
    * @param $list_ticket_mantis
    * @return string
    */
   private static function getInfoSolved($list_ticket_mantis) {
      $info = "";
      foreach ($list_ticket_mantis as &$ticket) {
         $notes = $ticket->notes;
         foreach ($notes as &$note) {
            $info .= $ticket->id . " - " . $note->reporter->name . " : " . $note->text . "<br/>";
         }
      }
      return $info;
   }

   /**
    * Function to show the form of plugin
    *
    * @param $item
    */
   public function showForm($item) {
      global $CFG_GLPI;

      $ws = new PluginMantisMantisws();

      // recover the first and only record
      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      // check if Web Service Mantis works fine
      if ($ws->testConnectionWS($conf->getField('host'),
                                $conf->getField('url'),
                                $conf->getField('login'),
                                Toolbox::sodiumDecrypt($conf->getField('pwd')))) {

         if ($item->fields['status'] == $conf->fields['neutralize_escalation']
               || $item->fields['status'] > $conf->fields['neutralize_escalation']) {

            $this->getFormForDisplayInfo($item, $item->getType());

         } else {

            if (self::canView() || self::canUpdate()) {

               $this->getFormForDisplayInfo($item, $item->getType());

               if (self::canUpdate()) {

                  $this->displayBtnToLinkissueGlpi($item);

               }
            }
         }
      } else {
         $msg = __('Plugin configuration is not correct (connection error).', 'mantis');
         echo "<div align='center'><br><br><img src=\"" . $CFG_GLPI["root_doc"] .
                  "/pics/warning.png\" alt=\"warning\"><br><br>";
         echo "<b>" . $msg . "</b></div>";
      }
   }

   /**
    * function to show action given by plugin
    *
    * @param $item
    */
   public function displayBtnToLinkissueGlpi($item) {
      global $CFG_GLPI;

      $config = new PluginMantisConfig();
      $config->getFromDB(1);

      $neutralize_escalation = false;
      if ($item->fields['status'] == $config->fields['neutralize_escalation']
            || $item->fields['status'] > $config->fields['neutralize_escalation']) {
         $neutralize_escalation = true;
      }

      if (!$neutralize_escalation) {
         $web_dir = Plugin::getWebDir('mantis');

         echo "<div id='popupLinkGlpiIssuetoMantisIssue'></div>";

         echo "<div id='popupLinkGlpiIssuetoMantisProject'></div>";

         Ajax::createModalWindow('popupLinkGlpiIssuetoMantisIssue',
                              $web_dir . '/front/mantis.form.php?action=linkToIssue&idTicket=' .
                              $item->fields['id'] . '&itemType=' . $item->getType(),
                              ['title'  =>  __('Link to an existing MantisBT issue', 'mantis'),
                               'width'  => 650,
                               'height' => 750]
         );

         Ajax::createModalWindow('popupLinkGlpiIssuetoMantisProject',
                              $web_dir . '/front/mantis.form.php?action=linkToProject&idTicket=' .
                              $item->fields['id'] . '&itemType=' . $item->getType(),
                              ['title'  => __('Create a new MantisBT issue', 'mantis'),
                               'width'  => 650,
                               'height' => 750]
         );

         echo "<table id='table1'  class='tab_cadre_fixe' >";
         echo "<th colspan='6'>" . __("MantisBT actions", "mantis") . "</th>";
         echo "<tr class='tab_bg_1'>";

         echo "<td style='text-align: center;'>";
         echo "<input onclick='popupLinkGlpiIssuetoMantisIssue.dialog(\"open\");'
                     value='" . __('Link to an existing MantisBT issue', 'mantis') . "'
                  class='submit' style='width : 200px;'></td>";

         echo "<td style='text-align: center;'>";
         echo "<input onclick='popupLinkGlpiIssuetoMantisProject.dialog(\"open\");'
                     value='" . __('Create a new MantisBT issue', 'mantis') . "'
                  class='submit' style='width : 250px;'></td>";

         echo "</tr>";
         echo "</table>";
      }
   }

   /**
    * Form for delete Link Between Glpi ticket and MantisBT ticket or MantisBT ticket
    *
    * @param $id_link
    * @param $id_Item
    * @param $id_mantis
    * @param $itemType
    */
   public function getFormToDelLinkOrIssue($id_link, $id_Item, $id_mantis, $itemType) {
      global $CFG_GLPI;

      $ws = new PluginMantisMantisws();
      $ws->initializeConnection();
      $issue = $ws->getIssueById($id_mantis);

      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      echo "<form action='#' id=" . $id_link . ">";
      echo "<table id=" . $id_link . " class='tab_cadre' cellpadding='5' >";
      echo "<th colspan='2'>" . __("What do you want to do ?", "mantis") . "</th>";

      echo "<tr class='tab_bg_1' >";
      echo "<td><input type='checkbox'  id='deleteLink" . $id_link . "' />";
         echo __("Only delete link between GLPi object AND MantisBT issue.", "mantis") . "</td>";
      echo "</tr>";

      if ($conf->fields['show_option_delete'] == 1 && $issue) {
         echo "<tr class='tab_bg_1'>";
         echo "<td><input type='checkbox' id='deleteIssue" . $id_link . "' >";
            echo __("Force delete MantisBT issue (and GLPi object link).", "mantis") . "</td>";
         echo "</tr>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td><input  id=" . $id_link . " name='delo'
               value='" . __('Delete') . "' class='submit'
               onclick='delLinkAndOrIssue(" . $id_link . "," . $id_mantis . "," . $id_Item . ");'></td>";
      echo "<td><div id='infoDel" . $id_link . "' ></div>";
      echo "<img id='waitDelete" . $id_link . "'
               src='" . Plugin::getWebDir('mantis') . "/pics/please_wait.gif'
               style='display:none;'/></td>";
      echo "</tr>";

      echo "<input type='hidden' name='idMantis" . $id_link . "' id='idMantis' value=" . $id_link . "/>";
      echo "<input type='hidden' name='id" . $id_link . "'       id='id'       value=" . $id_mantis . "/>";
      echo "<input type='hidden' name='idTicket" . $id_link . "' id='idticket' value=" . $id_Item . "/>";
      echo "<input type='hidden' name='itemType" . $id_link . "' id='itemType' value=" . $itemType . "/>";

      echo "</table>";
      Html::closeForm(false);
   }

   /**
    * Form to link glpi ticket to Mantis Ticket
    *
    * @param $item
    * @param $itemType
    */
   public function getFormForLinkGlpiTicketToMantisTicket($item, $itemType) {
      global $CFG_GLPI;

      $pref = new PluginMantisUserpref();
      if (!$pref->getFromDB(Session::getLoginUserID())) {
         $pref->getEmpty();
         $pref->fields['users_id'] = Session::getLoginUserID();
         $pref->fields['id'] = Session::getLoginUserID();
         $pref->add($pref->fields);
         $pref->updateInDB($pref->fields);
      }

      echo "<form action='#' >";
      echo "<table class='tab_cadre'cellpadding='5'>";
      echo "<th colspan='6'>" . __('Link to an existing MantisBT issue.', 'mantis') . "</th>";

      echo "<tr class='tab_bg_1'>";
      echo "<th width='100'>" . __('Id of MantisBT issue', 'mantis') . "</th>";
      echo "<td>";
         echo "<input size='35' id='idMantis1' type='text' name='idMantis1' onkeypress='if(event.keyCode==13)findProjectById();'/>";
         echo "<br /><a href='#' onclick='findProjectById();'>".__('Click to load issue from MantisBT', 'mantis')."</a>";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("MantisBT field for GLPi fields", "mantis") . "</th><td>";
      echo Dropdown::showFromArray('fieldsGlpi1', [],
                                    ['rand' => '', 'display' => false]
      );
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("MantisBT field for the link URL to the GLPi object", "mantis") . "</th><td>";
      echo Dropdown::showFromArray('fieldUrl1', [],
                                    ['rand' => '', 'display' => false]
      );
      echo "</td></tr>";

      $checked = ($pref->fields['followAttachment']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("Attachments", "mantis") . "</th>";
      echo "<td><input type='checkbox' name='followAttachment1' id='followAttachment1'
                           onclick='getAttachment1();'style='cursor: pointer;' " . $checked . ">"
                           . __("Forward attachments", "mantis")
                           . "<div id='attachmentforLinkToProject1' ><div/></td></tr>";

      if ($itemType == 'Ticket') {
         $checked = ($pref->fields['followFollow']) ? "checked" : "";
         echo "<tr class='tab_bg_1'>";
         echo "<th>" . __('Followups') . "</th>";
         echo "<td><input type='checkbox' name='followFollow' id='followFollow' " . $checked . ">"
                           . __("Forward ticket followup", "mantis") . "</td></tr>";
      }

      if ($itemType == 'Ticket') {
         $checked = ($pref->fields['followTask']) ? "checked" : "";
         echo "<tr class='tab_bg_1'>";
         echo "<th>" . __('Tasks') . "</th>";
         echo "<td><input type='checkbox' name='followTask' id='followTask' " . $checked . " >"
                              . __("Forward ticket tasks", "mantis") . "</td></tr>";
      }

      $checked = ($pref->fields['followTitle']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Title') . "</th>";
      echo "<td><input type='checkbox' name='followTitle' id='followTitle' " . $checked . " >"
                              . __("Forward title", "mantis") . "</td></tr>";

      $checked = ($pref->fields['followDescription']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Description') . "</th>";
      echo "<td><input type='checkbox' name='followDescription' id='followDescription' " . $checked . " >"
                              . __("Forward description", "mantis") . "</td></tr>";

      $checked = ($pref->fields['followCategorie']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Category') . "</th>";
      echo "<td><input type='checkbox' name='followCategorie' id='followCategorie' " . $checked . ">"
                              . __("Forward category", "mantis") . "</td></tr>";

      if ($itemType == 'Ticket') {
         $checked = ($pref->fields['followLinkedItem']) ? "checked" : "";
         echo "<tr class='tab_bg_1'>";
         echo "<th>" . _n('Linked ticket', 'Linked tickets', 2) . "</th>";
         echo "<td><input type='checkbox' name='linkedTicket' id='linkedTicket' " . $checked . ">"
                                 . __("Forward linked tickets", "mantis") . "</td></tr>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td><input  id='linktoIssue'  name='linktoIssue' value='" . __('Link') . "'
                     class='submit' onclick='linkIssueglpiToIssueMantis();'></td>";

      echo "<td width='150' height='20'>";
      echo "<div id='infoLinIssueGlpiToIssueMantis' ></div>";
      echo "<img id='waitForLinkIssueGlpiToIssueMantis' src='" . Plugin::getWebDir('mantis')
                           . "/pics/please_wait.gif' style='display:none;'/></td>";
      echo "</tr>";

      echo "<input type='hidden' name='idTicket1' id='idTicket1' value='" . $item . "'/>";
      echo "<input type='hidden' name='user1' id='user1' value='" . Session::getLoginUserID() . "'/>";
      echo "<input type='hidden' name='dateEscalade1' id='dateEscalade1' value='" . date("Y-m-d") . "'/>";
      echo "<input type='hidden' name='itemType1' id='itemType1' value='" . $itemType . "'/>";

      echo "</table>";
      Html::closeForm(false);
   }

   /**
    * Form to link glpi ticket to mantis project
    *
    * @param $idItem
    * @param $itemType
    */
   public function getFormForLinkGlpiTicketToMantisProject($idItem, $itemType) {
      global $CFG_GLPI;

      $config = new PluginMantisConfig();
      $config->getFromDB(1);

      $pref = new PluginMantisUserpref();
      if (!$pref->getFromDB(Session::getLoginUserID())) {
         $pref->getEmpty();
         $pref->fields['users_id'] = Session::getLoginUserID();
         $pref->fields['id'] = Session::getLoginUserID();
         $pref->add($pref->fields);
         $pref->updateInDB($pref->fields);
      }

      echo "<form action='#' >";
      echo "<table id='table2' class='tab_cadre' cellpadding='5'>";
      echo "<tr class='headerRow'><th colspan='6'>" . __("Create a new MantisBT issue", "mantis") . "</th></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th width='100'>" . __('Exact MantisBT project name', 'mantis') . "</th>";
      echo "<td id='tdSearch' height='24'>";
         echo "<input id='nameMantisProject' type='text' name='resume'
                     onkeypress='if(event.keyCode==13)findProjectByName();'/>";
         echo "<br /><a href='#' onclick='findProjectByName();'>"
                           . __('Click to load project from MantisBT', 'mantis') . "</a>&nbsp;";
      echo "</td>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Category') . "</th><td>";
      echo Dropdown::showFromArray('categorie', [],
                                    ['rand' => '', 'display' => false]
      );
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("MantisBT field for GLPi fields", "mantis") . "</th><td>";
      echo Dropdown::showFromArray('fieldsGlpi', [],
                                    ['rand' => '', 'display' => false]
      );
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("MantisBT field for the link URL to the GLPi object", "mantis") . "</th><td>";
      echo Dropdown::showFromArray('fieldUrl', [],
                                    ['rand' => '', 'display' => false]
      );
      echo "</td></tr>";

      if (!$config->fields['enable_assign']) {
         echo "<tr class='tab_bg_1'>";
         echo "<th>" . __('Assign') . "</th><td>";
         echo Dropdown::showFromArray('assignation', [],
                                       ['rand' => '', 'display' => false]
         );
         echo "</td></tr>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Summary') . "</th>";
      echo "<td><input id='resume' type='text' name='resume' size=35/></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Description') . "</th>";
      echo "<td><textarea  rows='5' cols='55' name='description' id='description'></textarea></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("Steps to reproduce", "mantis") . "</th>";
      echo "<td><textarea  rows='5' cols='55' name='stepToReproduce' id='stepToReproduce'></textarea></td></tr>";

      $checked = ($pref->fields['followAttachment']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __("Attachments", "mantis") . "</th>";
      echo "<td><input type='checkbox' name='followAttachment' id='followAttachment'
                           onclick='getAttachment();'style='cursor: pointer;' " . $checked . ">"
                           . __("Forward attachments", "mantis")
                           . "<div id='attachmentforLinkToProject' ><div/></td></tr>";

      if ($itemType == 'Ticket') {
         $checked = ($pref->fields['followFollow']) ? "checked" : "";
         echo "<tr class='tab_bg_1'>";
         echo "<th>" . __('Followups') . "</th>";
         echo "<td><input type='checkbox' name='followFollow' id='followFollow' " . $checked . ">"
                           . __("Forward followups", "mantis") . "</td></tr>";
      }

      if ($itemType == 'Ticket') {
         $checked = ($pref->fields['followTask']) ? "checked" : "";
         echo "<tr class='tab_bg_1'>";
         echo "<th>" . __('Tasks') . "</th>";
         echo "<td><input type='checkbox' name='followTask' id='followTask' " . $checked . " >"
                              . __("Forward tasks", "mantis") . "</td></tr>";
      }

      $checked = ($pref->fields['followTitle']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Title') . "</th>";
      echo "<td><input type='checkbox' name='followTitle' id='followTitle' " . $checked . " >"
                              . __("Forward title", "mantis") . "</td></tr>";

      $checked = ($pref->fields['followDescription']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Description') . "</th>";
      echo "<td><input type='checkbox' name='followDescription' id='followDescription' " . $checked . " >"
                              . __("Forward description", "mantis") . "</td></tr>";

      $checked = ($pref->fields['followCategorie']) ? "checked" : "";
      echo "<tr class='tab_bg_1'>";
      echo "<th>" . __('Category') . "</th>";
      echo "<td><input type='checkbox' name='followCategorie' id='followCategorie' " . $checked . ">"
                              . __("Forward category", "mantis") . "</td></tr>";

      if ($itemType == 'Ticket') {
         $checked = ($pref->fields['followLinkedItem']) ? "checked" : "";
         echo "<tr class='tab_bg_1'>";
         echo "<th>" . _n('Linked ticket', 'Linked tickets', 2) . "</th>";
         echo "<td><input type='checkbox' name='linkedTicket' id='linkedTicket' " . $checked . ">"
                                 . __("Forward linked tickets", "mantis") . "</td></tr>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td><input id='linktoProject' onclick='linkIssueglpiToProjectMantis();'
                  name='linktoProject' value='" . __("Link", "mantis") . "' class='submit'></td>";
      echo "<td width='150'>";
      echo "<div id='infoLinkIssueGlpiToProjectMantis' ></div>";
      echo "<img id='waitForLinkIssueGlpiToProjectMantis' src='" . Plugin::getWebDir('mantis') .
                                 "/pics/please_wait.gif' style='display:none;'/>";
      echo "</td></tr>";

      echo "</table>";

      echo "<input type='hidden' name='idTicket' id='idTicket' value='" . $idItem . "'/>";
      echo "<input type='hidden' name='user' id='user' value='" . Session::getLoginUserID() . "'/>";
      echo "<input type='hidden' name='dateEscalade' id='dateEscalade' value='" . date("Y-m-d") . "'/>";
      echo "<input type='hidden' name='itemType' id='itemType' value='" . $itemType . "'/>";

      Html::closeForm(false);
   }

   /**
    * Form to display information from MantisBT
    *
    * @param $item
    * @param $itemType
    */
   private function getFormForDisplayInfo($item, $itemType) {
      global $CFG_GLPI;

      $conf = new PluginMantisConfig();
      $conf->getFromDB(1);

      $neutralize_escalation = false;
      if ($item->fields['status'] == $conf->fields['neutralize_escalation']
            || $item->fields['status'] > $conf->fields['neutralize_escalation']) {
         $neutralize_escalation = true;
      }

      $can_write = self::canUpdate();

      // get together the links from glpi ticket and mantis tickets
      $res = $this->getLinkBetweenGlpiAndMantis($item, $itemType);

      echo "<table id='table1' class='tab_cadre_fixe'>";
      echo "<th colspan='8'>" . __("List of linked MantisBT issues", "mantis") . "</th>";

      if ($res->num_rows > 0) {

         echo "<tr class='headerRow'>";
            echo "<th>" . __('Link') . "</th>";
            echo "<th>" . __('ID') . "</th>";
            echo "<th>" . __('Summary') . "</th>";
            echo "<th>" . __('Project') . "</th>";
            echo "<th>" . __('Status') . "</th>";
            echo "<th>" . __('Date') . "</th>";
            echo "<th>" . __('User') . "</th>";
            echo "<th>#</th>";
         echo "</tr>";

         $user = new User();
         $conf = new PluginMantisConfig();
         $ws = new PluginMantisMantisws();
         $ws->initializeConnection();

         $web_dir = Plugin::getWebDir('mantis');

         while ($row = $res->fetch_assoc()) {

            $user->getFromDB($row["user"]);
            $issue = $ws->getIssueById($row["idMantis"]);
            $conf->getFromDB(1);

            echo "<div id='popupToDelete" . $row['id'] . "'></div>";

            Ajax::createModalWindow('popupToDelete' . $row['id'],
                                    $web_dir
                                    . '/front/mantis.form.php?action=deleteIssue&id='
                                    . $row['id'] . '&idTicket=' . $row['items_id'] . '&idMantis='
                                    . $row['idMantis'] . '&itemType=' . $itemType,
                                    ['title'  => __('Delete'),
                                     'width'  => 550,
                                     'height' => 200]
            );

            echo "<tr>";

            if (!$issue) {
               echo "<td class='center'>
                        <img src='" . $web_dir . "/pics/cross16.png'/></td>";
               echo "<td>" . $row["idMantis"] . "</td>";
               echo "<td colspan='5' class='center'>"
                     . __('Error when loading MantisBT issue', 'mantis') . "</td>";
            } else {
               echo "<tr>";
               echo "<td class='center'>";
               echo "<a href='" . $conf->fields['host'] . "/view.php?id=" . $issue->id . "' target='_blank' >";
               echo "<img src='" . $web_dir . "/pics/arrowRight16.png'/>";
               echo "</a></td>";
               echo "<td class='center'>" . $issue->id . "</td>";
               echo "<td class='center'>" . stripslashes($issue->summary) . "</td>";
               echo "<td class='center'>" . $issue->project->name . "</td>";
               echo "<td class='center'>" . $issue->status->name . "</td>";
               echo "<td class='center'>" . $row["dateEscalade"] . "</td>";
               echo "<td class='center'>" . $user->getName() . "</td>";
            }

            if ($can_write && !$neutralize_escalation) {
               echo "<td class = 'center'>";
               echo "<img src='" . $web_dir . "/pics/bin16.png'
                              onclick='popupToDelete" . $row['id'] . ".dialog(\"open\")'
                              style='cursor: pointer;' title='" . __('Delete') . "'/></td>";
            } else {
               echo "<td>-</td>";
            }

            echo "</tr>";
         }

      } else {

         echo "<tr>";
         echo "<td class='center' colspan='8'>"
                  . __("This GLPi object is not linked to MantisBT", "mantis") . "</td>";
         echo "</tr>";

         if ($neutralize_escalation) {
            echo "<tr>";
            echo "<th colspan='8'>"
                     . __('Escalation to MantisBT is neutralized by GLPi status', "mantis") . "</th>";
            echo "</tr>";
         }
      }

      echo "</table>";
   }

   /**
    * Function to check if link between glpi items and mantis issue exists
    *
    * @param $idItem
    * @param $id_mantis
    * @param $itemType
    * @return true if succeed else false
    */
   public function IfExistLink($idItem, $id_mantis, $itemType) {
      return $this->getFromDBByCrit(
         [
            'items_id' => Toolbox::cleanInteger($idItem),
            'idMantis' => Toolbox::cleanInteger($id_mantis),
            'itemtype' => $itemType,
         ]
      );
   }

   /**
    * Function to find all links record for an item and itemType
    *
    * @param $item
    * @param $itemType
    * @return Query
    */
   public function getLinkBetweenGlpiAndMantis($item, $itemType) {
      global $DB;
      return $DB->query("SELECT `glpi_plugin_mantis_mantis`.*
                        FROM `glpi_plugin_mantis_mantis`
                        WHERE `glpi_plugin_mantis_mantis`
                        .`items_id` = '" . Toolbox::cleanInteger($item->getField('id')) . "'
                        AND `glpi_plugin_mantis_mantis`.`itemtype` = '" . $itemType . "'
                        ORDER BY `glpi_plugin_mantis_mantis`.`dateEscalade`");
   }

   /**
    * Function to find all links record for an item
    *
    * @return Query
    */
   public static function getAllLinkBetweenGlpiAndMantis() {
      global $DB;

      return $DB->query("SELECT `glpi_plugin_mantis_mantis`.* FROM `glpi_plugin_mantis_mantis`");
   }

   /**
    * Force solution user when solution comes from Mantis.
    *
    * @param ITILSolution $solution
    *
    * @return void
    */
   public static function forceSolutionUserOnSolutionAdd(ITILSolution $solution) {
      if (array_key_exists('_from_mantis', $solution->input) && $solution->input['_from_mantis']) {
         $conf = new PluginMantisConfig();
         $conf->getFromDB(1);
         $solution->input['users_id'] = $conf->getField('users_id');
         unset($solution->input['_from_mantis']);
      }
   }
}
