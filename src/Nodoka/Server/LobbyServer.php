<?php

namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * @package Nodoka\Server
 */
class LobbyServer implements MessageComponentInterface {
    private $debugError;
    private $authenticator;
    private $users;

    /**
     * @param bool $debugError
     */
    function __construct($debugError = false) {
        $this->debugError = $debugError;
        $this->authenticator = new NullAuthenticator();
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
        $user = new User($conn);
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

            if (!$user->isAuthorized() && $cmd !== 'auth') {
                throw new \InvalidArgumentException('Not authorized.');
            }

            $function = 'onMessage' . ucfirst($cmd);
            call_user_func_array([$this, $function], $params);
        } catch (\Exception $e) {
            $this->onError($conn, $e);
        }
    }
    //endregion

    //region message handlers
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
     * auth by plain text password via wss connection.
     *
     * for Apache, configuration is required to support wss.
     * https://stackoverflow.com/questions/16979793/php-ratchet-websocket-ssl-connect
     *
     * @param User $user
     * @param $username
     * @param $password
     */
    function onMessageAuth(User $user, $username, $password) {
        $valid = $this->authenticator->authenticate($username, $password);
        if (!$valid) {
            $e = new \InvalidArgumentException('Invalid username or password.');
            $this->onError($user->getConnection(), $e);
            return;
        }

        $mockId = $username;
        $user->setAuthorized($mockId, $username);
    }

    /**
     * @param User $user
     * @param string[] ...$roundCommandTokens
     */
    function onMessagePlay(User $user, ...$roundCommandTokens) {
        $commandLine = implode(' ', $roundCommandTokens);
        $play = null; // todo
    }
    //endregion
}