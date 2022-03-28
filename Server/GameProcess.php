<?php declare(strict_types=1);

namespace Server;
use Concrete\Engine\Engine;
use Model\Game\IGame;
use Model\GameState;
use Model\Player\Input;
use PDO;
use PDOException;
use Swoole\Atomic;
use Swoole\Process;
use Swoole\Table;
use Swoole\WebSocket\Server;

class GameProcess {
    private Atomic $atomicState;
    private Table $inputTable;
    private Table $playerTable;

    public function __construct() {
        // These are objects for multi-threading/processing ICP:
        // Table is a shared memory db.
        // Atomic is a shared memory lock/counter/state machine.
        $playerTable = $this->playerTable = new Table(Config::CONCURRENT_MAX);
        $playerTable->column(Config::NAME_COL, Table::TYPE_STRING, Config::NAME_VARCHAR_MAX);
        $playerTable->create();
        $inputTable = $this->inputTable = new Table(Config::CONCURRENT_MAX);
        $inputTable->column(Config::INPUT_COL, Table::TYPE_INT, 4);       //1,2,4,8
        $inputTable->create();
        $this->atomicState = new Atomic(GameState::SHUTDOWN->value);
    }

    public function tryJoin(int $fd, mixed $data): bool {
        echo "attempting to join".PHP_EOL;
        $playerTable = $this->playerTable;
        $fdStr = strval($fd);
        if (!isset($data->name) || !is_string($data->name) || trim($data->name) == '' || mb_strlen($data->name, "UTF-8") > Config::NAME_VARCHAR_MAX) return false;
        if ($playerTable->exist($fdStr) || $playerTable->count() >= Config::CONCURRENT_MAX) return false;
        $this->inputTable->set($fdStr, [Config::INPUT_COL => Input::NONE->value]);
        $playerTable->set($fdStr, [Config::NAME_COL => $data->name]);
        $this->wakeUp();
        return true;
    }

    public function wakeUp(): void {
        if ($this->atomicState->get() == GameState::WAITING->value) $this->atomicState->wakeup();
    }

    public function tryInput(int $fd, mixed $data): bool {
        $fdStr = strval($fd);
        if (!isset($data->input) || !is_int($data->input) || !($input = Input::tryFrom($data->input))) return false;
        if (!$this->playerTable->exist($fdStr)) return false;
        $this->inputTable->set($fdStr, [Config::INPUT_COL => $input->value]);
        return true;
    }

    public function getNewProcess(IGame $game, Server $server): Process {
        return new Process(function ($process) use ($game, $server) {
            $state = GameState::from($this->atomicState->get());
            if ($state != GameState::SHUTDOWN || $state == GameState::ERROR) {
                error_log("Invalid Atomic GameState for getNewProcess: ".$state::class."::".$state->name);
                $server->shutdown();
            }

            $this->atomicState->set(GameState::STARTING->value);

            try {
                $pdo = new PDO(Config::PDO_DNS, options: Config::PDO_OPTIONS);
            } catch (PDOException $e) {
                $code = (int)$e->getCode();
                $msg = $e->getMessage();
                error_log("PDO Error ($code): $msg");
                $this->atomicState->set(GameState::ERROR->value);
                $server->shutdown();
                return;
            }

            $game->setUp($server, $pdo, $this->playerTable, $this->inputTable, $this->atomicState);
            new Engine($game, $this->atomicState);
            $this->atomicState->set(GameState::SHUTDOWN->value);
        });
    }
}
