<?php

namespace Nodoka\server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class LobbyServer implements MessageComponentInterface {
    private $users;

    function __construct() {
        $this->users = new \SplObjectStorage();
    }

    /**
     * @param ConnectionInterface $conn
     * @return User
     */
    function getUser(ConnectionInterface $conn) {
        if (!isset($this->users[$conn])) {
            throw new \LogicException();
        }
        return $this->users[$conn];
    }

    function send(ConnectionInterface $conn, array $json) {
        $data = json_encode($json);
        $conn->send($data);
    }

    function onOpen(ConnectionInterface $conn) {
        $user = new User();
        $user->conn = $conn;
        $this->users[$conn] = $user;
    }

    function onClose(ConnectionInterface $conn) {
        unset($this->users[$conn]);
    }

    function onError(ConnectionInterface $conn, \Exception $e) {
        echo "onError: " . $e->getMessage() . "\n";
    }

    function onMessage(ConnectionInterface $from, $msg) {
        $user = $this->getUser($from);

        $tokens = explode(' ', $msg);
        if (empty($tokens)) {
            $this->onError($from, new \InvalidArgumentException(
                "Invalid message $msg"
            ));
        }

        $cmd = array_shift($tokens);
        $params = $tokens;
        array_unshift($params, $user);

        if (!method_exists($this, $cmd)) {
            $this->onError($from, new \InvalidArgumentException(
                "Invalid message $msg"
            ));
        }

        call_user_func_array([$this, $cmd], $params);
    }

    function auth(User $user, $username) {
        // todo secure auth
        $user->username = $username;
    }

    function tableList(User $user) {
        $tables = ['dummyTable1', 'dummyTable2'];
        $this->send($user->conn, $tables);
    }
}

class User {
    /** @var ConnectionInterface */
    public $conn;
    public $username;
}