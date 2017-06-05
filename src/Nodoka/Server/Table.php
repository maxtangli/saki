<?php

namespace Nodoka\server;

use Saki\Play\Play;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Nodoka\server
 */
class Table {
    private $id;
    private $tableUserList;
    private $play;

    /**
     * @param int $id
     */
    function __construct(int $id) {
        $this->id = $id;
        $this->tableUserList = new ArrayList();
        $this->play = null;
    }

    /**
     * @return string
     */
    function __toString() {
        return "table {$this->getId()}";
    }

    /**
     * @return array
     */
    function toJson() {
        return [
            'id' => $this->getId(),
            'tableUserList' => $this->tableUserList->toArray(Utils::getMethodCallback('toJson')),
            'isStarted' => $this->isStarted()
        ];
    }

    /**
     * @return int
     */
    function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
    function getSeatCount() {
        return 4;
    }

    /**
     * @return int
     */
    function getUserCount() {
        return $this->tableUserList->count();
    }

    /**
     * @return bool
     */
    function isFull() {
        return $this->getUserCount() == $this->getSeatCount();
    }

    /**
     * @return int
     */
    function getReadyCount() {
        return $this->tableUserList->getCount(
            Utils::getMethodCallback('isReady'));
    }

    /**
     * @return bool
     */
    function isFullReady() {
        return $this->getReadyCount() == $this->getSeatCount();
    }

    /**
     * @return bool
     */
    function isStarted() {
        return isset($this->play);
    }

    function assertNotStarted() {
        if ($this->isStarted()) {
            throw new \LogicException("[$this] is started.");
        }
    }

    /**
     * @param $userId
     * @return User|false
     */
    function getInTableUserOrFalse($userId) {
        $match = function (TableUser $user) use ($userId) {
            return $user->getUser()->getId() == $userId;
        };
        $tableUser = $this->tableUserList->getSingleOrDefault($match, false);
        return $tableUser !== false ? $tableUser->getUser() : false;
    }

    /**
     * @param User $user
     * @return TableUser
     */
    private function getTableUser(User $user) {
        $match = function (TableUser $tableUser) use ($user) {
            return $tableUser->getUser() === $user;
        };
        return $this->tableUserList->getSingle($match);
    }

    /**
     * @param User $user
     */
    function join(User $user) {
        $this->assertNotStarted();
        if ($this->isFull()) {
            throw new \LogicException("[$this] is full.");
        }
        $this->tableUserList->insertLast(new TableUser($user));
    }

    /**
     * @param User $user
     */
    function leave(User $user) {
        $this->assertNotStarted();
        $this->tableUserList->remove($this->getTableUser($user)); // validate exist
    }

    /**
     * @param User $user
     */
    function ready(User $user) {
        $this->assertNotStarted();
        $this->getTableUser($user)->setReady(true); // validate exist

        if ($this->isFullReady()) {
            $this->start();
        }
    }

    /**
     * @param User $user
     */
    function unready(User $user) {
        $this->assertNotStarted();
        $this->getTableUser($user)->setReady(false); // validate exist
    }

    function allUnready() {
        $this->assertNotStarted();
        $unready = function (TableUser $tableUser) {
            $tableUser->setReady(false);
        };
        $this->tableUserList->walk($unready);
    }

//    function kickLostConnections() {
//        if ($this->isStarted()) {
//            return;
//        }
//
//        $isLostConnection = function (TableUser $tableUser) {
//            return !$tableUser->getUser()->isConnected();
//        };
//        $this->tableUserList
//            ->toArrayList()->where($isLostConnection)
//            ->walk([$this, 'leave']);
//    }

    function start() {
        if (!$this->isFullReady()) {
            throw new \LogicException("[$this] is not full ready.");
        }

        $play = new Play();
        $randomIndexes = (new ArrayList(range(0, $this->getUserCount() - 1)))->shuffle();
        foreach ($randomIndexes as $index) {
            $user = $this->tableUserList[$index]->getUser();
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