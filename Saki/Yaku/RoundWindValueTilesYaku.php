<?php
namespace Saki\Yaku;

use Saki\Tile\Tile;
use Saki\Win\WinAnalyzerSubTarget;

class RoundWindValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, WinAnalyzerSubTarget $subTarget) {
        return $tile == $subTarget->getRoundWind();
    }
}