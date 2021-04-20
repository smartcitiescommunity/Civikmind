function badges_initJs(root_doc) {
    this.usedBadges = new Array();
    this.root_doc = root_doc;
}

/**
 * badges_add_custom_values : add text input
 *
 * @param action
 * @param toobserve
 * @param toupdate
 */
this.badges_addToCart = function (action, toobserve, toupdate) {

    var object = this;

    var formInput = getFormData(toobserve);

    $.ajax({
         url: object.root_doc + '/plugins/badges/ajax/request.php',
         type: "POST",
         dataType: "json",
         data: 'action=' + action + '&' + formInput,
         success: function (data) {
            if (data.success) {
                var item_bloc = $('#' + toupdate);
                var result = "<tr id='badges_cartRow" + data.rowId + "'>\n";

                // Insert row in cart
                $.each(data.fields, function (index, row) {
                    result += "<td>" + row.label.replace(/\\["|']/g, '"') + "<input type='hidden' id='" + index + "' name='badges_cart[" + data.rowId + "][" + index + "]' value='" + row.value + "'></td>\n";

                    // Push used badges
                  if (index == 'badges_id' && row.value != 0) {
                      object.usedBadges.push(row.value);
                  }
                });
                result += "<td>" +
                    "<a href='#' onclick=\"badges_removeCart('badges_cartRow" + data.rowId + "')\"><i class='fa fa-times-circle fa-2x' style='color:darkred'></i></a>" +
                    "</td></tr>";
                item_bloc.append(result);
                item_bloc.css({"display": 'table'});

                // Reload badge list
                badges_reloadAvailableBadges();

            } else {
               $("#dialog-confirm").html(data.message);
               $("#dialog-confirm").dialog({
                  resizable: false,
                  height: 180,
                  width: 350,
                  modal: true,
                  buttons: {
                     OK: function () {
                         $(this).dialog("close");
                     }
                  }
                 });
            }
         }
      });
};

/**
 * Add badges
 *
 * @param action
 * @param toobserve
 */
this.badges_addBadges = function (action, toobserve) {

    var object = this;

    var formInput = getFormData(toobserve);

    $.ajax({
         type: "POST",
         dataType: "json",
         url: object.root_doc + '/plugins/badges/ajax/request.php',
         data: 'action=' + action + '&' + formInput,
         success: function (data) {
            $("#dialog-confirm").html(data.message);
            $("#dialog-confirm").dialog({
               resizable: false,
               height: 180,
               width: 350,
               modal: true,
               buttons: {
                  OK: function () {
                      $(this).dialog("close");
                      window.location.reload();
                  }
               }
             });
         }
      });
};

/**
 * Return badges
 *
 * @param action
 * @param toobserve
 */
this.badges_returnBadges = function (action, toobserve) {

    var object = this;

    var formInput = getFormData(toobserve);

    $.ajax({
         type: "POST",
         dataType: "json",
         url: object.root_doc + '/plugins/badges/ajax/request.php',
         data: 'action=' + action + '&' + formInput,
         success: function (data) {
            $("#dialog-confirm").html(data.message);
            $("#dialog-confirm").dialog({
               resizable: false,
               height: 180,
               width: 350,
               modal: true,
               buttons: {
                  OK: function () {
                      $(this).dialog("close");
                      window.location.reload();
                  }
               }
             });
         }
      });
};

/**
 * Search badges
 *
 * @param action
 * @param toobserve
 * @param toupdate
 */
this.badges_searchBadges = function (action, toobserve, toupdate) {
    var formInput = getFormData(toobserve);

    $.ajax({
         type: "POST",
         dataType: "json",
         url: object.root_doc + '/plugins/badges/ajax/request.php',
         data: 'action=' + action + '&' + formInput,
         success: function (data) {
            var result = data.message;
            var item_bloc = $('#' + toupdate);
            item_bloc.html(result);

            var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
            while (scripts = scriptsFinder.exec(result)) {
                eval(scripts[1]);
            }
         }
      });
};

/**
 * Reload available badges
 *
 */
this.badges_reloadAvailableBadges = function () {

    var object = this;

    $.ajax({
         type: "POST",
         url: object.root_doc + '/plugins/badges/ajax/request.php',
         data: {
            'action': 'reloadAvailableBadges',
            'used': object.usedBadges
         },
         success: function (result) {
            var item_bloc = $('#badges_available');
            item_bloc.html(result);

            var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
            while (scripts = scriptsFinder.exec(result)) {
                eval(scripts[1]);
            }
         }
      });
};

/**
 * badges_removeCart : delete text input
 *
 * @param field_id
 */
this.badges_removeCart = function (field_id) {

    var object = this;

    var value = $("tr[id=" + field_id + "] input[id=badges_id]").val();

    // Remove element from used badges variable
   for (var i = 0; i < object.usedBadges.length; i++) {
      if (object.usedBadges[i] === value) {
          object.usedBadges.splice(i, 1);
      }
   }
    // Reload badge list
    badges_reloadAvailableBadges();

    var item_bloc = $('#' + field_id);

    // Cart not visible if no data
   if (object.usedBadges.length === 0) {
       item_bloc.parent('table').css({'display': 'none'});
   }

    // Remove cart row
    $('#' + field_id).remove();
};

/**
 * Cancel wizard
 *
 * @param url
 */
this.badges_cancel = function (url) {
    window.location.href = url;
};

/**
 *  Get the form values and construct data url
 *
 * @param object form
 */
this.getFormData = function (form) {
   if (typeof (form) !== 'object') {
       var form = $('#' + form);
   }

    return encodeParameters(form[0]);
};

/**
 * Encode form parameters for URL
 *
 * @param elements
 */
this.encodeParameters = function (elements) {
    var kvpairs = [];

    $.each(elements, function (index, e) {
      if (e.name != '') {
         switch (e.type) {
            case 'radio':
            case 'checkbox':
               if (e.checked) {
                   kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
               }
                  break;
            case 'select-multiple':
               var name = e.name.replace("[", "").replace("]", "");
               $.each(e.selectedOptions, function (index, option) {
                   kvpairs.push(encodeURIComponent(name + '[' + option.index + ']') + '=' + encodeURIComponent(option.value));
               });
                  break;
            default:
               kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
                  break;
         }
      }
    });

    return kvpairs.join("&");
};
