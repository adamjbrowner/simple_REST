<?php

namespace Rest\Core;

class DB
{
    static private $PDOInstance;

    public static function getInstance()
    {
        if (!self::$PDOInstance) {
            try {
                $options = [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_WARNING,
                    \PDO::ATTR_STRINGIFY_FETCHES  => false,
                    \PDO::ATTR_EMULATE_PREPARES => false
                ];

                self::$PDOInstance = new \PDO(
                    "mysql:host={$_ENV['DB_HOST']};port={$_ENV['DB_PORT']};dbname={$_ENV['DB_NAME']}",
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASSWORD'],
                    $options
                );
            } catch (PDOException $e) {
                die("PDO CONNECTION ERROR: " . $e->getMessage() . "<br/>");
            }
        }
        return self::$PDOInstance;
    }
}
