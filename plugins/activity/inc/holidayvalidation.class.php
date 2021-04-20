<?php

/*
 -------------------------------------------------------------------------
 Activity plugin for GLPI
 Copyright (C) 2019 by the Activity Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Activity.

 Activity is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Activity is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Activity. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * PluginActivityHolidayValidation class
 */
class PluginActivityHolidayValidation extends CommonDBChild {

   static public $items_id  = 'plugin_activity_holidays_id';
   static public $itemtype  = 'PluginActivityHoliday';
   static        $rightname = "plugin_activity";

   /**
    * functions mandatory
    * getTypeName(), canCreate(), canView()
    * */
   static function getTypeName($nb = 0) {
      return _n('Holiday validation', 'Holidays validation', $nb, 'activity');
   }

   /**
    * @param $tickets_id
    *
    * @return bool
    */
   static function canValidate($hId) {
      global $DB;

      $query  = "SELECT *
                FROM `glpi_plugin_activity_holidayvalidations`
                WHERE `plugin_activity_holidays_id` = '$hId'
                      AND users_id_validate = '" . Session::getLoginUserID() . "'";
      $result = $DB->query($query);

      if ($DB->numrows($result)) {
         return true;
      }
      return false;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if ($item->getType() == "PluginActivityHoliday") {
         return [PluginActivityHolidayValidation::getTypeName(1)];
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item->getType() == "PluginActivityHoliday") {
         if ($tabnum == 0) {
            $validation = new PluginActivityHolidayValidation();
            $validation->showSummary($item);
         }
      }
      return true;
   }

   function prepareInputForAdd($input) {
      //      $input['comment_validation'] = '';
      $input['submission_date'] = date('Y-m-d H:i');

      return parent::prepareInputForAdd($input);
   }

   function post_addItem() {

      $holiday = new PluginActivityHoliday();
      if ($holiday->getFromDB($this->fields['plugin_activity_holidays_id'])) {

         // Set global validation to waiting
         if ($holiday->fields['global_validation'] == PluginActivityCommonValidation::NONE) {
            $input['id']                = $this->fields['plugin_activity_holidays_id'];
            $input['global_validation'] = PluginActivityCommonValidation::WAITING;
            $holiday->update($input);
         }
      }
   }

   function prepareInputForUpdate($input) {
      $input['validation_date'] = date('Y-m-d H:i:s');

      if (isset($input['refuse_holiday']) && $input['refuse_holiday'] == 1) {
         $input['status'] = PluginActivityCommonValidation::REFUSED;
      }

      if (isset($input['accept_holiday']) && $input['accept_holiday'] == 1) {
         $input['status'] = PluginActivityCommonValidation::ACCEPTED;
      }

      if ($input['status'] == PluginActivityCommonValidation::REFUSED && $input['comment_validation'] == "") {
         Session::addMessageAfterRedirect(__('If approval is denied, specify a reason.'), false, ERROR);
         return false;
      }

      return parent::prepareInputForUpdate($input);
   }


