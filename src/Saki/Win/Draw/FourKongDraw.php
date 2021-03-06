<?php
namespace Saki\Win\Draw;

use Saki\Game\Area;
use Saki\Game\Meld\KongMeldType;
use Saki\Game\Round;
use Saki\Win\Result\AbortiveDrawResult;
use Saki\Win\Result\ResultType;

/**
 * @package Saki\Win\Draw
 */
class FourKongDraw extends Draw {
    //region Draw impl
    protected function isDrawImpl(Round $round) {
        $areaList = $round->getAreaList();
        $kongCountList = $areaList->toArrayList(function (Area $area) {
            $melded = $area->getHand()->getMelded();
            $kongCount = $melded->toFiltered([KongMeldType::create()])->count();
            return $kongCount;
        });

        $kongCount = $kongCountList->getSum();
        $kongPlayerCount = $kongCountList->getCount(function (int $n) {
            return $n > 0;
        });

        $isFourKongDraw = $kongCount >= 4 && $kongPlayerCount >= 2;
        return $isFourKongDraw;
    }

    protected function getResultImpl(Round $round) {
        return new AbortiveDrawResult(
            $round->getRule()->getPlayerType(),
            ResultType::create(ResultType::FOUR_KONG_DRAW)
        );
    }
    //endregion
}