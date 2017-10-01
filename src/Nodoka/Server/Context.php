<?php

namespace Nodoka\Server;

use Saki\Play\Room;

/**
 * @package Nodoka\Server
 */
class Context {
    private $connectionRegister;
    private $room;

    function __construct() {
        $this->connectionRegister = new ConnectionRegister();
        $this->room = new Room();
    }

    /**
     * @return ConnectionRegister
     */
    function getConnectionRegister() {
        return $this->connectionRegister;
    }

    /**
     * @return Room
     */
    function getRoom() {
        return $this->room;
    }
}