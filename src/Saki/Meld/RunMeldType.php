<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;

class RunMeldType extends MeldType {
    function getTileCount() {
        return 3;
    }

    protected function validFaces(TileList $validCountTileList) {
        /** @var TIle $firstTile */
        if (!$validCountTileList->isAllSameSuit()) {
            return false;
        }

        /**
         *     a a+1 a+2
         * a   0   1   2
         * a+1 1   0   1
         * a+2 2   1   0
         */
        $expectedDiffs = [0, 0, 0, 1, 1, 1, 1, 2, 2];
        $diffList = (new ArrayList())->fromZipped($validCountTileList, $validCountTileList, function (Tile $t1, Tile $t2) {
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
        return WinSetType::create(WinSetType::HAND_WIN_SET);
    }
}

