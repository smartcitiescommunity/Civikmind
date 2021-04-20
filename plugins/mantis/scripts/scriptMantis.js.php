<?php
/**
 * --------------------------------------------------------------------------
 * LICENSE
 *
 * This file is part of mantis.
 *
 * mantis is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * mantis is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * --------------------------------------------------------------------------
 * @author    François Legastelois
 * @copyright Copyright (C) 2015-2018 by Teclib' and contributors.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/mantis
 * @link      https://pluginsglpi.github.io/mantis/
 * -------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

//change mimetype
header("Content-type: application/javascript");

$root_ajax = Plugin::getWebDir('mantis') . "/ajax/ajax.php";

$JS = <<<JAVASCRIPT

function getAttachment(){

    var div_info = $("#attachmentforLinkToProject");
    var checkBox = $("#followAttachment").is(':checked');
    var idTicket = $("#idTicket").val();
    var itemType = $("#itemType").val();

    if(checkBox == true){

        $.ajax({ // function that allows the ajax
            type: "POST", // method to transmit data to php file
            url: "{$root_ajax}", // url of php file
            data: "action=getTicketAttachment&" +
            "itemType=" + itemType + "&" +
            "idTicket=" + idTicket , // data to be transmitted

            success: function (msg) { // if the call has worked fine
                div_info.html(msg);
            },
            error: function () {
                div_info.html('Ajax Problem !');
            }
        });

        return false; // allows to stay in the same page when submitting the form

    }else{
        div_info.empty();
    }

}

function getAttachment1(){

    var div_info = $("#attachmentforLinkToProject1");
    var checkBox = $("#followAttachment1").is(':checked');
    var idTicket = $("#idTicket1").val();
    var itemType = $("#itemType1").val();

    if(checkBox == true){

        $.ajax({ // function that allows the ajax
            type: "POST", // method to transmit data to php file
            url: "{$root_ajax}", // url of php file
            data: "action=getTicketAttachment&" +
            "itemType=" + itemType + "&" +
            "idTicket=" + idTicket , // data to be transmitted

            success: function (msg) { // if the call has worked fine
                div_info.html(msg);
            },
            error: function () {
                div_info.html('Ajax Problem !');
            }
        });

        return false; // allows to stay in the same page when submitting the form

    }else{
        div_info.empty();
    }

}

/**
 * function to test connection with Mantis Web Service
 * @returns {boolean}
 */
function testConnexionMantisWS() {

    var dropdown = $("#dropdown_etatMantis");
    var div = $("#infoAjax");

    $.ajax({ // function that allows the ajax
        type: "POST", // method to transmit data to php file
        url: "{$root_ajax}", // url of php file
        data: "action=testConnexionMantisWS&" +
            "host=" + $("#host").val() + "&" +
            "url=" + $("#url").val() + "&" +
            "login=" + $("#login").val() + "&" +
            "pwd=" + $("#pwd").val(), // data to be transmitted

            success: function (msg) { // if the call has worked fine

                div.html(msg);

                if (msg.indexOf('check') != -1) {
                    addStateToSelect();
                }else{
                    removeOptionOfSelect(dropdown);
                }

            },
            error: function () {
                div.html('Ajax Problem !');
            }

    });
   return false; // allows to stay in the same page when submitting the form

}

function ifExistissueWithId() {

   var div = $("#infoFindIssueMantis");
   var id = $("#idMantis").val();

   $.ajax({ // function that allows the ajax
      type: "POST", // method to transmit data to php file
      url: "{$root_ajax}", // url of php file
      data: "action=findIssueById&" +
         "id=" + id, // data to be transmitted

      success: function (msg) { // if the call has worked fine
         div.html(msg);
      },
      error: function () {
         div.html("Ajax problem !");
      }

   });
   return false; // allows to stay in the same page when submitting the form

}


