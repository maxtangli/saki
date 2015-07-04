<?php
namespace Saki\Meld;

use Saki\Tile\TileList;

class SingleMeldType extends MeldType {
    function getTileCount() {
        return 1;
    }

    protected function validFaces(TileList $tileList) {
        return true;
    }
}