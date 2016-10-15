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

    function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $this->log("New connection! ({$conn->resourceId})");
        $conn->send($this->round->toJson());
    }

    function onMessage(ConnectionInterface $from, $msg) {
        $this->log(sprintf('Connection %d send: %s.' . "\n", $from->resourceId, $msg));

        // prepare
        $round = $this->round;
        $commandLine = $msg;

        // execute, for invalid command throw e
        $round->processLine($commandLine); // todo validate actor

        // send newest game state to all players
        $data = $round->toJson();

        $receiverCount = count($this->clients) - 1;
        $this->log(sprintf('Sending data "%s" to %d other connections.',
            $data, $receiverCount));

        foreach ($this->clients as $client) {
            $client->send($data);
        }
    }

    function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        $this->log("Connection {$conn->resourceId} has disconnected.");
    }

    function onError(ConnectionInterface $conn, \Exception $e) {
        $this->log("An error has occurred: {$e->getMessage()}.");

        $a = [
            'result' => 'error',
            'message' => $e->getMessage(),
        ];
        $json = json_encode($a);
        $conn->send($json);
//        $conn->close();
    }

    function log($line) {
        echo date('[Y-m-d h:m:s]') . $line . "\n";
    }
}