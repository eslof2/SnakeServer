<?php declare(strict_types=1);

namespace Model\Game;
use Concrete\Engine\Time;
use Model\Board\IBoard;
use Model\GameState;
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
    protected ?PDO $database;
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
        protected int $tickRateNs = 1000000000,
        protected float $timeScale = 1
    ) {
        $this->board->setGame($this);
    }

    public function setUp(Server $server, ?PDO $database, Table $playerTable, Table $inputTable, Atomic $atomicState): void {
        $this->server = $server;
        $this->database = $database;
        $this->playerTable = $playerTable;
        $this->inputTable = $inputTable;
        $this->atomicState = $atomicState;
    }

    public function getBoard(): IBoard { return $this->board; }
    public function getBoardSlots(): array { return $this->board->getSlots(); }
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
            $this->atomicState->set(GameState::WAITING->value);
            $this->atomicState->wait(0); //waiting indefinitely for new connections
            $this->broadcastState();
            return;
        }

        //mostly for stopping memory leak in lastTickByFd
        foreach ($this->lastTickByFd as $fd => $tick) {
            if (!$this->server->exist($fd)) $this->onDisconnect($fd);
        }

        /** @var array<string, IPlayer> */
        $newPlayers = [];
        foreach ($this->playerTable as $fdStr => $row) {
            $fd = intval($fdStr);
            if (!($player = $this->tryGetPlayer($fd))) {
                $player = $newPlayers[$fdStr] = $this->getNewPlayer($fd, $row[Config::NAME_COL]);
            }

            $player->setInput(Input::from($this->inputTable->get($fdStr, Config::INPUT_COL)));
        }

        foreach ($this->players as $p) $p->processMovement();
        foreach ($this->players as $p) $p->processCollision();

        foreach ($newPlayers as $fdStr => $noob) {
            if ($this->tryAddPlayer($noob)) continue;
            $this->playerTable->del($fdStr);
            $this->inputTable->del($fdStr);
        }

        $this->broadcastState();

        foreach ($this->players as $fd => $player) {
            if (!$player->isDead()) continue;
            unset($this->players[$fd]);
            $this->inputTable->del(strval($fd));
            $this->playerTable->del(strval($fd));
        }
    }

    protected function broadcastState(): void {
        foreach ($this->server->connections as $fd) $this->broadcastStateFor($fd);
    }
    protected function broadcastStateFor(int $fd): void {
        if (!isset($fd, $this->lastTickByFd)) $this->lastTickByFd[$fd] = $this->time->tickCount - 2;
        if (!($data = $this->view->serialize($this, $this->time->tickCount, $this->lastTickByFd[$fd]))) return;
        $this->server->push($fd, $data);
        $this->lastTickByFd[$fd] = $this->time->tickCount;
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
        $player->setBoard($this->board);
        if (!$player->tryInstantiate()) return false;
        $player->setDeathCallback($this->onPlayerDeath(...));
        $this->players[$fd] = $player;
        return true;
    }

    public function onDisconnect(int $fd): void {
        unset($this->lastTickByFd[$fd]);
    }

    public final function start(Time $time): void {
        $this->time = $time;
        $this->onStart();
    }

    public abstract function onStart(): void;

    protected function onPlayerDeath(IPlayer $player): void {
        //$sql = "INSERT INTO score (name, score) VALUES (:name, :score)";
        //$prep = $this->database->prepare($sql);
        $name = $player->getName();
        $score = $player->getScore();
        echo $name." ".$score;
        //$prep->bindParam("name", $name, PDO::PARAM_STR, Config::NAME_VARCHAR_MAX);
        //$prep->bindParam("score", $score, PDO::PARAM_INT); // TODO: hard coded
        //$prep->execute();
    }
}
