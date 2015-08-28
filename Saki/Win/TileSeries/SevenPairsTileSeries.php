<?php
namespace Saki\Win\TileSeries;

use Saki\Meld\Meld;
use Saki\Meld\MeldList;
use Saki\Tile\Tile;
use Saki\Win\WaitingType;

class SevenPairsTileSeries extends TileSeries {
    function existIn(MeldList $allMeldList) {
        $pairList = $allMeldList->getFilteredMeldList(function (Meld $meld) {
            return $meld->isPair();
        });
        $pairs = $pairList->toArray();
        return count($pairList) == 7 && array_unique($pairs) == $pairs;
    }

    protected function getWaitingTypeImpl(MeldList $allMeldList, Tile $winTile) {
        return WaitingType::getInstance(WaitingType::SINGLE_PAIR_WAITING);
    }
}