<?php

namespace Nodoka\server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * @package Nodoka\server
 */
class LobbyServer implements MessageComponentInterface {
    private $debugError;
    private $users;

    function __construct($debugError = false) {
        $this->debugError = $debugError;
        $this->users = new \SplObjectStorage();
    }

    /**
     * @return bool
     */
    function isDebugError() {
        return $this->debugError;
    }

    /**
     * @param ConnectionInterface $conn
     * @return User
     */
    function getUser(ConnectionInterface $conn) {
        return $this->users[$conn];
    }

    //region MessageComponentInterface impl
    function onOpen(ConnectionInterface $conn) {
        $user = new User('unknown', $conn);
        $this->users[$conn] = $user;
    }

    function onClose(ConnectionInterface $conn) {
        unset($this->users[$conn]);
    }

    function onError(ConnectionInterface $conn, \Exception $e) {
        if ($this->isDebugError()) {
            throw $e;
        } else {
            echo "onError: " . $e->getMessage() . "\n";
        }
    }

    function onMessage(ConnectionInterface $conn, $msg) {
        try {
            $user = $this->getUser($conn);

            $tokens = explode(' ', $msg);
            if (empty($tokens)) {
                throw new \InvalidArgumentException("Invalid message $msg");
            }

            $cmd = array_shift($tokens);
            $params = array_merge([$user], $tokens);

            if (!$this->validCommand($cmd)) {
                throw new \InvalidArgumentException("Invalid message $msg");
            }

            $function = 'onMessage' . ucfirst($cmd);
            call_user_func_array([$this, $function], $params);
        } catch (\Exception $e) {
            $this->onError($conn, $e);
        }
    }
    //endregion

    /**
     * @param $command
     * @return bool
     */
    function validCommand($command) {
        return in_array($command, [
            'auth',
        ]);
    }

    /**
     * @param User $user
     * @param $username
     * @throws \Exception
     */
    function onMessageAuth(User $user, $username) {
        // todo real auth
        $user->auth($username);
    }

    /**
     * @param User $user
     * @param string[] ...$roundCommandTokens
     */
    function onMessagePlay(User $user, ...$roundCommandTokens) {
        $commandLine = implode(' ', $roundCommandTokens);
        $play = null; // todo
    }
}