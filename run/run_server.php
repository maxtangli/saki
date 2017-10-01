<?php
require __DIR__ . '/../bootstrap.php';

use Nodoka\Server\Server;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Server()
        )
    ),
    8080
);

echo "Wild Koromo appeared!\n";
$server->run();