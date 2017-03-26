<?php

namespace Saki\Game;

use Saki\Util\ArrayList;

/**
 * History of Claims include: chow, pung and kong.
 * Note that current phase is not considered since it do not affects hasClaim() result.
 * Used in: yaku analyze.
 * @package Saki\Game
 */
class ClaimHistory {
    /**
     * @var ArrayList an ArrayList with ascend claim Turn values.
     */
    private $list;

    function __construct() {
        $this->list = new ArrayList();
    }

    function reset() {
        $this->list->removeAll();
    }

    /**
     * @param Turn $turn
     */
    function recordClaim(Turn $turn) {
        $valid = $this->list->isEmpty() ||
            $turn->isAfterOrSame($this->list->getLast());
        if (!$valid) {
            throw new \InvalidArgumentException();
        }

        $this->list->insertLast($turn);
    }

    /**
     * @param Turn|null $fromTurn
     * @return bool
     */
    function hasClaim(Turn $fromTurn = null) {
        $actualFromTurn = $fromTurn ?? Turn::createFirst();
        return $this->list->isEmpty() ?
            false :
            $actualFromTurn->isBeforeOrSame($this->list->getLast());
    }
}