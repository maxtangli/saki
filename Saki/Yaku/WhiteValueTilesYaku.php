<?php
namespace Saki\Yaku;

use Saki\Tile\Tile;
use Saki\Win\WinAnalyzerSubTarget;

class WhiteValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, WinAnalyzerSubTarget $subTarget) {
        return $tile == Tile::fromString('F');
    }
}