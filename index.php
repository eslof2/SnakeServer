<?php declare(strict_types=1);
const VENDOR_AUTOLOAD_PATH = __DIR__.'/vendor/autoload.php';
if (!file_exists(VENDOR_AUTOLOAD_PATH)) throw new Error("Can't find vendor/autoload.php, run composer install?");
require_once VENDOR_AUTOLOAD_PATH;

use Concrete\NormalMode\NormalGame;
use Server\Config;
use Server\GameProcess;
use Server\GameServer;
use Swoole\WebSocket\Server;

$game = new NormalGame(30, 30, Config::CONCURRENT_MAX);
$process = new GameProcess();
$server = new GameServer();

$webSocket = new Server("localhost", 80);
//$server->set([
//    'ssl_cert_file' => __DIR__ . '/config/example.com+5.pem',
//    'ssl_key_file' => __DIR__ . '/config/example.com+5-key.pem'
//]);

$server->start($webSocket, $process, $game);