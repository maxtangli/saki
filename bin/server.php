<?php
require dirname(__DIR__) . '../vendor/autoload.php';

use Nodoka\Server\Play;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Play()
        )
    ),
    8080
);

$server->run();