<?php
namespace App;
use PDO;
use PDOException;

class DBConnection
{
    private static $connection;

    public static function getConnection()
    {
        if (self::$connection === null) {
            $config = include __DIR__ . '/../config/config.php';
            try {
                self::$connection = new PDO(
                    "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset=utf8",
                    $config['db']['user'],
                    $config['db']['password']
                );
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
}
