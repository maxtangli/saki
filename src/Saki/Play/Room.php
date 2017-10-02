<?php

namespace Saki\Play;

use Saki\Util\ArrayList;

/**
 * @package Saki\Play
 */
class Room {
    private $roomerList;
    private $tableMatcher;

    function __construct() {
        $this->roomerList = new ArrayList();
        $this->tableMatcher = new TableMatcher();
    }

    /**
     * @return ArrayList
     */
    function getRoomerList() {
        return $this->roomerList;
    }

    /**
     * @return TableMatcher
     */
    function getTableMatcher() {
        return $this->tableMatcher;
    }

    /**
     * @param UserProxy $userProxy
     * @return Roomer
     */
    function getRoomerOrGenerate(UserProxy $userProxy) {
        $match = function (Roomer $roomer) use($userProxy) {
            return $roomer->isUserProxy($userProxy);
        };
        $generator = function () use($userProxy) {
            return new Roomer($userProxy, $this);
        };
        return $this->getRoomerList()->getSingleOrGenerate($match, $generator);
    }
}