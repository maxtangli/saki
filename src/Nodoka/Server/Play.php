<?php
namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Saki\Game\PlayerType;
use Saki\Game\Round;
use Saki\Game\SeatWind;

/**
 * @package Nodoka\Server
 */
class Play implements MessageComponentInterface {
    private $logEnable;
    private $round;
    private $viewerManager;
    protected $clients;

    function __construct() {
        $this->logEnable = true;
        $this->round = new Round();
        $this->viewerManager = new ViewerManager($this->round->getRule()->getPlayerType());
        $this->clients = new \SplObjectStorage;
    }

    function logOff() {
        $this->logEnable = false;
    }

    /**
     * @param string $line
     */
    private function log(string $line) {
        if ($this->logEnable) {
            echo date('[Y-m-d h:m:s]') . $line . "\n";
        }
    }

    /**
     * @param SeatWind $viewer
     * @return array
     */
    private function getRoundJson(SeatWind $viewer = null) {
        return $this->round->toJson($viewer);
    }

    /**
     * @param ConnectionInterface $client
     * @return SeatWind $viewer
     */
    private function add(ConnectionInterface $client) {
        $viewer = $this->viewerManager->register();
        $this->clients->attach($client, $viewer);
        return $viewer;
    }

    /**
     * @param ConnectionInterface $client
     */
    private function remove(ConnectionInterface $client) {
        $viewer = $this->clients[$client];
        $this->clients->detach($client);
        $this->viewerManager->unRegister($viewer);
    }

    /**
     * @param ConnectionInterface $client
     * @return SeatWind $viewer
     */
    function getViewer(ConnectionInterface $client) {
        return $this->clients[$client];
    }

    /**
     * @param ConnectionInterface $client
     * @param array $json
     */
    private function send(ConnectionInterface $client, array $json) {
        $data = json_encode($json);
        $this->log("Sending data to connection {$client->resourceId}: {$data}.");
        $client->send($data);
    }

    /**
     * @param ConnectionInterface $client
     */
    function onOpen(ConnectionInterface $client) {
        $this->log("Connection {$client->resourceId} opened.");
        $viewer = $this->add($client);
        $this->send($client, $this->getRoundJson($viewer));
    }

    /**
     * @param ConnectionInterface $client
     * @param string $message
     */
    function onMessage(ConnectionInterface $client, $message) {
        $this->log("Connection {$client->resourceId} message: {$message}.\n");

        // execute, for invalid command throw e
        $commandLine = $message;
        $this->round->processLine($commandLine); // todo validate actor

        // send newest game state to all players
        foreach ($this->clients as $client) {
            $viewer = $this->getViewer($client);
            $json = $this->getRoundJson($viewer);
            $this->send($client, $json);
        }
    }

    /**
     * @param ConnectionInterface $client
     */
    function onClose(ConnectionInterface $client) {
        $this->log("Connection {$client->resourceId} closed.");
        $this->remove($client);
    }

    /**
     * @param ConnectionInterface $client
     * @param \Exception $e
     */
    function onError(ConnectionInterface $client, \Exception $e) {
        $this->log("Connection {$client->resourceId} error: {$e->getMessage()}.");
        $error = [
            'result' => 'error',
            'message' => $e->getMessage(),
        ];
        $this->send($client, $error);
    }
}

class ViewerManager {
    private $remainSeatWindList;

    function __construct(PlayerType $playerType) {
        $this->remainSeatWindList = $playerType->getSeatWindList();
    }

    function register() {
        $viewer = $this->remainSeatWindList->getFirst(null, SeatWind::createEast());
        $this->remainSeatWindList->removeFirst();
        return $viewer;
    }

    // assume no duplicate
    function unRegister(SeatWind $viewer) {
        $this->remainSeatWindList->insertLast($viewer)->orderByAscending();
    }
}