<?php
namespace Saki\Meld;

use Saki\Tile\TileList;

class PairMeldType extends WeakMeldType {
    function getTileCount() {
        return 2;
    }

    protected function validFaces(TileList $tileList) {
        return $tileList[0] == $tileList[1];
    }

    function getTargetMeldType() {
        return TripleMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileList $tileList) {
        return [$tileList[0]];
    }
}