function findProjectById(){

    var idMantisIssue = $("#idMantis1").val();
    var idTicket = $("#idTicket1").val();
    var dropdownCustomField = $("#dropdown_fieldsGlpi1");
    var dropdownUrl = $("#dropdown_fieldUrl1");
    var itemType = $("#itemType1").val();

    var div_info = $("#infoLinIssueGlpiToIssueMantis");
    var div_wait = $("#waitForLinkIssueGlpiToIssueMantis");

    if(idMantisIssue.trim() == "" || idMantisIssue == null){

        $("#idMantis").css('border-color','red');
        div_wait.css('display', 'none');

    }else{
        $("#idMantis").css('border-color','#888888');

        $.ajax({ // function that allows the ajax
        type: "POST", // method to transmit data to php file
        url: "{$root_ajax}", // url of php file
        data: "action=getProjectName&" +
            "idTicket=" + idTicket + "&" +
            "itemType=" + itemType + "&" +
            "idMantis=" + idMantisIssue, // data to be transmitted

            success: function (msg) { // if the call has worked fine

                if (msg.indexOf('ERROR :') != -1) {
                    removeOptionOfSelect(dropdownCustomField);
                    removeOptionOfSelect(dropdownUrl);
                    div_wait.css('display', 'none');
                    div_info.html(msg.replace('ERROR :',''));
                } else {
                    div_info.empty();
                    div_wait.css('display', 'none');
                    addCustomFieldtoSelect(dropdownCustomField , msg);
                    addCustomFieldtoSelect(dropdownUrl, msg);
                    div_wait.css('display', 'none');
                }

            },
            error: function () {

                div_wait.css('display', 'none');
                div_info.html("Probleme Ajax");

            }
        });
   }
}



function linkIssueglpiToIssueMantis() {

    var idMantisIssue = $("#idMantis1").val();
    var idTicket = $("#idTicket1").val();
    var idUser = $("#user1").val();
    var date = $("#dateEscalade1").val();

    var glpiField = $("#dropdown_fieldsGlpi1").find(":selected").text();
    var glpiUrl = $("#dropdown_fieldUrl1").find(":selected").text();
    var itemType = $("#itemType1").val();
    var linkedTicket = $("#linkedTicket1").is(':checked');

    var followAttachment = $("#followAttachment1").is(':checked');
    var followFollow = $("#followFollow1").is(':checked');
    var followTask = $("#followTask1").is(':checked');
    var followTitle = $("#followTitle1").is(':checked');
    var followDescription = $("#followDescription1").is(':checked');
    var followCategorie = $("#followCategorie1").is(':checked');

    var div_info = $("#infoLinIssueGlpiToIssueMantis");
    var div_wait = $("#waitForLinkIssueGlpiToIssueMantis");

    div_info.empty();
    div_wait.css('display', 'block');


    if((idMantisIssue.trim() == "" || idMantisIssue == null) || (glpiField.trim() == "" || glpiField == null) || (glpiUrl.trim() == "" || glpiUrl == null)){

        if(idMantisIssue.trim() == "" || idMantisIssue == null){
            $("#idMantis").css('border-color','red');
            div_wait.css('display', 'none');
        }

        if(glpiField.trim() == "" || glpiField == null){
            $("#dropdown_fieldsGlpi").css('border-color','red');
            div_wait.css('display', 'none');
        }

        if(glpiUrl.trim() == "" || glpiUrl == null){
            $("#dropdown_fieldUrl").css('border-color','red');
            div_wait.css('display', 'none');
        }


    }else{

        $("#idMantis").css('border-color','#888888');
        $("#dropdown_fieldUrl").css('border-color','#888888');
        $("#dropdown_fieldsGlpi").css('border-color','#888888');

        $.ajax({ // function that allows the ajax
            type: "POST", // method to transmit data to php file
            url: "{$root_ajax}", // url of php file
            data: "action=LinkIssueGlpiToIssueMantis&" +
                "items_id=" + idTicket + "&" +
                "idMantis=" + idMantisIssue + "&" +
                "followAttachment=" + followAttachment + "&" +
                "followFollow=" + followFollow + "&" +
                "followTask=" + followTask + "&" +
                "glpiField=" + glpiField + "&" +
                "glpiUrl=" + glpiUrl + "&" +
                "linkedTicket=" + linkedTicket + "&" +
                "itemtype=" + itemType + "&" +
                "followTitle=" + followTitle + "&" +
                "followDescription=" + followDescription + "&" +
                "followCategorie=" + followCategorie + "&" +
                "user=" + idUser + "&" +
                "dateEscalade=" + date, // data to be transmitted

                    success: function (msg) { // if the call has worked fine

                    if (msg == true) {

                       div_wait.css('display', 'none');
                       popupLinkGlpiIssuetoMantisIssue.hide();
                       window.location.reload(true);

                    }else {

                       div_wait.css('display', 'none');
                       div_info.html(msg);

                    }},

                    error: function () {
                        div_wait.css('display', 'none');
                        div_info.html("Probleme Ajax");
                    }

        });
   }
}



