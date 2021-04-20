<?php
/**
 -------------------------------------------------------------------------
 oauthimap plugin for GLPI
 Copyright (C) 2018-2020 by the oauthimap Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of oauthimap.

 oauthimap is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 oauthimap is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with oauthimap. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Oauthimap\Imap;

use Glpi\Mail\Protocol\ProtocolInterface;
use Laminas\Mail\Protocol\Imap;
use PluginOauthimapAuthorization;

class ImapOauthProtocol extends Imap implements ProtocolInterface {

   /**
    * ID of PluginOauthimapApplication to use.
    * @var int
    */
   private $application_id;

    /**
     * @param  int   $application_id   ID of PluginOauthimapApplication to use
     */
   public function __construct($application_id) {
      $this->application_id = $application_id;
      parent::__construct();
   }

   public function login($user, $password) {
      $token = PluginOauthimapAuthorization::getAccessTokenForApplicationAndEmail($this->application_id, $user);

      if ($token === null) {
         trigger_error('Unable to get access token', E_USER_WARNING);
         return;
      }

      $this->sendRequest(
         'AUTHENTICATE',
         [
            'XOAUTH2',
            base64_encode("user={$user}\001auth=Bearer {$token}\001\001")
         ]
      );

      while (true) {
         $response = '';
         $isPlus = $this->readLine($response, '+', true);
         if ($isPlus) {
            // Send empty client response.
            $this->sendRequest('');
         } else {
            if (preg_match('/^NO /i', $response) ||
                preg_match('/^BAD /i', $response)) {
               return false;
            }
            if (preg_match("/^OK /i", $response)) {
               return true;
            }
         }
      }

      return false;
   }
}
