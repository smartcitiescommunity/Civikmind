<?php

include('../../../inc/includes.php');

Html::header_nocache();
Session::checkLoginUser();
header("Content-Type: text/html; charset=UTF-8");

if (isset($_POST['question'])) {
   if($_POST['question']==1){
      echo Html::scriptBlock("
      var allInput = document.getElementsByName(\"_status\");
      
      var pending = ".CommonITILObject::WAITING.";
      allInput.forEach(function(element){
      
      var event = new Event('change');
      if(element.value == pending){
         element.checked = true;
         element.dispatchEvent(event);
         
         var idd = element.id;
         var chosen_li = $('#'+idd).parent();
         
         var xBtnDrop = chosen_li.parent().siblings(\".x-button-drop\");
         //clean old status class
         xBtnDrop.attr('class','x-button x-button-drop');

         //find status
         var cstatus = chosen_li.data('status');

         //add status to dropdown button
         xBtnDrop.addClass(cstatus);
         
 
      }
      
      
      
      });
      
      ");
   }else{
      if($_POST['status'] == ""){
         echo Html::scriptBlock("
            var allInput = document.getElementsByName(\"_status\");
            
            var newStatus = ".CommonITILObject::INCOMING.";
            allInput.forEach(function(element){
           
            var event = new Event('change');
            if(element.value == newStatus){
               element.checked = true;
               element.dispatchEvent(event);
                var idd = element.id;
                  var chosen_li = $('#'+idd).parent();
                  
                  var xBtnDrop = chosen_li.parent().siblings(\".x-button-drop\");
                  //clean old status class
                  xBtnDrop.attr('class','x-button x-button-drop');
         
                  //find status
                  var cstatus = chosen_li.data('status');
         
                  //add status to dropdown button
                  xBtnDrop.addClass(cstatus);
            }
            
            });
      
      ");
      }else{
         echo Html::scriptBlock("
            var allInput = document.getElementsByName(\"_status\");
            
          
            allInput.forEach(function(element){
           
            var event = new Event('change');
            if(element.value == ".$_POST['status']."){
               element.checked = true;
               element.dispatchEvent(event);
               
                var idd = element.id;
               var chosen_li = $('#'+idd).parent();
               
               var xBtnDrop = chosen_li.parent().siblings(\".x-button-drop\");
               //clean old status class
               xBtnDrop.attr('class','x-button x-button-drop');
      
               //find status
               var cstatus = chosen_li.data('status');
      
               //add status to dropdown button
               xBtnDrop.addClass(cstatus);
            }     
            });
      
      ");
      }
   }


}
