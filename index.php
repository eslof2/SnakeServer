<?php declare(strict_types=1);
const VENDOR_AUTOLOAD_PATH = __DIR__.'/vendor/autoload.php';
if (!file_exists(VENDOR_AUTOLOAD_PATH)) throw new Error("Can't find vendor/autoload.php, run composer install?");
require_once VENDOR_AUTOLOAD_PATH;

use Concrete\NormalMode\NormalGame;
use Server\Config;
use Server\GameProcess;
use Server\GameServer;

$game = new NormalGame(30, 30, Config::CONCURRENT_MAX);

$process = new GameProcess();
$server = new GameServer();
$server->start($process, $game);