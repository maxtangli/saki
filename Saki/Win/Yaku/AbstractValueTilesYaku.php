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
        return $subTarget->getAllMeldList()->isValueTiles($this->getValueTile($subTarget));
    }

    /**
     * @param WinSubTarget $subTarget
     * @return Tile
     */
    abstract function getValueTile(WinSubTarget $subTarget);
}