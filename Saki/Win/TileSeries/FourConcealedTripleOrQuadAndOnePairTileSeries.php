<?php
namespace Saki\Win\TileSeries;

use Saki\Meld\MeldList;

class FourConcealedTripleOrQuadAndOnePairTileSeries extends FourTripleOrQuadAndOnePair {
    function existIn(MeldList $allMeldList) {
        return $this->existInImpl($allMeldList, true);
    }
}