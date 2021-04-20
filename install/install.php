<?php
/**
 * ---------------------------------------------------------------------Civikmind
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2021 Teclib' and contributors.
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

define('GLPI_ROOT', realpath('..'));

include_once (GLPI_ROOT . "/inc/based_config.php");
include_once (GLPI_ROOT . "/inc/db.function.php");

$GLPI = new GLPI();
$GLPI->initLogger();
$GLPI->initErrorHandler();

Config::detectRootDoc();

//Print a correct  Html header for application
function header_html($etape) {
   global $CFG_GLPI;

   // Send UTF8 Headers
   header("Content-Type: text/html; charset=UTF-8");

   echo "<!DOCTYPE html'>";
   echo "<html lang='es'>";
    echo "<head>";
    echo "<meta charset='utf-8'>";
   echo "<meta http-equiv='Content-Script-Type' content='text/javascript'> ";
    echo "<meta http-equiv='Content-Style-Type' content='text/css'> ";
   echo "<title>Configuración de Civikmind</title>";

   // CFG
   echo Html::getCoreVariablesForJavascript();

    // LIBS
   echo Html::script("public/lib/base.js");
   echo Html::script("public/lib/fuzzy.js");
   echo Html::script("js/common.js");

    // CSS
   echo Html::css('public/lib/base.css');
   echo Html::css("css/style_install.css");
   echo "</head>";
   echo "<body>";
   echo "<div id='principal'>";
   echo "<div id='bloc'>";
   echo "<div id='logo_bloc'></div>";
   echo "<h2>Configuración de Civikmind</h2>";
   echo "<br><h3>". $etape ."</h3>";
}


//Display a great footer.
function footer_html() {
   echo "</div></div></body></html>";
}


// choose language
function choose_language() {
   global $CFG_GLPI;

   echo "<form action='install.php' method='post'>";
   echo "<p class='center'>";

   // fix missing param for js drodpown
   $CFG_GLPI['ajax_limit_count'] = 15;

   Dropdown::showLanguages("language", ['value' => $_SESSION['glpilanguage']]);
   echo "</p>";
   echo "";
   echo "<p class='submit'><input type='hidden' name='install' value='lang_select'>";
   echo "<input type='submit' name='submit' class='submit' value='OK'></p>";
   Html::closeForm();
}


function acceptLicense() {

   echo "<div class='center'>";
   echo "<textarea id='license' cols='85' rows='10' readonly='readonly'>";
   readfile("../COPYING.txt");
   echo "</textarea>";

   echo "<br><a target='_blank' href='http://www.gnu.org/licenses/old-licenses/gpl-2.0-translations.html'>".
         __('También están disponibles traducciones no oficiales')."</a>";

   echo "<form action='install.php' method='post'>";
   echo "<p id='license'>";

   echo "<label for='agree' class='radio'>";
   echo "<input type='radio' name='install' id='agree' value='License'>";
   echo "<span class='outer'><span class='inner'></span></span>";
   echo __('He leído y ACEPTO los términos de la licencia escritos arriba.');
   echo " </label>";

   echo "<label for='disagree' class='radio'>";
   echo "<input type='radio' name='install' value='lang_select' id='disagree' checked='checked'>";
   echo "<span class='outer'><span class='inner'></span></span>";
   echo __('He leído y NO ACEPTO los términos de la licencia escritos arriba');
   echo " </label>";

   echo "<p><input type='submit' name='submit' class='submit' value=\"".__s('Continuar')."\"></p>";
   Html::closeForm();
   echo "</div>";
}


//confirm install form
function step0() {

   echo "<h3>".__('Instalación o actualización de Civikmind')."</h3>";
   echo "<p>".__s("Elija 'Instalar' para una instalación completamente nueva de Civikmind.")."</p>";
   echo "<p> ".__s("Seleccione 'Actualizar' para actualizar su versión de Civikmind desde una versión anterior")."</p>";
   echo "<form action='install.php' method='post'>";
   echo "<input type='hidden' name='update' value='no'>";
   echo "<p class='submit'><input type='hidden' name='install' value='Etape_0'>";
   echo "<input type='submit' name='submit' class='submit' value=\""._sx('button', 'Instalar')."\"></p>";
   Html::closeForm();

   echo "<form action='install.php' method='post'>";
   echo "<input type='hidden' name='update' value='yes'>";
   echo "<p class='submit'><input type='hidden' name='install' value='Etape_0'>";
   echo "<input type='submit' name='submit' class='submit' value=\""._sx('button', 'Actualizar')."\"></p>";
   Html::closeForm();
}


//Step 1 checking some compatibility issue and some write tests.
function step1($update) {
   echo "<h3>".__s('Comprobación de la compatibilidad de su entorno con la ejecución de Civikmind').
        "</h3>";
   echo "<table class='tab_check'>";

   $error = Toolbox::commonCheckForUseGLPI(true);

   echo "</table>";
   switch ($error) {
      case 0 :
         echo "<form action='install.php' method='post'>";
         echo "<input type='hidden' name='update' value='". $update."'>";
         echo "<input type='hidden' name='language' value='". $_SESSION['glpilanguage']."'>";
         echo "<p class='submit'><input type='hidden' name='install' value='Etape_1'>";
         echo "<input type='submit' name='submit' class='submit' value=\"".__('Continuar')."\">";
         echo "</p>";
         Html::closeForm();
         break;

      case 1 :
         echo "<h3>".__('¿Quieres continuar?')."</h3>";
         echo "<div class='submit'><form action='install.php' method='post' class='inline'>";
         echo "<input type='hidden' name='install' value='Etape_1'>";
         echo "<input type='hidden' name='update' value='". $update."'>";
         echo "<input type='hidden' name='language' value='". $_SESSION['glpilanguage']."'>";
         echo "<input type='submit' name='submit' class='submit' value=\"".__('Continuar')."\">";
         Html::closeForm();
         echo "&nbsp;&nbsp;";

         echo "<form action='install.php' method='post' class='inline'>";
         echo "<input type='hidden' name='update' value='". $update."'>";
         echo "<input type='hidden' name='language' value='". $_SESSION['glpilanguage']."'>";
         echo "<input type='hidden' name='install' value='Etape_0'>";
         echo "<input type='submit' name='submit' class='submit' value=\"".__('Reintentar')."\">";
         Html::closeForm();
         echo "</div>";
         break;

      case 2 :
         echo "<h3>".__('¿Quieres continuar?')."</h3>";
         echo "<form action='install.php' method='post'>";
         echo "<input type='hidden' name='update' value='".$update."'>";
         echo "<p class='submit'><input type='hidden' name='install' value='Etape_0'>";
         echo "<input type='submit' name='submit' class='submit' value=\"".__('Reintentar')."\">";
         echo "</p>";
         Html::closeForm();
         break;
   }

}


//step 2 import mysql settings.
function step2($update) {

   echo "<h3>".__('Configuración de la conexión a la base de datos')."</h3>";
   echo "<form action='install.php' method='post'>";
   echo "<input type='hidden' name='update' value='".$update."'>";
   echo "<fieldset><legend>".__('Parámetros de conexión a la base de datos')."</legend>";
   echo "<p><label class='block'>".__('Servidor SQL (MariaDB o MySQL)') ." </label>";
   echo "<input type='text' name='db_host'><p>";
   echo "<p><label class='block'>".__('Usuario SQL') ." </label>";
   echo "<input type='text' name='db_user'></p>";
   echo "<p><label class='block'>".__('Contraseña SQL')." </label>";
   echo "<input type='password' name='db_pass'></p></fieldset>";
   echo "<input type='hidden' name='install' value='Etape_2'>";
   echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
         __('Continuar')."'></p>";
   Html::closeForm();
}


//step 3 test mysql settings and select database.
function step3($host, $user, $password, $update) {

   error_reporting(16);
   echo "<h3>".__('Prueba de la conexión en la base de datos')."</h3>";

   //Check if the port is in url
   $hostport = explode(":", $host);
   if (count($hostport) < 2) {
      $link = new mysqli($hostport[0], $user, $password);
   } else {
      $link = new mysqli($hostport[0], $user, $password, '', $hostport[1]);
   }

   if ($link->connect_error
       || empty($host)
       || empty($user)) {
      echo "<p>".__("No pude conectarme a la base de datos")."\n <br>".
           sprintf(__('El servidor respondió: %s'), $link->connect_error)."</p>";

      if (empty($host)
          || empty($user)) {
         echo "<p>".__('El campo de servidor o usuario está vacío')."</p>";
      }

      echo "<form action='install.php' method='post'>";
      echo "<input type='hidden' name='update' value='".$update."'>";
      echo "<input type='hidden' name='install' value='Etape_1'>";
      echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
            __s('Volver atrás')."'></p>";
      Html::closeForm();

   } else {
      $_SESSION['db_access'] = ['host'     => $host,
                                     'user'     => $user,
                                     'password' => $password];
      echo  "<h3>".__('Conexión a la base de datos exitosa')."</h3>";

      //get database raw version
      $DB_ver = $link->query("SELECT version()");
      $row = $DB_ver->fetch_array();
      echo "<p class='center'>";
      $checkdb = Config::displayCheckDbEngine(true, $row[0]);
      echo "</p>";
      if ($checkdb > 0) {
         return;
      }

      if ($update == "no") {
         echo "<p>".__('Seleccione una base de datos:')."</p>";
         echo "<form action='install.php' method='post'>";

         if ($DB_list = $link->query("SHOW DATABASES")) {
            while ($row = $DB_list->fetch_array()) {
               if (!in_array($row['Database'], ["information_schema",
                                                     "mysql",
                                                     "performance_schema"] )) {
                  echo "<p>";
                  echo "<label class='radio'>";
                  echo "<input type='radio' name='databasename' value='". $row['Database']."'>";

                  echo "<span class='outer'><span class='inner'></span></span>";
                  echo $row['Database'];
                  echo " </label>";
                  echo " </p>";
               }
            }
         }

         echo "<p>";
         echo "<label class='radio'>";
         echo "<input type='radio' name='databasename' value='0'>";
         echo __('Cree una nueva base de datos o use una existente:');
         echo "<span class='outer'><span class='inner'></span></span>";
         echo "&nbsp;<input type='text' name='newdatabasename'>";
         echo " </label>";
         echo "</p>";
         echo "<input type='hidden' name='install' value='Etape_3'>";
         echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
               __('Continuar')."'></p>";
         $link->close();
         Html::closeForm();

      } else if ($update == "yes") {
         echo "<p>".__('Seleccione la base de datos para actualizar:')."</p>";
         echo "<form action='install.php' method='post'>";

         $DB_list = $link->query("SHOW DATABASES");
         while ($row = $DB_list->fetch_array()) {
            echo "<p>";
            echo "<label class='radio'>";
            echo "<input type='radio' name='databasename' value='". $row['Database']."'>";
            echo "<span class='outer'><span class='inner'></span></span>";
            echo $row['Database'];
            echo " </label>";
            echo "</p>";
         }

         echo "<input type='hidden' name='install' value='update_1'>";
         echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
                __('Continuar')."'></p>";
         $link->close();
         Html::closeForm();
      }

   }
}


//Step 4 Create and fill database.
function step4 ($databasename, $newdatabasename) {

   $host     = $_SESSION['db_access']['host'];
   $user     = $_SESSION['db_access']['user'];
   $password = $_SESSION['db_access']['password'];

   //display the form to return to the previous step.
   echo "<h3>".__('Inicialización de la base de datos')."</h3>";

   function prev_form($host, $user, $password) {

      echo "<br><form action='install.php' method='post'>";
      echo "<input type='hidden' name='db_host' value='". $host ."'>";
      echo "<input type='hidden' name='db_user' value='". $user ."'>";
      echo " <input type='hidden' name='db_pass' value='". rawurlencode($password) ."'>";
      echo "<input type='hidden' name='update' value='no'>";
      echo "<input type='hidden' name='install' value='Etape_2'>";
      echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
            __s('Volver atrás')."'></p>";
      Html::closeForm();
   }

   //Display the form to go to the next page
   function next_form() {

      echo "<br><form action='install.php' method='post'>";
      echo "<input type='hidden' name='install' value='Etape_4'>";
      echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
             __('Continuar')."'></p>";
      Html::closeForm();
   }

   //create security key
   $glpikey = new GLPIKey();
   $secured = $glpikey->keyExists();
   if (!$secured) {
      $secured = $glpikey->generate();
   }

   if (!$secured) {
      echo "<p><strong>".__('¡No se puede generar la llave de seguridad!')."</strong></p>";
      prev_form($host, $user, $password);
      return;
   }

   //Check if the port is in url
   $hostport = explode(":", $host);
   if (count($hostport) < 2) {
      $link = new mysqli($hostport[0], $user, $password);
   } else {
      $link = new mysqli($hostport[0], $user, $password, '', $hostport[1]);
   }

   $databasename    = $link->real_escape_string($databasename);
   $newdatabasename = $link->real_escape_string($newdatabasename);

   if (!empty($databasename)) { // use db already created
      $DB_selected = $link->select_db($databasename);

      if (!$DB_selected) {
         echo __('Imposible utilizar la base de datos:');
         echo "<br>".sprintf(__('El servidor respondió: %s'), $link->error);
         prev_form($host, $user, $password);

      } else {
         if (DBConnection::createMainConfig($host, $user, $password, $databasename)) {
            Toolbox::createSchema($_SESSION["glpilanguage"]);
            echo "<p>".__('OK - la base de datos fue inicializada')."</p>";

            next_form();

         } else { // can't create config_db file
            echo "<p>".__('Imposible escribir el archivo de configuración de la base de datos')."</p>";
            prev_form($host, $user, $password);
         }
      }

   } else if (!empty($newdatabasename)) { // create new db
      // Try to connect
      if ($link->select_db($newdatabasename)) {
         echo "<p>".__('Base de datos creada')."</p>";

         if (DBConnection::createMainConfig($host, $user, $password, $newdatabasename)) {
            Toolbox::createSchema($_SESSION["glpilanguage"]);
            echo "<p>".__('OK - la base de datos fue inicializada')."</p>";
            next_form();

         } else { // can't create config_db file
            echo "<p>".__('Imposible escribir el archivo de configuración de la base de datos')."</p>";
            prev_form($host, $user, $password);
         }

      } else { // try to create the DB
         if ($link->query("CREATE DATABASE IF NOT EXISTS `".$newdatabasename."`")) {
            echo "<p>".__('Base de datos creada')."</p>";

            if ($link->select_db($newdatabasename)
                && DBConnection::createMainConfig($host, $user, $password, $newdatabasename)) {

               Toolbox::createSchema($_SESSION["glpilanguage"]);
               echo "<p>".__('OK - la base de datos fue inicializada')."</p>";
               next_form();

            } else { // can't create config_db file
               echo "<p>".__('Imposible escribir el archivo de configuración de la base de datos')."</p>";
               prev_form($host, $user, $password);
            }

         } else { // can't create database
            echo __('¡Error al crear la base de datos!');
            echo "<br>".sprintf(__('El servidor respondió: %s'), $link->error);
            prev_form($host, $user, $password);
         }
      }

   } else { // no db selected
      echo "<p>".__("¡No seleccionaste una base de datos!"). "</p>";
      //prev_form();
      prev_form($host, $user, $password);
   }

   $link->close();

}

//send telemetry informations
function step6() {
   global $DB;
   echo "<h3>".__('Recolectar datos')."</h3>";

   include_once(GLPI_ROOT . "/inc/dbmysql.class.php");
   include_once(GLPI_CONFIG_DIR . "/config_db.php");
   $DB = new DB();

   echo "<form action='install.php' method='post'>";
   echo "<input type='hidden' name='install' value='Etape_5'>";

 //  echo Telemetry::showTelemetry();
 //  echo Telemetry::showReference();

   echo "<p class='submit'><input type='submit' name='submit' class='submit' value='".
            __('Continuar')."'></p>";
   Html::closeForm();
}

function step7() {
   echo "<h3>".__('Una última cosa antes de empezar')."</h3>";

   echo "<form action='install.php' method='post'>";
   echo "<input type='hidden' name='install' value='Etape_6'>";

   echo GLPINetwork::showInstallMessage();

   echo "<p class='submit'>";
   echo "<a href='".GLPI_NETWORK_SERVICES2."' target='_blank' class='vsubmit'>".
            __('Donativo')."</a>&nbsp;";
   echo "<input type='submit' name='submit' class='submit' value='".
            __('Continuar')."'>";
   echo "</p>";
   Html::closeForm();
}

// finish installation
function step8() {
   include_once(GLPI_ROOT . "/inc/dbmysql.class.php");
   include_once(GLPI_CONFIG_DIR . "/config_db.php");
   $DB = new DB();

   if (isset($_POST['send_stats'])) {
      //user has accepted to send telemetry infos; activate cronjob
      $DB->update(
         'glpi_crontasks',
         ['state' => 0],
         ['name' => 'telemetry']
      );
   }

   $url_base = str_replace("/install/install.php", "", $_SERVER['HTTP_REFERER']);
   $DB->update(
      'glpi_configs',
      ['value' => $DB->escape($url_base)], [
         'context'   => 'core',
         'name'      => 'url_base'
      ]
   );

   $url_base_api = "$url_base/apirest.php/";
   $DB->update(
      'glpi_configs',
      ['value' => $DB->escape($url_base_api)], [
         'context'   => 'core',
         'name'      => 'url_base_api'
      ]
   );

   Session::destroy(); // Remove session data (debug mode for instance) set by web installation

   echo "<h2>".__('La instalacion esta terminada')."</h2>";

   echo "<p>".__('Los accesos por defecto son:')."</p>";
   echo "<p><ul><li> ".__('civikmind/civikmind para la cuenta de adminsitración')."</li>";
   echo "<li>".__('soporte/soporte para la cuenta técnica')."</li>";
   echo "<li>".__('ciudadano/ciudadano para cuentas normales')."</li>";
   echo "<li>".__('publicador/publicador para cuentas de solo envios')."</li></ul></p>";
   echo "<p>".__('Puede eliminar o modificar estas cuentas, así como los datos iniciales.')."</p>";
   echo "<p class='center'><a class='vsubmit' href='../index.php'>".__('Usá Civikmind');
   echo "</a></p>";
}


function update1($DBname) {

   $host     = $_SESSION['db_access']['host'];
   $user     = $_SESSION['db_access']['user'];
   $password = $_SESSION['db_access']['password'];

   if (DBConnection::createMainConfig($host, $user, $password, $DBname) && !empty($DBname)) {
      $from_install = true;
      include_once(GLPI_ROOT ."/install/update.php");

   } else { // can't create config_db file
      echo __("No se puede crear el archivo de conexión de la base de datos, verifique los permisos del archivo.");
      echo "<h3>".__('¿Quieres continuar?')."</h3>";
      echo "<form action='install.php' method='post'>";
      echo "<input type='hidden' name='update' value='yes'>";
      echo "<p class='submit'><input type='hidden' name='install' value='Etape_0'>";
      echo "<input type='submit' name='submit' class='submit' value=\"".__('Continuar')."\">";
      echo "</p>";
      Html::closeForm();
   }
}



//------------Start of install script---------------------------


// Use default session dir if not writable
if (is_writable(GLPI_SESSION_DIR)) {
   Session::setPath();
}

Session::start();
error_reporting(0); // we want to check system before affraid the user.

if (isset($_POST["language"])) {
   $_SESSION["glpilanguage"] = $_POST["language"];
}

Session::loadLanguage('', false);

/**
 * @since 0.84.2
**/
function checkConfigFile() {

   if (file_exists(GLPI_CONFIG_DIR . "/config_db.php")) {
      Html::redirect($CFG_GLPI['root_doc'] ."/index.php");
      die();
   }
}

