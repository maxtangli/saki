<?php

namespace Nodoka\server;

use Saki\Play\Play;

/**
 * @package Nodoka\server
 */
class Room {
    private $matchingUserList;

    function __construct() {
        $this->matchingUserList = [];
    }

    function joinMatching(User $user) {
        $this->matchingUserList[$user->getId()] = $user;
    }

    function leaveMatching(User $user) {
        unset($this->matchingUserList[$user->getId()]);
    }

    function doMatching() {
        if (count($this->matchingUserList) >= 4) {
            $users = array_splice($this->matchingUserList, 0, 4);

            shuffle($users);
            $play = new Play();
            $play->joinAll($users);
            return $play;
        }
    }
}