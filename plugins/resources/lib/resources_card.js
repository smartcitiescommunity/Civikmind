$(window).load(function() {

   var navigation = $('nav').find('ul'),
       navigationWidth = navigation.width();

   navigation.css({ width: navigationWidth, float: 'none', margin: '0 auto', visibility: 'visible' });

});

$(document).ready(function() {

   var containerHeight = $('#container').height(),
       header = $('header');
   
   $(window).resize(function() {
   
      var windowHeight = $(this).height(),
          calculate = (274 + (windowHeight - containerHeight) + 12) / 2;
      
      if(calculate > 274) { calculate = 274 } else if(calculate < 42) { calculate = 42 }
      
      header.css({ height: calculate });
   
   }).trigger('resize');
   

   var contentAnimating = false,
       initialLoad = true;

   $('nav').delegate('a', 'click', function() {
   
      if(!contentAnimating) {
      
         contentAnimating = true;
         
         $(this).parent('li').siblings().removeClass('active').end().addClass('active');
         
         $.address.value($(this).attr('href'));
         
         if(initialLoad) {
            $('#plugin_resources_card-content-wrap').css({ marginLeft: ($(this).parent('li').index() * 590) * -1 });
            contentAnimating = false;
            initialLoad = false;
         } else {
            $('#plugin_resources_card-content-wrap').animate({ marginLeft: ($(this).parent('li').index() * 590) * -1 }, 500, 'easeOutExpo', function() { contentAnimating = false; });
         }
      
      }
   
      return false;
   
   });
   
   $('.scrollable').jScrollPane();

});