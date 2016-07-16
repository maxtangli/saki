<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Win\Point\PointList;
use Saki\Win\Result\Result;
use Saki\Win\Result\WinResult;

/**
 * @package Saki\Phase
 */
class OverPhaseState extends PhaseState {
    private $result;

    /**
     * @param Result $result
     */
    function __construct(Result $result) {
        $this->result = $result;
    }

    /**
     * @return Result|WinResult
     */
    function getResult() {
        return $this->result;
    }

    /**
     * @param Round $round
     * @return bool
     */
    function isGameOver(Round $round) {
        $pointList = $round->getPointHolder()->getPointList();
        $prevailingCurrent = $round->getPrevailing();

        if ($pointList->hasMinus()) {
            return true;
        }

        if ($prevailingCurrent->isNormalNotLast()) {
            return false;
        }

        if ($prevailingCurrent->isSuddenDeathLast()) {
            return true;
        } // else isNormalLast or isSuddenDeath

        if ($pointList->hasTiledTop()) {
            return false;
        }

        $topItem = $pointList->getSingleTop();
        $isTopEnoughPoint = $topItem->getPoint() >= 30000;
        if (!$isTopEnoughPoint) {
            return false;
        } // else isTopPlayerEnoughPoint
        
        $isDealerTop = $topItem->getSeatWind()->isDealer();
        $result = $round->getPhaseState()->getResult();
        return !($result->isKeepDealer()) || $isDealerTop;
    }

    /**
     * @param Round $round
     * @return PointList
     */
    function getFinalScore(Round $round) {
        if (!$this->isGameOver($round)) {
            throw new \InvalidArgumentException('Game is not over.');
        }

        $scoreStrategy = $round->getGameData()->getScoreStrategy();
        $raw = $round->getPointHolder()->getPointList();
        return $scoreStrategy->rawToFinal($raw);
    }

    //region PhaseState impl
    function getPhase() {
        return Phase::createOver();
    }

    function getDefaultNextState(Round $round) {
        throw new \LogicException('No nextState exists in OverPhaseState.');
    }

    function enter(Round $round) {
        $result = $this->getResult();

        // modify points
        $round->getPointHolder()->applyPointChangeMap($result->getPointChangeMap());
    }

    function leave(Round $round) {
    }
    //endregion
}