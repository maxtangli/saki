<?php
namespace Saki\Yaku;

use Saki\Tile;

class WhiteValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, YakuAnalyzerSubTarget $subTarget) {
        return $tile == Tile::fromString('F');
    }
}