<?php
namespace Saki\Game;

use Saki\Util\ArrayList;
use Saki\Util\ReadonlyArrayList;

/**
 * @package Saki\Game
 */
class PointFacade extends ArrayList {
    use ReadonlyArrayList;

    // todo good constructor style 

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