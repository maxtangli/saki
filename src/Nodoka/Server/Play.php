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

        echo "New connection! ({$conn->resourceId}).\n";
        $conn->send($this->round->toJson());
    }

    function onMessage(ConnectionInterface $from, $msg) {
        echo sprintf('Connection %d send: %s.' . "\n", $from->resourceId, $msg);

        // prepare
        $round = $this->round;
        $commandLine = $msg;

        // execute, for invalid command throw e
        $round->processLine($commandLine); // todo validate actor

        // send newest game state to all players
        $data = $round->toJson();

        $receiverCount = count($this->clients) - 1;
        echo sprintf('Sending data "%s" to %d other connections.' . "\n",
            $data, $receiverCount);

        foreach ($this->clients as $client) {
            $client->send($data);
        }
    }

    function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected.\n";
    }

    function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}.\n";

        $a = [
            'isError' => true,
            'message' => $e->getMessage(),
        ];
        $json = json_encode($a);
        $conn->send($json);

//        $conn->close();
    }
}