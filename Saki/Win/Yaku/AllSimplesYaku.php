<?php
namespace Saki\Win\Yaku;

use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;

class AllSimplesYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 1;
    }

    protected function getExposedFanCount() {
        return 1;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllTileSortedList()->isAll(function (Tile $tile) {
            return $tile->isSimple();
        });
    }
}