function closePopup() {
   window.opener.location.reload(true);
   window.close();
}

function addCustomFieldtoSelect(dropdownCustomField,name) {

   var nameProject = name;

   $.ajax({ // function that allows the ajax
      type: "POST", // method to transmit data to php file
      url: "{$root_ajax}", // url of php file
      dataType: "json",
      data: "action=getCustomFieldByProjectname&" +
         "name=" + name, // data to be transmitted
      success: function (msg) { // if the call has worked fine

         if (msg == false) {

         } else {

            var myOptions = msg.toString().split(',');
            var mySelect = dropdownCustomField;

            removeOptionOfSelect(dropdownCustomField);

            for(var i=0;i<msg.length; i++){
               obj = msg[i];
               mySelect.append( $('<option></option>').val(obj.id).html(obj.name) );
            }

         }

      },
      error: function () {
         alert('pb ajax');
      }

   });
   return false; // allows to stay in the same page when submitting the form

}

function findProjectByName() {

   var td = $("#tdSearch");
   var name = $("#nameMantisProject").val();
   var img = $("#resultImg");
   var dropdown = $("#dropdown_categorie");
   var dropdownCustomField = $("#dropdown_fieldsGlpi");
   var dropdownUrl = $("#dropdown_fieldUrl");
   var div_wait = $("#waitForLinkIssueGlpiToProjectMantis");
   var dropdownAssignation = $("#dropdown_assignation");

   div_wait.css('display', 'block');
   img.remove();

   $.ajax({ // function that allows the ajax
      type: "POST", // method to transmit data to php file
      url: "{$root_ajax}", // url of php file
      data: "action=findProjectByName&" +
         "name=" + name, // data to be transmitted
      success: function (msg) { // if the call has worked fine

         td.append(msg);

         if (msg.indexOf('check') != -1) {
            addOptionToSelect(dropdown, name);
            addActortoSelect(dropdownAssignation , name);
            addCustomFieldtoSelect(dropdownCustomField , name);
            addCustomFieldtoSelect(dropdownUrl, name);
            div_wait.css('display', 'none');
         } else {
            removeOptionOfSelect(dropdown);
            removeOptionOfSelect(dropdownAssignation);
            removeOptionOfSelect(dropdownCustomField);
            removeOptionOfSelect(dropdownUrl);
            div_wait.css('display', 'none');
         }

      },
      error: function () {
         div_wait.css('display', 'none');
         td.append("Ajax problem !");
      }

   });
   return false; // allows to stay in the same page when submitting the form

}


function addStateToSelect(){

  var dropdown = $("#dropdown_etatMantis");

      $.ajax({ // function that allows the ajax
      type: "POST", // method to transmit data to php file
      url: "{$root_ajax}", // url of php file
      data: "action=getStateMantis&" +
         "host=" + $("#host").val() + "&" +
         "url=" + $("#url").val() + "&" +
         "login=" + $("#login").val() + "&" +
         "pwd=" + $("#pwd").val(), // data to be transmitted
      success: function (msg) { // if the call has worked fine

         if (msg == false) {

         } else {
            var myOptions = msg.toString().split(',');

            var mySelect = dropdown;

            removeOptionOfSelect(dropdown);

            $.each(myOptions, function (val, text) {
               mySelect.append(
                  $('<option></option>').val(text).html(text)
               );
            });
         }

      },
      error: function () {
         alert('pb ajax');
      }


   });


}

