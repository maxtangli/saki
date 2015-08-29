<?php
namespace Saki\Win\TileSeries;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Win\WaitingType;

class FourWinSetAnd1PairTileSeries extends TileSeries {
    function existIn(MeldList $allMeldList) {
        $winSetList = $allMeldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isWinSet();
        });
        $pairList = $allMeldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isPair();
        });
        return count($winSetList) == 4 && count($pairList) == 1;
    }

    protected function getWaitingTypeImpl(MeldList $allMeldList, Tile $winTile) {
        // prepare data
        $runList = $allMeldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isRun();
        });
        $tripleList = $allMeldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isTriple();
        });
        $pairList = $allMeldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isPair();
        });

        // 両面待ち?
        $isTwoSideRunWaiting = $runList->any(function (Meld $runMeld) use ($winTile) {
            return ($runMeld->getFirst() == $winTile && $winTile->getNumber() != 7)
            || ($runMeld->getLast() == $winTile && $winTile->getNumber() != 3);
        });
        if ($isTwoSideRunWaiting) {
            return WaitingType::getInstance(WaitingType::TWO_SIDE_RUN_WAITING);
        }

        // 双碰待ち?
        $isTwoPongWaiting = $tripleList->any(function (Meld $tripleMeld) use ($winTile) {
            return $tripleMeld->getFirst() == $winTile;
        });
        if ($isTwoPongWaiting) {
            return WaitingType::getInstance(WaitingType::TWO_PONG_WAITING);
        }

        // 辺張待ち?
        $isOneSideWaiting = $runList->any(function (Meld $runMeld) use ($winTile) {
            return ($runMeld->getFirst() == $winTile && $winTile->getNumber() == 7)
            || ($runMeld->getLast() == $winTile && $winTile->getNumber() == 3);
        });
        if ($isOneSideWaiting) {
            return WaitingType::getInstance(WaitingType::ONE_SIDE_RUN_WAITING);
        }

        // 嵌張待ち?
        $isMiddleRunWaiting = $runList->any(function (Meld $runMeld) use ($winTile) {
            return $runMeld[1] == $winTile;
        });
        if ($isMiddleRunWaiting) {
            return WaitingType::getInstance(WaitingType::MIDDLE_RUN_WAITING);
        }

        // 単騎待ち?
        $pair = $pairList[0];
        if ($pair[0] == $winTile) {
            return WaitingType::getInstance(WaitingType::SINGLE_PAIR_WAITING);
        }

        return null; // error
    }
}