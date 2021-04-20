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

class PluginActivityTools {
   
   /**
    * Get the first ticket stored fields
    * @param array $params<bR>
    * entities_id : id of the entity
    * sons : either 0 or 1 if recursive or not
    * @return type
    */
   static function getFirstTicket($params){
      global $DB;
      $query = "SELECT `glpi_tickets`.*  
                FROM `glpi_tickets`
                WHERE `glpi_tickets`.`date` IN (SELECT min(`glpi_tickets`.`date`) 
                                                FROM `glpi_tickets` 
                                                WHERE `glpi_tickets`.`is_deleted` = 0 
                                                ".self::getSpecificEntityRestrict('glpi_tickets', $params).")
                  AND `glpi_tickets`.`is_deleted` = 0 
                  ".self::getSpecificEntityRestrict('glpi_tickets', $params);

      $result = $DB->fetchArray($DB->query($query));

      return $result;
   }

   /**
    * Get the last ticket stored fields
    * @param array $params<bR>
    * entities_id : id of the entity
    * sons : either 0 or 1 if recursive or not
    * @return type
    */
   static function getLastTicket($params){
      global $DB;
      $query = "SELECT `glpi_tickets`.*  
                FROM `glpi_tickets`
                WHERE `glpi_tickets`.`date` IN (SELECT max(`glpi_tickets`.`date`)
                                                FROM `glpi_tickets` 
                                                WHERE `glpi_tickets`.`is_deleted` = 0 
                                                ".self::getSpecificEntityRestrict('glpi_tickets', $params).")
                  AND `glpi_tickets`.`is_deleted` = 0 
                  ".self::getSpecificEntityRestrict('glpi_tickets', $params);

      $result = $DB->fetchArray($DB->query($query));

      return $result;
   }
   
   /**
    * Get months ticks between two dates
    * @param type $params
    * @return type
    */
   static function getMonthTicks($params){
      $months = self::getMonths($params);

      return json_encode($months['ticks']);
   }

   /**
    * Get an array of every months between :<br><ul>
    * <li>the beginning of a year to the end (default)</li>
    * <li>the first ticket and the last one (if all_period is set)<li>
    * @param type $params
    * @return string
    */
   static function getMonths($params){
      $months = Toolbox::getMonthsOfYearArray();
      $res = array();
      if(isset($params['all_period']) && $params['all_period']==1) {
         $firstticket = self::getFirstTicket($params);
         $lastticket = self::getLastTicket($params);
         $firstticketmonth = trim(date("m",strtotime($firstticket['date'])),'0');
         $lastticketmonth = trim(date("m",strtotime($lastticket['date'])),'0');
         $firstticketyear = trim(date("Y",strtotime($firstticket['date'])),'0');
         $lastticketyear = trim(date("Y",strtotime($lastticket['date'])),'0');

         $count = 0;
         for($y = $firstticketyear; $y <= $lastticketyear;$y++) {
            $deb = ($y == $firstticketyear)?$firstticketmonth:1;
            $end = ($y == $lastticketyear)?$lastticketmonth:12;
            for($m = $deb; ($m<=$end); $m++){
               $res[$m+$count] = array("year" => $y,"month" => $m);
               $tick = substr($months[$m],0,1);
               if($m == 1 || ($m == $firstticketmonth && $y == $firstticketyear)){
                  $tick .= " ".$y;
               }
               $res['ticks'][] = array($m+$count,$tick);
            }
            $count+=12;
         }

      } else {
         if(!isset($params['begin']) || !isset($params['end'])){
            $res[] = array("year" => $params['year']-1,"month" => 12);
            $res['ticks'][] = array(0,$months[12]." ".($params['year']-1));
            foreach($months as $key => $month){
               $res[] = array("year" => $params['year'],"month" => $key);
               $res['ticks'][] = array($key,$month);
            }
         } else {
            $firstticketmonth = trim(date("m",strtotime($params['begin'])),'0');
            $lastticketmonth = trim(date("m",strtotime($params['end'])),'0');
            $firstticketyear = trim(date("Y",strtotime($params['begin'])),'0');
            $lastticketyear = trim(date("Y",strtotime($params['end'])),'0');

            $count = 0;
            for($y = $firstticketyear; $y <= $lastticketyear;$y++) {
               $deb = ($y == $firstticketyear)?$firstticketmonth:1;
               $end = ($y == $lastticketyear)?$lastticketmonth:12;
               for($m = $deb; ($m<=$end); $m++){
                  $res[$m+$count] = array("year" => $y,"month" => $m);
                  $tick = substr($months[$m],0,1);
                  if($m == 1 || ($m == $firstticketmonth && $y == $firstticketyear)){
                     $tick .= " ".$y;
                  }
                  $res['ticks'][] = array($m+$count,$tick);
               }
               $count+=12;
            }
         }         
      }
      return $res;
   }
   
