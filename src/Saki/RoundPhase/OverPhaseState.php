<?php
namespace Saki\RoundPhase;

use Saki\Game\Player;
use Saki\Game\Round;
use Saki\Game\RoundPhase;
use Saki\RoundResult\RoundResult;

class OverPhaseState extends RoundPhaseState {
    private $roundResult;

    function __construct(RoundResult $roundResult) {
        $this->roundResult = $roundResult;
    }

    function getRoundResult() {
        return $this->roundResult;
    }

    function getRoundPhase() {
        return RoundPhase::getOverInstance();
    }

    function isGameOver(Round $round) {
        if ($round->getPlayerList()->hasMinusScorePlayer()) { // 有玩家被打飞，游戏结束
            return true;
        } elseif ($round->getRoundWindData()->isFinalRound()) { // 北入终局，游戏结束
            return true;
        } elseif (!$round->getRoundWindData()->isLastOrExtraRound()) { // 指定场数未达，游戏未结束
            return false;
        } else { // 达到指定场数
            $topPlayers = $round->getPlayerList()->getTopPlayers();
            if (count($topPlayers) != 1) {
                return false; // 并列第一，游戏未结束
            }

            $topPlayer = $topPlayers[0];
            $isTopPlayerEnoughScore = $topPlayer->getScore() >= 30000; // todo rule
            if (!$isTopPlayerEnoughScore) { // 若首位点数未达原点，游戏未结束
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
     * @return \Saki\FinalScore\FinalScoreItem[]
     */
    function getFinalScoreItems(Round $round) {
        if (!$this->isGameOver($round)) {
            throw new \InvalidArgumentException('Game is not over.');
        }

        $target = new FinalScoreStrategyTarget($round->getPlayerList());
        return $round->getGameData()->getFinalScoreStrategy()->getFinalScoreItems($target);
    }
    
    function getDefaultNextState(Round $round) {
        throw new \LogicException('No nextState exists in OverPhaseState.');
    }

    function enter(Round $round) {
        $result = $this->getRoundResult();
        // modify scores
        foreach($round->getPlayerList() as $player) {
            $afterScore = $result->getScoreDelta($player)->getAfter();
            $player->setScore($afterScore);
        }
        // clear accumulatedReachCount if isWin
        if ($result->getRoundResultType()->isWin()) {
            $round->getTileAreas()->setAccumulatedReachCount(0);
        }
    }

    function leave(Round $round) {

    }
}