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

namespace GlpiPlugin\Oauthimap\Provider;

use GlpiPlugin\Oauthimap\Oauth\OwnerDetails;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Token\AccessToken;

class Azure extends \TheNetworg\OAuth2\Client\Provider\Azure implements ProviderInterface {

   public static function getName(): string {
      return 'Azure';
   }

   public static function getIcon(): string {
      return 'fa-windows';
   }

   protected function createAccessToken(array $response, AbstractGrant $grant) {
      return new \GlpiPlugin\Oauthimap\Provider\Azure\AccessToken($response, $this);
   }

   public function getOwnerDetails(AccessToken $token): ?OwnerDetails {
      /* @var \TheNetworg\OAuth2\Client\Provider\AzureResourceOwner $owner */
      $owner = $this->getResourceOwner($token);

      $owner_details = new OwnerDetails();
      if (($email = $owner->claim('email')) !== null) {
         $owner_details->email = $email;
      } else if (($upn = $owner->claim('upn')) !== null) {
         $owner_details->email = $upn;
      }
      $owner_details->firstname = $owner->getFirstName();
      $owner_details->lastname = $owner->getLastName();

      return $owner_details;
   }
}
