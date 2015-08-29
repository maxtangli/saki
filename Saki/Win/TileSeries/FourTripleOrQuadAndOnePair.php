<?php
namespace Saki\Win\TileSeries;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;

class FourTripleOrQuadAndOnePair extends FourWinSetAnd1PairTileSeries {
    function existIn(MeldList $allMeldList) {
        return $this->existInImpl($allMeldList, null);
    }

    protected function existInImpl(MeldList $allMeldList, $concealed = null) {
        $tripleList = $allMeldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isTripleOrQuad();
        });
        $pairList = $allMeldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isPair();
        });
        $matchConcealed = $concealed === null || $tripleList->all(function (Meld $meld) use ($concealed) {
                return $meld->isConcealed() == $concealed;
            });
        return count($tripleList) == 4 && count($pairList) == 1 && $matchConcealed;
    }
}