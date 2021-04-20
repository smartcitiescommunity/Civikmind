var Cti = {
    debugMsg : false,
    debugHandler : false,
    sendCallback : function(message) {
      if (Cti.debugHandler){
        console.log('web socket not initialized to send' + JSON.stringify(message));
      }
    },
    init : function(username, agentNumber, webSocket) {
        this.username = username;
        this.agentNumber = agentNumber;
        this.sendCallback = webSocket.sendCallback;
        this.webSocket = webSocket;
    },

    close: function() {
        this.webSocket.close();
    },

    Topic : function(id) {
        if (typeof(jQuery) !== "undefined") {
            var callbacks, topic = id && Cti.ctiTopics[id];
            if (!topic) {
                callbacks = jQuery.Callbacks();
                topic = {
                    publish : callbacks.fire,
                    subscribe : callbacks.add,
                    unsubscribe : callbacks.remove,
                    clear : callbacks.empty
                };
                if (id) {
                    Cti.ctiTopics[id] = topic;
                }
            }
            return topic;
        }
        else if (typeof(SHOTGUN) !== "undefined") {
            return {
                clear : function(){
                    try{
                        SHOTGUN.remove('cti');
                    }catch(e){
                        console.error("Unable to clear cti handlers, ", e);
                    }
                },
                publish : function(val){
                    SHOTGUN.fire('cti/'+id,[val]);
                },
                subscribe : function(handler){
                    SHOTGUN.listen('cti/'+id,handler);
                },
                unsubscribe : function(handler){
                    console.error("Unsubscribe not implemented using shotgun");
                }
            };
        } else {
            console.error("No callback handler available ! Neither jQuery nor SHOTGUN is available.");
        }
    },

    setHandler : function(eventName, handler) {
        Cti.Topic(eventName).unsubscribe(handler);
        Cti.Topic(eventName).subscribe(handler);
        if (Cti.debugHandler) {
            console.log("subscribing : [" + eventName + "] to " + handler);
        }
    },

    unsetHandler : function(eventName, handler) {
        Cti.Topic(eventName).unsubscribe(handler);
        if (Cti.debugHandler) {
            console.log("unsubscribing : [" + eventName + "] to " + handler);
        }
    },

    clearHandlers : function() {
        Cti.Topic().clear();
    },

    receive : function(event) {
        var message = this.getMessage(event.data);
        if (message === null) {
            console.log("WARNING: No message in decoded json data: " + data);
            throw new TypeError("No message in decoded json data");
        }
        if (Cti.debugMsg){
            console.log("R<<< " + JSON.stringify(message));
        }
        try{
            Cti.Topic(message.msgType).publish(message.ctiMessage);
        }catch(e){
            console.error(message.msgType,message.ctiMessage,e);
        }
        Cti.msgReceived ++;
    },

    getMessage : function(jsonData) {
        try {
            return JSON.parse(jsonData);
        } catch (err) {
            console.log("ERROR: " + err + ", event.data not json encoded: " + jsonData);
            throw new TypeError("Json parse of received data failed");
        }
    },
    sendPing : function() {
        console.log("rec "+ Cti.msgReceived + " " + Cti.ctiChannelSocket);
        Cti.msgReceived = 0;
        var message = Cti.WebsocketMessageFactory.createPing();
        Cti.sendCallback(message);
    },
    changeUserStatus : function(statusId) {
        var message = Cti.WebsocketMessageFactory.createUserStatusUpdate(statusId);
        this.sendCallback(message);
    },
    loginAgent : function(agentPhoneNumber, agentId) {
        var message = Cti.WebsocketMessageFactory.createAgentLogin(agentPhoneNumber, agentId);
        this.sendCallback(message);
    },
    logoutAgent : function(agentId) {
        var message = Cti.WebsocketMessageFactory.createAgentLogout(agentId);
        this.sendCallback(message);
    },
    pauseAgent : function(agentId, reason) {
        var message = Cti.WebsocketMessageFactory.createPauseAgent(agentId, reason);
        this.sendCallback(message);
    },
    unpauseAgent : function(agentId) {
        var message = Cti.WebsocketMessageFactory.createUnpauseAgent(agentId);
        this.sendCallback(message);
    },
    listenAgent : function(agentId) {
        var args ={'agentid' : agentId};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.listenAgentCmd, args);
        this.sendCallback(message);
    },
    dnd : function(state) {
        var message = Cti.WebsocketMessageFactory.createDnd(state);
        this.sendCallback(message);
    },
    dial : function(destination, variables) {
        var message = Cti.WebsocketMessageFactory.createDial(destination, variables);
        this.sendCallback(message);
    },
    dialFromMobile : function(destination, variables) {
        var message = Cti.WebsocketMessageFactory.createDialMobile(destination, variables);
        this.sendCallback(message);
    },
    dialFromQueue : function(destination, queueId, callerIdName, callerIdNumber, variables) {
        var message = Cti.WebsocketMessageFactory.createDialFromQueue(destination, queueId, callerIdName, callerIdNumber, variables);
        this.sendCallback(message);
    },
    originate : function(destination) {
        var message = Cti.WebsocketMessageFactory.createOriginate(destination);
        this.sendCallback(message);
    },
    hangup : function() {
        var message = Cti.WebsocketMessageFactory.createHangup();
        this.sendCallback(message);
    },
    answer : function() {
        var message = Cti.WebsocketMessageFactory.createAnswer();
        this.sendCallback(message);
    },
    hold : function() {
        var message = Cti.WebsocketMessageFactory.createHold();
        this.sendCallback(message);
    },
    directTransfer : function(destination) {
        var message = Cti.WebsocketMessageFactory.createDirectTransfer(destination);
        this.sendCallback(message);
    },
    attendedTransfer : function(destination) {
        var message = Cti.WebsocketMessageFactory.createAttendedTransfer(destination);
        this.sendCallback(message);
    },
    completeTransfer : function() {
        var message = Cti.WebsocketMessageFactory.createCompleteTransfer();
        this.sendCallback(message);
    },
    cancelTransfer : function() {
        var message = Cti.WebsocketMessageFactory.createCancelTransfer();
        this.sendCallback(message);
    },
    conference : function() {
        var message = Cti.WebsocketMessageFactory.createConference();
        this.sendCallback(message);
    },
    conferenceMute : function(numConf, index) {
      var message = Cti.WebsocketMessageFactory.createConferenceMute(numConf, index);
      this.sendCallback(message);
    },
    conferenceUnmute : function(numConf, index) {
      var message = Cti.WebsocketMessageFactory.createConferenceUnmute(numConf, index);
      this.sendCallback(message);
    },
    conferenceMuteAll : function(numConf) {
        var message = Cti.WebsocketMessageFactory.createConferenceMuteAll(numConf);
        this.sendCallback(message);
    },
    conferenceUnmuteAll : function(numConf) {
        var message = Cti.WebsocketMessageFactory.createConferenceUnmuteAll(numConf);
        this.sendCallback(message);
    },
    conferenceMuteMe : function(numConf) {
        var message = Cti.WebsocketMessageFactory.createConferenceMuteMe(numConf);
        this.sendCallback(message);
    },
    conferenceUnmuteMe : function(numConf) {
        var message = Cti.WebsocketMessageFactory.createConferenceUnmuteMe(numConf);
        this.sendCallback(message);
    },
    conferenceKick : function(numConf, index) {
      var message = Cti.WebsocketMessageFactory.createConferenceKick(numConf, index);
      this.sendCallback(message);
    },
    subscribeToQueueStats : function() {
        var message = Cti.WebsocketMessageFactory.createSubscribeToQueueStats();
        this.sendCallback(message);
    },
    subscribeToQueueCalls : function(queueId) {
        var message = Cti.WebsocketMessageFactory.createSubscribeToQueueCalls(queueId);
        this.sendCallback(message);
    },
    unSubscribeToQueueCalls : function(queueId) {
        var message = Cti.WebsocketMessageFactory.createUnSubscribeToQueueCalls(queueId);
        this.sendCallback(message);
    },
    subscribeToAgentStats : function() {
        var message = Cti.WebsocketMessageFactory.createSubscribeToAgentStats();
        this.sendCallback(message);
    },
    getQueueStatistics : function(queueId, window, xqos) {
        var message = Cti.WebsocketMessageFactory.createGetQueueStatistics(queueId, window, xqos);
        this.sendCallback(message);
    },
    subscribeToAgentEvents : function() {
        var message = Cti.WebsocketMessageFactory.createSubscribeToAgentEvents();
        this.sendCallback(message);
    },
    getAgentStates : function() {
        var message = Cti.WebsocketMessageFactory.createGetAgentStates();
        this.sendCallback(message);
    },
    getConfig : function(objectType) {
        var message = Cti.WebsocketMessageFactory.createGetConfig(objectType);
        this.sendCallback(message);
    },
    getList : function(objectType) {
        var message = Cti.WebsocketMessageFactory.createGetList(objectType);
        this.sendCallback(message);
    },
    getAgentDirectory : function() {
        var message = Cti.WebsocketMessageFactory.createGetAgentDirectory();
        this.sendCallback(message);
    },
    getConferenceRooms : function() {
        var message = Cti.WebsocketMessageFactory.createGetList("meetme");
        this.sendCallback(message);
    },
    setAgentQueue : function(agentId, queueId, penalty) {
        var message = Cti.WebsocketMessageFactory.createSetAgentQueue(agentId, queueId, penalty);
        this.sendCallback(message);
    },
    removeAgentFromQueue : function(agentId,queueId) {
        var message = Cti.WebsocketMessageFactory.createRemoveAgentFromQueue(agentId,queueId);
        this.sendCallback(message);
    },
    moveAgentsInGroup : function(groupId, fromQueueId, fromPenalty, toQueueId, toPenalty) {
        var args = {};
        args.groupId = groupId;
        args.fromQueueId = fromQueueId;
        args.fromPenalty = fromPenalty;
        args.toQueueId = toQueueId;
        args.toPenalty = toPenalty;

        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.moveAgentsInGroupCmd, args);
        this.sendCallback(message);

    },
    addAgentsInGroup : function(groupId, fromQueueId, fromPenalty, toQueueId, toPenalty) {
        var args = {};
        args.groupId = groupId;
        args.fromQueueId = fromQueueId;
        args.fromPenalty = fromPenalty;
        args.toQueueId = toQueueId;
        args.toPenalty = toPenalty;

        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.addAgentsInGroupCmd, args);
        this.sendCallback(message);
    },
    removeAgentGroupFromQueueGroup: function(groupId, queueId, penalty) {
        var args ={'groupId' : groupId, 'queueId' : queueId, 'penalty' : penalty};

        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.removeAgentGroupFromQueueGroupCmd, args);
        this.sendCallback(message);
    },
    addAgentsNotInQueueFromGroupTo: function(groupId, queueId, penalty) {
        var args ={'groupId' : groupId, 'queueId' : queueId, 'penalty' : penalty};

        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.addAgentsNotInQueueFromGroupToCmd, args);
        this.sendCallback(message);
    },
    monitorPause: function(agentId) {
        var args = {'agentid' : agentId};

        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.monitorPause, args);
        this.sendCallback(message);
    },
    monitorUnpause: function(agentId) {
        var args = {'agentid' : agentId};

        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.monitorUnpause, args);
        this.sendCallback(message);
    },
    inviteConferenceRoom: function(userId) {
        var message = Cti.WebsocketMessageFactory.createInviteConferenceRoom(userId);
        this.sendCallback(message);
    },
    naFwd: function(destination,state) {
        var args = {'state' : state, 'destination' : destination};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.naFwd, args);
        this.sendCallback(message);
    },
    uncFwd: function(destination,state) {
        var args = {'state' : state, 'destination' : destination};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.uncFwd, args);
        this.sendCallback(message);
    },
    busyFwd: function(destination,state) {
        var args = {'state' : state, 'destination' : destination};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.busyFwd, args);
        this.sendCallback(message);
    },
    getAgentCallHistory : function(size) {
        var args = {'size' : size};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.getAgentCallHistoryCmd, args);
        this.sendCallback(message);
    },
    findCustomerCallHistory : function(requestId, filters, size) {
        var args = {'id': requestId, 'request': {'filters': filters, 'size' : size}};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.findCustomerCallHistoryCmd, args);
        this.sendCallback(message);
    },
    getUserCallHistory: function(size) {
        var args = {'size' : size};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.getUserCallHistoryCmd, args);
        this.sendCallback(message);
    },
    getUserCallHistoryByDays: function(days) {
        var args = {'days' : days};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.getUserCallHistoryByDaysCmd, args);
        this.sendCallback(message);
    },
    getQueueCallHistory: function(queue, size) {
        var args = {'queue': queue, 'size' : size};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.getQueueCallHistoryCmd, args);
        this.sendCallback(message);
    },
    setAgentGroup: function(agentId, groupId) {
        var message = Cti.WebsocketMessageFactory.createSetAgentGroup(agentId, groupId);
        this.sendCallback(message);
    },
    directoryLookUp: function(term) {
        var message = Cti.WebsocketMessageFactory.createDirectoryLookUp(term);
        this.sendCallback(message);
    },
    getFavorites: function() {
        var message = Cti.WebsocketMessageFactory.createGetFavorites();
        this.sendCallback(message);
    },
    addFavorite: function(contactId, source) {
        var message = Cti.WebsocketMessageFactory.createAddFavorite(contactId, source);
        this.sendCallback(message);
    },
    removeFavorite: function(contactId, source) {
        var message = Cti.WebsocketMessageFactory.createRemoveFavorite(contactId, source);
        this.sendCallback(message);
    },
    setData: function(variables) {
        var args = {'variables' : variables};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.setDataCmd, args);
        this.sendCallback(message);
    },
    getCurrentCallsPhoneEvents: function() {
        var message = Cti.WebsocketMessageFactory.createGetCurrentCallsPhoneEvents();
        this.sendCallback(message);
    },
    sendDtmf: function(key) {
        var args = {'key' : key};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.sendDtmf, args);
        this.sendCallback(message);
    },
    sendFlashTextMessage: function(to, seq, message) {
        var m = Cti.WebsocketMessageFactory.createFlashTextMessage(to, seq, message);
        this.sendCallback(m);
    },
    subscribeToPhoneHints: function(phoneNumbers) {
        var args = {'phoneNumbers' : phoneNumbers};
        var message = Cti.WebsocketMessageFactory.createMessageFromArgs(Cti.WebsocketMessageFactory.subscribeToPhoneHints, args);
        this.sendCallback(message);
    },
    unsubscribeFromAllPhoneHints: function() {
        var message = Cti.WebsocketMessageFactory.createMessage(Cti.WebsocketMessageFactory.unsubscribeFromAllPhoneHints);
        this.sendCallback(message);
    },

};
Cti.ctiTopics = {};

