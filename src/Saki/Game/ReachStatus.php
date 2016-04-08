<?php
namespace Saki\Game;

use Saki\Util\Immutable;

class ReachStatus implements Immutable {

    static function createNotReach() {
        $obj = new self(1);
        $obj->reachGlobalTurn = null;
        return $obj;
    }

    private $reachGlobalTurn;

    /**
     * @param int $reachGlobalTurn
     */
    function __construct(int $reachGlobalTurn) {
        $this->reachGlobalTurn = $reachGlobalTurn;
    }

    function isReach() {
        return $this->reachGlobalTurn !== null;
    }

    function isDoubleReach() {
        return $this->isReach() && $this->getReachGlobalTurn() == 1;
    }

    function getReachGlobalTurn() {
        if (!$this->isReach()) {
            throw new \LogicException();
        }
        return $this->reachGlobalTurn;
    }
}