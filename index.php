<?php declare(strict_types=1);
require_once __DIR__.'/vendor/autoload.php';

use Concrete\NormalMode\NormalGame;
use Server\Config;
use Server\GameProcess;
use Server\GameServer;

$game = new NormalGame(30, 30, Config::CONCURRENT_MAX);

$process = new GameProcess();
$server = new GameServer();
$server->start($process, $game);