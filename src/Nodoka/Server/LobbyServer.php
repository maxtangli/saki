<?php

namespace Nodoka\server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Saki\Play\Play;
use Saki\Util\ArrayList;

class LobbyServer implements MessageComponentInterface {
    private $debugError;
    private $tableList;
    private $users;

    function __construct($debugError = false) {
        $this->debugError = $debugError;
        $this->tableList = new TableList(100);
        $this->users = new \SplObjectStorage();
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
        $user = new User();
        $user->conn = $conn;
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

    function onMessage(ConnectionInterface $from, $msg) {
        try {
            $user = $this->getUser($from);

            $tokens = explode(' ', $msg);
            if (empty($tokens)) {
                throw new \InvalidArgumentException("Invalid message $msg");
            }

            $cmd = array_shift($tokens);
            $params = $tokens;
            array_unshift($params, $user);

            if (!method_exists($this, $cmd)) {
                throw new \InvalidArgumentException("Invalid message $msg");
            }

            call_user_func_array([$this, $cmd], $params);
        } catch (\Exception $e) {
            $this->onError($from, $e);
        }
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

    /**
     * @return TableList
     */
    function getTableList() {
        return $this->tableList;
    }

    function auth(User $user, $username) {
        // todo secure auth
        $user->username = $username;
    }

    function tableInfoList(User $user) {
        $tables = ['dummyTable1', 'dummyTable2'];
        $this->send($user->conn, $tables);
    }

    function tableJoin(User $user, $tableId) {
        $this->tableList->getTableById($tableId)->join($user);
    }

    function tableLeave(User $user) {
        $this->tableList->getTableByUser($user)->leave($user);
    }

    function tableReady(User $user) {
        $this->tableList->getTableByUser($user)->ready($user);
    }

    function tableUnready(User $user) {
        $this->tableList->getTableByUser($user)->unready($user);
    }

    function tablePlay(User $user, ...$roundCommandTokens) {
        $commandLine = implode(' ', $roundCommandTokens);
        $this->tableList->getTableByUser($user)
            ->getPlay()->tryExecute($user, $commandLine);
    }
}

class User {
    /** @var ConnectionInterface */
    public $conn;
    public $username;

    function __construct() {
    }

    function __toString() {
        return 'user';
    }
}

class TableList {
    private $tableList;

    function __construct($tableCount) {
        $idToTable = function ($id) {
            return new Table($id);
        };
        $this->tableList = (new ArrayList(range(0, $tableCount - 1)))
            ->select($idToTable);
    }

    /**
     * @param int $id
     * @return Table
     */
    function getTableById($id) {
        return $this->tableList[$id];
    }

    /**
     * @param User $user
     * @return Table
     */
    function getTableByUser(User $user) {
        $userExist = function (Table $table) use ($user) {
            return $table->userExist($user);
        };
        return $this->tableList->getSingle($userExist);
    }
}

class Table {
    private $id;
    private $userList;
    private $readyFlags;
    private $play;

    /**
     * @param int $id
     */
    function __construct(int $id) {
        $this->id = $id;
        $this->userList = new ArrayList();
        $this->readyFlags = new \SplObjectStorage();
        $this->play = null;
    }

    function __toString() {
        return "table {$this->getId()}";
    }

    function getId() {
        return $this->id;
    }

    function getSeatCount() {
        return 4;
    }

    function getUserCount() {
        return $this->userList->count();
    }

    function isFull() {
        return $this->getUserCount() == $this->getSeatCount();
    }

    function getReadyCount() {
        $n = 0;
        foreach ($this->readyFlags as $user) {
            $ready = $this->readyFlags[$user];
            if ($ready) ++$n;
        }
        return $n;
    }

    function isFullReady() {
        return $this->getReadyCount() == $this->getSeatCount();
    }

    function isStarted() {
        return isset($this->play);
    }

    function assertNotStarted() {
        if ($this->isStarted()) {
            throw new \LogicException("[$this] is started.");
        }
    }

    function userExist(User $user) {
        return $this->userList->valueExist($user);
    }

    function userReady(User $user) {
        $this->userList->getIndex($user); // validate exist
        return $this->readyFlags[$user];
    }

    function join(User $user) {
        $this->assertNotStarted();
        if ($this->isFull()) {
            throw new \LogicException("[$this] is full.");
        }
        $this->userList->insertLast($user);
        $this->readyFlags[$user] = false;
    }

    function leave(User $user) {
        $this->assertNotStarted();
        $this->userList->remove($user); // validate exist
        $this->readyFlags->detach($user);
    }

    function ready(User $user) {
        $this->assertNotStarted();
        $this->userList->getIndex($user); // validate exist
        $this->readyFlags[$user] = true;

        if ($this->isFullReady()) {
            $this->start();
        }
    }

    function unready(User $user) {
        $this->assertNotStarted();
        $this->userList->getIndex($user); // validate exist
        $this->readyFlags[$user] = false;
    }

    function allUnready() {
        $this->assertNotStarted();
        foreach ($this->userList as $user) {
            $this->readyFlags[$user] = false;
        }
    }

    function start() {
        if (!$this->isFullReady()) {
            throw new \LogicException("[$this] is not full ready.");
        }

        $play = new Play();
        $randomIndexes = (new ArrayList(range(0, $this->getUserCount() - 1)))->shuffle();
        foreach ($randomIndexes as $index) {
            $user = $this->userList[$index];
            $play->join($user);
        }
        $this->play = $play;
    }

    /**
     * @return Play
     */
    function getPlay() {
        if (!$this->isStarted()) {
            throw new \LogicException("[$this] is not started.");
        }
        return $this->play;
    }

    function finish() {
        if (!$this->isStarted()) {
            throw new \LogicException("[$this] is not started.");
        }
        $this->play = null;
        $this->allUnready();
    }
}