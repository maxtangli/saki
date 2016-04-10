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
        if ($round->getPlayerList()->hasMinusPointPlayer()) { // 有玩家被打飞，游戏结束
            return true;
        } elseif ($round->getPrevailingWindData()->isFinalRound()) { // 北入终局，游戏结束
            return true;
        } elseif (!$round->getPrevailingWindData()->isLastOrExtraRound()) { // 指定场数未达，游戏未结束
            return false;
        } else { // 达到指定场数
            $topPlayers = $round->getPlayerList()->getTopPlayers();
            if (count($topPlayers) != 1) {
                return false; // 并列第一，游戏未结束
            }

            $topPlayer = $topPlayers[0];
            $isTopPlayerEnoughPoint = $topPlayer->getArea()->getPoint() >= 30000; // todo rule
            if (!$isTopPlayerEnoughPoint) { // 若首位点数未达原点，游戏未结束
                return false;
            } else { // 首位点数达到原点，非连庄 或 连庄者达首位，游戏结束
                $result = $round->getPhaseState()->getRoundResult();
                $keepDealer = $result->isKeepDealer();
                $dealerIsTopPlayer = $round->getPlayerList()->getDealerPlayer() == $topPlayer;
                return (!$keepDealer || $dealerIsTopPlayer);
            }
        }
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