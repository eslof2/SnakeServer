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
        $server = new Server("127.0.0.1", 8000);
        $server->set([
    		'ssl_cert_file' => __DIR__ . '/localhost+2.pem',
    		'ssl_key_file' => __DIR__ . '/localhost+2-key.pem'
		]);
        $server->on('Start', $this->onStart(...));
        $server->on('Open', $this->onOpen(...));
        $server->on('Message', $this->onMessage(...));
        $server->on('Close', $this->onClose(...));
        $process = $gameProcess->getNewProcess($game, $server);
        $server->addProcess($process);
        $server->start();
    }

    protected function onStart(Server $server): void {
        echo "Server has begun on host ".getHostByName(getHostName()).PHP_EOL;
    }

    protected function onOpen(Server $server, Request $request): void {
    	echo "on open".PHP_EOL;
        $server->send($request->fd, json_encode(["fd" => $request->fd]));
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
