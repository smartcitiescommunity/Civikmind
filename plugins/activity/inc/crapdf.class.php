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

require_once(GLPI_ROOT."/plugins/activity/lib/fpdf/fpdf.php");
require_once(GLPI_ROOT."/plugins/activity/lib/fpdf/font/arial.php");

class PluginActivityCraPDF extends FPDF {
   /* Attributs d'un rapport envoyés par l'utilisateur avant la génération. */

   var $time                = [];
   var $timeHeader          = [];
   var $holiday             = [];
   var $holidayHeader       = [];
   var $holidayComments     = [];
   var $generalinformations = [];
   var $footerInformations  = [];
   var $total_all           = 0;
   var $no_cra              = ""; // Num du document, généré.
   var $working_days        = 0;
   var $all_days            = 0;

   /* Constantes pour paramétrer certaines données. */
   var $line_height               = 3.5;     // Hauteur d'une ligne simple.
   var $day_width                 = 6;       // Largeur de cellule jour
   var $generalinformations_width = 125;     // Largeur de cellule information générale
   var $total_width               = 12;
   var $activityname_width        = 79;
   var $pol_def                   = 'arial'; // Police par défaut;
   var $tail_pol_def              = 6.5;     // Taille par défaut de la police.
   var $tail_titre                = 14;      // Taille du titre.
   var $margin_top                = 20;      // Marge du haut.
   var $margin_left               = 5;       // Marge de gauche et de droite accessoirement.
   var $largeur_grande_cell       = 280;     // Largeur d'une cellule qui prend toute la page.

   /*    * ************************************ */
   /* Methodes génériques de mise en forme. */
   /*    * ************************************ */

   /**
    * Fonction permettant de dessiner une ligne blanche séparatrice.
    */
   function Separator() {
      $this->Cell($this->largeur_grande_cell, $this->line_height, '', 0, 0, '');
      $this->SetY($this->GetY() + 10);
   }

   /**
    * Fonctions permettant remplir la couleur d'une cellule
    */
   function SetBackgroundGrey() {
      $this->SetFillColor(205, 205, 205);
   }

   function SetBackgroundHardGrey() {
      $this->SetFillColor(192, 192, 192);
   }

   function SetBackgroundBlue() {
      $this->SetFillColor(153, 204, 255);
   }

   function SetBackgroundRed() {
      $this->SetFillColor(255, 0, 0);
   }

   function SetBackgroundYellow() {
      $this->SetFillColor(255, 255, 204);
   }

   function SetBackgroundWhite() {
      $this->SetFillColor(255, 255, 255);
   }

   function SetBackgroundColor($color) {
      switch ($color) {
         case 'grey': $this->SetBackgroundGrey();
            break;
         case 'hardgrey': $this->SetBackgroundHardGrey();
            break;
         case 'red': $this->SetBackgroundRed();
            break;
         case 'blue': $this->SetBackgroundBlue();
            break;
         case 'yellow': $this->SetBackgroundYellow();
            break;
         case 'white': $this->SetBackgroundWhite();
            break;
         default : $this->SetBackgroundWhite();
            break;
      }
   }

   /**
    * Fonctions permettant définir la couleur du texte
    */
   function SetFontGrey() {
      $this->SetTextColor(205, 205, 205);
   }

   function SetFontRed() {
      $this->SetTextColor(255, 0, 0);
   }

   function SetFontBlue() {
      $this->SetTextColor(153, 204, 255);
   }

   function SetFontDarkBlue() {
      $this->SetTextColor(0, 0, 255);
   }

   function SetFontBlack() {
      $this->SetTextColor(0, 0, 0);
   }

   function SetFontColor($color) {
      switch ($color) {
         case 'grey': $this->SetFontGrey();
            break;
         case 'black': $this->SetFontBlack();
            break;
         case 'red': $this->SetFontRed();
            break;
         case 'blue': $this->SetFontBlue();
            break;
         case 'darkblue': $this->SetFontDarkBlue();
            break;
         default: $this->SetFontBlack();
            break;
      }
   }

