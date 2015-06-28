<?php
namespace Saki\Yaku;

use Saki\Tile;

class RedValueTilesYaku extends ValueTilesYaku {
    function isValueTile(Tile $tile, YakuAnalyzerSubTarget $subTarget) {
        return $tile == Tile::fromString('C');
    }
}