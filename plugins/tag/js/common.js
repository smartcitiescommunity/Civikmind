var rgb2hex = function(rgb){
   rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
   return (rgb && rgb.length === 4) ? "#" +
      ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
      ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
      ("0" + parseInt(rgb[3],10).toString(16)).slice(-2) : '';
};

var idealTextColor = function(hexTripletColor) {
   var nThreshold = 105;
   if (hexTripletColor.indexOf('rgb') != -1) {
      hexTripletColor = rgb2hex(hexTripletColor);
   }
   hexTripletColor = hexTripletColor.replace('#','');
   var components = {
      R: parseInt(hexTripletColor.substring(0, 2), 16),
      G: parseInt(hexTripletColor.substring(2, 4), 16),
      B: parseInt(hexTripletColor.substring(4, 6), 16)
   };
   var bgDelta = (components.R * 0.299) + (components.G * 0.587) + (components.B * 0.114);
   return ((255 - bgDelta) < nThreshold) ? "#000000" : "#E6E6E6";
}

var formatOptionSelection = function(option, container) {
   if (typeof option.color != 'undefined'
       && option.color.length > 0) {
      var invertedcolor = idealTextColor(option.color);
      $(container)
         .css("background-color", option.color)
         .css("border-color", invertedcolor)
         .css("color", invertedcolor)
         .children('.select2-selection__choice__remove')
            .css("color", invertedcolor);
   }
   return option.text;
};

var formatOptionResult = function(option, container) {
   var template = "<span class='tag_choice' style='";
   if (typeof option.color != 'undefined'
       && option.color !== "") {
      var invertedcolor = idealTextColor(option.color);
      template+= " background-color: " + option.color + "; ";
      template+= " color: " + invertedcolor + "; ";
   } else {
      template+= " background-color: #DDDDDD; ";
   }
   template+= "'>" + option.text + "</span>";

   return $(template);
};