   /**
    * Redéfinit une fonte
    *
    * @param type $color
    * @param type $bold
    * @param type $size
    */
   function SetFontNormal($color, $bold, $size) {
      $this->SetFontColor($color);
      if ($bold) {
         $this->SetFont($this->pol_def, 'B', $size);
      } else {
         $this->SetFont($this->pol_def, '', $size);
      }
   }

   /**
    * Permet de dessiner une cellule.
    *
    * @param type $w
    * @param type $h
    * @param type $value
    * @param type $border
    * @param type $align
    * @param type $color
    * @param type $bold
    * @param type $size
    * @param type $fontColor
    */
   function CellValue($w, $h, $value, $border = 'LRB', $align = 'L', $color = '', $bold = false, $size = '', $fontColor = '') {
      if (empty($size)) {
         $size = $this->tail_pol_def;
      }
      $this->SetBackgroundColor($color);
      $this->SetFontNormal($fontColor, $bold, $size);
      $this->Cell($w, $h, $value, $border, 0, $align, 1);
   }

   /**
    * Permet de dessiner une cellule multiligne.
    *
    * @param type $w
    * @param type $h
    * @param type $value
    * @param type $border
    * @param type $align
    * @param type $color
    * @param type $bold
    * @param type $size
    * @param type $fontColor
    */
   function MultiCellValue($w, $h, $value, $border = 'LRB', $align = 'C', $color = '', $bold = false, $size = '', $fontColor = '') {
      if (empty($size)) {
         $size = $this->tail_pol_def;
      }
      $this->SetBackgroundColor($color);
      $this->SetFontNormal($fontColor, $bold, $size);
      $this->MultiCell($w, $h, $value, $border, $align, 1);
   }

   /*    * *************************************** */
   /* Methodes générant le contenu du rapport. */
   /*    * *************************************** */

   /**
    * Fonction permettant de dessiner l'entéte du rapport.
    */
   function Header() {
      /* Constantes pour les largeurs de cellules de l'entéte (doivent étre = $largeur_grande_cell). */
      $logo_width  = 30;
      $logo_height = 30;

      /* Margin. */
      $this->SetX($this->margin_left);
      $this->SetY($this->margin_top);

      /* Logo. */
      $image  = '../pics/logo.jpg';
      $target = 30;
      list($width, $height, $type, $attr) = getimagesize($image);
      list($width, $height) = $this->imageResize($width, $height, $target);
      $this->Image($image, $this->margin_left + 5, $this->margin_top + $height / 3, $width, $height); // x, y, w, h
      $this->Cell($logo_width, $logo_height, '', 0, 0, 'C');

      // Title
      $this->CellValue(55, $logo_height / 2, Toolbox::decodeFromUtf8(__('Report of Activities', 'activity')), 0, 'C', '', 1, $this->tail_titre);
   }

   /**
    * Fonctions permettant de dessiner le tableau des informations générales.
    */
   function setGeneralInformations($generalinformations) {
      $this->generalinformations = $generalinformations;
   }

   function getGeneralInformations() {
      return $this->generalinformations;
   }

