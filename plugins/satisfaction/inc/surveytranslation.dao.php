<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2018 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

class PluginSatisfactionSurveyTranslationDAO{

   static $tablename = "glpi_plugin_satisfaction_surveytranslations";

   static function getSurveyTranslationByCrit($crit = []){
      global $DB;
      $datas = [];

      $query = "SELECT * FROM `".self::$tablename."`";
      if(!empty($crit)){
         $it = 0;
         foreach($crit as $key=>$value){
            if($it == 0){
               $query.= " WHERE ";
            }else{
               $query.= " AND ";
            }
            if(is_string($value)){
               $query.= "`$key` = '".$value."'";
            }else{
               $query.= "`$key` = ".$value;
            }
            $it++;
         }
      }

      $result = $DB->query($query);

      while ($data = $DB->fetchAssoc($result)) {
         $datas[] = $data;
      }
      return $datas;
   }

   static function countSurveyTranslationByCrit($crit = []){
      global $DB;

      $query = "SELECT count(*) as nb FROM `".self::$tablename."`";
      if(!empty($crit)){
         $it = 0;
         foreach($crit as $key=>$value){
            if($it == 0){
               $query.= " WHERE ";
            }else{
               $query.= " AND ";
            }
            if(is_string($value)){
               $query.= "`$key` = '".$value."'";
            }else{
               $query.= "`$key` = ".$value;
            }

            $it++;
         }
      }

      $result = $DB->query($query);
      while ($data = $DB->fetchAssoc($result)) {
         return $data['nb'];
      }
      return 0;
   }

   static function getSurveyTranslationByID($ID){
      global $DB;

      $query = "SELECT * FROM `".self::$tablename."`";
      $query .=" WHERE `id` = ".$ID;

      $result = $DB->query($query);
      while ($data = $DB->fetchAssoc($result)) {
         return $data;
      }
   }

   static function newSurveyTranslation($surveyId, $questionId, $language, $value){
      global $DB;

      $query = "INSERT INTO `".self::$tablename."`";
      $query .= " (`plugin_satisfaction_surveys_id`, `glpi_plugin_satisfaction_surveyquestions_id`, `language`, `value`)";
      $query .= " VALUES(".$surveyId.",".$questionId.",'".$language."','".$value."')";

      if($DB->query($query)){
         return $DB->insert_id();
      }else{
         return null;
      }
   }

   static function editSurveyTranslation($id, $value){
      global $DB;

      $query = "UPDATE `".self::$tablename."`";
      $query .= " SET `value` = '".$value."'";
      $query .= " WHERE `id` = ".$id;

      return ($DB->query($query));
   }
}