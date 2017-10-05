<?php

namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Saki\Play\Response;
use Saki\Play\UserProxy;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Nodoka\Server
 */
class User implements UserProxy {
    private $context;
    private $connection;
    private $id;

    /**
     * @param Context $context
     * @param ConnectionInterface $conn
     */
    function __construct(Context $context, ConnectionInterface $conn) {
        $this->context = $context;
        $this->connection = $conn;
        $this->id = Utils::generateRandomToken('unauthorized');
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

    //region UserProxy impl
    function getId() {
        return $this->id;
    }

    function send(Response $response) {
        $this->getConnection()->send($response->getJsonInString());
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
        $this->send(Response::createError($e));
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
            $authorized = true;
            if (!$authorized) {
                throw new \InvalidArgumentException();
            }

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

        throw new \InvalidArgumentException("Invalid message[$message].");
    }
}