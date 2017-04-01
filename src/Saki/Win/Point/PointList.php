<?php
namespace Saki\Win\Point;

use Saki\Game\PointItem;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Util\Immutable;
use Saki\Util\Utils;

/**
 * @package Saki\Game
 */
class PointList extends ArrayList {
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
     * @return array
     */
    function toJson() {
        return $this->toOrderByRank()->toArray(
            Utils::getMethodCallback('toJson')
        );
    }

    /**
     * @return PointList
     */
    function toOrderByRank() {
        $getPointItemRank = function (PointItem $pointItem) {
            return $pointItem->getRank();
        };
        return $this->toOrderBySeatWind()->orderByAscending($getPointItemRank);
    }

    /**
     * @return PointList
     */
    function toOrderBySeatWind() {
        $getPointItemSeatWind = function (PointItem $pointItem) {
            return $pointItem->getSeatWind();
        };
        return $this->getCopy()->orderByAscending($getPointItemSeatWind);
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
     * Used in: isGameOver.
     * @param int $point
     * @return bool
     */
    function isDealerSingleTopAndEnoughPoint(int $point) {
        $topItemList = $this->getTopItemList();
        if ($topItemList->count() != 1) {
            return false;
        }
        /** @var PointItem $singleTop */
        $singleTop = $topItemList->getSingle();
        return $singleTop->getPoint() >= $point
            && $singleTop->getSeatWind()->isDealer();
    }

    /**
     * Used in: isGameOver.
     * @param int $point
     * @return bool
     */
    function isTopEnoughPoint(int $point) {
        $enoughPoint = function (PointItem $pointItem) use($point) {
            return $pointItem->getPoint() >= $point;
        };
        return $this->any($enoughPoint);
    }

    /**
     * @return ArrayList
     */
    private function getTopItemList() {
        $getPointItemRank = function (PointItem $pointItem) {
            return $pointItem->getRank();
        };
        /** @var PointItem $maxItem */
        $maxItem = $this->getMin($getPointItemRank);
        $maxPoint = $maxItem->getPoint();
        $isMaxPoint = function (PointItem $item) use ($maxPoint) {
            return $item->getPoint() == $maxPoint;
        };
        return $this->toArrayList()->where($isMaxPoint);
    }
}