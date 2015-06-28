<?php
namespace Saki\Yaku;

use Saki\Meld\Meld;
use Saki\Tile;

abstract class ValueTilesYaku extends Yaku {
    function getConcealedFanCount() {
        return 1;
    }

    function getExposedFanCount() {
        return 1;
    }

    protected function existInImpl(YakuAnalyzerSubTarget $subTarget) {
        $meldList = $subTarget->getAllMeldList()->getFilteredMeldList(function (Meld $meld) {
            return $meld->isTripleOrQuad();
        });
        return $meldList->isAny(function (Meld $meld) use ($subTarget) {
            return $this->isValueTile($meld[0], $subTarget);
        });
    }

    abstract function isValueTile(Tile $tile, YakuAnalyzerSubTarget $subTarget);
}