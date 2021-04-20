/* global CFG_GLPI */
window.actualTime = new function() {
   this.ajax_url = CFG_GLPI.root_doc + '/plugins/actualtime/ajax/timer.php';
   var timer;
   var popup_div = '';
// Translations
   var symb_d = '%dd';
   var symb_day = '%d day';
   var symb_days = '%d days';
   var symb_h = '%dh';
   var symb_hour = '%d hour';
   var symb_hours = '%d hours';
   var symb_min = '%dmin';
   var symb_minute = '%d minute';
   var symb_minutes = '%d minutes';
   var symb_s = '%ds';
   var symb_second = '%d second';
   var symb_seconds = '%d seconds';
   var text_warning = 'Warning';
   var text_pause = 'Pause';
   var text_restart = 'Restart';
   var text_done = 'Done';

   this.showTaskForm = function(e) {
      e.preventDefault();
      $('<div>')
         .dialog({
            modal: true,
            width: 'auto',
            height: 'auto',
         })
         .load(this.ajax_url + '?showform=true', function () {
            $(this).dialog('option', 'position', ['center', 'center']);
            var div = $(this).parent();
            var of = div.offset();
            div.css('position', 'fixed');
            div.offset(of);
         });
   }

   this.timeToText = function(time, format) {
      var days = 0;
      var hours = 0;
      var minutes = 0;
      var distance = time;
      var seconds = distance % 60;
      distance -= seconds;
      var text = (format == 3 ? (seconds > 1 ? symb_seconds : symb_second) : symb_s).replace('%d', seconds);
      ;
      if (distance > 0) {
         minutes = (distance % 3600) / 60;
         distance -= minutes * 60;
         text = (format == 3 ? (minutes > 1 ? symb_minutes : symb_minute) : symb_min).replace('%d', minutes) + ' ' + text;
         if (distance > 0) {
            if (format == 2) {
               hours = distance / 3600;
               if (minutes < 10) {
                  minutes = '0' + minutes;
               }
               return symb_h.replace('%d', hours) + (seconds > 0 ? symb_min.replace('%d', minutes) + symb_s.replace('%d', (seconds < 10 ? '0' : '') + seconds) : minutes);
            }
            hours = (distance % 86400) / 3600;
            distance -= hours * 3600;
            text = (format == 3 ? (hours > 1 ? symb_hours : symb_hour) : symb_h).replace('%d', hours) + ' ' + text;
            if (distance > 0) {
               days = distance / 86400;
               text = (format == 3 ? (days > 1 ? symb_days : symb_day) : symb_d).replace('%d', days) + ' ' + text;
            }
         }
      }
      return text;
   }

   this.showTimerPopup = function(ticket) {
      $("#actualtime_popup").remove();
      // only if enabled in settings
      if (popup_div) {
         $("body").append(popup_div.replace(/%t/g, ticket));
         $("#actualtime_popup").attr('title', text_warning);
         $(function () {
            var _of = window;
            var _at = 'left+20 bottom-20';
            //calculate relative dialog position
            $('.message_result').each(function () {
               var _this = $(this);
               if (_this.attr('aria-describedby') != 'message_result') {
                  _of = _this;
                  _at = 'right top-' + (10 + _this.outerHeight());
               }
            });
            $("#actualtime_popup")
               .attr('title', text_warning)
               .dialog({
                  dialogClass: 'message_after_redirect warn_msg',
                  minHeight: 40,
                  minWidth: 200,
                  position: {
                     my: 'left bottom',
                     at: _at,
                     of: _of,
                     collision: 'none'
                  },
                  autoOpen: false,
                  show: {
                     effect: 'slide',
                     direction: 'down',
                     'duration': 800
                  }
               })
               .dialog('open');
         });
         setTimeout(function () {
            // Transform in position:fixed to solve dialog bug
            of = $("#actualtime_popup").parent().offset();
            $("#actualtime_popup").parent().css('position', 'fixed');
            $("#actualtime_popup").parent().offset(of);
         }, 1000);
      }
   }

   this.startCount = function(task, time) {
      timer = setInterval(function () {
         time += 1;
         var timestr = window.actualTime.timeToText(time, 1);
         $("[id^='actualtime_timer_" + task + "_']").text(timestr);
         $("#actualtime_popup span").text(timestr);
      }, 1000);
   }

   this.endCount = function() {
      clearInterval(timer);
   }

   this.fillCurrentTime = function(task, time) {
      var timestr = window.actualTime.timeToText(time, 1);
      $("[id^='actualtime_timer_" + task + "_']").text(timestr);
   }

   this.pressedButton = function(task, val) {
      jQuery.ajax({
         type: "POST",
         url: this.ajax_url,
         dataType: 'json',
         data: {action: val, task_id: task},
         success: function (result) {
            if (result['class'] == 'info_msg') {
               if (val == 'start') {
                  window.actualTime.startCount(task, result['time']);
                  $("[id^='actualtime_timer_" + task + "_']").css('color', 'red');
                  $("[id^='actualtime_button_" + task + "_1_']").attr('value', text_pause).attr('action', 'pause').css('background-color', 'orange').prop('disabled', false);
                  $("[id^='actualtime_button_" + task + "_2_']").attr('action', 'end').css('background-color', 'red').prop('disabled', false);
                  window.actualTime.showTimerPopup(result['ticket_id']);
                  $("[id^='actualtime_faclock_" + task + "_']").addClass('fa-clock-o').css('color', 'red');
                  return;
               } else if ((val == 'end') || (val == 'pause')) {
                  window.actualTime.endCount();
                  $("#actualtime_popup").remove();
                  // Update all forms of this task (normal and modal)
                  $("[id^='actualtime_timer_" + task + "_']").css('color', 'black');
                  $("[id^='actualtime_faclock_" + task + "_']").css('color', 'black');
                  var timestr = window.actualTime.timeToText(result['time'], 1);
                  $("[id^='actualtime_timer_" + task + "_']").text(timestr);
                  $("[id^='actualtime_segment_" + task + "_']").html(result['segment']);
                  if (val == 'end') {
                     // Update state fields also (as Done)
                     $("select[name='state']").attr('data-track-changes', '');
                     $("span.state.state_1[onclick='change_task_state(" + task + ", this)']").attr('title', text_done).toggleClass('state_1 state_2');
                     $("input[type='hidden'][name='id'][value='" + task + "']").closest("table#mainformtable").find("select[name='state']").val(2).trigger('change');
                     $("select[name='state']").removeAttr('data-track-changes');
                     $("[id^='actualtime_button_" + task + "_']").attr('action', '').css('background-color', 'gray').prop('disabled', true);
                     if (typeof result["duration"] !== 'undefined') {
                        var actiontime = $("input[type='hidden'][name='id'][value='" + task + "']").closest("table#mainformtable").find("select[name='actiontime']");
                        actiontime.attr('data-track-changes', '');
                        actiontime.val(result['duration']).trigger('change');
                        actiontime.removeAttr('data-track-changes');
                        $("div#viewitemtickettask" + task + " span.actiontime").text(window.actualTime.timeToText(result['duration'], 1));
                     }
                  } else {
                     $("[id^='actualtime_button_" + task + "_1_']").attr('value', text_restart).attr('action', 'start').css('background-color', 'green').prop('disabled', false);
                  }
               }
            }
            $('#message_result').html(result['mensage']);
            $('#message_result').attr('title', result['title']);
            $(function () {
               var _of = window;
               var _at = 'left+20 bottom-20';
               //calculate relative dialog position
               $('.message_result').each(function () {
                  var _this = $(this);
                  if (_this.attr('aria-describedby') != 'message_result') {
                     _of = _this;
                     _at = 'right top-' + (10 + _this.outerHeight());
                  }
               });
               $('#message_result').dialog({
                  dialogClass: 'message_after_redirect ' + result['class'],
                  minHeight: 40,
                  minWidth: 200,
                  position: {
                     my: 'left bottom',
                     at: _at,
                     of: _of,
                     collision: 'none'
                  },
                  autoOpen: false,
                  show: {
                     effect: 'slide',
                     direction: 'down',
                     'duration': 800
                  }
               })
                  .dialog('open');
               $(document.body).on('click', function (e) {
                  if ($('#message_result').dialog('isOpen')
                     && !$(e.target).is('.ui-dialog, a')
                     && !$(e.target).closest('.ui-dialog').length) {
                     $('#message_result').dialog('close');
                     // redo focus on initial element
                     e.target.focus();
                  }
               });
            });
         }
      });
   }

   this.init = function(ajax_url) {
      window.actualTime.ajax_url = ajax_url;
      if (!$("#message_result").length) {
         $("body").append("<div id='message_result'></div>");
      }

      // Initialize
      jQuery.ajax({
         type: 'GET',
         url: window.actualTime.ajax_url + '?footer',
         dataType: 'json',
         success: function (result) {
            symb_d = result['symb_d'];
            symb_day = result['symb_day'];
            symb_days = result['symb_days'];
            symb_h = result['symb_h'];
            symb_hour = result['symb_hour'];
            symb_hours = result['symb_hours'];
            symb_min = result['symb_min'];
            symb_minute = result['symb_minute'];
            symb_minutes = result['symb_minutes'];
            symb_s = result['symb_s'];
            symb_second = result['symb_second'];
            symb_seconds = result['symb_seconds'];
            text_warning = result['text_warning'];
            text_pause = result['text_pause'];
            text_restart = result['text_restart'];
            text_done = result['text_done'];
            popup_div = result['popup_div'];

            if (result['ticket_id']) {
               window.actualTime.startCount(result['task_id'], result['time']);
               window.actualTime.showTimerPopup(result['ticket_id']);
            }
         }
      });
   }
}();

$(document).ready(function(){
   var url = CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.actualtime+"/ajax/timer.php";
   window.actualTime.init(url);
});