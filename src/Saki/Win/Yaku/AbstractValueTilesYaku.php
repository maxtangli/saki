<?php
namespace Saki\Win\Yaku;

use Saki\Game\Tile\Tile;
use Saki\Win\WinSubTarget;

/**
 * @package Saki\Win\Yaku
 */
abstract class AbstractValueTilesYaku extends Yaku {
    //region Yaku impl
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
        return $subTarget->getAllMeldList()
            ->isValueTiles($this->getValueTile($subTarget));
    }
    //endregion

    /**
     * @param WinSubTarget $subTarget
     * @return Tile
     */
    abstract function getValueTile(WinSubTarget $subTarget);
}