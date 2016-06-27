<?php
namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Saki\Game\Round;
use Saki\Game\SeatWind;

class Play implements MessageComponentInterface {
    protected $clients;
    private $round;
    private $actor;

    function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->round = new Round();
        $this->actor = SeatWind::createEast(); // todo multiple player
    }

    function getActor(ConnectionInterface $from) {
        return $this->actor; // todo
    }

    function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
        $conn->send($this->round->__toString());
    }

    function onMessage(ConnectionInterface $from, $msg) {
        echo sprintf('Connection %d send: %s.' . "\n", $from->resourceId, $msg);

        // prepare
        $round = $this->round;
        $actor = $this->getActor($from);
        $commandLine = $msg;

        // execute, for invalid command throw e
        $round->processLine($commandLine, $actor);

        // debug: skip to actor
        if ($round->getPhase()->isPublic()) {
            $round->process('skip 4');
        }

        // send newest game state to all players
        $from->send($round->__toString()); // todo send to all
        return;

        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);
            }
        }
    }

    function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->send($e->getMessage());
//        $conn->close();
    }
}