$(function() {
   // do like a jquery toggle but based on a parameter
   $.fn.toggleFromValue = function(val) {
      var that = this;
      if (val === 1
          || val === "1"
          || val === true) {
         that.show();
         $(that).find('[_required]').prop('required', true);
      } else {
         that.hide();
         $(that).find('[required]').prop('required', false).attr('_required', 'true');
      }
   }

   // remove required from hidden fields
   $(document).on('click','.xivo_config form input[type=submit]',function() {
      xivoCheckConfig();
   });
});

var xivoCheckConfig = function() {
   $(".xivo_config .xivo_config_block").each(function() {
      var that = $(this);
      if (that.css("display") == "none") {
         $(that).find('[required]').prop('required', false);
      }
   });
};
