<?php declare(strict_types=1);

namespace Server;

use PDO;

class Config {
    const HOST = '127.0.0.1';
    const DB = 'snake';
    const USER = 'root';
    const PASS = 'root';
    const PORT = "3307";
    const CHARSET = 'utf8mb4';
    const OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    const NAME_VARCHAR_MAX = 42;
    const CONCURRENT_MAX = 5;
    const INPUT_COL = 'input';
    const NAME_COL = 'name';
    public static function getDsn(): string {
        return "mysql:host=" . Config::HOST . ";dbname=" . Config::DB . ";charset=" . Config::CHARSET . ";port=" . Config::PORT;
    }
}