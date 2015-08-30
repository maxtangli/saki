<?php
namespace Saki\Meld;

use Saki\Tile\TileList;

class SingleMeldType extends WeakMeldType {
    function getTileCount() {
        return 1;
    }

    protected function validFaces(TileList $tileList) {
        return true;
    }

    function getTargetMeldType() {
        return PairMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileList $tileList) {
        return [$tileList[0]];
    }
}