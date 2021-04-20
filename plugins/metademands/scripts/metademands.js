/**
 * metademandWizard
 *
 * @param  options
 */
(function ($) {
   $.fn.metademandWizard = function (options) {

      var object = this;
      init();

      /**
       * Start the plugin
       */
      function init() {
         object.params = new Array();
         object.params['lang'] = '';
         object.params['root_doc'] = '';

         if (options != undefined) {
            $.each(options, function (index, val) {
               if (val != undefined && val != null) {
                  object.params[index] = val;
               }
            });
         }
      }

      /**
       * metademands_show_field_onchange : show or hide fields
       *
       * @param params $params - id : id of object to observe
       *                       - value : value to compare
       *                       - valueDisplay : value field to dislay
       *                       - titleDisplay : title field to dislay
       */
      this.metademands_show_field_onchange = function (params) {
         var item = document.getElementById(params.id);
         item.onchange = function () {
            object.metademands_show_field(params);

            // If datetime interval, show label2
            if (item.value == 'datetime_interval' || item.value == 'date_interval') {
               document.getElementById('show_label2').style.display = "inline";
            } else {
               document.getElementById('show_label2').style.display = "none";
            }
         };
      };

      /**
       * metademands_show_field : show or hide fields
       *
       * @param params $params - id : id of object to observe
       *                       - value : value to compare
       *                       - valueDisplay : value field to dislay
       *                       - titleDisplay : title field to dislay
       */
      this.metademands_show_field = function (params) {
         var item = document.getElementById(params.id);
         if (item.value == params.value || item.value == params.value2  || item.value == params.value3 || item.value == params.value4) {
            document.getElementById(params.valueDisplay).style.display = "inline";
            if (params.titleDisplay != undefined) {
               document.getElementById(params.titleDisplay).style.display = "inline";
            }

            document.getElementById(params.valueDisplay_title).style.display = "none";
            if (params.titleDisplay != undefined) {
               document.getElementById(params.titleDisplay_title).style.display = "none";
            }
         } else if (item.value == params.value_title || item.value == params.value_informations) {
            document.getElementById(params.valueDisplay).style.display = "none";
            if (params.titleDisplay != undefined) {
               document.getElementById(params.titleDisplay).style.display = "none";
            }

            document.getElementById(params.valueDisplay_title).style.display = "inline";
            if (params.titleDisplay != undefined) {
               document.getElementById(params.titleDisplay_title).style.display = "inline";
            }
         } else {
            document.getElementById(params.valueDisplay).style.display = "none";
            if (params.titleDisplay != undefined) {
               document.getElementById(params.titleDisplay).style.display = "none";
            }

            document.getElementById(params.valueDisplay_title).style.display = "none";
            if (params.titleDisplay != undefined) {
               document.getElementById(params.titleDisplay_title).style.display = "none";
            }
         }

         // If datetime interval, show label2
         if (item.value == 'datetime_interval' || item.value == 'date_interval') {
            document.getElementById('show_label2').style.display = "inline";
         } else {
            document.getElementById('show_label2').style.display = "none";
         }

      };

      /**
       * changeNbValue : display text input
       *
       * @param newValue
       */
      this.changeNbValue = function (newValue) {
         document.getElementById('nbValue').value = newValue;
         return true;
      };

      /**
       * metademands_add_custom_values : add text input
       */
      this.metademands_add_custom_values = function (field_id) {
         var count = $('#count_custom_values').val();
         $('#count_custom_values').val(parseInt(count) + 1);

         var display_comment = $('#display_comment').val();
         var display_default = $('#display_default').val();
         $.ajax({
            url: object.params['root_doc'] + '/plugins/metademands/ajax/addnewvalue.php',
            type: "POST",
            dataType: "html",
            data: {
               'action': 'add',
               'display_comment': display_comment,
               'display_default': display_default,
               'count': $('#count_custom_values').val()
            },
            success: function (response, opts) {
               var item_bloc = $('#' + field_id);
               item_bloc.append(response);

               var scripts, scriptsFinder = /<script[^>]*>([\s\S]+?)<\/script>/gi;
               while (scripts = scriptsFinder.exec(response)) {
                  eval(scripts[1]);
               }
            }
         });
      };

      /**
       * metademands_delete_custom_values : delete text input
       *
       * @param field_id
       */
      this.metademands_delete_custom_values = function (field_id) {
         var count = $('#count_custom_values').val();
         $('#custom_values' + count).remove();
         $('#comment_values' + count).remove();
         $('#default_values' + count).remove();
         $('#' + field_id + count).remove();
         $('#count_custom_values').val(parseInt(count) - 1);


      };

      /**
       * setMandatoryField : change mandatory mark
       *
       * @param  toupdate  : element id to update
       * @param  toobserve : element id to observe
       * @param  check_value : value to check
       */
      this.metademand_setMandatoryField = function (toupdate, toobserve, check_value, type) {

         object.metademand_checkEmptyField(toupdate, toobserve, check_value, type);

         if (type == 'checkbox') {
            $("input[check='" + toobserve + "']").change(function () {
               object.metademand_checkEmptyField(toupdate, toobserve, check_value, type);
            });
         } else {
            $("[name='" + toobserve + "']").change(function () {
               object.metademand_checkEmptyField(toupdate, toobserve, check_value, type);
            });
         }
      };

      /**
       * metademand_checkEmptyField : check if field must be mandatory
       *
       * @param  toupdate    : element id to update
       * @param  toobserve   : element id to observe
       * @param  check_value : value to check
       */
      this.metademand_checkEmptyField = function (toupdate, toobserve, check_value, type) {

         if (type == 'checkbox') {
            obs = $("input[check='" + toobserve + "']:checked");
         } else if (type == 'radio') {
            obs = $("[name='" + toobserve + "']:checked");
         } else {
            obs = $("[name='" + toobserve + "']");
         }
         // const zerodiff = (currentValue) => currentValue == 0;

         var op1 = (!Array.isArray(check_value) &&
             check_value != 0 &&
             obs.val() == check_value);
         var op2 = (Array.isArray(check_value) &&
             obs.val() != 0 &&
             check_value.includes(parseInt(obs.val(),10)));

         if (  op1|| op2
            //  ||
            // check_value == 'NOT_NULL' &&
            // $("[name='" + toobserve + "']").val() != 0
         ) {
            $('#' + toupdate).html('*');
         } else {
            $('#' + toupdate).html('');
         }
      };

      // this.metademand_displayField = function (toupdate, toobserve, check_value) {
      //     $('#' + toupdate).hide();
      //
      //     this.metademand_checkField(toupdate, toobserve, check_value);
      //     $("[name='" + toobserve + "']").change(function () {
      //         this.metademand_checkField(toupdate, toobserve, check_value);
      //     });
      // };
      //
      // this.metademand_checkField = function (toupdate, toobserve, check_value) {
      //     if (check_value != 0 && ($("[name='" + toobserve + "']").val() == check_value
      //         || (check_value == 'NOT_NULL' && $("[name='" + toobserve + "']").val() != 0))) {
      //         $('#' + toupdate).show();
      //     } else {
      //         $('#' + toupdate).hide();
      //     }
      // };

      return this;
   };
}(jQuery));
