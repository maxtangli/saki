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
        $yakuList = new YakuList([], $subTarget->isConcealed());
        /** @var Yaku[] $yakus */
        $yakus = $this->getYakuSet()->toArray();
        foreach ($yakus as $yaku) {
            if ($yaku->existIn($subTarget)) {
                $yakuList->push($yaku);
            }
        }
        $yakuList->normalize(); // remove mutually-excluded yaku
        return $yakuList;
    }
}