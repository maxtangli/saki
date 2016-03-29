<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Win\WaitingType;

class WeakRunMeldType extends WeakMeldType {
    function getTileCount() {
        return 2;
    }

    protected function validFaces(TileList $tileList) {
        $sameSuit = $tileList[0]->getTileType()->isSuit() && $tileList[0]->getTileType() == $tileList[1]->getTileType();
        if (!$sameSuit) {
            return false;
        }

        $numberDiff = abs($tileList[0]->getNumber() - $tileList[1]->getNumber());
        return in_array($numberDiff, [1, 2]);
    }

    function getPossibleTileLists(Tile $firstTile) {
        $result = [];
        if ($firstTile->isSuit() && $firstTile->getNumber() <= 8) {
            $nextTile = $firstTile->getNextTile();
            $result[] = new TileList([$firstTile, $nextTile]);

            if ($firstTile->getNumber() <= 7) {
                $nextNextTile = $nextTile->getNextTile();
                $result[] = new TileList([$firstTile, $nextNextTile]);
            }
        }
        return $result;
    }

    function getTargetMeldType() {
        return RunMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileList $validMeldTileList) {
        $numberList = $validMeldTileList->toTileNumberList();
        $numbers = [$numberList->getMin(), $numberList->getMax()];
        $waitingTypeValue = $this->getWaitingTypeImpl($validMeldTileList)->getValue();
        if ($waitingTypeValue == WaitingType::MIDDLE_RUN_WAITING) {
            $waitingNumbers = [$numbers[0] + 1];
        } elseif ($waitingTypeValue == WaitingType::ONE_SIDE_RUN_WAITING) {
            $waitingNumbers = $numbers[0] == 1 ? [3] : [7];
        } elseif ($waitingTypeValue == WaitingType::TWO_SIDE_RUN_WAITING) {
            $waitingNumbers = [$numbers[0] - 1, $numbers[1] + 1];
        } else {
            throw new \LogicException();
        }

        $type = $validMeldTileList[0]->getTileType();
        $tiles = array_map(function ($number) use ($type) {
            return Tile::getInstance($type, $number);
        }, $waitingNumbers);
        return $tiles;
    }

    protected function getWaitingTypeImpl(TileList $validMeldTileList) {
        $numbers = [$validMeldTileList[0]->getNumber(), $validMeldTileList[1]->getNumber()];
        $numberDiff = abs($numbers[1] - $numbers[0]);
        if ($numberDiff == 2) {
            $waitingTypeValue = WaitingType::MIDDLE_RUN_WAITING;
        } elseif ($numbers[0] == 1) {
            $waitingTypeValue = WaitingType::ONE_SIDE_RUN_WAITING;
        } elseif ($numbers[1] == 9) {
            $waitingTypeValue = WaitingType::ONE_SIDE_RUN_WAITING;
        } else {
            $waitingTypeValue = WaitingType::TWO_SIDE_RUN_WAITING;
        }
        return WaitingType::getInstance($waitingTypeValue);
    }

    function getWinSetType() {
        return WinSetType::getInstance(WinSetType::PURE_WEAK);
    }
}