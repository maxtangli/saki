<?php
require __DIR__ . '/../bootstrap.php';

use Nodoka\Server\PlayServer;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new PlayServer()
        )
    ),
    8080
);

echo "Wild Nodoka appeared!\n";
$server->run();