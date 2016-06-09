<?php
namespace Saki\Game;

use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class RiichiStatus implements Immutable {
    private static $nullInstance;

    /**
     * @return RiichiStatus
     */
    static function createNotRiichi() {
        if (self::$nullInstance === null) {
            $obj = new self(Turn::createFirst());
            $obj->riichiTurn = null;
            self::$nullInstance = $obj;
        }
        return self::$nullInstance;
    }

    private $riichiTurn;

    /**
     * @param Turn $riichiTurn
     */
    function __construct(Turn $riichiTurn) {
        $this->riichiTurn = $riichiTurn;
    }

    /**
     * @return bool
     */
    function isRiichi() {
        return $this->riichiTurn !== null;
    }

    /**
     * @return bool
     */
    function isDoubleRiichi() {
        return $this->isRiichi() &&
        $this->getRiichiTurn()->isFirstCircle();
    }

    /**
     * @param Turn $turn
     * @return bool
     */
    function isFirstTurn(Turn $turn) {
        // validate isRiichi
        return $this->isRiichi() &&
        $turn->isBeforeOrSame($this->getRiichiTurn()->toNextCircleOfThis());
    }

    /**
     * @return Turn
     */
    function getRiichiTurn() {
        if (!$this->isRiichi()) {
            throw new \LogicException();
        }
        return $this->riichiTurn;
    }
}