<?php

namespace Saki\Play;

/**
 * @package Saki\Play
 */
class Roomer {
    private $userProxy;
    private $room;
    private $roomState;

    /**
     * @param Room $room
     * @param UserProxy $userProxy
     */
    function __construct(UserProxy $userProxy, Room $room) {
        $this->userProxy = $userProxy;
        $this->room = $room;
        $this->roomState = RoomState::create(RoomState::NULL);
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
            $this->roomState = RoomState::create(RoomState::MATCHING);
            $this->getUserProxy()->sendOk();
            // todo match logic
        }
    }

    function matchingOff() {
        if ($this->getRoomState()->isMatching()) {
            $this->roomState = RoomState::create(RoomState::IDLE);
            $this->getUserProxy()->sendOk();
        }
    }
}