   function post_updateItem($history = 1) {
      global $CFG_GLPI;

      $holiday = new PluginActivityHoliday();
      $holiday->getFromDB($this->fields['plugin_activity_holidays_id']);

      $condition = ["plugin_activity_holidays_id" => $this->fields['plugin_activity_holidays_id']];
      $dbu       = new DbUtils();
      $datas     = $dbu->getAllDataFromTable($this->getTable(), $condition);

      // Check if all holidaysValidation are validated or not
      //Set global validation to accepted to define one
      if (($holiday->fields['global_validation'] == PluginActivityCommonValidation::WAITING)
          && in_array("status", $this->updates)) {

         $input['id']                = $this->fields['plugin_activity_holidays_id'];
         $input['global_validation'] = self::computeValidationStatus($holiday);
         $holiday->update($input);
      }

      /*$isValidated = array(
         'allValidated' => 0,
         'allRefused'   => 0,
         'allWaiting'   => 0
      );
      $finalValidated = 0;
      if (sizeof($datas) > 0){
         foreach ($datas as $data) {
            if ($data['status'] == PluginActivityCommonValidation::ACCEPTED) {
               $isValidated['allValidated'] ++;
            } else if ($data['status'] == PluginActivityCommonValidation::REFUSED) {
               $isValidated['allRefused'] ++;
            } else {
               $isValidated['allWaiting'] ++;
            }

         }
      }

      if ($isValidated['allWaiting'] > 0 ){
         $finalValidated = PluginActivityCommonValidation::WAITING;
      }else if ( $isValidated['allValidated'] > 0 && $isValidated['allRefused'] == 0){
         $finalValidated = PluginActivityCommonValidation::ACCEPTED;
      }else if ( $isValidated['allValidated'] == 0 && $isValidated['allRefused'] > 0){
         $finalValidated = PluginActivityCommonValidation::REFUSED;
      }else{
         $finalValidated = PluginActivityCommonValidation::WAITING;
      }




      if ($holiday->fields['status'] != $finalValidated ){
         $holiday->fields['status'] = $finalValidated;
         $holiday->update($holiday->fields);
      }*/

      $donotif  = $CFG_GLPI["notifications_mailing"];
      $mailsend = false;
      if (isset($this->input['_disablenotif'])) {
         $donotif = false;
      }

      // If holiday validated, send mail to the applicant
      if ($holiday->fields['global_validation'] == PluginActivityCommonValidation::ACCEPTED
          || $holiday->fields['global_validation'] == PluginActivityCommonValidation::REFUSED) {
         if (count($this->updates) && in_array('status', $this->updates) && $donotif) {
            if ($CFG_GLPI["notifications_mailing"]) {
               $options = ['plugin_activity_holidayvaldiations_id' => $this->fields["id"]];

               $mailsend = NotificationEvent::raiseEvent('answervalidation', $holiday, $options);
            }
         }

         if ($mailsend) {
            $user = new User();
            $user->getFromDB($holiday->fields["users_id"]);
            $email = $user->getDefaultEmail();
            if (!empty($email)) {
               //TRANS: %s is the user name
               Session::addMessageAfterRedirect(sprintf(__('Mail sent to %s', 'activity'), $user->getDefaultEmail()));
            } else {
               Session::addMessageAfterRedirect(sprintf(__('The selected user (%s) has no valid email address. The request has been created, without email confirmation.'),
                                                        $user->getName()),
                                                false, ERROR);
            }
         }
      }
   }


   static function computeValidationStatus($item) {

      $validation_status = PluginActivityCommonValidation::WAITING;

      $accepted = 0;
      $rejected = 0;

      // Percent of validation
      $validation_percent = $item->fields['validation_percent'];

      $statuses    = [PluginActivityCommonValidation::ACCEPTED => 0,
                      PluginActivityCommonValidation::WAITING  => 0,
                      PluginActivityCommonValidation::REFUSED  => 0];
      $restrict    = ["plugin_activity_holidays_id" => $item->getID()];
      $dbu         = new DbUtils();
      $validations = $dbu->getAllDataFromTable(static::getTable(), $restrict);

      if ($total = count($validations)) {
         foreach ($validations as $validation) {
            $statuses[$validation['status']]++;
         }
      }

      if ($validation_percent > 0) {
         if (($statuses[PluginActivityCommonValidation::ACCEPTED] * 100 / $total) >= $validation_percent) {
            $validation_status = PluginActivityCommonValidation::ACCEPTED;
         } else if (($statuses[PluginActivityCommonValidation::REFUSED] * 100 / $total) >= $validation_percent) {
            $validation_status = PluginActivityCommonValidation::REFUSED;
         }
      } else {
         if ($statuses[PluginActivityCommonValidation::ACCEPTED]) {
            $validation_status = PluginActivityCommonValidation::ACCEPTED;
         } else if ($statuses[PluginActivityCommonValidation::REFUSED]) {
            $validation_status = PluginActivityCommonValidation::REFUSED;
         }
      }

      return $validation_status;
   }

