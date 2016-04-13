<?php
namespace Saki\Game;

use Saki\Util\Comparable;

/**
 * @package Saki\Game
 */
class PointFacadeItem {
    use Comparable; // design note: do not use ComparableSequence to avoid confusion.

    function compareTo($other) {
        /** @var PointFacadeItem $other */
        $other = $other;

        if ($pointDiff = $this->getPoint() - $other->getPoint()) {
            return $pointDiff;
        }

        return $this->getSeatWind()->compareTo($other->getSeatWind());
    }

    private $seatWind;
    private $point;

    /**
     * @param SeatWind $seatWind
     * @param int $point
     */
    function __construct(SeatWind $seatWind, int $point) {
        $this->seatWind = $seatWind;
        $this->point = $point;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s%s', $this->getSeatWind(), $this->getPoint());
    }

    /**
     * @return SeatWind
     */
    function getSeatWind() {
        return $this->seatWind;
    }

    /**
     * @return int
     */
    function getPoint() {
        return $this->point;
    }
}