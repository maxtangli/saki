<?php
namespace Saki\Win\TileSeries;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;

class FourRunAndOnePairTileSeries extends FourWinSetAnd1PairTileSeries {
    function existIn(MeldList $allMeldList) {
        $runList = $allMeldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isRun();
        });
        $pairList = $allMeldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isPair();
        });
        return count($runList) == 4 && count($pairList) == 1;
    }
}