   /**
    * Get the validation statistics
    *
    * @param $tID holiday id
    *
    * @return statistics array
    **/
   static function getValidationStats($tID) {

      $tab = PluginActivityCommonValidation::getAllStatusArray();
      $dbu = new DbUtils();
      $nb  = $dbu->countElementsInTable(static::getTable(), [static::$items_id => $tID]);

      $stats = [];
      foreach ($tab as $status => $name) {
         $restrict    = [static::$items_id => $tID, "status" => $status];
         $dbu         = new DbUtils();
         $validations = $dbu->countElementsInTable(static::getTable(), $restrict);
         if ($validations > 0) {
            if (!isset($stats[$status])) {
               $stats[$status] = 0;
            }
            $stats[$status] = $validations;
         }
      }

      $list = "";
      foreach ($stats as $stat => $val) {
         $list .= $tab[$stat];
         $list .= sprintf(__('%1$s (%2$d%%) '), " ", HTml::formatNumber($val * 100 / $nb));
      }

      return $list;
   }

   function showSummary($item) {

      $canedit = Session::haveRight("plugin_activity_can_validate", 1);

      $dbu     = new DbUtils();
      $number  = false;
      $hID     = $item->fields['id'];
      $holiday = new PluginActivityHoliday();
      $holiday->getFromDB($hID);

      echo Html::scriptBlock("$(document).ready(function(){
                         if ($('.panelopt').size() == 0) {
                             $('#hideopt').hide();
                         }
                        $('#hideopt').click(function(){
                           $('.panelopt').toggle();
                        });
                     });");


      echo "<div id='hideopt' class='options'>" . __('See validation options', 'activity');
      echo "&nbsp;<i style='color:#004F91;' class=\"fa fa-angle-double-down\"></i>";
      echo "</div>";
      echo "<div class='panelopt' style='display: none;'>";
      if ($canedit) {
         echo "<form method='post' name=form action='" .
              Toolbox::getItemTypeFormURL(static::$itemtype) . "'>";
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";
      echo "<th colspan='3'>" . self::getTypeName(Session::getPluralNumber()) . "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Global approval status') . "</td>";
      echo "<td colspan='2'>";
      PluginActivityCommonValidation::dropdownStatus("global_validation",
                                                     ['value' => $item->fields["global_validation"]]);
      echo "</td></tr>";

      echo "<tr>";
      echo "<th colspan='2'>" . _x('item', 'State') . "</th>";
      echo "<th colspan='2'>";
      echo self::getValidationStats($hID);
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Minimum validation required') . "</td>";
      if ($canedit) {
         echo "<td>";
         echo $item->getValueToSelect('validation_percent', 'validation_percent',
                                      $item->fields["validation_percent"]);
         echo "</td>";
         echo "<td><input type='submit' name='update' class='submit' value='" .
              _sx('button', 'Save') . "'>";
         if (!empty($hID)) {
            echo "<input type='hidden' name='id' value='$hID'>";
         }
         echo "</td>";
      } else {
         echo "<td colspan='2'>";
         echo Dropdown::getValueWithUnit($item->fields["validation_percent"], "%");
         echo "</td>";
      }
      echo "</tr>";
      echo "</table>";
      if ($canedit) {
         Html::closeForm();
      }
      echo "</div>";

      if (isset($holiday->fields['id'])) {
         $hValidation = new PluginActivityHolidayValidation();
         $dbu         = new DbUtils();
         $datas       = $dbu->getAllDataFromTable($hValidation->getTable(), ["plugin_activity_holidays_id" => $holiday->fields['id']]);

         $number = sizeof($datas);
      }

      if ($number) {

         foreach ($datas as $data) {
            if ($data["users_id_validate"] == Session::getLoginUserID()
                && $data['status'] == PluginActivityCommonValidation::WAITING) {
               $this->showForm($data["id"], ['parent' => $holiday->fields['id']]);
            }
         }

         $colonnes    = [_x('item', 'State'),
                         sprintf(__('%1$s: %2$s'), __('Request'), __('Date')),
                         __('Approval date'),
                         __('Approver'),
                         sprintf(__('%1$s: %2$s'), __('Approval'), __('Comments'))];
         $nb_colonnes = count($colonnes);

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr><th colspan='" . $nb_colonnes . "'>";
         echo _n('Validation for this holiday', 'Validations for this holiday', $number > 1 ? 2 : 1, 'activity');
         echo "</th></tr>";

         echo "<tr>";
         foreach ($colonnes as $colonne) {
            echo "<th>" . $colonne . "</th>";
         }
         echo "</tr>";

         Session::initNavigateListItems('PluginActivityHolidayValidation',
            //TRANS : %1$s is the itemtype name, %2$s is the name of the item (used for headings of a list)
                                        sprintf(__('%1$s = %2$s'), $holiday->getTypeName(1),
                                                $holiday->fields["name"]));

         foreach ($datas as $data) {

            Session::addToNavigateListItems('PluginActivityHolidayValidation', $data["id"]);
            $status = PluginActivityCommonValidation::getStatus($data['status']);
            echo "<tr class='tab_bg_1'>";
            echo "<td class='center'>";
            if ($data['status'] == PluginActivityCommonValidation::ACCEPTED) {
               echo "<div style='color:forestgreen'><i class='far fa-check-circle fa-4x'></i><br>" . $status . "</div>";
            } else if ($data['status'] == PluginActivityCommonValidation::REFUSED) {
               echo "<div style='color:darkred'><i class='far fa-times-circle fa-4x'></i><br>" . $status . "</div>";
            } else {
               echo "<div style='color:orange'><i class='far fa-question-circle fa-4x'></i><br>" . $status . "</div>";
            }
            echo "</td>";

            echo "<td>" . Html::convDateTime($data["submission_date"]) . "</td>";
            echo "<td>" . Html::convDateTime($data["validation_date"]) . "</td>";
            echo "<td>" . $dbu->getUserName($data["users_id_validate"]) . "</td>";
            echo "<td>" . $data["comment_validation"] . "</td>";
            echo "</tr>";
         }
         echo "</table>";

      } else {
         echo "<div class='center b'>" . __('No holiday validation request found', 'activity') . "</div>";
      }
   }


