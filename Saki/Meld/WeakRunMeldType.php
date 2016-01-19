<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;
use Saki\Tile\TileSortedList;
use Saki\Win\WaitingType;

class WeakRunMeldType extends WeakMeldType {
    function getTileCount() {
        return 2;
    }

    protected function validFaces(TileSortedList $tileSortedList) {
        $sameSuit = $tileSortedList[0]->getTileType()->isSuit() && $tileSortedList[0]->getTileType() == $tileSortedList[1]->getTileType();
        if (!$sameSuit) {
            return false;
        }

        $numberDiff = abs($tileSortedList[0]->getNumber() - $tileSortedList[1]->getNumber());
        return in_array($numberDiff, [1, 2]);
    }

    function getPossibleTileLists(Tile $firstTile) {
        $result = [];
        if ($firstTile->isSuit() && $firstTile->getNumber() <= 8) {
            $nextTile = $firstTile->toNextTile();
            $result[] = new TileList([$firstTile, $nextTile]);

            if ($firstTile->getNumber() <= 7) {
                $nextNextTile = $nextTile->toNextTile();
                $result[] = new TileList([$firstTile, $nextNextTile]);
            }
        }
        return $result;
    }

    function getTargetMeldType() {
        return RunMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileSortedList $validMeldTileSortedList) {
        $type = $validMeldTileSortedList[0]->getTileType();

        $numbers = [$validMeldTileSortedList[0]->getNumber(), $validMeldTileSortedList[1]->getNumber()];
        $waitingTypeValue = $this->getWaitingTypeImpl($validMeldTileSortedList)->getValue();
        if ($waitingTypeValue == WaitingType::MIDDLE_RUN_WAITING) {
            $waitingNumbers = [$numbers[0] + 1];
        } elseif ($waitingTypeValue == WaitingType::ONE_SIDE_RUN_WAITING) {
            $waitingNumbers = $numbers[0] == 1 ? [3] : [7];
        } elseif ($waitingTypeValue == WaitingType::TWO_SIDE_RUN_WAITING) {
            $waitingNumbers = [$numbers[0] - 1, $numbers[1] + 1];
        } else {
            throw new \LogicException();
        }

        $tiles = array_map(function ($number) use ($type) {
            return Tile::getInstance($type, $number);
        }, $waitingNumbers);
        return $tiles;
    }

    protected function getWaitingTypeImpl(TileSortedList $validMeldTileSortedList) {
        $numbers = [$validMeldTileSortedList[0]->getNumber(), $validMeldTileSortedList[1]->getNumber()];
        $numberDiff = $numbers[1] - $numbers[0];
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