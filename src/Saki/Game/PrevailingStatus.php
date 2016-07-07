<?php

namespace Saki\Game;

use Saki\Util\ComparableSequence;
use Saki\Util\Immutable;
use Saki\Util\Utils;

/**
 * @package Saki\Game
 */
class PrevailingStatus implements Immutable {
    //region ComparableSequence impl
    use ComparableSequence;

    /**
     * @param PrevailingStatus $other
     * @return bool
     */
    function compareTo($other) {
        if ($prevailingDiff = $this->getPrevailingWind()->compareTo($other->getPrevailingWind())) {
            return $prevailingDiff;
        };

        $prevailingTurnDiff = $this->getPrevailingWindTurn() <=> $other->getPrevailingWindTurn();
        return $prevailingTurnDiff;
    }
    //endregion

    /**
     * @return PrevailingStatus
     */
    static function createFirst() {
        return new self(PrevailingWind::createEast(), 1);
    }

    private $prevailingWind;
    private $prevailingWindTurn;

    /**
     * @param PrevailingWind $prevailingWind
     * @param int $prevailingWindTurn
     */
    function __construct(PrevailingWind $prevailingWind, int $prevailingWindTurn) {
        if (!Utils::inRange($prevailingWindTurn, 1, 4)) {
            throw new \InvalidArgumentException();
        }

        $this->prevailingWind = $prevailingWind;
        $this->prevailingWindTurn = $prevailingWindTurn;
    }

    /**
     * @return string
     */
    function __toString() {
        $n = $this->getPrevailingWindTurn();
        return sprintf('%s,%s%s', 
            $this->getPrevailingWind(), $n, Utils::getNumberSuffix($n));
    }

    /**
     * @return PrevailingStatus
     */
    function toNextPrevailingWindTurn() {
        return new self($this->getPrevailingWind(), $this->getPrevailingWindTurn() + 1);
    }

    /**
     * @return PrevailingStatus
     */
    function toNextPrevailingWind() {
        return new self($this->getPrevailingWind()->toNext(), 1);
    }

    /**
     * @return PrevailingWind
     */
    function getPrevailingWind() {
        return $this->prevailingWind;
    }

    /**
     * @return int
     */
    function getPrevailingWindTurn() {
        return $this->prevailingWindTurn;
    }

    /**
     * Used in: SeatWind roll.
     * @return SeatWind
     */
    function getInitialSeatWindOfDealer() {
        return SeatWind::fromIndex($this->getPrevailingWindTurn());
    }
}