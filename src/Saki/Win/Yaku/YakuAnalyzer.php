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
        $yakuList = new YakuItemList();
        /** @var Yaku[] $yakus */
        $yakus = $this->getYakuSet()->toArray();
        foreach ($yakus as $yaku) {
            $actualFan = $yaku->getFan($subTarget);
            if ($actualFan > 0) {
                $yakuList->insertLast(new YakuItem($yaku, $actualFan));
            }
        }
        $yakuList->normalize(); // remove mutually-excluded yaku
        return $yakuList;
    }
}