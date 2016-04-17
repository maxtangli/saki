<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Win\Result\Result;

class OverPhaseState extends PhaseState {
    private $Result;

    function __construct(Result $Result) {
        $this->Result = $Result;
    }

    function getResult() {
        return $this->Result;
    }

    function isGameOver(Round $round) {
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
        // modify points
        foreach ($round->getPlayerList() as $player) {
            /** @var Player $player */
            $player = $player;
            $afterPoint = $result->getPointDelta($player)->getAfter();
            $player->getArea()->setPoint($afterPoint);
        }
        // clear accumulatedReachCount if isWin
        if ($result->getResultType()->isWin()) {
            // todo added to player already or not?
            $round->getAreas()->setReachPoints(0);
        }
    }

    function leave(Round $round) {
    }
    //endregion
}