   function drawGeneralInformations() {
      foreach ($this->getGeneralInformations() as $type => $value) {
         $this->SetX(147);
         switch ($type) {
            case 'name':
               $this->CellValue(15, $this->line_height, Toolbox::decodeFromUtf8(__('from')).' : ', 0, 'R');
               $this->CellValue($this->generalinformations_width, $this->line_height + 1.15, Toolbox::decodeFromUtf8($value), 'LRBT', 'C', 'blue', 1, $this->tail_pol_def * 1.5, 'darkblue');
               $this->Ln(5.5);
               $this->SetX(182);
               $this->CellValue($this->generalinformations_width / 3, 2, Toolbox::decodeFromUtf8(__('Name')), 0, 'C');
               $this->CellValue($this->generalinformations_width / 3, 2, Toolbox::decodeFromUtf8(__('First name')), 0, 'C');
               $this->Ln(3);
               break;
            case 'date':
               $this->CellValue(15, $this->line_height, Toolbox::decodeFromUtf8(__('during', 'activity')).' : ', 0, 'R');
               $this->CellValue($this->generalinformations_width, $this->line_height + 1.15, Toolbox::decodeFromUtf8($value), 'LRBT', 'C', 'hardgrey', 1, $this->tail_pol_def * 1.5, 'black');
               $this->Ln(5.5);
               $this->SetX(182);
               $this->CellValue($this->generalinformations_width / 3, 2, Toolbox::decodeFromUtf8(__('Month')), 0, 'C');
               $this->CellValue($this->generalinformations_width / 3, 2, Toolbox::decodeFromUtf8(__('Year', 'activity')), 0, 'C');
               $this->Ln(3);
               break;
            case 'client':
               $this->CellValue(15, $this->line_height, Toolbox::decodeFromUtf8(__('for', 'activity')).' : ', 0, 'R');
               $this->CellValue($this->generalinformations_width, $this->line_height + 1.15, Toolbox::decodeFromUtf8($value), 'LRBT', 'C', 'yellow', 1, $this->tail_pol_def * 1.5, 'darkblue');
               $this->Ln(5.5);
               $this->SetX(182);
               $this->CellValue($this->generalinformations_width / 3, 2, Toolbox::decodeFromUtf8(__('Client', 'activity')), 0, 'C');
               $this->CellValue($this->generalinformations_width / 3, 2, Toolbox::decodeFromUtf8(__('Service')), 0, 'C');
               $this->Ln(3);
               break;
         }
      }
   }

   /**
    * Fonctions permettant de dessiner la zone des temps passés.
    */
   function setTime($time) {
      $this->time = $time;

      $nb_lines      = 26;
      $nb_activities = count($time);
      if ($nb_activities < $nb_lines) {
         $nb_lines_to_add = $nb_lines - $nb_activities;
         $one_line        = $this->getTimeHeader();
         foreach ($one_line as &$data) {
            $data['values'] = '';
         }
         for ($i = 0; $i < $nb_lines_to_add; $i++) {
            $this->time[] = $one_line;
         }
      }
   }

   function setTimeHeader($timeHeader) {
      $this->timeHeader = $timeHeader;
   }

   function getTime() {
      return $this->time;
   }

   function getTimeHeader() {
      return $this->timeHeader;
   }

   function drawTimeHeader() {
      $this->CellValue($this->activityname_width, $this->line_height, '', 0, 'C', 'white');
      foreach ($this->getTimeHeader() as $num => $header) {
         if (isset($header['options']['weekend'])) {
            $this->CellValue($this->day_width, $this->line_height, Toolbox::decodeFromUtf8($header['header']), 'LRBT', 'C', 'hardgrey');
         } else {
            $this->CellValue($this->day_width, $this->line_height, Toolbox::decodeFromUtf8($header['header']), 'LRBT', 'C');
         }
      }
      $this->completeTable(count($this->getTimeHeader()), $this->line_height, 'LRBT');
      $this->Ln();

      $this->CellValue($this->activityname_width, $this->line_height, Toolbox::decodeFromUtf8(__('Project / activity', 'activity')), 'LRBT', 'C', 'blue', 0, 8);
      foreach ($this->getTimeHeader() as $num => $header) {
         $num = (float) $num;
         if (isset($header['options']['weekend'])) {
            $this->CellValue($this->day_width, $this->line_height, Toolbox::decodeFromUtf8($num), 'LRBT', 'C', 'hardgrey');
         } else {
            $this->CellValue($this->day_width, $this->line_height, Toolbox::decodeFromUtf8($num), 'LRBT', 'C');
         }
      }
      $this->completeTable(count($this->getTimeHeader()), $this->line_height, 'LRBT');
      $this->CellValue($this->total_width, $this->line_height, Toolbox::decodeFromUtf8(__('Total')), 'LRBT', 'C', 'hardgrey', 1);
      $this->Ln();
   }

