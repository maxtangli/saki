<?php

namespace Saki\Play;

use Saki\Util\ArrayList;

/**
 * @package Saki\Play
 */
class Room {
    private $roomerList;

    function __construct() {
        $this->roomerList = new ArrayList();
    }

    /**
     * @return ArrayList
     */
    function getRoomerList() {
        return $this->roomerList;
    }

    /**
     * @param UserProxy $userProxy
     * @return Roomer
     */
    function enter(UserProxy $userProxy) {
    }
}

/**
 * @package Saki\Play
 */
class Roomer {
    private $room;
    private $userProxy;

    /**
     * @param Room $room
     * @param UserProxy $userProxy
     */
    function __construct(Room $room, UserProxy $userProxy) {
        $this->room = $room;
        $this->userProxy = $userProxy;
    }

    /**
     * @return Room
     */
    function getRoom() {
        return $this->room;
    }

    /**
     * @return UserProxy
     */
    function getUserProxy() {
        return $this->userProxy;
    }

    function leave() {
    }
}