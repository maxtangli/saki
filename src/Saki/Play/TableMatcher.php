<?php

namespace Saki\Play;

use Saki\Util\ArrayList;

/**
 * @package Saki\Play
 */
class TableMatcher {
    private $roomerList;

    function __construct() {
        $this->roomerList = new ArrayList();
    }

    /**
     * @return int
     */
    function getMatchingCount() {
        return $this->roomerList->count();
    }

    /**
     * @param Roomer $roomer
     * @return bool
     */
    function exist(Roomer $roomer) {
        return $this->roomerList->valueExist($roomer);
    }

    /**
     * @param Roomer $roomer
     */
    function matchOn(Roomer $roomer) {
        if (!$this->exist($roomer)) {
            $this->roomerList->insertLast($roomer);
        }
    }

    /**
     * @param Roomer $roomer
     */
    function matchOff(Roomer $roomer) {
        if ($this->exist($roomer)) {
            $this->roomerList->remove($roomer);
        }
    }

    /**
     * @return Table|false
     */
    function tryMatching() {
        $playerCount = 4;
        $doMatching = ($this->getMatchingCount() >= $playerCount);
        if ($doMatching) {
            $matchingRoomers = $this->roomerList->getFirstMany($playerCount);
            $this->roomerList->removeFirst($playerCount);

            $table = new Table($matchingRoomers);
            return $table;
        } else {
            return false;
        }
    }
}
