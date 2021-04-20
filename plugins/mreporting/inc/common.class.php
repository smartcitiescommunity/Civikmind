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

use Odtphp\Odf;

class PluginMreportingCommon extends CommonDBTM {
   static $rightname = 'statistic';

   const MNBSP = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

   /**
    * Returns the type name with consideration of plural
    *
    * @param number $nb Number of item(s)
    * @return string Itemtype name
    */
   public static function getTypeName($nb = 0) {
      return __("More Reporting", 'mreporting');
   }

   public static function canCreate() {
      return false;
   }

   static function getMenuContent() {
      global $CFG_GLPI;

      $web_full_dir = Plugin::getWebDir('mreporting');
      $img_db = "<img src='".$web_full_dir."/pics/dashboard.png'
                           title='".__("Dashboard", 'mreporting')."'
                           alt='".__("Dashboard", 'mreporting')."'>";
      $img_ct   = "<img src='".$web_full_dir."/pics/list_dashboard.png'
                           title='".__("Reports list", 'mreporting')."'
                           alt='".__("Reports list", 'mreporting')."'>";

      $web_rel_dir   = Plugin::getWebDir('mreporting', false);
      $url_central   = "/$web_rel_dir/front/central.php";
      $url_dashboard = "/$web_rel_dir/front/dashboard.php";

      $menu = parent::getMenuContent();

      $menu['page'] = PluginMreportingDashboard::CurrentUserHaveDashboard() ? $url_dashboard : $url_central;
      $menu['icon'] = self::getIcon();

      $menu['options']['dashboard']['page']            = $url_dashboard;
      $menu['options']['dashboard']['title']           = __("Dashboard", 'mreporting');
      $menu['options']['dashboard']['links'][$img_db]  = $url_dashboard;
      $menu['options']['dashboard']['links'][$img_ct]  = $url_central;
      if (PluginMreportingConfig::canCreate()) {
         $menu['options']['dashboard']['links']['config'] = PluginMreportingConfig::getSearchURL(false);
      }

      $menu['options']['dashboard_list']               = $menu['options']['dashboard'];
      $menu['options']['dashboard_list']['page']       = $url_central;
      $menu['options']['dashboard_list']['title']      = __("Reports list", 'mreporting');

      $menu['options']['config']['title']              = PluginMreportingConfig::getTypeName(2);
      $menu['options']['config']['page']               = PluginMreportingConfig::getSearchURL(false);
      $menu['options']['config']['links']              = $menu['options']['dashboard']['links'];
      $menu['options']['config']['links']['search']    = PluginMreportingConfig::getSearchURL(false);
      if (PluginMreportingConfig::canCreate()) {
         $menu['options']['config']['links']['add']    = PluginMreportingConfig::getFormURL(false);
      }

      return $menu;
   }

