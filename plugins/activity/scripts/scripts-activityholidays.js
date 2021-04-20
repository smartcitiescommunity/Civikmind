function updateDuration(input,root_doc){
   var hiddenDuration = document.getElementById('actiontime');

   var beginDate = document.getElementById('begin');
   var endDate = document.getElementById('end');

   var tmp = beginDate.value.split('-');
   var objDateDeb = new Date(tmp[2],tmp[1]-1,tmp[0]);
   tmp = endDate.value.split('-');
   var objDateEnd  = new Date(tmp[2],tmp[1]-1,tmp[0]);

   if (dateDiff(objDateDeb,objDateEnd).day < 0) {
      objDateEnd = objDateDeb;
      endDate.value = beginDate.value;
   }

   getActionTime(beginDate.value,endDate.value,Math.abs(dateDiff(objDateEnd, objDateDeb).day),input,'day',root_doc);
}


function getXHRObject(){
   if (window.XMLHttpRequest) {
      xhr_object = new XMLHttpRequest();
   } else if (window.ActiveXObject) {
      xhr_object = new ActiveXObject("Microsoft.XMLHTTP");
   } else {
      return;
   }

   return xhr_object;
}
function getActionTime(beginDate,endDate,actiontime,input,format,root_doc){
   var xhr_object = null;

   var cbAmBegin  = document.getElementById('cb_begindate_am');
   var cbPmBegin  = document.getElementById('cb_begindate_pm');
   var cbAllDayBegin  = document.getElementById('cb_begindate_allday');
   var cbAmEnd  = document.getElementById('cb_enddate_am');
   var cbPmEnd  = document.getElementById('cb_enddate_pm');
   var cbAllDayEnd  = document.getElementById('cb_enddate_allday');

   document.getElementById('div_duration').innerHTML = '';
   var img = document.createElement('img');
   img.setAttribute('src',root_doc + '/plugins/activity/pics/loading.gif');
   document.getElementById('div_duration').appendChild(img);


   if (beginDate == endDate) {
      if (cbAllDayBegin.checked==true) {
         finalDuration = 1;
      } else {
         finalDuration = 0.5;
      }

      document.getElementById('actiontime').value = finalDuration;
      document.getElementById('div_duration').innerHTML = finalDuration;
      updateRadioBtnDate(input,finalDuration);
   } else {
      args="begin="+beginDate+"&end="+endDate+"&actiontime="+actiontime;

      xhr_object = getXHRObject();
      xhr_object.open("POST",root_doc + '/plugins/activity/ajax/duration.php', true);
      xhr_object.onreadystatechange = function() {
         if (xhr_object.readyState == 4) {
            var jsondata = JSON.parse(xhr_object.responseText);
            var finalDuration =  jsondata['actiontime']-1;
            console.log(finalDuration)

            if (cbAmBegin.checked == true ||cbPmBegin.checked) {
               finalDuration+=0.5;
            } else if (cbAllDayBegin.checked == true ) {
               finalDuration+=1;
            }
            if (cbAmEnd.checked == true ||cbPmEnd.checked) {
               finalDuration-=0.5;
            }

            updateRadioBtnDate(input,finalDuration);

            if (!isNaN(finalDuration)) {
               document.getElementById('div_duration').innerHTML = finalDuration;
               document.getElementById('actiontime').value = finalDuration;
            }
         }
         return xhr_object.readyState;
      };
      xhr_object.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr_object.send(args);
   }
}

