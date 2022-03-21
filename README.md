# SnakeServer
PHP/Swoole multiplayer Snake game server.

Requires PHP 8.1

Requires Swoole: https://openswoole.com/docs/get-started/installation

In the composer: swoole-ide-helper not required but helps for autocompletion et.c.


Concrete contains implemented classes.

Model contains abstract/interface/traits/enums.

Server sets up the WebSocket server, starts the game process, and manages multiprocess/thread communication.
