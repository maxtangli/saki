<?php
namespace Saki\Win\Yaku\Fan1;

use Saki\Tile\Tile;
use Saki\Win\WinSubTarget;
use Saki\Win\Yaku\Yaku;

/**
 * 断么九
 * @package Saki\Win\Yaku
 */
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
        return $subTarget->getAllTileSortedList(true)->all(function (Tile $tile) {
            return $tile->isSimple();
        });
    }
}