<?php

namespace Saki\Win\Yaku;

use Saki\Win\WinSubTarget;

class YakuAnalyzer {
    private $yakuSet;

    function __construct(YakuSet $yakuSet) {
        $this->yakuSet = $yakuSet;
    }

    function getYakuSet() {
        return $this->yakuSet;
    }

    function analyzeYakuList(WinSubTarget $subTarget) {
        $yakuList = new YakuItemList([]);
        /** @var Yaku[] $yakus */
        $yakus = $this->getYakuSet()->toArray();
        foreach ($yakus as $yaku) {
            $actualFanCount = $yaku->getFanCount($subTarget);
            if ($actualFanCount > 0) {
                $yakuList->push(new YakuItem($yaku, $actualFanCount));
            }
        }
        $yakuList->normalize(); // remove mutually-excluded yaku
        return $yakuList;
    }
}