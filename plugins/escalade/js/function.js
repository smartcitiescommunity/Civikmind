var getUrlParameter = function(val) {
   var result = undefined,
       tmp = [];

   location.search
      .substr(1) // remove '?'
      .split("&")
      .forEach(function (item) {
         tmp = item.split("=");
         if (tmp[0] === val) {
            result = decodeURIComponent(tmp[1]);
         }
      });
   return result;
};


var checkDOMChange = function (selector, handler) {
   if ($(selector).get(0)) {
      return handler();
   }
   setTimeout( function() {
      checkDOMChange(selector, handler);
   }, 100 );
};