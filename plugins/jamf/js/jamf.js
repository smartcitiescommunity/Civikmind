/*
 -------------------------------------------------------------------------
 JAMF plugin for GLPI
 Copyright (C) 2019-2020 by Curtis Conard
 https://github.com/cconard96/jamf
 -------------------------------------------------------------------------
 LICENSE
 This file is part of JAMF plugin for GLPI.
 JAMF plugin for GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 JAMF plugin for GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with JAMF plugin for GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/* global GLPI_PLUGINS_PATH */
/* global CFG_GLPI */
(function(){
   window.JamfPlugin = function() {
      var self = this;

      /**
       * All possible MDM commands for the item on this page (if applicable).
       * @since 1.1.0
       * @type {{}}
       */
      this.commands = {};

      this.dialog_confirm_command = null;

      this.dialog_send_command = null;

      this.jamf_id = -1;

      this.itemtype = null;

      this.items_id = -1;

      /**
       * The AJAX directory.
       * @since 1.1.0
       * @type {string}
       */
      this.ajax_root = '';

      this.init = function (args) {
         if (args !== undefined && args.commands !== undefined) {
            self.commands = args.commands;
            self.jamf_id = args.jamf_id;
            self.itemtype = args.itemtype;
            self.items_id = args.items_id;
            self.ajax_root = args.ajax_root || CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.jamf+"/ajax/";
         }
      };

      this.onMDMCommandButtonClick = function(command, event) {
         event.preventDefault();
         if (self.commands[command] !== undefined) {
            if (self.commands[command]['params'] !== undefined) {
               showMDMCommandForm(command);
            } else if (self.commands[command]['confirm'] !== undefined && self.commands[command]['confirm'] === true) {
               showMDMCommandConfirmation(command);
            } else {
               self.sendMDMCommand(command);
            }
         }
      };

      /**
       *
       * @param {Object} command
       */
      var showMDMCommandForm = function(command) {
         $.ajax({
            method: 'GET',
            url: (self.ajax_root + "getMDMCommandForm.php"),
            data: {
               command: command
            }
         }).done(function (data) {
            if (data !== undefined && data !== null) {
               if (self.dialog_send_command !== undefined && self.dialog_send_command !== null) {
                  self.dialog_send_command.remove();
               }
               self.dialog_send_command = $(data).appendTo('#page');
               self.dialog_send_command.dialog({
                  autoOpen: false,
                  modal: true,
                  close: function() {
                     self.dialog_send_command.remove();
                  }
               });
               self.dialog_send_command.dialog({
                  buttons : {
                     "Send" : function() {
                        if (self.commands[command]['confirm'] !== undefined && self.commands[command]['confirm'] === true) {
                           showMDMCommandConfirmation(command, self.dialog_send_command.serialize());
                        } else {
                           self.sendMDMCommand(command, self.dialog_send_command.serialize());
                        }
                     },
                     "Cancel" : function() {
                        $(this).dialog("close");
                     }
                  }
               });
               self.dialog_send_command.dialog("open");
            }
         });
      };

      var showMDMCommandConfirmation = function(command, params) {
         if (self.dialog_confirm_command === undefined || self.dialog_confirm_command === null) {
            self.dialog_confirm_command = $("<div id='jamf-mdmcommand-confirm'></div>").appendTo('#page');
            $(document).ready(function() {
               $('#jamf-mdmcommand-confirm').dialog({
                  autoOpen: false,
                  modal: true,
                  close: function() {
                     self.dialog_confirm_command.remove();
                  }
               });
            });
         }

         self.dialog_confirm_command.dialog({
            buttons : {
               "Confirm" : function() {
                  self.sendMDMCommand(command, params);
               },
               "Cancel" : function() {
                  $(this).dialog("close");
               }
            }
         });

         var warn_text = _x('message', 'Are you sure you want to send the command: %s?', 'jamf').replace("%s", _x('mdm_command', self.commands[command].name, 'jamf'));
         self.dialog_confirm_command.text(warn_text);
         self.dialog_confirm_command.dialog("open");
      };

      /**
       *
       */
      this.sendMDMCommand = function(command, params) {
         if (params === undefined) {
            params = '';
         }
         $.ajax({
            method: 'POST',
            url: (self.ajax_root + "sendMDMCommand.php"),
            data: {
               command: command,
               fields: params,
               jamf_id: self.jamf_id,
               itemtype: self.itemtype,
               items_id: self.items_id
            }
         }).always(function() {
            location.reload();
         });
      };
   };
})();