function updateRadioBtnDate(input,tmpDuration){
   var beginDate = document.getElementById('begin');
   var endDate = document.getElementById('end');

   var cbAmBegin     = document.getElementById('cb_begindate_am');
   var cbPmBegin     = document.getElementById('cb_begindate_pm');
   var cbAllDayBegin = document.getElementById('cb_begindate_allday');

   var cbAmEnd = document.getElementById('cb_enddate_am');
   var cbPmEnd = document.getElementById('cb_enddate_pm');
   var cbAllDayEnd = document.getElementById('cb_enddate_allday');

   if (tmpDuration == 0) {
      cbAmEnd.disabled = true;
      cbPmEnd.disabled = true;
      cbAllDayEnd.disabled = true;
   } else {
      // same date
      if (beginDate.value == endDate.value) {

         cbAmBegin.disabled = false;
         cbPmBegin.disabled = false;
         cbAllDayBegin.disabled = false;

         cbAmEnd.disabled = true;
         cbPmEnd.disabled = true;
         cbAllDayEnd.disabled = true;

         cbAmEnd.checked = false;
         cbPmEnd.checked = false;
         cbAllDayEnd.checked = false;

      } else {
         cbAmBegin.disabled = true;
         cbPmBegin.disabled = false;
         cbAllDayBegin.disabled = false;

         cbAmEnd.disabled = false;
         cbPmEnd.disabled = true;
         cbAllDayEnd.disabled = false;

         if (cbAmBegin.checked == true) {
            cbPmBegin.checked = true;
         }
         if (cbPmEnd.checked == true) {
            cbAmEnd.checked = true;
         } else if (cbPmEnd.checked == false && cbAmEnd.checked == false && cbAllDayEnd.checked == false) {
            cbAllDayEnd.checked = true;
         }

      }
   }
}


function dateDiff(date1, date2){
   var diff = {};                           // Initialisation du retour
   var tmp = date2 - date1;

   tmp = Math.floor(tmp/1000);             // Nombre de secondes entre les 2 dates
   diff.sec = tmp % 60;                    // Extraction du nombre de secondes

   tmp = Math.floor((tmp-diff.sec)/60);    // Nombre de minutes (partie entière)
   diff.min = tmp % 60;                    // Extraction du nombre de minutes

   tmp = Math.floor((tmp-diff.min)/60);    // Nombre d'heures (entières)
   diff.hour = tmp % 24;                   // Extraction du nombre d'heures

   tmp = Math.floor((tmp-diff.hour)/24);   // Nombre de jours restants
   diff.day = tmp;

   return diff;
}


/**
 *
 * @param root_doc
 * @param id
 */
function plugin_activity_show_details(root_doc, holidayperiod_id) {
   users_id = $('input[name="users_id"]').val();
   $('#tr_plugin_activity_details').closest('tr').remove();
   $.ajax({
      url: root_doc + '/plugins/activity/ajax/activityholidays.php',
      type: 'POST',
      data: '&load_holiday_details&users_id=' + users_id + '&holiday_period_id=' + holidayperiod_id,
      dataType: 'html',
      success: function (code_html, statut) {
         $('#tr_plugin_activity_holidayperiods_id').closest('tr').after(code_html);
      },
   });
}

/**
 *
 * @param root_doc
 * @param id
 */
function plugin_activity_show_details_users(root_doc, users_id) {
   holidayperiod_id = $('input[name="plugin_activity_holidayperiods_id"]').val();
   $('#tr_plugin_activity_details').closest('tr').remove();
   $.ajax({
      url: root_doc + '/plugins/activity/ajax/activityholidays.php',
      type: 'POST',
      data: '&load_holiday_details&users_id=' + users_id + '&holiday_period_id=' + holidayperiod_id,
      dataType: 'html',
      success: function (code_html, statut) {
         $('#tr_plugin_activity_holidayperiods_id').closest('tr').after(code_html);
      },
   });
}

/**
 *
 * @param root_doc
 * @param id
 */
function plugin_activity_show_periods(root_doc, plugin_activity_holidaytypes_id) {
   $.ajax({
      url: root_doc + '/plugins/activity/ajax/activitytypes.php',
      type: 'POST',
      data: '&load_holiday_period&holiday_type_id=' + plugin_activity_holidaytypes_id,
      dataType: 'html',
      success: function (is_period, statut) {
         if (is_period) {
            $('#tr_plugin_activity_holidayperiods_id').show();
         } else {
            $('#tr_plugin_activity_holidayperiods_id').hide();
            $('#tr_plugin_activity_details').closest('tr').remove();
         }
      },
   });
}