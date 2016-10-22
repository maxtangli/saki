<?php
namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Saki\Game\Round;

class Play implements MessageComponentInterface {
    protected $clients;
    private $round;

    function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->round = new Round();
    }

    function onOpen(ConnectionInterface $client) {
        $this->log("Connection {$client->resourceId} created.");
        $this->clients->attach($client);

        $data = $this->round->toJson();
        $this->log("Sending data to connection {$client->resourceId}: {$data}.");
        $client->send($data);
    }

    function onMessage(ConnectionInterface $from, $msg) {
        $this->log("Connection {$from->resourceId} message: {$msg}.\n");

        // prepare
        $round = $this->round;
        $commandLine = $msg;

        // execute, for invalid command throw e
        $round->processLine($commandLine); // todo validate actor

        // send newest game state to all players
        $data = $round->toJson();

        $receiverCount = count($this->clients);
        $this->log("Sending data to {$receiverCount} connections: {$data}.");

        foreach ($this->clients as $client) {
            $client->send($data);
        }
    }

    function onClose(ConnectionInterface $client) {
        $this->log("Connection {$client->resourceId} disconnected.");

        $this->clients->detach($client);
    }

    function onError(ConnectionInterface $client, \Exception $e) {
        $this->log("Connection {$client->resourceId} occurred an error: {$e->getMessage()}.");

        $a = [
            'result' => 'error',
            'message' => $e->getMessage(),
        ];
        $json = json_encode($a);
        $client->send($json);
//        $client->close();
    }
    
    function log($line) {
        echo date('[Y-m-d h:m:s]') . $line . "\n";
    }
}