   /**
    * Parsing all classes
    * Search all class into inc folder
   */
   static function parseAllClasses($inc_dir) {

      $classes = [];
      $matches = [];

      if ($handle = opendir($inc_dir)) {
         while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
               $fcontent = file_get_contents($inc_dir."/".$entry);
               if (preg_match("/class\s(.+)Extends PluginMreporting.*Baseclass/i",
                     $fcontent, $matches)) {
                  $classes[] = trim($matches[1]);
               }
            }
         }
      }

      return $classes;
   }

   /**
    * Get all reports from parsing class
    *
    * @params
   */

   function getAllReports($with_url = true, $params = []) {
      global $LANG;

      $reports = [];

      $inc_dir = Plugin::getPhpDir('mreporting') . "/inc";
      $pics_dir = "../pics";

      if (isset($params['classname'])
            && !empty($params['classname'])) {
         $classes = [];
         $classes[] = $params['classname'];

      } else {
         //parse inc dir to search report classes
         $classes = self::parseAllClasses($inc_dir);

         sort($classes);
      }

      //construct array to list classes and functions
      foreach ($classes as $classname) {
         $i = 0;
         if (!class_exists($classname)) {
            continue;
         }

         //scn = short class name
         $scn = str_replace('PluginMreporting', '', $classname);
         if (isset($LANG['plugin_mreporting'][$scn]['title'])) {
            $title = $LANG['plugin_mreporting'][$scn]['title'];

            $functions = get_class_methods($classname);

            foreach ($functions as $f_name) {
               $ex_func = preg_split('/(?<=\\w)(?=[A-Z])/', $f_name);
               if ($ex_func[0] != 'report') {
                  continue;
               }

               if (isset($LANG['plugin_mreporting'][$scn][$f_name])) {
                  $gtype      = strtolower($ex_func[1]);
                  $title_func = $LANG['plugin_mreporting'][$scn][$f_name]['title'];
                  $category_func = '';
                  if (isset($LANG['plugin_mreporting'][$scn][$f_name]['category'])) {
                     $category_func = $LANG['plugin_mreporting'][$scn][$f_name]['category'];
                  }

                  if (isset($LANG['plugin_mreporting'][$scn][$f_name]['desc'])) {
                     $des_func = $LANG['plugin_mreporting'][$scn][$f_name]['desc'];
                  } else {
                     $des_func = "";
                  }
                  $url_graph  = "graph.php?short_classname=$scn".
                     "&amp;f_name=$f_name&amp;gtype=$gtype";
                  $min_url_graph  = "front/graph.php?short_classname=$scn".
                     "&amp;f_name=$f_name&amp;gtype=$gtype";

                  $reports[$classname]['title'] = $title;
                  $reports[$classname]['functions'][$i]['function'] = $f_name;
                  $reports[$classname]['functions'][$i]['title'] = $title_func;
                  $reports[$classname]['functions'][$i]['desc'] = $des_func;
                  $reports[$classname]['functions'][$i]['category_func'] = $category_func;
                  $reports[$classname]['functions'][$i]['pic'] = $pics_dir."/chart-$gtype.png";
                  $reports[$classname]['functions'][$i]['gtype'] = $gtype;
                  $reports[$classname]['functions'][$i]['short_classname'] = $scn;
                  $reports[$classname]['functions'][$i]['is_active'] = false;

                  $config = new PluginMreportingConfig();
                  if ($config->getFromDBByFunctionAndClassname($f_name, $classname)) {
                     if ($config->fields['is_active'] == 1) {
                        $reports[$classname]['functions'][$i]['is_active'] = true;
                        $reports[$classname]['functions'][$i]['id'] = $config->fields['id'];
                     }
                     $reports[$classname]['functions'][$i]['right'] = READ;
                     if (isset($_SESSION['glpiactiveprofile'])) {
                        $reports[$classname]['functions'][$i]['right'] =
                           PluginMreportingProfile::canViewReports($_SESSION['glpiactiveprofile']['id'], $config->fields['id']);
                     }
                  }

                  if ($with_url) {
                     $reports[$classname]['functions'][$i]['url_graph'] = $url_graph;
                     $reports[$classname]['functions'][$i]['min_url_graph'] = $min_url_graph;
                  }

                  $i++;
               }
            }
         }
      }

      return $reports;
   }

   /**
    * Show list of activated reports
    *
    * @param array $opt : short_classname,f_name,gtype,rand
   */
   static function title($opt) {
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th>".__("Select statistics to be displayed")."&nbsp;:</th></tr>";
      echo "<tr><td class='center'>";
      echo self::getSelectAllReports(true);
      echo "</td>";
      echo "</tr>";
      echo "</table>";
   }

   static function getSelectAllReports($onchange = false, $setIdInOptionsValues = false) {

      $common = new self();
      $reports = $common->getAllReports(true);

      if (empty($reports)) {
         return "";
      }

      $js_onchange = $onchange ? " onchange='window.location.href=this.options[this.selectedIndex].value'" : "";

      $select  = "<select name='report' $js_onchange>";
      $select .= "<option value='-1' selected>".Dropdown::EMPTY_VALUE."</option>";

      foreach ($reports as $classname => $report) {
         $graphs = [];
         foreach ($report['functions'] as $function) {
            if ($function['is_active']) {
               $graphs[$function['category_func']][] = $function;
            }
         }

         foreach ($graphs as $cat => $graph) {
            $remove = true;
            foreach ($graph as $key => $value) {
               if ($value['right']) {
                  $remove = false;
               }
            }
            if ($remove) {
               unset($graphs[$cat]);
            }
         }

         if (count($graphs) > 0) {
            $select.= "<optgroup label=\"".$report['title']."\">";
            foreach ($graphs as $cat => $graph) {
               if (count($graph) > 0) {
                  $select.= "<optgroup label=\"&nbsp;&nbsp;&nbsp;$cat\">";

                  usort(
                     $graph,
                     function ($a, $b) {
                        $a_title = $a['title'];
                        $b_title = $b['title'];
                        return strcmp($a_title, $b_title);
                     }
                  );

                  foreach ($graph as $key => $value) {
                     if ($value['right']) {
                        if ($value['is_active']) {
                           $comment = "";
                           if (isset($value["desc"])) {
                              $comment = $value["desc"];
                           }
                           $option_value = $value["url_graph"];
                           if ($setIdInOptionsValues) {
                              $option_value = $value['id'];
                           }
                           $icon = self::getReportIcon($value['function']);
                           $select .= "<option value='$option_value' title=\"".
                                     Html::cleanInputText($comment).
                                     "\">&nbsp;&nbsp;&nbsp;".$icon."&nbsp;".
                                     $value["title"]."</option>";
                        }
                     }
                  }

                  $select.= "</optgroup>";
               }
            }
            $select.= "</optgroup>";
         }
      }
      $select.= "</select>";

      return $select;
   }

   /**
    * parse All class for list active reports
    * and display list
   */

   function showCentral($params) {

      $reports = $this->getAllReports(true, $params);

      if ($reports === false) {
         echo "<div class='center'>".__("No report is available !", 'mreporting')."</div>";
         return false;
      }

      echo "<table class='tab_cadre_fixe' id='mreporting_functions'>";

      foreach ($reports as $classname => $report) {
         $i = 0;
         $nb_per_line = 2;
         $graphs = [];
         foreach ($report['functions'] as $function) {
            if ($function['is_active']) {
               $graphs[$classname][$function['category_func']][] = $function;
            }
         }

         $count = 0;
         if (isset($graphs[$classname])) {
            foreach ($graphs[$classname] as $cat => $graph) {
               if (self::haveSomeThingToShow($graph)) {
                  echo "<tr class='tab_bg_1'><th colspan='4'>".$cat."</th></tr>";
                  foreach ($graph as $k => $v) {
                     if ($v['right'] && $v['is_active']) {
                        if ($i%$nb_per_line == 0) {
                           if ($i != 0) {
                              echo "</tr>";
                           }
                           echo "<tr class='tab_bg_1' valign='top'>";
                        }

                        echo "<td>";
                        echo "<a href='".$v['url_graph']."'>";
                        echo "<img src='".$v['pic']."' />&nbsp;";
                        echo $v['title'];
                        echo "</a>";
                        echo"</td>";
                        $i++;
                     }

                     $count++;
                     if ($i%$nb_per_line > 0) {
                         $count++;
                     }
                  }

                  while ($i%$nb_per_line != 0) {
                     echo "<td>&nbsp;</td>";
                     $i++;
                  }
               }
            }
         }
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<th colspan='2'>";

         if (isset($graphs[$classname]) && $count>0) {
            $height = 200 + 30*$count;

            echo "<div class='f_right'>";
            echo __("Export")." : ";
            echo "<a href='#' onClick=\"var w = window.open('popup.php?classname=$classname' ,'glpipopup', ".
                  "'height=$height, width=1000, top=100, left=100, scrollbars=yes'); w.focus();\">";
            echo "ODT</a>";
            echo "</div>";
         } else {
            echo __("No report is available !", 'mreporting');
         }
         echo "</th>";
         echo "</tr>";
      }

      echo "</table>";
   }

   static function haveSomeThingToShow($graph) {
      foreach ($graph as $k => $v) {
         if ($v['right']) {
            return true;
         }
      }
      return false;
   }

   /**
    * show Export Form from popup.php
    * for odt export
   */

   function showExportForm($opt) {
      $classname = $opt["classname"];
      if ($classname) {
         echo "<div align='center'>";

         echo "<form method='POST' action='export.php?switchto=odtall&classname=".$classname."'
                     id='exportform' name='exportform'>\n";

         echo "<table class='tab_cadre_fixe'>";

         $reports = $this->getAllReports(false, $opt);

         foreach ($reports as $class => $report) {
            $i = 0;
            $nb_per_line = 2;
            $graphs = [];
            foreach ($report['functions'] as $function) {
               if ($function['gtype'] === "sunburst") {
                  continue;
               }
               if ($function['is_active']) {
                  $graphs[$classname][$function['category_func']][] = $function;
               }
            }

            foreach ($graphs[$classname] as $cat => $graph) {
               echo "<tr class='tab_bg_1'><th colspan='4'>".$cat."</th></tr>";
               foreach ($graph as $k => $v) {

                  if ($v['is_active']) {
                     if ($i%$nb_per_line == 0) {
                        if ($i != 0) {
                           echo "</tr>";
                        }
                        echo "<tr class='tab_bg_1'>";
                     }

                     echo "<td>";
                     echo "<input type='checkbox' name='check[" . $v['function'].$classname . "]'";
                     if (isset($_POST['check']) && $_POST['check'] == 'all') {
                        echo " checked ";
                     }
                     echo ">";
                     echo "</td>";
                     echo "<td>";
                     echo "<img src='".$v['pic']."' />&nbsp;";
                     echo $v['title'];
                     echo "</td>";
                     $i++;
                  }
               }

               while ($i%$nb_per_line != 0) {
                  echo "<td width='10'>&nbsp;</td>";
                  echo "<td>&nbsp;</td>";
                  $i++;
               }
            }
            echo "</tr>";
         }

         echo "<tr class='tab_bg_2'>";
         echo "<td colspan='4' class='center'>";
         echo "<div align='center'>";
         echo "<table>";
         echo "<tr class='tab_bg_2'>";
         echo "<td>".__("Begin date")."</td>";
         echo "<td>";
         $date1 =  strftime("%Y-%m-%d", time() - (30 * 24 * 60 * 60));
         Html::showDateField("date1", ['value'      => $date1,
                                       'maybeempty' => true]);
         echo "</td>";
         echo "<td>".__("End date")."</td>";
         echo "<td>";
         $date2 = strftime("%Y-%m-%d");
         Html::showDateField("date2", ['value'      => $date2,
                                       'maybeempty' => true]);
         echo "</td>";
         echo "</tr>";
         echo "</table>";
         echo "</div>";

         echo "</td>";
         echo "</tr>";

         echo "</table>";
         Html::openArrowMassives("exportform", true);

         $option[0] = __("Without data", 'mreporting');
         $option[1] = __("With data", 'mreporting');
         Dropdown::showFromArray("withdata", $option, []);
         echo "&nbsp;";
         echo "<input type='button' id='export_submit' value='".__("Export")."' class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";

         echo "<script type='text/javascript'>
            $('#export_submit').on('click', function () {
               //get new crsf
               $.ajax({
                  url: '../ajax/get_new_crsf_token.php'
               }).done(function(token) {
                  $('#export_form input[name=_glpi_csrf_token]').val(token);
                  document.getElementById('exportform').submit();
               });
            });
         </script>";
      }
   }

   /**
    * exit from grpah if no @params detected
    *
    * @params
   */

   function initParams($params, $export = false) {
      if (!isset($params['classname'])) {
         if (!isset($params['short_classname'])) {
            exit;
         }
         if (!isset($params['f_name'])) {
            exit;
         }
         if (!isset($params['gtype'])) {
            exit;
         }
      }

      return $params;
   }

   /**
    * init Params for graph function
    *
    * @params
   */

   static function initGraphParams($params) {
      $crit        = [];

      // Default values of parameters
      $raw_datas   = [];
      $title       = "";
      $desc        = "";
      $root        = "";

      $export      = false;
      $opt         = [];

      foreach ($params as $key => $val) {
         $crit[$key]=$val;
      }

      return $crit;
   }

   /**
    * show Graph : Show graph
    *
    * @params $options ($opt, export)
    * @params $opt (classname, short_classname, f_name, gtype)
   */

   function showGraph($opt, $export = false, $forceFormat = null) {
      global $LANG, $CFG_GLPI;

      if (!isset($opt['hide_title'])) {
         self::title($opt);
         $opt['hide_title'] = false;
      }

      if (!isset($opt['width'])) {
         $opt['width'] = false;
      }

      //check the format display charts configured in glpi
      $opt = $this->initParams($opt, $export);
      $config = PluginMreportingConfig::initConfigParams($opt['f_name'],
         "PluginMreporting".$opt['short_classname']);

      if ('PNG' === $forceFormat || $config['graphtype'] == 'PNG') {
         $graph = new PluginMreportingGraphpng();
      } else {
         // Defaults to SVG
         $graph = new PluginMreportingGraph();
      }

      //generate default date
      if (!isset($_SESSION['mreporting_values']['date1'.$config['randname']])) {
         $_SESSION['mreporting_values']['date1'.$config['randname']] = strftime("%Y-%m-%d",
            time() - ($config['delay'] * 24 * 60 * 60));
      }
      if (!isset($_SESSION['mreporting_values']['date2'.$config['randname']])) {
         $_SESSION['mreporting_values']['date2'.$config['randname']] = strftime("%Y-%m-%d");
      }

      // save/clear selectors
      if (isset($opt['submit'])) {
         self::saveSelectors($opt['f_name'], $config);
      } else if (isset($opt['reset'])) {
         self::resetSelectorsForReport($opt['f_name']);
      }
      self::getSelectorValuesByUser();

      //dynamic instanciation of class passed by 'short_classname' GET parameter
      $classname = 'PluginMreporting'.$opt['short_classname'];

      //dynamic call of method passed by 'f_name' GET parameter with previously instancied class
      $obj = new $classname($config);
      $datas = $obj->{$opt['f_name']}($config);

      //show graph (pgrah type determined by first entry of explode of camelcase of function name
      $title_func = $LANG['plugin_mreporting'][$opt['short_classname']][$opt['f_name']]['title'];
      $des_func = "";
      if (isset($LANG['plugin_mreporting'][$opt['short_classname']][$opt['f_name']]['desc'])) {
         $des_func = $LANG['plugin_mreporting'][$opt['short_classname']][$opt['f_name']]['desc'];
      }

      $opt['class'] = $classname;
      $opt['withdata'] = 1;
      $params = ["raw_datas"   => $datas,
                 "title"      => $title_func,
                 "desc"       => $des_func,
                 "export"     => $export,
                 "opt"        => $opt];
      return $graph->{'show'.$opt['gtype']}($params, $opt['hide_title'], $opt['width']);
   }


   static function dropdownExt($options = []) {
      global $DB;

      $p['myname']                  = '';
      $p['value']                   = "";
      $p['ajax_page']               = '';
      $p['class']                   = '';
      $p['span']                    = '';
      $p['gtype']                   = '';
      $p['show_graph']              = '';
      $p['randname']                = '';
      $p['display_svg']             = '';
      foreach ($options as $key => $value) {
         $p[$key] = $value;
      }

      echo "<select name='switchto' id='".$p['myname']."'>";

      $elements[0] = Dropdown::EMPTY_VALUE;
      if ($p['gtype'] !== "sunburst") {
         $elements["odt"] = "ODT";
      }
      $elements["csv"] = "CSV";
      if ($p['show_graph']) {
         $elements["png"] = "PNG";
         if ($p['display_svg']) {
            $elements["svg"] = "SVG";
         }
      }
      foreach ($elements as $key => $val) {
         echo "<option value='".$key."'>".$val."</option>";
      }

      echo "</select>";

      $params =  ['span' => $p['span'],
                  'ext' => '__VALUE__',
                  'randname' => $p['randname']];

      Ajax::updateItemOnSelectEvent($p['myname'], $p['span'],
                                  $p['ajax_page'],
                                  $params);
   }

   /**
    * end Graph : Show graph datas array, setup link, export
    *
    * @params $options ($opt, export, datas, unit, labels2, flip_data)
   */

   static function endGraph($options, $dashboard = false) {
      global $CFG_GLPI;

      $opt        = [];
      $export     = false;
      $datas      = [];
      $unit       = '';
      $labels2    =  [];
      $flip_data  = false;

      foreach ($options as $k => $v) {
         $$k=$v;
      }

      $randname = false;
      if (isset($opt['randname']) && $opt['randname'] !== false) {
         $randname = $opt['randname'];
         $_REQUEST['short_classname'] = $opt['short_classname'];
         $_REQUEST['f_name'] = $opt['f_name'];
         $_REQUEST['gtype'] = $opt['gtype'];
         $_REQUEST['randname'] = $opt['randname'];

         //End Script for graph display
         //if $randname exists

         $config = PluginMreportingConfig::initConfigParams($opt['f_name'],
                                                            "PluginMreporting".$opt['short_classname']);
         if (!$export) {
            if ($config['graphtype'] == 'SVG') {
               echo "}
                  showGraph$randname();
               </script>";
            }
            echo "</div>";
         }
      }

      if (!$dashboard) {
         $request_string = self::getRequestString($_REQUEST);

         if ($export != "odtall") {
            if ($randname !== false && !$export) {

               $show_graph = PluginMreportingConfig::showGraphConfigValue($opt['f_name'], $opt['class']);
               self::showGraphDatas($datas, $unit, $labels2, $flip_data, $show_graph);

            }
            if (!$export) {
               if (isset($_REQUEST['f_name']) && $_REQUEST['f_name'] != "test") {
                  echo "<div class='graph_bottom'>";
                  echo "<span style='float:left'>";
                  echo "<br><br>";
                  self::showNavigation();
                  echo "</span>";
                  echo "<span style='float:right'>";
                  if (Session::haveRight('config', UPDATE)) {
                     echo "<b>".PluginMreportingConfig::getTypeName()."</b> : ";
                     echo "&nbsp;<a href='config.form.php?name=".$opt['f_name'].
                     "&classname=".$opt['class']."' target='_blank'>";
                     echo "<img src='../pics/config.png' class='title_pics'/></a>";
                  }
                  if ($randname !== false) {
                     echo "<br><br>";

                     echo "<form method='post' action='export.php?$request_string'
                        style='margin: 0; padding: 0' target='_blank' id='export_form'>";

                     echo "<b>".__("Export")."</b> : ";
                     $params = ['myname'   => 'ext',
                        'ajax_page'               => Plugin::getWebDir('mreporting')."/ajax/dropdownExport.php",
                        'class'                   => __CLASS__,
                        'span'                    => 'show_ext',
                        'gtype'                   => $_REQUEST['gtype'],
                        'show_graph'              => $show_graph,
                        'display_svg'             => ($config['graphtype'] != 'PNG'),
                        'randname'                => $randname];

                     self::dropdownExt($params);

                     echo "<span id='show_ext'></span>";
                     Html::closeForm();

                  }
                  echo "</span>";
               }
               echo "<div style='clear:both;'></div>";
               echo "</div>";

               if (isset($_REQUEST['f_name']) && $_REQUEST['f_name'] != "test") {
                  echo "</div></div>";
               }
            }

            if ($randname == false) {
               echo "</div>";
            }
         }
      }

      //destroy specific palette
      unset($_SESSION['mreporting']['colors']);
      unset($_SESSION['mreporting_values']);
   }

   /**
    * Compile datas for unit display
    *
    * @param $datas, ex : array( 'test1' => 15, 'test2' => 25)
    * @param $unit, ex : '%', 'Kg' (optionnal)
    * @if percent, return new datas
    */

   static function compileDatasForUnit($values, $unit = '') {

      $datas = $values;

      if ($unit == '%') {
         //complie news datas with percent values
         $calcul = [];

         $simpledatas = true;
         foreach ($datas as $k=>$v) {
            //multiple array
            if (is_array($v)) {
               $simpledatas = false;
            }
         }
         if (!$simpledatas) {

            $types = [];

            foreach ($datas as $k => $v) {

               if (is_array($v)) {
                  foreach ($v as $key => $val) {
                     $types[$key][$k] = $val;
                  }
               }
            }
            $datas = $types;
         }

         foreach ($datas as $k=>$v) {
            //multiple array
            if (!$simpledatas) {
               foreach ($v as $key => $val) {
                  $total = array_sum($v);
                  if ($total == 0) {
                     $calcul[$k][$key] = Html::formatNumber(0);
                  } else {
                     $calcul[$k][$key]= Html::formatNumber(($val*100)/$total);
                  }
               }
            } else {//simple array
               $total = array_sum($values);
               $calcul[$k]= Html::formatNumber(($v*100)/$total);

            }
         }

         if (!$simpledatas) {
            $datas = [];
            foreach ($calcul as $k => $v) {
               if (is_array($v)) {
                  foreach ($v as $key => $val) {
                     $datas[$key][$k] = $val;
                  }
               }
            }
         } else {
            $datas = $calcul;
         }

      }

      return $datas;
   }

   /**
    * show Graph datas
    *
    * @param $datas, ex : array( 'test1' => 15, 'test2' => 25)
    * @param $unit, ex : '%', 'Kg' (optionnal)
    * @param $labels2, ex : dates
    * @param $flip_data, flip array if necessary
   */

   static function showGraphDatas ($datas = [], $unit = '', $labels2 = [],
                                   $flip_data = false, $show_graph = false) {
      global $CFG_GLPI;

      $simpledatas = false;
      $treedatas = false;

      //simple and tree array
      $depth = self::getArrayDepth($datas);

      if (!$labels2 && $depth < 2) {
         $simpledatas = true;
      }

      if (strtolower($_REQUEST['gtype']) == "sunburst") {
         $treedatas = true;
      }

      if ($flip_data == true) {
         $labels2 = array_flip($labels2);
      }

      $types = [];

      foreach ($datas as $k => $v) {
         if (is_array($v)) {
            foreach ($v as $key => $val) {
               if (isset($labels2[$key])) {
                  $types[$key][$k] = $val;
               }
            }
         }
      }

      if ($flip_data != true) {
         $tmp = $datas;
         $datas = $types;
         $types = $tmp;
      }
      //simple array
      if ($simpledatas) {
         $datas = [__("Number", 'mreporting') => 0];
      }

      $rand = mt_rand();
      echo "<br>";
      echo "<table class='tab_cadre' width='90%'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th>";

      echo "<a href=\"javascript:showHideDiv('view_datas$rand','viewimg','".
         $CFG_GLPI["root_doc"]."/pics/deplier_down.png','".
         $CFG_GLPI["root_doc"]."/pics/deplier_up.png');\">";

      if ($show_graph) {
         $img = "deplier_down.png";
      } else {
         $img = "deplier_up.png";
      }
      echo "<img alt='' name='viewimg' src=\"".
         $CFG_GLPI["root_doc"]."/pics/$img\">&nbsp;";

      echo __("data", 'mreporting')."</a>";
      echo "</th>";
      echo "</tr>";
      echo "</table>";

      $visibility = $show_graph ? "display:none;" : "display:inline;";
      echo "<div align='center' style='".$visibility."' id='view_datas$rand'>";
      echo "<table class='tab_cadre' width='90%'>";

      echo "<tr class='tab_bg_1'>";
      if (!($treedatas)) {
         echo "<th></th>";
      }
      foreach ($datas as $label => $cols) {
         if (!empty($labels2)) {
            echo "<th>".$labels2[$label]."</th>";
         } else {
            echo "<th>".$label."</th>";
         }
      }
      echo "</tr>";
      if ($treedatas) {
         echo "<tr class='tab_bg_1'>";
         self::showGraphTreeDatas($types, $flip_data);
         echo "</tr>";
      } else {
         foreach ($types as $label2 => $cols) {
            echo "<tr class='tab_bg_1'>";
            echo "<td>".$label2."</td>";
            if ($simpledatas) { //simple array
               echo "<td class='center'>".$cols." ".$unit."</td>";
            } else if ($treedatas) { //multiple array
               self::showGraphTreeDatas($cols, $flip_data);
            } else { //multiple array
               foreach ($cols as $date => $nb) {
                  if (!is_array($nb)) {
                     echo "<td class='center'>".$nb." ".$unit."</td>";
                  }
               }
            }
                     echo "</tr>";
         }
      }

      echo "</table>";
      echo "</div><br>";
   }

   static function showGraphTreeDatas($cols, $flip_data = false) {
      if ($flip_data != true) {
         arsort($cols);
         foreach ($cols as $label => $value) {
            echo "<tr class='tab_bg_1'>";
            echo "<th class='center'>$label</th>";
            echo "<td class='center'>";
            if (is_array($value)) {
               echo "<table class='tab_cadre' width='90%'>";
               self::showGraphTreeDatas($value);
               echo "</table>";
            } else {
               echo $value;
            }
            echo "</td></tr>";
         }
      } else {
         foreach ($cols as $label => $value) {
            echo "<tr class='tab_bg_1'>";
            echo "<th class='center'>$label</th>";
            echo "<td class='center'>";
            if (is_array($value)) {
               echo "<table class='tab_cadre' width='90%'>";
               self::showGraphTreeDatas($value, true);
               echo "</table>";
            } else {
               echo $value;
            }
            echo "</td></tr>";
         }

      }
   }

   /**
    * Launch export of datas
    *
    * @param $opt
   */
   function export($opt) {
      global $LANG;

      switch ($opt['switchto']) {
         default:
         case 'png':
            $graph = new PluginMreportingGraphpng();
            //check the format display charts configured in glpi
            $opt = $this->initParams($opt, true);
            $opt['export']    = 'png';
            $opt['withdata']  = 1;
            break;
         case 'csv':
            $graph = new PluginMreportingGraphcsv();
            $opt['export']    = 'csv';
            $opt['withdata']  = 1;
            break;
         case 'odt':
            $graph = new PluginMreportingGraphpng();
            $opt = $this->initParams($opt, true);
            $opt['export'] = 'odt';
            break;
         case 'odtall':
            $graph = new PluginMreportingGraphpng();
            $opt = $this->initParams($opt, true);
            $opt['export'] = 'odtall';
            break;
      }

      //export all with odt
      if (isset($opt['classname'])) {
         if (isset($opt['check'])) {
            unset($_SESSION['glpi_plugin_mreporting_odtarray']);

            $reports = $this->getAllReports(false, $opt);

            foreach ($reports as $classname => $report) {
               foreach ($report['functions'] as $func) {
                  foreach ($opt['check'] as $do=>$to) {
                     if ($do == $func['function'].$classname) {
                        //dynamic instanciation of class passed by 'short_classname' GET parameter
                        $config = PluginMreportingConfig::initConfigParams($func['function'], $classname);
                        $class = 'PluginMreporting'.$func['short_classname'];
                        $obj = new $class($config);
                        $randname = $classname.$func['function'];
                        if (isset($opt['date1']) && isset($opt['date2'])) {

                           $s = strtotime($opt['date2'])-strtotime($opt['date1']);

                           // If customExportDates exists in class : we configure the dates
                           if (method_exists($obj, 'customExportDates')) {
                              $opt = $obj->customExportDates($opt, $func['function']);
                           }

                           $_REQUEST['date1'.$randname] = $opt['date1'];
                           $_REQUEST['date2'.$randname] = $opt['date2'];
                        }

                        //dynamic call of method passed by 'f_name'
                        //GET parameter with previously instancied class
                        $method = $func['function'];
                        $datas = $obj->$method($config);

                        //show graph (pgrah type determined by
                        //first entry of explode of camelcase of function name
                        $title_func = $LANG['plugin_mreporting'][$func['short_classname']][$func['function']]['title'];

                        $des_func = "";
                        if (isset($LANG['plugin_mreporting'][$func['short_classname']][$func['function']]['desc'])) {
                           $des_func = $LANG['plugin_mreporting'][$func['short_classname']][$func['function']]['desc'];
                        }
                        if (isset($LANG['plugin_mreporting'][$func['short_classname']][$func['function']]['desc'])
                              &&isset($opt['date1'])
                                 && isset($opt['date2'])) {
                           $des_func.= " - ";
                        }

                        if (isset($opt['date1'])
                              && isset($opt['date2'])) {
                           $des_func.= Html::convdate($opt['date1'])." / ".
                              Html::convdate($opt['date2']);
                        }
                        $options = ["short_classname" => $func['short_classname'],
                                    "f_name" => $func['function'],
                                    "class" => $opt['classname'],
                                    "gtype" => $func['gtype'],
                                    "randname" => $randname,
                                    "withdata"   => $opt['withdata']];

                        $show_label = 'always';

                        $params = ["raw_datas"  => $datas,
                                   "title"      => $title_func,
                                   "desc"       => $des_func,
                                   "export"     => $opt['export'],
                                   "opt"        => $options];

                        $graph->{'show'.$func['gtype']}($params);
                     }
                  }
               }
            }
            if (isset($_SESSION['glpi_plugin_mreporting_odtarray']) &&
                  !empty($_SESSION['glpi_plugin_mreporting_odtarray'])) {

               if (PluginMreportingPreference::atLeastOneTemplateExists()) {
                  $template = PluginMreportingPreference::checkPreferenceTemplateValue(Session::getLoginUserID());
                  if ($template) {
                     self::generateOdt($_SESSION['glpi_plugin_mreporting_odtarray']);
                  } else {
                     Html::popHeader(__("General Report - ODT", 'mreporting'), $_SERVER['PHP_SELF']);
                     echo "<div class='center'><br>".__("Please, select a model in your preferences", 'mreporting')."<br><br>";
                     Html::displayBackLink();
                     echo "</div>";
                     Html::popFooter();
                  }
               } else {
                  Html::popHeader(__("General Report - ODT", 'mreporting'), $_SERVER['PHP_SELF']);
                  echo "<div class='center'><br>".__("No model available", 'mreporting')."<br><br>";
                  Html::displayBackLink();
                  echo "</div>";
                  Html::popFooter();
               }
            }
         } else { //no selected data
            Html::popHeader(__("General Report - ODT", 'mreporting'), $_SERVER['PHP_SELF']);
            echo "<div class='center'><br>".__("No graphic selected", 'mreporting')."<br><br>";
            Html::displayBackLink();
            echo "</div>";
            Html::popFooter();
         }

      } else {
         $config = PluginMreportingConfig::initConfigParams($opt['f_name'],
         "PluginMreporting".$opt['short_classname']);

         // get periods selected
         self::getSelectorValuesByUser();

         //dynamic instanciation of class passed by 'short_classname' GET parameter
         $classname = 'PluginMreporting'.$opt['short_classname'];
         $obj = new $classname($config);

         //dynamic call of method passed by 'f_name' GET parameter with previously instancied class
         $datas = $obj->{$opt['f_name']}($config);

         //show graph (pgrah type determined by first entry of explode of camelcase of function name
         $title_func = $LANG['plugin_mreporting'][$opt['short_classname']][$opt['f_name']]['title'];
         $des_func = "";
         if (isset($LANG['plugin_mreporting'][$opt['short_classname']][$opt['f_name']]['desc'])) {
            $des_func = $LANG['plugin_mreporting'][$opt['short_classname']][$opt['f_name']]['desc'];
         }
         if (isset($LANG['plugin_mreporting'][$opt['short_classname']][$opt['f_name']]['desc'])
               && isset($_REQUEST['date1'.$opt['randname']])
               && isset($_REQUEST['date2'.$opt['randname']])) {
            $des_func.= " - ";
         }
         if (isset($_REQUEST['date1'.$opt['randname']])
               && isset($_REQUEST['date2'.$opt['randname']])) {
            $des_func.= Html::convdate($_REQUEST['date1'.$opt['randname']]).
                        " / ".Html::convdate($_REQUEST['date2'.$opt['randname']]);
         }

         $show_label = 'always';

         $opt['class'] = $classname;
         $params = ["raw_datas"  => $datas,
                    "title"      => $title_func,
                    "desc"       => $des_func,
                    "export"     => $opt['export'],
                    "opt"        => $opt];

         $graph->{'show'.$opt['gtype']}($params);
      }
   }

   static function generateOdt($params) {
      global $LANG;

      $config = ['PATH_TO_TMP' => GLPI_DOC_DIR . '/_tmp'];

      $category    = '';
      $description = '';
      $short_classname = str_replace('PluginMreporting', '', $params[0]['class']);
      if (isset($LANG['plugin_mreporting'][$short_classname]['title'])) {
         $category    = $LANG['plugin_mreporting'][$short_classname]['title'];
         $description = $LANG['plugin_mreporting'][$short_classname][$params[0]['f_name']]['desc'];
      }

      $odf = new Odf("../templates/template.odt", $config);
      $odf->setVars('category', $category, ENT_NOQUOTES, "utf-8");
      $odf->setVars('title', $params[0]['title'], ENT_NOQUOTES, "utf-8");
      $odf->setVars('description', $description, ENT_NOQUOTES, "utf-8");

      $path = GLPI_PLUGIN_DOC_DIR."/mreporting/" . $params[0]['f_name'] . ".png";

      if (is_file($path)) {
         list($image_width, $image_height) = @getimagesize($path);
         $image_width *= Odf::PIXEL_TO_CM;
         $image_height *= Odf::PIXEL_TO_CM * 17 / $image_width;
         $odf->setImage('image', $path, -1, 17, $image_height);
      } else {
         $odf->setVars('image', "", true, 'UTF-8');
      }

      $singledatas   = $odf->setSegment('singledatas');
      $multipledatas = $odf->setSegment('multipledatas');

      if ($params[0]['withdata']) {
         $datas       = $params[0]['raw_datas']['datas'];
         $first       = $datas;
         $first       = array_shift($first);
         $is_multiple = is_array($first);

         // Multidatas graph
         if ($is_multiple) {
            $multipledatas->setVars('datas_title', mb_strtoupper(__('data', 'mreporting')), ENT_NOQUOTES, "utf-8");

            foreach ($datas as $key => $value) {
               $multipledatas->subtitle->datas_subtitle(mb_strtoupper($key), ENT_NOQUOTES, "utf-8");
               $multipledatas->subtitle->merge();

               foreach ($value as $col => $val) {
                  $multipledatas->datas->row($col, ENT_NOQUOTES, "utf-8");
                  $multipledatas->datas->value($val, ENT_NOQUOTES, "utf-8");
                  $multipledatas->datas->merge();
               }
               $multipledatas->merge();
            }

            // Simples graph
         } else {
            $singledatas->setVars('datas_title', mb_strtoupper(__('data', 'mreporting')), ENT_NOQUOTES, "utf-8");
            foreach ($datas as $key => $value) {
               $singledatas->datas->row($key, ENT_NOQUOTES, "utf-8");
               $singledatas->datas->value($value, ENT_NOQUOTES, "utf-8");
               $singledatas->datas->merge();
            }
            $singledatas->merge();
         }

      }
      $odf->mergeSegment($singledatas);
      $odf->mergeSegment($multipledatas);
      $odf->exportAsAttachedFile();
   }

   // === SELECTOR FUNCTIONS ====

   static function selectorForMultipleGroups($field, $condition = [], $label = '') {
      global $DB;

      echo "<br /><b>".$label." : </b><br />";

      $values = [];
      if (isset($_SESSION['mreporting_values'][$field])) {
         if (is_array($_SESSION['mreporting_values'][$field])) { //if link in from dashboard
            $values = $_SESSION['mreporting_values'][$field];
         } else {
            $values = [$_SESSION['mreporting_values'][$field]];
         }
      }

      $datas = [];
      foreach (getAllDataFromTable('glpi_groups', $condition, false, 'name') as $data) {
         $datas[$data['id']] = $data['completename'];
      }

      $param = ['multiple' => true,
                'display'  => true,
                'size'     => count($values),
                'values'   => $values];

      Dropdown::showFromArray($field, $datas, $param);
   }

   static function selectorForSingleGroup($field, $condition = [], $label = '') {
      echo "<br /><b>".$label." : </b><br />";

      $value = isset($_SESSION['mreporting_values'][$field]) ? $_SESSION['mreporting_values'][$field] : 0;

      Dropdown::show("Group", ['comments'  => false,
                               'name'      => $field,
                               'value'     => $value,
                               'condition' => $condition]);
   }


   static function selectorGrouprequest() {
      self::selectorForSingleGroup(
         'groups_request_id',
         ['is_requester' => 1],
         __("Requester group")
      );
   }

   static function selectorGroupassign() {
      self::selectorForSingleGroup(
         'groups_assign_id',
         ['is_assign' => 1],
         __("Group in charge of the ticket")
      );
   }

   static function selectorMultipleGrouprequest() {
      self::selectorForMultipleGroups(
         'groups_request_id',
         ['is_requester' => '1'],
         __("Requester group")
      );
   }

   static function selectorMultipleGroupassign() {
      self::selectorForMultipleGroups(
         'groups_assign_id',
         ['is_assign' => '1'],
         __("Group in charge of the ticket")
      );
   }

   static function selectorUserassign() {
      echo "<br /><b>".__("Technician in charge of the ticket")." : </b><br />";
      $options = ['name'        => 'users_assign_id',
                  'entity'      => isset($_SESSION['glpiactive_entity']) ? $_SESSION['glpiactive_entity'] : 0,
                  'right'       => 'own_ticket',
                  'value'       => isset($_SESSION['mreporting_values']['users_assign_id']) ? $_SESSION['mreporting_values']['users_assign_id'] : 0,
                  'ldap_import' => false,
                  'comments'    => false];
      User::dropdown($options);
   }
   /**
    * Show the selector for tickets status (new, open, solved, closed...)
    */
   static function selectorAllSlasWithTicket() {
      global $LANG, $DB;

      echo "<b>" . $LANG['plugin_mreporting']['Helpdeskplus']['selector']["slas"] . " : </b><br />";

      $query = "SELECT DISTINCT s.id,
         s.name
      FROM glpi_slas s
      INNER JOIN glpi_tickets t ON s.id = t.slas_id_ttr
      WHERE t.status IN (" . implode(
            ',',
            array_merge(Ticket::getSolvedStatusArray(), Ticket::getClosedStatusArray())
         ) . ")
      AND t.is_deleted = '0'
      ORDER BY s.name ASC";
      $result = $DB->query($query);

      $values = [];
      while ($data = $DB->fetchAssoc($result)) {
         $values[$data['id']] = $data['name'];
      }

      $selected_values = isset($_SESSION['mreporting_values']['slas']) ? $_SESSION['mreporting_values']['slas'] : [];

      Dropdown::showFromArray('slas', $values, ['values' => $selected_values,
                                                'multiple' => true,
                                                'readonly' => false]);
   }

   static function selectorPeriod($period = "day") {
      global $LANG;

      echo '<b>'.$LANG['plugin_mreporting']['Helpdeskplus']['period'].' : </b><br />';

      $elements = [
         'day'    => _n("Day", "Days", 1),
         'week'   => __("Week", 'mreporting'),
         'month'  => __("Month", 'mreporting'),
         'year'   => __("By year")];

      Dropdown::showFromArray("period", $elements,
                              ['value' => isset($_SESSION['mreporting_values']['period'])
                                 ? $_SESSION['mreporting_values']['period'] : 'month']);
   }

   static function selectorType() {
      echo "<br /><b>"._n("Type", "Types", 1) ." : </b><br />";
      Ticket::dropdownType('type',
                           ['value' => isset($_SESSION['mreporting_values']['type'])
                              ? $_SESSION['mreporting_values']['type'] : Ticket::INCIDENT_TYPE]);

   }

   static function selectorCategory($type = true) {
      global $CFG_GLPI;

      echo "<br /><b>"._n('Ticket category', 'Ticket categories', 2) ." : </b><br />";
      if ($type) {
         $rand = Ticket::dropdownType('type',
                                      ['value' => isset($_SESSION['mreporting_values']['type'])
                                                  ? $_SESSION['mreporting_values']['type']
                                                  : Ticket::INCIDENT_TYPE,
                                       'toadd' => [-1 => __('All')]]);
         $params = ['type'            => '__VALUE__',
                    'currenttype'     => Ticket::INCIDENT_TYPE,
                    'entity_restrict' => -1,
                    'condition'       => ['is_incident' => 1],
                    'value'           => isset($_SESSION['mreporting_values']['itilcategories_id'])
                                         ? $_SESSION['mreporting_values']['itilcategories_id']
                                         : 0];
         echo "<span id='show_category_by_type'>";
      }

      $params['comments'] = false;
      ITILCategory::dropdown($params);

      if ($type) {
         echo "</span>";

         Ajax::updateItemOnSelectEvent("dropdown_type$rand", "show_category_by_type",
                                       $CFG_GLPI["root_doc"]."/ajax/dropdownTicketCategories.php",
                                       $params);
      }
   }

   static function selectorLimit() {
      echo "<b>".__("Maximal count")." :</b><br />";

      Dropdown::showListLimit(); // glpilist_limit
   }


   static function selectorAllstates() {
      echo "<br><b>"._n('Status', 'Statuses', 2)." : </b><br />";
      $default = [CommonITILObject::INCOMING,
                  CommonITILObject::ASSIGNED,
                  CommonITILObject::PLANNED,
                  CommonITILObject::WAITING];

      $i = 1;
      foreach (Ticket::getAllStatusArray() as $value => $name) {
         echo '<label>';
         echo '<input type="hidden" name="status_'.$value.'" value="0" /> ';
         echo '<input type="checkbox" name="status_'.$value.'" value="1"';
         if ((isset($_SESSION['mreporting_values']['status_'.$value])
            && ($_SESSION['mreporting_values']['status_'.$value] == '1'))
               || (!isset($_SESSION['mreporting_values']['status_'.$value])
                  && in_array($value, $default))) {
            echo ' checked="checked"';
         }
         echo ' /> ';
         echo $name;
         echo '</label>';
         if ($i%3 == 0) {
            echo "<br />";
         }
         $i++;
      }
   }

   static function selectorDateinterval() {
      $randname = 'PluginMreporting'.$_REQUEST['short_classname'].$_REQUEST['f_name'];

      if (!isset($_SESSION['mreporting_values']['date1'.$randname])) {
         $_SESSION['mreporting_values']['date1'.$randname] = strftime("%Y-%m-%d", time() - (365 * 24 * 60 * 60));
      }
      if (!isset($_SESSION['mreporting_values']['date2'.$randname])) {
         $_SESSION['mreporting_values']['date2'.$randname] = strftime("%Y-%m-%d");
      }

      $date1 = $_SESSION['mreporting_values']["date1".$randname];
      echo "<b>".__("Start date")."</b><br />";
      Html::showDateField("date1$randname", ['value'      => $date1,
                                             'maybeempty' => false]);
      echo "</td>";

      $date2 = $_SESSION['mreporting_values']["date2".$randname];
      echo "<td>";
      echo "<b>".__("End date")."</b><br />";
      Html::showDateField("date2$randname", ['value'      => $date2,
                                             'maybeempty' => false]);
   }

   /**
    * Show entity level selector.
    * @return display selector
    */
   static function selectorEntityLevel() {
      global $DB;

      echo "<b>".__('Max depth entity level', 'mreporting')." :</b><br />";

      $default_level = self::getActiveEntityLevel();
      if (isset($_SESSION['mreporting_values']['entitylevel'])) {
         $selected = $_SESSION['mreporting_values']['entitylevel'];
      } else {
         $selected = $default_level;
      }

      $values = [$default_level];
      $maxlevel = self::getMaxEntityLevel();
      for ($i=($default_level+1); $i<=$maxlevel; $i++) {
         $values[$i] = $i;
      }

      return Dropdown::showFromArray('entitylevel', $values, ['value'  => $selected]);
   }

   /**
    * Get SQL condition to filter entity depth by level.
    * @param  string  $field     the sql table field to compare
    * @return string sql condition
    */
   static function getSQLEntityLevel($field = "`glpi_entities`.`level`") {

      if (isset($_SESSION['mreporting_values']['entitylevel'])) {
         $maxlevel = $_SESSION['mreporting_values']['entitylevel'];
      } else {
         $maxlevel = self::getMaxEntityLevel();
      }

      $default_level = self::getActiveEntityLevel();

      $where_entities_level = "({$field} = {$default_level}";
      for ($i=($default_level+1); $i<=$maxlevel; $i++) {
         $where_entities_level.= " OR {$field} = {$i}";
      }
      $where_entities_level.= ")";

      return $where_entities_level;
   }

   /**
    * Get active entity level according to GLPi SESSION
    * @return integer default entity level
    */
   static function getActiveEntityLevel() {

      if (isset($_SESSION['glpiactive_entity'])) {
         $Entity = new Entity();
         $Entity->getFromDB($_SESSION['glpiactive_entity']);
         return $Entity->fields['level'];
      } else {
         return 0;
      }
   }

   /**
    * Get max entity level according to GLPi SESSION current active entity
    * @return integer max entity level
    */
   static function getMaxEntityLevel() {
      global $DB;

      if (count($_SESSION['glpiactiveentities']) > 1) {
         $restrict = " `id` IN ({$_SESSION['glpiactiveentities_string']})";
      } else {
         $restrict = " `id` = {$_SESSION['glpiactiveentities_string']}";
      }

      $query = "SELECT MAX(level) AS 'maxlevel'
                  FROM glpi_entities
                  WHERE {$restrict}";

      $result = $DB->query($query);
      if ($DB->numrows($result) > 0) {
         return $DB->result($result, 0, "maxlevel");
      } else {
         return 0;
      }
   }

   static function canAccessAtLeastOneReport($profiles_id) {
      return countElementsInTable("glpi_plugin_mreporting_profiles",
                                  ['profiles_id' => $profiles_id, 'right' => READ]);
   }

   static function showNavigation() {
      echo "<div class='center'>";
      echo "<a href='central.php'>".__("Back")."</a>";
      echo "</div>";
   }

   /**
    * Transform a request var into a get string
    * @param  array $var the request string ($_REQUEST, $_POST, $_GET)
    * @return string the imploded array. Format : $key=$value&$key2=$value2...
    */
   static function getRequestString($var) {
      unset($var['submit']);

      // For have clean URL (best practice)
      if (isset($var['reset'])) {
         unset($var['reset']);
      }

      return http_build_query($var);
   }


   /**
    * Show a date selector
    * @param  datetime $date1    date of start
    * @param  datetime $date2    date of ending
    * @param  string $randname random string (to prevent conflict in js selection)
    * @return nothing
    */
   static function showSelector($date1, $date2, $randname) {
      global $CFG_GLPI;

      if (!isset($_REQUEST['f_name'])) {
         $has_selector = false; //Security
      } else {
         $has_selector = (isset($_SESSION['mreporting_selector'][$_REQUEST['f_name']]));
      }

      echo "<div class='center'>";
      $request_string = self::getRequestString($_GET);
      echo "<form method='POST' action='?$request_string' name='form' id='mreporting_date_selector'>";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_1'>";

      if ($has_selector) {
         self::getReportSelectors();
      }

      echo "<td colspan='2' class='center'>";
      if ($has_selector) {
         echo "<input type='submit' class='submit' name='submit' value=\"". _sx('button', 'Post') ."\">";
      }
      $_SERVER['REQUEST_URI'] .= "&date1".$randname."=".$date1."&date2".$randname."=".$date2;

      SavedSearch::showSaveButton(SavedSearch::URI, __CLASS__);

      //If there's no selector for the report, there's no need for a reset button !
      if ($has_selector) {
         echo "<a href='?$request_string&reset=reset'>&nbsp;&nbsp;";
         echo "<img title=\"".__s('Blank')."\" alt=\"".__s('Blank')."\" src='".
               $CFG_GLPI["root_doc"]."/pics/reset.png' class='calendrier'>";
         echo "</a>";
      }
      echo "</td>";
      echo "</tr>";

      echo "</table>";
      Html::closeForm();

      echo "</div>";

      unset($_SESSION['mreporting_selector']);
   }

   /**
    * Parse and include selectors functions
    */
   static function getReportSelectors($export = false) {
      ob_start();
      self::addToSelector();
      $graphname = $_REQUEST['f_name'];
      if (!isset($_SESSION['mreporting_selector'][$graphname])
         || empty($_SESSION['mreporting_selector'][$graphname])) {
         return;
      }

      $classname = 'PluginMreporting'.$_REQUEST['short_classname'];
      if (!class_exists($classname)) {
         return;
      }

      $i = 1;
      foreach ($_SESSION['mreporting_selector'][$graphname] as $selector) {
         if ($i%4 == 0) {
            echo '</tr><tr class="tab_bg_1">';
         }
         $selector = 'selector'.ucfirst($selector);
         if (method_exists('PluginMreportingCommon', $selector)) {
            $classselector = 'PluginMreportingCommon';
         } else if (method_exists($classname, $selector)) {
            $classselector = $classname;
         } else {
            continue;
         }

         $i++;
         echo '<td>';
         $classselector::$selector();
         echo '</td>';
      }
      while ($i%4 != 0) {
         $i++;
         echo '<td>&nbsp;</td>';
      }

      $content = ob_get_clean();

      if ($export) {
         return $content;
      }
      echo $content;
   }

   static function saveSelectors($graphname, $config = []) {
      $remove = ['short_classname', 'f_name', 'gtype', 'submit'];
      $values = [];

      foreach ($_REQUEST as $key => $value) {
         if (!preg_match("/^_/", $key) && !in_array($key, $remove)) {
            $values[$key] = $value;
         }
         if (empty($value)) {
            unset($_REQUEST[$key]);
         }
      }

      //clean unmodified date
      if (isset($config['randname'])) {
         if (isset($_REQUEST['date1'.$config['randname']])
            && $_REQUEST['date1'.$config['randname']]
               == $_SESSION['mreporting_values']['date1'.$config['randname']]) {
            unset($_REQUEST['date1'.$config['randname']]);
         }
         if (isset($_REQUEST['date2'.$config['randname']])
            && $_REQUEST['date2'.$config['randname']]
               == $_SESSION['mreporting_values']['date2'.$config['randname']]) {
            unset($_REQUEST['date2'.$config['randname']]);
         }
      }

      if (!empty($values)) {
         $pref = new PluginMreportingPreference();
         $id = $pref->addDefaultPreference(Session::getLoginUserID());
         $tmp['id'] = $id;

         $pref->getFromDB($id);

         if (!is_null($pref->fields['selectors'])) {
            $selectors = $pref->fields['selectors'];
            $sel = json_decode(stripslashes($selectors), true);
            $sel[$graphname] = $values;
         } else {
            $sel = $values;
         }
         $tmp['selectors'] = addslashes(json_encode($sel));
         $pref->update($tmp);
      }
      $_SESSION['mreporting_values'] = $values;
   }

   static function getSelectorValuesByUser() {
      global $DB;

      $myvalues  = (isset($_SESSION['mreporting_values'])?$_SESSION['mreporting_values']:[]);
      $selectors = PluginMreportingPreference::checkPreferenceValue('selectors', Session::getLoginUserID());
      if ($selectors) {
         $values = json_decode(stripslashes($selectors), true);
         if (isset($values[$_REQUEST['f_name']])) {
            foreach ($values[$_REQUEST['f_name']] as $key => $value) {
               $myvalues[$key] = $value;
            }
         }
      }
      $_SESSION['mreporting_values'] = $myvalues;
   }

   static function addToSelector() {
      foreach ($_REQUEST as $key => $value) {
         if (!isset($_SESSION['mreporting_values'][$key])) {
             $_SESSION['mreporting_values'][$key] = $value;
         }
      }
   }

   static function resetSelectorsForReport($report_name) {
      global $DB;

      $users_id = Session::getLoginUserID();
      $selectors = PluginMreportingPreference::checkPreferenceValue('selectors', $users_id);

      if ($selectors) {
         $values = json_decode(stripslashes($selectors), true);
         if (isset($values[$report_name])) {
            unset($values[$report_name]);
         }
         $selector = addslashes(json_encode($values));

         $query = "UPDATE `glpi_plugin_mreporting_preferences`
                   SET `selectors`='$selector'
                   WHERE `users_id`='$users_id'";
         $DB->query($query);
      }
   }

   /**
    * Generate a SQL date test with $_REQUEST date fields
    * @param  string  $field     the sql table field to compare
    * @param  integer $delay     if $_REQUET date fields not provided,
    *                            generate them from $delay (in days)
    * @param  string $randname   random string (to prevent conflict in js selection)
    * @return string             The sql test to insert in your query
    */
   static function getSQLDate($field = "`glpi_tickets`.`date`", $delay = 365, $randname = '') {

      if (empty($_SESSION['mreporting_values']['date1'.$randname])) {
         $_SESSION['mreporting_values']['date1'.$randname] = strftime("%Y-%m-%d", time() - ($delay * 24 * 60 * 60));
      }
      if (empty($_SESSION['mreporting_values']['date2'.$randname])) {
         $_SESSION['mreporting_values']['date2'.$randname] = strftime("%Y-%m-%d");
      }

      $date_array1=explode("-", $_SESSION['mreporting_values']['date1'.$randname]);
      $time1=mktime(0, 0, 0, $date_array1[1], $date_array1[2], $date_array1[0]);

      $date_array2=explode("-", $_SESSION['mreporting_values']['date2'.$randname]);
      $time2=mktime(0, 0, 0, $date_array2[1], $date_array2[2], $date_array2[0]);

      //if data inverted, reverse it
      if ($time1 > $time2) {
         list($time1, $time2) = [$time2, $time1];
         list($_SESSION['mreporting_values']['date1'.$randname],
            $_SESSION['mreporting_values']['date2'.$randname]) = [
            $_SESSION['mreporting_values']['date2'.$randname],
            $_SESSION['mreporting_values']['date1'.$randname]
            ];
      }

      $begin=date("Y-m-d H:i:s", $time1);
      $end=date("Y-m-d H:i:s", $time2);

      return "($field >= '$begin' AND $field <= ADDDATE('$end', INTERVAL 1 DAY) )";
   }


   /**
    * Get the max value of a multidimensionnal array
    * @param  array() $array the array to compute
    * @return number the sum
    */
   static function getArrayMaxValue($array) {
      $max = 0;

      if (!is_array($array)) {
         return $array;
      }

      foreach ($array as $value) {
         if (is_array($value)) {
            $sub_max = self::getArrayMaxValue($value);
            if ($sub_max > $max) {
               $max = $sub_max;
            }
         } else {
            if ($value > $max) {
               $max = $value;
            }
         }
      }

      return $max;
   }


   /**
    * Computes the sum of a multidimensionnal array
    * @param  array() $array the array where to seek
    * @return number the sum
    */
   static function getArraySum($array) {
      $sum = 0;

      if (!is_array($array)) {
         return $array;
      }

      foreach ($array as $value) {
         if (is_array($value)) {
            $sum+= self::getArraySum($value);
         } else {
            $sum+= $value;
         }
      }

      return $sum;
   }


   /**
    * Get the depth of a multidimensionnal array
    * @param  array() $array the array where to seek
    * @return number the sum
    */
   static function getArrayDepth($array) {
      $max_depth = 1;

      foreach ($array as $value) {
         if (is_array($value)) {
            $depth = self::getArrayDepth($value) + 1;

            if ($depth > $max_depth) {
               $max_depth = $depth;
            }
         }
      }

      return $max_depth;
   }


   /**
    * Transform a flat array to a tree array
    * @param  array $flat_array the flat array. Format : array('id', 'parent', 'name', 'count')
    * @return array the tree array. Format : array(name => array(name2 => array(count), ...)
    */
   static function buildTree($flat_array) {
      $raw_tree = self::mapTree($flat_array);
      $tree = self::cleanTree($raw_tree);
      return $tree;
   }


   /**
    * Transform a flat array to a tree array (without keys changes)
    * @param  array $flat_array the flat array. Format : array('id', 'parent', 'name', 'count')
    * @return array the tree array. Format : array(orginal_keys, children => array(...)
    */
   static function mapTree(array &$elements, $parentId = 0) {
      $branch = [];

      foreach ($elements as $element) {
         if (isset($element['parent']) && $element['parent'] == $parentId) {
            $children = self::mapTree($elements, $element['id']);
            if ($children) {
               $element['children'] = $children;
            }
            $branch[$element['id']] = $element;
         }
      }
      return $branch;
   }


   /**
    * Transform a tree array to a tree array (with clean keyss)
    * @param  array $flat_array the tree array.
    *               Format : array('id', 'parent', 'name', 'count', children => array(...)
    * @return array the tree array.
    *               Format : array(name => array(name2 => array(count), ...)
    */
   static function cleanTree($raw_tree) {
      $tree = [];

      foreach ($raw_tree as $id => $node) {
         if (isset($node['children'])) {
            $sub = self::cleanTree($node['children']);

            if ($node['count'] > 0) {
               $current = [$node['name'] => intval($node['count'])];
               $tree[$node['name']] = array_merge($current, $sub);
            } else {
               $tree[$node['name']] = $sub;
            }
         } else {
            $tree[$node['name']] = intval($node['count']);
         }
      }

      return $tree;
   }

   static function getReportIcon($report_name) {
      //see font-awesome : http://fortawesome.github.io/Font-Awesome/cheatsheet/
      $icons = [
         'pie'       => "&#xf200",
         'hbar'      => "&#xf036;",
         'hgbar'     => "&#xf036;",
         'line'      => "&#xf201;",
         'gline'     => "&#xf201;",
         'area'      => "&#xf1fe;",
         'garea'     => "&#xf1fe;",
         'vstackbar' => "&#xf080;",
         'sunburst'  => "&#xf185;",
      ];

      $extract    = preg_split('/(?<=\\w)(?=[A-Z])/', $report_name);
      $chart_type = strtolower($extract[1]);

      return $icons[$chart_type];
   }

   static function getIcon() {
      return 'fa fa-chart-pie';
   }
}
