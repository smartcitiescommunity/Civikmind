<?php
/*
 -------------------------------------------------------------------------
 xivo plugin for GLPI
 Copyright (C) 2017 by the xivo Development Team.

 https://github.com/pluginsGLPI/xivo
 -------------------------------------------------------------------------

 LICENSE

 This file is part of xivo.

 xivo is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 xivo is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with xivo. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginXivoXuc {
   function getLoginForm() {
      // prepare a form for js submitting
      $out = "<form id='xuc_login_form'>
         <h2>".__("Connect to XIVO", 'xivo')."</h2>

         <label for='xuc_username'>".__("XIVO username", 'xivo')."</label>
         <input type='text' id='xuc_username'>

         <label for='xuc_password'>".__("XIVO password", 'xivo')."</label>
         <input type='password' id='xuc_password'>

         <label for='xuc_phoneNumber'>".__("XIVO phone number", 'xivo')."</label>
         <input type='text' id='xuc_phoneNumber' size='6'>

         <input type='submit' class='submit' id='xuc_sign_in' value='".__("Connect")."'>

         <div id='xuc_message'></div>
      </form>";

      return $out;
   }

   function getLoggedForm() {
      $user = new User;
      $user->getFromDB($_SESSION['glpiID']);
      $picture = "";
      if (isset($user->fields['picture'])) {
         $picture = $user->fields['picture'];
      }

      $current_config = PluginXivoConfig::getConfig();

      $out = "<form id='xuc_logged_form'>
         <h2>
            <i id='xuc_sign_out' class='fa fa-power-off pointer'></i>".
            __("XIVO connected", 'xivo')."&nbsp;
         </h2>

         <div id='xuc_user_info'>
            <div id='xuc_user_picture'>
               <img src='".User::getThumbnailURLForPicture($picture)."'>
            </div>
            <div class='floating_text'>
               <div id='xuc_fullname'></div>
               <div id='xuc_statuses'>";
      if ($current_config['enable_callcenter'] && PLUGIN_XIVO_ENABLE_CALLCENTER) {
         $out .= "<div>
                     <label for='xuc_user_status'>".__("User", 'xivo')."</label>
                     <select id='xuc_user_status'></select>
                  </div>";
      }
      $out .= "   <div>
                     <label for='xuc_phone_status'>".__("Phone", 'xivo')."</label>
                     <input type='text' id='xuc_phone_status' readonly>
                  </div>
               </div>
            </div>
         </div>
      </form>

      <div class='separ'></div>

      <div id='xuc_call_informations'>
         <h2 id='xuc_call_titles'>
            <div id='xuc_ringing_title'>".__("Incoming call", 'xivo')."</div>
            <div id='xuc_oncall_title'>".__("On call", 'xivo')."</div>
            <div id='xuc_dialing_title'>".__("Dialing", 'xivo')."</div>
         </h2>
         <div class='xuc_content'>
            <div><b>".__('Caller num:')."</b>&nbsp;<span id='xuc_caller_num'></span></div>
            <div id='xuc_caller_infos'></div>

            <div id='auto_actions'>
               <i class='fa fa-phone-square'
                  id='xuc_answer'
                  title='".__("Answer", 'xivo')."'></i>
               <i class='fa fa-phone-square fa-rotate-90'
                  id='xuc_hangup'
                  title='".__("Hangup", 'xivo')."'></i>
               <i class='fa fa-pause-circle'
                  id='xuc_hold'
                  title='".__("Hold", 'xivo')."'></i>
            </div>
         </div>
      </div>
      <div id='xuc_call_actions'>
         <h2>".__("Phone actions", 'xivo')."</h2>
         <div class='xuc_content'>
            <div class='manual_actions'>
               <input type='text' class='input-inline' id='dial_phone_num' placeholder='".__("Dial number", 'xivo')."' />
               <input type='text' class='input-inline' id='transfer_phone_num' placeholder='".__("Transfer to number", 'xivo')."' />
               <i class='fa fa-phone-square'
                  id='xuc_dial'
                  title='".__("Dial", 'xivo')."'></i>
               <i class='fa fa-arrow-circle-right'
                  id='xuc_transfer'
                  title='".__("Transfer", 'xivo')."'></i>
            </div>
         </div>
      </div>";

      return $out;
   }

   function getCallLink($users_id = 0) {
      $data = [
         'phone'          => null,
         'phone2'         => null,
         'mobile'         => null,
         'title'          => '',
      ];
      $user = new User;
      if ($user->getFromDB($users_id)) {
         if (!empty($user->fields['phone'])) {
            $data['phone']  = $user->fields['phone'];
            $data['phone2'] = $user->fields['phone2'];
            $data['mobile'] = $user->fields['mobile'];
            $data['title']  = sprintf(__("Call %s: %s"), $user->getName(), $user->fields['phone']);
         }
      }

      return $data;
   }

   function getUserInfosByPhone($params = []) {
      global $DB;

      $data = [
         'users'    => [],
         'tickets'  => [],
         'redirect' => false,
         'message'  => null
      ];

      $caller_num = isset($params['caller_num'])
         ? preg_replace('/\D+/', '', $params['caller_num']) // only digits
         : 0;

      if (empty($caller_num)) {
         return $data;
      }

      $r_not_digit = "[^0-9]*";
      $regex_num = "^".$r_not_digit.implode($r_not_digit, str_split($caller_num)).$r_not_digit."$";

      // try to find user by its phone or mobile numbers
      $iterator_users = $DB->request([
         'SELECT' => ['id'],
         'FROM'  => 'glpi_users',
         'WHERE' => [
            'OR' => [
               'phone'  => ['REGEXP', $regex_num],
               'mobile' => ['REGEXP', $regex_num],
            ]
         ]
      ]);
      foreach ($iterator_users as $data_user) {
         $userdata = getUserName($data_user["id"], 2);
         $name     = "<b>".__("User found in GLPI:", 'xivo')."</b>".
                     "&nbsp;".$userdata['name'];
         $name     = sprintf(__('%1$s %2$s'), $name,
                             Html::showToolTip($userdata["comment"],
                                               ['link'    => $userdata["link"],
                                                'display' => false]));

         $data_user['link'] = $name;
         $data['users'][]   = $data_user;
      }

      // one user search for tickets
      if (count($data['users']) > 1) {
         // mulitple user, no redirect and return a message
         $data['message'] = __("Multiple users found with this phone number", 'xivo');
      } else if (count($data['users']) == 1) {
         $current_user     = current($data['users']);
         $users_id         = $current_user['id'];
         $iterator_tickets = $DB->request([
            'SELECT'     => ['glpi_tickets.id', 'glpi_tickets.name', 'glpi_tickets.content'],
            'FROM'       => 'glpi_tickets',
            'INNER JOIN' => [
               'glpi_tickets_users' => [
                  'FKEY' => [
                     'glpi_tickets_users' => 'tickets_id',
                     'glpi_tickets'       => 'id',
                  ]
               ]
            ],
            'WHERE'     => [
               'glpi_tickets_users.type' => CommonITILActor::REQUESTER,
               'glpi_tickets.status'     => ["<", CommonITILObject::SOLVED],
            ],
         ]);
         $data['tickets'] = iterator_to_array($iterator_tickets);
         $nb_tickets = count($iterator_tickets);

         $ticket = new Ticket;
         $user   = new User;
         $user->getFromDB($users_id);

         if ($nb_tickets == 1) {
            // if we have one user with one ticket, redirect to ticket
            $ticket->getFromDB(current($data['tickets'])['id']);
            $data['redirect'] = $ticket->getLinkURL();
         } else if ($nb_tickets > 1) {
            // if we have one user with multiple tickets, redirect to user (on Ticket tab)
            $data['redirect'] = $user->getLinkURL().'&forcetab=Ticket$1';
         } else {
            // if the current user has no tickets, redirect to ticket creation form
            $data['redirect'] = $ticket->getFormUrl().'?_users_id_requester='.$user->getID();
         }
      }

      return $data;
   }
}