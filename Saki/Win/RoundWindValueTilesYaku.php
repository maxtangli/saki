<?php
namespace Saki\Win;

use Saki\Tile\Tile;

class RoundWindValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, WinAnalyzerSubTarget $subTarget) {
        return $tile == $subTarget->getRoundWind();
    }
}