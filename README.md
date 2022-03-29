# SnakeServer
PHP/Swoole multiplayer Snake game server.

Lubuntu 21.10 (just the image I had on disk) <br />
apt update apt upgrade reboot <br />
 <br />
sudo apt install lsb-release ca-certificates apt-transport-https software-properties-common -y <br />
sudo add-apt-repository ppa:ondrej/php && sudo add-apt-repository ppa:openswoole/ppa <br />
apt update apt upgrade reboot <br />
 <br />
sudo apt install php8.1 php8.1-mbstring php8.1-common php8.1-sqlite3 php8.1-openswoole composer <br />
git clone https://github.com/eslof/SnakeServer <br />
cd SnakeServer <br />
sudo php index.php


Concrete contains implemented classes. <br />
Model contains abstract/interface/traits/enums. <br />
Server sets up the WebSocket server, starts the game process, and manages multiprocess/thread communication. <br />