   function computeTotal($data_time = []) {
      $total_all = 0;
      foreach ($data_time as $times) {
         foreach ($times as $data) {
            $total_all += PluginActivityReport::TotalTpsPassesArrondis($data['values']);
         }
      }
      $this->setTotal($this->getTotal() + $total_all);
   }

   function drawTime($data_time = [], $type) {

      $count = 0;
      foreach ($data_time as $activity => $times) {
         $line_color   = '';
         $working_days = 0;
         if (($count % 2) != 1 || $count == 0) {
            $line_color = 'yellow';
         }
         $border = 'LR';
         if ($count == 0) {
            $border = 'LRT';
         }

         $activity_data = $this->getActivityName($activity);
         if ($type == PluginActivityReport::$WORK) {
            $this->CellValue($this->activityname_width / 2, $this->line_height, Toolbox::decodeFromUtf8($activity_data['parent']), str_replace('R', '', $border), 'L', $line_color, 0, '', 'darkblue');
            $this->CellValue($this->activityname_width / 2, $this->line_height, Toolbox::decodeFromUtf8($activity_data['child']), str_replace('L', '', $border), 'C', $line_color, 0, '', 'darkblue');
         } else {
            $this->CellValue($this->activityname_width / 2, $this->line_height, Toolbox::decodeFromUtf8($activity_data['child']), $border, 'L', $line_color, 0, '');
            $this->CellValue($this->activityname_width / 2, $this->line_height, Toolbox::decodeFromUtf8($activity_data['parent']), $border, 'L', $line_color, 0, '');
         }

         $total_activity = 0;
         foreach ($times as $data) {
            $font_color = 'darkblue';
            if (isset($data['options']['weekend'])) {
               $color = 'hardgrey';
            } else if (isset($data['options']['depass'])) {
               $color      = $line_color;
               $font_color = 'red';
            } else {
               $color = $line_color;
               $working_days++;
            }

            // Use round values
            $data['values'] = PluginActivityReport::TotalTpsPassesArrondis($data['values']);

            $data['values'] = (float) $data['values'];
            $this->CellValue($this->day_width, $this->line_height, !empty($data['values']) ? $data['values'] : '', $border, 'C', $color, 0, '', $font_color);
            $total_activity += $data['values'];
         }
         $count++;
         $this->completeTable(count($times), $this->line_height, $border);

         // Dislay activity total
         if (PluginActivityReport::isIncorrectValue($total_activity)) {
            $font_color = 'red';
         }
         $this->CellValue($this->total_width, $this->line_height, !empty($total_activity) ? $total_activity : '', $border, 'C', $line_color, 0, '', $font_color);
         $this->Ln();
      }

      $this->setWorkingDays($working_days);
      $this->setAllDays(count($times));
      $this->total_bigwidth = $this->activityname_width + $this->day_width * 31;
   }

   function completeTable($all_days, $h, $border) {
      if ($all_days < 31) {
         for ($i = 0; $i < (31 - $all_days); $i++) {
            $this->CellValue($this->day_width, $h, '', $border, 'C', 'hardgrey', 0, '');
         }
      }
   }

   function getActivityName($activity) {
      $parent = '';
      $child  = '';
      if (!is_integer($activity)) {
         if (strstr($activity, '>')) {
            list($parent, $child) = explode('>', $activity);
         } else {
            $child = $activity;
         }
      }

      return ['parent' => $parent, 'child' => $child];
   }

   /**
    * Fonctions permettant de dessiner la zone des congés.
    */
   function setHoliday($holiday = [], $sickness = [], $part_time = []) {
      if (empty($part_time)) {
         $part_time = $this->getTimeHeader();
      }
      if (empty($holiday)) {
         $holiday   = $this->getTimeHeader();
      }
      if (empty($sickness)) {
         $sickness  = $this->getTimeHeader();
      }

      $this->holiday[__('Part time', 'activity')]                        = $part_time;
      $this->holiday[__('Holidays or exceptional absences', 'activity')] = $holiday;
      $this->holiday[__('Sickness or maternity', 'activity')]            = $sickness;
   }

