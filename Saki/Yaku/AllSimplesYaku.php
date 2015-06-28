<?php
namespace Saki\Yaku;

use Saki\Tile;

class AllSimplesYaku extends Yaku {
    function getConcealedFanCount() {
        return 1;
    }

    function getExposedFanCount() {
        return 1;
    }

    protected function existInImpl(YakuAnalyzerSubTarget $subTarget) {
        return $subTarget->getAllTileSortedList()->isAll(function (Tile $tile) {
            return $tile->isSimple();
        });
    }
}