Cti.MessageType = {
    ERROR : "Error",
    LOGGEDON : "LoggedOn",
    SHEET : "Sheet",
    USERSTATUSES : "UsersStatuses",
    USERSTATUSUPDATE : "UserStatusUpdate",
    USERCONFIGUPDATE : "UserConfigUpdate",
    DIRECTORYRESULT : "DirectoryResult",
    PHONESTATUSUPDATE : "PhoneStatusUpdate",
    VOICEMAILSTATUSUPDATE : "VoiceMailStatusUpdate",
    LINKSTATUSUPDATE : "LinkStatusUpdate",
    QUEUESTATISTICS : "QueueStatistics",
    QUEUECONFIG : "QueueConfig",
    QUEUELIST : "QueueList",
    QUEUEMEMBER : "QueueMember",
    QUEUEMEMBERLIST : "QueueMemberList",
    QUEUECALLS : "QueueCalls",
    GETQUEUESTATISTICS : "GetQueueStatistics",
    AGENTCONFIG : "AgentConfig",
    AGENTDIRECTORY : "AgentDirectory",
    AGENTERROR : "AgentError",
    AGENTLIST : "AgentList",
    AGENTLISTEN: "AgentListen",
    AGENTSTATISTICS: "AgentStatistics",
    AGENTGROUPLIST : "AgentGroupList",
    AGENTSTATEEVENT : "AgentStateEvent",
    CONFERENCES: "ConferenceList",
    CONFERENCEEVENT: "ConferenceEvent",
    CONFERENCEPARTICIPANTEVENT: "ConferenceParticipantEvent",
    CONFERENCECOMMANDERROR: "ConferenceCommandError",
    CALLHISTORY: "CallHistory",
    CALLHISTORYBYDAYS: "CallHistoryByDays",
    CUSTOMERCALLHISTORY: "CustomerCallHistoryResponseWithId",
    FAVORITES: "Favorites",
    FAVORITEUPDATED: "FavoriteUpdated",
    PHONEEVENT: "PhoneEvent",
    CURRENTCALLSPHONEEVENTS: "CurrentCallsPhoneEvents",
    PHONEHINTSTATUSEVENT: "PhoneHintStatusEvent",
    LINECONFIG: "LineConfig",
    AUTHENTICATIONTOKEN: "AuthenticationToken",
    RIGHTPROFILE: "RightProfile",
    FLASHTEXTEVENT: "FlashTextEvent",
    WEBRTCCMD: "WebRTCCmd"
};

