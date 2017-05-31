<?php

namespace Nodoka\server;

use Ratchet\ConnectionInterface;

/**
 * @package Nodoka\server
 */
class User {
    private $username;
    private $conn;

    function __construct(string $username) {
        $this->username = $username;
        $this->conn = NullClient::create();
    }

    /**
     * @return string
     */
    function __toString() {
        return "user {$this->getId()}";
    }

    /**
     * @return int|string
     */
    function getId() {
        return $this->username;
    }

    /**
     * @return string
     */
    function getUsername() {
        return $this->username;
    }

    /**
     * @return bool
     */
    function isConnected() {
        return !($this->conn instanceof NullClient)
            && !($this->conn instanceof AIClient);
    }

    /**
     * @return ConnectionInterface
     */
    function getConn() {
        return $this->conn;
    }

    /**
     * @param ConnectionInterface|null $conn
     */
    function setConn($conn = null) {
        $this->conn = $conn ?? NullClient::create();
    }
}