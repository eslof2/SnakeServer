# SnakeServer
PHP/Swoole multiplayer Snake game server.

Requires PHP 8.1 <br />
Requires Swoole: https://openswoole.com/docs/get-started/installation <br />
In the composer: ext-mbstring and ext-pdo required, and swoole-ide-helper not required but helps for autocompletion et.c. <br />


Concrete contains implemented classes. <br />
Model contains abstract/interface/traits/enums. <br />
Server sets up the WebSocket server, starts the game process, and manages multiprocess/thread communication. <br />
