<?php

namespace Nodoka\Server;

use Ratchet\ConnectionInterface;

/**
 * @package Nodoka\Server
 */
class ConnectionRegister {
    private $connToUser;

    function __construct() {
        $this->connToUser = new \SplObjectStorage();
    }

    /**
     * @param ConnectionInterface $conn
     * @return bool
     */
    function exist(ConnectionInterface $conn) {
        return $this->connToUser->contains($conn);
    }

    /**
     * @param ConnectionInterface $conn
     * @param User $user
     */
    function register(ConnectionInterface $conn, User $user) {
        if ($this->exist($conn)) {
            throw new \InvalidArgumentException();
        }

        $this->connToUser->attach($conn, $user);
    }

    /**
     * @param ConnectionInterface $conn
     */
    function unRegister(ConnectionInterface $conn) {
        if (!$this->exist($conn)) {
            throw new \InvalidArgumentException();
        }

        $this->connToUser->detach($conn);
    }

    /**
     * @param ConnectionInterface $conn
     * @return User
     */
    function getUser(ConnectionInterface $conn) {
        if (!$this->exist($conn)) {
            throw new \InvalidArgumentException();
        }

        return $this->connToUser[$conn];
    }

    /**
     * @param ConnectionInterface $conn
     * @param callable $generator
     * @return User
     */
    function getUserOrGenerate(ConnectionInterface $conn, callable $generator) {
        return $this->exist($conn) ? $this->getUser($conn) : call_user_func($generator);
    }
}