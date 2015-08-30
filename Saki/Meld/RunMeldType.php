<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

class RunMeldType extends MeldType {
    function getTileCount() {
        return 3;
    }

    protected function validFaces(TileList $tileList) {
        $sameSuit = $tileList[0]->getTileType()->isSuit() &&
            $tileList[0]->getTileType() == $tileList[1]->getTileType() && $tileList[1]->getTileType() == $tileList[2]->getTileType();
        if (!$sameSuit) {
            return false;
        }

        $numbers = [$tileList[0]->getNumber(), $tileList[1]->getNumber(), $tileList[2]->getNumber()];
        sort($numbers);
        $consecutiveNumber = $numbers[0] + 1 == $numbers[1] && $numbers[1] + 1 == $numbers[2];
        return $consecutiveNumber;
    }

    function getTargetMeldType() {
        return null;
    }

    protected function getWaitingTilesImpl(TileList $tileList) {
        throw new \InvalidArgumentException();
    }
}

