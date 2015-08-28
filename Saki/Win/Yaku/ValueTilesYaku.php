<?php
namespace Saki\Win\Yaku;

use Saki\Meld\Meld;
use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;

abstract class ValueTilesYaku extends Yaku {
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
        $meldList = $subTarget->getAllMeldList()->getFilteredMeldList(function (Meld $meld) {
            return $meld->isTripleOrQuad();
        });
        return $meldList->isAny(function (Meld $meld) use ($subTarget) {
            return $this->isValueTile($meld[0], $subTarget);
        });
    }

    abstract function isValueTile(Tile $tile, WinSubTarget $subTarget);
}