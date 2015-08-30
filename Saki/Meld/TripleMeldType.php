<?php
namespace Saki\Meld;

use Saki\Tile\TileList;

class TripleMeldType extends WeakMeldType {
    function getTileCount() {
        return 3;
    }

    protected function validFaces(TileList $tileList) {
        return $tileList[0] == $tileList[1] && $tileList[1] == $tileList[2];
    }

    function getTargetMeldType() {
        return QuadMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileList $tileList) {
        return [$tileList[0]];
    }
}