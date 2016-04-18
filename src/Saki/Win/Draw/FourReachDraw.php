<?php
namespace Saki\Win\Draw;

use Saki\Game\Area;
use Saki\Game\Round;
use Saki\Win\Result\AbortiveDrawResult;
use Saki\Win\Result\ResultType;

/**
 * @package Saki\Win\Draw
 */
class FourReachDraw extends Draw {
    //region Draw impl
    protected function isDrawImpl(Round $round) {
        $areaList = $round->getAreas()->getAreaList();
        return $areaList->all(function (Area $area) {
            return $area->getReachStatus()->isReach();
        });
    }

    protected function getResultImpl(Round $round) {
        return new AbortiveDrawResult(
            $round->getGameData()->getPlayerType(),
            ResultType::create(ResultType::FOUR_REACH_DRAW)
        );
    }
    //endregion
}