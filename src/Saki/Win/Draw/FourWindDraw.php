<?php
namespace Saki\Win\Draw;

use Saki\Game\Round;
use Saki\Win\Result\AbortiveDrawResult;
use Saki\Win\Result\ResultType;

/**
 * @package Saki\Win\Draw
 */
class FourWindDraw extends Draw {
    //region Draw impl
    protected function isDrawImpl(Round $round) {
        
        $isFirstRound = $round->getTurn()->isFirstCircle();
        $isFourSameWindDiscard = $round->getOpenHistory()->isFourSameWindDiscard();
        return $isFirstRound && $isFourSameWindDiscard;
    }

    protected function getResultImpl(Round $round) {
        return new AbortiveDrawResult(
            $round->getGameData()->getPlayerType(),
            ResultType::create(ResultType::FOUR_WIND_DRAW)
        );
    }
    //endregion
}