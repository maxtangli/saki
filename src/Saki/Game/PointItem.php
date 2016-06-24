<?php
namespace Saki\Game;

use Saki\Util\Comparable;
use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class PointItem implements Immutable {
    //region Comparable impl
    use Comparable; // design note: do not use ComparableSequence to avoid confusion.

    /**
     * @param PointItem $other
     * @return bool
     */
    function compareTo($other) {
        return $this->getRank() <=> $other->getRank();
    }
    //endregion

    /**
     * @return \Closure
     */
    static function getComparatorBySeatWind() {
        return function (PointItem $v1, PointItem $v2) {
            return $v1->getSeatWind()->compareTo($v2->getSeatWind());
        };
    }

    private $seatWind;
    private $point;
    private $rank;

    /**
     * @param SeatWind $seatWind
     * @param int $point
     * @param int $rank
     */
    function __construct(SeatWind $seatWind, int $point, int $rank) {
        $this->seatWind = $seatWind;
        $this->point = $point;
        $this->rank = $rank;
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s%s%s', $this->getRank(), $this->getSeatWind(), $this->getPoint());
    }

    /**
     * @param int $newPoint
     * @return PointItem
     */
    function toPointKeepRank(int $newPoint) {
        return new PointItem($this->getSeatWind(), $newPoint, $this->getRank());
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

    /**
     * @return int
     */
    function getRank() {
        return $this->rank;
    }

    /**
     * @return int
     */
    function toScore() {
        if ($this->getPoint() % 1000 != 0) {
            throw new \BadMethodCallException();
        }
        return intval($this->getPoint() / 1000);
    }
}