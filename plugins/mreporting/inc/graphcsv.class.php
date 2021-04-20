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

class PluginMreportingGraphcsv extends PluginMreportingGraph {
   const DEBUG_CSV = false;

   function initGraph($options) {
      if (!self::DEBUG_CSV) {
         header("Content-type: application/csv");
         header("Content-Disposition: inline; filename=export.csv");
      }
   }

   function showHbar($params, $dashboard = false, $width = false) {
      global $CFG_GLPI;

      $criterias = PluginMreportingCommon::initGraphParams($params);
      foreach ($criterias as $key => $val) {
         $$key=$val;
      }

      // Write in Log
      if (self::DEBUG_CSV && isset($raw_datas)) {
         Toolbox::logdebug($raw_datas);
      }

      $datas = isset($raw_datas['datas']) ? $raw_datas['datas'] : [];

      if (count($datas) <= 0) {
         return false;
      }

      $configs = PluginMreportingConfig::initConfigParams($opt['f_name'], $opt['class']);

      foreach ($configs as $k => $v) {
         $$k=$v;
      }

      if ($unit == '%') {
         $datas = PluginMreportingCommon::compileDatasForUnit($datas, $unit);
      }

      $values = array_values($datas);
      $labels = array_keys($datas);

      $options = ["title" => $title,
                  "desc" => $desc,
                  "randname" => $randname,
                  "export" => $export];

      $this->initGraph($options);

      //titles
      $out = $title." - ".$desc."\r\n";
      foreach ($labels as $label) {
         $out.= $label.$CFG_GLPI['csv_delimiter'];
      }
      $out = substr($out, 0, -1)."\r\n";

      //values
      foreach ($values as $value) {
         $out.= $value." ".$unit.$CFG_GLPI['csv_delimiter'];
      }
      $out = substr($out, 0, -1)."\r\n";

      echo $out;
   }

   function showPie($params, $dashboard = false, $width = false) {
      $this->showHbar($params);
   }

   function showHgbar($params, $dashboard = false, $width = false) {
      global $CFG_GLPI;

      $criterias = PluginMreportingCommon::initGraphParams($params);

      foreach ($criterias as $key => $val) {
         $$key=$val;
      }

      // Write in log
      if (self::DEBUG_CSV && isset($raw_datas)) {
         Toolbox::logdebug($raw_datas);
      }

      $datas = isset($raw_datas['datas']) ? $raw_datas['datas'] : [];

      if (count($datas) <= 0) {
         return false;
      }

      $configs = PluginMreportingConfig::initConfigParams($opt['f_name'], $opt['class']);

      foreach ($configs as $k => $v) {
         $$k=$v;
      }

      if ($unit == '%') {
         $datas = PluginMreportingCommon::compileDatasForUnit($datas, $unit);
      }

      $labels2 = array_values($raw_datas['labels2']);

      $options = ["title" => $title,
                  "desc" => $desc,
                  "randname" => $randname,
                  "export" => $export];

      $this->initGraph($options);

      $out = $title." - ".$desc."\r\n";

      foreach ($datas as $label2 => $cols) {
         //title
         $out.= $label2."\r\n";

         //subtitle
         $i = 0;
         foreach ($cols as $value) {
            $label = "";
            if (isset($labels2[$i])) {
               $label = str_replace(",", "-", $labels2[$i]);
            }
            $out.= $label.$CFG_GLPI['csv_delimiter'];
            $i++;
         }
         $out = substr($out, 0, -1)."\r\n";

         //values
         foreach ($cols as $value) {
            $out.= $value." ".$unit.";";
         }
         $out = substr($out, 0, -1)."\r\n\r\n";
      }
      $out = substr($out, 0, -1)."\r\n";

      echo $out;
   }

   function showVstackbar($params, $dashboard = false, $width = false) {
      $this->showHGbar($params);
   }

   function showArea($params, $dashboard = false, $width = false) {
      $this->showHbar($params);
   }

   function showGarea($params, $dashboard = false, $width = false) {
      $this->showHGbar($params);
   }

   function showSunburst($params, $dashboard = false, $width = false) {
      $criterias = PluginMreportingCommon::initGraphParams($params);

      foreach ($criterias as $key => $val) {
         $$key=$val;
      }

      if (self::DEBUG_CSV && isset($raw_datas)) {
         Toolbox::logdebug($raw_datas);
      }

      if (isset($raw_datas['datas'])) {
         $datas = $raw_datas['datas'];
      } else {
         $datas = [];
      }

      if (count($datas) <= 0) {
         return false;
      }

      $configs = PluginMreportingConfig::initConfigParams($opt['f_name'], $opt['class']);

      foreach ($configs as $k => $v) {
         $$k=$v;
      }

      if ($unit == '%') {
         $datas = PluginMreportingCommon::compileDatasForUnit($datas, $unit);
      }

      $options = ["title" => $title,
                  "desc" => $desc,
                  "randname" => $randname,
                  "export" => $export];

      $this->initGraph($options);

      $out = $title." - ".$desc."\r\n";
      $out.= $this->sunburstLevel($datas);

      echo $out;
   }

   function sunburstLevel($datas, $level = 0) {
      $out = "";

      $i = 0;
      foreach ($datas as $label => $value) {
         for ($j=0; $j < $level; $j++) {
            if ($i > 0) {
               $out.= $CFG_GLPI['csv_delimiter'];
            }
         }

         if (is_array($value)) {
            arsort($value);
            $out.= $label.$CFG_GLPI['csv_delimiter'];
            $out.= $this->sunburstLevel($value, $level+1)."\r\n";
         } else {
            $out.= $label.$CFG_GLPI['csv_delimiter'].$value."\r\n";
         }
         $i++;
      }

      return $out;
   }
}
