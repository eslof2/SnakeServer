<?php declare(strict_types=1);
const VENDOR_AUTOLOAD_PATH = __DIR__.'/vendor/autoload.php';
if (!file_exists(VENDOR_AUTOLOAD_PATH)) throw new Error("Can't find vendor/autoload.php, run composer install?");
require_once VENDOR_AUTOLOAD_PATH;

use Concrete\NormalMode\NormalBoard;
use Concrete\NormalMode\NormalGame;
use Concrete\NormalMode\NormalPlayer;
use Concrete\NormalMode\NormalSlot;
use Concrete\Factory\SlotFactory;
use Concrete\Factory\PlayerFactory;
use Concrete\View\DeltaView;
use Server\Config;
use Server\GameProcess;
use Server\GameServer;
use Swoole\WebSocket\Server;

$board = new NormalBoard(30, 30, new SlotFactory(NormalSlot::class));
$game = new NormalGame($board, new DeltaView(), new PlayerFactory(NormalPlayer::class), Config::CONCURRENT_MAX);

$process = new GameProcess();
$server = new GameServer();

$webSocket = new Server("localhost", 80); //, int $mode = SWOOLE_PROCESS, int $sockType = SWOOLE_SOCK_TCP | SWOOLE_SSL);
//$server->set([
//    'ssl_cert_file' => __DIR__ . '/config/example.com+5.pem',
//    'ssl_key_file' => __DIR__ . '/config/example.com+5-key.pem'
//]);

$server->start($webSocket, $process, $game);