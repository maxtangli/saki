<?php
namespace Saki\Phase;

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
        $pointFacade = $round->getAreas()->getPointFacade();
        $prevailingCurrent = $round->getPrevailingCurrent();

        if ($pointFacade->hasMinus()) {
            return true;
        }

        if ($prevailingCurrent->isNormalNotLast()) {
            return false;
        }

        if ($prevailingCurrent->isSuddenDeathLast()) {
            return true; // todo right? write detailed rule doc
        } // else isNormalLast or isSuddenDeath

        if ($pointFacade->hasTiledTop()) {
            return false;
        }

        $topItem = $pointFacade->getSingleTop();
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
     * @return \Saki\FinalPoint\FinalPointItem[]
     */
    function getFinalPointItems(Round $round) {
        if (!$this->isGameOver($round)) {
            throw new \InvalidArgumentException('Game is not over.');
        }

        $target = new FinalPointStrategyTarget($round->getPlayerList());
        return $round->getGameData()->getFinalPointStrategy()->getFinalPointItems($target);
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
        $areas->applyPointChangeMap($result->getPointChangeMap());

        // clear accumulatedReachCount if isWin
        if ($result->getResultType()->isWin()) {
            $areas->setReachPoints(0);
        }
    }

    function leave(Round $round) {
    }
    //endregion
}