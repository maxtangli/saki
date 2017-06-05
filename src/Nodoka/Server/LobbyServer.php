<?php

namespace Nodoka\server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * @package Nodoka\server
 */
class LobbyServer implements MessageComponentInterface {
    private $debugError;
    private $tableList;
    private $authorizedUsers;

    function __construct($debugError = false) {
        $this->debugError = $debugError;
        $this->tableList = new TableList(1);
        $this->authorizedUsers = new \SplObjectStorage();
    }

    /**
     * @return bool
     */
    function isDebugError() {
        return $this->debugError;
    }

    function send(ConnectionInterface $conn, array $json) {
        $data = json_encode($json);
        $conn->send($data);
    }

    function onOpen(ConnectionInterface $conn) {
        // waiting auth
    }

    function onClose(ConnectionInterface $conn) {
        if (!$this->isAuthorized($conn)) {
            return;
        }

        $user = $this->getAuthorizedUser($conn);
        if ($this->getTableList()->inTable($user->getId())) {
            $table = $this->getTableList()->getTable($user->getId());
            if ($table->isStarted()) {
                // keep table playing and expect king's return
            } else {
                // leave table since lost connection
                $table->leave($user);
            }
        } else {
            // not in table, do nothing
        }

        unset($this->authorizedUsers[$conn]);
    }

    function onError(ConnectionInterface $conn, \Exception $e) {
        if ($this->isDebugError()) {
            throw $e;
        } else {
            echo "onError: " . $e->getMessage() . "\n";
        }
    }

    function onMessage(ConnectionInterface $from, $msg) {
        try {
            $tokens = explode(' ', $msg);
            if (empty($tokens)) {
                throw new \InvalidArgumentException("Invalid message $msg");
            }

            $cmd = array_shift($tokens);
            $params = $tokens;
            if ($cmd == 'auth') {
                array_unshift($params, $from);
            } else {
                array_unshift($params, $from, $this->getAuthorizedUser($from));
            }

            if (!$this->validCommand($cmd)) {
                throw new \InvalidArgumentException("Invalid message $msg");
            }

            call_user_func_array([$this, $cmd], $params);
        } catch (\Exception $e) {
            $this->onError($from, $e);
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @return bool
     */
    function isAuthorized(ConnectionInterface $conn) {
        return isset($this->authorizedUsers[$conn]);
    }

    /**
     * @param ConnectionInterface $conn
     * @return User
     */
    function getAuthorizedUser(ConnectionInterface $conn) {
        if (!$this->isAuthorized($conn)) {
            throw new \LogicException("user not exist for $conn");
        }
        return $this->authorizedUsers[$conn];
    }

    /**
     * @param User $user
     * @return ConnectionInterface
     */
    function getConnectionByUser(User $user) {
        /** @var ConnectionInterface $conn */
        foreach ($this->authorizedUsers as $conn) {
            if ($this->authorizedUsers[$conn] === $user) {
                return $conn;
            }
        }
        throw new \InvalidArgumentException("\$user[$user] not existed.");
    }

    /**
     * @return TableList
     */
    function getTableList() {
        return $this->tableList;
    }

    /**
     * @param $command
     * @return bool
     */
    function validCommand($command) {
        return in_array($command, [
            'auth',
            'tableInfoList',
            'tableJoin', 'tableLeave', 'tableReady', 'tableUnready',
            'tablePlay'
        ]);
    }

    /**
     * @param ConnectionInterface $conn
     * @param $username
     * @throws \Exception
     */
    function auth(ConnectionInterface $conn, $username) {
        // todo secure auth
        $userId = $username;
        $authOk = true;
        if (!$authOk) {
            throw new \Exception("user[$username] auth failed.");
        }

        $tableList = $this->getTableList();
        if ($tableList->inTable($userId)) {
            $user = $tableList->getUser($userId);
        } else {
            $user = new User($username);
        }

        $this->authorizedUsers[$conn] = $user;
    }

    /**
     * @param ConnectionInterface $conn
     * @param User $user
     */
    function tableInfoList(ConnectionInterface $conn, User $user) {
        $json = $this->getTableList()->toJson();
        $this->send($conn, $json);
    }

    /**
     * @param ConnectionInterface $conn
     * @param User $user
     * @param int $tableId
     */
    function tableJoin(ConnectionInterface $conn, User $user, $tableId) {
        $this->getTableList()->getTableById($tableId)->join($user);
    }

    /**
     * @param ConnectionInterface $conn
     * @param User $user
     */
    function tableLeave(ConnectionInterface $conn, User $user) {
        $this->getTableList()->getTable($user->getId())->leave($user);
    }

    /**
     * @param ConnectionInterface $conn
     * @param User $user
     */
    function tableReady(ConnectionInterface $conn, User $user) {
        $this->getTableList()->getTable($user->getId())->ready($user);
    }

    /**
     * @param ConnectionInterface $conn
     * @param User $user
     */
    function tableUnready(ConnectionInterface $conn, User $user) {
        $this->getTableList()->getTable($user->getId())->unready($user);
    }

    /**
     * @param ConnectionInterface $conn
     * @param User $user
     * @param string[] ...$roundCommandTokens
     */
    function tablePlay(ConnectionInterface $conn, User $user, ...$roundCommandTokens) {
        $commandLine = implode(' ', $roundCommandTokens);
        $table = $this->getTableList()->getTable($user->getId());
        $play = $table->getPlay();

        $play->tryExecute($user, $commandLine);

        if ($play->getRound()->getPhaseState()->isGameOver()) {
            // kick lost connection users in table
            // todo
        }
    }
}