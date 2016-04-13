<?php
namespace Saki\Game;

use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class ReachStatus implements Immutable {
    private static $nullInstance;

    /**
     * @return ReachStatus
     */
    static function createNotReach() {
        if (self::$nullInstance === null) {
            $obj = new self(Turn::createFirst());
            $obj->reachTurn = null;
            self::$nullInstance = $obj;
        }
        return self::$nullInstance;
    }

    private $reachTurn;

    /**
     * @param Turn $reachTurn
     */
    function __construct(Turn $reachTurn) {
        $this->reachTurn = $reachTurn;
    }

    /**
     * @return bool
     */
    function isReach() {
        return $this->reachTurn !== null;
    }

    /**
     * @return bool
     */
    function isDoubleReach() {
        return $this->isReach() &&
        $this->getReachTurn()->isFirstCircle();
    }

    /**
     * @param Turn $turn
     * @return bool
     */
    function isFirstTurn(Turn $turn) {
        // validate isReach
        return $this->isReach() &&
        $turn->isBeforeOrSame($this->getReachTurn()->toNextCicleOfThis());
    }

    /**
     * @return Turn
     */
    function getReachTurn() {
        if (!$this->isReach()) {
            throw new \LogicException();
        }
        return $this->reachTurn;
    }
}