<?php
namespace Saki\Phase;

use Saki\FinalPoint\FinalPointStrategyTarget;
use Saki\Game\Phase;
use Saki\Game\Round;
use Saki\Win\Result\Result;

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
     * @return Result
     */
    function getResult() {
        return $this->result;
    }

    /**
     * @param Round $round
     * @return bool
     */
    function isGameOver(Round $round) { // todo refactor into simpler ver
        $pointList = $round->getAreas()->getPointHolder()->getPointList();
        $prevailingCurrent = $round->getPrevailingCurrent();

        if ($pointList->hasMinus()) {
            return true;
        }

        if ($prevailingCurrent->isNormalNotLast()) {
            return false;
        }

        if ($prevailingCurrent->isSuddenDeathLast()) {
            return true; // todo right? write detailed rule doc
        } // else isNormalLast or isSuddenDeath

        if ($pointList->hasTiledTop()) {
            return false;
        }

        $topItem = $pointList->getSingleTop();
        $isTopEnoughPoint = $topItem->getPoint() >= 30000; // todo wrap in rule class
        if (!$isTopEnoughPoint) {
            return false;
        } // else isTopPlayerEnoughPoint

        // todo
        $isDealerTop = $topItem->getSeatWind()->isDealer();
        $result = $round->getPhaseState()->getResult();
        return !($result->isKeepDealer()) || $isDealerTop;
    }

    /**
     * @param Round $round
     * @return \Saki\Win\Point\PointList
     */
    function getFinalScore(Round $round) {
        if (!$this->isGameOver($round)) {
            throw new \InvalidArgumentException('Game is not over.');
        }

        $scoreStrategy = $round->getGameData()->getScoreStrategy();
        $raw = $round->getAreas()->getPointHolder()->getPointList();
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
        $areas = $round->getAreas();

        // modify points
        $areas->getPointHolder()->applyPointChangeMap($result->getPointChangeMap());
    }

    function leave(Round $round) {
    }
    //endregion
}