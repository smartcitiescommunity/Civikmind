/*
 -------------------------------------------------------------------------
 Screenshot
 Copyright (C) 2020-2021 by Curtis Conard
 https://github.com/cconard96/glpi-screenshot-plugin
 -------------------------------------------------------------------------
 LICENSE
 This file is part of Screenshot.
 Screenshot is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Screenshot is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Screenshot. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/* global CFG_GLPI */
/* global GLPI_PLUGINS_PATH */
window.GLPIMediaCapture = new function() {

   /**
    * Array storing the size used for the preview canvas in the format [width, height].
    * @type {number[]}
    */
   const preview_size = [200, 180];

   let config = {};

   function isMobileBrowser() {
      const userAgent = navigator.userAgent.toLowerCase();
      return userAgent.match(/ipad|iphone|ipod|android/i);
   }

   /**
    * Check if the browser supports this feature. If not, this will hide the timeline button.
    */
   this.evalTimelineAction = function() {
      if (isMobileBrowser()) {
         $('#attach_screenshot_timeline').hide();
         $('#attach_screenrecording_timeline').hide();
      }
   }

   /**
    * Update a preview and full-size canvas based on the supplied image.
    * Each canvas parameter is optional and can be skipped by setting it to null.
    *
    * @param {ImageBitmap|HTMLVideoElement} img The image.
    * @param {HTMLCanvasElement} preview The canvas being used to preview the image/frame.
    * @param {HTMLCanvasElement} full The full-size canvas that stores the image/frame. Not needed if doing a recording.
    */
   function updateCanvases(img, preview = null, full = null) {
      const sourceWidth = img.videoWidth ?? img.width;
      const sourceHeight = img.videoHeight ?? img.height;
      if (preview !== null) {
         preview.width = preview_size[0];
         preview.height = preview_size[1];
         let ratio = Math.min(preview.width / sourceWidth, preview.height / sourceHeight);
         let x = (preview.width - sourceWidth * ratio) / 2;
         let y = (preview.height - sourceHeight * ratio) / 2;
         preview.getContext('2d').clearRect(0, 0, preview.width, preview.height);
         preview.getContext('2d').drawImage(img, 0, 0, sourceWidth, sourceHeight,
            x, y, sourceWidth * ratio, sourceHeight * ratio);
      }
      if (full !== null) {
         full.width = sourceWidth;
         full.height = sourceHeight;
         full.getContext('2d').clearRect(0, 0, sourceWidth, sourceHeight);
         full.getContext('2d').drawImage(img, 0, 0, sourceWidth, sourceHeight,
            0, 0, sourceWidth, sourceHeight);
      }
   }

   /**
    * Prompt the user to select a screen device, (re)-build the form, grab the first frame only, and update the preview and full-size image canvases.
    * @param {jQuery} form_obj The form object that will be cleared and have the canvases and upload button added to.
    * @param {string} itemtype The type of the item this recording would be attached to.
    * @param {integer} items_id The ID of the item this recording would be attached to.
    */
   function captureScreenshot(form_obj, itemtype, items_id) {
      navigator.mediaDevices.getDisplayMedia({video: true})
      .then(mediaStream => {
         const track = mediaStream.getVideoTracks()[0];
         // Clear any previous elements in case this is being reused
         form_obj.empty();
         // Remove any previous event handlers
         form_obj.off();
         form_obj.html(`
            <table class="tab_cadre_fixe">
               <tr class="headerRow"><th>New Item - Screenshot</th></tr>
               <tr>
                   <td>
                     <canvas id="screenshotPreview" width="${preview_size[0]}" height="${preview_size[1]}"></canvas>
                     <canvas id="screenshotFull" width="200" height="180" style="display: none"></canvas>
                   </td>
               </tr>
               <tr>
                   <td>
                       <button type="submit" name="upload" class="vsubmit">${__('Upload', 'screenshot')}</button>
                   </td>
               </tr>
            </table>
         `);
         // Bind upload action handler
         form_obj.on('click', 'button[name="upload"]', function(e) {
            e.preventDefault();
            const img_format = config['screenshot_format'];
            const canvas = form_obj.find('#screenshotFull').get(0);
            const base64 = canvas.toDataURL(img_format);
            const ajax_data = {
               itemtype: itemtype,
               items_id: items_id,
               format: img_format,
               img: base64
            };
            $(this).attr('disabled', true);
            $.ajax({
               type: 'POST',
               url: CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.screenshot+"/ajax/screenshot.php",
               data: ajax_data
            }).done(function() {
               location.reload();
            });
         });

         if (typeof ImageCapture !== "undefined") {
            imageCapture = new ImageCapture(track);
            imageCapture.grabFrame().then(img => {
               updateCanvases(img, form_obj.find('#screenshotPreview').get(0), form_obj.find('#screenshotFull').get(0));
               track.stop();
            })
         } else {
            const video = document.createElement('video');
            video.srcObject = mediaStream;

            return new Promise((resolve, reject) => {
               try {
                  video.addEventListener('loadeddata', event => {
                     video.play().then(() => {
                        updateCanvases(video, form_obj.find('#screenshotPreview').get(0), form_obj.find('#screenshotFull').get(0));
                        track.stop();
                     });
                  });
               } catch(error) {
                  track.stop();
                  reject(error);
               }
            });
         }
      });
   }

   function getPreferredBitrate(track) {
      // Reference Bitrates based on YouTube recommendations
      // 360@30 - 1 Mbps   | 360@60 - 1.5 Mbps | Pixel Count - 230400
      // 720@30 - 5 Mbps   | 720@60 - 7.5 Mbps | Pixel Count - 921600
      // 1080@30 - 8 Mbps  | 1080@60 - 12 Mbps | Pixel Count - 2073600
      // 1440@30 - 16 Mbps | 1440@60 - 24 Mbps | Pixel Count - 3686400
      // 2160@30 - 40 Mbps | 2160@60 - 60 Mbps | Pixel Count - 8294400

      const motion_factor = 0.5; // Weight value. How much activity we expect
      // br = (pixels * f * motion_factor) / 10;

      const settings = track.getSettings();
      return ((settings.width * settings.height) * settings.frameRate * motion_factor) / 10;
   }

   function getRecordingCodec(requested_format) {
      const codecs = ['vp9', 'vp8'];
      return codecs.find(c => MediaRecorder.isTypeSupported(requested_format + ';codecs=' + c))
   }

   /**
    * Prompt the user to select a screen device, (re)-build the form, and wait for the user to start the MediaRecorder.
    * Then, this will continually grab frames from the video stream at a rate of 10 FPS and update the preview canvas until the user stops the recording.
    * They can either restart the recording or upload the last recording.
    * @param {jQuery} form_obj The form object that will be cleared and have the canvases and buttons added to.
    * @param {string} itemtype The type of the item this recording would be attached to.
    * @param {integer} items_id The ID of the item this recording would be attached to.
    */
   function showRecordingForm(edit_panel, itemtype, items_id) {
      edit_panel.empty();
      navigator.mediaDevices.getDisplayMedia({video: true, frameRate: 10})
      .then(mediaStream => {
         const track = mediaStream.getVideoTracks()[0];

         let recorder = new MediaRecorder(mediaStream, {
            mimeType: 'video/webm;codecs='+getRecordingCodec('video/webm'),
            videoBitsPerSecond: getPreferredBitrate(track),
         });
         let blob = null;

         const stopRecording = function() {
            recorder.stop();
            const tracks = mediaStream.getTracks();
            tracks.forEach(function(track) {
               track.stop();
            });
            //$(this).parent().append(`<button type="button" name="restart" class="vsubmit">${__('Restart recording', 'screenshot')}</button>`);
            $(this).parent().append(`<button type="button" name="upload" class="vsubmit">${__('Upload', 'screenshot')}</button>`);
            $(this).remove();
         }
         const upload = function() {
            if (blob === null) {
               return;
            }
            $(this).attr('disabled', true);
            const data = new FormData();
            data.append('blob', blob);
            data.append('itemtype', itemtype);
            data.append('items_id', items_id);
            data.append('format', 'video/webm');
            $.ajax({
               type: 'POST',
               url: CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.screenshot+"/ajax/screenshot.php",
               data: data,
               processData: false,
               contentType: false
            }).done(function() {
               location.reload();
            });
         }

         $(edit_panel).on('click', 'button[name="stop"]', {}, stopRecording);
         $(edit_panel).on('click', 'button[name="upload"]', {}, upload);

         let chunks = [];
         recorder.ondataavailable = function(event) {
            if (event.data.size > 0) {
               chunks.push(event.data);

               // Create blob
               blob = new Blob(chunks, {
                  type: 'video/webm'
               });
            }
         }
         recorder.onstart = function() {
            // No-op
         }
         // Start recording the video stream
         const startRecording = function() {
            edit_panel.find('button:not([name="start"])').remove();
            $(this).parent().append(`<button type="button" name="stop" class="vsubmit">${__('Stop recording', 'screenshot')}</button>`);
            $(this).remove();

            recorder.start();
            edit_panel.find('#screenshotPreview').get(0).srcObject = recorder.stream;
         }
         const restartRecording = function() {
            if (recorder !== undefined) {
               try {
                  recorder.stop();
               } catch {}
            }
            blob = null;
            showRecordingForm(edit_panel, itemtype, items_id);
         }
         $(edit_panel).on('click', 'button[name="start"]', {}, startRecording);
         //$(edit_panel).on('click', 'button[name="restart"]', {}, restartRecording);
      });

      $(edit_panel).html(`
         <table class="tab_cadre_fixe">
            <tr class="headerRow"><th>New Item - Screen Recording</th></tr>
            <tr>
                <td>
                  <video id="screenshotPreview" width="${preview_size[0]}" height="${preview_size[1]}" autoplay muted></video>
                </td>
            </tr>
            <tr>
                <td>
                    <button type="button" name="start" class="vsubmit">${__('Start recording', 'screenshot')}</button>
                </td>
            </tr>
         </table>
      `);
   }

   // Get Config
   $.ajax({
      type: 'GET',
      url: CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.screenshot+"/ajax/config.php",
   }).done(function(cfg) {
      config = cfg;
   });

   $(document).on('click', '#attach_screenshot_timeline', function() {
      const edit_panel = $($(this).data('editpanel'));
      const itemtype = $(this).data('itemtype');
      const items_id = $(this).data('items_id');
      captureScreenshot(edit_panel, itemtype, items_id);
   });

   $(document).on('click', '#attach_screenrecording_timeline', function() {
      const edit_panel = $($(this).data('editpanel'));
      const itemtype = $(this).data('itemtype');
      const items_id = $(this).data('items_id');
      showRecordingForm(edit_panel, itemtype, items_id);
   });
}