<?php
namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Play\Participant;
use Saki\Play\Play;

/**
 * @package Nodoka\Server
 */
class PlayServer implements MessageComponentInterface {
    private $logEnable;
    private $play;

    function __construct() {
        $this->logEnable = true;
        $this->play = new Play();
    }

    /**
     * @return boolean
     */
    function isLogEnable() {
        return $this->logEnable;
    }

    /**
     * @param boolean $logEnable
     */
    function setLogEnable(bool $logEnable) {
        $this->logEnable = $logEnable;
    }

    /**
     * @return Play
     */
    function getPlay() {
        return $this->play;
    }

    /**
     * @param string $line
     */
    private function log(string $line) {
        if ($this->isLogEnable()) {
            echo date('[Y-m-d h:m:s]') . $line . "\n";
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param array $json
     */
    private function send(ConnectionInterface $conn, array $json) {
        $data = json_encode($json);
        $this->log("Connection {$conn->resourceId} sending: {$data}.");

        $conn->send($data);
    }

    private function notifyAll() {
        $play = $this->getPlay();
        foreach ($play->getUserKeys() as $conn) {
            $this->send($conn, $play->getJson($conn));
        }
    }

    /*
    E area: resourceID.role
        init: >=1 player => assign remain seatWind to local clients
              0 player => remove all local clients

        player join => remove all local clients => join => init
        player leave => init
    */

    private function fillAIClients() {
        $nTodo = 4; // for debug, prefer fast impl to accurate solution
        while ($nTodo-- > 0) {
            $this->getPlay()->join(new AIClient());
        }
    }

    private function clearAIClients() {
        $play = $this->getPlay();
        $clients = $play->getUserKeys();
        foreach ($clients as $client) {
            if ($client instanceof AIClient) {
                $play->leave($client);
            }
        }
    }

    //region MessageComponentInterface impl
    function onOpen(ConnectionInterface $conn) {
        $this->log("Connection {$conn->resourceId} opened.");

        $this->clearAIClients();
        $this->getPlay()->join($conn);
        $this->fillAIClients();

        $this->notifyAll();
    }

    function onClose(ConnectionInterface $conn) {
        $this->log("Connection {$conn->resourceId} closed.");

        $this->clearAIClients();
        $this->getPlay()->leave($conn);
        $this->fillAIClients();

        $this->notifyAll();
    }

    function onError(ConnectionInterface $conn, \Exception $e) {
        $this->log("Connection {$conn->resourceId} error: {$e->getMessage()}.");

        $error = [
            'result' => 'error',
            'message' => $e->getMessage(),
        ];
        $this->send($conn, $error);
    }

    function onMessage(ConnectionInterface $from, $msg) {
        $this->log("Connection {$from->resourceId} message: {$msg}.\n");

        // try execute, jump to onError if command invalid
        $play = $this->getPlay();
        $play->tryExecute($from, $msg);

        // todo refactor temp solution
        // if current actor is private and is AI, execute discard
        $round = $play->getRound();
        if ($round->getPhase()->isPrivate()) {
            $actor = $round->getCurrentSeatWind();
            /** @var Participant $participant */
            $participant = $play->getParticipantList($actor, true)->getSingle();
            $isAI = $participant->getUserKey() instanceof AIClient;
            if ($isAI) {
                $executableList = $round->getProcessor()->getProvider()->getExecutableList($actor);
                foreach ($executableList as $executable) {
                    if ($executable instanceof DiscardCommand) {
                        $executable->execute();
                        break;
                    }
                }
            }
        }

        $this->notifyAll();
    }
    //endregion
}