   /**
    * Get entity restriction for a specific table
    * @param type $table, name of the table
    * @param type $params
    * @return type
    */
   static function getSpecificEntityRestrict($table,$params){

      $dbu = new DbUtils();
      if(isset($params['entities_id']) /*&& ($params['entities_id'] != 0)*/) {
         if(isset($params['sons']) && ($params['sons'] != 0)) {

   //         $tmp_sons = isset($sons)
            $entities = " AND `$table`.`entities_id` IN  (".implode(",",$dbu->getSonsOf("glpi_entities",$params['entities_id'])).") ";
          } else {
            $entities = " AND `$table`.`entities_id` = ".$params['entities_id']." ";
         }
      } else {
         $entities = $dbu->getEntitiesRestrictRequest("AND",$table/*,'','',isset($params['sons']) && ($params['sons'] != 0),true*/);
      }
      return $entities;
   }
   
   static function roundTime($time){
      $mod = round($time / 30,0);

      if($time > 480){
         return ($mod * 30)+120;
      } else {
         return ($mod * 30)+30;
      }   
   }

   static function timeTicks($time){
      $res = array();

      //For an 8h day length (8*60)
      $daylength = 480;

      $space = 30;
      //Based on a 8h day;
      if($time > $daylength){
         $space = $daylength/4;
      }

      for($i = 0;$i <= $time;$i+=$space){
         if($time > $daylength){
            if(($i % $daylength) == 0){ // 1 day
               $res[] = array($i,($i/$daylength)." j ");
            } else {
               if(($i % ($space * 2)) == 0) { // 1/2 day
                  $res[] = array($i,floor($i/$daylength).".5 j");
               } else {
                  if((($i % $daylength) % ($space * 3)) == 0) { // 3/4 day
                     $res[] = array($i,floor($i/$daylength).".75 j ");
                  } else {
                     if(($i % $daylength) % ($space) == 0) { // 1/4 day
                        $res[] = array($i,floor($i/$daylength).".25 j ");
                     }
                  }
               }
            }
         } else {
            if(($i % 60) == 0) {
               $res[] = array($i,($i/60)." h ");
            } else {
               if($i < 60){
                  $res[] = array($i,$i." min");
               } else {
                  $h = floor($i / 60);
                  $m = $i % 60;
                  $res[] = array($i,$h." h ".$m);
               }
            }
         }
      }
      return $res;
   }
   
   static function getExportForm(){
      return "<tr><td><form name='image-download' id='image-download' action='' onsubmit='return false'>"
              ."<label><input type='radio' name='format' value='png' checked='checked'> PNG</label>"
              . "<label><input type='radio' name='format' value='jpeg'> JPEG</label>&nbsp;"
   //           . "<button name='to-image' onclick='exportTo(\"to-image\")'>To Image</button>"
              . "<button name='download' onclick='exportTo(\"download\")'>".__("Export the report","activity")."</button>"
   //           . "<button name='reset' onclick='exportTo(\"reset\")'>Reset</button>"
              . "  </form></td></tr>";
   }
   
