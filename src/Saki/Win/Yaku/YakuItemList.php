<?php
namespace Saki\Win\Yaku;

use Saki\Util\ArrayList;

class YakuItemList extends ArrayList {
    function toYakuList() {
        return (new ArrayList())->fromSelect($this, function (YakuItem $yakuItem) {
            return $yakuItem->getYaku();
        });
    }

    function getTotalFanCount() {
        return $this->getSum(function (YakuItem $item) {
            return $item->getFanCount();
        });
    }

    function normalize() {
        // if exist yaku-man yaku, remove all not-yaku-man yaku
//        $yakuManItems = $this->toFilteredArray(function (YakuItem $yakuItem) {
//            return $yakuItem->getYaku()->isYakuMan();
//        });
//        if (count($yakuManItems) > 0) {
//            $this->setInnerArray($yakuManItems);
//        }

        $yakuManItemList = (new ArrayList($this->toArray()))->where(function (YakuItem $yakuItem) {
            return $yakuItem->getYaku()->isYakuMan();
        });
        if (!$yakuManItemList->isEmpty()) {
            $this->fromSelect($yakuManItemList);
        }

        // remove excluded yakus
        /** @var ArrayList $excludeYakuList */
        $excludeYakuList = $this->getAggregated(new ArrayList(), function (ArrayList $carry, YakuItem $yakuItem) {
            $carry->insertLast($yakuItem->getYaku()->getExcludedYakus());
            return $carry;
        });
        $this->where(function (YakuItem $yakuItem) use ($excludeYakuList) {
            return !$excludeYakuList->valueExist($yakuItem->getYaku());
        });

        // dora-type-yaku requires at least 1 non-dora-type-yaku
        if ($this->isAll(function (YakuItem $yakuItem) {
            return $yakuItem->getYaku()->isDoraTypeYaku();
        })
        ) {
            $this->removeAll();
        }
    }
}