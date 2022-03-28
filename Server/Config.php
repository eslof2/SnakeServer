<?php declare(strict_types=1);

namespace Server;
use PDO;

class Config {
    const PDO_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    const NAME_VARCHAR_MAX = 42;
    const CONCURRENT_MAX = 5;
    const INPUT_COL = 'input';
    const NAME_COL = 'name';
    const PDO_DNS = "sqlite:".__DIR__."/snake.sqlite3";
}