   /**
    * Print the validation form
    *
    * @param $ID        integer  ID of the item
    * @param $options   array    options used
    *
    *
    * @return bool
    */
   function showForm($ID, $options = []) {

      $dbu              = new DbUtils();
      $validation_admin = true;

      $options['colspan']     = 1;
      $options['candel']      = false;
      $options['formtitle']   = '';
      $options['formoptions'] = "id='formvalidation'";

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      $holiday = new PluginActivityHoliday();
      $holiday->getFromDB($this->fields['plugin_activity_holidays_id']);

      $validator = ($this->fields["users_id_validate"] == Session::getLoginUserID());

      echo "<table class='tab_cadre_fixe' id='mainformtable'>";

      if ($validator && $this->fields["status"] == PluginActivityCommonValidation::WAITING) {
         echo "<tr class='tab_bg_2'>";
         echo "<th colspan='4'>" . __('Do you approve this holiday ?', 'activity') . "</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __('Status of the approval request') . "</td>";
         echo "<td class='center'>";
         echo "<div style='color:forestgreen'><i id='accept_holiday' class='question far fa-check-circle fa-4x'></i><br>" . __('Accept holiday', 'activity') . "</div>";
         echo "<input type='hidden' name='accept_holiday' value='0'>";
         echo "</td>";
         echo "<td class='center'>";
         echo "<div style='color:darkred'><i id='refuse_holiday' class='question far fa-times-circle fa-4x'></i><br>" . __('Refuse holiday', 'activity') . "</div>";
         echo "<input type='hidden' name='refuse_holiday' value='0'>";
         echo "<input type='hidden' name='validation_date' value='" . date('Y-m-d H:i:s') . "' />";
         echo "<input type='hidden' name='id' value='" . $this->fields['id'] . "'>";
         echo "</td>";
         echo "</tr>";

         echo Html::scriptBlock('$( "#accept_holiday" ).click(function() {
                                $( "#formvalidation" ).append("<input type=\'hidden\' name=\'accept_holiday\' value=\'1\' />");
                                $( "#formvalidation" ).append("<input type=\'hidden\' name=\'update\' value=\'1\' />");
                                $( "#formvalidation" ).submit();
                              });
                              $( "#refuse_holiday" ).click(function() {
                                $( "#formvalidation" ).append("<input type=\'hidden\' name=\'refuse_holiday\' value=\'1\' />");
                                $( "#formvalidation" ).append("<input type=\'hidden\' name=\'update\' value=\'1\' />");
                                $( "#formvalidation" ).submit();
                              });');
      }

