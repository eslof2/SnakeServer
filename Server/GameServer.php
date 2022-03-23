<?php

namespace Server;
use Model\Game\IGame;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class GameServer {
    protected GameProcess $gameProcess;
    
    public function start(GameProcess $gameProcess, IGame $game): void {
        $this->gameProcess = $gameProcess;
        $server = new Server("0.0.0.0", 9502);
        $server->on('Start', $this->onStart(...));
        $server->on('Open', $this->onOpen(...));
        $server->on('Message', $this->onMessage(...));
        $server->on('Close', $this->onClose(...));
        $process = $gameProcess->getNewProcess($game, $server);
        $server->addProcess($process);
        $server->start();
    }

    protected function onStart(Server $server): void {
        echo "Server has begun and what not".PHP_EOL;
    }

    protected function onOpen(Server $server, Request $request): void {
        $this->gameProcess->wakeUp();
    }

    protected function onMessage(Server $server, Frame $frame): void {
        $fd = $frame->fd;
        if (!($data = json_decode($frame->data))) return;
        if (!isset($data->request) || !is_int($data->request) || !($req = PlayerRequest::tryFrom($data->request))) return;
        $success = match ($req) {
            PlayerRequest::JOIN => $this->gameProcess->tryJoin($fd, $data),
            PlayerRequest::INPUT => $this->gameProcess->tryInput($fd, $data)
        };
    }

    protected function onClose(Server $server, int $fd): void {}
}