<?php

namespace Nodoka\Server;

use Ratchet\ConnectionInterface;

/**
 * @package Nodoka\Server
 */
class User implements ConnectionInterface {
    private $connection;
    private $authorized;
    private $id;
    private $username;

    function __construct(ConnectionInterface $connection) {
        $this->connection = $connection;
        $this->authorized = false;
        $this->id = sprintf('guest-%s-%s', date('YmdHis'), rand());
        $this->username = 'unknown';
    }

    /**
     * @return string
     */
    function __toString() {
        return "user {$this->getId()}";
    }

    /**
     * @return string
     */
    function getId() {
        return $this->id;
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
     * @param array $data
     */
    function sendJson(array $data) {
        $this->send(json_encode($data));
    }

    function sendResponseOk() {
        $this->sendJson(['response' => 'ok']);
    }

    /**
     * @param string $message
     */
    function sendResponseError(string $message) {
        $this->sendJson([
            'response' => 'error',
            'message' => $message
        ]);
    }

    /**
     * @return bool
     */
    function isAuthorized() {
        return $this->authorized;
    }

    /**
     * @param string $id
     * @param string $username
     */
    function setAuthorized(string $id, string $username) {
        $this->id = $id;
        $this->username = $username;
        $this->authorized = true;
    }
}