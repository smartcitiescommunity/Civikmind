<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 moreticket plugin for GLPI
 Copyright (C) 2013-2016 by the moreticket Development Team.

 https://github.com/InfotelGLPI/moreticket
 -------------------------------------------------------------------------

 LICENSE

 This file is part of moreticket.

 moreticket is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 moreticket is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with moreticket. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * Class PluginMoreticketCloseTicket
 */
class PluginMoreticketCloseTicket extends CommonDBTM {

   static $types     = ['Ticket'];
   var    $dohistory = true;
   static $rightname = "plugin_moreticket";

   /**
    * Have I the global right to "create" the Object
    * May be overloaded if needed (ex KnowbaseItem)
    *
    * @return booleen
    **/
   static function canCreate() {

      if (static::$rightname) {
         return Session::haveRight(static::$rightname, UPDATE);
      }
      return false;
   }

   /**
    * Display moreticket-item's tab for each users
    *
    * @param CommonGLPI $item
    * @param int        $withtemplate
    *
    * @return array|string
    */
   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      $config = new PluginMoreticketConfig();

      if (!$withtemplate) {
         if ($item->getType() == 'Ticket'
             && $item->fields['status'] == Ticket::CLOSED
             && $config->closeInformations()) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               $dbu = new DbUtils();
               return self::createTabEntry(__('Close ticket informations', 'moreticket'),
                                           $dbu->countElementsInTable($this->getTable(),
                                                                      ["tickets_id" => $item->getID()]));
            }
            return __('Close ticket informations', 'moreticket');
         }
      }

      return '';
   }

   /**
    * Display tab's content for each users
    *
    * @static
    *
    * @param CommonGLPI $item
    * @param int        $tabnum
    * @param int        $withtemplate
    *
    * @return bool|true
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      $config = new PluginMoreticketConfig();

      if ($item->getType() == 'Ticket'
          && ($item->fields['status'] == Ticket::CLOSED)
          && $config->closeInformations()) {

         self::showForTicket($item);
      }

      return true;
   }

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    *
    * @param int $nb
    *
    * @return string|translated
    */
   public static function getTypeName($nb = 0) {

      return __('Close ticket informations', 'moreticket');
   }

   // Check the mandatory values of forms
   /**
    * @param $values
    *
    * @return bool
    */
   static function checkMandatory($values) {
      $checkKo = [];

      $config = new PluginMoreticketConfig();

      $mandatory_fields = ['solution' => __('Solution description', 'moreticket')];

      if ($config->mandatorySolutionType() == true) {
         $mandatory_fields['solutiontypes_id'] = _n('Solution type', 'Solution types', 1);
      }

      $msg = [];

      foreach ($values as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if (empty($value)) {
               $msg[]     = $mandatory_fields[$key];
               $checkKo[] = 1;
            }
         }
         $_SESSION['glpi_plugin_moreticket_close'][$key] = $value;
      }

      if (in_array(1, $checkKo)) {
         Session::addMessageAfterRedirect(__('Ticket cannot be closed', 'moreticket') . "<br>" . _n('Mandatory field', 'Mandatory fields', 2) . " : " . implode(', ', $msg), false, ERROR);
         return false;
      }
      return true;
   }

   /**
    * @param Ticket $item
    *
    * @return bool
    */
   static function showForTicket(Ticket $item) {

      if (!self::canView()) {
         return false;
      }

      $canedit = ($item->canUpdate() && self::canUpdate());

      $dbu = new DbUtils();

      echo "<form name='form' method='post' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";

      echo "<div align='center'><table class='tab_cadre_fixe'>";
      echo "<tr><th colspan='2'>" . __('Close ticket informations', 'moreticket') . "</th></tr>";

      // Writer
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Writer');
      echo "</td>";
      echo "<td>";
      echo $dbu->getUserName(Session::getLoginUserID());
      echo "<input name='requesters_id' type='hidden' value='" . Session::getLoginUserID() . "'>";
      echo "</td>";
      echo "</tr>";

      // Date
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo "</td>";
      echo "<td>";
      Html::showDateTimeField("date", ['value' => date('Y-m-d H:i:s')]);
      echo "</td>";
      echo "</tr>";

      // Comments
      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Comments');
      echo "</td>";
      echo "<td>";
      echo "<textarea cols='80' rows='8' name='comment'></textarea>";
      echo "</td>";
      echo "</tr>";

      // Documents
      echo "<tr class='tab_bg_1'>";
      echo "<td colspan='2' style='padding:10px 20px 0px 20px'>";
      echo Html::file();
      echo "(" . Document::getMaxUploadSize() . ")&nbsp;";
      echo "</td>";
      echo "</tr>";

      if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='6'>";
         echo "<input type='submit' name='add' class='submit' value='" . _sx('button', 'Add') . "' >";
         echo "<input type='hidden' name='tickets_id' class='submit' value='" . $item->fields['id'] . "' >";
         echo "<input type='hidden' name='items_id' class='submit' value='" . $item->fields['id'] . "' >";
         echo "<input type='hidden' name='itemtype' class='submit' value='Ticket' >";
         echo "</td>";
         echo "</tr>";
      }
      echo "</table></div>";
      Html::closeForm();

      // List
      self::showList($item, $canedit);
   }

   /**
    * Provides search options configuration. Do not rely directly
    * on this, @see CommonDBTM::searchOptions instead.
    *
    * @since 9.3
    *
    * This should be overloaded in Class
    *
    * @return array a *not indexed* array of search options
    *
    * @see https://glpi-developer-documentation.rtfd.io/en/master/devapi/search.html
    **/
   public function rawSearchOptions() {

      $tab = parent::rawSearchOptions();

      $tab[] = [
         'id'                 => '10',
         'table'              => $this->getTable(),
         'field'              => 'date',
         'name'               => __('Date'),
         'datatype'           => 'datetime',
         'massiveaction'      => false
      ];

      $tab[] = [
         'id'                 => '11',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __('Comments'),
         'datatype'           => 'text',
         'massiveaction'      => true
      ];

      $tab[] = [
         'id'                 => '12',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'name'               => __('Writer'),
         'datatype'           => 'dropdown',
         'linkfield'          => 'requesters_id',
         'massiveaction'      => false
      ];

      return $tab;
   }

   /**
    * Print the wainting ticket form
    *
    * @param $item
    * @param $canedit
    *
    * @return Nothing
    * @internal param int $ID ID of the item
    * @internal param array $options - target filename : where to go when done.*     - target filename : where to go
    *    when done.
    *     - withtemplate boolean : template or basic item
    */
   static function showList($item, $canedit) {

      // validation des droits
      if (!self::canView()) {
         return false;
      }

      if (isset($_REQUEST["start"])) {
         $start = $_REQUEST["start"];
      } else {
         $start = 0;
      }

      $rand = mt_rand();

      // Get close informations
      $data = self::getCloseTicketFromDB($item->getField('id'), ['start' => $start,
                                                                 'limit' => $_SESSION['glpilist_limit']]);
      $dbu  = new DbUtils();
      $number = $dbu->countElementsInTable("glpi_plugin_moreticket_closetickets",
                                           ['tickets_id' => $item->getField('id')]);
      if ($number == 0) {
         echo "<div class='center'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th>" . __('No historical') . "</th></tr>";
         echo "</table>";
         echo "</div><br>";

      } else {
         $doc = new Document();
         echo "<div class='center'>";
         // Display the pager
         Html::printAjaxPager(__('Close ticket informations', 'moreticket'), $start, $number);

         if ($canedit) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['item' => __CLASS__, 'container' => 'mass' . __CLASS__ . $rand];
            Html::showMassiveActions($massiveactionparams);
         }

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr>";
         echo "<th width='10'>";
         if ($canedit) {
            echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
         }
         echo "</th>";
         echo "<th>" . __('Date') . "</th>";
         echo "<th>" . __('Comments') . "</th>";
         echo "<th>" . __('Writer') . "</th>";
         echo "<th>" . __('Document') . "</th>";
         echo "</tr>";

         $dbu = new DbUtils();

         foreach ($data as $closeTicket) {
            echo "<tr class='tab_bg_2'>";
            echo "<td width='10'>";
            if ($canedit) {
               Html::showMassiveActionCheckBox(__CLASS__, $closeTicket['id']);
            }
            echo "</td>";
            echo "<td>";
            echo Html::convDateTime($closeTicket['date']);
            echo "</td>";
            echo "<td>";
            echo $closeTicket['comment'];
            echo "</td>";
            echo "<td>";
            echo $dbu->getUserName($closeTicket['requesters_id']);
            echo "</td>";
            echo "<td>";
            if ($doc->getFromDB($closeTicket['documents_id'])) {
               echo $doc->getLink();
            }
            echo "</td>";
            echo "</tr>";
         }

         if ($canedit) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
         echo "</table>";
         echo "</div>";
         Html::printAjaxPager(__('Close ticket informations', 'moreticket'), $start, count($data));
      }
   }

   /**
    * Get close ticket informations
    *
    * @param type       $tickets_id
    * @param array|type $options
    *
    * @return bool
    */
   static function getCloseTicketFromDB($tickets_id, $options = []) {
      $dbu  = new DbUtils();
      $data = $dbu->getAllDataFromTable("glpi_plugin_moreticket_closetickets",
                                        ['tickets_id' => $tickets_id]+
                                        ['ORDER' => 'date DESC']+
                                        ['START' => (int)$options['start']]+
                                        ['LIMIT' => (int)$options['limit']],
                                        false);

      return $data;
   }

   /**
    * Print the wainting ticket form
    *
    * @param $ID integer ID of the item
    * @param $options array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    * @return Nothing (display)
    * */
   function showForm($ID, $options = []) {
      global $CFG_GLPI;

      // validation des droits
      if (!$this->canView()) {
         return false;
      }

      $ticket = new Ticket();

      if ($ID > 0) {
         if (!$ticket->getFromDB($ID)) {
            $ticket->getEmpty();
         }
      } else {
         // Create item
         $ticket->getEmpty();
      }

      // If values are saved in session we retrieve it
      if (isset($_SESSION['glpi_plugin_moreticket_close'])) {
         foreach ($_SESSION['glpi_plugin_moreticket_close'] as $key => $value) {
            $ticket->fields[$key] = str_replace(['\r\n', '\r', '\n'], '', $value);
         }
      }

      unset($_SESSION['glpi_plugin_moreticket_close']);

      echo "<div class='spaced' id='moreticket_close_ticket'>";
      echo "</br>";
      echo "<table class='moreticket_close_ticket' id='cl_menu'>";
      echo "<tr><td>";
      echo _n('Solution template', 'Solution templates', 1) . "&nbsp;:&nbsp;&nbsp;";
      $rand_template = mt_rand();
      $rand_text     = mt_rand();
      $rand_type     = mt_rand();
      SolutionTemplate::dropdown(['value'  => 0,
                                       'entity' => $ticket->getEntityID(),
                                       'rand'   => $rand_template,
                                       // Load type and solution from bookmark
                                       'toupdate'
                                                => ['value_fieldname'
                                                                     => 'value',
                                                         'to_update' => 'solution' . $rand_text,
                                                         'url'       => $CFG_GLPI["root_doc"] .
                                                                        "/ajax/solution.php",
                                                         'moreparams'
                                                                     => ['type_id'
                                                                              => 'dropdown_solutiontypes_id' .
                                                                                 $rand_type]]]);

      echo "</td></tr>";

      echo "<tr><td>";
      echo _n('Solution type', 'Solution types', 1);
      $config = new PluginMoreticketConfig();
      if ($config->mandatorySolutionType() == true) {
         echo "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      }
      Dropdown::show('SolutionType',
                     ['value'  => $ticket->getField('solutiontypes_id'),
                           'rand'   => $rand_type,
                           'entity' => $ticket->getEntityID()]);
      echo "</td></tr>";
      echo "<tr><td>";
      echo __('Solution description', 'moreticket') . "&nbsp;:&nbsp;<span class='red'>*</span>&nbsp;";
      $rand = mt_rand();
      Html::initEditorSystem("solution" . $rand);
      if (!isset($ticket->fields['solution'])) {
         $ticket->fields['solution'] = '';
      }
      echo "<div id='solution$rand_text'>";
      echo "<textarea id='solution$rand' name='solution' rows='3'>" . stripslashes($ticket->fields['solution']) . "</textarea></div>";
      echo "</td></tr>";
      echo "</table>";
      echo "</div>";
   }

   // Hook done on before add ticket - checkMandatory
   /**
    * @param $item
    *
    * @return bool
    */
   static function preAddCloseTicket($item) {
      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      $config = new PluginMoreticketConfig();
      if (isset($config->fields['use_solution']) && $config->useSolution()) {
         // Get allowed status
         $solution_status = array_keys(json_decode($config->solutionStatus(), true));

         // Then we add tickets informations
         if (isset($item->input['id']) && isset($item->input['status']) && in_array($item->input['status'], $solution_status)) {
            if (self::checkMandatory($item->input)) {
               // Add followup on immediate ticket closing
               if ($config->closeFollowup()
                   && $item->input['id'] == 0) {
                  $item->input['statusold'] = $item->input['status'];
                  $item->input['status'] = 0;
               }

               $item->input['solution'] = str_replace(['\r', '\n', '\r\n'], '', $item->input['solution']);
            } else {
               $_SESSION['saveInput'][$item->getType()] = $item->input;
               $item->input                             = [];
            }
         }
         return true;
      }

      return false;
   }

   static function postAddCloseTicket(Ticket $item) {

      if (!is_array($item->input) || !count($item->input)) {
         // Already cancel by another plugin
         return false;
      }

      $config = new PluginMoreticketConfig();
      if (isset($config->fields['use_solution']) && $config->useSolution()) {
         // Get allowed status
         $solution_status = array_keys(json_decode($config->solutionStatus(), true));

         // Then we add tickets informations
         if (isset($item->input['id'])
             && isset($item->input['status'])
             && $item->input['status'] ==0) {

            $input = [];
            $input['itemtype'] = 'Ticket';
            $input['items_id'] = $item->getID();
            $input['content'] = $item->input['solution'];
            $input['date_creation'] = $item->input['date'];
            $input['solutiontypes_id'] = $item->input['solutiontypes_id'];

            $itilsolution = new ITILSolution();
            $id = $itilsolution->add($input);

            //Validate solution if ticket closed
            if(in_array($item->input['status'], $solution_status)){
               $inputUpd['status'] = 3;
               $inputUpd['id'] = $id;
               $itilsolution->update($inputUpd);
            }

            $item->update(['id'=>$item->fields['id'],'status' => $item->input['statusold']]);
         }
      }

   }

   /**
    *
    */
   public function post_addItem() {

      $dbu = new DbUtils();

      $changes[0] = '0';
      $changes[1] = '';
      $changes[2] = sprintf(__('%1$s added closing informations', 'moreticket'),
                            $dbu->getUserName(Session::getLoginUserID()));
      Log::history($this->fields['tickets_id'], 'Ticket', $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);

      parent::post_addItem();
   }


   /**
    * @param int $history
    *
    * @return nothing|void
    */
   public function post_updateItem($history = 1) {

      $dbu = new DbUtils();

      $changes[0] = '0';
      $changes[1] = '';
      $changes[2] = sprintf(__('%1$s updated closing informations', 'moreticket'),
                            $dbu->getUserName(Session::getLoginUserID()));
      Log::history($this->fields['tickets_id'], 'Ticket', $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);

      parent::post_updateItem();
   }


   /**
    * @param int $history
    *
    * @return nothing|void
    */
   public function post_purgeItem($history = 1) {

      $dbu = new DbUtils();

      $changes[0] = '0';
      $changes[1] = '';
      $changes[2] = sprintf(__('%1$s deleted closing informations', 'moreticket'),
                            $dbu->getUserName(Session::getLoginUserID()));
      Log::history($this->fields['tickets_id'], 'Ticket', $changes, 0, Log::HISTORY_LOG_SIMPLE_MESSAGE);

      parent::post_updateItem();
   }

   /**
    * Cleaning the information entered in the ticket for adding solution
    * but not useful so delete to not add solution.
    *
    * @param \Ticket $ticket
    */
   static function cleanCloseTicket(Ticket $ticket) {

      $fields = ['solutiontemplates_id', 'solution', 'solutiontypes_id'];
      foreach ($fields as $field) {
         if (isset($ticket->input[$field])) {
            unset($ticket->input[$field]);
         }
      }

   }
}
