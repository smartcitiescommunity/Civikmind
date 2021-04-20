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

/**
 * Class PluginManageentitiesGenerateCRI
 */
class PluginManageentitiesGenerateCRI extends CommonGLPI {

   static $rightname = "ticket";

   const TASK_TO_DO = 1;
   const TASK_DONE = 2;
   const MINUTE = 60;
   const HOUR = 3600;
   const DAY = 86400;

   /**
    * @param int $nb
    *
    * @return string|\translated
    * @see CommonDBTM::getTypeName($nb)
    *
    */
   static function getMenuName($nb = 0) {
      return __('Generate report intervention', 'manageentities');
   }

   /**
    * @return array
    */
   static function getMenuContent() {

      $menu = [];

      $menu['title'] = self::getMenuName();
      $menu['page'] = "/plugins/manageentities/front/generatecri.php";
      $menu['links']['search'] = self::getSearchURL(false);
      $menu['icon'] = self::getIcon();

      return $menu;
   }

   /**
    * @return string
    */
   static function getIcon() {
      return "fab fa-wpforms";
   }

   /**
    * @param $ticket
    * @param $entities
    *
    * @throws \GlpitestSQLError
    */
   function showWizard($ticket, $entities) {

      $rand = mt_rand();
      $rand_user = mt_rand();
      $tasktemplate = 0;
      $countTasks = [];
      $config = PluginManageentitiesConfig::getInstance();

      $values = ['itilcategories_id' => 0,
         'type' => Entity::getUsedConfig('tickettype',
            $_SESSION['glpiactive_entity'],
            '', Ticket::INCIDENT_TYPE),
         'content' => '',
         'name' => '',
         'entities_id' => $_SESSION['glpiactive_entity'],
         'status' => CommonITILObject::PLANNED,
         'urgency' => 3,
         'impact' => 3,
         'priority' => (int)Ticket::computePriority(3, 3),
         '_tasktemplates_id' => [],
         'users_intervenor' => [Session::getLoginUserID()]
      ];


      // Get default values from posted values on reload form
      if (isset($_POST)) {
         $options = $_POST;
      }


      if (isset($options['name'])) {
         $order = ["\\'", '\\"', "\\\\"];
         $replace = ["'", '"', "\\"];
         $options['name'] = str_replace($order, $replace, $options['name']);
      }

      if (isset($options['content'])) {
         // Clean new lines to be fix encoding
         $order = ['\\r', '\\n', "\\'", '\\"', "\\\\"];
         $replace = ["", "", "'", '"', "\\"];
         $options['content'] = str_replace($order, $replace, $options['content']);
      }

      // Restore saved value or override with page parameter
      $saved = $this->restoreInput();
      foreach ($values as $name => $value) {
         if (!isset($options[$name])) {
            if (isset($saved[$name])) {
               $options[$name] = $saved[$name];
            } else {
               $options[$name] = $value;
            }
         }
      }
      // Check category / type validity
      if ($options['itilcategories_id']) {
         $cat = new ITILCategory();
         if ($cat->getFromDB($options['itilcategories_id'])) {
            switch ($options['type']) {
               case Ticket::INCIDENT_TYPE :
                  if (!$cat->getField('is_incident')) {
                     $options['itilcategories_id'] = 0;
                  }
                  break;

               case Ticket::DEMAND_TYPE :
                  if (!$cat->getField('is_request')) {
                     $options['itilcategories_id'] = 0;
                  }
                  break;

               default :
                  break;
            }
         }
      }


      // Load ticket template if available :
      $tt = $ticket->getITILTemplateToUse(false, $options['type'],
         $options['itilcategories_id'],
         $_SESSION["glpiactive_entity"]);

      // Predefined fields from template : reset them
      if (isset($options['_predefined_fields'])) {
         $options['_predefined_fields']
            = Toolbox::decodeArrayFromInput($options['_predefined_fields']);
      } else {
         $options['_predefined_fields'] = [];
      }

      PluginManageentitiesEntity::showManageentitiesHeader(__('Generate Intervention report', 'manageentities'));
      echo "<form name='generate' method='post' action='" . self::getFormUrl() . "'>";
      echo "<table class='tab_cadre' width='60%'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th colspan='4' style='padding-top:16px; font-weight: bold;'>";
      echo __('Ticket informations', 'manageentities');
      echo "</th>";
      echo "</tr>";

      $predefined_fields = [];
      $tpl_key = Ticket::getTemplateFormFieldName();
      // override default ticket by predefined fields into ticket & task template
      if (isset($tt->predefined) && count($tt->predefined) > 0) {
         foreach ($tt->predefined as $predeffield => $predefvalue) {
            if (isset($values[$predeffield])) {
               if ($predeffield == '_tasktemplates_id') {
                  $tasktemplate = new TaskTemplate();
                  $array_task_template = $tt->predefined['_tasktemplates_id'];
                  foreach ($array_task_template as $id_task_template) {
                     $tasktemplate->getFromDB($id_task_template);
                  }
               } else if (((count($options['_predefined_fields']) == 0)
                     && ($options[$predeffield] == $values[$predeffield]))
                  || (isset($options['_predefined_fields'][$predeffield])
                     && ($options[$predeffield] == $options['_predefined_fields'][$predeffield]))
                  || (isset($options[$tpl_key])
                     && ($options[$tpl_key] != $tt->getID()))
                  // user pref for requestype can't overwrite requestype from template
                  // when change category
                  || (($predeffield == 'requesttypes_id')
                     && empty($saved))) {

                  // Load template data
                  $options[$predeffield] = $predefvalue;
                  $predefined_fields[$predeffield] = $predefvalue;
               }
            } else {
               echo "<input type='hidden' name='" . $predeffield . "' value='" . $predefvalue . "'>";
            }
         }
      }

      // override default ticket by hidden fields into ticket
      if (isset($tt->hidden) && count($tt->hidden) > 0) {
         foreach ($tt->hidden as $key_hidden => $value_hidden) {
            if (!array_key_exists($key_hidden, $options)) {
               echo "<input type='hidden' name='" . $key_hidden . "' value='" . $value_hidden . "'>";
            }
         }
      }

      $opt = ['name' => 'entities_id',
         'rand' => $rand,
         'on_change' => 'this.form.submit()',
         'value' => $options['entities_id']];

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo _n('Client', 'Clients', 2, 'manageentities');
      echo "</td>";
      echo "<td colspan='3'>";
      Entity::dropdown($opt);
      echo "</td>";
      echo "</tr>";

      //      $params = ['entities_id' => '__VALUE__', 'fieldname' => 'entities_id'];
      //      Ajax::updateItemOnSelectEvent("dropdown_entities_id$rand", "contract$rand", "../ajax/dropdownCustomer.php", $params);

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Type');
      echo "</td>";
      echo "<td colspan='3'>";
      /// Auto submit to load template
      $opt['on_change'] = 'this.form.submit()';
      $opt['value'] = $options['type'];

      $rand = $ticket::dropdownType('type', $opt);
      $params = ['type' => '__VALUE__',
         'entity_restrict' => $entities,
         'value' => $options['itilcategories_id'],
         'currenttype' => $options['type']];

      Ajax::updateItemOnSelectEvent("dropdown_type$rand", "show_category_by_type",
         "../ajax/dropdownGenerateCriCategories.php",
         $params);

      echo "</td>";
      echo "</tr>";


      $conditions = [];


      switch ($options['type']) {
         case Ticket::INCIDENT_TYPE :
            $conditions['is_incident'] = 1;
            break;

         case Ticket::DEMAND_TYPE :
            $conditions['is_request'] = 1;
            break;

         default :
            break;
      }

      if ($tt->isMandatoryField("itilcategories_id")
         && ($options["itilcategories_id"] > 0)) {
         $opt_categories['display_emptychoice'] = false;
      }

      $opt_categories['condition'] = $conditions;
      $opt_categories['on_change'] = 'this.form.submit()';
      $opt_categories['value'] = $options['itilcategories_id'];
      $opt_categories['entity'] = $options["entities_id"];


      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo sprintf(__('%1$s%2$s'), __('Category'),
         $tt->getMandatoryMark('itilcategories_id'));
      echo "</td>";
      echo "<td colspan='3'>";
      echo "<span id='show_category_by_type'>";
      ITILCategory::dropdown($opt_categories);
      echo "</span>";
      echo "</td>";
      echo "</tr>";

      if ($entities && !$config->getField('get_pdf_cri')) {
         self::showContractLinkDropdown($entities);
      }

      if (!$tt->isHiddenField('name')
         || $tt->isPredefinedField('name')) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . sprintf(__('%1$s%2$s'), __('Title'), $tt->getMandatoryMark('name')) . "</td>";
         echo "<td colspan='3'>";
         if (!$tt->isHiddenField('name')) {
            $opt = [
               'value' => $options['name'],
               'maxlength' => 250,
               'size' => 80,
            ];

            if ($tt->isMandatoryField('name')) {
               $opt['required'] = 'required';
            }
            echo Html::input('name', $opt);

         } else {
            echo $options['name'];
            echo "<input type='hidden' name='name' value=\"" . $options['name'] . "\">";
         }
         echo "</tr>";
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . $tt->getBeginHiddenFieldText('content');
      printf(__('%1$s%2$s'), __('Description'), $tt->getMandatoryMark('content'));
      echo $tt->getEndHiddenFieldText('content');
      echo "</td>";

      echo "<td colspan='3'>";
      echo $tt->getBeginHiddenFieldValue('content');
      $rand_text = mt_rand();
      $rows = 5;
      $content_id = "content$rand";

      $content = $options['content'];

      $content = Html::setRichTextContent(
         $content_id,
         $content,
         $rand
      );

      echo "<div id='content$rand_text'>";
      Html::textarea([
         'name' => 'content',
         'filecontainer' => 'content_info',
         'editor_id' => $content_id,
         'required' => $tt->isMandatoryField('content'),
         'rows' => $rows,
         'enable_richtext' => true,
         'value' => $content
      ]);
      echo "</div>";
      echo "</td>";

      if ($tt->isMandatoryField('urgency') || $tt->isPredefinedField('urgency')
         && $tt->isHiddenField('urgency')) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . $tt->getBeginHiddenFieldText('urgency');
         printf(__('%1$s%2$s'), __('Urgency'), $tt->getMandatoryMark('urgency'));
         echo $tt->getEndHiddenFieldText('urgency');
         echo "</td>";
         echo "<td colspan='3'>";
         echo $tt->getBeginHiddenFieldValue('urgency');
         Ticket::dropdownUrgency(['value' => $options['urgency']]);
         echo $tt->getEndHiddenFieldValue('urgency', $ticket);
         echo "</td>";
      }

