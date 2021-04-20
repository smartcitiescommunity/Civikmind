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

include ("../../../inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkLoginUser();

if (isset($_POST['ext'])
      && !empty($_POST['ext'])) {
   if ($_POST['ext'] == "odt") {
      echo "&nbsp;";
      $option = [];
      $option[1] = __("With data", 'mreporting');
      $option[0] = __("Without data", 'mreporting');
      Dropdown::showFromArray("withdata", $option, []);

   }

   if ($_POST['ext'] == "svg") {
      //close previous form
      Html::Closeform();

      $randname = $_POST['randname'];
      echo "<form method='post' action='export_svg.php' id='export_svg_form' ".
         "style='margin: 0; padding: 0' target='_blank'>";
      echo "<input type='hidden' name='svg_content' value='none' />";
      echo "<input type='button' class='submit' id='export_svg_link' target='_blank' href='#' ".
                        "onClick='return false;' value='"._sx('button', 'Post')."' />";
      Html::Closeform();
      echo "<script type='text/javascript'>
            $('#export_svg_link').on('click', function () {
               var svg_content = vis{$randname}.scene[0].canvas.innerHTML;

               var form = document.getElementById('export_svg_form');
               form.svg_content.value = svg_content;
               form.submit();

               // In svg export, set new crsf token
               $.ajax({
                  url: '../ajax/get_new_crsf_token.php'
               }).done(function(token) {
                  $('#export_svg_form input[name=_glpi_csrf_token]').val(token);
               });

               // In main form, set new crsf token
               $.ajax({
                  url: '../ajax/get_new_crsf_token.php'
               }).done(function(token) {
                  $('#mreporting_date_selector input[name=_glpi_csrf_token]').val(token);
               });

            });
         </script>";
   } else {

      echo "&nbsp;<input type='submit' id='export_submit' name='export' value=\"".
      _sx('button', 'Post')."\" class='submit'>";

      echo "<script type='text/javascript'>
         $('#export_submit').on('click', function () {
            $.ajax({
               url: '../ajax/get_new_crsf_token.php'
            }).done(function(token) {
               $('#export_form input[name=_glpi_csrf_token]').val(token);
            });
         });

      </script>";
   }

}