function addActortoSelect(dropdown,name) {

   var nameProject = name;

   $.ajax({ // function that allows the ajax
      type: "POST", // method to transmit data to php file
      url: "{$root_ajax}", // url of php file
      dataType: "json",
      data: "action=getActorByProjectname&" +
         "name=" + name, // data to be transmitted
      success: function (msg) { // if the call has worked fine

         if (msg == false) {

         } else {

            var myOptions = msg.toString().split(',');
            var mySelect = dropdown;

            removeOptionOfSelect(dropdown);

            for(var i=0;i<msg.length; i++){
               obj = msg[i];
               mySelect.append( $('<option></option>').val(obj.id).html(obj.name) );
            }

         }

      },
      error: function () {
         alert('pb ajax');
      }

   });
   return false; // allows to stay in the same page when submitting the form

}




function addOptionToSelect(dropdown, name) {

   var nameProject = name;

   $.ajax({ // function that allows the ajax
      type: "POST", // method to transmit data to php file
      url: "{$root_ajax}", // url of php file
      dataType: "json",
      data: "action=getCategoryFromProjectName&" +
         "name=" + name, // data to be transmitted
      success: function (msg) { // if the call has worked fine

         if (msg == false) {

         } else {
            var myOptions = msg.toString().split(',');
            var mySelect = dropdown;

            removeOptionOfSelect(dropdown);

            $.each(myOptions, function (val, text) {
               mySelect.append(
                  $('<option></option>').val(val).html(text)             );
            });
         }

      },
      error: function () {
         alert('pb ajax');
      }


   });
   return false; // allows to stay in the same page when submitting the form

}

function removeOptionOfSelect(dropdown) {
   dropdown.find('option').remove()
}

function linkIssueglpiToProjectMantis() {

    var nameMantisProject = $("#nameMantisProject").val();
    var cate = $("#dropdown_categorie").find(":selected").text();
    var glpiField = $("#dropdown_fieldsGlpi").find(":selected").text();
    var glpiUrl = $("#dropdown_fieldUrl").find(":selected").text();
    var assign = $("#dropdown_assignation").find(":selected").val();
    var resume = $("#resume").val();
    var description = $("#description").val();
    var stepToReproduce = $("#stepToReproduce").val();
    var followAttachment = $("#followAttachment").is(':checked');

    var followFollow = $("#followFollow").is(':checked');
    var itemType = $("#itemType").val();
    var followTask = $("#followTask").is(':checked');
    var followTitle = $("#followTitle").is(':checked');
    var followDescription = $("#followDescription").is(':checked');
    var followCategorie = $("#followCategorie").is(':checked');
    var linkedTicket = $("#linkedTicket").is(':checked');


    var idTicket = $("#idTicket").val();
    var idUser = $("#user").val();
    var date = $("#dateEscalade").val();

    var div_info = $("#infoLinkIssueGlpiToProjectMantis");
    var div_wait = $("#waitForLinkIssueGlpiToProjectMantis");

    div_info.empty();
    div_wait.css('display', 'block');

   if((resume == null || resume == "")||(description.length < 1)){

      if(resume == null || resume == ""){
         $("#resume").css('border-color','red');
      }else{
         $("#resume").css('border-color','#888888');
      }

      if(description.length < 1){
         $("#description").css('border-color','red');
      }else{
         $("#description").css('border-color','#888888');
      }

      div_wait.css('display', 'none');

   }else{


      $.ajax({ // function that allows the ajax
         type: "POST", // method to transmit data to php file
         url: "{$root_ajax}", // url of php file
         data: "action=LinkIssueGlpiToProjectMantis&" +
            "idTicket=" + idTicket + "&" +
            "nameMantisProject=" + nameMantisProject + "&" +
            "user=" + idUser + "&" +
            "dateEscalade=" + date + "&" +
            "resume=" + resume + "&" +
            "assign=" + assign + "&" +
            "glpiField=" + glpiField + "&" +
            "itemType=" + itemType + "&" +
            "linkedTicket=" + linkedTicket + "&" +
            "stepToReproduce=" + stepToReproduce + "&" +
            "followAttachment=" + followAttachment + "&" +
            "glpiUrl=" + glpiUrl + "&" +
            "followFollow=" + followFollow + "&" +
            "followTask=" + followTask + "&" +
            "followTitle=" + followTitle + "&" +
            "followDescription=" + followDescription + "&" +
            "followCategorie=" + followCategorie + "&" +
            "categorie=" + cate + "&" +
            "description=" + description, // data to be transmitted
         success: function (msg) { // if the call has worked fine

            if (msg == true) {
               div_wait.css('display', 'none');
               popupLinkGlpiIssuetoMantisProject.hide();
               window.location.reload(true);
            }
            else {
               div_wait.css('display', 'none');
               div_info.html(msg);
            }

         },
         error: function () {

            div_wait.css('display', 'none');
            div_info.html("Probleme Ajax");

         }

      });
   }
}

