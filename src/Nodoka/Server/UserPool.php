<?php

namespace Nodoka\Server;
use Ratchet\ConnectionInterface;

/**
 * @package Nodoka\Server
 */
class UserPool {
    private $users;

    function __construct() {
        $this->users = new \SplObjectStorage();
    }

    /**
     * @param ConnectionInterface $conn
     * @return User
     */
    function getUser(ConnectionInterface $conn) {
        return $this->users[$conn];
    }

    /**
     * @param ConnectionInterface $conn
     * @param User $user
     */
    function registerConnection(ConnectionInterface $conn, User $user) {
        $this->users[$conn] = $user;
    }

    /**
     * @param ConnectionInterface $conn
     */
    function unRegisterConnection(ConnectionInterface $conn) {
        unset($this->users[$conn]);
    }
}