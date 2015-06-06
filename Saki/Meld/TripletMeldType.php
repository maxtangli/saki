<?php
namespace Saki\Meld;

use Saki\TileList;

class TripletMeldType extends MeldType {
    function getTileCount() {
        return 3;
    }

    protected function validFaces(TileList $tileList) {
        return $tileList[0] == $tileList[1] && $tileList[1] == $tileList[2];
    }
}