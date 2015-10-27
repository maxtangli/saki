<?php
namespace Saki\Win\Yaku;

use Saki\Meld\Meld;
use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;

abstract class AbstractValueTilesYaku extends Yaku {
    protected function getConcealedFanCount() {
        return 1;
    }

    protected function getNotConcealedFanCount() {
        return 1;
    }

    protected function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        $meldList = $subTarget->getAllMeldList()->toFilteredMeldList(function (Meld $meld) {
            return $meld->isTripleOrQuad();
        });
        return $meldList->any(function (Meld $meld) use ($subTarget) {
            return $this->isValueTile($meld[0], $subTarget);
        });
    }

    abstract function isValueTile(Tile $tile, WinSubTarget $subTarget);
}