<?php
/**
 * ------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of SCCM plugin.
 *
 * SCCM plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * SCCM plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * ------------------------------------------------------------------------
 * @author    François Legastelois <flegastelois@teclib.com>
 * @copyright Copyright (C) 2014-2018 by Teclib' and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/sccm
 * @link      https://pluginsglpi.github.io/sccm/
 * ------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginSccmSccm {

   var $devices;

   static function getTypeName($nb = 0) {
      return __('SCCM', 'sccm');
   }

   function showHome() {
      echo __('Please, read the documentation before using that.', 'footprints');
   }

   function getDevices($where = 0, $limit = 99999999) {

      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $res = $PluginSccmSccmdb->connect();
      if (!$res) {
         die;
      }

      $query = self::getcomputerQuery();

      if ($where!=0) {
         $query.= " WHERE csd.MachineID = '" . $where . "'";
      }

      $result = $PluginSccmSccmdb->exec_query($query);

      $i = 0;
      $tab = [];

      while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) AND $i < $limit) {

         $tab['MD-SystemName'] = strtoupper($tab['MD-SystemName']);

         $this->devices[] = $tab;

         $i++;
      }

      $PluginSccmSccmdb->disconnect();
   }

   function getDatas($type, $deviceid, $limit = 99999999) {

      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $res = $PluginSccmSccmdb->connect();
      if (!$res) {
         die;
      }

      $datas = [];

      switch ($type) {
         case 'processors' :
            $fields = ['Manufacturer00','Name00','NormSpeed00','AddressWidth00','CPUKey00','NumberOfCores00', 'NumberOfLogicalProcessors00'];
            $table = 'Processor_DATA';
         break;
      }

      $query = "SELECT ".implode(',', $fields)."\n";
      $query.= " FROM ".$table."\n";
      $query.= " WHERE MachineID = '".$deviceid."'"."\n";

      $result = $PluginSccmSccmdb->exec_query($query);

      $data = [];

      $i=0;
      $tab = [];
      while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) AND $i < $limit) {
         $tmp = [];

         foreach ($tab as $key => $value) {
            $tmp[$key] = $this->cleanValue($value);
         }
         $data[] = $tmp;

         $i++;
      }

      $PluginSccmSccmdb->disconnect();

      return $data;
   }

   function cleanValue($value) {
      $value = Html::clean($value);
      $value = Toolbox::clean_cross_side_scripting_deep($value);
      $value = Toolbox::addslashes_deep($value);
      return $value;
   }

   function getNetwork($deviceid, $limit = 99999999) {

      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $res = $PluginSccmSccmdb->connect();
      if (!$res) {
         die;
      }

      $query = "SELECT NeDa.IPAddress00 as \"ND-IpAddress\",
      NeDa.MACAddress00 as \"ND-MacAddress\",
      NeDa.IPSubnet00 as \"ND-IpSubnet\",
      NeDa.DefaultIPGateway00 as \"ND-IpGateway\",
      NeDa.DHCPServer00 as \"ND-DHCPServer\",
      NeDa.DNSDomain00 as \"ND-DomainName\",
      net.Name0 as \"ND-Name\"
      FROM Network_DATA NeDa
      INNER JOIN v_R_System VrS ON VrS.ResourceID=NeDa.MachineID
      INNER JOIN v_GS_NETWORK_ADAPTER net ON net.ResourceID=NeDa.MachineID AND NeDa.ServiceName00=net.ServiceName0
      WHERE MACAddress00 is not null
      AND NeDa.MachineID = '".$deviceid."'";

      $result = $PluginSccmSccmdb->exec_query($query);

      $data = [];

      $i=0;
      $tab = [];
      while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) AND $i < $limit) {
         $tmp = [];

         foreach ($tab as $key => $value) {
            $tmp[$key] = $this->cleanValue($value);
         }
         $data[] = $tmp;

         $i++;
      }

      $PluginSccmSccmdb->disconnect();

      return $data;
   }

   function getSoftware($deviceid, $limit = 99999999) {

      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $res = $PluginSccmSccmdb->connect();
      if (!$res) {
         die;
      }

      $query = "SELECT ArPd_64.DisplayName0 as \"ArPd-DisplayName\",
      ArPd_64.InstallDate0 as \"ArPd-InstallDate\",
      ArPd_64.Version0 as \"ArPd-Version\",
      ArPd_64.Publisher0 as \"ArPd-Publisher\"
      FROM v_GS_ADD_REMOVE_PROGRAMS_64 ArPd_64
      INNER JOIN v_R_System VrS on VrS.ResourceID=ArPd_64.ResourceID
      WHERE ArPd_64.ResourceID = $deviceid
      UNION
      SELECT ArPd.DisplayName0 as \"ArPd-DisplayName\",
      ArPd.InstallDate0 as \"ArPd-InstallDate\",
      ArPd.Version0 as \"ArPd-Version\",
      ArPd.Publisher0 as \"ArPd-Publisher\"
      FROM v_GS_ADD_REMOVE_PROGRAMS ArPd
      INNER JOIN v_R_System VrS on VrS.ResourceID=ArPd.ResourceID
      WHERE ArPd.ResourceID = $deviceid";

      $result = $PluginSccmSccmdb->exec_query($query);

      $data = [];

      $i=0;
      $tab = [];
      while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) AND $i < $limit) {
         $tmp = [];

         foreach ($tab as $key => $value) {
            $tmp[$key] = $this->cleanValue($value);
         }
         $data[] = $tmp;

         $i++;
      }

      $PluginSccmSccmdb->disconnect();

      return $data;
   }

   function getMemories($deviceid, $limit = 99999999) {

      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $res = $PluginSccmSccmdb->connect();
      if (!$res) {
         die;
      }

      $query = "SELECT
            Capacity0 as \"Mem-Capacity\",
            Caption0 as \"Mem-Caption\",
            Description0 as \"Mem-Description\",
            FormFactor0 as \"Mem-FormFactor\",
            Manufacturer0 as \"Mem-Manufacturer\",
            Removable0 as \"Mem-Removable\",
            '' as \"Mem-Purpose\",
            Speed0 as \"Mem-Speed\",
            BankLabel0 as \"Mem-Type\",
            GroupID as \"Mem-NumSlots\",
            '' as \"Mem-SerialNumber\"
         FROM v_GS_PHYSICAL_MEMORY

         WHERE ResourceID = '".$deviceid."'

         ORDER BY \"Mem-NumSlots\"";

      $result = $PluginSccmSccmdb->exec_query($query);

      $data = [];

      $i=0;
      $tab = [];
      while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) AND $i < $limit) {
         $tmp = [];

         foreach ($tab as $key => $value) {
            $tmp[$key] = $this->cleanValue($value);
         }
         $data[] = $tmp;

         $i++;
      }

      $PluginSccmSccmdb->disconnect();

      return $data;
   }

   function getVideos($deviceid, $limit = 99999999) {

        $PluginSccmSccmdb = new PluginSccmSccmdb();
        $res = $PluginSccmSccmdb->connect();
      if (!$res) {
         die;
      }

        $query = "
      SELECT
         VideoProcessor0 as \"Vid-Chipset\",
         AdapterRAM0/1024 as \"Vid-Memory\",
         Name0 as \"Vid-Name\",
         CONCAT(CurrentHorizontalResolution0, 'x', CurrentVerticalResolution0) as \"Vid-Resolution\",
         GroupID as \"Vid-PciSlot\"
      FROM v_GS_VIDEO_CONTROLLER
      WHERE VideoProcessor0 is not null
      AND ResourceID = '".$deviceid."'
      ORDER BY GroupID";

      $result = $PluginSccmSccmdb->exec_query($query);

      $data = [];

      $i=0;
      $tab = [];
      while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) AND $i < $limit) {
         $tmp = [];

         foreach ($tab as $key => $value) {
            $tmp[$key] = $this->cleanValue($value);
         }
         $data[] = $tmp;

         $i++;
      }

        $PluginSccmSccmdb->disconnect();

        return $data;
   }

   function getSounds($deviceid, $limit = 99999999) {

      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $res = $PluginSccmSccmdb->connect();
      if (!$res) {
         die;
      }

      $query = "
      SELECT distinct
         Description0 as \"Snd-Description\",
         Manufacturer0 as \"Snd-Manufacturer\",
         Name0 as \"Snd-Name\"
      FROM v_GS_SOUND_DEVICE
      WHERE ResourceID = '".$deviceid."'";

      $result = $PluginSccmSccmdb->exec_query($query);

      $data = [];

      $i=0;
      $tab = [];
      while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) AND $i < $limit) {
         $tmp = [];

         foreach ($tab as $key => $value) {
            $tmp[$key] = $this->cleanValue($value);
         }
         $data[] = $tmp;

         $i++;
      }

      $PluginSccmSccmdb->disconnect();

      return $data;
   }

   function getStorages($deviceid, $limit = 99999999) {

      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $res = $PluginSccmSccmdb->connect();
      if (!$res) {
         die;
      }

      $query = "
      SELECT
         md.SystemName00,
         gld.ResourceID as \"gld-ResourceID\",
         gld.Description0 as \"gld-Description\",
         gld.DeviceID0 as \"gld-Partition\",
         gld.FileSystem0 as \"gld-FileSystem\",
         gld.Size0 as \"gld-TotalSize\",
         gld.FreeSpace0 as \"gld-FreeSpace\",
         gld.VolumeName0 as \"gld-MountingPoint\",
         gdi.Caption0 as \"gdi-Caption\"
      FROM v_GS_LOGICAL_DISK as gld
      INNER JOIN v_gs_Disk as gdi on gdi.ResourceID = gld.ResourceID
      LEFT JOIN Motherboard_DATA as md on gld.ResourceID = md.MachineID
      WHERE gld.GroupID = gdi.GroupID
      AND gld.ResourceID = '".$deviceid."'";

      $result = $PluginSccmSccmdb->exec_query($query);

      $data = [];

      $i=0;
      $tab = [];
      while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) AND $i < $limit) {
         $tmp = [];

         foreach ($tab as $key => $value) {
            $tmp[$key] = $this->cleanValue($value);
         }
         $data[] = $tmp;

         $i++;
      }

      $PluginSccmSccmdb->disconnect();

      return $data;
   }

   function getMedias($deviceid, $limit = 99999999) {

      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $res = $PluginSccmSccmdb->connect();
      if (!$res) {
         die;
      }

      $query = "
      SELECT distinct
         Description0 as \"Med-Description\",
         Manufacturer0 as \"Med-Manufacturer\",
         Caption0 as \"Med-Model\",
         Name0 as \"Med-Name\",
         SCSITargetID0 as \"Med-SCSITargetId\",
         MediaType0 as \"Med-Type\"
      FROM v_GS_CDROM
      WHERE ResourceID = '".$deviceid."'";

      $result = $PluginSccmSccmdb->exec_query($query);

      $data = [];

      $i=0;
      $tab = [];
      while (($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) AND $i < $limit) {
         $tmp = [];

         foreach ($tab as $key => $value) {
            $tmp[$key] = $this->cleanValue($value);
         }
         $data[] = $tmp;

         $i++;
      }

      $PluginSccmSccmdb->disconnect();

      return $data;
   }

   static function install() {
      $cronCollect = new CronTask;

      if ($cronCollect->getFromDBbyName(__CLASS__, 'sccm')) {

         $cronCollect->fields["name"] = "SCCMCollect";
         $cronCollect->fields["hourmin"] = 4;
         $cronCollect->fields["hourmax"] = 5;
         $cronCollect->update($cronCollect->fields);

      } else if (!$cronCollect->getFromDBbyName(__CLASS__, 'SCCMCollect')) {

         CronTask::register(__CLASS__, 'SCCMCollect', 7 * DAY_TIMESTAMP,
            ['param' => 24, 'mode' => CronTask::MODE_EXTERNAL, 'hourmin' => 4, 'hourmax' => 5]);

      }

      CronTask::register(__CLASS__, 'SCCMPush', 7 * DAY_TIMESTAMP,
            ['param' => 24, 'mode' => CronTask::MODE_EXTERNAL, 'hourmin' => 6, 'hourmax' => 7]);
   }

   static function uninstall() {
      CronTask::unregister(__CLASS__);
   }

   static function cronSCCMCollect($task) {
      return self::executeCollect($task);
   }

   static function cronSCCMPush($task) {
      return self::executePush($task);
   }

   static function cronInfo($name) {
      if ($name == "SCCMCollect") {
         return ['description' => __("Interface - SCCMCollect", "sccm")];
      }
      if ($name == "SCCMPush") {
         return ['description' => __("Interface - SCCMPush", "sccm")];
      }

   }

   static function executeCollect($task) {
      ini_set('max_execution_time', 0);
      $retcode = -1;

      $REP_XML = GLPI_PLUGIN_DOC_DIR.'/sccm/xml/';

      $PluginSccmConfig = new PluginSccmConfig();
      $PluginSccmConfig->getFromDB(1);

      $PluginSccmSccm = new PluginSccmSccm();

      if ($PluginSccmConfig->getField('active_sync') == 1) {

         $PluginSccmSccm->getDevices();
         Toolbox::logInFile('sccm', "getDevices OK \n", true);

         Toolbox::logInFile('sccm', "Generate XML start : "
            . count($PluginSccmSccm->devices) . " files\n", true);

         foreach ($PluginSccmSccm->devices as $device_values) {

            $PluginSccmSccmxml = new PluginSccmSccmxml($device_values);

            $PluginSccmSccmxml->setAccessLog();
            $PluginSccmSccmxml->setAccountInfos();
            $PluginSccmSccmxml->setHardware();
            $PluginSccmSccmxml->setOS();
            $PluginSccmSccmxml->setBios();
            $PluginSccmSccmxml->setProcessors();
            $PluginSccmSccmxml->setSoftwares();
            $PluginSccmSccmxml->setMemories();
            $PluginSccmSccmxml->setVideos();
            $PluginSccmSccmxml->setSounds();
            $PluginSccmSccmxml->setUsers();
            $PluginSccmSccmxml->setNetworks();
            $PluginSccmSccmxml->setStorages();

            $SXML = $PluginSccmSccmxml->sxml;

            $SXML->asXML($REP_XML.$PluginSccmSccmxml->device_id.".ocs");

            Toolbox::logInFile('sccm', "Collect OK for device - ".$PluginSccmSccmxml->device_id." \n", true);
            $task->addVolume(1);
         }
         $retcode = 1;
         Toolbox::logInFile('sccm', "Collect completed \n", true);

      } else {
         echo __("Collect is disabled by configuration.", "sccm");
      }

      return $retcode;
   }

   static function getcomputerQuery() {
      return "SELECT csd.Description00 as \"CSD-Description\",
      csd.Domain00 as \"CSD-Domain\",
      csd.Manufacturer00 as \"CSD-Manufacturer\",
      csd.Model00 as \"CSD-Model\",
      csd.Roles00 as \"CSD-Roles\",
      csd.SystemType00 as \"CSD-SystemType\",
      csd.UserName00 as \"CSD-UserName\",
      csd.MachineID as \"CSD-MachineID\",
      csd.TimeKey as \"CSD-TimeKey\",
      md.SystemName00 as \"MD-SystemName\",
      osd.BuildNumber00 as \"OSD-BuildNumber\",
      osd.Caption00 as \"OSD-Caption\",
      osd.CSDVersion00 as \"OSD-CSDVersion\",
      osd.BootDevice00 as \"OSD-BootDevice\",
      osd.InstallDate00 as \"OSD-InstallDate\",
      osd.LastBootUpTime00 as \"OSD-LastBootUpTime\",
      osd.Manufacturer00 as \"OSD-Manufacturer\",
      osd.Name00 as \"OSD-Name\",
      osd.Organization00 as \"OSD-Organization\",
      osd.RegisteredUser00 as \"OSD-RegisteredUser\",
      osd.TotalVirtualMemorySize00 as \"OSD-TotalVirtualMemory\",
      osd.TotalVisibleMemorySize00 as \"OSD-TotalVisibleMemory\",
      osd.Version00 as \"OSD-Version\",
      pbd.SerialNumber00 as \"PBD-SerialNumber\",
      pbd.ReleaseDate00 as \"PBD-ReleaseDate\",
      pbd.Name00 as \"PBD-Name\",
      pbd.SMBIOSBIOSVersion00 as \"PBD-BiosVersion\",
      pbd.Version00 as \"PBD-Version\",
      pbd.Manufacturer00 as \"PBD-Manufacturer\",
      sdi.User_Name0 as \"SDI-UserName\",
      sd.SMSID0 as \"SD-UUID\",
      sd.SystemRole0 as \"SD-SystemRole\",
      VrS.User_Name0 as \"VrS-UserName\",
      vWD.LastHWScan as \"vWD-LastScan\"
      FROM Computer_System_DATA csd
      LEFT JOIN Motherboard_DATA md ON csd.MachineID = md.MachineID
      LEFT JOIN Operating_System_DATA osd ON csd.MachineID = osd.MachineID
      LEFT JOIN v_GS_WORKSTATION_STATUS vWD ON csd.MachineID = vWD.ResourceID
      LEFT JOIN PC_BIOS_DATA pbd ON csd.MachineID = pbd.MachineID
      LEFT JOIN System_DISC sdi ON csd.MachineID = sdi.ItemKey
      LEFT JOIN System_DATA sd ON csd.MachineID = sd.MachineID
      INNER JOIN v_R_System VrS ON csd.MachineID = VrS.ResourceID
      WHERE csd.MachineID is not null and csd.MachineID != ''";
   }


   static function executePush($task) {

      $PluginSccmSccmdb = new PluginSccmSccmdb();
      $res = $PluginSccmSccmdb->connect();
      $PluginSccmConfig = new PluginSccmConfig();
      $PluginSccmConfig->getFromDB(1);
      $retcode = -1;

      if ($PluginSccmConfig->getField('active_sync') == 1) {
         if ($res) {

            $query = self::getcomputerQuery();
            $result = $PluginSccmSccmdb->exec_query($query);

            $tab = [];

            while ($tab = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {

               $REP_XML = realpath(GLPI_PLUGIN_DOC_DIR.'/sccm/xml/'.$tab['CSD-MachineID'].'.ocs');

               if ($REP_XML === false) {
                  Toolbox::logInFile('sccm', "There is a problem with the path, realpath function return false.\nPath : ".$REP_XML."\n", true);
                  continue;
               }

               $xmlFile = simplexml_load_file($REP_XML, 'SimpleXMLElement', LIBXML_NOCDATA);
               if ($xmlFile !== false) {

                  $ch = curl_init();
                  if ($PluginSccmConfig->getField('verify_ssl_cert') != "1") {
                     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                  }

                  if ($PluginSccmConfig->getField('use_auth_ntlm') == "1") {
                     curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NTLM);
                  }

                  if ($PluginSccmConfig->getField('unrestricted_auth') == "1") {
                     curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
                  }

                  if ($PluginSccmConfig->getField('use_auth_info') == "1") {
                     curl_setopt($ch, CURLOPT_USERPWD, $PluginSccmConfig->getField('auth_info'));
                  }

                  curl_setopt($ch, CURLOPT_URL, $PluginSccmConfig->getField('fusioninventory_url'));
                  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
                  curl_setopt($ch, CURLOPT_HEADER, 1);
                  curl_setopt($ch, CURLOPT_POST, 1);
                  curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlFile->asXML());
                  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
                  curl_setopt($ch, CURLOPT_REFERER, $PluginSccmConfig->getField('fusioninventory_url'));
                  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                  $ch_result = curl_exec($ch);
                  if ($ch_result === false) {
                     Toolbox::logInFile('sccm', curl_error($ch)."\n", true);
                  } else {

                     $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                     if ($httpcode != 200) {
                        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
                        $body = substr($ch_result, $header_size);

                        Toolbox::logInFile('sccm', "Push KO - ".$tab['CSD-MachineID']." -> STATUS CODE : ".$httpcode." \n", true);
                        Toolbox::logInFile('sccm', "ERROR RETURNED : ".$body." \n", true);
                     } else {
                        $task->addVolume(1);

                        if ($PluginSccmConfig->getField('use_lasthwscan') == 1) {
                           $data = PluginFusioninventoryAgent::getByDeviceID($tab['CSD-MachineID']);
                           $pfInventoryComputerComputer = new PluginFusioninventoryInventoryComputerComputer();
                           $a_computerextend = $pfInventoryComputerComputer->hasAutomaticInventory($data['computers_id']);
                           if (count($a_computerextend) > 0) {
                              $a_computerextend['last_fusioninventory_update'] = $tab['vWD-LastScan']->format('Y-m-d h:i');
                              $pfInventoryComputerComputer->update($a_computerextend);
                           }
                        }
                        Toolbox::logInFile('sccm', "Push OK - ".$tab['CSD-MachineID']." \n", true);
                     }

                  }
                  curl_close($ch);
               } else {
                  Toolbox::logInFile('sccm', "Can't load the file with the path : ".$REP_XML."\n", true);
               }
            }
            Toolbox::logInFile('sccm', "Push completed \n", true);
            $PluginSccmSccmdb->disconnect();
            $retcode = 1;
         }
      } else {
         echo __("Push is disabled by configuration.", "sccm");
      }

      return $retcode;
   }

}
