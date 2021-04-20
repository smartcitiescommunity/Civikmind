# CURL command for using XIVO Api

Theses commands are extracted from https://documentation.xivo.solutions/en/stable/xivo/api_sdk/rest_api/rest_api.html

## Auth (get token)

Create a new token, using the xivo_user backend, expiring in 10 minutes

curl -k -X POST \
-H 'Content-Type: application/json' \
-u 'user:password' \
-d '{"backend": "xivo_service", "expiration": 600}' \
"https://ip_api:9497/0.1/token"

>{"data": {"xivo_user_uuid": "49f4f44f-ce1f-41d4-b44d-09f4822f92d0", "expires_at": "2017-03-27T09:49:42.366064", "token": "4ccf942b-7371-4f19-bd04-0ac4fc4f70f5", "acls": ["confd.users.me.read", "confd.users.me.update", "confd.users.me.funckeys.*", "confd.users.me.funckeys.*.*", "confd.users.me.#.read", "confd.users.me.services.*.*", "confd.users.me.forwards.*.*", "ctid-ng.users.me.#", "ctid-ng.transfers.*.read", "ctid-ng.transfers.*.delete", "ctid-ng.transfers.*.complete.update", "dird.#.me.read", "dird.directories.favorites.#", "dird.directories.lookup.*.headers.read", "dird.directories.lookup.*.read", "dird.directories.personal.*.read", "dird.personal.#", "events.calls.me", "events.transfers.me", "events.chat.message.*.me", "events.statuses.*", "events.switchboards", "events.config.users.me.services.*.*", "events.config.users.me.forwards.*.*", "websocketd"], "issued_at": "2017-03-27T09:39:42.366094", "auth_id": "49f4f44f-ce1f-41d4-b44d-09f4822f92d0"}}

## Get list of devices

curl -X GET \
--header 'Accept: application/json' \
--header 'X-Auth-Token: mytoken_givenby_auth'
'https://ip_api:9486/1.1/devices'