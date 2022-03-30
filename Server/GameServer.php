<?php

namespace Server;
use Model\Game\IGame;
use Model\Log;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

class GameServer {
    protected GameProcess $gameProcess;

    public function start(Server $server, GameProcess $gameProcess, IGame $game): void {
        $this->gameProcess = $gameProcess;
        $server->on('Start', $this->onStart(...));
        $server->on('Open', $this->onOpen(...));
        $server->on('Message', $this->onMessage(...));
        $server->on('Close', $this->onClose(...));
        $process = $gameProcess->getNewProcess($game, $server);
        $server->addProcess($process);
        Log::Message("Starting server at: ".$server->host.":".$server->port);
        $server->start();
    }

    protected function onStart(Server $server): void {
        Log::Message("Server has started at: ".$server->host.":".$server->port);
    }

    protected function onOpen(Server $server, Request $request): void {
        Log::Message("Connection opened with id: ".$request->fd);
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
        if ($req == PlayerRequest::JOIN && !$success) {
            Log::Message("Join request was unsuccessful for id: ".$fd);
        }
    }

    protected function onClose(Server $server, int $fd): void {
        Log::Message("Connection closed with id: ".$fd);
    }
}
