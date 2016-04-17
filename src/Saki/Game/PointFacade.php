<?php
namespace Saki\Game;

use Saki\Util\ArrayList;
use Saki\Util\Immutable;
use Saki\Util\ReadonlyArrayList;

/**
 * @package Saki\Game
 */
class PointFacade extends ArrayList implements Immutable {
    use ReadonlyArrayList;

    // todo good constructor style 

    /**
     * @return PointFacade
     */
    function toOrderByPointDescend() {
        return $this->getCopy()->orderByDescending(PointFacadeItem::getComparator());
    }

    /**
     * @return PointFacade
     */
    function toOrderBySeatWind() {
        return $this->getCopy()->orderByAscending(PointFacadeItem::getComparatorBySeatWind());
    }

    /**
     * Used in: isGameOver.
     * @return bool
     */
    function hasMinus() {
        return $this->any(function (PointFacadeItem $item) {
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
     * @return PointFacadeItem
     */
    function getSingleTop() {
        return $this->getTopItemList()->getSingle();
    }

    /**
     * @return ArrayList
     */
    protected function getTopItemList() {
        /** @var PointFacadeItem $maxItem */
        $maxItem = $this->getMax(PointFacadeItem::getComparator());
        $maxPoint = $maxItem->getPoint();
        return $this->toArrayList()->where(function (PointFacadeItem $item) use ($maxPoint) {
            return $item->getPoint() == $maxPoint;
        });
    }
}