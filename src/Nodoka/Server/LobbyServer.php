<?php

namespace Nodoka\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Saki\Command\Command;
use Saki\Play\Play;

/**
 * @package Nodoka\Server
 */
class LobbyServer implements MessageComponentInterface {
    private $debugError;
    private $authenticator;
    private $users;
    private $room;

    /**
     * @param bool $debugError
     */
    function __construct($debugError = false) {
        $this->debugError = $debugError;
        $this->authenticator = new NullAuthenticator();
        $this->users = new \SplObjectStorage();
        $this->room = new Room();
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

    /**
     * @param ConnectionInterface $conn
     * @param User $user
     */
    private function registerConnection(ConnectionInterface $conn, User $user) {
        $this->users[$conn] = $user;
    }

    /**
     * @param ConnectionInterface $conn
     */
    private function unRegisterConnection(ConnectionInterface $conn) {
        unset($this->users[$conn]);
    }

    /**
     * @return Room
     */
    function getRoom() {
        return $this->room;
    }

    //region MessageComponentInterface impl
    function onOpen(ConnectionInterface $conn) {
        $user = new User($conn);
        $this->registerConnection($conn, $user);
    }

    function onClose(ConnectionInterface $conn) {
        /** @var User $user */
        $user = $this->getUser($conn);

        $this->unRegisterConnection($conn);

        // handle lost connection for playing
        if ($this->getRoom()->isPlaying($user)) {
            $play = $this->getRoom()->getPlay($user);

            $aiClient = new NullClient();
            $user->setConnection($aiClient);
            $this->registerConnection($aiClient, $user);

            $this->tryAI($play);
        }
    }

    function onError(ConnectionInterface $conn, \Exception $e) {
        $this->onThrowable($conn, $e);
    }

    function onThrowable(ConnectionInterface $conn, \Throwable $e) {
        if ($this->isDebugError()) {
            throw $e;
        } else {
            echo "onError: " . $e->getMessage() . "\n";
        }

        $user = $this->getUser($conn);
        $user->sendResponseError($e->getMessage());
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

            if (!$this->validCommandName($cmd)) {
                throw new \InvalidArgumentException("Invalid message $msg");
            }

            if ($cmd == 'auth' && $user->isAuthorized()) {
                throw new \InvalidArgumentException('Already authorized.');
            }

            if ($cmd !== 'auth' && !$user->isAuthorized()) {
                throw new \InvalidArgumentException('Not authorized.');
            }

            $function = 'onMessage' . ucfirst($cmd);
            call_user_func_array([$this, $function], $params);
        } catch (\Throwable $e) {
            $this->onThrowable($conn, $e);
        }
    }
    //endregion

    //region message handlers
    /**
     * @param $command
     * @return bool
     */
    function validCommandName($command) {
        return in_array($command, [
            'auth', 'join', 'leave', 'play'
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
        $connection = $user->getConnection();

        $valid = $this->authenticator->authenticate($username, $password);
        if (!$valid) {
            throw new \InvalidArgumentException('Invalid username or password.');
        }

        $mockId = $username;
        $user->setAuthorized($mockId, $username);
        $user->sendResponseOk();

        // todo refine this temp solution, consider login time etc.
        // handle come back after lost connection
        if ($this->getRoom()->isPlaying($user)) {
            $originUser = $this->getRoom()->getPlayingUser($user->getId());
            $originUser->setConnection($connection);
            $this->registerConnection($connection, $originUser);

            // notice all users that hero's come back
            $play = $this->getRoom()->getPlay($originUser);
            $this->sendPlay($play);
        }
    }

    /**
     * @param User $user
     */
    function onMessageJoin(User $user) {
        $this->getRoom()->joinMatching($user);
        $user->sendResponseOk();

        $play = $this->getRoom()->doMatching();
        if ($play !== false) {
            $this->sendPlay($play);
        }
    }

    /**
     * @param User $user
     */
    function onMessageLeave(User $user) {
        $this->getRoom()->leaveMatching($user);
        $user->sendResponseOk();
    }

    /**
     * @param User $user
     * @param string[] ...$roundCommandTokens
     */
    function onMessagePlay(User $user, ...$roundCommandTokens) {
        $commandLine = implode(' ', $roundCommandTokens);
        $play = $this->getRoom()->getPlay($user);
        $play->tryExecute($user, $commandLine);
        $this->sendPlay($play);

        $this->tryAI($play);
    }

    // todo move into Play?
    private function sendPlay(Play $play) {
        /** @var User[] $users */
        $users = $play->getUserKeys();
        foreach ($users as $user) {
            $user->sendJson($play->getJson($user));
        }
    }

    private function tryAI(Play $play) {
        $action = AI::create()->tryAI($play);
        if ($action !== false) {
            /** @var User $user */
            $user = $action[0];
            /** @var Command $nextCommand */
            $nextCommand = $action[1];

            $message = 'play ' . $nextCommand;
            $this->onMessage($user->getConnection(), $message);
        }
    }
    //endregion
}
