<?php
namespace Saki\Game;

use Saki\Util\ComparableSequence;
use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class Turn implements Immutable {
    use ComparableSequence;

    function compareTo($other) {
        /** @var Turn $other */
        $other = $other;
        $circleCountDiff = $this->circleCount <=> $other->circleCount;
        if ($circleCountDiff != 0) {
            return $circleCountDiff;
        }

        // todo simplify offset
        $seatWindDiff = $this->getSeatWind()->getWindTile()->getWindOffsetFrom(
            $other->getSeatWind()->getWindTile()
        );
        return $seatWindDiff;
    }

    /**
     * @param string $s
     * @return Turn
     */
    static function fromString(string $s) {
        $circleCount = intval(substr($s, 0, strlen($s) - 1));
        $seatWind = SeatWind::fromString(substr($s, -1));
        return new self($circleCount, $seatWind);
    }

    /**
     * Note that here it's more clear to provide a factory method rather than default constructor.
     * @return Turn
     */
    static function createFirst() {
        return new self(1, SeatWind::createEast());
    }

    private $circleCount;
    private $seatWind;

    /**
     * @param int $circleCount
     * @param SeatWind $seatWind
     */
    function __construct(int $circleCount, SeatWind $seatWind) {
        $valid = $circleCount >= 1;
        if (!$valid) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid argument int $circleCount[%s], SeatWind $seatWind[%s].',
                    $circleCount, $seatWind
                )
            );
        }

        $this->circleCount = $circleCount;
        $this->seatWind = $seatWind;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s%s', $this->circleCount, $this->seatWind);
    }

    /**
     * @param SeatWind $goalSeatWind
     * @return $this|Turn
     */
    function toSeatWind(SeatWind $goalSeatWind) {
        if ($this->getSeatWind() == $goalSeatWind) {
            return $this;
        }

        $addCircleCount = $goalSeatWind->isDealer() ? 1 : 0;
        $goalCircleCount = $this->getCircleCount() + $addCircleCount;
        return new Turn($goalCircleCount, $goalSeatWind);
    }

    /**
     * @return int
     */
    function getCircleCount() {
        return $this->circleCount;
    }

    /**
     * @return SeatWind
     */
    function getSeatWind() {
        return $this->seatWind;
    }
}