      if ($ID > 0) {
         if ($validator) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Approval comments') . "<br>(" . __('Optional when approved') . ")</td>";
            echo "<td colspan='2'><textarea cols='100' rows='3' name='comment_validation'>" .
                 $this->fields["comment_validation"] . "</textarea>";
            echo "</td></tr>";

         } else {
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Status of the approval request') . "</td>";
            echo "<td colspan='2'>" . PluginActivityCommonValidation::getStatus($this->fields["status"]) . "</td></tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Comments') . "</td>";
            echo "<td colspan='2'>" . $this->fields["comment_validation"] . "</td></tr>";
         }
      }

      if ($validation_admin) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __('Approval requester') . "</td>";
         echo "<td colspan='2'>";
         echo $dbu->getUserName($holiday->fields["users_id"]);
         echo "</td></tr>";

         echo "<tr class='tab_bg_1'><td>" . __('Approver') . "</td>";
         echo "<td colspan='2'>";
         echo $dbu->getUserName($this->fields["users_id_validate"]);
         echo "</td></tr>";

      } else {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __('Approval requester') . "</td>";
         echo "<td colspan='2'>" . $dbu->getUserName($this->fields["users_id"]) . "</td></tr>";

         echo "<tr class='tab_bg_1'><td>" . __('Approver') . "</td>";
         echo "<td colspan='2'>" . $dbu->getUserName($this->fields["users_id_validate"]) . "</td></tr>";

      }

      $options['formfooter'] = '';
      $options['colspan']    = 1;
      $options['canedit']    = false;
      $this->showFormButtons($options);
      Html::closeForm();
      return true;
   }


   /**
    * @since version 0.84
    *
    * @see CommonDBConnexity::getHistoryChangeWhenUpdateField
    **/
   function getHistoryChangeWhenUpdateField($field) {

      $dbu = new DbUtils();
      if ($field == 'status') {
         $username = $dbu->getUserName($this->fields["users_id_validate"]);
         $result   = ['0', '', ''];
         if ($this->fields["status"] == 'accepted') {
            //TRANS: %s is the username
            $result[2] = sprintf(__('Approval granted by %s'), $username);
         } else {
            //TRANS: %s is the username
            $result[2] = sprintf(__('Update the approval request to %s'), $username);
         }
         return $result;
      }
      return false;
   }


   /**
    * @since version 0.84
    *
    * @see CommonDBChild::getHistoryNameForItem
    **/
   function getHistoryNameForItem(CommonDBTM $item, $case) {

      $dbu      = new DbUtils();
      $username = $dbu->getUserName($this->fields["users_id_validate"]);
      switch ($case) {
         case 'add':
            return sprintf(__('Approval request send to %s'), $username);

         case 'delete':
            return sprintf(__('Cancel the approval request to %s'), $username);
      }
      return '';
   }

   static function getSpecificValueToDisplay($field, $values, array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'users_id_validate':
            $user = new User();
            $user->getFromDB($values[$field]);
            return $user->getLink();
      }

      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      $dbu = new DbUtils();
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;
      switch ($field) {
         case 'users_id_validate' :
            $holidayValidation = new PluginActivityHolidayValidation();
            $validators        = $holidayValidation->find();
            $elements          = [Dropdown::EMPTY_VALUE];
            foreach ($validators as $validator) {
               $elements[$validator['users_id_validate']] = $dbu->getUserName($validator['users_id_validate']);
            }

            return Dropdown::showFromArray($name, $elements, ['display' => false, 'value' => $values[$field]]);
      }

      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }
}
