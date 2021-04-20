var Membership = {


    sendCallback : function() {
        console.error("Cti Not initialized: User not logged on");
    },
    init: function(cti) {
        this.MessageFactory.init(cti.WebsocketMessageFactory);
        this.sendCallback = cti.sendCallback;
    },
    getUserDefaultMembership: function(userId) {
        var message = this.MessageFactory.createGetUserDefaultMembership(userId);
        this.sendCallback(message);
    },
    setUserDefaultMembership: function(userId, membership) {
        var message = this.MessageFactory.createSetUserDefaultMembership(userId, membership);
        this.sendCallback(message);
    },
    setUsersDefaultMembership: function(userIds, membership) {
        var message = this.MessageFactory.createSetUsersDefaultMembership(userIds, membership);
        this.sendCallback(message);
    },
    applyUsersDefaultMembership: function(userIds) {
        var message = this.MessageFactory.createApplyUsersDefaultMembership(userIds);
        this.sendCallback(message);
    },

    MessageType: {
        USERQUEUEDEFAULTMEMBERSHIP: 'UserQueueDefaultMembership',
        USERSQUEUEDEFAULTMEMBERSHIP: 'UsersQueueDefaultMembership'
    },

    MessageFactory: {
        ctiMessageFactory: {
            createMessage: function () {
                console.error("Cti Not initialzed: User not logged on");
            }
        },
        init: function (ctiMessageFactory) {
            this.ctiMessageFactory = ctiMessageFactory;
        },
        createMessage: function (command) {
            return this.ctiMessageFactory.createMessage(command);
        },
        createGetUserDefaultMembership: function (userId) {
            var message = this.createMessage("getUserDefaultMembership");
            message.userId = userId;
            return message;
        },
        createSetUserDefaultMembership: function (userId, membership) {
            var message = this.createMessage("setUserDefaultMembership");
            message.userId = userId;
            message.membership = membership;
            return message;
        },
        createSetUsersDefaultMembership: function (userIds, membership) {
            var message = this.createMessage("setUsersDefaultMembership");
            message.userIds = userIds;
            message.membership = membership;
            return message;
        },
        createApplyUsersDefaultMembership: function(userIds) {
            var message = this.createMessage("applyUsersDefaultMembership");
            message.userIds = userIds;
            return message;
        }
    }
};
