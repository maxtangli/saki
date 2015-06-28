<?php
namespace Saki\Yaku;

use Saki\Tile;

class RoundWindValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, YakuAnalyzerSubTarget $subTarget) {
        return $tile == $subTarget->getRoundWind();
    }
}