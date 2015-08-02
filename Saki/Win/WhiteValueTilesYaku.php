<?php
namespace Saki\Win;

use Saki\Tile\Tile;

class WhiteValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, WinAnalyzerSubTarget $subTarget) {
        return $tile == Tile::fromString('F');
    }
}