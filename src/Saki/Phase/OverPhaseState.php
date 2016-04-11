<?php
namespace Saki\Phase;

use Saki\Game\Phase;
use Saki\Game\Player;
use Saki\Game\Round;
use Saki\RoundResult\RoundResult;

class OverPhaseState extends PhaseState {
    private $roundResult;

    function __construct(RoundResult $roundResult) {
        $this->roundResult = $roundResult;
    }

    function getRoundResult() {
        return $this->roundResult;
    }

    function getPhase() {
        return Phase::getOverInstance();
    }

    function isGameOver(Round $round) {
        $playerList = $round->getPlayerList();
        $prevailingCurrent = $round->getPrevailingCurrent();

        if ($playerList->hasMinusPointPlayer()) {
            return true;
        }

        if ($prevailingCurrent->isNormalNotLast()) {
            return false;
        }

        if ($prevailingCurrent->isSuddenDeathLast()) {
            return true; // todo right? write detailed rule doc
        } // else isNormalLast or isSuddenDeath

        if ($playerList->areTiledForTop()) {
            return false;
        }

        $topPlayer = $playerList->getSingleTopPlayer();
        $isTopPlayerEnoughPoint = $topPlayer->getArea()->getPoint() >= 30000; // todo wrap in rule class
        if (!$isTopPlayerEnoughPoint) {
            return false;
        } // else isTopPlayerEnoughPoint

        $result = $round->getPhaseState()->getRoundResult();
        $keepDealer = $result->isKeepDealer();
        $dealerIsTopPlayer = $playerList->getEastPlayer() == $topPlayer;
        return (!$keepDealer || $dealerIsTopPlayer);
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

    function getDefaultNextState(Round $round) {
        throw new \LogicException('No nextState exists in OverPhaseState.');
    }

    function enter(Round $round) {
        $result = $this->getRoundResult();
        // modify points
        foreach ($round->getPlayerList() as $player) {
            /** @var Player $player */
            $player = $player;
            $afterPoint = $result->getPointDelta($player)->getAfter();
            $player->getArea()->setPoint($afterPoint);
        }
        // clear accumulatedReachCount if isWin
        if ($result->getRoundResultType()->isWin()) {
            // todo added to player already or not?
            $round->getAreas()->setReachPoints(0);
        }
    }

    function leave(Round $round) {
    }
}