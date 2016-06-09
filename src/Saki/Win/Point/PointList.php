<?php
namespace Saki\Win\Point;

use Saki\Game\PointItem;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Util\Immutable;

/**
 * @package Saki\Game
 */
class PointList extends ArrayList implements Immutable {
//    use ReadonlyArrayList; // todo support readonly and toOrdered() both

    /**
     * @param int[] $pointMap ['E' => $point, ...]
     * @return PointList
     */
    static function fromPointMap(array $pointMap) {
        $pointPairs = [];
        foreach ($pointMap as $seatWindString => $point) {
            $pointPairs[] = [SeatWind::fromString($seatWindString), $point];
        }

        // sort by point desc, seatWind asc
        $sort = function (array $a, array $b) {
            if ($pointDiff = ($a[1] <=> $b[1])) {
                return -$pointDiff;
            }
            return call_user_func(SeatWind::getComparator(), $a[0], $b[0]);
        };
        usort($pointPairs, $sort);

        $items = [];
        foreach ($pointPairs as $index => list($seatWind, $point)) {
            $rank = $index + 1;
            $items[] = new PointItem($seatWind, $point, $rank);
        }
        return (new self($items))->toOrderBySeatWind();
    }

    /**
     * @param array $rawPointPairs [[$seatWind, $point] ...]
     * @return PointList
     */
    static function fromPointPairs(array $rawPointPairs) {
        // sort by point desc, seatWind asc
        $pointPairs = $rawPointPairs;
        $sort = function (array $a, array $b) {
            if ($pointDiff = ($a[1] <=> $b[1])) {
                return -$pointDiff;
            }
            return call_user_func(SeatWind::getComparator(), $a[0], $b[0]);
        };
        usort($pointPairs, $sort);

        $items = [];
        foreach ($pointPairs as $index => list($seatWind, $point)) {
            $rank = $index + 1;
            $items[] = new PointItem($seatWind, $point, $rank);
        }
        return (new self($items))->toOrderBySeatWind();
    }

    /**
     * @return PointList
     */
    function toOrderByRank() {
        return $this->getCopy()->orderByAscending(PointItem::getComparator());
    }

    /**
     * @return PointList
     */
    function toOrderBySeatWind() {
        return $this->getCopy()->orderByAscending(PointItem::getComparatorBySeatWind());
    }

    /**
     * @param SeatWind $seatWind
     * @return PointItem
     */
    function getSeatWind(SeatWind $seatWind) {
        return $this->getSingle(function (PointItem $item) use ($seatWind) {
            return $item->getSeatWind() == $seatWind;
        });
    }

    /**
     * Used in: isGameOver.
     * @return bool
     */
    function hasMinus() {
        return $this->any(function (PointItem $item) {
            return $item->getPoint() < 0;
        });
    }

    /**
     * @return bool
     */
    function hasTiledTop() {
        return $this->getTopItemList()->count() >= 2;
    }

    /**
     * @return PointItem
     */
    function getSingleTop() {
        return $this->getTopItemList()->getSingle();
    }

    /**
     * @return ArrayList
     */
    protected function getTopItemList() {
        /** @var PointItem $maxItem */
        $maxItem = $this->getMin(PointItem::getComparator());
        $maxPoint = $maxItem->getPoint();
        return $this->toArrayList()->where(function (PointItem $item) use ($maxPoint) {
            return $item->getPoint() == $maxPoint;
        });
    }
}