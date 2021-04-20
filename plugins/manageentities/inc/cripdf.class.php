<?php
/*
 * @version $Id: HEADER 15930 2011-10-30 15:47:55Z tsmr $
 -------------------------------------------------------------------------
 Manageentities plugin for GLPI
 Copyright (C) 2014-2017 by the Manageentities Development Team.

 https://github.com/InfotelGLPI/manageentities
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Manageentities.

 Manageentities is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Manageentities is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Manageentities. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

require_once(GLPI_ROOT . "/plugins/manageentities/fpdf/fpdf.php");
require_once(GLPI_ROOT . "/plugins/manageentities/fpdf/font/symbol.php");

class PluginManageentitiesCriPDF extends FPDF {

   /* Attributs d'un rapport envoyés par l'utilisateur avant la génération. */

   var $sous_contrat      = false;    // Détermine si c'est une intervention sous contrat.
   var $deplacement       = false;     // S'il y a la gestion des deplacements dans le contrat
   var $nombredeplacement = 0;   // Total déplacements
   var $libelle_activite  = "";   // Libellé de l'activité du CRI.
   var $description_cri   = "";    // Description du document (concaténation des suivis non privés).
   var $no_cri            = "";             // Né du document, généré.

   /* Autres attributs, récupérés par exemple en base de donnée. */
   var $demande_associee  = "";   // Identifiant du ticket pour lequel on génére le rapport.
   var $intervenant       = "";        // Intervenant du ticket.
   var $date_intervention = null;// Tableau de 3 éléments (0 --> année et mois; 1 --> du; 2 --> au).
   var $entite            = null;           // Tableau de 3 élements : entity, entitydata et contrat.
   var $temps_passes      = null;     // Tableau des temps passés sur l'intervention.
   var $forfait           = false;         // Type de contrat forfait
   var $intervention      = false;    //Type de contrat à l'intervention

   /* Constantes pour paramétrer certaines données. */
   var $line_height         = 5;         // Hauteur d'une ligne simple.
   var $pol_def             = 'Arial';       // Police par défaut;
   var $tail_pol_def        = 10;       // Taille par défaut de la police.
   var $tail_titre          = 22;         // Taille du titre.
   var $marge_haut          = 5;          // Marge du haut.
   var $marge_gauche        = 15;       // Marge de gauche et de droite accessoirement.
   var $largeur_grande_cell = 190;   // Largeur d'une cellule qui prend toute la page.
   var $tail_bas_page       = 20;      // Hauteur du bas de page.
   var $nb_carac_ligne      = 90;     // Pour le détail des travaux;

   /* Constantes pour les régles de calcul d'un arrondi de temps avec définition d'un seuil supplémentaire. */
   var $tranches_seuil   = 0.001;
   var $tranches_arrondi = [0, 0.25, 0.5, 0.75, 1];

   /* ************************************* */
   /* Methodes génériques de mise en forme. */
   /* ************************************* */

   /** Fonction permettant de dessiner une ligne blanche séparatrice. */
   function Separateur() {
      $this->Cell($this->largeur_grande_cell, $this->line_height, '', 0, 0, '');
      $this->SetY($this->GetY() + $this->line_height);
   }

   /** Positionne la couleur de fond en gris clair. */
   function SetFondClair() {
      //$this->SetFillColor(205, 205, 205);
      //$this->SetFillColor(58, 86, 147);
      $this->SetFillColor(100, 122, 157);
   }

   /** Positionne la couleur de fond en gris foncé. */
   function SetFondFonce() {
      //$this->SetFillColor(85, 85, 85);
      $this->SetFillColor(205, 205, 205);
   }

   /**
    * Positionne la fonte pour un label.
    *
    * @param $italic Vrai si c'est en italique, faux sinon.
    */
   function SetFontLabel($italic) {
      if ($italic) {
         $this->SetFont($this->pol_def, 'I', $this->tail_pol_def);
      } else {
         $this->SetFont($this->pol_def, '', $this->tail_pol_def);
      }
   }

   /**
    * Redéfinit une fonte normale.
    *
    * @param $souligne Vrai si le texte sera souligné, faux sinon étant la valeur par défaut.
    */
   function SetFontNormale($souligne = false) {
      if ($souligne) {
         $this->SetFont($this->pol_def, 'U', $this->tail_pol_def);
      } else {
         $this->SetFont($this->pol_def, '', $this->tail_pol_def);
      }
   }

   /**
    * Permet de dessiner une cellule definissant un label d'une cellule ou plusieurs cellules
    * valeurs.
    *
    * @param $italic Vrai si le label est en italique, faux sinon.
    * @param $w Largeur de la cellule contenant le label.
    * @param $label Valeur du label.
    * @param $multH Multiplicateur de la hauteur de la cellule, par défaut vaut 1, par augmenté
    *    donc.
    * @param $align Détermine l'alignement du texte dans la cellule.
    * @param $bordure Détermine les bordures é positionner, par défaut, toutes.
    */
   function CellLabel($italic, $w, $label, $multH = 1, $align = '', $bordure = 1) {
      $this->SetFondClair();
      $this->SetFontLabel($italic);
      $this->Cell($w, $this->line_height * $multH, $label, $bordure, 0, $align, 1);
   }

   /**
    * Permet de dessiner une cellule dite normale.
    *
    * @param $w Largeur de la cellule contenant la valeur.
    * @param $valeur Valeur é afficher.
    * @param $align Détermine l'alignement de la cellule.
    * @param $multH Multiplicateur de la hauteur de la cellule, par défaut vaut 1, par augmenté
    *    donc.
    * @param $bordure Détermine les bordures é positionner, par défaut, toutes.
    * @param $souligne Détermine si le contenu de la cellule est souligné.
    */
   function CellValeur($w, $valeur, $align = '', $multH = 1, $bordure = 1, $souligne = false) {
      $this->SetFontNormale($souligne);
      $this->Cell($w, $this->line_height * $multH, $valeur, $bordure, 0, $align);
   }

   /**
    * Permet de dessinner un cellule vide et grisée foncée.
    *
    * @param $w Largeur de la cellule.
    */
   function CellVideFoncee($w) {
      $this->SetFondFonce();
      $this->Cell($w, $this->line_height, '', 1, 0, '', 1);
   }

   /* **************************************** */
   /* Methodes générant le contenu du rapport. */
   /* **************************************** */

   /**
    * Fonction permettant de dessiner l'entéte du rapport.
    */
   function Header() {
      global $CFG_GLPI;

      /* Constantes pour les largeurs de cellules de l'entéte (doivent étre = $largeur_grande_cell). */
      $largeur_logo  = 50;
      $largeur_titre = 90;
      $largeur_date  = 50;
      /* On fixe les marge. */
      $this->SetX($this->marge_gauche);
      $this->SetY($this->marge_haut);
      // Date du jour.
      $aujour_hui = getdate();

      $plugin_company = new PluginManageentitiesCompany();
      $filepath_logo  = $plugin_company->getLogo($this);
      /* Logo. */
      if ($filepath_logo != NULL && file_exists(GLPI_DOC_DIR . "/" . $filepath_logo)) {
         $this->Image(GLPI_DOC_DIR . "/" . $filepath_logo, 17, 10, 35, 10);
      }

      $this->Cell($largeur_logo, 20, '', 1, 0, 'C');
      /* Titre. */
      $this->SetFont($this->pol_def, 'B', $this->tail_titre);
      $this->Cell($largeur_titre, $this->line_height * 2, Toolbox::decodeFromUtf8(_n('Report', 'Reports', 1)), 'LTR', 0, 'C');
      $this->SetY($this->GetY() + $this->line_height * 2);
      $this->SetX($largeur_logo + 10);
      $this->Cell($largeur_titre, $this->line_height * 2, Toolbox::decodeFromUtf8(__('of this intervention', 'manageentities')), 'LRB', 0, 'C');
      $this->SetY($this->GetY() - $this->line_height * 2);
      $this->SetX($largeur_titre + $largeur_logo + 10);

      $client      = new PluginManageentitiesEntityLogo();
      $client_logo = $client->getLogo($this->entite[0]->fields["id"]);

      if ($client_logo != NULL && file_exists(GLPI_DOC_DIR . "/" . $client_logo)) {
         $size = self::fctaffichimage(GLPI_DOC_DIR . "/" . $client_logo, 40, 18);
         if ($size[1] < 15) {
            $this->Image(GLPI_DOC_DIR . "/" . $client_logo, $largeur_titre + $largeur_logo + 15, $this->line_height * 2, $size[0], $size[1]);
         } elseif ($size[0] > 20) {
            $this->Image(GLPI_DOC_DIR . "/" . $client_logo, $largeur_titre + $largeur_logo + 15, $this->line_height + 1, $size[0], $size[1]);
         } else {
            $this->Image(GLPI_DOC_DIR . "/" . $client_logo, $largeur_titre + $largeur_logo + 25, $this->line_height + 1, $size[0], $size[1]);
         }
         $this->Cell($largeur_logo, 20, '', 1, 0, 'C');
         $this->SetY($this->GetY() + $this->line_height * 4);

         /* Date et heure. */
         $this->CellValeur($this->largeur_grande_cell, Toolbox::decodeFromUtf8(__('Created by', 'manageentities') . ' : ' . $this->GetDateFormatee($aujour_hui) . " " . __('in', 'manageentities') . " " . $this->GetHeureFormatee($aujour_hui)), 'C', 1, 'LTRB', false); // Libellé pour la date.
         $this->SetY($this->GetY() + $this->line_height);
      } else {
         $config = PluginManageentitiesConfig::getInstance();
         if(!$config->fields['disable_date_header']){
            /* Date et heure. */
            $this->CellValeur($largeur_date, Toolbox::decodeFromUtf8(__('Created by', 'manageentities')) . ' :', 'C', 1, 'LTR', true); // Libellé pour la date.
            $this->SetY($this->GetY() + $this->line_height);
            $this->SetX($largeur_titre + $largeur_logo + 10);
            $this->CellValeur($largeur_date, $this->GetDateFormatee($aujour_hui), 'C', 1, 'LR'); // Date.
            $this->SetY($this->GetY() + $this->line_height);
            $this->SetX($largeur_titre + $largeur_logo + 10);
            $this->CellValeur($largeur_date, Toolbox::decodeFromUtf8(__('in', 'manageentities')) . ' :', 'C', 1, 'LR', true); // Libellé pour l'heure.
            $this->SetY($this->GetY() + $this->line_height);
            $this->SetX($largeur_titre + $largeur_logo + 10);
            $this->CellValeur($largeur_date, $this->GetHeureFormatee($aujour_hui), 'C', 1, 'LRB'); // Heure.
            $this->SetY($this->GetY() + $this->line_height);
         } else{
            /* Empty */
            $this->CellValeur($largeur_date, "", 'C', 1, 'LTR', true); // Libellé pour la date.
            $this->SetY($this->GetY() + $this->line_height);
            $this->SetX($largeur_titre + $largeur_logo + 10);
            $this->CellValeur($largeur_date, "", 'C', 1, 'LR'); // Date.
            $this->SetY($this->GetY() + $this->line_height);
            $this->SetX($largeur_titre + $largeur_logo + 10);
            $this->CellValeur($largeur_date, "", 'C', 1, 'LR', true); // Libellé pour l'heure.
            $this->SetY($this->GetY() + $this->line_height);
            $this->SetX($largeur_titre + $largeur_logo + 10);
            $this->CellValeur($largeur_date, "", 'C', 1, 'LRB'); // Heure.
            $this->SetY($this->GetY() + $this->line_height);
         }
      }


      /* Identifiant de rapport. */
      $this->Cell($this->largeur_grande_cell, $this->line_height, Toolbox::decodeFromUtf8("N°") . $this->GetNoCri($aujour_hui), 1, 0, 'C');
      $this->SetY($this->GetY() + $this->line_height);
   }

   /**
    * Fonction permettant de dessiner le tableau des informations générales.
    */
   function InfosGenerales() {
      /* Num de demande de support associé. */
      $this->SetTextColor(255, 255, 255);
      $this->SetFontNormale(false); // Repositionnement de la fonte normale.
      $this->CellLabel(false, $this->largeur_grande_cell / 2, Toolbox::decodeFromUtf8(__('Request number of associated help', 'manageentities')));
      $this->SetTextColor(0, 0, 0);
      $this->CellValeur($this->largeur_grande_cell / 2, $this->demande_associee);
      $this->SetTextColor(255, 255, 255);
      $this->SetY($this->GetY() + $this->line_height);

      /* Intervenant. */
      $this->SetY($this->GetY() + $this->line_height);

      $intervenants = explode(',', $this->intervenant);
      if (sizeof($intervenants) > 1) {
         $plural = 2;
      } else {
         $plural = 1;
      }

      $this->CellLabel(false, $this->largeur_grande_cell, Toolbox::decodeFromUtf8(_n('Technician', 'Technicians', $plural, 'manageentities')), 1, 'C');
      $this->SetY($this->GetY() + $this->line_height);
      $this->SetTextColor(0, 0, 0);
      $this->SetFontNormale(false);
      foreach ($intervenants as $une_ligne) {
         $this->TestBasDePageDetailTravaux($une_ligne);

         $this->MultiCell($this->largeur_grande_cell, $this->line_height, $une_ligne, 'LR');
      }
      $this->Cell($this->largeur_grande_cell, 0, '', 'LRB'); // Ligne de fin de cellule pour mettre la bordure du bas.
      $this->SetY($this->GetY() + $this->line_height);
      $this->SetTextColor(255, 255, 255);

      /* Date d'intervention. */
      $this->CellLabel(false, 40, Toolbox::decodeFromUtf8(__('Intervention date', 'manageentities')), 2);

      /* Année et mois... */
      $this->CellLabel(true, 20, Toolbox::decodeFromUtf8(__('Year', 'manageentities')));
      $this->SetTextColor(0, 0, 0);
      $this->CellValeur(35, Toolbox::decodeFromUtf8($this->date_intervention[0]["year"]));
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(true, 20, Toolbox::decodeFromUtf8(__('month')));
      $monthsarray = Toolbox::getMonthsOfYearArray();
      $this->SetTextColor(0, 0, 0);
      $this->CellValeur(35, Toolbox::decodeFromUtf8($monthsarray[$this->date_intervention[0]["mon"]]));
      $this->CellVideFoncee(40);
      $this->Ln();

      /* Du, Au... */
      $this->SetX($this->GetX() + 40);
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(true, 20, Toolbox::decodeFromUtf8(__('From', 'manageentities')));
      $this->SetTextColor(0, 0, 0);
      $this->CellValeur(35, $this->date_intervention[1]);
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(true, 20, Toolbox::decodeFromUtf8(__('To', 'manageentities')));
      $this->SetTextColor(0, 0, 0);
      $this->CellValeur(35, $this->date_intervention[2]);
      $this->CellVideFoncee(40);
      $this->SetY($this->GetY() + $this->line_height);
   }

   /**
    * Fonction permettant de dessiner le tableau des informations de l'entité
    * concernée par le rapport.
    */
   function InfosEntite() {
      global $DB;

      if (!isset($this->entite[0]->fields["id"])) $this->entite[0]->fields["id"] = 0;
      if (!isset($this->entite[0]->fields["name"])) $this->entite[0]->fields["name"] = __('Root entity');
      $query = "SELECT *
        FROM `glpi_plugin_manageentities_contacts`
        WHERE `entities_id` = " . $this->entite[0]->fields["id"] . "
        AND `is_default` = 1";

      $result = $DB->query($query);
      while ($data = $DB->fetchArray($result)) {
         $contact = new contact;
         $contact->GetfromDb($data["contacts_id"]);
         $manager = $contact->fields["firstname"] . " " . $contact->fields["name"];
      }

      /* Nom de l'entité. */
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(false, 40, Toolbox::decodeFromUtf8(__('Society name', 'manageentities')));
      $this->SetTextColor(0, 0, 0);
      $this->CellValeur(150, Toolbox::decodeFromUtf8($this->entite[0]->fields["name"]));
      $this->SetY($this->GetY() + $this->line_height);
      /* Ville. */
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(false, 40, Toolbox::decodeFromUtf8(__('City')));
      $this->SetTextColor(0, 0, 0);
      if (!isset($this->entite[0]->fields["town"]))
         $this->entite[0]->fields["town"] = "";
      $this->CellValeur(150, Toolbox::decodeFromUtf8($this->entite[0]->fields["town"]));
      $this->SetY($this->GetY() + $this->line_height);
      /* Responsable. */
      if (!isset($manager))
         $manager = "";
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(false, 40, Toolbox::decodeFromUtf8(__('Person in charge', 'manageentities')));
      $this->SetTextColor(0, 0, 0);
      $this->CellValeur(150, Toolbox::decodeFromUtf8($manager));
      $this->SetY($this->GetY() + $this->line_height);
   }

   /**
    * Fonction permettant de dessiner un cellule particuliére avec un symbole.
    * La cellule est en réalité composée de 2 cellules
    *
    * @param $cochee Vrai pour un symbol carré noir, faux pour un carré blanc.
    * @param $w Largeur totale.
    * @param $label Contenu de la seconde sous cellule.
    */
   function CellContrat($cochee, $w, $label) {

      $largeur_symbol = 2.5;

      $this->SetFondClair();
      $this->SetFontLabel(true);
      if ($cochee) {
         $this->SetFont('Zapfdingbats', '', 6);
         $this->Cell($largeur_symbol, $this->line_height, chr(110), 'LTB', 0, '', 1);
      } else {
         $this->SetFont('Zapfdingbats', '', 6);
         $this->Cell($largeur_symbol, $this->line_height, chr(111), 'LTB', 0, '', 1);
      }
      $this->SetFontLabel(true);
      $this->Cell($w - $largeur_symbol, $this->line_height, $label, 'TRB', 0, '', 1);
   }

   /**
    * Fonction permettant de dessiner les informations du contrat de l'entité
    * concernée par le rapport.
    */
   function InfosContrats() {

      $this->SetTextColor(255, 255, 255);
      /* Type de contrat. */
      $this->CellLabel(false, 40, Toolbox::decodeFromUtf8(_n('Contract type', 'Contract types', 1)), 2);
      /* Sous contrat. */

      $this->CellContrat($this->sous_contrat, 50, Toolbox::decodeFromUtf8(__('Help on contract', 'manageentities')));

      if ($this->sous_contrat) {
         $this->CellLabel(true, 35, Toolbox::decodeFromUtf8(__('Contract number', 'manageentities')));
         $this->SetTextColor(0, 0, 0);
         $this->CellValeur(65, $this->entite[1]);
      } else {
         $this->CellVideFoncee(100);
         $this->SetTextColor(0, 0, 0);
      }
      $this->Ln();
      /* Hors contrat. */
      $this->SetX($this->GetX() + 40);
      $this->SetTextColor(255, 255, 255);
      $this->CellContrat(!$this->sous_contrat, 50, Toolbox::decodeFromUtf8(__('Out of contract', 'manageentities')));
      $this->CellVideFoncee(100);
      $this->SetTextColor(0, 0, 0);
      $this->SetY($this->GetY() + $this->line_height);
   }

   /** Fonction permettant de dessiner l'entéte du tableau des temps passés. */
   function TempsPassesEntete() {
      $config = PluginManageentitiesConfig::getInstance();
      /* Entéte du tableau des temps passés. */
      $width = 0;
      if ($this->forfait) $width = 15;
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(false, $this->largeur_grande_cell, Toolbox::decodeFromUtf8(__('Crossed time (itinerary including)', 'manageentities')), 1, 'C');
      $this->SetTextColor(0, 0, 0);
      $this->Ln();
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(true, 95, Toolbox::decodeFromUtf8(__('Wording of the activities', 'manageentities')), 2, 'C');

      $this->CellLabel(true, 20 + $width, Toolbox::decodeFromUtf8(__('Date of', 'manageentities')), 1, 'C', 'LTR');
      if (!$this->forfait) $this->CellLabel(true, 15, Toolbox::decodeFromUtf8(__('Hour of', 'manageentities')), 1, 'C', 'LTR');
      $this->CellLabel(true, 20 + $width, Toolbox::decodeFromUtf8(__('Date of', 'manageentities')), 1, 'C', 'LTR');
      if (!$this->forfait) $this->CellLabel(true, 15, Toolbox::decodeFromUtf8(__('Hour of', 'manageentities')), 1, 'C', 'LTR');
      if ($this->intervention) {
         $this->CellLabel(true, 25, Toolbox::decodeFromUtf8(_x('Quantity', 'Number')), 1, 'C', 'LTR');
      } else {
         $this->CellLabel(true, 25, Toolbox::decodeFromUtf8(__('Crossed time', 'manageentities')), 1, 'C', 'LTR');
      }
      $this->SetTextColor(0, 0, 0);

      $this->Ln();
      $this->SetX($this->GetX() + 95);
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(true, 20 + $width, Toolbox::decodeFromUtf8(__('Begin')), 1, 'C', 'LBR');
      if (!$this->forfait) $this->CellLabel(true, 15, Toolbox::decodeFromUtf8(__('Begin')), 1, 'C', 'LBR');
      $this->CellLabel(true, 20 + $width, Toolbox::decodeFromUtf8(__('End')), 1, 'C', 'LBR');
      if (!$this->forfait) $this->CellLabel(true, 15, Toolbox::decodeFromUtf8(__('End')), 1, 'C', 'LBR');
      if ($this->intervention) {
         $this->CellLabel(true, 25, Toolbox::decodeFromUtf8(__('of this intervention', 'manageentities')), 1, 'C', 'LBR');
      } else {
         if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
            $this->CellLabel(true, 25, Toolbox::decodeFromUtf8(__('(in days)', 'manageentities')), 1, 'C', 'LBR');
         } else {
            $this->CellLabel(true, 25, Toolbox::decodeFromUtf8(__('(in hours)', 'manageentities')), 1, 'C', 'LBR');
         }
      }
      $this->SetTextColor(0, 0, 0);
      $this->Ln();
   }

   /** Fonction permettant de dessiner la zone des temps passés. */
   function TempsPasses() {
      $config = PluginManageentitiesConfig::getInstance();

      $_SESSION["glpi_plugin_manageentities_total"] = 0;
      // Entéte du tableau des temps passés.
      $this->TempsPassesEntete();
      /* Les lignes des temps passés. */
      $total_tps = 0;
      for ($l = 0; $l < count($this->temps_passes); $l++) {
         $this->TestBasDePageTpsPasses(); // Test pour un éventuel saut de page.
         if ($config->fields['useprice'] == PluginManageentitiesConfig::NOPRICE) {
            $this->CellValeur(95, Toolbox::decodeFromUtf8($this->libelle_activite[$l]));
         } elseif ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
            $this->CellValeur(95, Toolbox::decodeFromUtf8($this->libelle_activite[$l]));
         } else {
            $this->CellValeur(95, $this->libelle_activite);
         }

         $width = 0;
         if ($this->forfait) $width = 15;
         $this->CellValeur(20 + $width, $this->temps_passes[$l][0], 'C');
         if (!$this->forfait) $this->CellValeur(15, $this->temps_passes[$l][1], 'C');
         $this->CellValeur(20 + $width, $this->temps_passes[$l][2], 'C');
         if (!$this->forfait) $this->CellValeur(15, $this->temps_passes[$l][3], 'C');
         $this->CellValeur(25, $this->TotalTpsPassesArrondis($this->temps_passes[$l][4]), 'C');
         $total_tps += $this->temps_passes[$l][4];
         $this->Ln();
      }

      if ($this->deplacement) {
         /* Déplacement. */
         $this->Separateur();
         $this->Cell(115, $this->line_height, '', 0, 0, '');
         $this->SetTextColor(255, 255, 255);
         if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
            $this->CellLabel(true, 40, Toolbox::decodeFromUtf8(__('Travel (in days)', 'manageentities')));
         } else {
            $this->CellLabel(true, 40, Toolbox::decodeFromUtf8(__('Travel', 'manageentities')));
         }
         $this->SetTextColor(0, 0, 0);
         $this->CellValeur(35, $this->nombredeplacement, 'C');
      }

      //      $this->Separateur();

      /* Le total. */
      $this->Separateur();
      $this->Cell(115, $this->line_height, '', 0, 0, '');
      $this->SetTextColor(255, 255, 255);
      if ($this->intervention) {
         $this->CellLabel(true, 40, Toolbox::decodeFromUtf8(__('Total')));
      } else {
         if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY) {
            $this->CellLabel(true, 40, Toolbox::decodeFromUtf8(__('Total (in days)', 'manageentities')));
         } else {
            $this->CellLabel(true, 40, Toolbox::decodeFromUtf8(__('Total (in hours)', 'manageentities')));
         }
      }
      $this->SetTextColor(0, 0, 0);
      $this->CellValeur(35, $this->TotalTpsPassesArrondis($total_tps + $this->nombredeplacement), 'C');
      $this->Separateur();

      $_SESSION["glpi_plugin_manageentities_total"] = ($total_tps + $this->nombredeplacement);
   }

   /**
    * Permet d'arrondir le total des temps passés avec les tranches définies en constantes.
    * Peut étre améliorée afin de boucler (while) sur les tranches pour ne pas avoir une suite de
    * if, else if.
    *
    * @param Total é arrondir.
    *
    * @return Le total arrondi selon la régle de gestion.
    */
   function TotalTpsPassesArrondis($a_arrondir) {

      $result = 0;

      $partie_entiere = floor($a_arrondir);
      $reste          = $a_arrondir - $partie_entiere + 10; // Le + 10 permet de pallier é un probléme de comparaison (??) par la suite.
      /* Initialisation des tranches majorées du seuil supplémentaire. */
      $tranches_majorees = [];
      for ($i = 0; $i < count($this->tranches_arrondi); $i++) {
         // Le + 10 qui suit permet de pallier é un probléme de comparaison (??) par la suite.
         $tranches_majorees[] = $this->tranches_arrondi[$i] + $this->tranches_seuil + 10;
      }
      if ($reste < $tranches_majorees[0]) {
         $result = $partie_entiere;

      } else if ($reste >= $tranches_majorees[0] && $reste < $tranches_majorees[1]) {
         $result = $partie_entiere + $this->tranches_arrondi[1];

      } else if ($reste >= $tranches_majorees[1] && $reste < $tranches_majorees[2]) {
         $result = $partie_entiere + $this->tranches_arrondi[2];

      } else if ($reste >= $tranches_majorees[2] && $reste < $tranches_majorees[3]) {
         $result = $partie_entiere + $this->tranches_arrondi[3];

      } else {
         $result = $partie_entiere + $this->tranches_arrondi[4];
      }

      return $result;
   }

   /** Fonction permettant de gérer un saut de page pour la zone des temps passés. */
   function TestBasDePageTpsPasses() {

      if ($this->GetSeuilSaut() < $this->line_height) {
         $this->AddPage();
         $this->SetY($this->GetY() + $this->line_height);
         // On redessine l'entéte.
         $this->TempsPassesEntete();
      }
   }

   /** Fonction permettant de dessiner l'entéte du tableau du détail des travaux réalisés. */
   function DetailTravauxEntete() {
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(false, $this->largeur_grande_cell, Toolbox::decodeFromUtf8(__('Detail of work done', 'manageentities')), 1, 'C');
      $this->SetY($this->GetY() + $this->line_height);
      $this->SetFontNormale(false); // Repositionnement de la fonte normale.
      $this->SetTextColor(0, 0, 0);
   }

   /**
    * Fonction permettant de dessiner la zone détail des travaux réalisés préalablement remplie.
    *
    * @param $description Texte é afficher dans la zone de détail.
    */
   function DetailTravaux() {

      // Entéte du tableau des temps passés.
      $this->DetailTravauxEntete();

      $decoupage1 = [];
      $tok        = strtok($this->description_cri, "\n");
      while ($tok !== false) {
         $decoupage1[] = $tok;
         $tok          = strtok("\n");
      }
      $this->description_cri = $decoupage1;
      foreach ($this->description_cri as $une_ligne) {
         $this->TestBasDePageDetailTravaux($une_ligne);
         $this->MultiCell($this->largeur_grande_cell, $this->line_height, $une_ligne, 'LR');
      }
      $this->Cell($this->largeur_grande_cell, 0, '', 'LRB'); // Ligne de fin de cellule pour mettre la bordure du bas.
   }

   /**
    * Fonction permettant de gérer un saut de page pour la zone du détail des travaux réalisés.
    *
    * @param $une_ligne Ligne é tester.
    */
   function TestBasDePageDetailTravaux($une_ligne) {

      $nb_lg_necessaires = 1;
      if (strlen($une_ligne) > $this->nb_carac_ligne) {
         $nb_lg_necessaires = round(strlen($une_ligne) / $this->nb_carac_ligne);
      }
      if (($nb_lg_necessaires * $this->line_height) > $this->GetSeuilSaut()) {
         $this->Cell($this->largeur_grande_cell, 0, '', 'LRB'); // Ligne de fin de cellule pour mettre la bordure du bas.
         $this->AddPage();
         $this->SetY($this->GetY() + $this->line_height);
         // On redessine l'entéte.
         $this->DetailTravauxEntete();
      }
   }

   /** Fonction permettant de dessiner la zone pour les observations du client. */
   function Observations() {
      $tail_zone    = 30;
      $ligne_points = '...........................................';

      // On teste s'il reste de la place.
      $this->TestBasDePageGenerique($tail_zone);
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(false, $this->largeur_grande_cell, Toolbox::decodeFromUtf8(__('Customer comments', 'manageentities')), 1, 'C');
      $this->SetTextColor(0, 0, 0);
      $this->SetY($this->GetY() + $this->line_height);
      $this->CellValeur($this->largeur_grande_cell, '', 'C', 1, 'LTR');
      $this->Ln();
      $this->CellValeur($this->largeur_grande_cell, $ligne_points . $ligne_points . $ligne_points . $ligne_points, 'C', 0.5, 'LR');
      $this->Ln();
      $this->CellValeur($this->largeur_grande_cell, '', 'C', 1.5, 'LR');
      $this->Ln();
      $this->CellValeur($this->largeur_grande_cell, $ligne_points . $ligne_points . $ligne_points . $ligne_points, 'C', 0.5, 'LR');
      $this->Ln();
      $this->CellValeur($this->largeur_grande_cell, '', 'C', 1.5, 'LR');
      $this->Ln();
      $this->CellValeur($this->largeur_grande_cell, $ligne_points . $ligne_points . $ligne_points . $ligne_points, 'C', 0.5, 'LR');
      $this->Ln();
      $this->CellValeur($this->largeur_grande_cell, '', 'C', 0.5, 'LBR');
      $this->SetTextColor(0, 0, 0);
      $this->Ln();
   }

   /** Fonction permettant de dessiner l'entéte du tableau des commentaires de la société. */
   function DetailCommentaires() {
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(false, $this->largeur_grande_cell, Toolbox::decodeFromUtf8(__('Comments')), 1, 'C');
      $this->SetY($this->GetY() + $this->line_height);
      $this->SetFontNormale(false); // Repositionnement de la fonte normale.
      $this->SetTextColor(0, 0, 0);
   }

   /**
    * Fonction permettant de dessiner la zone détail des commentaires de la société préalablement
    * remplie.
    *
    * @param $description Texte é afficher dans la zone de détail.
    */
   function Commentaires() {

      $plugin_company = new PluginManageentitiesCompany();
      $comment        = $plugin_company->getComment($this);

      // Entéte du tableau des temps passés.
      $this->DetailCommentaires();

      $decoupage1 = [];
      $tok        = strtok($comment, "\n");
      while ($tok !== false) {
         $decoupage1[] = $tok;
         $tok          = strtok("\n");
      }
      $comment = $decoupage1;
      foreach ($comment as $une_ligne) {
         $this->TestBasDePageCommentaires(Toolbox::decodeFromUtf8($une_ligne));
         $this->MultiCell($this->largeur_grande_cell, $this->line_height, Toolbox::decodeFromUtf8($une_ligne), 'LR');
      }
      $this->Cell($this->largeur_grande_cell, 0, '', 'LRB'); // Ligne de fin de cellule pour mettre la bordure du bas.
   }

   /**
    * Fonction permettant de gérer un saut de page pour la zone du détail des travaux réalisés.
    *
    * @param $une_ligne Ligne é tester.
    */
   function TestBasDePageCommentaires($une_ligne) {

      $nb_lg_necessaires = 1;
      if (strlen($une_ligne) > $this->nb_carac_ligne) {
         $nb_lg_necessaires = round(strlen($une_ligne) / $this->nb_carac_ligne);
      }
      if (($nb_lg_necessaires * $this->line_height) > $this->GetSeuilSaut()) {
         $this->Cell($this->largeur_grande_cell, 0, '', 'LRB'); // Ligne de fin de cellule pour mettre la bordure du bas.
         $this->AddPage();
         $this->SetY($this->GetY() + $this->line_height);
         // On redessine l'entéte.
         $this->DetailCommentaires();
      }
   }

   /** Fonction permettant de dessiner la zone du cachet et du visa du client. */
   function CachetClient() {
      $tail_zone = 32.5;

      // On teste s'il reste de la place.
      $this->TestBasDePageGenerique($tail_zone);
      $this->SetTextColor(255, 255, 255);
      $this->CellLabel(false, $this->largeur_grande_cell / 2, Toolbox::decodeFromUtf8(__('Customer stamp', 'manageentities')), 1, 'C');
      $this->CellLabel(false, $this->largeur_grande_cell / 2, Toolbox::decodeFromUtf8(__('Customer Visa', 'manageentities')), 1, 'C');
      $this->SetY($this->GetY() + $this->line_height);
      $this->CellValeur($this->largeur_grande_cell / 2, '', '', 5.5); // Cachet client.
      $this->CellValeur($this->largeur_grande_cell / 2, '', '', 5.5); // Visa client.
      $this->SetTextColor(0, 0, 0);
      $this->Ln();
   }

   /**
    * Test s'il reste de la place pour une zone non divisible en bas de page.
    *
    * @param $tail_zone Taille de la zone pour laquelle on souhaite savoir s'il reste de la place.
    */
   function TestBasDePageGenerique($tail_zone) {

      if ($this->GetSeuilSaut() < $tail_zone) {
         $this->AddPage();
         $this->SetY($this->GetY() + $this->line_height);
      }
   }

   /**
    * Fonction permettant de dessiner le pied de page du rapport.
    */
   function Footer() {
      // Positionnement par rapport au bas de la page.
      $this->SetY(-$this->tail_bas_page);
      /* Numéro de page. */
      $this->SetFont($this->pol_def, '', 9);
      $this->Cell(
         0, $this->tail_bas_page / 2, Toolbox::decodeFromUtf8(__('Page', 'manageentities')) . ' ' . $this->PageNo() . ' ' . Toolbox::decodeFromUtf8(__('on', 'manageentities')) . ' {nb}', 0, 0, 'C');
      $this->Ln(10);
      /* Infos company. */
      $this->SetFont($this->pol_def, 'I', 9);

      $plugin_company = new PluginManageentitiesCompany();
      $address        = $plugin_company->getAddress($this);


      if (isset($address)) {
         $strAddress = nl2br($address);
         $listLines  = explode("<br />", $strAddress);
         if (sizeof($listLines) > 1) {
            foreach ($listLines as $line) {
               $this->Cell(0, $this->tail_bas_page / 4, Toolbox::decodeFromUtf8($line), 0, 0, 'C');
               $this->Ln();
            }
         } else {
            $this->Cell(0, $this->tail_bas_page / 4, Toolbox::decodeFromUtf8($strAddress), 0, 0, 'C');
         }
      } else {
         $this->Cell(0, $this->tail_bas_page / 4, "", 0, 0, 'C');
      }

      $this->Ln(5);
   }

   /** Fonction permettant de dessiner le rapport partie par partie. */
   function DrawCri() {

      $this->AliasNbPages(); // Pour initialiser le nombre de page.
      $this->AddPage(); // La premiére page.

      $this->InfosGenerales();
      $this->Separateur();
      $this->InfosEntite();
      $this->Separateur();
      $this->InfosContrats();
      $this->Separateur();
      $this->TempsPasses();
      $this->Separateur();
      $this->DetailTravaux();
      $this->Separateur();
      $config = new PluginManageentitiesConfig();
      if ($config->isCommentCri()) {
         $this->Commentaires();
         $this->Separateur();
      }
      $this->Observations();
      $this->Separateur();
      $this->CachetClient();
   }

   /* **************** */
   /* Autres méthodes. */
   /* **************** */

   /**
    * Retourne une date donnée formatée dd/mm/yyyy.
    *
    * @param $une_date Date é formater.
    *
    * @return La date donnée au format dd/mm/yyyy.
    */
   function GetDateFormatee($une_date) {

      return $this->CompleterAvec0($une_date['mday'], 2) . "/" . $this->CompleterAvec0($une_date['mon'], 2) . "/" . $une_date['year'];
   }

   /**
    * Retourne une heure donnée au format hh:mm.
    *
    * @param $une_date Date é formater.
    *
    * @return L'heure donnée au format hh:mm.
    */
   function GetHeureFormatee($une_date) {

      return $this->CompleterAvec0($une_date['hours'], 2) . ":" . $this->CompleterAvec0($une_date['minutes'], 2);
   }

   /**
    * Génération auto du né du CRI é l'aide d'une date donnée et ne le fait qu'une fois.
    *
    * @param $une_date Date servant é la génération du né du CRI.
    *
    * @return Le né de CRI généré.
    */
   function GetNoCri($une_date = "") {

      if ($this->no_cri == "" && $une_date != "") {
         $this->no_cri = substr($une_date['year'], 2) . $this->CompleterAvec0($une_date['mon'], 2)
                         . $this->CompleterAvec0($une_date['mday'], 2) . "-" . $this->CompleterAvec0($une_date['hours'], 2)
                         . $this->CompleterAvec0($une_date['minutes'], 2) . $this->CompleterAvec0($une_date['seconds'], 2);
      }
      return $this->no_cri;
   }

   /**
    * Compléte une chaéne donnée avec des '0' suivant la longueur donnée et voulue de la chaéne.
    *
    * @param $une_chaine Chaéne é compléter.
    * @param $lg Longueur finale souhaitée de la chaéne donnée.
    *
    * @return La chaéne complétée.
    */
   function CompleterAvec0($une_chaine, $lg) {

      while (strlen($une_chaine) != $lg) {
         $une_chaine = "0" . $une_chaine;
      }

      return $une_chaine;
   }

   /* ********************* */
   /* Getteurs et setteurs. */
   /* ********************* */

   function SetSousContrat($sous_contrat) {
      $this->sous_contrat = $sous_contrat;
   }

   function SetDeplacement($deplacement) {
      $this->deplacement = $deplacement;
   }

   function SetNombreDeplacement($nombredeplacement) {
      $this->nombredeplacement = $nombredeplacement;
   }

   function SetLibelleActivite($libelle_activite) {

      $config = PluginManageentitiesConfig::getInstance();

      if (is_array($libelle_activite)) {

         $this->libelle_activite = $libelle_activite;

      } else if ($config->fields['hourorday'] == PluginManageentitiesConfig::DAY && is_integer($libelle_activite)) {

         $this->libelle_activite = Toolbox::decodeFromUtf8(Dropdown::getDropdownName("glpi_plugin_manageentities_critypes",
                                                                                     $libelle_activite));
      } else {
         $this->libelle_activite = Toolbox::decodeFromUtf8($libelle_activite);
      }
   }

   function SetDescriptionCri($description_cri) {
      $this->description_cri = $description_cri;
      $this->description_cri = Html::clean($this->description_cri);
      $this->description_cri = stripcslashes($this->description_cri);
      $this->description_cri = htmlspecialchars_decode($this->description_cri);
      $this->description_cri = str_replace("\\\\", "\\", $this->description_cri);
      $this->description_cri = str_replace("\\'", "'", $this->description_cri);
      $this->description_cri = str_replace("<br>", " ", $this->description_cri);
      $this->description_cri = Toolbox::decodeFromUtf8($this->description_cri);
   }

   function SetIntervenant($intervenant) {
      $this->intervenant = Toolbox::decodeFromUtf8($intervenant);
   }

   function SetDemandeAssociee($demande_associee) {
      $this->demande_associee = $demande_associee;
   }

   function SetDateIntervention($date_intervention) {
      // Les dates sont recues de la base directement et de la forme yyyy-mm-dd hh:mm.
      /* Année et mois de l'intervention. */
      $this->date_intervention[0] = getdate(mktime(0, 0, 0,
                                                   substr($date_intervention[0], 5, 2),
                                                   substr($date_intervention[0], 8, 2),
                                                   substr($date_intervention[0], 0, 4)));
      /* Du et Au. */
      $this->date_intervention[1] = substr($date_intervention[1], 8, 2) . "/" . substr($date_intervention[1], 5, 2) . "/"
                                    . substr($date_intervention[1], 0, 4);
      $this->date_intervention[2] = substr($date_intervention[2], 8, 2) . "/" . substr($date_intervention[2], 5, 2) . "/"
                                    . substr($date_intervention[2], 0, 4);
   }

   function SetEntite($entite) {
      $this->entite = $entite;
   }

   function SetTempsPasses($temps_passes) {
      $this->temps_passes = $temps_passes;
   }

   function GetSeuilSaut() {
      return (297 - $this->GetY() - $this->tail_bas_page);
   }

   function setForfait() {
      $this->forfait = true;
   }

   function setIntervention() {
      $this->intervention = true;
   }

   // ---------------------------------------------------
   // Fonction de redimensionnement A L'AFFICHAGE
   // ---------------------------------------------------
   // La FONCTION : fctaffichimage($img_Src, $W_max, $H_max)
   // Les paramètres :
   // - $img_Src : URL (chemin + NOM) de l'image Source
   // - $W_max : LARGEUR maxi finale ----> ou 0 : largeur libre
   // - $H_max : HAUTEUR maxi finale ----> ou 0 : hauteur libre
   // ---------------------
   // return [width, height]

   // ---------------------------------------------------
   function fctaffichimage($img_Src, $W_max, $H_max) {

      if (file_exists($img_Src)) {
         // ---------------------
         // Lit les dimensions de l'image source
         $img_size = getimagesize($img_Src);
         $W_Src    = $img_size[0]; // largeur source
         $H_Src    = $img_size[1]; // hauteur source
         // ---------------------
         if (!$W_max) {
            $W_max = 0;
         }
         if (!$H_max) {
            $H_max = 0;
         }
         // ---------------------
         // Teste les dimensions tenant dans la zone
         $W_test = round($W_Src * ($H_max / $H_Src));
         $H_test = round($H_Src * ($W_max / $W_Src));
         // ---------------------
         // si l'image est plus petite que la zone
         if ($W_Src < $W_max && $H_Src < $H_max) {
            $W = $W_Src;
            $H = $H_Src;
            // sinon si $W_max et $H_max non definis
         } elseif ($W_max == 0 && $H_max == 0) {
            $W = $W_Src;
            $H = $H_Src;
            // sinon si $W_max libre
         } elseif ($W_max == 0) {
            $W = $W_test;
            $H = $H_max;
            // sinon si $H_max libre
         } elseif ($H_max == 0) {
            $W = $W_max;
            $H = $H_test;
            // sinon les dimensions qui tiennent dans la zone
         } elseif ($H_test > $H_max) {
            $W = $W_test;
            $H = $H_max;
         } else {
            $W = $W_max;
            $H = $H_test;
         }
         // ---------------------
      } else { // si le fichier image n existe pas
         $W = 0;
         $H = 0;
      }
      return [$W, $H];
   }

}