   function setHolidayHeader($holidayHeader) {
      $this->holidayHeader = $holidayHeader;
   }

   function setHolidayComments($holidayComments) {
      $this->holidayComments = $holidayComments;
   }

   function getHoliday() {
      return $this->holiday;
   }

   function getHolidayHeader() {
      return $this->holidayHeader;
   }

   function getHolidayComments() {
      return $this->holidayComments;
   }

   function drawHolidayHeader() {
      $count      = count($this->getTime());
      $line_color = '';
      if (($count % 2) != 1) {
         $line_color = 'yellow';
      }
      $this->CellValue($this->activityname_width / 2, $this->line_height, Toolbox::decodeFromUtf8(__('Absences', 'activity')), 'LRBT', 'C', 'blue', 0, 8);
      $this->CellValue($this->activityname_width / 2, $this->line_height, Toolbox::decodeFromUtf8(__('Comments')), 'LRBT', 'C', 'blue', 0, 8);
      foreach ($this->getTimeHeader() as $header) {
         $color = $line_color;
         if (isset($header['options']['weekend'])) {
            $color = 'hardgrey';
         }
         $this->CellValue($this->day_width, $this->line_height, '', 'LRBT', 'C', $color);
      }
      $this->completeTable(count($this->getTimeHeader()), $this->line_height, 'LRBT');
      $this->CellValue($this->total_width, $this->line_height, $this->getTotal(), 'LRBT', 'C', $line_color, 0, '', 'darkblue');
      $this->Ln();
   }

   /**
    * Fonctions permettant de dessiner le total.
    */
   function setTotal($total) {
      $this->total_all = $total;
   }

   function getTotal() {
      return $this->total_all;
   }

   function drawTotal() {
      $count      = count($this->getTime()) + count($this->getHoliday()) + 1;
      $line_color = '';
      if (($count % 2) != 1) {
         $line_color = 'yellow';
      }

      $this->CellValue($this->total_bigwidth / 3, $this->line_height, Toolbox::decodeFromUtf8(strtoupper(__('Total'))), 'LBT', 'L', 'hardgrey', 1);
      $this->CellValue($this->total_bigwidth / 3, $this->line_height, Toolbox::decodeFromUtf8(__('Entry except in the month, the total must be equal to the number of working days this month', 'activity').' : '), 'BT', 'R', 'hardgrey', 0, 6);
      $this->CellValue($this->total_bigwidth / 3, $this->line_height, $this->getWorkingDays(), 'BT', 'C', 'hardgrey', 1, 6);
      if ($this->total_all != $this->working_days) {
         $this->CellValue($this->total_width, $this->line_height, $this->getTotal(), 'LRBT', 'C', $line_color, 1, '', 'red');
      } else {
         $this->CellValue($this->total_width, $this->line_height, $this->getTotal(), 'LRBT', 'C', 'blue', 1);
      }
      $this->Ln(4);
      if ($this->total_all != $this->working_days) {
         $this->CellValue($this->total_bigwidth, $this->line_height, Toolbox::decodeFromUtf8(strtoupper(__('Total incorrect', 'activity'))), 0, 'R', '', 0, 8);
      } else {
         $this->CellValue($this->total_bigwidth, $this->line_height, Toolbox::decodeFromUtf8(strtoupper(__('Total OK', 'activity'))), 0, 'R', '', 0, 8);
      }
   }

   function setWorkingDays($working_days) {
      $this->working_days = $working_days;
   }

   function getWorkingDays() {
      return $this->working_days;
   }

   function setAllDays($all_days) {
      $this->all_days = $all_days;
   }

   function getAllDays() {
      return $this->all_days;
   }

   function setFooterInformations($footerInformations) {
      $this->footerInformations = $footerInformations;
   }

   function getFooterInformations() {
      return $this->footerInformations;
   }

