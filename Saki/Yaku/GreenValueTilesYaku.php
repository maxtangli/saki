<?php
namespace Saki\Yaku;

use Saki\Tile\Tile;
use Saki\Win\WinAnalyzerSubTarget;

class GreenValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, WinAnalyzerSubTarget $subTarget) {
        return $tile == Tile::fromString('P');
    }
}