<?php
namespace Saki\Meld;

use Saki\TileList;

class SequenceMeldType extends MeldType {
    function getTileCount() {
        return 3;
    }

    protected function validFaces(TileList $tileList) {
        $sameSuit = $tileList[0]->getTileType()->isSuit() &&
            $tileList[0]->getTileType() == $tileList[1]->getTileType() && $tileList[1]->getTileType() == $tileList[2]->getTileType();
        $numbers = [$tileList[0]->getNumber(), $tileList[1]->getNumber(), $tileList[2]->getNumber()];
        sort($numbers);
        $consecutiveNumber = $numbers[0] + 1 == $numbers[1] && $numbers[1] + 1 == $numbers[2];
        return $sameSuit && $consecutiveNumber;
    }
}