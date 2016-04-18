<?php
namespace Saki\Win\Yaku;

use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;

abstract class AbstractValueTilesYaku extends Yaku {
    function getConcealedFan() {
        return 1;
    }

    function getNotConcealedFan() {
        return 1;
    }

    function getRequiredSeries() {
        return [];
    }

    protected function matchOther(WinSubTarget $subTarget) {
        return $subTarget->getAllMeldList()->isValueTiles($this->getValueTile($subTarget));
    }

    /**
     * @param WinSubTarget $subTarget
     * @return Tile
     */
    abstract function getValueTile(WinSubTarget $subTarget);
}