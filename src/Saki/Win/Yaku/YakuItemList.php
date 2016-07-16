<?php
namespace Saki\Win\Yaku;

use Saki\Util\ArrayList;

/**
 * @package Saki\Win\Yaku
 */
class YakuItemList extends ArrayList {
    /**
     * @return $this
     */
    function toYakuList() {
        $toYaku = function (YakuItem $yakuItem) {
            return $yakuItem->getYaku();
        };
        return (new ArrayList())->fromSelect($this, $toYaku);
    }

    /**
     * @return int
     */
    function getTotalFan() {
        $toFan = function (YakuItem $item) {
            return $item->getFan();
        };
        return $this->getSum($toFan);
    }

    /**
     * @return $this
     */
    function normalize() {
        // 1. if exist yaku-man yaku, remove all not-yaku-man yaku
        $isYakuman = function (YakuItem $yakuItem) {
            return $yakuItem->getYaku()->isYakuMan();
        };
        $yakumanItemList = (new ArrayList($this->toArray()))->where($isYakuman);
        if (!$yakumanItemList->isEmpty()) {
            $this->fromSelect($yakumanItemList);
        }

        // 2. remove excluded yakus
        $toExcludedYakus = function (YakuItem $yakuItem) {
            return $yakuItem->getYaku()->getExcludedYakus();
        };
        $excludeYakuList = (new ArrayList())
            ->fromSelectMany($this, $toExcludedYakus);

        $isNotExcluded = function (YakuItem $yakuItem) use ($excludeYakuList) {
            return !$excludeYakuList->valueExist($yakuItem->getYaku());
        };
        $this->where($isNotExcluded);

        // 3. dora-type-yaku requires at least 1 non-dora-type-yaku
        $isDoraTypeYaku = function (YakuItem $yakuItem) {
            return $yakuItem->getYaku()->isDoraTypeYaku();
        };
        if ($this->all($isDoraTypeYaku)) {
            $this->removeAll();
        }

        return $this;
    }
}