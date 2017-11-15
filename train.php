<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$loader = require __DIR__.'/vendor/autoload.php';

use Goutte\Client;

$client = new Client();
$parser = new \Parsers\Train($client);
$parser->start();

?>