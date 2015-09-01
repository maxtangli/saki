<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileSortedList;

class RunMeldType extends MeldType {
    function getTileCount() {
        return 3;
    }

    protected function validFaces(TileSortedList $tileSortedList) {
        $sameSuit = $tileSortedList[0]->getTileType()->isSuit() &&
            $tileSortedList[0]->getTileType() == $tileSortedList[1]->getTileType() && $tileSortedList[1]->getTileType() == $tileSortedList[2]->getTileType();
        if (!$sameSuit) {
            return false;
        }

        $numbers = [$tileSortedList[0]->getNumber(), $tileSortedList[1]->getNumber(), $tileSortedList[2]->getNumber()];
        $isConsecutiveNumber = $numbers[0] + 1 == $numbers[1] && $numbers[1] + 1 == $numbers[2];
        return $isConsecutiveNumber;
    }

    function getPossibleTileSortedLists(Tile $firstTile) {
        if ($firstTile->isSuit() && $firstTile->getNumber() <= 7) {
            $nextTile = $firstTile->toNextTile();
            $nextNextTile = $nextTile->toNextTile();
            $tileSortedList = new TileSortedList([$firstTile, $nextTile, $nextNextTile]);
            return [$tileSortedList];
        } else {
            return [];
        }
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::HAND_WIN_SET);
    }
}

