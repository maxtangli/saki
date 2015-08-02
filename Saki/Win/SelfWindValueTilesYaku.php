<?php
namespace Saki\Win;

use Saki\Tile\Tile;

class SelfWindValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, WinAnalyzerSubTarget $subTarget) {
        return $tile == $subTarget->getSelfWind();
    }
}