toggleSelect = function(level) {

   //toggle select
   var current_select = $("#select_level_"+level+" select");
   if (current_select[0].disabled == false) {
      current_select.select2("enable", false);
   } else {
      current_select.select2("enable", true);
   }
}