Cti.PhoneStatus = {
    ONHOLD : 16,
    RINGING : 8,
    INDISPONIBLE : 4,
    BUSY_AND_RINGING : 9,
    AVAILABLE : 0,
    CALLING : 1,
    BUSY : 2,
    DEACTIVATED : -1,
    UNEXISTING : -2,
    ERROR : -99
};

Cti.PhoneStatusColors = {
    "16" : "#F7FE2E",
    "8" : "#2E2EFE",
    "4" : "#F2F2F2",
    "9" : "#CC2EFA",
    "0" : "#01DF01",
    "1" : "#FF8000",
    "2" : "#81BEF7",
    "-1" : "#F2F2F2",
    "-2" : "#F2F2F2",
    "-99" : "#F2F2F2"
};

Cti.WebsocketMessageFactory = {

    messageClaz : "web",
    pingClaz : "ping",
    agentLoginCmd : "agentLogin",
    agentLogoutCmd : "agentLogout",
    userStatusUpdateCmd : "userStatusUpdate",
    dndCmd : "dnd",
    dialCmd : "dial",
    dialFromMobileCmd : "dialFromMobile",
    dialFromQueueCmd : "dialFromQueue",
    originateCmd : "originate",
    pauseAgentCmd : "pauseAgent",
    unpauseAgentCmd : "unpauseAgent",
    listenAgentCmd : "listenAgent",
    subscribeToAgentStatsCmd : "subscribeToAgentStats",
    subscribeToQueueStatsCmd : "subscribeToQueueStats",
    subscribeToQueueCallsCmd : "subscribeToQueueCalls",
    unSubscribeToQueueCallsCmd : "unSubscribeToQueueCalls",
    hangupCmd : "hangup",
    answerCmd : "answer",
    holdCmd : "hold",
    directTransferCmd : "directTransfer",
    attendedTransferCmd : "attendedTransfer",
    completeTransferCmd : "completeTransfer",
    cancelTransferCmd : "cancelTransfer",
    conferenceCmd : "conference",
    conferenceMuteCmd: "conferenceMute",
    conferenceUnmuteCmd: "conferenceUnmute",
    conferenceMuteAllCmd: "conferenceMuteAll",
    conferenceUnmuteAllCmd: "conferenceUnmuteAll",
    conferenceMuteMeCmd: "conferenceMuteMe",
    conferenceUnmuteMeCmd: "conferenceUnmuteMe",
    conferenceKickCmd: "conferenceKick",
    getQueueStatisticsCmd : "getQueueStatistics",
    subscribeToAgentEventsCmd : "subscribeToAgentEvents",
    getAgentStatesCmd : "getAgentStates",
    getConfigCmd : "getConfig",
    getListCmd : "getList",
    getAgentDirectoryCmd : "getAgentDirectory",
    setAgentQueueCmd : "setAgentQueue",
    removeAgentFromQueueCmd : "removeAgentFromQueue",
    moveAgentsInGroupCmd : "moveAgentsInGroup",
    addAgentsInGroupCmd : "addAgentsInGroup",
    removeAgentGroupFromQueueGroupCmd : "removeAgentGroupFromQueueGroup",
    addAgentsNotInQueueFromGroupToCmd : "addAgentsNotInQueueFromGroupTo",
    monitorPause : "monitorPause",
    monitorUnpause : "monitorUnpause",
    inviteConferenceRoom: "inviteConferenceRoom",
    naFwd: "naFwd",
    uncFwd: "uncFwd",
    busyFwd: "busyFwd",
    getAgentCallHistoryCmd: "getAgentCallHistory",
    getUserCallHistoryCmd: "getUserCallHistory",
    getUserCallHistoryByDaysCmd: "getUserCallHistoryByDays",
    getQueueCallHistoryCmd: "getQueueCallHistory",
    findCustomerCallHistoryCmd: "findCustomerCallHistory",
    setAgentGroupCmd: "setAgentGroup",
    directoryLookUpCmd: "directoryLookUp",
    getFavoritesCmd: "getFavorites",
    addFavoriteCmd: "addFavorite",
    removeFavoriteCmd: "removeFavorite",
    setDataCmd: "setData",
    getCurrentCallsPhoneEvents: "getCurrentCallsPhoneEvents",
    flashTextBrowserRequest: "flashTextBrowserRequest",
    sendDtmf: "sendDtmf",
    subscribeToPhoneHints: "subscribeToPhoneHints",
    unsubscribeFromAllPhoneHints: "unsubscribeFromAllPhoneHints",


    createFlashTextRequest: function(requestType) {
      var m = this.createMessage(this.flashTextBrowserRequest);
      m.request = requestType;
      return m;
    },

    createFlashTextMessage: function(to, seq, message) {
      var m = this.createFlashTextRequest("FlashTextDirectMessage");
      m.sequence = seq;
      m.to = {"username": to};
      m.message = message;
      return m;
    },

    createAgentLogin : function(agentPhoneNumber, agentid) {
        var message = this.createMessage(this.agentLoginCmd);
        message.agentphonenumber = agentPhoneNumber;
        return this._createAgentMessage(message, agentid);
    },
    createAgentLogout : function(agentid) {
        return this._createAgentMessage(this.createMessage(this.agentLogoutCmd), agentid);
    },
    createPauseAgent : function(agentid, reason) {
        var message = this._createAgentMessage(this.createMessage(this.pauseAgentCmd), agentid);
        if ( typeof(reason) !== 'undefined') {
          message.reason = reason;
        }
        return message;
    },
    createUnpauseAgent : function(agentid) {
        return this._createAgentMessage(this.createMessage(this.unpauseAgentCmd), agentid);
    },
    _createAgentMessage : function(message, agentid) {
        message.agentid = agentid;
        return message;
    },
    createUserStatusUpdate : function(status) {
        var message = this.createMessage(this.userStatusUpdateCmd);
        message.status = status;
        return message;
    },
    createDnd : function(state) {
        var message = this.createMessage(this.dndCmd);
        message.state = state;
        return message;
    },
    createDial : function(destination, variables) {
        var msg = this.createDestinationMessage(this.dialCmd, destination);
        msg.variables = variables;
        return msg;
    },
    createDialMobile : function(destination, variables) {
        var msg = this.createDestinationMessage(this.dialFromMobileCmd, destination);
        msg.variables = variables;
        return msg;
    },
    createDialFromQueue : function(destination, queueId, callerIdName, callerIdNumber, variables) {
        var msg = this.createDestinationMessage(this.dialFromQueueCmd, destination);
        msg.queueId = parseInt(queueId);
        msg.callerIdName = callerIdName;
        msg.callerIdNumber = callerIdNumber;
        msg.variables = variables;
        return msg;
    },
    createOriginate : function(destination) {
        return this.createDestinationMessage(this.originateCmd, destination);
    },
    createHangup : function() {
        return this.createMessage(this.hangupCmd);
    },
    createAnswer : function() {
        return this.createMessage(this.answerCmd);
    },
    createHold : function() {
        return this.createMessage(this.holdCmd);
    },
    createDirectTransfer : function(destination) {
        return this.createDestinationMessage(this.directTransferCmd, destination);
    },
    createAttendedTransfer : function(destination) {
        return this.createDestinationMessage(this.attendedTransferCmd, destination);
    },
    createCompleteTransfer : function() {
        return this.createMessage(this.completeTransferCmd);
    },
    createCancelTransfer : function() {
        return this.createMessage(this.cancelTransferCmd);
    },
    createConference : function() {
        return this.createMessage(this.conferenceCmd);
    },
    createConferenceMute : function(numConf, index) {
      var message = this.createMessage(this.conferenceMuteCmd);
      message.numConf = numConf;
      message.index = index;
      return message;
    },
    createConferenceUnmute : function(numConf, index) {
      var message = this.createMessage(this.conferenceUnmuteCmd);
      message.numConf = numConf;
      message.index = index;
      return message;
    },
    createConferenceMuteAll : function(numConf) {
      var message = this.createMessage(this.conferenceMuteAllCmd);
      message.numConf = numConf;
      return message;
    },
    createConferenceUnmuteAll : function(numConf) {
      var message = this.createMessage(this.conferenceUnmuteAllCmd);
      message.numConf = numConf;
      return message;
    },
    createConferenceMuteMe : function(numConf) {
      var message = this.createMessage(this.conferenceMuteMeCmd);
      message.numConf = numConf;
      return message;
    },
    createConferenceUnmuteMe : function(numConf) {
      var message = this.createMessage(this.conferenceUnmuteMeCmd);
      message.numConf = numConf;
      return message;
    },
    createConferenceKick : function(numConf, index) {
      var message = this.createMessage(this.conferenceKickCmd);
      message.numConf = numConf;
      message.index = index;
      return message;
    },
    createSubscribeToAgentStats : function() {
        return this.createMessage(this.subscribeToAgentStatsCmd);
    },
    createSubscribeToQueueStats : function() {
        return this.createMessage(this.subscribeToQueueStatsCmd);
    },
    createSubscribeToQueueCalls: function(queueId) {
        var message = this.createMessage(this.subscribeToQueueCallsCmd);
        message.queueId = queueId;
        return message;
    },
    createUnSubscribeToQueueCalls: function(queueId) {
        var message = this.createMessage(this.unSubscribeToQueueCallsCmd);
        message.queueId = queueId;
        return message;
    },
    createGetQueueStatistics : function(queueId, window, xqos) {
        var message = this.createMessage(this.getQueueStatisticsCmd);
        message.queueId = queueId;
        message.window = window;
        message.xqos = xqos;
        return message;
    },
    createSubscribeToAgentEvents : function() {
        return this.createMessage(this.subscribeToAgentEventsCmd);
    },
    createGetAgentStates : function() {
        return this.createMessage(this.getAgentStatesCmd);
    },
    createGetConfig : function(objectType) {
        var msg = this.createMessage(this.getConfigCmd);
        msg.objectType = objectType;
        return msg;
    },
    createGetList : function(objectType) {
        var msg = this.createMessage(this.getListCmd);
        msg.objectType = objectType;
        return msg;
    },
    createSetAgentQueue : function(agentId, queueId, penalty) {
        var msg = this.createMessage(this.setAgentQueueCmd);
        msg.agentId = agentId;
        msg.queueId = queueId;
        msg.penalty = penalty;
        return msg;
    },
    createRemoveAgentFromQueue : function(agentId,queueId) {
        var msg = this.createMessage(this.removeAgentFromQueueCmd);
        msg.agentId = agentId;
        msg.queueId = queueId;
        return msg;
    },
    createGetAgentDirectory : function(objectType) {
        return this.createMessage(this.getAgentDirectoryCmd);
    },
    createPing : function() {
        var message = {};
        message.claz = this.pingClaz;
        return message;
    },
    createInviteConferenceRoom: function(userId) {
        return this.createMessageFromArgs(this.inviteConferenceRoom, {userId: userId});
    },
    createSetAgentGroup: function(agentId, groupId) {
        var msg = this.createMessage(this.setAgentGroupCmd);
        msg.agentId = agentId;
        msg.groupId = groupId;
        return msg;
    },
    createDirectoryLookUp: function(term) {
        var msg = this.createMessage(this.directoryLookUpCmd);
        msg.term = term;
        return msg;
    },
    createGetFavorites: function() {
        var msg = this.createMessage(this.getFavoritesCmd);
        return msg;
    },
    createAddFavorite: function(contactId, source) {
        var msg = this.createMessage(this.addFavoriteCmd);
        msg.contactId = contactId;
        msg.source = source;
        return msg;
    },
    createRemoveFavorite: function(contactId, source) {
        var msg = this.createMessage(this.removeFavoriteCmd);
        msg.contactId = contactId;
        msg.source = source;
        return msg;
    },
    createGetCurrentCallsPhoneEvents: function() {
        var msg = this.createMessage(this.getCurrentCallsPhoneEvents);
        return msg;
    },
    createDestinationMessage : function(command, destination) {
        var message = this.createMessage(command);
        message.destination = destination;
        return message;
    },
    createMessageFromArgs : function(command, args) {
        var msg = this.createMessage(command);
        for (var arg in args) {
            msg[arg] = args[arg];
        }
        return msg;
    },
    createMessage : function(command) {
        var message = {};
        message.claz = this.messageClaz;
        message.command = command;
        return message;
    }
};
Cti.WebSocket = (function() {
    var socketState = {};
    var missed_heartbeats = 0;
    var pingInterval = 5000;
    var heartbeat_interval = null;
    var heartbeat_msg = JSON.stringify(Cti.WebsocketMessageFactory.createPing());


    var setSocketHandlers = function(socket) {
        socket.onopen = function() {
            socketState.status = "opened";
            Cti.Topic(Cti.MessageType.LINKSTATUSUPDATE).publish(socketState);
            if (heartbeat_interval === null) {
                missed_heartbeats = 0;
                heartbeat_interval = setInterval(function() {
                    try {
                        missed_heartbeats++;
                        console.log("ms :" + missed_heartbeats);
                        if (missed_heartbeats >= 3)
                            throw new Error("Too many missed heartbeats.");
                        socket.send(heartbeat_msg);
                    } catch(e) {
                        clearInterval(heartbeat_interval);
                        heartbeat_interval = null;
                        console.warn("Closing connection. Reason: " + e.message);
                        socket.close();
                        socket.onclose();
                        socket.onclose = function() {};
                    }
                }, pingInterval);
            }
        };

        socket.onclose = function() {
            if (heartbeat_interval !== null) {
                clearInterval(heartbeat_interval);
                heartbeat_interval = null;
            }
            socketState.status = "closed";
            Cti.Topic(Cti.MessageType.LINKSTATUSUPDATE).publish(socketState);
        };

        socket.onerror = function(error) {
            console.warn('ERROR: Error detected: ' + JSON.stringify(error));
        };

        socket.onmessage = function(event) {
            missed_heartbeats = 0;
            Cti.receive(event);
        };

        socket.sendCallback = function(message) {
            var jsonMessage = JSON.stringify(message);
            if (Cti.debugMsg) {
                console.log("S>>> " + jsonMessage);
            }
            socket.send(jsonMessage);
        };
    };

    return {
        init : function(wsurl, username, phoneNumber, WSClass) {
            var WS = WSClass || (window.MozWebSocket ? MozWebSocket : WebSocket);
            var ctiChannelSocket = new WS(wsurl);
            setSocketHandlers(ctiChannelSocket);
            Cti.init(username, phoneNumber, ctiChannelSocket);
        }
    };
})();
