<?php
require __DIR__ . '/vendor/autoload.php';

use Rest\Core\App;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


error_reporting($_ENV['DEBUG'] ? E_ALL : 0);

ini_set("display_errors", $_ENV['DEBUG'] ? E_ALL : 0);
ini_set("log_errors", E_ALL);
ini_set("error_log", $_ENV['ERROR_LOG']);

$app = new App();
$app->go();
