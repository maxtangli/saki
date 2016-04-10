<?php
namespace Saki\Win\Yaku;

use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;

abstract class AbstractValueTilesYaku extends Yaku {
    function getConcealedFanCount() {
        return 1;
    }

    function getNotConcealedFanCount() {
        return 1;
    }

    function getRequiredTileSeries() {
        return [];
    }

    protected function matchOtherConditions(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isValueTiles($this->getValueTile($subTarget));
    }

    /**
     * @param WinSubTarget $subTarget
     * @return Tile
     */
    abstract function getValueTile(WinSubTarget $subTarget);
}