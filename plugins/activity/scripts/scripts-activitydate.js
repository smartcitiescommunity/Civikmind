// *********************** Activity.class.php ***********************//
// *********** Managing the calendar with the month and year ****************//
function changeClickTodayActivity(params) {
   $(document).ready(function () {
      // Only in ticket.php
      if (location.pathname.indexOf('activity.form.php') > 0) {

         var todayIdElmt = $('span[class*="fc-button-today"]');
         todayIdElmt.click(function () {
            var date = new Date();
            goToMonthActivity(undefined, date.getFullYear(), params.lang_month);
         });
      }
   });
}

function goToMonthActivity(month, year, monthNames) {
   var date = new Date();
   var d = date.getDate();
   var m = month !== undefined ? parseInt(month, 0) : date.getMonth();
   var y = year !== undefined ? parseInt(year, 0) : date.getFullYear();

   var yearIdElm = $('[name=\"year\"]');
   yearIdElm.html(y);
   $('#last_year_activity').html('');
   lastYearActivityButton('#last_year_activity', y);
   $('#months-list-activity').html('');
   monthsListActivity('#months-list-activity', y, monthNames);
   $('#next_year_activity').html('');
   nextYearActivityButton('#next_year_activity', y);

   return new Date(y, m, d);
}
function lastYearActivityButton(element, year) {
   var monthClass = 'fc-button fc-button-agendaWeek fc-state-default';
   $('<a class=\"' + monthClass + ' activity_href\"  href=\"#' + (year - 1) + '\">' + (year - 1) + '</a>').click(function () {
      if (!$(this).hasClass('fc-state-disabled')) {
         $(this).removeClass('fc-state-hover');
      }
   })
      .mousedown(function () {
         $(this)
            .not('.fc-state-active')
            .not('.fc-state-disabled')
            .addClass('fc-state-down');
      })
      .mouseup(function () {
         $(this).removeClass('fc-state-down');
      })
      .hover(
         function () {
            $(this)
               .not('.fc-state-active')
               .not('.fc-state-disabled')
               .addClass('fc-state-hover');
         },
         function () {
            $(this)
               .removeClass('fc-state-hover')
               .removeClass('fc-state-down');
         }
      ).appendTo(element);
}

function nextYearActivityButton(element, year) {
   var monthClass = 'fc-button fc-button-agendaWeek fc-state-default';
   $('<a class=\"' + monthClass + ' activity_href\"  href=\"#' + (year + 1) + '\">' + (year + 1) + '</a>').click(function () {
      if (!$(this).hasClass('fc-state-disabled')) {
         $(this).removeClass('fc-state-hover');
      }
   })
      .mousedown(function () {
         $(this)
            .not('.fc-state-active')
            .not('.fc-state-disabled')
            .addClass('fc-state-down');
      })
      .mouseup(function () {
         $(this).removeClass('fc-state-down');
      })
      .hover(
         function () {
            $(this)
               .not('.fc-state-active')
               .not('.fc-state-disabled')
               .addClass('fc-state-hover');
         },
         function () {
            $(this)
               .removeClass('fc-state-hover')
               .removeClass('fc-state-down');
         }
      ).appendTo(element);
}


function monthsListActivity(element, year, monthNames) {
   for (var m = 0; m < monthNames.length; m++) {
      var monthClass = 'fc-button fc-button-agendaWeek fc-state-default';
      switch (m) {
         case 0:
            monthClass = 'fc-button fc-button-month fc-state-default fc-corner-left';
            break;

         case monthNames.length - 1:
            monthClass = 'fc-button fc-button-month fc-state-default fc-corner-right';
            break;
      }

      $('<a class=\"' + monthClass + ' activity_href\" href=\"#' + m + '_' + year + '\">' + monthNames[m] + '</a>').click(function () {
         if (!$(this).hasClass('fc-state-disabled')) {
            $(this).removeClass('fc-state-hover');
         }
      })
         .mousedown(function () {
            $(this)
               .not('.fc-state-active')
               .not('.fc-state-disabled')
               .addClass('fc-state-down');
         })
         .mouseup(function () {
            $(this).removeClass('fc-state-down');
         })
         .hover(
            function () {
               $(this)
                  .not('.fc-state-active')
                  .not('.fc-state-disabled')
                  .addClass('fc-state-hover');
            },
            function () {
               $(this)
                  .removeClass('fc-state-hover')
                  .removeClass('fc-state-down');
            }
         ).appendTo(element);
   }
}
// *********************** report.class.php ***********************//
// *********** button previous month and next month ****************//
function monthActivityButton(element, title) {
   var monthClass = 'fc-button fc-button-agendaWeek fc-state-default';
   $('<a class=\"' + monthClass + ' activity_href\"  >' + title + '</a>').click(function () {
      if (!$(this).hasClass('fc-state-disabled')) {
         $(this).removeClass('fc-state-hover');
      }
   })
      .mousedown(function () {
         $(this)
            .not('.fc-state-active')
            .not('.fc-state-disabled')
            .addClass('fc-state-down');
      })
      .mouseup(function () {
         $(this).removeClass('fc-state-down');
      })
      .hover(
         function () {
            $(this)
               .not('.fc-state-active')
               .not('.fc-state-disabled')
               .addClass('fc-state-hover');
         },
         function () {
            $(this)
               .removeClass('fc-state-hover')
               .removeClass('fc-state-down');
         }
      ).appendTo(element);
}

$(document).on('click', '#next_month_report a', function () {
   var monthIdElmnt    = $('select[name="month"]');
   var yearIdElmnt     = $('select[name="year"]');
   var month           = monthIdElmnt.val();
   var year            = yearIdElmnt.val();

   if (monthIdElmnt.val() == 12) {
      yearIdElmnt.val(parseInt(year) + 1);
      monthIdElmnt.val(1);
   } else {
      monthIdElmnt.val(parseInt(month) + 1);
   }

   $('#send_cra').click();
});

$(document).on('click', '#last_month_report a', function () {
   var monthIdElmnt    = $('select[name="month"]');
   var yearIdElmnt     = $('select[name="year"]');
   var month           = monthIdElmnt.val();
   var year            = yearIdElmnt.val();

   if (monthIdElmnt.val() == 1) {
      yearIdElmnt.val(parseInt(year) - 1);
      monthIdElmnt.val(12);
   } else {
      monthIdElmnt.val(parseInt(month) - 1);
   }
   $('#send_cra').click();
});