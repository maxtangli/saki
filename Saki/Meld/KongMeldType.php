<?php
namespace Saki\Meld;

use Saki\TileList;

class KongMeldType extends MeldType {
    function getTileCount() {
        return 4;
    }

    protected function validFaces(TileList $tileList) {
        return $tileList[0] == $tileList[1] && $tileList[1] == $tileList[2] && $tileList[2] == $tileList[3];
    }
}