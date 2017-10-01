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
    function getRoomer(UserProxy $userProxy) {
        $match = function (Roomer $roomer) use($userProxy) {
            return $roomer->isUserProxy($userProxy);
        };
        $generator = function () use($userProxy) {
            return new Roomer($userProxy, $this);
        };
        return $this->getRoomerList()->getSingleOrGenerate($match, $generator);
    }
}