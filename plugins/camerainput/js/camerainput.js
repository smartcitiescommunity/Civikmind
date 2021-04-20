/*
 -------------------------------------------------------------------------
 Camera Input
 Copyright (C) 2020-2021 by Curtis Conard
 https://github.com/cconard96/glpi-camerainput-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Camera Input.
 Camera Input is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Camera Input is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Camera Input. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
*/

/* global CFG_GLPI */
/* global GLPI_PLUGINS_PATH */
$(document).on('ready', function() {
   if (typeof navigator.mediaDevices === 'undefined' || typeof navigator.mediaDevices.getUserMedia === 'undefined' || typeof ImageCapture === 'undefined') {
      return;
   }

   function getQuaggaConfig() {
      let plugin_config = {
         barcode_formats: ["code_39_reader", "code_128_reader"]
      };
      $.ajax({
         method: "GET",
         url: (CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.camerainput + "/ajax/config.php"),
         async: false
      }).done((config) => {
         plugin_config = config;
      });
      return {
         numOfWorkers: 0,
         locate: true,
         inputStream : {
            name : "Live",
            type : "LiveStream",
            target: '#camera-input-viewport'
         },
         decoder : {
            readers : plugin_config['barcode_formats']
         },
         locator: {
            halfSample: false,
            patchSize: "medium", // x-small, small, medium, large, x-large
         }
      };
   }

   // Initialize viewport
   $(`<div id="camera-input-viewport"><video autoplay muted preload="auto"></video></div>`).appendTo('main');
   $('#camera-input-viewport').dialog({
      autoOpen: false,
      width: 640,
      height: 400,
      position: {
         my: "center",
         at: "center",
         of: window
      },
      resizable: false,
      close: function() {
         Quagga.stop();
      }
   });
   // Dynamically resize video element and dialog
   $("#camera-input-viewport video").on('loadedmetadata', function() {
      const vidWidth = Math.min(window.innerWidth - 10, 640);
      const vidHeight = vidWidth * 0.5625; // 16:9
      this.width = vidWidth;
      this.height = vidHeight;
      $('#camera-input-viewport').dialog("option", "width", vidWidth);
      $('#camera-input-viewport').dialog("option", "height", vidHeight + 60);
   });

   // Hook into global search box
   const global_search = $('#champRecherche');
   if (global_search.length > 0) {
      global_search.append(`
         <button type="button" class="camera-input" title="Camera search">
             <i class="fas fa-camera"></i>
         </button>`);
      global_search.find('.camera-input').on('click', function() {
         $('#camera-input-viewport').dialog('open');
         Quagga.init(getQuaggaConfig(), function(err) {
            if (err) {
               console.log(err);
               return
            }
            Quagga.start();
         });

         Quagga.onDetected(function(data) {
            Quagga.stop();
            global_search.find('input[name="globalsearch"]').val(data.codeResult.code);
            global_search.find('button[type="submit"]').click();
         });
      });
   }

   // Hook into Physical Inventory plugin search (if present)
   if (window.location.href.indexOf('/physicalinv/front') > -1) {
      const physinv_search = $('main form').first();
      if (physinv_search) {
         physinv_search.find('input[name="searchnumber"]').after(`
         <button type="button" class="camera-input pointer" style="border-radius: 3px 3px 3px 3px; padding: 3px; background: white; border: none; height: 40px" title="Camera search">
             <i class="fas fa-camera fa-lg"></i>
         </button>`);
         physinv_search.find('.camera-input').on('click', function() {
            $('#camera-input-viewport').dialog('open');
            Quagga.init(getQuaggaConfig(), function(err) {
               if (err) {
                  console.log(err);
                  return
               }
               Quagga.start();
            });

            Quagga.onDetected(function(data) {
               Quagga.stop();
               physinv_search.find('input[name="searchnumber"]').val(data.codeResult.code);
               physinv_search.find('input[type="submit"]').click();
            });
         });
      }
   }

   // Hook into Asset Audit plugin search (if present)
   if (window.location.href.indexOf('/assetaudit/front') > -1) {
      const assetaudit_search = $('main form').first();
      if (assetaudit_search) {
         assetaudit_search.find('input[name="search_criteria"]').after(`
         <button type="button" class="camera-input pointer" style="border-radius: 3px 3px 3px 3px; padding: 3px; background: white; border: none; height: 40px" title="Camera search">
             <i class="fas fa-camera fa-lg"></i>
         </button>`);
         assetaudit_search.find('.camera-input').on('click', function() {
            $('#camera-input-viewport').dialog('open');
            Quagga.init(getQuaggaConfig(), function(err) {
               if (err) {
                  console.log(err);
                  return
               }
               Quagga.start();
            });

            Quagga.onDetected(function(data) {
               Quagga.stop();
               assetaudit_search.find('input[name="search_criteria"]').val(data.codeResult.code);
               assetaudit_search.find('input[type="submit"]').click();
            });
         });
      }
   }
});
