<?php
namespace Saki\RoundPhase;

use Saki\Game\Player;
use Saki\Game\RoundData;
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

    function isGameOver(RoundData $roundData) {
        if ($roundData->getPlayerList()->hasMinusScorePlayer()) { // 有玩家被打飞，游戏结束
            return true;
        } elseif ($roundData->getRoundWindData()->isFinalRound()) { // 北入终局，游戏结束
            return true;
        } elseif (!$roundData->getRoundWindData()->isLastOrExtraRound()) { // 指定场数未达，游戏未结束
            return false;
        } else { // 达到指定场数
            $topPlayers = $roundData->getPlayerList()->getTopPlayers();
            if (count($topPlayers) != 1) {
                return false; // 并列第一，游戏未结束
            }

            $topPlayer = $topPlayers[0];
            $isTopPlayerEnoughScore = $topPlayer->getScore() >= 30000; // todo rule
            if (!$isTopPlayerEnoughScore) { // 若首位点数未达原点，游戏未结束
                return false;
            } else { // 首位点数达到原点，非连庄 或 连庄者达首位，游戏结束
                $result = $roundData->getPhaseState()->getRoundResult();
                $keepDealer = $result->isKeepDealer();
                $dealerIsTopPlayer = $roundData->getPlayerList()->getDealerPlayer() == $topPlayer;
                return (!$keepDealer || $dealerIsTopPlayer);
            }
        }
    }

    /**
     * @param RoundData $roundData
     * @return \Saki\FinalScore\FinalScoreItem[]
     */
    function getFinalScoreItems(RoundData $roundData) {
        if (!$this->isGameOver($roundData)) {
            throw new \InvalidArgumentException('Game is not over.');
        }

        $target = new FinalScoreStrategyTarget($roundData->getPlayerList());
        return $roundData->getGameData()->getFinalScoreStrategy()->getFinalScoreItems($target);
    }
    
    function getDefaultNextState(RoundData $roundData) {
        throw new \LogicException('No nextState exists in OverPhaseState.');
    }

    function enter(RoundData $roundData) {
        $result = $this->getRoundResult();
        // modify scores
        $roundData->getPlayerList()->walk(function (Player $player) use ($result) {
            $afterScore = $result->getScoreDelta($player)->getAfter();
            $player->setScore($afterScore);
        });
        // clear accumulatedReachCount if isWin
        if ($result->getRoundResultType()->isWin()) {
            $roundData->getTileAreas()->setAccumulatedReachCount(0);
        }
    }

    function leave(RoundData $roundData) {

    }
}