   static function getExportFunction(){
      return "this.exportTo = function (operation) {

               var
                 format = $('#image-download input:radio[name=format]:checked').val();
               if(typeof format === 'undefined') format = 'png';
               if (Flotr.isIE && Flotr.isIE < 9) {
                 alert(
                   \"Your browser doesn't allow you to get a bitmap image from the plot, \" +
                   \"you can only get a VML image that you can use in Microsoft Office.<br />\"
                 );
               }

               if (operation == 'to-image') {
                 graph.download.saveImage(format, null, null, true)
               } else if (operation == 'download') {
                 graph.download.saveImage(format);
               } else if (operation == 'reset') {
                 graph.download.restoreCanvas();
               }
             };";
   }
   
   static function getOptions($params){
      $firstmonth = reset($params['months']);
      $min = $firstmonth['month'];
      
      $defaultColors = array('#1f77b4','#aec7e8','#ff7f0e','#ffbb78','#2ca02c',
                       '#98df8a','#d62728','#ff9896','#9467bd','#c5b0d5',
                       '#8c564b','#c49c94','#e377c2','#f7b6d2','#7f7f7f',
                       '#c7c7c7','#bcbd22','#dbdb8d','#17becf','#9edae5');
      
      $options = "   {
                  yaxis: {";
      if(isset($params['yticks'])){
         $options .=" ticks: ".json_encode($params['yticks']).",";
      }
                      
      $options .="    min: ".(isset($params['minyaxis'])?$params['minyaxis']:0).",
                      max: ".(isset($params['maxyaxis'])?$params['maxyaxis']+0.25*$params['maxyaxis']:500)."
                  },
                  y2axis: {
                      min: ".(isset($params['miny2axis'])?$params['miny2axis']:0).",
                      max: ".(isset($params['maxy2axis'])?$params['maxy2axis']+0.25*$params['maxy2axis']:500)."
                  },
                  xaxis: {
                      ticks: ".json_encode($params['months']['ticks']).",
                      min: ".((isset($params['all_period']) && $params['all_period'] == 1)?$min:0).",
                      max: ".((count($params['months']['ticks'])+((isset($params['all_period']) && $params['all_period'] == 1)?$min:0))).",
                  },";
      if(isset($params['bars'])){
         $options .="bars: {
                        show:true,";
         if(isset($params['bars']['stacked']) && $params['bars']['stacked'] == true){
            $options .="   stacked:true,
                           stackingType:'a',";
         }
         $options .="   fill:true,
                        fillOpacity:".(isset($params['bars']['fillOpacity'])?$params['bars']['fillOpacity']:0.7).",
                        lineWidth:".(isset($params['bars']['lineWidth'])?$params['bars']['lineWidth']:0).",
                        barWidth:".(isset($params['bars']['barWidth'])?$params['bars']['barWidth']:0.8).",
                        shadowSize : ".(isset($params['bars']['shadowSize'])?$params['bars']['shadowSize']:0)."
                     },";
      }
      $options .="grid: {
                      verticalLines: false,
                      backgroundColor: ['#fff', '#ccc']
                  },
                  HtmlText: false,
                  legend: {
                    position: ".(isset($params['legend'])?$params['legend']:"'nw'")."
                  },
                  colors:".json_encode(isset($params['colors'])?$params['colors']:$defaultColors)."
               }
            );";
      
      return $options;
   }
   
   static function showLine($params,$object){
      $id = md5($params['title']);
      echo "<table class='tab_cadre_fixe' ><tr><th>".$params['title']."</th></tr>";
      echo "<tr><td>".$object->showForm($params)."</td></tr>";
      echo self::getExportForm();
      
      if(!(isset($params['all_period']) && $params['all_period'] == 1)){
         echo "<tr><td class='stats' id='$id'>
               </td></tr></table>";
      } else {
         echo "</table>";
         echo "<div id='$id' class='statsbig'></div>";
      }

      Html::requireJs('activity');

      $script = "(function graph_$id(container){";

      $script .= "graph = Flotr.draw(";
      $script .= "   container,";
      //Send an array of different arrays of data
      $script .= str_replace('"%vFormatter%"','function(o){ if(o.y > 0) return "+"+Math.round(o.y)+"%"; else return Math.round(o.y)+"%"; }',json_encode($params['data'], JSON_NUMERIC_CHECK)).",";

      $script .= self::getOptions($params);
      $script .= self::getExportFunction();
      $script .= "return graph;";
      $script .= "})(document.getElementById('$id'))";

      echo Html::scriptBlock('$(document).ready(function() {'.$script.'});');
   }
   
   static function showForm($params){
   
      $form = "<form action='".$_SERVER['REQUEST_URI']."' method='POST'>";
      $form .= "<table class='tab_cadre_fixe'><tr><th>".__("Entity")."</th>";
      $form .= "<td class='tab_bg_2'>".Entity::dropdown(array('name' => 'entities_id',
                                                               'display' => false,
                                                               'value' => isset($params['entities_id'])? $params['entities_id'] : 0,
                                                               'entity'    => $_SESSION['glpiactiveentities']))
                           ."&nbsp;".__('Recursive')."&nbsp;<input type='checkbox' name='sons' value=1 ".(isset($params['sons'])? "checked" : "")."></td>";
      $form .= "<th>".__("By year")."</th><td class='tab_bg_2'>"
               . Dropdown::showNumber("year", ['value' => $params['year'],
                                               'min'   => date("Y") - 10,
                                               'max'   => date("Y"),
                                               'display' => false]) . "</td></tr>";
      
//      if(isset($params['groups_id'])){
//         $form .= "<tr><th colspan='2'>".__("Group")."</th><td class='tab_bg_2' colspan='2'>".Group::dropdown(array('display' => false,'value' => $params['groups_id']))."</td></tr>";
//      }
      $count = 0;
      foreach($params as $key => $value){
         $validparam = false;
         
         if((substr($key,0,3) == "nb_") && ($value == 0 || $value == 1)){
            $tmp = self::showCheck($params['labels'][$key], $key, $value);
            $validparam = true;
         }
         
         if(($key == "all_period") && ($value == 0 || $value == 1)){
            $tmp = self::showCheck($params['labels'][$key], $key, $value);
            $validparam = true;
         }
         
         if($key == "groups_id") {
            $tmp = "<th>".__("Group")."</th><td class='tab_bg_2'>".Group::dropdown(array('display' => false,'value' => $value))."</td>";
            $validparam = true;
         }
         
         if($validparam){
            if($count%2 == 0) {
               $form .= "<tr>";
            }
            $form .= $tmp;
            if($count%2 == 1) {
               $form .= "</tr>";
            }
            $count++;
         }
         
      }
      
      if($count%2 != 0){
         $form .= "<td colspan='2' class='tab_bg_2'></td></tr>";
      }
      if(isset($params['specify_dates'])){
         $form .= "<tr><th>".__('Begin')."</th><td>".Html::showDateField("begin", array('display' => false,'value' => isset($params['begin'])?$params['begin']:date("Y-01-01")))."</td>";
         $form .= "<th>".__('End')."</th><td>".Html::showDateField("end", array('display' => false,'value' => isset($params['end'])?$params['end']:date("Y-m-d")))."</td></tr>";
      }
      $form .= "<tr><th colspan='4'><input type='submit' class='submit' value='".__("Refresh","activity")."'/></th></tr>";
      $form .= "</table>";
      $form .= Html::closeForm(false);
      
      return $form;
   }
   
   private static function showCheck($label,$name,$value){
      $checked = (isset($value)&&$value==1)?"checked":"";
      $check = "<th>".$label."</th><td class='tab_bg_2'><input type='checkbox' name='$name' value='1'".$checked."/></td>";
      return $check;
   }
   
   static function initParams(&$params,$post){
      foreach($post as $key => $value){
         $params[$key] = $value;
      }
      if(!empty($post)) {
         foreach($params as $key => $value){
            if(substr($key,0,3) == "nb_" && !isset($post[$key])){
               $params[$key] = 0;
            }
         }
      }
      $params['sons'] = ($_SESSION["glpiactive_entity_recursive"] == 1 && (empty($post) || (isset($post['sons']))))?$_SESSION["glpiactive_entity_recursive"]:null;
   }
}