if (!isset($_SESSION['can_process_install']) || !isset($_POST["install"])) {
   $_SESSION = [];

   $_SESSION["glpilanguage"] = Session::getPreferredLanguage();

   checkConfigFile();

   // Add a flag that will be used to validate that installation can be processed.
   // This flag is put here just after checking that DB config file does not exist yet.
   // It is mandatory to validate that `Etape_4` to `Etape_6` are not used outside installation process
   // to change GLPI base URL without even being authenticated.
   $_SESSION['can_process_install'] = true;

   header_html(__("Elige tu idioma"));
   choose_language();

} else {
   // Check valid Referer :
   Toolbox::checkValidReferer();
   // Check CSRF: ensure nobody strap first page that checks if config file exists ...
   Session::checkCSRF($_POST);

   // DB clean
   if (isset($_POST["db_pass"])) {
      $_POST["db_pass"] = stripslashes($_POST["db_pass"]);
      $_POST["db_pass"] = rawurldecode($_POST["db_pass"]);
      $_POST["db_pass"] = stripslashes($_POST["db_pass"]);
   }

   switch ($_POST["install"]) {
      case "lang_select" : // lang ok, go accept licence
         checkConfigFile();
         header_html(SoftwareLicense::getTypeName(1));
         acceptLicense();
         break;

      case "License" : // licence  ok, go choose installation or Update
         checkConfigFile();
         header_html(__('Iniciando instalación'));
         step0();
         break;

      case "Etape_0" : // choice ok , go check system
         checkConfigFile();
         //TRANS %s is step number
         header_html(sprintf(__('Paso %d'), 0));
         $_SESSION["Test_session_GLPI"] = 1;
         step1($_POST["update"]);
         break;

      case "Etape_1" : // check ok, go import mysql settings.
         checkConfigFile();
         // check system ok, we can use specific parameters for debug
         Toolbox::setDebugMode(Session::DEBUG_MODE, 0, 0, 1);

         header_html(sprintf(__('Paso %d'), 1));
         step2($_POST["update"]);
         break;

      case "Etape_2" : // mysql settings ok, go test mysql settings and select database.
         checkConfigFile();
         header_html(sprintf(__('Paso %d'), 2));
         step3($_POST["db_host"], $_POST["db_user"], $_POST["db_pass"], $_POST["update"]);
         break;

      case "Etape_3" : // Create and fill database
         checkConfigFile();
         header_html(sprintf(__('Paso %d'), 3));
         if (empty($_POST["databasename"])) {
            $_POST["databasename"] = "";
         }
         if (empty($_POST["newdatabasename"])) {
            $_POST["newdatabasename"] = "";
         }
         step4($_POST["databasename"],
               $_POST["newdatabasename"]);
         break;

      case "Etape_4" : // send telemetry informations
         header_html(sprintf(__('Paso %d'), 4));
         step6();
         break;

      case "Etape_5" : // finish installation
         header_html(sprintf(__('Paso %d'), 5));
         step7();
         break;

      case "Etape_6" : // finish installation
         header_html(sprintf(__('Paso %d'), 6));
         step8();
         break;

      case "update_1" :
         checkConfigFile();
         if (empty($_POST["databasename"])) {
            $_POST["databasename"] = "";
         }
         update1($_POST["databasename"]);
         break;
   }
}
footer_html();
