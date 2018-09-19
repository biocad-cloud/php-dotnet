<?php

#  +--------+   1. Send Sec-WebSocket-Key                 +--------+
#  |        | ------------------------------------------> |        |
#  |        |   2. Return encrypted Sec-WebSocket-Accept  |        |
#  | client | <------------------------------------------ | server |
#  |        |   3. Verify locally                         |        |
#  |        | ------------------------------------------> |        |
#  +--------+                                             +--------+

# GET /chat HTTP/1.1
# Host: server.example.com
# Upgrade: websocket
# Connection: Upgrade
# Sec-WebSocket-Key: dGhlIHNhbXBsZSBub25jZQ==
# Origin: http://example.com
# Sec-WebSocket-Protocol: chat, superchat
# Sec-WebSocket-Version: 13