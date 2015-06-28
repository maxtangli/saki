<?php
namespace Saki\Yaku;

use Saki\Tile;

class SelfWindValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, YakuAnalyzerSubTarget $subTarget) {
        return $tile == $subTarget->getSelfWind();
    }
}