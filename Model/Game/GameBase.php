<?php declare(strict_types=1);

namespace Model\Game;
use Concrete\Engine\Time;
use Model\Board\IBoard;
use Model\GameState;
use Model\Log;
use Model\Player\Input;
use Model\Player\IPlayer;
use Model\View\IView;
use PDO;
use Server\Config;
use Swoole\Atomic;
use Swoole\Table;
use Swoole\WebSocket\Server;

abstract class GameBase implements IGame {
    protected Server $server;
    protected PDO $database;
    protected Table $playerTable;
    protected Table $inputTable;
    protected Atomic $atomicState;
    /** @var IPlayer[] $players TODO: set up "fd to id" and use SplFixedArray where id == index */
    protected array $players = [];
    protected IView $view;
    protected Time $time;
    /** @var array<int, int> $lastTickByFd */
    protected array $lastTickByFd = [];

    public function __construct(
        protected IBoard $board,
        protected int $maxPlayerCount,
        protected int $tickRateNs = 333333333,
        protected float $timeScale = 1
    ) { $this->board->setGame($this); }

    public function setUp(Server $server, PDO $database, Table $playerTable, Table $inputTable, Atomic $atomicState): void {
        $this->server = $server;
        $this->database = $database;
        $this->playerTable = $playerTable;
        $this->inputTable = $inputTable;
        $this->atomicState = $atomicState;
    }

    public function getBoard(): IBoard { return $this->board; }
    public function getPlayer(int $fd): IPlayer { return $this->players[$fd]; }
    /** @return IPlayer[] */
    public function getPlayers(): array { return $this->players; }
    public function setTimeScale(float $timeScale): void { $this->timeScale = $timeScale; }
    public function getTickRateNs(): int { return $this->tickRateNs; }
    public function getTimeScale(): float { return $this->timeScale; }
    public function process(): void {
        // even if there's no connections there can still be active snakes that will die and submit score
        if ($this->playerTable->count() == 0 && count($this->players) == 0) {
            // otherwise we wait for wakeup signals from websocket requests
            Log::Message("No active players, server put in sleep/wait mode.");
            $this->atomicState->set(GameState::WAITING->value);
            $this->atomicState->wait(0); //waiting indefinitely for new connections
            Log::Message("Server woken up from sleep/wait mode, sending state to whomever woke me up.");
            $this->broadcastState();
            return;
        }

        // mostly for stopping memory leak in lastTickByFd
        foreach ($this->lastTickByFd as $fd => $tick) {
            if (!$this->server->exist($fd)) $this->onDisconnect($fd);
        }

        /** @var array<string, IPlayer> */
        $newPlayers = [];
        foreach ($this->playerTable as $fdStr => $row) {
            $fd = intval($fdStr);
            if (!($player = $this->tryGetPlayer($fd))) {
<<<<<<< Updated upstream
                $player = $newPlayers[$fdStr] = $this->getNewPlayer($fd, $row[Config::NAME_COL]);
=======
                Log::Message("New player detected from join request for id: ".$fdStr);
                $player = $newPlayers[$fdStr] = $this->playerFactory->getNewInstance($fd, $row[Config::NAME_COL]);
>>>>>>> Stashed changes
            }

            $player->setInput(Input::from($this->inputTable->get($fdStr, Config::INPUT_COL)));
            $this->inputTable->set($fdStr, [Config::INPUT_COL => Input::NONE->value]);
        }

        foreach ($this->players as $p) $p->processMovement();
        foreach ($this->players as $p) $p->processCollision();

        foreach ($newPlayers as $fdStr => $noob) {
            if ($this->tryAddPlayer($noob)) continue;
            Log::Message("Failed to add new player, removing from ICP tables for id: ".$fdStr);
            $this->playerTable->del($fdStr);
            $this->inputTable->del($fdStr);
        }

        $this->broadcastState();

        foreach ($this->players as $fd => $player) {
            if (!$player->isDead()) continue;
            Log::Message("Removing dead player from array and IPC tables for id: ".$fd);
            unset($this->players[$fd]);
            $this->inputTable->del(strval($fd));
            $this->playerTable->del(strval($fd));
        }
    }

    protected function broadcastState(): void {
        foreach ($this->server->connections as $fd) $this->broadcastStateFor($fd);
    }

    protected function broadcastStateFor(int $fd): void {
        if (!array_key_exists($fd, $this->lastTickByFd)) {
            Log::Message("New connection, sending id to client and streaming data for id: ".$fd);
            $this->lastTickByFd[$fd] = $this->time->tickCount - 2;
            // TODO: this is a good place to send gridSize and stuff
            $this->server->push($fd, json_encode(['fd' => $fd]));
        }
        $data = $this->view->serialize($this, $this->time->tickCount, $this->lastTickByFd[$fd]);
        $this->lastTickByFd[$fd] = $this->time->tickCount;
        if ($data === null) return;
        $this->server->push($fd, $data);
    }

    public function onDisconnect(int $fd): void {
        Log::Message("Disconnect for id: ".$fd);
        unset($this->lastTickByFd[$fd]);
    }

    public function tryGetPlayer(int $fd): ?IPlayer {
        if (!array_key_exists($fd, $this->players)) return null;
        return $this->players[$fd];
    }

    //we let inherit class decide what class of player
    //really we should probably be using an abstracted factory pattern
    protected abstract function getNewPlayer(int $fd, string $name): IPlayer;
    public function tryAddPlayer(IPlayer $player): bool {
        $fd = $player->getFd();
        Log::Message("Attempting to add player to game with id: ".$fd);
        $player->setBoard($this->board);
        if (!$player->tryInstantiate()) return false;
        $player->setDeathCallback($this->onPlayerDeath(...));
        $this->players[$fd] = $player;
        Log::Message("Player added to game with id: ".$fd);
        return true;
    }

    public final function start(Time $time): void {
        Log::Message("Game has started.");
        $this->time = $time;
        $this->onStart();
    }

    public abstract function onStart(): void;
    protected function onPlayerDeath(IPlayer $player): void {
        $fd = $player->getFd();
        Log::Message("Player death, inserting name and score into db for id: ".$fd);
        $sql = "INSERT INTO score (name, score) VALUES (:name, :score)";
        $prep = $this->database->prepare($sql);
        $name = $player->getName();
        $score = $player->getScore();
        $prep->bindParam("name", $name, PDO::PARAM_STR, Config::NAME_VARCHAR_MAX);
        $prep->bindParam("score", $score, PDO::PARAM_INT); // TODO: hard coded
        $success = $prep->execute();
        Log::Message("Status for db insert for id: ".$fd.", was ".($success ? "" : "un")."successful.");
    }
}
