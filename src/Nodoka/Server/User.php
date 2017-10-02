<?php

namespace Nodoka\Server;
use Ratchet\ConnectionInterface;
use Saki\Play\UserProxy;
use Saki\Util\ArrayList;

/**
 * @package Nodoka\Server
 */
class User implements UserProxy {
    private $context;
    private $connection;
    private $id;
    private $state;

    /**
     * @param Context $context
     * @param ConnectionInterface $conn
     */
    function __construct(Context $context, ConnectionInterface $conn) {
        $this->context = $context;
        $this->connection = $conn;
        $this->id = sprintf('s-%s-%s', time(), mt_rand());
        $this->state = 'unauthorized';
    }

    /**
     * @return Context
     */
    function getContext() {
        return $this->context;
    }

    /**
     * @return ConnectionInterface
     */
    function getConnection() {
        return $this->connection;
    }

    function send(array $json) {
        $this->getConnection()->send(json_encode($json));
    }

    //region UserProxy impl
    function getId() {
        return $this->id;
    }

    function sendRound(array $json) {
        $this->send($json);
    }

    function sendOk() {
        $this->send([
            'response' => 'ok'
        ]);
    }

    function sendError(string $message) {
        $this->send([
            'response' => 'error',
            'message' => $message
        ]);
    }
    //endregion

    function onOpen() {
        $this->getContext()->getConnectionRegister()->register($this->getConnection(), $this);
        $this->getContext()->getRoom()->getRoomerOrGenerate($this)->join();
    }

    function onClose() {
        $this->getContext()->getRoom()->getRoomerOrGenerate($this)->leave();
        $this->getContext()->getConnectionRegister()->unRegister($this->getConnection());
    }

    function onError(\Exception $e) {
        $this->sendError($e->getMessage());
    }

    function onMessage(string $message) {
        $tokenList = new ArrayList(explode(' ', $message));
        $command = $tokenList->getFirst(); // validate
        $paramList = $tokenList->removeFirst();
        $tokenList = null;

        $roomer = $this->getContext()->getRoom()->getRoomerOrGenerate($this);
        if ($command == 'auth') {
            $paramList->assertCount(2);
            list($username, $password) = $paramList->toArray();
            // todo auth

            $this->id = $username;
            $roomer->authorize();
            return;
        }

        if ($command == 'matchingOn') {
            $roomer->matchingOn();
            return;
        }

        if ($command == 'matchingOff') {
            $roomer->matchingOff();
            return;
        }

        if ($command == 'play') {
            $roundCommand = implode(' ', $paramList->toArray());
            $roomer->play($roundCommand);
            return;
        }

        throw new \InvalidArgumentException();
    }
}