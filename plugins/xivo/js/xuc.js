var Xuc = function() {
   var debugCTI        = false;

   var username        = '';
   var password        = '';
   var phoneNumber     = '';
   var pageNumbers     = [];
   var bearerToken     = '';
   var callerGlpiInfos = {};
   var lastState       = null;
   var lastStateDate   = null;

   var do_auto_open    = true;

   var logged          = false;
   var plugin_ajax_url = "";

   var userStatuses    = {};

   var xivo_config     = {};
   var xivo_store      = {};

   // possible states
   // * AgentLogin
   // * AgentReady
   // * AgentOnPause
   // * AgentRinging
   // * AgentOnCall
   // * AgentDialing
   // * AgentOnWrapup
   // * AgentLoggedOut
   //
   // this var will be in this format {phonenum: state, ...}
   var agentsState     = {};

   var callerNum       = '';
   var callerName      = ''
   var redirectTo      = false;

   // click2call cache to avoid redundant ajax requests
   var users_cache     = {};

   var my_xuc = this;

   /**
    * Set storage var from xivo config
    * @return {void
    */
   my_xuc.detectStore = function() {
      // load session storage
      if (my_xuc.xivo_config.xuc_local_store) {
         console.debug("xivo plugin use local storage");
         my_xuc.xivo_store = store.local;
      } else {
         console.debug("xivo plugin use session storage");
         my_xuc.xivo_store = store.session;
      }
   }

   /**
    * Init UI in GLPI
    * @param  {Object} xivo_config the current xivo configuration
    * @return {void}
    */
   my_xuc.init = function(xivo_config) {
      my_xuc.xivo_config = xivo_config;
      my_xuc.detectStore();
      my_xuc.setAjaxUrl();
      my_xuc.retrieveXivoSession();
      my_xuc.initAgentForm();

      if (my_xuc.retrieveXivoSession() !== false) {
         $.when(my_xuc.checkTokenValidity())
            .then(function() {
               my_xuc.initConnection();
            })
            .fail(function(jqXHR, textStatus) {
               if (jqXHR.responseJSON.error == "TokenExpired") {
                  my_xuc.destroyXivoSession();
               }
            });
      }
   };

   my_xuc.initAgentForm = function() {
      $("#c_preference ul #preferences_link")
         .after("<li id='xivo_agent'>\
                  <a class='fa fa-phone' id='xivo_agent_button'></a>\
                  <i class='fa fa-circle' id='xivo_agent_status'></i>\
                  <div id='xivo_agent_form'>empty</div>\
                </li>");

      $(document)
         .on("click", "#xivo_agent_button", function() {
            $("#xivo_agent_form").toggle();
            if (!logged) {
               my_xuc.loadLoginForm();
            }
         })
         .on("submit", "#xuc_login_form", function(e) {
            e.preventDefault();
            my_xuc.xucSignIn();
         })
         .on("click", "#xuc_sign_in", function(e) {
            e.preventDefault();
            my_xuc.xucSignIn();
         })
         .on("click", "#xuc_sign_out", function(e) {
            e.preventDefault();
            my_xuc.xucSignOut();
         })
         .on("click", "#xuc_hangup", function(e) {
            e.preventDefault();
            my_xuc.hangup();
         })
         .on("click", "#xuc_answer", function(e) {
            e.preventDefault();
            my_xuc.answer();
         })
         .on("click", "#xuc_hold", function(e) {
            e.preventDefault();
            my_xuc.hold();
         })
         .on("click", "#xuc_dial", function(e) {
            e.preventDefault();
            my_xuc.dial();
         })
         .on("keypress", "#dial_phone_num", function(e) {
            if (e.which === 13) { //enter key
               e.preventDefault();
               my_xuc.dial();
            }
         })
         .on("click", "#xuc_transfer", function(e) {
            e.preventDefault();
            my_xuc.transfer();
         })
         .on("keypress", "#transfer_phone_num", function(e) {
            if (e.which === 13) { //enter key
               e.preventDefault();
               my_xuc.transfer();
            }
         })
         .on("click", "#deconnexion", function(e) {
            if (!my_xuc.xivo_config.xuc_local_store) {
               my_xuc.destroyXivoSession();
            }
         });
   }

   my_xuc.setAjaxUrl = function() {
      plugin_ajax_url = CFG_GLPI.root_doc+"/"+GLPI_PLUGINS_PATH.mreporting+"/ajax/xuc.php";
   };

   /**
    * Check the current token store as object property is still valid on xuc
    * @return Ajax Promise
    */
   my_xuc.checkTokenValidity = function() {
      return $.ajax({
         type: "GET",
         url: my_xuc.xivo_config.xuc_url + "/xuc/api/2.0/auth/check",
         dataType: 'json',
         beforeSend : function(xhr) {
            xhr.setRequestHeader('Authorization', 'Bearer ' + bearerToken);
         }
      });
   };

   /**
    * Init connection to CTI with xuc libs.
    * Init XIVO events (phones, statuses events)
    */
   my_xuc.initConnection = function() {
      $.when(my_xuc.loadLoggedForm()).then(function() {
         if (debugCTI) {
            Cti.debugMsg = true;
         }
         Cti.clearHandlers();

         var protocol = "ws";
         if (my_xuc.xivo_config.xuc_secure) {
            protocol = "wss";
         }

         var wsurl = my_xuc.xivo_config.xuc_url.replace(/https*:\/\//, protocol+'://')
                        + "/xuc/api/2.0/cti?token="+bearerToken;
         Cti.WebSocket.init(wsurl, username, phoneNumber);

         Callback.init(Cti);
         Membership.init(Cti);
         logged = true;

         Cti.setHandler(Cti.MessageType.LOGGEDON, function() {
            $("#xivo_agent_form").hide();

            // get list of status for users
            Cti.setHandler(Cti.MessageType.USERSTATUSES, my_xuc.setUserStatuses);

            // callback for user's status change
            Cti.setHandler(Cti.MessageType.USERSTATUSUPDATE, my_xuc.userStatusUpdate);
            Cti.setHandler(Cti.MessageType.PHONESTATUSUPDATE, function(event) {
               if (event.status !== null) {
                  $("#xuc_phone_status").val(event.status);
               }
            });
            Cti.setHandler(Cti.MessageType.USERCONFIGUPDATE, function(event) {
               if (event.fullName !== null) {
                  $("#xuc_fullname").text(event.fullName);
               }
            });

            // intercept current user's phone events to swith status in panel
            Cti.setHandler(Cti.MessageType.PHONEEVENT, my_xuc.phoneEvents);

            // enable status icon for known phone numbers
            if (my_xuc.xivo_config.enable_presence) {
               console.debug("enable_presence");
               // intercept phone events
               Cti.setHandler(Cti.MessageType.PHONEHINTSTATUSEVENT, my_xuc.phoneHintsEvents);

               console.debug("subscribe to these numbers", pageNumbers);
               Cti.subscribeToPhoneHints(pageNumbers);
            }

            // restore last state of ui (after a browser navigation for example)
            my_xuc.restoreLastState();
         });
      });
   };

   /**
    * Populate select html tag for user status with data returned by CTI
    */
   my_xuc.setUserStatuses = function(statuses) {
      userStatuses = statuses;
      $("#xuc_user_status").empty();
      $.each(statuses, function(key, item) {
         $("#xuc_user_status")
            .append("<option data-color='"+item.color+"' value='"+item.name+"'>"+ item.longName + "</option>");
      });

      var formatStatus = function (status) {
         var option = status.element;
         var color = $(option).data('color');

         var template = "<span>"
            + "<i class='fa fa-circle' style='color: "+color+"'></i>&nbsp;"
            + status.text.toUpperCase()
            + "</span>";
         return $(template);
      };

      $("#xuc_user_status").select2({
         width: '180px',
         minimumResultsForSearch: -1,
         formatResult: formatStatus,
         formatSelection: formatStatus,
         templateResult: formatStatus,
         templateSelection: formatStatus
      });

      // set cti event on change select
      // double event watching (one for glpi9.3 and select2 v4, second for glpi9.2 and select2 v3.5)
      $('#xuc_user_status').on('select2:select select2-selecting', function (e) {
         var optionSelected = $(this).find("option:selected").val();
         // 9.2 compatibility
         if ("val" in e) {
            optionSelected = e.val;
         }
         Cti.changeUserStatus(optionSelected);
      });
   };

   my_xuc.agentStateEventHandler = function(agentState) {
      var agent_num = agentState.phoneNb;
      if (agent_num.length) {
         console.debug(agent_num, agentState.name);
         agentsState[agent_num] = agentState.name;
         my_xuc.saveXivoSession();
         $('.xivo_callto_link')
            .filter('[data-phone="'+ agent_num +'"]')
            .removeClass (function (index, className) {
               // remove class starting by 'Agent'
               return (className.match (/\Agent\S+/g) || []).join(' ');
            })
            .addClass(agentState.name);
      }
   }

   my_xuc.phoneHintsEvents = function(event) {
      my_xuc.setPhonePresence(event.number, event.status);
   }

   my_xuc.directoryResultHandler = function(directory) {
      if ("entries" in directory) {
         $.each(directory.entries, function(index, entry) {
            var phone_number = entry.entry[1];
            my_xuc.setPhonePresence(phone_number, entry.status);
         });
      }
   }

   my_xuc.setPhonePresence = function(phone_num, phone_status) {
      console.debug('receieve PHONEHINTSTATUSEVENT: ', phone_num, phone_status);
      if (phone_status in Cti.PhoneStatusColors) {
         var status_color = Cti.PhoneStatusColors[phone_status];
         if (status_color == "#F2F2F2") {
            status_color = "#B6B6B6";
         }

         $('.xivo_callto_link')
            .filter('[data-phone="'+ phone_num +'"], [data-phone2="'+ phone_num +'"]')
            .css('color', status_color);
      }
   }

   /**
    * Callback triggerd when user status changes
    * @param  Object event the event passed by CTI on status change
    *                      it should contains:
    *                         - status = key of status,
    *                                    we can match to the Xuc object user_status property
    */
   my_xuc.userStatusUpdate = function(event) {
      var current_status = userStatuses.filter(function(status) {
         return status.name == event.status;
      })[0];

      $("#xivo_agent_button")
         .addClass('logged fa fa-phone')
         .removeClass (function (index, className) {
            // remove class starting by 'status_'
            return (className.match (/\bstatus_\S+/g) || []).join(' ');
         })
         .addClass('status_' + current_status.name);

      $("#xivo_agent_status")
         .css('color', current_status.color);

      if (event.status !== null) {
         $("#xuc_user_status").val(current_status.name).trigger('change');
      }
   };

   /**
    * Retrieve xivo properties in LocalStorage
    * @return bool
    */
   my_xuc.retrieveXivoSession = function() {
      var xivo_data = my_xuc.xivo_store.get('xivo');

      if (xivo_data !== null
          && typeof xivo_data == "object") {
         username      = ("username" in xivo_data      ? xivo_data.username : '');
         password      = ("password" in xivo_data      ? xivo_data.password : '');
         phoneNumber   = ("phoneNumber" in xivo_data   ? xivo_data.phoneNumber : '');
         bearerToken   = ("bearerToken" in xivo_data   ? xivo_data.bearerToken : '');
         lastState     = ("lastState" in xivo_data     ? xivo_data.lastState : null);
         lastStateDate = ("lastStateDate" in xivo_data ? new Date(xivo_data.lastStateDate) : null);
         callerNum     = ("callerNum" in xivo_data     ? xivo_data.callerNum : '');
         callerName    = ("callerName" in xivo_data    ? xivo_data.callerName : '');
         agentsState   = ("agentsState" in xivo_data   ? xivo_data.agentsState : {});

         return true;
      }

      return false;
   };

   /**
    * Clear Xivo data in LocalStorage
    */
   my_xuc.destroyXivoSession = function() {
      my_xuc.xivo_store.remove('xivo');
   };

   /**
    * Save xivo properties in LocalStorage
    */
   my_xuc.saveXivoSession = function() {
      var xivo_data = {
         'username':      username,
         'password':      password,
         'phoneNumber':   phoneNumber,
         'bearerToken':   bearerToken,
         'lastState':     lastState,
         'lastStateDate': (lastStateDate instanceof Date ? lastStateDate.toJSON(): null),
         'callerNum':     callerNum,
         'callerName':    callerName,
         'agentsState':   agentsState,
      }
      my_xuc.xivo_store.set('xivo', xivo_data);
   };

   /**
    * Load login form in GLPI UI
    */
   my_xuc.loadLoginForm = function() {
      $("#xivo_agent_form").load(plugin_ajax_url, {
         'action': 'get_login_form'
      });
   };

   /**
    * Load logged form in GLPI UI
    */
   my_xuc.loadLoggedForm = function() {
      return $.ajax({
         'type': 'POST',
         'url': plugin_ajax_url,
         'data': {
            'action': 'get_logged_form'
         },
         'success': function(html) {
            $("#xivo_agent_form").html(html)
         }
      });
   };

   /**
    * Take login form parameters, store them in LocalStorage, and init CTI connection
    */
   my_xuc.xucSignIn = function() {
      username    = $("#xuc_username").val();
      password    = $("#xuc_password").val();
      phoneNumber = $("#xuc_phoneNumber").val();

      $("#xuc_message").html("");

      $.when(my_xuc.loginOnXuc()).then(
         function(data) { // doneFilter
            bearerToken   = data.token;
            lastState     = null;
            lastStateDate = null;
            my_xuc.saveXivoSession();
            my_xuc.initConnection();
         },
         function(data) { //failFilter
            if (typeof data.responseJSON.message != "undefined") {
               $("#xuc_message")
                  .addClass('error')
                  .html(data.responseJSON.message);
            }
         });
   };

   /**
    * Logout from CTI (and reset GLPI UI)
    */
   my_xuc.xucSignOut = function() {
      Cti.webSocket.close();
      my_xuc.loadLoginForm();
      my_xuc.destroyXivoSession();
      $("#xivo_agent_form").hide();
      $("#xivo_agent_status").css('color', 'transparent');
      logged = false;
   };

   /**
    * Login on Xuc Rest API
    * @return Ajax Promise
    */
   my_xuc.loginOnXuc = function() {
      console.debug(my_xuc);
      return $.ajax({
         type: "POST",
         url: my_xuc.xivo_config.xuc_url + "/xuc/api/2.0/auth/login",
         contentType: "application/json",
         data: JSON.stringify({
            'login': username,
            'password': password
         }),
      dataType: 'json'
      });
   };

   /**
    * Find all link to user form and append they 'callto' links
    * @return nothing
    */
   my_xuc.click2Call = function() {
      var elements = [],
          users_id = [];

      console.debug("init click2Call");

      users_cache = my_xuc.xivo_store.get('users_cache') || {};

      // found all dropdowns tooltips icons
      $("#page a[id^=comment_link_users_id]:not(.callto_link_added)").each(function() {
         var that    = $(this);
         var user_id = that.parent().children('input[type=hidden]').val();

         if (user_id > 0) {
            that.user_id = user_id;
            users_id.indexOf(user_id) === -1 ? users_id.push(user_id) :false;
            elements.push(that);
         }
      });

      // found all user links (like in ticket form page)
      $("#page a[id^=tooltiplink]:not(.callto_link_added)").each(function() {
         var that    = $(this);
         var matches = that.attr('href').match(/user.form.php\?id=(\d+)/);
         if (matches !== null && matches.length > 1) {
            var user_id = matches[1];
            if (user_id > 0) {
               that.user_id = user_id;
               users_id.indexOf(user_id) === -1 ? users_id.push(user_id) :false;
               elements.push(that);
            }
         }
      });

      // deferred ajax calls to retrieve users informations (phone, title, etc)
      // and when done, append 'callto:' links
      $.when.apply($, my_xuc.getUsers(users_id)).then(function() {
         console.debug("users for this page retrieved:", users_cache);

         my_xuc.xivo_store.set('users_cache', users_cache);
         my_xuc.appendCalltoIcons(elements);

         // add phone numbers for phone hints subscribe
         Object.keys(users_cache).map(function(user_id) {
            if (users_cache[user_id].phone !== null
                && users_cache[user_id].phone !== ""
                && users_cache[user_id].phone !== undefined) {
               pageNumbers.push(users_cache[user_id].phone);
            }

            if (users_cache[user_id].phone2 !== null
                 && users_cache[user_id].phone2 !== ""
                 && users_cache[user_id].phone2 !== undefined) {
               pageNumbers.push(users_cache[user_id].phone2);
            }
         });
      });

      // event for callto icons
      var clicked = false;
      $(document)
         .on("click", "#page .xivo_callto_link", function() {
            // only fire dial event if not already fired a short time ago
            if (!clicked) {
               my_xuc.dial($(this).data('phone'));
               clicked = true;
            }

            // after a short time, indicate we can reclick on a callto link
            setTimeout(function() {
               clicked = false;
            }, 5000);
         });
   };

   /**
    * For each elements passed, add 'callto_link_added' cl and append 'callto:'' link after
    * @param  Array elements list of dom elements
    *                        (each should have a user_id key to match users_cache list)
    */
   my_xuc.appendCalltoIcons = function(elements) {
      $.each(elements, function(index, element) {
         var user_id = element.user_id;
         var data = users_cache[user_id];
         if ('phone' in data
             && data.phone != null) {

            var agentState = '';
            if (data.phone in agentsState) {
               agentState = agentsState[data.phone];
            }

            element
               .addClass("callto_link_added")
               .after("<span"
                  + " data-phone='" + data.phone + "'"
                  + " data-mobile='" + data.mobile + "'"
                  + " data-phone2='" + data.phone2 + "'"
                  + " class='xivo_callto_link " + agentState + "'"
                  + " title='" + data.title+ "'></a>");
         }
      });
   };

   /**
    * Restore last state of UI saved in local storage (after a redirection for example)
    */
   my_xuc.restoreLastState = function() {
      if (lastStateDate === null) {
         return false;
      }

      var now = new Date;
      if (Math.abs(now.getTime() - lastStateDate.getTime()) > (60 *  60 * 1000)) {
         return false;
      }
      switch (lastState) {
         case "EventRinging":
         case "EventEstablished":
            var event = {
               otherDN: callerNum,
               otherDName: callerName,
               eventType: lastState,
               lastStateDate: lastStateDate
            };
            my_xuc.phoneEvents(event);
            $("#xivo_agent_form").show();
            break;
      }
   }

   /**
    * Callback triggered when phone status changes
    * @param  Object event original CTI event
    */
   my_xuc.phoneEvents = function(event) {
      callerNum     = event.otherDN;
      callerName    = event.otherDName;
      lastState     = event.eventType;
      if (typeof event.lastStateDate == "undefined") {
         lastStateDate = new Date();
      }

      switch (event.eventType) {
         case "EventRinging":
            my_xuc.phoneRinging();
            break;
         case "EventReleased":
            my_xuc.commReleased();
            break;
         case "EventDialing":
            my_xuc.commDialing();
            break;
         case "EventEstablished":
            my_xuc.commEstablished();
            break;
      }

      my_xuc.saveXivoSession();
   };

   /**
    * Callback triggered when phone is ringing
    */
   my_xuc.phoneRinging = function() {
      my_xuc.showCallInformations("#xuc_ringing_title");
      my_xuc.enableTransferAction();
      my_xuc.getCallerInformations();
      $("#xivo_agent_button").addClass('ringing');
   };

   /**
    * Callback triggered when dialing number
    */
   my_xuc.commDialing = function() {
      my_xuc.showCallInformations("#xuc_dialing_title");
      $("#xuc_call_actions").hide();
   };

   /**
    * Callback triggered when a phone call etablished
    */
   my_xuc.commEstablished = function() {
      my_xuc.showCallInformations("#xuc_oncall_title");
      $("#xivo_agent_button").removeClass('ringing');
      my_xuc.enableTransferAction();
      my_xuc.getCallerInformations();

      if (my_xuc.xivo_config.enable_auto_open
          && do_auto_open
          && redirectTo !== false) {
         if (my_xuc.xivo_config.auto_open_blank) {
            var child = window.open(redirectTo, '_blank');

            // stop auto_open in current instance to avoid multiple window opening
            do_auto_open = false;

            // monitor child closing event to re-start auto-open if needed
            var child_timer = setInterval(function() {
               if (child.closed) {
                  clearInterval(child_timer);
                  do_auto_open = true;
               }
            }, 1000);
         } else {
            // only if window is visible (curent tab of browser)
            if (!document.hidden) {
               window.location = redirectTo;
            }
         }
      }
   };

   /**
    * Callback triggered when communication hanged up
    */
   my_xuc.commReleased = function() {
      $("#xivo_agent_form").hide();
      $("#xuc_hold").hide();
      $("#xuc_call_informations").hide();
      $("#xuc_call_actions .auto_actions").hide();
      $("#xivo_agent_button").removeClass('ringing');
      my_xuc.enableDialAction();
      callerNum = null;
      callerName = 'null';
      callerGlpiInfos = {};
      $("#xuc_caller_num").html('');
      $("#xuc_caller_numname").html('');
      $("#dial_phone_num").val('');
      $("#xuc_call_actions").show();
   };

   /**
    * Show in GLPI UI the caller informations and also phone controls
    * @param  String titleToShow title div selector to show
    */
   my_xuc.showCallInformations = function(titleToShow) {
      $("#xivo_agent_form").show();
      $("#xuc_call_titles div").hide();
      $(titleToShow).show();
      $("#auto_actions").show();
      $("#xuc_call_actions").show();
      my_xuc.displayCallerInformations();
   };

   /**
    * Display transfer control (also hide dial control)
    */
   my_xuc.enableTransferAction = function() {
      $("#dial_phone_num").hide();
      $("#xuc_dial").hide();
      $("#transfer_phone_num").show();
      $("#xuc_transfer").show();
   };

   /**
    * Display dial control (also hide transfer control)
    */
   my_xuc.enableDialAction = function() {
      $("#dial_phone_num").show();
      $("#xuc_dial").show();
      $("#transfer_phone_num").hide();
      $("#xuc_transfer").hide();
   };

   /**
    * triggers ajax query to retrieve caller informations by its phone number
    */
   my_xuc.getCallerInformations = function() {
      $.ajax({
         url: plugin_ajax_url,
         method: "POST",
         dataType: 'json',
         data: {
            'action': 'get_user_infos_by_phone',
            'caller_num': callerNum
         }
      })
      .done(function(data) {
         callerGlpiInfos = data;
         my_xuc.saveXivoSession();
         my_xuc.displayCallerInformations();

         if (data.redirect !== false) {
            redirectTo = data.redirect
         }

         my_xuc.saveXivoSession();
      });
   };

   /**
    * display caller informations in GLPI UI
    */
   my_xuc.displayCallerInformations = function() {
      $("#xuc_call_informations").show();
      $("#xuc_caller_num").html(callerNum);

      // display caller information (from glpi ajax request)
      var html = ''
      if (typeof callerGlpiInfos == "object"
          && 'users' in callerGlpiInfos
          && callerGlpiInfos.users.length == 1) {
         var user = callerGlpiInfos.users[0];
         html = user.link;
      }

      $('#xuc_caller_infos').html(html);
   };

   /**
    * Hangup the current call on CTI
    */
   my_xuc.hangup = function() {
      Cti.hangup();

      // if gui still in call mode, reset stuff after a while
      setTimeout(function() {
         if ($("#xuc_hangup").length) {
            my_xuc.commReleased();
            lastState     = null;
            lastStateDate = null;
            my_xuc.saveXivoSession();
         }
      }, 250);
   };

   /**
    * Answer the current call on CTI
    * Warning: the function doesn't seem to work at the moment.
    */
   my_xuc.answer = function() {
      Cti.answer();
   };

   /**
    * Hold the current call on CTI
    * Warning: the function doesn't seem to work at the moment.
    */
   my_xuc.hold = function() {
      xc_webrtc.answer();
   };

   /**
    * Launch on CTI a call with target_num parameter
    * @param  String target_num the to call (if not set, we will get the val of #dial_phone_num)
    */
   my_xuc.dial = function(target_num) {
      target_num = typeof target_num !== 'undefined'
                     ? target_num
                     : $("#dial_phone_num").val();
      var variables = {};
      Cti.dial(String(target_num), variables);
   };

   /**
    * Launch on CTI a transfer with target_num parameter
    * @param  String target_num the to call (if not set, we will get the val of #transfer_phone_num)
    */
   my_xuc.transfer = function(target_num) {
      target_num = typeof target_num !== 'undefined'
                     ? target_num
                     : $("#transfer_phone_num").val();
      var variables = {};
      Cti.directTransfer(String(target_num), variables);
   };

   /**
    * For all user's id passed, retrieve the user information by calling ajax requests
    * @param  Array users_id list of integer user's id
    * @return Array deferreds ajax request
    */
   my_xuc.getUsers = function(users_id) {
      var deferreds = [];

      $.each(users_id, function(index, user_id) {
         if (user_id in users_cache) {
            return true;
         }
         deferreds.push($.ajax({
            'url': plugin_ajax_url,
            'data': {
               'id': user_id,
               'action': 'get_call_link'
            },
            'dataType': 'json',
            'success': function(data) {
               users_cache[user_id] = data;
            }
         }));
      });

      return deferreds;
   };
};
