<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Util\ArrayList;

/**
 * @package Saki\Meld
 */
class RunMeldType extends MeldType {
    //region MeldType impl
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
        $toDiffAbs = function (Tile $t1, Tile $t2) {
            return abs($t1->getNumber() - $t2->getNumber());
        };
        $diffList = (new ArrayList())->fromZipped($validCountTileList, $validCountTileList, $toDiffAbs);
        $isConsecutiveNumber = $diffList->valueExist($expectedDiffs);
        return $isConsecutiveNumber;
    }

    protected function getPossibleTileLists(Tile $firstTile) {
        $result = [];
        if ($firstTile->isSuit() && $firstTile->getNumber() <= 7) {
            $nextTile = $firstTile->getNextTile(1);
            $nextNextTile = $firstTile->getNextTile(2);
            $result[] = new TileList([$firstTile, $nextTile, $nextNextTile]);
        }
        return $result;
    }

    function getWinSetType() {
        return WinSetType::create(WinSetType::HAND_WIN_SET);
    }
    //endregion
}

