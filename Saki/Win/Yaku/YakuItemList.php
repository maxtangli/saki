<?php
namespace Saki\Win\Yaku;

use Saki\Util\ArrayLikeObject;

class YakuItemList extends ArrayLikeObject {
    function toYakuList() {
        return new ArrayLikeObject($this->toArray(function (YakuItem $yakuItem) {
            return $yakuItem->getYaku();
        }));
    }

    function getTotalFanCount() {
        return $this->sum(function (YakuItem $item) {
            return $item->getFanCount();
        });
    }

    function normalize() {
        // if exist yaku-man yaku, remove all not-yaku-man yaku
        $yakuManItems = $this->toFilteredArray(function (YakuItem $yakuItem) {
            return $yakuItem->getYaku()->isYakuMan();
        });

        if (count($yakuManItems) > 0) {
            $this->setInnerArray($yakuManItems);
        }

        // remove excluded yakus
        /** @var ArrayLikeObject $excludeYakuList */
        $excludeYakuList = $this->toReducedValue(function (ArrayLikeObject $carry, YakuItem $yakuItem) {
            $carry->push($yakuItem->getYaku()->getExcludedYakus());
            return $carry;
        }, new ArrayLikeObject([]));
        $remainYakuItems = $this->toFilteredArray(function (YakuItem $yakuItem) use ($excludeYakuList) {
            return !$excludeYakuList->valueExist($yakuItem->getYaku());
        });
        $this->setInnerArray($remainYakuItems);

        // dora-type-yaku requires at least 1 non-dora-type-yaku
        if ($this->all(function(YakuItem $yakuItem) {
            return $yakuItem->getYaku()->isDoraTypeYaku();
        })) {
            $this->clear();
        }
    }
}