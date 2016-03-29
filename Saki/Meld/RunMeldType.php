<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;

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

        $expectedDiffs = [0, 0, 0, 1, 1, 1, 1, 2, 2];
        $diffList = (new ArrayList())->fromZipped($tileList, $tileList, function(Tile $t1, Tile $t2) {
            return abs($t1->getNumber() - $t2->getNumber());
        });
        $isConsecutiveNumber = $diffList->valueExist($expectedDiffs);

        return $isConsecutiveNumber;
    }

    function getPossibleTileLists(Tile $firstTile) {
        if ($firstTile->isSuit() && $firstTile->getNumber() <= 7) {
            $nextTile = $firstTile->getNextTile();
            $nextNextTile = $nextTile->getNextTile();
            return [new TileList([$firstTile, $nextTile, $nextNextTile])];
        } else {
            return [];
        }
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::HAND_WIN_SET);
    }
}

