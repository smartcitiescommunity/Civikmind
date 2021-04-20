var Callback = {


    sendCallback : function() {
        console.error("Cti Not initialzed: User not logged on");
    },
    init: function(cti) {
        this.MessageFactory.init(cti.WebsocketMessageFactory);
        this.sendCallback = cti.sendCallback;
    },
    getCallbackLists: function() {
        var message = this.MessageFactory.createGetCallbackLists();
        this.sendCallback(message);
    },
    findCallbackRequest: function(requestId, filters, offset, limit) {
        var message = this.MessageFactory.createFindCallbackRequest(requestId, filters, offset, limit);
        this.sendCallback(message);
    },
    getCallbackPreferredPeriods: function() {
        var message = this.MessageFactory.createGetCallbackPreferredPeriods();
        this.sendCallback(message);
    },
    takeCallback: function(uuid) {
        var message = this.MessageFactory.createTakeCallback(uuid);
        this.sendCallback(message);
    },
    releaseCallback: function(uuid) {
        var message = this.MessageFactory.createReleaseCallback(uuid);
        this.sendCallback(message);
    },
    startCallback: function(uuid, number) {
        var message = this.MessageFactory.createStartCallback(uuid, number);
        this.sendCallback(message);
    },
    listenCallbackMessage: function(voiceMessageRef) {
        var message = this.MessageFactory.listenCallbackMessage(voiceMessageRef);
        this.sendCallback(message);
    },
    updateCallbackTicket: function(uuid, status, comment, dueDate, periodUuid) {
        var message = this.MessageFactory.createUpdateCallbackTicket(uuid, status, comment, dueDate, periodUuid);
        this.sendCallback(message);
    },

    MessageType: {
        CALLBACKLISTS: 'CallbackLists',
        CALLBACKFINDRESPONSE: 'FindCallbackResponseWithId',
        CALLBACKTAKEN: 'CallbackTaken',
        CALLBACKRELEASED: 'CallbackReleased',
        CALLBACKSTARTED: 'CallbackStarted',
        CALLBACKCLOTURED: 'CallbackClotured',
        CALLBACKPREFERREDPERIODS: 'PreferredCallbackPeriodList',
        CALLBACKREQUESTUPDATED: 'CallbackRequestUpdated'
    },

    MessageFactory: {
        ctiMessageFactory : {
            createMessage : function() {
                console.error("Cti Not initialzed: User not logged on");
            }
        },
        init: function(ctiMessageFactory) {
            this.ctiMessageFactory = ctiMessageFactory;
        },
        createMessage: function(command) {
            return this.ctiMessageFactory.createMessage(command);
        },
        createGetCallbackLists: function() {
            return this.createMessage("getCallbackLists");
        },
        createFindCallbackRequest: function(requestId, filters, offset, limit) {
            var message = this.createMessage("findCallbackRequest");
            message.id = requestId;
            message.request = { filters: filters, offset: offset, limit: limit };
            return message;
        },
        createGetCallbackPreferredPeriods: function() {
            return this.createMessage("getCallbackPreferredPeriods");
        },
        createTakeCallback: function(uuid) {
            var message = this.createMessage("takeCallback");
            message.uuid = uuid;
            return message;
        },
        createReleaseCallback: function(uuid) {
            var message = this.createMessage("releaseCallback");
            message.uuid = uuid;
            return message;
        },
        createStartCallback: function(uuid, number) {
            var message = this.createMessage("startCallback");
            message.uuid = uuid;
            message.number = number;
            return message;
        },
        listenCallbackMessage: function(voiceMessageRef) {
            var message = this.createMessage("listenCallbackMessage");
            message.voiceMessageRef = voiceMessageRef;
            return message;
         },
        createUpdateCallbackTicket: function(uuid, status, comment, dueDate, periodUuid) {
            var message = this.createMessage("updateCallbackTicket");
            message.uuid = uuid;
            message.status = status;
            message.comment = comment;
            if(typeof(dueDate) !== "undefined" && typeof(periodUuid) !== "undefined") {
                message.dueDate = dueDate;
                message.periodUuid = periodUuid;
            }
            return message;
        }
    }
};