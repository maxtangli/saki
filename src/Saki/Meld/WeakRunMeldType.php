<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\Waiting\WaitingType;

/**
 * @package Saki\Meld
 */
class WeakRunMeldType extends WeakMeldType {
    /**
     * @param TileList $validCountTileList
     * @return int[] list($diff, $min, $max)
     */
    protected function getNumbers(TileList $validCountTileList) {
        $numberList = $validCountTileList->toTileNumberList(); // validate
        $diff = abs($numberList[0] - $numberList[1]);
        return [$diff, $numberList->getMin(), $numberList->getMax()];
    }

    //region MeldType impl
    function getTileCount() {
        return 2;
    }

    protected function validFaces(TileList $validCountTileList) {
        list($diff, ,) = $this->getNumbers($validCountTileList);
        return $validCountTileList->isAllSameSuit() && in_array($diff, [1, 2]);
    }

    protected function getPossibleTileLists(Tile $firstTile) {
        $result = [];
        if ($firstTile->isSuit() && $firstTile->getNumber() <= 8) {
            $nextTile = $firstTile->getNextTile(1);
            $result[] = new TileList([$firstTile, $nextTile]);

            if ($firstTile->getNumber() <= 7) {
                $nextNextTile = $firstTile->getNextTile(2);
                $result[] = new TileList([$firstTile, $nextNextTile]);
            }
        }
        return $result;
    }
    //endregion

    //region WeakMeldType impl
    function getTargetMeldType() {
        return RunMeldType::create();
    }

    protected function getWaitingImpl(TileList $validMeldTileList) {
        list(, $min, $max) = $this->getNumbers($validMeldTileList);
        $v = $this->getWaitingTypeImpl($validMeldTileList)->getValue();
        if ($v == WaitingType::MIDDLE_RUN_WAITING) {
            $waitingNumbers = [$min + 1];
        } elseif ($v == WaitingType::ONE_SIDE_RUN_WAITING) {
            $waitingNumbers = $min == 1 ? [3] : [7];
        } elseif ($v == WaitingType::TWO_SIDE_RUN_WAITING) {
            $waitingNumbers = [$min - 1, $max + 1];
        } else {
            throw new \LogicException();
        }

        /** @var Tile $firstTile */
        $firstTile = $validMeldTileList[0];
        return TileList::fromNumbers($waitingNumbers, $firstTile->getTileType());
    }

    protected function getWaitingTypeImpl(TileList $validMeldTileList) {
        list($diff, $min, $max) = $this->getNumbers($validMeldTileList);
        if ($diff == 2) {
            $v = WaitingType::MIDDLE_RUN_WAITING;
        } elseif ($diff == 1) {
            if ($min == 1) {
                $v = WaitingType::ONE_SIDE_RUN_WAITING;
            } elseif ($max == 9) {
                $v = WaitingType::ONE_SIDE_RUN_WAITING;
            } else {
                $v = WaitingType::TWO_SIDE_RUN_WAITING;
            }
        } else {
            throw new \LogicException();
        }
        return WaitingType::create($v);
    }

    function getWinSetType() {
        return WinSetType::create(WinSetType::PURE_WEAK);
    }
    //endregion
}