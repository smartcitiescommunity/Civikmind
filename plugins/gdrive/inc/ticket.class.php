<?php
/*
 -------------------------------------------------------------------------
 Gdrive plugin for GLPI
 Copyright (C) 2018 by the TICgal Team.

 https://github.com/pluginsGLPI/gdrive
 -------------------------------------------------------------------------

 LICENSE

 This file is part of the Gdrive plugin.

 Gdrive plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 3 of the License, or
 (at your option) any later version.

 Gdrive plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Gdrive. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   gdrive
 @author    the TICgal team
 @copyright Copyright (c) 2018 TICgal team
 @license   AGPL License 3.0 or (at your option) any later version
            http://www.gnu.org/licenses/agpl-3.0-standalone.html
 @link      https://tic.gal
 @since     2018
 ---------------------------------------------------------------------- */
if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

/**
* 
*/
class PluginGdriveTicket extends CommonDBTM{

	public static $rightname = 'ticket';

	static function getTypeName($nb = 0) {
		return __('GDrive', 'Gdrive');
	}

	static public function postForm($params) {
		global $CFG_GLPI;
		$item = $params['item'];
		$config= PluginGdriveConfig::getConfig();

		switch ($item->getType()) {
			case 'Ticket':
			case 'ITILFollowup':
			case 'TicketTask':
				$out="<script type='text/javascript'>

				// The Browser API key obtained from the Google API Console.
				// Replace with your own Browser API key, or your own key.
				var developerKey = '".$config->fields['developer_key']."';

				// The Client ID obtained from the Google API Console. Replace with your own Client ID.
				var clientId = '".$config->fields['client_id']."'

				// Replace with your own project number from console.developers.google.com.
				// See 'Project number' under 'IAM & Admin' > 'Settings'
				var appId = '".$config->fields['app_id']."';

				// Scope to use to access user's Drive items.
				var scope = ['https://www.googleapis.com/auth/drive'];

				var pickerApiLoaded = false;
				var oauthToken;

				// Use the Google API Loader script to load the google.picker script.
				function loadPicker() {
					gapi.load('auth', {'callback': onAuthApiLoad});
					gapi.load('picker', {'callback': onPickerApiLoad});
				}

				function onAuthApiLoad() {
					var authBtn = document.getElementById('auth');
					authBtn.disabled = false;
					authBtn.addEventListener('click', function() {
						gapi.auth2.authorize({
							client_id: clientId,
							scope: scope
						}, handleAuthResult);
					});
			    }

				function onPickerApiLoad() {
					pickerApiLoaded = true;
				}

				function handleAuthResult(authResult) {
					if (authResult && !authResult.error) {
						oauthToken = authResult.access_token;
						document.cookie='access_token='+oauthToken;
						createPicker();
					}else{
						if(authResult.error=='popup_closed_by_user'){
							oauthToken=getCookie('access_token');
							createPicker();
						}else{
							alert(authResult.error);
						}
					}
				}
				//Get content of cookie
				function getCookie(cname){
					var name = cname + '=';
				    var decodedCookie = decodeURIComponent(document.cookie);
				    var ca = decodedCookie.split(';');
				    for(var i = 0; i <ca.length; i++) {
				        var c = ca[i];
				        while (c.charAt(0) == ' ') {
				            c = c.substring(1);
				        }
				        if (c.indexOf(name) == 0) {
				            return c.substring(name.length, c.length);
				        }
				    }
				    return '';
				}

				// Create and render a Picker object for picking user Documents.
				function createPicker() {
					if (pickerApiLoaded && oauthToken) {
						var picker = new google.picker.PickerBuilder()
						.enableFeature(google.picker.Feature.MULTISELECT_ENABLED)
						.setAppId(appId)
						.addView(google.picker.ViewId.DOCS)
						.setOAuthToken(oauthToken)
						.setDeveloperKey(developerKey)
						.setCallback(pickerCallback)
						.build();
						picker.setVisible(true);
					}
				}

				// A simple callback implementation.
				function pickerCallback(data) {
					var message = '".__('Uploaded file','gdrive')."';
					if (data[google.picker.Response.ACTION] == google.picker.Action.PICKED) {
						var fileInput=document.querySelectorAll('[type=file]')[0];
						for(var i=0;i<data[google.picker.Response.DOCUMENTS].length;i++){
							var file=data[google.picker.Response.DOCUMENTS][i];
							downloadFile(file,fileInput,function(res){
								if(data==false){
									message='".__('Error loading the file','gdrive')."';
								}
							});
						}
					}
					document.getElementById('result').innerHTML = message;
				}

				function downloadFile(file,fileInput,callback){
					if (file[google.picker.Document.URL]) {
						var accessToken = oauthToken;
						var xhr = new XMLHttpRequest();
						xhr.responseType = 'blob';
						xhr.open('GET', 'https://www.googleapis.com/drive/v2/files/'+file[google.picker.Document.ID]+'?alt=media');
						xhr.setRequestHeader('Authorization', 'Bearer ' + accessToken);
						xhr.onload = function() {
							var fil = new File([xhr.response], file[google.picker.Document.NAME], {type: file[google.picker.Document.MIME_TYPE], lastModified: Date.now()});
							var editor = {targetElm: fileInput};
							var fileTag=uploadFile(fil,editor);
							callback(true);
						};
						xhr.onerror = function() {
							alert('Error: '+xhr.error);
							callback(false);
						};
						xhr.send();
					} else {
						alert('Sin url');
						callback(false);
					}
				}
			    </script>";
				$out .= "<tr>
					<th colspan='2'>".self::getTypeName(2)."</th>
				</tr>";
				$out .= "<tr>
					<td>
						<label>".__('Select file Drive','gdrive')."</label>
					</td>
					<td align='center'><button type='button' id='auth' disabled>".__('Select file','gdrive')."</button><div id='result'></div></td>
				</tr>";
				$out.='<script type="text/javascript" src="https://apis.google.com/js/client.js?onload=loadPicker"></script>';
				echo $out;
				break;
		}
		
	}
}