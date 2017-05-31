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
                $user->setConn(new AIClient());
            } else {
                // leave table since lost connection
                $table->leave($user);
                $user->setConn(null);
            }
        } else {
            // not in table, do nothing
            $user->setConn(null);
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
            $firstParam = ($cmd == 'auth' ? $from : $this->getAuthorizedUser($from));
            array_unshift($params, $firstParam);

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

        $user->setConn($conn);
        $this->authorizedUsers[$conn] = $user;
    }

    /**
     * @param User $user
     */
    function tableInfoList(User $user) {
        $json = $this->getTableList()->toJson();
        $this->send($user->getConn(), $json);
    }

    /**
     * @param User $user
     * @param int $tableId
     */
    function tableJoin(User $user, $tableId) {
        $this->tableList->getTableById($tableId)->join($user);
    }

    /**
     * @param User $user
     */
    function tableLeave(User $user) {
        $this->getTableList()->getTable($user->getId())->leave($user);
    }

    /**
     * @param User $user
     */
    function tableReady(User $user) {
        $this->getTableList()->getTable($user->getId())->ready($user);
    }

    /**
     * @param User $user
     */
    function tableUnready(User $user) {
        $this->getTableList()->getTable($user->getId())->unready($user);
    }

    /**
     * @param User $user
     * @param string[] ...$roundCommandTokens
     */
    function tablePlay(User $user, ...$roundCommandTokens) {
        $commandLine = implode(' ', $roundCommandTokens);
        $this->getTableList()->getTable($user->getId())
            ->getPlay()->tryExecute($user, $commandLine);
    }
}