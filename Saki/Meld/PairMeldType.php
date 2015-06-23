<?php
namespace Saki\Meld;

use Saki\TileList;

class PairMeldType extends MeldType {
    function getTileCount() {
        return 2;
    }

    protected function validFaces(TileList $tileList) {
        return $tileList[0] == $tileList[1];
    }
}