<?php

namespace Nodoka\server;

use Ratchet\ConnectionInterface;

/**
 * @package Nodoka\server
 */
class User implements ConnectionInterface {
    private $username;
    private $connection;
    private $authorized;

    function __construct(string $username, ConnectionInterface $connection) {
        $this->username = $username;
        $this->connection = $connection;
        $this->authorized = false;
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
     * @return ConnectionInterface
     */
    function getConnection() {
        return $this->connection;
    }

    /**
     * @param ConnectionInterface $connection
     */
    function setConnection(ConnectionInterface $connection) {
        $this->connection = $connection;
    }

    //region ConnectionInterface impl
    function send($data) {
        return $this->getConnection()->send($data);
    }

    function close() {
        return $this->getConnection()->close();
    }

    //endregion

    /**
     * @return bool
     */
    function isAuthorized() {
        return $this->authorized;
    }

    /**
     * @param string $username
     */
    function auth(string $username) {
        $this->username = $username;
    }
}