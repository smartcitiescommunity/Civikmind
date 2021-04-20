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

class PluginMreportingGraph {

   const DEBUG_GRAPH = false;
   protected $width  = 850;

   /**
    * init Graph : Show Titles / Date selector
    *
    * @params $options ($rand, short_classname, title, desc, delay)
   */
   function initGraph($options) {
      global $LANG, $CFG_GLPI;

      $width        = $this->width + 100;
      $randname     = $options['randname'];

      if (!$options['showHeader']) {
         echo "<div class='center'><div id='fig' style='width:{$width}px'>";
         //Show global title
         if (isset($LANG['plugin_mreporting'][$options['short_classname']]['title'])) {
            echo "<div class='graph_title'>";
            echo $LANG['plugin_mreporting'][$options['short_classname']]['title'];
            echo "</div>";
         }
         //Show graph title
         echo "<div class='graph_title'>";
         $gtype = $_REQUEST['gtype'];

         echo "<img src='".Plugin::getWebDir('mreporting')."/pics/chart-$gtype.png' class='title_pics' />";
         echo $options['title'];
         echo "</div>";

         $desc = '';
         if (!empty($options['desc'])) {
            $desc =$options['desc'];
            if (isset($_SESSION['mreporting_values']['date1'.$randname])
                  && isset($_SESSION['mreporting_values']['date1'.$randname])) {
               $desc.= " - ";
            }
         }

         if (isset($_SESSION['mreporting_values']['date1'.$randname])
               && isset($_SESSION['mreporting_values']['date1'.$randname])) {
            $desc.= Html::convdate($_SESSION['mreporting_values']['date1'.$randname])." / ".
               Html::convdate($_SESSION['mreporting_values']['date2'.$randname]);
         }
         echo "<div class='graph_desc'>".$desc."</div>";

         //Show date selector
         echo "<div class='graph_navigation'>";
         PluginMreportingCommon::showSelector(
            $_SESSION['mreporting_values']['date1'.$randname],
            $_SESSION['mreporting_values']['date2'.$randname],
            $randname);
         echo "</div>";

         $ex_func = explode($options['short_classname'], $options['randname']);
         if (!is_numeric($ex_func[0])) {
            $classname = $ex_func[0].$options['short_classname'];
            $functionname = $ex_func[1];

            $config = PluginMreportingConfig::initConfigParams($functionname, $classname);

            // We check if a configuration is needed for the graph
            if (method_exists(new $classname($config), 'needConfig')) {
               $object = new $classname();
               $object->needConfig($config);
            }
         }
      }

      //Script for graph display
      if ($randname !== false) {
         echo "<div class='graph' id='graph_content$randname'>";

         $colors = "'".implode ("', '", PluginMreportingConfig::getColors())."'";
         echo "<script type='text/javascript+protovis'>
            showGraph$randname = function() {
               colors = pv.colors($colors);";
      }
   }

   /**
    * Show an horizontal bar chart
    *
    * @param $raw_datas : an array with :
    *    - key 'datas', ex : array( 'test1' => 15, 'test2' => 25)
    *    - key 'unit', ex : '%', 'Kg' (optionnal)
    * @param $title : title of the chart
    * @param $desc : description of the chart (optionnal)
    * @param $show_label : behavior of the graph labels,
    *                      values : 'hover', 'never', 'always' (optionnal)
    * @param $export : keep only svg to export (optionnal)
    * @return nothing
    */
   function showHbar($params, $dashboard = false, $width = false) {

      ob_start();
      if ($width !== false) {
         $this->width = $width;
      }

      $criterias = PluginMreportingCommon::initGraphParams($params);
      foreach ($criterias as $key => $val) {
         $$key=$val;
      }

      $configs = PluginMreportingConfig::initConfigParams($opt['f_name'], $opt['class']);
      foreach ($configs as $k => $v) {
         $$k=$v;
      }

      $options = ["title" => $title,
                  "desc" => $desc,
                  "randname" => $randname,
                  "delay" => $delay,
                  "export" => $export,
                  "short_classname" => $opt["short_classname"],
                  "showHeader" => $dashboard];

      $this->initGraph($options);

      if (!isset($raw_datas['datas'])) {
         echo "}</script>";
         echo __("No data for this date range !", 'mreporting');
         $end['opt']["export"] = false;
         $end['opt']["randname"] = false;
         $end['opt']["f_name"] = $opt['f_name'];
         $end['opt']["class"] = $opt['class'];
         PluginMreportingCommon::endGraph($end, $dashboard);
         return false;
      }

      if (empty($unit) && !empty($raw_datas['unit'])) {
         $unit = $raw_datas['unit'];
      }

      $datas = $raw_datas['datas'];
      $links = [];
      if (isset($raw_datas['links'])) {
         $links = $raw_datas['links'];
      }
      $datas = $this->initDatasSimple($datas, $unit, $links);

      $nb_bar = count($datas);
      $height = 25 * $nb_bar + 50;

      $always = '';
      $hover = '';

      $left = 240;
      if ($dashboard) {
         $left = 180;
         if ($height > 380) {
            $height = 380;
         }
      }

      $always = '';
      $hover = '';
      PluginMreportingConfig::checkVisibility($show_label, $always, $hover);

      $JS = <<<JAVASCRIPT
   var width_hbar = {$this->width};
   var height_hbar = {$height};
   var x = pv.Scale.linear(0, max).range(0, .67 * width_hbar);
   var y = pv.Scale.ordinal(pv.range(n)).splitBanded(0, height_hbar, 4/5);

   var offset = 0;

   vis{$randname} = new pv.Panel()
      .width(width_hbar)
      .height(height_hbar)
      .bottom(20)
      .left({$left})
      .right(0)
      .top(5);

   vis{$randname}.add(pv.Panel)
      .data(datas)
      .top(function() y(this.index))
      .height(y.range().band)
      .add(pv.Panel)
         .def("active", false)
      .add(pv.Bar) // horizontal bar
         .left(0)
         .width(function(d) {
            var r = 360 - 20 * offset;
            if (r < 0) r = 0;
            var len = x(d) - r;
            return len;
         })
         .height(y.range().band)
         .event("mouseover", function() {
            return this.parent.active(true);
         })
         .event("mouseout", function()  {
            return this.parent.active(false);
         })
         .event("click", function() {
            self.open(links[this.parent.parent.index], '_blank');
         })
         .fillStyle(function() {
            if (this.parent.active()) return colors(this.parent.parent.index).alpha(.5);
            else return colors(this.parent.parent.index);
         })
         .strokeStyle(function() {
            return colors(this.parent.parent.index).darker();
         })
         .lineWidth(2)
         .top(2)
         .bottom(2)
      .anchor("right").add(pv.Label) // bar value with unit (on right)
         .textAlign("left")
         .text(function(d) {
            return  d+" {$unit}";
         })
         .textMargin(5)
         .textBaseline("middle")
         .textStyle(function() {
            return colors(this.parent.parent.index).darker();
         })
         .textShadow("0.1em 0.1em 0.1em rgba(4,4,4,.5)")
      .parent.anchor("left").add(pv.Label) // bar label (on left )
         .textMargin(5)
         .textAlign("right")
         .text(function() {
            return labels[this.parent.parent.index];
         })
         .font(function() {
            return (this.parent.active()) ? "bold 11px sans-serif" : "";
         })
      .root.add(pv.Rule) // axis
         .data(x.ticks(5))
         .left(x)
         .strokeStyle(function(d) {
            return d ? "rgba(255,255,255,.3)" : "black";
         })
         .lineWidth(function() {
            return (this.index == 0) ? 2 : 1;
         })
      .add(pv.Rule)
         .bottom(0)
         .height(height_hbar)
         .strokeStyle(function(d) d ? "#eee" : "black")
      .anchor("bottom").add(pv.Label) // x axis labels
         .strokeStyle("black")
         .text(x.tickFormat);

   //render in loop to animate
   var interval = setInterval(function() {
      offset++;
      vis{$randname}.render();
      if (offset > 100) clearInterval(interval);
   }, 20);

JAVASCRIPT;

      if ($show_graph) {
         echo $JS;
      }

      $opt['randname'] = $randname;
      $options = ["opt"     => $opt,
                  "export" => $export,
                  "datas"  => $datas,
                  "unit"   => $unit];
      PluginMreportingCommon::endGraph($options, $dashboard);

      $content = ob_get_clean();

      if ($dashboard) {
         return $content;
      } else {
         echo $content;
      }
   }


   /**
    * Show a pie chart
    *
    * @params :
    * $raw_datas : an array with :
    *    - key 'datas', ex : array( 'test1' => 15, 'test2' => 25)
    *    - key 'unit', ex : '%', 'Kg' (optionnal)
    * @param $title : title of the chart
    * @param $desc : description of the chart (optionnal)
    * @param $show_label : behavior of the graph labels,
    *                      values : 'hover', 'never', 'always' (optionnal)
    * @param $export : keep only svg to export (optionnal)
    * @return nothing
    */
   function showPie($params, $dashboard = false, $width = false) {
      ob_start();
      if ($width !== false) {
         $this->width = $width;
      }

      $criterias = PluginMreportingCommon::initGraphParams($params);

      foreach ($criterias as $key => $val) {
         $$key=$val;
      }

      $configs = PluginMreportingConfig::initConfigParams($opt['f_name'], $opt['class']);

      foreach ($configs as $k => $v) {
         $$k=$v;
      }

      $options = ["title" => $title,
                  "desc" => $desc,
                  "randname" => $randname,
                  "export" => $export,
                  "delay" => $delay,
                  "short_classname" => $opt["short_classname"],
                  "showHeader" => $dashboard];

      $this->initGraph($options);

      if (!isset($raw_datas['datas'])) {
         echo "}</script>";
         echo __("No data for this date range !", 'mreporting');
         $end['opt']["export"] = false;
         $end['opt']["randname"] = false;
         $end['opt']["f_name"] = $opt['f_name'];
         $end['opt']["class"] = $opt['class'];
         PluginMreportingCommon::endGraph($end, $dashboard);
         return false;
      }

      if (empty($unit) && !empty($raw_datas['unit'])) {
         $unit = $raw_datas['unit'];
      }

      $datas = $raw_datas['datas'];
      $datas = $this->initDatasSimple($datas, $unit);

      $nb_bar = count($datas);
      $height = 15 * $nb_bar + 50;
      if ($height < 300) {
         $height = 300;
      }
      $always = '';
      $hover = '';
      $radius = 150;
      $left = 10;
      $right_legend = 5;

      if ($dashboard) {
         $left = 40;
      }

      PluginMreportingConfig::checkVisibility($show_label, $always, $hover);

      $JS = <<<JAVASCRIPT
   var width_pie = {$this->width};
   var height_pie = {$height};
   var radius = {$radius};
   var angle = pv.Scale.linear(0, pv.sum(datas)).range(0, 2 * Math.PI);
   var Hilighted = [false, false,false, false,false, false];

   var offset = 0;

   vis{$randname} = new pv.Panel()
      .left({$left})
      .bottom(5)
      .width(width_pie)
      .height(height_pie + 50)
      .def("o", -1)
      .lineWidth(0)
   vis{$randname}.add(pv.Wedge)
         .top(210)
         .data(datas)
         .outerRadius(radius-40)
         .angle(function(d) {
            var r = max - (max / 2.3) - (max / 80) * offset * 2;
            if (r < 0) r = 0;
            return angle(d - r);
         })
         .left(function() { return (width_pie - 80) / 4
            + Math.cos(this.startAngle() + this.angle() / 2)
            * ((Hilighted[this.index]) ? 20 : 0); })
         .bottom(function() { return height_pie / 2
            - Math.sin(this.startAngle() + this.angle() / 2)
            * ((Hilighted[this.index]) ? 20 : 0); })
         .fillStyle(function() {
            return Hilighted[this.index]? colors(this.index).alpha(.6) : colors(this.index);
         })
         .event("mouseover", function() {
            this.parent.o(this.index) ;
            Hilighted[this.index] = true;
            return vis{$randname};
         })
         .event("mouseout", function() {
            this.parent.o(-1) ;
            Hilighted[this.index] = false;
            return vis{$randname};
         })
         .strokeStyle(function() { return colors(this.index).darker(); })
         .lineWidth(3)
      .add(pv.Wedge) // invisible wedge to offset label
         .visible(false)
         .innerRadius(1.2 * (radius-25))
         .outerRadius(radius-40)
         .fillStyle(null)
         .strokeStyle(null)
         .visible(function(d) { return d > .15; })
      .anchor("center").add(pv.Label)
         .visible(function(d) {
            return (Hilighted[this.index] && {$hover} || {$always}) ? true : false;
         })
         .textAngle(0)
         .textStyle(function() { return colors(this.index).darker(); })
         .text(function() { return datas[this.index]+" {$unit}"; });

   // legend
   vis{$randname}.add(pv.Dot)
      .data(labels)
      .right({$right_legend})
      .top(function(d) { return 5 + this.index * 15; })
      .fillStyle(function() {
         return (this.parent.o() == this.index) ? colors(this.index).alpha(.6) : colors(this.index)
         && Hilighted[this.index]? colors(this.index).alpha(.6) : colors(this.index);
      })
      .event("mouseover", function() {Hilighted[this.index] = true; return vis{$randname};})
      .event("mouseout", function() { Hilighted[this.index] = false; return vis{$randname};})
      .strokeStyle(function() { return colors(this.index).darker(); })
   .anchor("right").add(pv.Label)
      .textAlign("right")
      .textMargin(12)
      .textBaseline("middle")
      .textStyle(function() { return colors(this.index).darker(); })
      .textDecoration(function() { return (this.parent.o() == this.index) ? "underline" : "none";});


   //render in loop to animate
   var interval = setInterval(function() {
      offset++;
      vis{$randname}.render();
      if (offset > 100) clearInterval(interval);
   }, 20);
JAVASCRIPT;

      if ($show_graph) {
         echo $JS;
      }

      $opt['randname'] = $randname;
      $options = ["opt"     => $opt,
                  "export" => $export,
                  "datas"  => $datas,
                  "unit"   => $unit];

      PluginMreportingCommon::endGraph($options, $dashboard);

      $content = ob_get_clean();

      if ($dashboard) {
         return $content;
      } else {
         echo $content;
      }
   }

   /**
    * Show a sunburst chart (see : http://mbostock.github.com/protovis/ex/sunburst.html)
    *
    * @params :
    * @param $raw_datas : an array with :
    *    - key 'datas', ex :
    *          array(
    *             'key1' => array('key1.1' => val, 'key1.2' => val, 'key1.3' => val),
    *             'key2' => array('key2.1' => val, 'key2.2' => val, 'key2.3' => val)
    *          )
    *    - key 'root', root label in graph center
    *    - key 'unit', ex : '%', 'Kg' (optionnal)
    * @param $title : title of the chart
    * @param $desc : description of the chart (optionnal)
    * @param $show_label : behavior of the graph labels,
    *                      values : 'hover', 'never', 'always' (optionnal)
    * @param $export : keep only svg to export (optionnal)
    * @return nothing
    */
   function showSunburst($params, $dashboard = false, $width = false) {
      ob_start();
      if ($width !== false) {
         $this->width = $width;
      }
      $criterias = PluginMreportingCommon::initGraphParams($params);

      foreach ($criterias as $key => $val) {
         $$key=$val;
      }

      $configs = PluginMreportingConfig::initConfigParams($opt['f_name'], $opt['class']);

      foreach ($configs as $k => $v) {
         $$k=$v;
      }

      $options = ["title" => $title,
                  "desc" => $desc,
                  "randname" => $randname,
                  "export" => $export,
                  "delay" => $delay,
                  "short_classname" => $opt["short_classname"],
                  "showHeader" => $dashboard];

      $this->initGraph($options);

      if (isset($_REQUEST['export'])) {
         $export_txt = "true";
      } else {
         $export_txt =  "false";
      }

      if (!isset($raw_datas['datas'])) {
         echo "}</script>";
         echo __("No data for this date range !", 'mreporting');
         $end['opt']["export"] = false;
         $end['opt']["randname"] = false;
         $end['opt']["f_name"] = $opt['f_name'];
         $end['opt']["class"] = $opt['class'];
         PluginMreportingCommon::endGraph($end, $dashboard);
         return false;
      }

      $datas = $raw_datas['datas'];

      $labels2 = [];
      if (isset($raw_datas['labels2'])) {
         $labels2 = $raw_datas['labels2'];
      }

      if (empty($unit) && !empty($raw_datas['unit'])) {
         $unit = $raw_datas['unit'];
      }

      $datas = $this->initDatasTree($datas, $unit);

      $always = '';
      $hover = '';
      PluginMreportingConfig::checkVisibility($show_label, $always, $hover);
      $height = 450;
      $width = $this->width;
      $top = 10;
      $left = 10;
      if ($dashboard) {
         $top = 25;
         $height = 380;
         $left = 50;
      }

      $JS = <<<JAVASCRIPT

   function getLevelIndex(node) {
      var levelIndex = -1;
      var children = node.parentNode.childNodes;
      for(var i= 0; i < children.length; i++) {
         if (children[i] == node) {
            levelIndex = i;
            break;
         }
      }
      return levelIndex;
   }

   function getLevelNbNode(node) {
      return node.parentNode.childNodes.length;
   }

   var   width = {$width},
         height = {$height}
         i = -1, //mouseover index
         offset = 99; // animation offset

   vis{$randname} = new pv.Panel()
      .width(width)
      .height(height)
      .top({$top})
      .left({$left})
      .event("mousemove", pv.Behavior.point(Infinity));

   /*** Radial layout ***/
   var partition = vis{$randname}.add(pv.Layout.Partition.Fill)
      .nodes(pv.dom(datas).root(null).nodes())
      .size(function(d) d.nodeValue)
      .order("ascending")
      .orient("radial");

   /*** wedges ***/
   var wedge = partition.node.add(pv.Wedge)
      .fillStyle(function(d) {
         //return pv.Colors.category19().by(function(d) d.parentNode && d.parentNode.nodeName);
         if (d.parentNode && d.parentNode.nodeName) {
            var root = d;
            while (root.minDepth != 0) root = root.parentNode
            var rootNodeIndex = getLevelIndex(root);

            //compute alpha value
            var levelIndex = getLevelIndex(d);
            var levelIndexParent = getLevelIndex(d.parentNode);
            var nbLevelNode = getLevelNbNode(d);
            var nbLevelParentNode = getLevelNbNode(d.parentNode);
            var alpha_index = 1 - levelIndex / (nbLevelNode+1);
            var alpha_index_parent  = 1 - levelIndexParent / (nbLevelParentNode+1);

            //specific case alpha index when 1st lvl
            if (d.parentNode == root) alpha_index_parent = 1;

            var alpha = alpha_index * alpha_index_parent;

            return colors(rootNodeIndex).alpha(alpha);
         }
         else {
            if (d.parentNode) {
               levelIndex = getLevelIndex(d);
               return colors(levelIndex);
            } else return colors(0);
         }
      })
      .angle(function(d) {
         var motion = (offset / 30) > 1 ? 1:(offset / 30);
         return motion * d.size * 2 * Math.PI / sum;
      })
      .outerRadius(function(d) {
         if (d.index == 0) return 0; //remove root
         var motion = (offset / 15) > 1 ? 1:(offset / 15);
         return d.outerRadius*motion;
      })
      .innerRadius(function(d) {
         if (d.parentNode && d.parentNode.index == 0) return 0;
         var lastchild = 1;
         if (d.childNodes.length == 0 && this.index > 0 && this.index == i) {
            lastchid = 1.5;
            //console.log(d.innerRadius);
         }
         var motion = (offset / 15) > 1 ? 1:(offset / 15);
         return d.innerRadius*motion*lastchild;
      })
      .strokeStyle(function() {
         return this.fillStyle().darker();
      })
      .lineWidth(function() {
         if (this.index == i) return 4;
         else return 1;
      });

   if ({$export_txt} == false) {
      wedge.text(function(d) { return d.nodeName; });
      wedge.event("mouseover", pv.Behavior.tipsy({gravity: "w", fade: true}));
   }

   /*** wedge interaction ***/
   wedge.anchor().add(pv.Mark)
      .event("point", function() {
         (i = this.index, label);
         return vis{$randname};
      })
      .event("unpoint", function() {
         (i = -1, label);
         return vis{$randname};
      });

   /*** Label titles ***/
   partition.label.add(pv.Label)
      .visible(function(d) d.angle * d.outerRadius >= 6)
      .textAngle(0)
      .left(function(d) {
         var out = 1;
         //console.log(d);
         //if (d.depth == 1) out = 1.13;
         return out * ((height-20) / 2) * d.depth * Math.cos(d.midAngle) + width/2;
      })
      .bottom(function(d) {3
         var out = 1;
         //if (d.depth == 1) out = 1.13;
         return - out * ((height-20) / 2) * d.depth * Math.sin(d.midAngle) + height/2;
      })
      .text(function(d) {
         var label = d.nodeName;
         if (label && label.length > 8) {
            label = label.substring(0, 8)+"..";
         }
         return label;
      });

   /*** Label values ***/
   var label = wedge.anchor("inner").add(pv.Label)
      .font("bold 11px sans-serif")
      .visible(function() {
         if (this.index > 0 && this.index == i) return true;
         else return false;
      })
      .textMargin(4)
      //.textAngle(0)
      .textStyle("white")
      .text(function(d) {
         return d.size
      })
      .strokeStyle(function() {
         return this.target.fillStyle().darker();
      });

   //render in loop to animate
   var interval = setInterval(function() {
      offset++;
      vis{$randname}.render();

      //limit loop
      if (offset > 100) clearInterval(interval);
   }, 20);
JAVASCRIPT;

      if ($show_graph) {
         echo $JS;
      }

      $opt['randname'] = $randname;
      $options = ["opt"        => $opt,
                  "export"    => $export,
                  "datas"     => $datas,
                  "flip_data" => $flip_data,
                  "labels2"   => $labels2,
                  "unit"      => $unit];
      PluginMreportingCommon::endGraph($options, $dashboard);

      $content = ob_get_clean();

      if ($dashboard) {
         return $content;
      } else {
         echo $content;
      }
   }

   /**
    * Show a horizontal grouped bar chart
    *
    * @param $raw_datas : an array with :
    *    - key 'datas', ex : array( 'test1' => array(15,20,50), 'test2' => array(36,15,22))
    *    - key 'labels2', ex : array('label 1', 'label 2', 'label 3')
    *    - key 'unit', ex : '%', 'Kg' (optionnal)
    * @param $title : title of the chart
    * @param $desc : description of the chart (optionnal)
    * @param $show_label : behavior of the graph labels,
    *                      values : 'hover', 'never', 'always' (optionnal)
    * @param $export : keep only svg to export (optionnal)
    * @return nothing
    */
   function showHgbar($params, $dashboard = false, $width = false) {
      $criterias = PluginMreportingCommon::initGraphParams($params);
      ob_start();
      if ($width !== false) {
         $this->width = $width;
      }
      foreach ($criterias as $key => $val) {
         $$key=$val;
      }

      $configs = PluginMreportingConfig::initConfigParams($opt['f_name'], $opt['class']);

      foreach ($configs as $k => $v) {
         $$k=$v;
      }

      $options = ["title" => $title,
                  "desc" => $desc,
                  "randname" => $randname,
                  "export" => $export,
                  "delay" => $delay,
                  "short_classname" => $opt["short_classname"],
                  "showHeader" => $dashboard];

      $this->initGraph($options);

      if (!isset($raw_datas['datas'])) {
         echo "}</script>";
         echo __("No data for this date range !", 'mreporting');
         $end['opt']["export"] = false;
         $end['opt']["randname"] = false;
         $end['opt']["f_name"] = $opt['f_name'];
         $end['opt']["class"] = $opt['class'];
         PluginMreportingCommon::endGraph($end, $dashboard);
         return false;
      }

      if (empty($unit) && !empty($raw_datas['unit'])) {
         $unit = $raw_datas['unit'];
      }

      $datas = $raw_datas['datas'];
      $labels2 = $raw_datas['labels2'];
      $datas = $this->initDatasMultiple($datas, $labels2, $unit);

      $nb_bar = count($datas);
      $nb_bar2 = count($labels2);
      $height = 28 * $nb_bar * $nb_bar2 + 50;

      $always = '';
      $hover = '';
      PluginMreportingConfig::checkVisibility($show_label, $always, $hover);
      $left = 240;
      $bottomAxis = 5;
      if ($dashboard) {
         $left = 100;
         if ($height > 300) {
            $height = 300;
         }
         $bottomAxis = -15;
      }

      $JS = <<<JAVASCRIPT
   var width_hgbar = {$this->width};
   var height_hgbar = {$height};
   var x = pv.Scale.linear(0, max).range(0, .7 * width_hgbar);
   var y = pv.Scale.ordinal(pv.range(n+1)).splitBanded(0, height_hgbar, 4/5);
   var Hilighted = [false, false,false, false,false, false];

   var offset = 0, index_active = -1;

   vis{$randname} = new pv.Panel()
      .width({$this->width})
      .height(height_hgbar)
      .bottom(40)
      .left({$left})
      .right(50)
      .top(5);

   // axis and tick
   vis{$randname}.add(pv.Rule)
         .data(x.ticks(6))
         .left(x)
         .strokeStyle(function(d) { return d ? "rgba(255,255,255,.3)" : "#000"; })
         .lineWidth(function() { return (this.index == 0) ? 2 : 1; })
      .add(pv.Rule)
         .bottom({$bottomAxis})
         .height(height_hgbar)
         .strokeStyle(function(d) d ? "#eee" : "black")
      .anchor("bottom").add(pv.Label)
         .text(x.tickFormat);

   panel = vis{$randname}.add(pv.Panel)
      .data(datas)
      .top(function() { return y(this.index) + m*14; })
      .height(y.range().band)
   .anchor("left").add(pv.Label)
      .textMargin(5)
      .textAlign("right")
      .text(function() { return labels[this.parent.index]; })
   .parent.add(pv.Panel)
      .data(function(d) { return d; })
      .top(function() { return (this.index * y.range().band / m); })
      .height(y.range().band /m);

   panel_bar = panel.add(pv.Panel)
      .def("active", false);

   bar = panel_bar.add(pv.Bar)
      .left(0)
      .width(function(d) {
         var r = 360 - 15 * offset;
         if (r < 0) r = 0;
         var len = x(d) - r;
         return len;
      })
      .strokeStyle("black")
      .lineWidth(1)
      .top(2)
      .bottom(2)
      .fillStyle(function() {
         if(this.parent.active() || Hilighted[this.parent.parent.index]) {
            return colors(this.parent.parent.index).alpha(.6);
         }
         else return colors(this.parent.parent.index);
      })
      .event("mouseover", function() {
         this.parent.active(true);
         Hilighted[this.parent.active] = true;
         index_active = this.parent.parent.index;
         return vis{$randname};
      })
//      .event("mouseover", pv.Behavior.extjsTooltips("test"))
      .event("mouseout", function() {
         this.parent.active(false);
         Hilighted[this.parent.active] = false;
         index_active = -1;
         return vis{$randname};
      })
      .strokeStyle(function() { return colors(this.parent.parent.index).darker(); })
      .lineWidth(2)
   .anchor("right").add(pv.Label)
      .textAlign("left")
      .visible(function(d) {
         return ((this.parent.active() || (d <= max / 100 && d!=0)
            || Hilighted[this.parent.parent.index])  && {$hover} || {$always}) ? true : false;
      })
      .textStyle(function() { return colors(this.parent.parent.index).darker(); })
      .text(function(d) { return  d+" {$unit}"; });

   // legend
   vis{$randname}.add(pv.Dot)
      .data(labels2)
      .right(80)
      .top(function(d) { return 5 + this.index * 15; })
      .fillStyle(function() {
         return Hilighted[this.index]? colors(this.index).alpha(.6) : colors(this.index);
      })
      .event("mouseover", function() {
         Hilighted[this.index] = true;
         return vis{$randname};
      }) // override
      .event("mouseout", function() {
         Hilighted[this.index] = false;
         return vis{$randname};
      })
      .strokeStyle(function() { return colors(this.index).darker(); })
   .anchor("right").add(pv.Label)
      .textAlign("right")
      .textMargin(12)
      .textBaseline("middle")
      .textStyle(function() { return colors(this.index).darker(); })
      .textDecoration(function() { return (index_active == this.index) ? "underline" : "none";});

   //render in loop to animate
   var interval = setInterval(function() {
      offset++;
      vis{$randname}.render();
      if (offset > 100) clearInterval(interval);
   }, 20);
JAVASCRIPT;

      if ($show_graph) {
         echo $JS;
      }

      $opt['randname'] = $randname;
      $options = ["opt"        => $opt,
                  "export"    => $export,
                  "datas"     => $datas,
                  "labels2"   => $labels2,
                  "flip_data" => $flip_data,
                  "unit"      => $unit];
      PluginMreportingCommon::endGraph($options, $dashboard);

      $content = ob_get_clean();

      if ($dashboard) {
         return $content;
      } else {
         echo $content;
      }
   }


   /**
    * Show a horizontal grouped bar chart
    *
    * @param $raw_datas : an array with :
    *    - key 'datas', ex : array( 'test1' => array(15,20,50), 'test2' => array(36,15,22))
    *    - key 'labels2', ex : array('label 1', 'label 2', 'label 3')
    *    - key 'unit', ex : '%', 'Kg' (optionnal)
    * @param $title : title of the chart
    * @param $desc : description of the chart (optionnal)
    * @param $show_label : behavior of the graph labels,
    *                      values : 'hover', 'never', 'always' (optionnal)
    * @param $export : keep only svg to export (optionnal)
    * @return nothing
    */
   function showVstackbar($params, $dashboard = false, $width = false) {
      ob_start();
      if ($width !== false) {
         $this->width = $width;
      }
      $criterias = PluginMreportingCommon::initGraphParams($params);

      foreach ($criterias as $key => $val) {
         $$key=$val;
      }

      $configs = PluginMreportingConfig::initConfigParams($opt['f_name'], $opt['class']);

      foreach ($configs as $k => $v) {
         $$k=$v;
      }

      $options = ["title" => $title,
                  "desc" => $desc,
                  "randname" => $randname,
                  "export" => $export,
                  "delay" => $delay,
                  "short_classname" => $opt["short_classname"],
                  "showHeader" => $dashboard];

      $this->initGraph($options);

      if (!isset($raw_datas['datas'])) {
         echo "}</script>";
         echo __("No data for this date range !", 'mreporting');
         $end['opt']["export"] = false;
         $end['opt']["randname"] = false;
         $end['opt']["f_name"] = $opt['f_name'];
         $end['opt']["class"] = $opt['class'];
         PluginMreportingCommon::endGraph($end, $dashboard);
         return false;
      }

      if (empty($unit) && !empty($raw_datas['unit'])) {
         $unit = $raw_datas['unit'];
      }

      $datas = $raw_datas['datas'];
      $labels2 = $raw_datas['labels2'];
      $datas = $this->initDatasMultiple($datas, $labels2, $unit, true);

      $nb_bar = count($datas);
      $nb_bar2 = count($labels2);

      $always = '';
      $hover = '';
      PluginMreportingConfig::checkVisibility($show_label, $always, $hover);

      $height = 20 * $nb_bar + 50;
      if ($height < 400) {
         $height = 400;
      }
      $width = $this->width;
      if ($dashboard) {
         $height = 250;
      }

      $JS = <<<JAVASCRIPT
   var w = {$width},
       h = {$height},
       x = pv.Scale.ordinal(pv.range(m)).splitBanded(0, .8 * w, 4/5),
       y = pv.Scale.linear(0, 1.1 * max).range(0, h - {$nb_bar2} * 5.5),
       offset = 0, // animation
       i = -1 // mouseover index
       Hilighted = [false, false,false, false,false, false];


   vis{$randname} = new pv.Panel()
       .width(w)
       .height(h)
       .bottom(150)
       .left(50)
       .right(50)
       .top(5);

   /*** y-axis ticks and labels ***/
   vis{$randname}.add(pv.Rule)
      .data(y.ticks())
      .bottom(y)
      .left(function(d) d ? 0 : null)
      .width(function(d) d ? w : null)
      .strokeStyle(function(d) d ? "#eee" : "black")
      .anchor("left").add(pv.Label)
        .text(y.tickFormat);

   /*** stacks of bar ***/
   var stack{$randname} = vis{$randname}.add(pv.Layout.Stack)
      .layers(datas)
      .x(function() x(this.index))
      .y(function(d) 1- 50/offset + y(d))

   /*** bars ***/
   var bar{$randname} = stack{$randname}.layer.add(pv.Bar)
      .width(x.range().band)
      .fillStyle(function(d) {
         if(Hilighted[this.parent.index] && d!=0) return colors(this.parent.index).alpha(.6);
         else if(d!=0)return colors(this.parent.index);
      })
      .strokeStyle(function(d) {
         if ((this.index == i || Hilighted[this.parent.index]) && d!=0)
         return colors(this.parent.index).darker();
      })
      .event("mouseover", function(d) {
         i = this.index;
         return vis{$randname};
      })
      .event("mouseout", function() {
         i = -1;
         return vis{$randname};
      });

   bar{$randname}.anchor("center").add(pv.Label)
      .visible(function(d){
         return ( (this.index == i || Hilighted[this.parent.index])
            && ({$hover} && (d!=0)) || ({$always}  && (d!=0)) ) ? true : false ;
      })
      .textBaseline("center")
      .text(function(d) { return d+" {$unit}"; })
      .textStyle(function() { return colors(this.parent.index).darker(); });


   /*** x-axis labels and ticks ***/
   var hiddenanchor{$randname} = stack{$randname}.layer.add(pv.Bar)
      .fillStyle(null)
      .width(x.range().band)
      .bottom(-80)
      .height(80)
   .anchor("top").add(pv.Label)
      .visible(function() !this.parent.index)
      .textAlign("left")
      .textMargin(5)
      .textBaseline("top")
      .textAngle(Math.PI / 4)
      .text(function() { return labels2[this.index]; })
      .font(function() {
         return (i == this.index) ? "bold 11px sans-serif" : "";
      })
   .anchor("bottom").add(pv.Rule)
      .height(3);

   // legend
   dot{$randname} = vis{$randname}.add(pv.Dot) // legend dots
      .data(labels)
      .right(50)
      .top(function(d) { return (15 * $nb_bar) + (this.index * -15); })
      .fillStyle(function(d) {
         return Hilighted[this.index]? colors(this.index).alpha(.6) : colors(this.index);
      })
      .event("mouseover", function(d) {
         Hilighted[this.index] = true;
         return vis{$randname};
      })
      .event("mouseout", function() {
         Hilighted[this.index] = false;
         return vis{$randname};
      })
      .strokeStyle(function() { return colors(this.index).darker(); })
   .anchor("right").add(pv.Label) // legend labels
      .textAlign("right")
      .textMargin(12)
      .textBaseline("middle")
      .textStyle(function() { return colors(this.index).darker(); });

   dot{$randname}.anchor("left").add(pv.Label) // legend labels
      .textAlign("left")
      .textBaseline("middle")
      .text(function() {
         if (i>=0) return datas[this.index][i]+" {$unit}";
         else return "";
      })
      .textStyle(function() { return colors(this.index).darker(); });

   //render in loop to animate
   //vis{$randname}.render();
   var interval = setInterval(function() {
         offset++;
         vis{$randname}.render();
         if (offset > 100) clearInterval(interval);
      }, 20);

JAVASCRIPT;

      if ($show_graph) {
         echo $JS;
      }

      $opt['randname'] = $randname;
      $options = ["opt"        => $opt,
                  "export"    => $export,
                  "datas"     => $datas,
                  "labels2"   => $labels2,
                  "flip_data" => $flip_data,
                  "unit"      => $unit];
      PluginMreportingCommon::endGraph($options, $dashboard);

      $content = ob_get_clean();

      if ($dashboard) {
         return $content;
      } else {
         echo $content;
      }
   }
   /**
    * Show a Area chart
    *
    * @param $raw_datas : an array with :
    *    - key 'datas', ex : array( 'test1' => 15, 'test2' => 25)
    *    - key 'unit', ex : '%', 'Kg' (optionnal)
    *    - key 'spline', curves line (boolean - optionnal)
    * @param $title : title of the chart
    * @param $desc : description of the chart (optionnal)
    * @param $show_label : behavior of the graph labels,
    *                      values : 'hover', 'never', 'always' (optionnal)
    * @param $export : keep only svg to export (optionnal)
    * @param $area : show plain chart instead only a line (optionnal)
    * @return nothing
    */
   function showArea($params, $dashboard = false, $width = false) {
      ob_start();
      if ($width !== false) {
         $this->width = $width;
      }

      $criterias = PluginMreportingCommon::initGraphParams($params);

      foreach ($criterias as $key => $val) {
         $$key=$val;
      }

      $configs = PluginMreportingConfig::initConfigParams($opt['f_name'], $opt['class']);

      foreach ($configs as $k => $v) {
         $$k=$v;
      }

      $area = true;
      if (isset($params['area'])) {
         $area = $params['area'];
      }

      $options = ["title" => $title,
                  "desc" => $desc,
                  "randname" => $randname,
                  "export" => $export,
                  "delay" => $delay,
                  "short_classname" => $opt["short_classname"],
                  "showHeader" => $dashboard];

      $this->initGraph($options);

      if (!isset($raw_datas['datas'])) {
         echo "}</script>";
         echo __("No data for this date range !", 'mreporting');
         $end['opt']["export"] = false;
         $end['opt']["randname"] = false;
         $end['opt']["f_name"] = $opt['f_name'];
         $end['opt']["class"] = $opt['class'];
         PluginMreportingCommon::endGraph($end, $dashboard);
         return false;
      }

      if (empty($unit) && !empty($raw_datas['unit'])) {
         $unit = $raw_datas['unit'];
      }

      $datas = $raw_datas['datas'];
      $datas = $this->initDatasSimple($datas, $unit);

      $always = '';
      $hover = '';
      PluginMreportingConfig::checkVisibility($show_label, $always, $hover);
      $height = 350;
      $width = $this->width;
      $bottom = 80;
      $left = 20;
      $right = 50;
      if ($dashboard) {
         $height = 340;
         $width = 395;
         $left = 30;
      }

      $JS = <<<JAVASCRIPT
   var width_area = {$width};
   var height_area = {$height};
   var offset = 0;
   var step = Math.round(n / 20);

   var x = pv.Scale.linear(0, n-1).range(5, width_area);
   var y = pv.Scale.linear(0, max).range(0, height_area);


   /* The root panel. */
   vis{$randname} = new pv.Panel()
      .width(width_area)
      .height(height_area)
      .bottom({$bottom})
      .left({$left})
      .right({$right})
      .top(5);

   /* Y-axis and ticks. */
   vis{$randname}.add(pv.Rule)
      .data(y.ticks(5))
      .bottom(y)
      .lineWidth(1)
      .strokeStyle(function(d) d ? "#eee" : "black")
      .anchor("left").add(pv.Label)
         .text(y.tickFormat);

   /* X-axis and ticks. */
   vis{$randname}.add(pv.Rule)
      .data(datas)
      .left(function() x(this.index)-1)
      .bottom(-5)
      .strokeStyle(function() {
         if (this.index == 0) return "black";
         return (i == this.index) ? "black" : "#eee";
      })
      .height(height_area - 30)
      .anchor("bottom").add(pv.Label)
         .visible(function() {
            if ((this.index / step) == Math.round(this.index / step)) return true;
            else return false;
         })
         .textAngle(Math.PI / 4)
         .text(function() { return labels[this.index]; })
         .textAlign("left")
         .textMargin(5)
         .textBaseline("top");

   /* add mini black lines in front of labels tick */
   vis{$randname}.add(pv.Rule)
      .data(datas)
      .left(function() x(this.index)-1)
      .bottom(-5)
      .strokeStyle("black")
      .height(5)
      .visible(function() {
         if ((this.index / step) == Math.round(this.index / step)) return true;
         else return false;
      });

   /* The line with an area. */
   var line{$randname} = vis{$randname}.add(pv.Line)
      .tension(function () {
         return ('{$unit}' == '%') ? 0.9 : 0.7;
      })
      .data(datas)
      .interpolate(function () { //curve line
         if ({$spline}>0) return "cardinal";
         else return "linear";
      })
      .left(function() { return x(this.index); })
      .bottom(function(d) { return y(d); })
      .visible(function() {return this.index  < ((offset / 2) * ( n / 12));})
      .lineWidth(4);

   if ('{$area}'>0) {
      line{$randname}.add(pv.Area)
         .visible(function() {
            return n < ((offset / 2) * ( n / 12));
         })
         .bottom(1)
         .fillStyle(function() { return colors(0).alpha(.5); })
         .height(function(d) { return y(d); });
   }

   /* Dots */
   var dot = line{$randname}.add(pv.Dot)
      .left(function() { return x(this.index); })
      .bottom(function(d) { return y(d); })
      .fillStyle(function () { return (i == this.index) ? "#ff7f0e" : "white";})
      .lineWidth(2)
      .size(function () { return (i == this.index) ? 20 : 10;});

   /* The mouseover dots and label. */
   var i = -1;
   vis{$randname}.add(pv.Dot)
       .visible(function() i >= 0)
       .left(5)
       .top(5)
       .fillStyle("#ff7f0e")
       .lineWidth(1)
     .anchor("right").add(pv.Label)
       .text(function() { return (i >= 0 && {$hover}) ? datas[i]+" {$unit}":'';})
       .textStyle("#1f77b4");

   /* An invisible bar to capture events (without flickering). */
   vis{$randname}.add(pv.Bar)
      .fillStyle("rgba(0,0,0,.001)")
      .event("mouseout", function() {
         i = -1;
         return vis{$randname};
      })
      .event("mousemove", function() {
         i = Math.round(x.invert(vis{$randname}.mouse().x));
         return vis{$randname};
      });

   //render in loop to animate
   var interval = setInterval(function() {
      offset++;
      vis{$randname}.render();
      if (offset > 100) clearInterval(interval);
   }, 20);
JAVASCRIPT;

      if ($show_graph) {
         echo $JS;
      }

      $opt['randname'] = $randname;
      $options = ["opt"        => $opt,
                        "export"    => $export,
                        "datas"     => $datas,
                        "unit"      => $unit];
      PluginMreportingCommon::endGraph($options, $dashboard);

      $content = ob_get_clean();

      if ($dashboard) {
         return $content;
      } else {
         echo $content;
      }
   }

   /**
    * Show a Line chart
    *
    * @param $raw_datas : an array with :
    *    - key 'datas', ex : array( 'test1' => 15, 'test2' => 25)
    *    - key 'unit', ex : '%', 'Kg' (optionnal)
    *    - key 'spline', curves line (boolean - optionnal)
    * @param $title : title of the chart
    * @param $desc : description of the chart (optionnal)
    * @param $show_label : behavior of the graph labels,
    *                      values : 'hover', 'never', 'always' (optionnal)
    * @param $export : keep only svg to export (optionnal)
    * @return nothing
    */
   function showLine($params, $dashboard = false, $width = false) {
      $params['area'] = false;
      if ($dashboard) {
         return  $this->showArea($params, $dashboard, $width);
      } else {
         $this->showArea($params, $dashboard, $width);
      }
   }

    /**
    * Show a multi-area chart
    *
    * @param $raw_datas : an array with :
    *    - key 'datas', ex : array( 'test1' => 15, 'test2' => 25)
    *    - key 'unit', ex : '%', 'Kg' (optionnal)
    *    - key 'spline', curves line (boolean - optionnal)
    * @param $title : title of the chart
    * @param $desc : description of the chart (optionnal)
    * @param $show_label : behavior of the graph labels,
    *                      values : 'hover', 'never', 'always' (optionnal)
    * @param $export : keep only svg to export (optionnal)
    * @return nothing
    */
   function showGarea($params, $dashboard = false, $width = false) {
      ob_start();
      if ($width !== false) {
         $this->width = $width;
      }
      $criterias = PluginMreportingCommon::initGraphParams($params);

      foreach ($criterias as $key => $val) {
         $$key=$val;
      }

      $configs = PluginMreportingConfig::initConfigParams($opt['f_name'], $opt['class']);
      foreach ($configs as $k => $v) {
         $$k=$v;
      }

      $options = ["title" => $title,
                        "desc" => $desc,
                        "randname" => $randname,
                        "export" => $export,
                        "delay" => $delay,
                        "short_classname" => $opt["short_classname"],
          "showHeader" => $dashboard];

      $this->initGraph($options);

      if (!isset($raw_datas['datas'])) {
         echo "}</script>";
         echo __("No data for this date range !", 'mreporting');
         $end['opt']["export"] = false;
         $end['opt']["randname"] = false;
         $end['opt']["f_name"] = $opt['f_name'];
         $end['opt']["class"] = $opt['class'];
         PluginMreportingCommon::endGraph($end, $dashboard);
         return false;
      }

      $area = true;
      if (isset($params['area'])) {
         $area = $params['area'];
      }

      if (empty($unit) && !empty($raw_datas['unit'])) {
         $unit = $raw_datas['unit'];
      }

      $datas = $raw_datas['datas'];
      $labels2 = $raw_datas['labels2'];
      $datas = $this->initDatasMultiple($datas, $labels2, $unit);

      $always = '';
      $hover = '';
      PluginMreportingConfig::checkVisibility($show_label, $always, $hover);

      $nb_bar = count($datas);
      $height = 20 * $nb_bar + 250;
      if ($height < 450) {
         $height = 450;
      }
      if ($dashboard) {
         $this->width -= 35;
         $height = 350;
      }

      $JS = <<<JAVASCRIPT
   var width_area = {$this->width};
   var height_area = {$height};
   var offset = 0;
   var step = Math.round(m / 20);

   var x = pv.Scale.linear(0, m-1).range(5, width_area);
   var y = pv.Scale.linear(0, max).range(0, height_area-(n*14));
   var i = -1;

   /* The root panel. */
   vis{$randname} = new pv.Panel()
      .width(width_area)
      .height(height_area)
      .bottom(60)
      .left(50)
      .right(50)
      .top(5);

   /* Y-ticks. */
   vis{$randname}.add(pv.Rule)
      .data(y.ticks())
      .bottom(function(d) { return Math.round(y(d)) - .5; })
      .strokeStyle(function(d) { return d ? "#eee" : "black"; })
     .anchor("left").add(pv.Label)
       .text(function(d) { return d.toFixed(1) });

   /* X-ticks. */
   vis{$randname}.add(pv.Rule)
      .data(x.ticks(m))
      .left(function(d) { return Math.round(x(d)) - .5; })
      .strokeStyle(function() {
         if (this.index == 0) return "black";
         return (i == this.index) ? "black" : "#eee";
      })
      .height(height_area - (n*14))
      .bottom(-5)
     .anchor("bottom").add(pv.Label)
      .textAngle(Math.PI / 4)
      .textAlign("left")
      .textMargin(5)
      .text(function(d){ return  labels2[this.index]; })
      .font(function() {
         return (i == this.index) ? "bold 11px sans-serif" : "";
      })
      .visible(function() {
         if ((this.index / step) == Math.round(this.index / step)) return true;
         else return false;
      });

   /* add mini black lines in front of labels tick */
   vis{$randname}.add(pv.Rule)
      .data(x.ticks(m))
      .left(function() { return x(this.index)-1; })
      .bottom(-5)
      .strokeStyle("black")
      .height(5)
      .visible(function() {
         if ((this.index / step) == Math.round(this.index / step)) return true;
         else return false;
      });

   /* A panel for each data series. */
   var panel{$randname} = vis{$randname}.add(pv.Panel)
      .data(datas);

   /* The line. */
   var lines{$randname} = panel{$randname}.add(pv.Line)
      .tension(function () {
         return ('{$unit}' == '%') ? 0.9 : 0.7;
      })
      .data(function(d) { return d; })
      .interpolate(function () { //curve line
         if ({$spline}>0) return "cardinal";
         else return "linear";
      })
      .strokeStyle(function() { return colors(this.parent.index); })
      .left(function() { return x(this.index); })
      .bottom(function(d) { return y(d); })
      .visible(function() { return (this.index < ((offset / 2) * ( m / 12))); })
      .lineWidth(2);

   if ('{$area}'>0) {
      lines{$randname}.add(pv.Area)
         .visible(function() {
            return m < ((offset / 2) * ( m / 12));
         })
         .lineWidth(0)
         .bottom(1)
         .fillStyle(function() { return colors(this.parent.index).alpha(.15); })
         .height(function(d) { return y(d); });
   }

   /* The dots*/
   var dots{$randname} = lines{$randname}.add(pv.Dot)
      .left(function() { return x(this.index); })
      .bottom(function(d) { return y(d); })
      .fillStyle(function () {
         return (i == this.index) ? colors(this.parent.index) : "white";
      })
      .lineWidth(2)
      .size(function () { return (i == this.index) ? 15 : 10;});


   /* The legend */
   var legend_dots{$randname} = lines{$randname}.add(pv.Dot)
         .data(function(d) { return [d[i]]; })
         .left(5)
         .top(function() { return this.parent.index * 13 + 10; });

   var legend_labels{$randname} = legend_dots{$randname}.anchor("right").add(pv.Label)
         .text(function(d) {
            var text = labels[this.parent.index];
            if (i > -1 && {$hover}) text += " : "+d+" {$unit}"; // mouse over labels
            return text;
         })
         .textStyle(function() { return colors(this.parent.index).darker(); });


   /* An invisible bar to capture events (without flickering). */
   vis{$randname}.add(pv.Bar)
      .fillStyle("rgba(0,0,0,.001)")
      .event("mouseout", function() {
         i = -1;
         return vis{$randname};
      })
      .event("mousemove", function() {
         i = Math.round(x.invert(vis{$randname}.mouse().x));
         return vis{$randname}  ;
      });


   //render in loop to animate
   var interval = setInterval(function() {
      offset++;
      vis{$randname}.render();
      if (offset > 100) clearInterval(interval);
   }, 20);

JAVASCRIPT;

      if ($show_graph) {
         echo $JS;
      }

      $opt['randname'] = $randname;
      $options = ["opt"        => $opt,
                        "export"    => $export,
                        "datas"     => $datas,
                        "labels2"   => $labels2,
                        "flip_data" => $flip_data,
                        "unit"      => $unit];
      PluginMreportingCommon::endGraph($options, $dashboard);

      $content = ob_get_clean();

      if ($dashboard) {
         return $content;
      } else {
         echo $content;
      }
   }

   /**
    * Show a multi-line charts
    *
    * @param $raw_datas : an array with :
    *    - key 'datas', ex : array( 'test1' => 15, 'test2' => 25)
    *    - key 'unit', ex : '%', 'Kg' (optionnal)
    * @param $title : title of the chart
    * @param $desc : description of the chart (optionnal)
    * @param $show_label : behavior of the graph labels,
    *                      values : 'hover', 'never', 'always' (optionnal)
    * @param $export : keep only svg to export (optionnal)
    * @return nothing
    */
   function showGline($params, $dashboard = false, $width = false) {
      $params['area'] = false;
      if ($dashboard) {
         return $this->showGarea($params, $dashboard, $width);
      } else {
         $this->showGarea($params, $dashboard, $width);
      }
   }

   /**
    * Compile simple datas
    *
    * @param $datas, ex : array( 'test1' => 15, 'test2' => 25)
    * @param $unit, ex : '%', 'Kg' (optionnal)
    * @return nothing
    */

   function initDatasSimple($datas, $unit = '', $links = []) {

      $datas = PluginMreportingCommon::compileDatasForUnit($datas, $unit);

      $labels = array_keys($datas);
      $values = array_values($datas);

      $out = "var datas = [\n";
      foreach ($values as $value) {
         $out.= "\t".addslashes($value).",\n";
      }
      $out = substr($out, 0, -2)."\n";
      $out.= "];\n";

      $out.= "var labels = [\n";
      foreach ($labels as $label) {
         $out.= "\t'".addslashes($label)."',\n";
      }
      $out = substr($out, 0, -2)."\n";
      $out.= "];\n";

      $out.= "var links = [\n";
      foreach ($links as $link) {
         $out.= "\t'".addslashes($link)."',\n";
      }
      $out.= "];\n";

      echo $out;
      if (count($values) > 0) {
         $max = (max($values)*1.1);
      } else {
         $max = 1;
      }
      if ($unit == '%') {
         $max = 110;
      }

      echo "var max = $max;";
      echo "var n = ".count($values).";";

      return $datas;
   }

   /**
    * Compile multiple datas
    *
    * @param $datas, ex : array( 'test1' => 15, 'test2' => 25)
    * @param $labels2
    * @param $unit, ex : '%', 'Kg' (optionnal)
    * @param $stacked : if stacked graph, option to compile the max value
    * @return nothing
    */

   function initDatasMultiple($datas, $labels2, $unit = '', $stacked = false) {

      $datas = PluginMreportingCommon::compileDatasForUnit($datas, $unit);

      $labels = array_keys($datas);
      $values = array_values($datas);
      $max = 0;

      if ($stacked) {

         $tmp = [];
         foreach ($values as $k => $v) {

            foreach ($v as $key => $val) {
                  $tmp[$key][$k] = $val;
            }
         }
         if (count($tmp) > 0) {
            foreach ($tmp as $date => $nb) {
               $count = array_sum(array_values($nb));
               if ($count > $max) {
                  $max = $count;
               }
            }
         }
      }

      //merge missing keys
      $empty_values = array_fill_keys(array_keys($labels2), 0);
      foreach ($values as $k => $v) {
         $values[$k] = array_replace($empty_values, $v);
      }

      $out = "var datas = [\n";
      foreach ($values as $line) {
         $out.= "\t[";
         foreach ($line as $label2 => $value) {
            $out.= addslashes($value).",";
            if ($value > $max && !$stacked) {
               $max = $value;
            }
         }
         $out = substr($out, 0, -1)."";
         $out.= "],\n";
      }
      $out = substr($out, 0, -2)."\n";
      $out.= "];\n";

      $out.= "var labels = [\n";
      foreach ($labels as $label) {
         $out.= "\t'".addslashes($label)."',\n";
      }
      $out = substr($out, 0, -2)."\n";
      $out.= "];\n";

      $out.= "var labels2 = [\n";
      foreach ($labels2 as $label) {
         $out.= "\t'".addslashes($label)."',\n";
      }
      $out = substr($out, 0, -2)."\n";
      $out.= "];\n";
      echo $out;

      if (!$stacked) {
         $max = ($max*1.2);
      }
      if ($unit == '%') {
         $max = 110;
      }

      echo "var n = ".count($labels).";";
      echo "var m = ".count($labels2).";";
      echo "var max = $max;";

      return $datas;

   }


   /**
    * Compile Tree datas
    *
    * @param $datas, ex :
    *          array(
    *             'key1' => array('key1.1' => val, 'key1.2' => val, 'key1.3' => val),
    *             'key2' => array('key2.1' => val, 'key2.2' => val, 'key2.3' => val)
    *          )
    * @param $unit, ex : '%', 'Kg' (optionnal)
    * @return nothing
    */

   function initDatasTree($datas, $unit = '') {

      $datas = PluginMreportingCommon::compileDatasForUnit($datas, $unit);

      echo "var datas = ".json_encode($datas).";";
      echo "var sum = ".PluginMreportingCommon::getArraySum($datas).";";

      return $datas;
   }



   function legend($datas) {

   }
}