function deleteLinkGlpiMantis(id, idticket, idMantis, deleteAll) {

   var question = "";
   if (deleteAll) question = "Vous allez supprimer l'issue MantisBT ainsi que le lien";
   else question = "Vous allez supprimer le lien vers l'issue mantisBT";

   //alert (id+" --- "+idticket+" --- "+idMantis);

   if (confirm(question)) {

      $.ajax({ // function that allows the ajax
         type: "POST", // method to transmit data to php file
         url: "{$root_ajax}", // url of php file
         data: "action=deleteLinkMantis&" +
            "id=" + id + "&" +
            "idMantis=" + idMantis + "&" +
            "items_id=" + idticket,// data to be transmitted
         success: function (msg) { // if the call has worked fine

            if (msg == true) {
               window.location.reload(true);
            }
            else {
               alert(msg);
            }

         },
         error: function () {
            alert("Ajax problem");
         }

      });

   }

}

function delLinkAndOrIssue(id, idMantis, idTicket) {

   var checkIssue = $('#deleteIssue' + id);
   var checkLink = $('#deleteLink' + id);

   var div_wait = $('#waitDelete' + id);
   var div_info = $('#infoDel' + id);

    var itemType = $('#itemType' + id).val();

   var popupName = "popupToDelete" + id;
   var popup = $('input[name="' + popupName + '"]');

   //alert(itemType);

   if (checkIssue.is(':checked') && !checkLink.is(':checked') ||
      checkIssue.is(':checked') && checkLink.is(':checked')) {

      div_wait.css('display', 'block');
      $.ajax({ // function that allows the ajax
         type: "POST", // method to transmit data to php file
         url: "{$root_ajax}", // url of php file
         data: "action=deleteIssueMantisAndLink&" +
            "id=" + id + "&" +
            "idMantis=" + idMantis + "&" +
            "items_id=" + idTicket,// data to be transmitted
         success: function (msg) { // if the call has worked fine

            if (msg == true) {
               div_wait.css('display', 'none');
               eval(popupName).hide();
               window.location.reload(true);
            }
            else {
               div_wait.css('display', 'none');
               div_info.html(msg);
            }

         },
         error: function () {
            div_wait.css('display', 'none');
            div_info.html('Problem Ajax');
         }

      });
   }

   if (!checkIssue.is(':checked') && checkLink.is(':checked')) {

      div_wait.css('display', 'block');
      $.ajax({ // function that allows the ajax
         type: "POST", // method to transmit data to php file
         url: "{$root_ajax}", // url of php file
         data: "action=deleteLinkMantis&" +
            "id=" + id + "&" +
            "idMantis=" + idMantis + "&" +
            "items_id=" + idTicket,// data to be transmitted
         success: function (msg) { // if the call has worked fine

            if (msg == true) {
               div_wait.css('display', 'none');
               eval(popupName).hide();
               window.location.reload(true);
            }
            else {
               div_wait.css('display', 'none');
               div_info.html(msg);
            }

         },
         error: function () {
            div_wait.css('display', 'none');
            div_info.html('Problem Ajax');
         }

      });
   }

}


function getBaseURL() {
   var url = location.href;
   var baseURL = url.substring(0, url.indexOf('/', 14));

   if (baseURL.indexOf('http://localhost') != -1) {
      var pathname = location.pathname;
      var index1 = url.indexOf(pathname);
      var index2 = url.indexOf("/", index1 + 1);
      var baseLocalUrl = url.substr(0, index2);

      return alert(baseLocalUrl);
   }
   else {
      return alert(baseURL);
   }

}
JAVASCRIPT;

echo $JS;
