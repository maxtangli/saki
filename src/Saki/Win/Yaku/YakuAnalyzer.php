<?php

namespace Saki\Win\Yaku;

use Saki\Util\Immutable;
use Saki\Win\WinSubTarget;

/**
 * @package Saki\Win\Yaku
 */
class YakuAnalyzer implements Immutable {
    private $yakuSet;

    /**
     * @param YakuSet $yakuSet
     */
    function __construct(YakuSet $yakuSet) {
        $this->yakuSet = $yakuSet;
    }

    /**
     * @return YakuSet
     */
    function getYakuSet() {
        return $this->yakuSet;
    }

    /**
     * @param WinSubTarget $subTarget
     * @return YakuItemList
     */
    function analyzeYakuList(WinSubTarget $subTarget) {
        $yakuExist = function (Yaku $yaku) use ($subTarget) {
            return $yaku->existIn($subTarget);
        };
        $existYakuList = $this->getYakuSet()->toArrayList()
            ->where($yakuExist);

        $toYakuItem = function (Yaku $yaku) use ($subTarget) {
            return new YakuItem($yaku, $yaku->getFan($subTarget));
        };
        $yakuItemList = (new YakuItemList())->fromSelect($existYakuList, $toYakuItem);

        return $yakuItemList->normalize();
    }
}