<?php
namespace Saki\Game;

use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class ReachStatus implements Immutable {

    /**
     * @return ReachStatus
     */
    static function createNotReach() {
        $obj = new self(RoundTurn::createFirst());
        $obj->reachRoundTurn = null;
        return $obj;
    }

    private $reachRoundTurn;

    /**
     * @param RoundTurn $reachRoundTurn
     */
    function __construct(RoundTurn $reachRoundTurn) {
        $this->reachRoundTurn = $reachRoundTurn;
    }

    /**
     * @return bool
     */
    function isReach() {
        return $this->reachRoundTurn !== null;
    }

    /**
     * @return RoundTurn
     */
    function getReachRoundTurn() {
        if (!$this->isReach()) {
            throw new \LogicException();
        }
        return $this->reachRoundTurn;
    }

    /**
     * @return bool
     */
    function isDoubleReach() {
        return $this->isReach() && $this->getReachRoundTurn()->getGlobalTurn() == 1;
    }
}