<?php

namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * @package Nodoka\Server
 */
class Server implements MessageComponentInterface {
    private $context;

    function __construct() {
        $this->context = new Context();
    }

    /**
     * @return Context
     */
    function getContext() {
        return $this->context;
    }

    //region MessageComponentInterface impl
    function onOpen(ConnectionInterface $conn) {
        $connectionRegister = $this->getContext()->getConnectionRegister();
        $generator = function () use($conn) {
            return new User($this->getContext(), $conn);
        };
        $user = $connectionRegister->getUserOrGenerate($conn, $generator);

        $user->onOpen();
    }

    function onClose(ConnectionInterface $conn) {
        $user = $this->getContext()->getConnectionRegister()->getUser($conn);
        $user->onClose();
    }

    function onError(ConnectionInterface $conn, \Exception $e) {
        $user = $this->getContext()->getConnectionRegister()->getUser($conn);
        $user->onError($e);
    }

    function onMessage(ConnectionInterface $from, $msg) {
        $user = $this->getContext()->getConnectionRegister()->getUser($from);
        $user->onMessage($msg);
    }
    //endregion
}