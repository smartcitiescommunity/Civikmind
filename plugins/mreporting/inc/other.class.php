<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Mreporting plugin for GLPI
 Copyright (C) 2003-2011 by the mreporting Development Team.

 https://forge.indepnet.net/projects/mreporting
 -------------------------------------------------------------------------

 LICENSE

 This file is part of mreporting.

 mreporting is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 mreporting is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with mreporting. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

class PluginMreportingOther Extends PluginMreportingBaseclass {

   function reportHbarLogs($configs = []) {
      global $DB;

      //Init delay value
      $this->sql_date = PluginMreportingCommon::getSQLDate("`glpi_tickets`.`date`",
                                                   $configs['delay'], $configs['randname']);

      $prefix = "SELECT count(*) as cpt FROM `glpi_logs` WHERE ";
      //Add/remove a software on a computer
      $query_software = "$prefix `linked_action` IN (4,5)";

      //Add/remove a software on a computer
      $query_computer_software = "$prefix `linked_action` IN (4,5)";

      $query_software_version  = "$prefix `itemtype`='Software'
                                  AND `itemtype_link`='SoftwareVersion'
                                  AND `linked_action` IN (17, 18, 19)";
      $query_add_infocom       = "$prefix `itemtype`='Software'
                                  AND `itemtype_link`='Infocom'
                                  AND `linked_action` IN (17)";
      $query_user_profiles     = "$prefix `itemtype`='User'
                                  AND `itemtype_link`='Profile_User'
                                  AND `linked_action` IN (17, 18, 19)";
      $query_user_groups     = "$prefix `itemtype`='User'
                                  AND `itemtype_link`='Group_User'
                                  AND `linked_action` IN (17, 18, 19)";

      $query_user_deleted    = "$prefix `itemtype`='User' AND `linked_action` IN (12)";

      $query_ocs             = "$prefix `linked_action` IN (8, 9, 10, 11)";
      $query_device          = "$prefix `linked_action` IN (1, 2, 3, 6, 7)";
      $query_relation        = "$prefix `linked_action` IN (15, 16)";
      $query_item            = "$prefix `linked_action` IN (13, 14, 17, 18, 19, 20)";
      $query_other           = "$prefix `id_search_option` IN (16, 19)";

      $datas = [];

      $result = $DB->query($query_computer_software);
      $datas['datas'][__('Add/remove software on a computer', 'mreporting')] = $DB->result($result, 0, 'cpt');

      $result = $DB->query($query_software_version);
      $datas['datas'][__('Add/remove version on a software', 'mreporting')] = $DB->result($result, 0, 'cpt');

      $result = $DB->query($query_add_infocom);
      $datas['datas'][__('Add infocom', 'mreporting')] = $DB->result($result, 0, 'cpt');

      $result = $DB->query($query_user_profiles);
      $datas['datas'][__('Add/remove profile on a user', 'mreporting')] = $DB->result($result, 0, 'cpt');

      $result = $DB->query($query_user_groups);
      $datas['datas'][__('Add/remove group on a user', 'mreporting')] = $DB->result($result, 0, 'cpt');

      $result = $DB->query($query_user_deleted);
      $datas['datas'][__('User deleted from LDAP', 'mreporting')] = $DB->result($result, 0, 'cpt');

      $plugin = new Plugin();
      if ($plugin->isActivated("webservices")) {
         $query_webservice = "$prefix `itemtype`='PluginWebservicesClient'";

         // Display this information is not usefull if webservices is not activated
         $result = $DB->query($query_webservice);
         $datas['datas'][__('Webservice logs', 'mreporting')] = $DB->result($result, 0, 'cpt');
      }

      $result = $DB->query($query_ocs);
      $datas['datas'][__('OCS Infos', 'mreporting')] = $DB->result($result, 0, 'cpt');

      $result = $DB->query($query_device);
      $datas['datas'][__('Add/update/remove device', 'mreporting')] = $DB->result($result, 0, 'cpt');

      $result = $DB->query($query_relation);
      $datas['datas'][__('Add/remove relation', 'mreporting')] = $DB->result($result, 0, 'cpt');

      $result = $DB->query($query_item);
      $datas['datas'][__('Add/remove item', 'mreporting')] = $DB->result($result, 0, 'cpt');

      $result = $DB->query($query_other);
      $datas['datas'][__('Comments & date_mod changes', 'mreporting')] = $DB->result($result, 0, 'cpt');

      $plugin = new Plugin();
      if ($plugin->isActivated("genericobject")) {
         $query_genericobject = "$prefix `itemtype` LIKE '%PluginGenericobject%'";

         // Display this information is not usefull if genericobject is not activated
         $result = $DB->query($query_genericobject);
         $datas['datas'][__('Genericobject plugin logs', 'mreporting')] = $DB->result($result, 0, 'cpt');
      }

      return $datas;
   }

   /**
   * Preconfig datas with your values when init config is done
   *
   * @param type $funct_name
   * @param type $classname
   * @param PluginMreportingConfig $config
   * @return $config
   */
   function preconfig($funct_name, $classname, PluginMreportingConfig $config) {

      if ($funct_name != -1 && $classname) {

         $ex_func = preg_split('/(?<=\\w)(?=[A-Z])/', $funct_name);
         if ($ex_func[0] != 'report') {
            return false;
         }
         $gtype = strtolower($ex_func[1]);

         switch ($gtype) {
            case 'pie':
               $config->fields["name"]=$funct_name;
               $config->fields["classname"]=$classname;
               $config->fields["is_active"]="1";
               $config->fields["show_label"]="hover";
               $config->fields["spline"]="0";
               $config->fields["show_area"]="0";
               $config->fields["show_graph"]="1";
               $config->fields["default_delay"]="30";
               $config->fields["show_label"]="hover";
               break;
            default :
               $config->preconfig($funct_name, $classname);
               break;

         }

      }
      return $config->fields;
   }

}