      if ($tt->isMandatoryField('impact') || $tt->isPredefinedField('impact')
         && !$tt->isHiddenField('impact')) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . $tt->getBeginHiddenFieldText('impact');
         printf(__('%1$s%2$s'), __('Impact'), $tt->getMandatoryMark('impact'));
         echo $tt->getEndHiddenFieldText('impact');
         echo "</td>";
         echo "<td colspan='3'>";
         echo $tt->getBeginHiddenFieldValue('impact');
         Ticket::dropdownImpact(['value' => $options['impact']]);
         echo "</td>";
         echo "</tr>";
      }


      if ($tt->isMandatoryField('priority') || $tt->isPredefinedField('priority')
         && !$tt->isHiddenField('priority')) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>" . $tt->getBeginHiddenFieldText('priority');
         printf(__('%1$s%2$s'), __('Priority'), $tt->getMandatoryMark('priority'));
         echo $tt->getEndHiddenFieldText('priority');
         echo "</td>";
         echo "<td colspan='3'>";
         echo $tt->getBeginHiddenFieldValue('priority');
         Ticket::dropdownImpact(['value' => $options['priority']]);
         echo "</td>";
         echo "</tr>";

      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>";
      echo __('Technician');
      echo "</td>";
      echo "<td colspan='3'>";

      $user = new User();
      $dbu = new DbUtils();
      $condition = ['is_deleted' => 0];
      $users = $user->find($condition);
      $techs = [];
      foreach ($users as $data) {
         $techs[$data['id']] = $dbu->getUserName($data['id']);
      }

      Dropdown::showFromArray('users_intervenor', $techs, ['values' => $options["users_intervenor"],
         'multiple' => true,
         'entity' => $entities]);
      echo "</td>";
      echo "</tr>";

      if ($tasktemplate) {
         echo "<tr class='tab_bg_1' >";
         echo "<th colspan='4'>";
         echo __('Predefined task informations', 'manageentities');
         echo "</th>";
         echo "</tr>";
         echo "<tr class='tab_bg_1'>";
         echo "<td colspan='4'>";
         echo "<div style='margin: 10px; padding:10px; width:400px; border:dashed;'>";
         echo "<span style='font-weight:bold; font-size: 15px;'>" . _n('Task', 'Tasks', 1) . " : </span><br>";
         echo "<span style='font-weight:bold;'>" . __('Description') . " : </span>";
         echo "<span>" . Html::clean($tasktemplate->getField('content')) . "</span><br>";
         echo "<span style='font-weight:bold;'>" . __('Duration') . " : </span>";
         echo "<span>" . self::formatDuration($tasktemplate->getField('actiontime')) . "</span><br>";
         if ($tasktemplate->getField('groups_id_tech') > 0) {
            $group = new Group();
            $group->getFromDB($tasktemplate->getField('groups_id_tech'));
            echo "<span style='font-weight:bold;'>" . __('Technician group') . ": </span>";
            echo "<span>" . $group->getField('name') . "</span><br>";
         }
         echo "<input name ='predefined-task' type='hidden' value='" . $tasktemplate->fields['id'] . "'>";
         echo "</div>";
         echo "</td>";
         echo "</tr>";
      }

      echo "<tr class='tab_bg_1' >";
      echo "<th colspan='4'>";
      echo __('Accomplished tasks informations', 'manageentities');
      echo "&nbsp&nbsp<a onclick='addTaskOnView(false);' style='cursor:pointer;' id='img_add_cci' name='' ";
      echo "title='" . __('Add this Task', 'manageentities') . "'><i class='fa fa-plus-circle'></i></a>";
      echo "</th>";
      echo "</tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Description') . "</td>";

      echo "<td>";
      echo $tt->getBeginHiddenFieldValue('content');
      $rand_text = mt_rand();
      $rand = mt_rand();
      $rows = 5;
      $content_id = "content$rand";

      $content = isset($options['description']) ? $options['description'] : "";

      $content = Html::setRichTextContent(
         $content_id,
         $content,
         $rand
      );

      echo "<div id='content$rand_text'>";
      Html::textarea([
         'name' => 'description',
         'filecontainer' => 'content_info',
         'id' => 'description',
         'editor_id' => $content_id,
         'required' => $tt->isMandatoryField('description'),
         'rows' => $rows,
         'enable_richtext' => true,
         'value' => $content
      ]);
      echo "</div>";
      echo "</td>";

      echo "<td>" . __('Start date');
      echo "<br><br><span>" . __('Duration') . "</span>";
      echo "<br><br><br><i class='fas fa-user fa-fw' title='" . _n('User', 'Users', 1) . "'></i>";
      echo _n("Technician", "Technicians", 1, "manageentities");
      echo "<br><br><span>" . __('Category') . "</span>";
      echo "</td>";

      $heure = intval(date('H'));
      if ($heure < 12) {
         $date = strtotime(date('Y-m-d') . '+'.$config->getField("default_time_am").' sec');
      } else {
         $date = strtotime(date('Y-m-d') . '+'.$config->getField("default_time_pm").' sec');
      }
      echo "<td>";
      $date = date('Y-m-d H:i:s', $date);
      Html::showDateTimeField("plan[begin]", ['value' => $date, 'timestep' => -1, 'maybeempty' => false, 'canedit' => true, 'mindate' => '', 'maxdate' => '']);

      echo "<br><div>";

      $rand = Dropdown::showTimeStamp("plan[_duration]", ['value' => $config->getField("default_duration"),
         'min' => 0,
         'max' => 50 * HOUR_TIMESTAMP,
         'emptylabel' => __('Specify an end date')]);
      echo "<br><div id='date_end$rand'></div>";

      $event_options = ['duration' => '__VALUE__', 'name' => "plan[end]"];

      Ajax::updateItemOnSelectEvent("dropdown_plan[_duration]$rand", "date_end$rand", "../ajax/taskend.php", $event_options);
      echo "</div><br>";
      $params = ['name' => "users_id_tech",
         'right' => "own_ticket",
         'rand' => $rand_user,
         'value' => Session::getLoginUserID(),
         'entity' => $options["entities_id"],
         'width' => '80%'];

      echo "<div id='users_id_tech'>";
      User::dropdown($params);
      echo "</div><br>";


      $params = ['name' => "taskcategories_id",
                 'entity' => $options["entities_id"],
                 'value' => isset($options['taskcategories_id']) ? $options['taskcategories_id'] : 0 ];
      echo "<div id='taskcategories_id'>";
      TaskCategory::dropdown($params);
      echo "</div>";

      echo "</td>";
      echo "</tr>";

      $task_stored = json_encode([]);

      foreach ($options as $key => $value){
         if(strpos($key,"description") !== false){
            $options[$key] = str_replace('\r\n' , '' ,nl2br(Toolbox::unclean_cross_side_scripting_deep($value)));
         }
      }

      if (count($saved) > 0) {
         foreach ($saved as $key => $value) {
            if (strpos($key, 'begin') !== false && substr($key, strrpos($key, 'n') + 1) !== '') {
               $countTasks [] = substr($key, strrpos($key, 'n') + 1);
            }
         }
         if (count($countTasks) > 0) {
            $task_stored = json_encode(self::returnTasksStoreSession($countTasks, $saved));
         }
      } else {
         foreach ($options as $key => $value) {
            if (strpos($key, 'begin') !== false && substr($key, strrpos($key, 'n') + 1) !== '') {
               $countTasks [] = substr($key, strrpos($key, 'n') + 1);
            }
         }
         if (count($countTasks) > 0) {
            $task_stored = json_encode(self::returnTasksStoreSession($countTasks, $options));
         }
      }

      if (count($countTasks) > 0) {
         echo "<input name ='has_task' type='hidden' value='true'>";
      } else {
         echo "<input name ='has_task' type='hidden' value='false'>";
      }

      echo "<script> 

           $(document).ready(function() {
           let storedTasks = $task_stored; 
              addTaskOnView(true, storedTasks);
           });

            function removeBlockTask(taskcount) {
                
               $(\"#task_\" + taskcount).remove();
              let taskCountDone   = $('#tasks').children('div').last().attr('data-index');
             
              if (taskCountDone === undefined) {
                $('[name =\"has_task\"]').val('false');
              }
            }
            
            function addTaskOnView(isOnRefresh, storedTasks = []) {

              let description = '';
              let duration    = '';
              let begin       = '';
              let end         = '';
              let userIdTech  = '';
              let tasksCategory  = '';
                 
                 
              if (Object.keys(storedTasks).length) {
               $('#tab-tasks').show();
                  $.each(storedTasks, function(taskcount, value) {
                       description = value['description'];
                       duration    = value['duration'];
                       begin       = value['begin'];
                       end         = value['end'];
                       userIdTech  = value['users_id_tech'];
                       tasksCategory  = value['taskcategories_id'];
                    
                    
              let durationDisplay = secondsToHm(duration);
              
              let taskCount = taskcount;
              
            //first element
              if (taskCount === undefined) {
                  taskCount = 0;
              }
              
              var blocTask = getBlockTask(taskCount, description, userIdTech, begin, end, duration, durationDisplay,tasksCategory);
              getUserName(userIdTech,taskCount);
        
                $('#tasks').append(blocTask);   
               }); 
               
           } else if (!isOnRefresh) {
                  
               $('#tab-tasks').show();
                description = tinyMCE.get($('textarea[name =\"description\"]')[0].id).getContent();
                duration    = $('[name =\"plan[_duration]\"]').val();
                begin       = $('[name =\"plan[begin]\"]').val();
                end         = $('[name =\"plan[end]\"]').val();
                userIdTech  = $('[name =\"users_id_tech\"]').val();
                tasksCategory  = $('[name =\"taskcategories_id\"]').val();
                           
                if (description == '' || begin == ''  || userIdTech == 0 || end === undefined  && duration == 0) {
                    alert('" . __('Content, end and begin date are mandatory for a task !', 'manageentities') . "');                           
              } else if (tasksCategory == 0 && ".$config->fields['hourorday']." == ".PluginManageentitiesConfig::HOUR." ) {
                    alert('" . __('Task category must be defined', 'manageentities') . "');                           
              } else if (end <= begin) {
                    alert('" . __('End date must be after the begin date !', 'manageentities') . "');
              } else {
                //convert duration for display
              let durationDisplay = secondsToHm(duration);
              
              let taskCount = $('#tasks').children('div').last().attr('data-index');

               //first element
              if (taskCount === undefined) {
                  taskCount = 0;
              }
              taskCount ++;
              
             var blocTask = getBlockTask(taskCount, description, userIdTech, begin, end, duration, durationDisplay,tasksCategory);
             getUserName(userIdTech,taskCount);
              $('[name =\"has_task\"]').val('true');
              
              $('#tasks').append(blocTask);   
              
              tinyMCE.get($('textarea[name =\"description\"]')[0].id).setContent('');
              $('[name =\"plan[_duration]\"]').val();
              $('[name =\"plan[begin]\"]').val();  
              $('[name =\"users_id_tech\"]').val(); 
              $('[name =\"taskcategories_id\"]').val(); 
                 }
              }
         };
            
            function getBlockTask(taskCount, description, userIdTech, begin, end, duration, durationDisplay,tasksCategory) {
              var  blocTask  = '<div data-index=\"' + taskCount + '\" style=\"margin: 10px; padding:10px; width:100 %; border:dashed;\" id=\"task_' + taskCount + '\" >';
               blocTask += '<tr class=\"tab_bg_1\">';
               blocTask += '<a onclick=\"removeBlockTask(' + taskCount + ');\" \"style = \"cursor:pointer;\" ><i style = \"float:right;\" class=\"fas fa-minus-circle\" ></i ></a> ';
               blocTask += '<span style = \"font-weight:bold; font-size: 15px;\" >".__('Task')." :</span><br> ';
               blocTask += '<span style = \"font-weight:bold;\" >". __("Description", 'servicecatalog')." : </span ><span> ' + description + ' </span><br> ';
               blocTask += '<span style = \"font-weight:bold;\"  ". __('Technician as assigned','manageentities')." : </span><span id=\"user_tech_name' + taskCount +'\"></span><br> ';
               blocTask += '<span style = \"font-weight:bold;\" >".__('Begin date')." : </span ><span> ' + dateToYMD(begin) + ' </span><br> ';
               blocTask += (end == undefined || end == 'undefined') ? '' : ' <span style = \"font-weight:bold;\"> '+ __('End date') + ' : </span><span> ' + dateToYMD(end) + ' </span><br> ';
               blocTask += (duration > 0) ? ' <span style = \"font-weight:bold;\" >".__('Duration')." : </span><span> ' + durationDisplay + ' </span><br> ' : '';
               blocTask += ' <input name = \"duration' + taskCount + '\" type = \"hidden\" value = \"' + duration + '\"\>';
               blocTask += ' <input name = \"begin' + taskCount + '\" type = \"hidden\" value = \"' + begin + '\"\>';
               blocTask += ' <input name = \"end' + taskCount + '\" type = \"hidden\" value = \"' + end + '\"\>';
               blocTask += ' <input name = \"description' + taskCount + '\" type = \"hidden\" value = \"' + description + '\"\>';
               blocTask += ' <input name = \"users_id_tech' + taskCount + '\" type = \"hidden\" value = \"' + userIdTech + '\"\>';
               blocTask += ' <input name = \"taskcategories_id' + taskCount + '\" type = \"hidden\" value = \"' + tasksCategory + '\"\>';
               blocTask += ' </tr></div> ';
               
               return blocTask;
            }
            
            function dateToYMD(dateToConvert) {
                let newDate = new Date(dateToConvert);
                var d = newDate.getDate();
                var m = newDate.getMonth() + 1;
                var y = newDate.getFullYear();
                var hh = newDate.getHours();
                var mm = newDate.getMinutes();
                var ss = newDate.getSeconds();
                return '' + (d <= 9 ? '0' + d : d) + '-' + (m <=9 ? '0' + m : m) + '-' +  y + ' ' +
                              (hh <= 9 ? '0' + hh : hh) + ':' + (mm <= 9 ? '0' + mm : mm) + ':' + (ss <= 9 ? '0' + ss : hh) ;
            }
            
            function secondsToHm(d) {
                d = Number(d);
                var h = Math.floor(d / 3600);
                var m = Math.floor(d % 3600 / 60);
           
                var hDisplay = h > 0 ? h + (h == 1 ? \" h \" : \" h \") : \"\";
                var mDisplay = m > 0 ? m + (m == 1 ? \" m \" : \" m \") : \"\";
                return hDisplay + mDisplay; 
            }
            
             function getUserName(userIdTech,taskCount) {
                   return $.ajax({
                             url   : CFG_GLPI.root_doc + '/plugins/manageentities/ajax/getUserTechName.php',
                             type  : 'POST',
                             data  : {
                                     'user_id_tech': userIdTech,
                             },
                             success:function(data) {
                                   $('#user_tech_name' + taskCount).html(JSON.parse(data));
                                 }
                           });
            }
            
           </script>";

      echo "<tr class='tab_bg_1' id='tab-tasks' style='display: none'>";
      echo "<td colspan='4'>";
      echo "<div style='width: 400px;' id='tasks'></div>";
      echo "</td>";
      echo "</tr>";

      if ($config->getField("non_accomplished_tasks")) {
         echo "<tr class='tab_bg_1' >";
         echo "<th colspan='4'>";
         echo __('Non-accomplished tasks informations', 'manageentities');
         echo "</th>";
         echo "</tr>";

         echo "<tr class='tab_bg_1'>";
         echo "<td>" . __('Description') . "</td>";
         echo "<td colspan='3'>";
         echo $tt->getBeginHiddenFieldValue('content');
         $rand_text = mt_rand();
         $rand = mt_rand();
         $rows = 5;
         $content_id = "content$rand";

         $content = isset($options['description-undone']) ? $options['description-undone'] : "";

         $content = Html::setRichTextContent(
            $content_id,
            $content,
            $rand
         );

         echo "<div id='content$rand_text'>";
         Html::textarea([
            'name' => 'description-undone',
            'filecontainer' => 'content_info',
            'id' => 'description-undone',
            'editor_id' => $content_id,
            'required' => $tt->isMandatoryField('description-undone'),
            'rows' => $rows,
            'enable_richtext' => true,
            'value' => $content
         ]);
         echo "</div>";
         echo "</td>";
         echo "</tr>";
      }


      echo "<tr class='tab_bg_2'>";
      echo "<td class='center' colspan='4'>";
      echo "<input type='submit' name='generatecri' value='" . _sx('button', 'Generate', 'manageentities') . "' class='submit'>";
      echo "</td></tr>";
      echo "</table></div>";

      Html::closeForm();

   }

   /**
    * @param $input
    *
    * @return bool|int
    */
   static function createTicketAndAssociateContract($input) {

      $ticket = new Ticket();
      $allowed_fields = [
         'type',
         'itilcategories_id',
         'name',
         'content',
         'urgency',
         'entities_id',
         'impact',
         'status',
         'priority',
         'locations_id',
         '_groups_id_assign',
         '_groups_id_requester',
         '_groups_id_observer',
         '_users_id_requester',
         '_users_id_observer',
         '_groups_id_assign',
         'requesttypes_id',
         //         'internal_time_to_own',
         //         'olas_id_tto',
         //         'olas_id_ttr',
         //         'internal_time_to_resolve'
      ];

      $inputs = [];

      foreach ($input as $key => $value) {
         if (in_array($key, $allowed_fields)) {
            switch ($key) {
               //               case 'content':
               //               case 'name':
               //                  $inputs[$key] = addslashes($value);
               //                  break;
               default :
                  $inputs[$key] = $value;
                  break;
            }
         }
      }
      $inputs['status'] = CommonITILObject::PLANNED;

      $ticketId = $ticket->add($inputs);

      if ($ticketId) {
         foreach ($input['users_intervenor'] as $user_assign) {
            $user_ticket = new Ticket_User();
            if (!$user_ticket->getFromDBByCrit(['tickets_id' => $ticketId,
               'users_id' => $user_assign,
               'type' => Ticket_User::ASSIGN])) {
               $user_ticket->add(['tickets_id' => $ticketId,
                  'users_id' => $user_assign,
                  'type' => Ticket_User::ASSIGN]);
            }
         }
         return $ticketId;
      }
   }

   static function createTicketTaskUndone($input, $tickets_id) {
      $ticket = new Ticket();
      $ticket_ticket = new Ticket_Ticket();

      $allowed_fields = [
         'type',
         'itilcategories_id',
         'name',
         'content',
         'urgency',
         'entities_id',
         'impact',
         'status',
         'priority',
         'locations_id',
         '_groups_id_assign',
         '_groups_id_requester',
         '_groups_id_observer',
         '_users_id_requester',
         '_users_id_observer',
         '_groups_id_assign',
         'requesttypes_id',
         //         'internal_time_to_own',
         //         'olas_id_tto',
         //         'olas_id_ttr',
         //         'internal_time_to_resolve'
      ];

      $inputs = [];

      foreach ($input as $key => $value) {
         if (in_array($key, $allowed_fields)) {
            switch ($key) {
               default :
                  $inputs[$key] = $value;
                  break;
            }
         }
      }
      $inputs['status'] = CommonITILObject::INCOMING;

      $ticketId = $ticket->add($inputs);

      $ticket_ticket->add(['tickets_id_1' => $ticketId, 'tickets_id_2' => $tickets_id, 'link' => '3']);
      if ($ticketId) {
         foreach ($input['users_intervenor'] as $user_assign) {
            $user_ticket = new Ticket_User();
            if (!$user_ticket->getFromDBByCrit(['tickets_id' => $ticketId,
               'users_id' => $user_assign,
               'type' => Ticket_User::ASSIGN])) {
               $user_ticket->add(['tickets_id' => $ticketId,
                  'users_id' => $user_assign,
                  'type' => Ticket_User::ASSIGN]);
            }
         }
         return $ticketId;
      }
   }


   /**
    * @param $inputs
    * @param $ticket_id
    *
    * @return bool
    */
   static function createTasks($inputs, $ticket_id) {

      if (isset($inputs['predefined-task'])) {
         $task_template = new TaskTemplate();
         $task_template_id = $inputs['predefined-task'];
         $task_template->getFromDB($task_template_id);

         $ticket_task = new TicketTask();
         $user_ticket_task = $task_template->getField('users_id_tech') > 0 ?
            $task_template->getField('users_id_tech') : Session::getLoginUserID();

         $input = ['tasktemplates_id' => $task_template_id,
            'taskcategories_id' => $task_template->getField('tasktemplates_id'),
            'tickets_id' => $ticket_id,
            'users_id' => Session::getLoginUserID(),
            'users_id_tech' => $user_ticket_task,
            'content' => $task_template->getField('content'),
            'state' => $task_template->getField('state'),
            'groups_id_tech' => $task_template->getField('groups_id_tech'),
            'actiontime' => $task_template->getField('actiontime'),
            'is_private' => $task_template->getField('is_private')];

         $ticket_task->add($input);
      }

      $inputs['_plan'] = [];
//      $inputs['plan']  = [];
      $hasDuration = false;
      $hasBegin = false;
      $hasEnd = false;
      $hasDescription = false;
      $hasTech = false;
      unset($inputs['description-undone']);
      if ($inputs['has_task'] == "true") {
         unset($inputs['description']);
      }

      $countTasks = [];

      foreach ($inputs as $key => $value) {
         if (strpos($key, 'description') !== false) {
            if ($key == "description") {
               $countTasks[] = 0;
               $inputs['description0'] = $value;
               $inputs['duration0'] = $inputs['plan']['_duration'];
               $inputs['begin0'] = $inputs['plan']['begin'];
               $inputs['users_id_tech0'] = $inputs['users_id_tech'];
               $inputs['taskcategories_id0'] = $inputs['taskcategories_id'];
               $inputs['end0'] = isset($inputs['plan']['end']) ? $inputs['plan']['end'] : "undefined";

            } else {
               $countTasks [] = substr($key, strrpos($key, 'n') + 1);
            }
         }
      }

      foreach ($countTasks as $countTask) {
         foreach ($inputs as $key => $value) {

            if (strpos($key, 'description') !== false) {
               if ($key == 'description' . $countTask) {
                  $inputs['description'] = $inputs['description' . $countTask];
                  $hasDescription = true;
               }
            }

            if (strpos($key, 'duration') !== false) {
               if ($key == 'duration' . $countTask) {
                  $inputs['plan']['_duration'] = $inputs['duration' . $countTask];
                  $hasDuration = true;
               }
            }

            if (strpos($key, 'users_id_tech') !== false) {
               if ($key == 'users_id_tech' . $countTask) {
                  $inputs['users_id_tech'] = $inputs['users_id_tech' . $countTask];
                  $hasTech = true;
               }
            }

            if (strpos($key, 'taskcategories_id') !== false) {
               if ($key == 'taskcategories_id' . $countTask) {
                  $inputs['taskcategories_id'] = $inputs['taskcategories_id' . $countTask];
                  $hasTech = true;
               }
            }

            if (strpos($key, 'begin') !== false) {
               if ($key == 'begin' . $countTask) {
                  $new_date = date('d-m-Y H:i', strtotime($inputs['begin' . $countTask]));
                  $inputs['plan']['begin'] = $inputs['begin' . $countTask];
                  $inputs['_plan']['begin'] = $new_date;
                  $hasBegin = true;
               }
            }

            if (strpos($key, 'end') !== false) {
               if ($key == 'end' . $countTask) {
                  if ($inputs['end' . $countTask] != 'undefined') {
                     $new_date = date('d-m-Y H:i', strtotime($inputs['end' . $countTask]));
                     $inputs['plan']['end'] = $inputs['end' . $countTask];
                     $inputs['_plan']['end'] = $new_date;
                  }
                  $hasEnd = true;
               }

            }

            if ($hasBegin && $hasDuration && $hasEnd && $hasDescription && $hasTech) {
               $ticket_task = new TicketTask();
               $ticket_task->add(['tickets_id' => $ticket_id,
                  'users_id' => Session::getLoginUserID(),
                  'users_id_tech' => $inputs['users_id_tech'],
                  'taskcategories_id' => $inputs['taskcategories_id'],
                  '_plan' => $inputs['_plan'],
                  'plan' => $inputs['plan'],
                  'content' => $inputs['description'],
                  'state' => self::TASK_DONE]);
               $hasDuration = false;
               $hasDescription = false;
               $hasBegin = false;
               $hasEnd = false;
               $hasTech = false;
            }
         }
      }
      return true;
   }

   /**
    * @param $ticket_id
    *
    * @return string
    * @throws \GlpitestSQLError
    */
   public static function getDescriptionFromTasks($ticket_id) {
      global $DB, $CFG_GLPI;
      $config = PluginManageentitiesConfig::getInstance();

      /*
       * Information complémentaire pour la description globale du CRI.
       * Préremplissage avec les informations des suivis non privés.
       */
      $desc = "";
      $join = "";
      $and = "";
      $query = "";

      if ($config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
         $join = " LEFT JOIN `glpi_plugin_manageentities_taskcategories`
                        ON (`glpi_plugin_manageentities_taskcategories`.`taskcategories_id` =
                        `glpi_tickettasks`.`taskcategories_id`)";
         $and = " AND `glpi_plugin_manageentities_taskcategories`.`is_usedforcount` = 1";
      }

      if ($config->fields['use_publictask'] == PluginManageentitiesConfig::HOUR) {
         $query = "SELECT `content`, `begin`, `end`
                   FROM `glpi_tickettasks` $join
                   WHERE `tickets_id` = '" . $ticket_id . "'
                   AND `is_private` = 0 $and";
      } else {
         $query = "SELECT `content`, `begin`, `end`
                   FROM `glpi_tickettasks` $join
                   WHERE `tickets_id` = '" . $ticket_id . "' $and";
      }

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      if ($number) {
         while ($data = $DB->fetchArray($result)) {
            $desc .= $data["content"] . "\n\n";
         }
      }

      return $desc;
   }

   /**
    * @param $inputs
    * @param $ticket_id
    * @param $PluginManageentitiesCri
    */
   static function generateCri($inputs, $ticket_id, $PluginManageentitiesCri) {
      global $DB, $CFG_GLPI;

      $config = PluginManageentitiesConfig::getInstance();
      if (!$config->getField("get_pdf_cri")) {
         $PluginManageentitiesCriPrice = new PluginManageentitiesCriPrice();
         $desc = self::getDescriptionFromTasks($ticket_id);
         $critypes = '';
         if (isset($inputs['plugin_manageentities_contractdays_id'])
            && $inputs['plugin_manageentities_contractdays_id'] > 0) {
            $critypes = $PluginManageentitiesCriPrice->getItems($inputs['plugin_manageentities_contractdays_id']);
         }
         $critypes_default = 0;

         if (!empty($critypes)) {
            foreach ($critypes as $value) {
               $critypes_default = $value['plugin_manageentities_critypes_id'];
            }
         }

         $desc = substr($desc, 0, strlen($desc) - 2);

         $input['REPORT_ID'] = $ticket_id;
         $input['users_id'] = Session::getLoginUserID();
         $input['CONTRAT'] = isset($inputs['contracts_id']) ?
            $inputs['contracts_id'] : 0;
         $input['CONTRACTDAY'] = isset($inputs['plugin_manageentities_contractdays_id']) ?
            $inputs['plugin_manageentities_contractdays_id'] : 0;
         $input['WITHOUTCONTRACT'] = (isset($inputs['contracts_id']) && $inputs['contracts_id']) > 0 ? false : true;
         $input['REPORT_ACTIVITE'] = $critypes_default;
         $input['REPORT_DESCRIPTION'] = $desc;
         $input['entities_id'] = $inputs['entities_id'];
         $input['enregistrement'] = true;
//      $input['download']           = isset($inputs['download']) ? $inputs['download'] : 0;
         $PluginManageentitiesCri->generatePdf($input);

      } else {
         $ticket = new Ticket();
         $ticket->getFromDB($ticket_id);

         Html::header(__('Entities portal', 'manageentities'), '', "helpdesk", "pluginmanageentitiesgeneratecri");
         PluginManageentitiesCriDetail::displayTabContentForItem($ticket);

      }

   }

   /**
    * @param        $entities_id
    * @param string $type
    *
    * @return array
    * @throws \GlpitestSQLError
    */
   static function showContractLinkDropdown($entities_id, $type = 'ticket') {
      global $DB, $CFG_GLPI;

      $contract = new contract();
      $contract->getEmpty();
      $rand = mt_rand();
      $width = 300;

      $query = "SELECT DISTINCT(`glpi_contracts`.`id`),
                       `glpi_contracts`.`name`,
                       `glpi_contracts`.`num`,
                       `glpi_plugin_manageentities_contracts`.`contracts_id`,
                       `glpi_plugin_manageentities_contracts`.`id` as ID_us,
                       `glpi_plugin_manageentities_contracts`.`is_default` as is_default
               FROM `glpi_contracts`
               LEFT JOIN `glpi_plugin_manageentities_contracts`
                    ON (`glpi_plugin_manageentities_contracts`.`contracts_id` = `glpi_contracts`.`id`)
               WHERE `glpi_plugin_manageentities_contracts`.`entities_id` = '" . $entities_id . "'
               ORDER BY `glpi_contracts`.`name` ";

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $selected = false;
      $contractSelected = 0;
      $contractdaySelected = 0;

      // Display contract
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __('Intervention with contract', 'manageentities') . "</td>";
      echo "<td>";
      if ($number) {
         if ($type == 'ticket') {
            $elements = [Dropdown::EMPTY_VALUE];
            $value = 0;
            while ($data = $DB->fetchArray($result)) {
               if ($data["id"]) {
                  $selected = true;
                  $value = $data["id"];
               } else if ($data["is_default"] == '1' && !$selected) {
                  $contractSelected = $data['contracts_id'];
                  $value = $data["id"];
               }

               if (PluginManageentitiesContract::checkRemainingOpenContractDays($data["id"])) {
                  $elements[$data["id"]] = $data["name"] . " - " . $data["num"];
               }
            }
            if ($value == 0 && count($elements) == 2) {
               unset($elements[0]);
            }
            $rand = Dropdown::showFromArray('contracts_id', $elements, ['value' => $value, 'width' => $width]);
         } else {
            while ($data = $DB->fetchArray($result)) {
            }
            if ($contractSelected) {
               echo Dropdown::getDropdownName('glpi_contracts', $contractSelected);
            }
         }
      } else {
         echo __('No active contracts', 'manageentities');
      }

      if ($number) {

         // Tooltip for contract
         if (!empty($contractSelected)) {
            echo '&nbsp;';
            $contract->getFromDB($contractSelected);
            Html::showToolTip($contract->fields['comment'], ['link' => $contract->getLinkURL(),
               'linktarget' => '_blank']);
         }

         // Ajax for contract
         $params = ['contracts_id' => '__VALUE__',
            'contractdays_id' => $contractdaySelected,
            'current_contracts_id' => $contractSelected,
            'width' => $width];
         Ajax::updateItemOnSelectEvent("dropdown_contracts_id$rand", "show_contractdays", $CFG_GLPI["root_doc"] . "/plugins/manageentities/ajax/dropdownContract.php", $params);
         Ajax::updateItem("show_contractdays", $CFG_GLPI["root_doc"] . "/plugins/manageentities/ajax/dropdownContract.php", $params, "dropdown_contracts_id$rand");
         echo "</td>";

         // Display contract day
         echo "<td>" . __('Periods of contract', 'manageentities') . "</td>";
         echo "<td>";
         $restrict = ['entities_id' => $contract->fields['entities_id'],
            'contracts_id' => $contractSelected];
         $restrict += ['NOT' => ['plugin_manageentities_contractstates_id' => 2]]; //Closed contract was 8, is now 2
         if ($type == 'ticket') {
            echo "<span id='show_contractdays'>";
            Dropdown::show('PluginManageentitiesContractDay', ['name' => 'plugin_manageentities_contractdays_id',
               'value' => $contractdaySelected,
               'condition' => $restrict,
               'width' => $width]);
            echo "</span>";
         } else {
            echo Dropdown::getDropdownName('glpi_plugin_manageentities_contractdays', $contractdaySelected);
         }
         echo "</td>";
         echo "</tr>";

         return ['contractSelected' => $contractSelected,
            'contractdaySelected' => $contractdaySelected,
            'is_contract' => $number];
      }
   }

   /**
    * Save the input data in the Session
    *
    * @return void
    **@since 0.84
    *
    */
   protected function saveInput($input) {
      $_SESSION['saveInput'][$this->getType()] = $input;
   }

   /**
    * Clear the saved data stored in the session
    *
    * @return void
    **@since 0.84
    *
    */
   protected function clearSavedInput() {
      unset($_SESSION['saveInput'][$this->getType()]);
   }

   /**
    * @param $input
    *
    * @return bool
    */
   function checkMandatoryFields($input) {
      $msg = [];
      $checkKo = false;

      $this->saveInput($input);
      // check if categories and at least one tech for tasks. check if the customer entity are at least contract even
      //if we don't choose one
      $mandatory_fields = ['itilcategories_id' => __('Category'),
         'users_intervenor' => __('Technician as assigned'),
         'description' => __('Description'),
         'has_task' => __('Task')];

      foreach ($input as $key => $value) {
         if (array_key_exists($key, $mandatory_fields)) {
            if ($key == 'has_task' && !array_key_exists('predefined-task', $input) && !array_key_exists('description', $input)) {
               if ($value == 'false') {
                  $msg[] = $mandatory_fields[$key];
                  $checkKo = true;
               }
            } else {
               if (empty($value) && !array_key_exists('description1', $input)) {
                  $msg[] = $mandatory_fields[$key];
                  $checkKo = true;
               }
            }
         }
      }

      if (!array_key_exists('users_intervenor', $input)) {
         $msg[] = $mandatory_fields['users_intervenor'];
         $checkKo = true;
      }

      $config = PluginManageentitiesConfig::getInstance();
      if ($input['taskcategories_id'] == 0 && $config->fields['hourorday'] == PluginManageentitiesConfig::HOUR) {
         $msg[] = _n('Task category','Task categories',1);
         $checkKo = true;
      }

      if ($checkKo) {
         Session::addMessageAfterRedirect(sprintf(__("Mandatory fields are not filled. Please correct: %s"),
            implode(', ', $msg)), false, ERROR);
         return true;
      }
      return $checkKo;
   }

   /**
    * Format a duration into a human-readable time.
    *
    * @param float $duration
    *   Duration in seconds, with fractional component.
    *
    * @return string
    */
   public static function formatDuration($duration) {
      if ($duration >= self::DAY * 2) {
         return gmdate('z \d\a\y\s H:i:s', $duration);
      }
      if ($duration > self::DAY) {
         return gmdate('\1 \d\a\y H:i:s', $duration);
      }
      if ($duration > self::HOUR) {
         return gmdate("H:i:s", $duration);
      }
      if ($duration > self::MINUTE) {
         return gmdate("i:s", $duration);
      }
      return round($duration, 3) . 's';
   }

   /**
    * Get the data saved in the session
    *
    * @param array $default Array of value used if session is empty
    *
    * @return array Array of value
    **@since 0.84
    *
    */
   protected function restoreInput(array $default = []) {

      if (isset($_SESSION['saveInput'][$this->getType()])) {
         $saved = Html::cleanPostForTextArea($_SESSION['saveInput'][$this->getType()]);

         // clear saved data when restored (only need once)
         $this->clearSavedInput();

         return $saved;
      }

      return $default;
   }

   protected function returnTasksStoreSession($countTasks, $inputs) {
      foreach ($countTasks as $countTask) {
         foreach ($inputs as $key => $value) {
            if (strpos($key, 'description') !== false) {
               if ($key == 'description' . $countTask) {
                  $task_stored[$countTask]['description'] = $inputs['description' . $countTask];
               }
            }

            if (strpos($key, 'duration') !== false) {
               if ($key == 'duration' . $countTask) {
                  $task_stored[$countTask]['duration'] = $inputs['duration' . $countTask];
               }
            }

            if (strpos($key, 'begin') !== false) {
               if ($key == 'begin' . $countTask) {
                  $task_stored[$countTask]['begin'] = $inputs['begin' . $countTask];
               }
            }

            if (strpos($key, 'end') !== false) {
               if ($key == 'end' . $countTask) {
                  $task_stored[$countTask]['end'] = $inputs['end' . $countTask];
               }
            }

            if (strpos($key, 'state') !== false) {
               if ($key == 'state' . $countTask) {
                  $task_stored[$countTask]['state'] = $inputs['state' . $countTask];
               }
            }

            if (strpos($key, 'users_id_tech') !== false) {
               if ($key == 'users_id_tech' . $countTask) {
                  $task_stored[$countTask]['users_id_tech'] = $inputs['users_id_tech' . $countTask];
               }
            }
            if (strpos($key, 'taskcategories_id') !== false) {
               if ($key == 'taskcategories_id' . $countTask) {
                  $task_stored[$countTask]['taskcategories_id'] = $inputs['taskcategories_id' . $countTask];
               }
            }
         }
      }
      return $task_stored;
   }

}
