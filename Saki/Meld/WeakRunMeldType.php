<?php
namespace Saki\Meld;

use Saki\Tile\Tile;
use Saki\Tile\TileList;

class WeakRunMeldType extends MeldType {
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

    function getTargetMeldType() {
        return RunMeldType::getInstance();
    }

    protected function getWaitingTilesImpl(TileList $tileList) {
        $type = $tileList[0]->getTileType();

        $numbers = [$tileList[0]->getNumber(), $tileList[1]->getNumber()];
        sort($numbers);
        $numberDiff = $numbers[1] - $numbers[0];
        if ($numberDiff == 2) {
            $waitingNumbers = [$numbers[0] + 1];
        } elseif ($numbers[0] == 1) {
            $waitingNumbers = [3];
        } elseif ($numbers[1] == 9) {
            $waitingNumbers = [7];
        } else {
            $waitingNumbers = [$numbers[0] - 1, $numbers[1] + 1];
        }

        $tiles = array_map(function ($number) use ($type) {
            return new Tile($type, $number);
        }, $waitingNumbers);
        return $tiles;
    }
}