   /**
    * Fonction permettant de dessiner le pied de page du rapport.
    */
   function Footer() {
      $times    = $this->getTime();
      $holidays = $this->getHoliday();
      if (!empty($times) && !empty($holidays)) {
         $this->Ln(-6);
         foreach ($this->getFooterInformations() as $value) {
            $this->SetX(60);
            $this->CellValue(180, $this->line_height, Toolbox::decodeFromUtf8($value), 0, 'C', '', 0, 5.5);
            $this->Ln();
         }
         $this->SetX(60);
         $this->CellValue(180, $this->line_height, Toolbox::decodeFromUtf8(__('Generated with GLPI on', 'activity').' '.date('d/m/Y')), 0, 'C', '', 0, 5.5);
      }
   }

   /**
    * Fonction permettant de dessiner le rapport partie par partie.
    */
   function DrawCra() {
      $this->AliasNbPages();
      $this->AddPage();
      $this->Separator();

      $this->drawGeneralInformations();
      $this->ln(2);

      // Get times
      $times    = $this->getTime();
      $holidays = $this->getHoliday();

      // Compute total
      $this->computeTotal($times);
      $this->computeTotal($holidays);

      // Display work times
      if (!empty($times)) {
         $this->drawTimeHeader();
         $this->drawTime($times, PluginActivityReport::$WORK);
      }

      // Display holiday times
      if (!empty($holidays) && !empty($times)) {
         $this->drawHolidayHeader();
      }
      if (!empty($holidays)) {
         $this->drawTime($holidays, PluginActivityReport::$HOLIDAY);
      }

      // Display total of all
      if (!empty($times)) {
         $this->drawTotal();
         $this->Separator();
      }
   }

   /*    * *************** */
   /* Autres méthodes. */
   /*    * *************** */

   /**
    * Retourne une date donnée formatée dd/mm/yyyy.
    * @param $une_date Date é formater.
    * @return La date donnée au format dd/mm/yyyy.
    */
   function GetDateFormatee($une_date) {

      return $this->CompleterAvec0($une_date['mday'], 2)."/".$this->CompleterAvec0($une_date['mon'], 2)."/".$une_date['year'];
   }

   /**
    * Retourne une heure donnée au format hh:mm.
    * @param $une_date Date é formater.
    * @return L'heure donnée au format hh:mm.
    */
   function GetHeureFormatee($une_date) {

      return $this->CompleterAvec0($une_date['hours'], 2).":".$this->CompleterAvec0($une_date['minutes'], 2);
   }

   /**
    * Génération auto du né du CRI é l'aide d'une date donnée et ne le fait qu'une fois.
    * @param $une_date Date servant é la génération du né du CRI.
    * @return Le né de CRI généré.
    */
   function GetNoCra($une_date = "") {

      if ($this->no_cra == "" && $une_date != "") {
         $this->no_cra = substr($une_date['year'], 2).$this->CompleterAvec0($une_date['mon'], 2)
                 .$this->CompleterAvec0($une_date['mday'], 2)."-".$this->CompleterAvec0($une_date['hours'], 2)
                 .$this->CompleterAvec0($une_date['minutes'], 2).$this->CompleterAvec0($une_date['seconds'], 2);
      }
      return $this->no_cra;
   }

   /**
    * Compléte une chaéne donnée avec des '0' suivant la longueur donnée et voulue de la chaéne.
    * @param $une_chaine Chaéne é compléter.
    * @param $lg Longueur finale souhaitée de la chaéne donnée.
    * @return La chaéne complétée.
    */
   function CompleterAvec0($une_chaine, $lg) {

      while (strlen($une_chaine) != $lg) {
         $une_chaine = "0".$une_chaine;
      }

      return $une_chaine;
   }

   /**
    * ImageResize
    * @param int $width
    * @param int $height
    * @param int $target
    * @return array
    */
   function imageResize($width, $height, $target) {
      if ($width > $height) {
         $percentage = ($target / $width);
      } else {
         $percentage = ($target / $height);
      }

      $width  = round($width * $percentage);
      $height = round($height * $percentage);

      return [$width, $height];
   }

}