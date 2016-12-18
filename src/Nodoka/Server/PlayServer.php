<?php
namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Saki\Command\Command;
use Saki\Command\PrivateCommand\DiscardCommand;
use Saki\Command\PublicCommand\PassCommand;
use Saki\Play\Participant;
use Saki\Play\Play;
use Saki\Util\Utils;

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
//        $this->log("Connection {$conn->resourceId} sending data.");

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
        $this->goAI();

        $this->notifyAll();
    }

    function onClose(ConnectionInterface $conn) {
        $this->log("Connection {$conn->resourceId} closed.");

        $this->clearAIClients();
        $this->getPlay()->leave($conn);
        $this->fillAIClients();
        $this->goAI();

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
        $this->log("Connection {$from->resourceId} message: {$msg}.");

        // try execute, jump to onError if command invalid
        $play = $this->getPlay();
        $play->tryExecute($from, $msg);

        $this->goAI();

        $this->notifyAll();
    }

    private function goAI() {
        // todo refactor: better responsibility assign
        $play = $this->getPlay();

        // if no player, exit
        $isAI = function (Participant $participant) {
            return $participant->isAI();
        };
        if ($play->getParticipantList()->all($isAI)) {
            return;
        }

        // if player exist
        while (true) {
            $round = $play->getRound();
            $currentActor = $round->getCurrentSeatWind();
            $commandProvided = $round->getProcessor()->getProvider()->provideAll();

            if ($round->getPhase()->isPrivate()) {
                // if private actor is AI, execute discard random
                /** @var Participant $currentParticipant */
                $currentParticipant = $play
                    ->getParticipantList($currentActor, true, true)
                    ->getSingle();
                if ($currentParticipant->isAI()) {
                    /** @var Command $discard */
                    $randomDiscard = $commandProvided
                        ->getActorProvided($currentActor, DiscardCommand::class)
                        ->getRandom();
                    $round->getProcessor()->processLine($randomDiscard->__toString());
                    continue;
                }
            } elseif ($round->getPhase()->isPublic()) {
                $publicParticipantList = $play->getParticipantList($currentActor, false, true);
                if ($publicParticipantList->all($isAI)) {
                    // if public actors are all AI, execute passAll
                    $round->getProcessor()->processLine('passAll');
                    continue;
                } else {
                    // for all public actors where actor is AI or commands=[pass], execute pass
                    $shouldExecutePass = function (Participant $participant) use ($commandProvided) {
                        $actor = $participant->getRole()->getViewer();
                        $commandList = $commandProvided->getActorProvided($actor);

                        $isPass = Utils::toClassPredicate(PassCommand::class);
                        return $commandList->any($isPass)
                            && ($participant->isAI() || $commandList->count() == 1);
                    };
                    $list = $publicParticipantList->getCopy()->where($shouldExecutePass);
                    if ($list->isNotEmpty()) {
                        $executePass = function (Participant $participant) use ($round) {
                            $actorString = $participant->getRole()->getViewer()->__toString();
                            $commandLine = "pass $actorString";
                            $round->getProcessor()->processLine($commandLine);
                        };
                        $list->walk($executePass);
                        continue;
                    }
                }
            }

            break;
        }
    }
    //endregion
}