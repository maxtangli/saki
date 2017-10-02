<?php

namespace Saki\Play;

/**
 * @package Saki\Play
 */
class Roomer implements UserProxy {
    private $userProxy;
    private $room;
    private $roomState;
    private $seat;

    /**
     * @param Room $room
     * @param UserProxy $userProxy
     */
    function __construct(UserProxy $userProxy, Room $room) {
        $this->userProxy = $userProxy;
        $this->room = $room;
        $this->roomState = RoomState::create(RoomState::NULL);
        $this->seat = null;
    }

    /**
     * @return Room
     */
    function getRoom() {
        return $this->room;
    }

    /**
     * @return RoomState
     */
    function getRoomState() {
        return $this->roomState;
    }

    /**
     * @return UserProxy
     */
    function getUserProxy() {
        return $this->userProxy;
    }

    /**
     * @param UserProxy $userProxy
     * @return bool
     */
    function isUserProxy(UserProxy $userProxy) {
        return $this->getUserProxy()->getId() == $userProxy->getId();
    }

    //region UserProxy impl
    function getId() {
        return $this->getUserProxy()->getId();
    }

    function sendRound(array $json) {
        return $this->getUserProxy()->sendRound($json);
    }

    function sendOk() {
        return $this->getUserProxy()->sendOk();
    }

    function sendError(string $message) {
        return $this->getUserProxy()->sendError($message);
    }

    /**
     * @return Seat
     */
    function getSeat() {
        if ($this->getRoomState()->isPlaying()) {
            if (!isset($this->seat)) {
                throw new \LogicException();
            }
            return $this->seat;
        }
        throw new \InvalidArgumentException();
    }

    //endregion

    function join() {
        if ($this->getRoomState()->isNull()) {
            $this->getRoom()->getRoomerList()->insertLast($this);
            $this->roomState = RoomState::create(RoomState::UNAUTHORIZED);
            $this->getUserProxy()->sendOk();
        }
    }

    function leave() {
        if ($this->getRoomState()->isInRoom()) {
            $this->getRoom()->getRoomerList()->remove($this);
            $this->roomState = RoomState::create(RoomState::UNAUTHORIZED);
            $this->getUserProxy()->sendOk();
        }
    }

    function authorize() {
        if ($this->getRoomState()->isUnauthorized()) {
            $this->roomState = RoomState::create(RoomState::IDLE);
            $this->getUserProxy()->sendOk();
        }
    }

    function matchingOn() {
        if ($this->getRoomState()->isIdle()) {
            $tableMatcher = $this->getRoom()->getTableMatcher();

            $tableMatcher->matchOn($this);
            $this->roomState = RoomState::create(RoomState::MATCHING);
            $this->getUserProxy()->sendOk();

            $tableOrFalse = $tableMatcher->tryMatching();
            if ($tableOrFalse instanceof Table) {
                $seat = $tableOrFalse->getSeat($this);
                $this->seat = $seat;

                $tableOrFalse->notifyAll();
            }
        }
    }

    function matchingOff() {
        if ($this->getRoomState()->isMatching()) {
            $tableMatcher = $this->getRoom()->getTableMatcher();

            $tableMatcher->matchOff($this);
            $this->roomState = RoomState::create(RoomState::IDLE);
            $this->getUserProxy()->sendOk();
        }
    }

    /**
     * @param string $command
     */
    function play(string $command) {
        $seat = $this->getSeat();
        $seat->tryExecute($command);

        $isGameOver = $seat->getTable()->getRound()
            ->getPhaseState()->isGameOver();
        if ($isGameOver) {
            $this->roomState = RoomState::create(RoomState::IDLE);
            $this->seat = null;
        }
    }
}