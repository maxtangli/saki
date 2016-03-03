<?php
namespace Saki\Game;

use Saki\FinalScore\FinalScoreStrategyTarget;
use Saki\RoundPhase\NullPhaseState;
use Saki\RoundPhase\RoundPhaseState;
use Saki\Win\WinAnalyzer;
use Saki\Win\WinTarget;

class RoundData {
    // immutable during game
    private $gameData;
    private $winAnalyzer;

    // immutable during round
    private $roundWindData;

    // variable during round
    private $playerList;
    private $turnManager;
    private $tileAreas;
    /** @var RoundPhaseState */
    private $phaseState;

    function __construct() {
        $gameData = new GameData();
        $this->gameData = $gameData;
        $this->winAnalyzer = new WinAnalyzer($gameData->getYakuSet());

        $this->roundWindData = new RoundWindData($gameData->getPlayerCount(), $gameData->getTotalRoundType());

        $this->playerList = new PlayerList($gameData->getPlayerCount(), $gameData->getInitialScore());
        $this->turnManager = new TurnManager($this->playerList);
        $wall = new Wall($gameData->getTileSet());
        $this->tileAreas = new TileAreas($wall, $this->playerList, function () {
            return $this->turnManager->getRoundTurn();
        });

        $this->phaseState = new NullPhaseState();
    }

    function reset($keepDealer) {
        if (!is_bool($keepDealer)) {
            throw new \InvalidArgumentException('bool expected.');
        }

        $this->getRoundWindData()->reset($keepDealer);

        $currentDealer = $this->getPlayerList()->getDealerPlayer();
        $nextDealer = $keepDealer ? $currentDealer : $this->getTurnManager()->getOffsetPlayer(1, $currentDealer);
        $this->getPlayerList()->reset($nextDealer);

        $this->getTurnManager()->reset();
        $this->getTileAreas()->reset();

        $this->phaseState = new NullPhaseState();
    }

    function debugReset(RoundDebugResetData $resetData) {
        $this->getRoundWindData()->debugReset($resetData->getRoundWind(), $resetData->getRoundWindTurn(), $resetData->getSelfWindTurn());

        $dealer = $this->getPlayerList()->getSelfWindPlayer($resetData->getDealerWind());
        $this->getPlayerList()->reset($dealer);

        $this->getTurnManager()->reset();
        $this->getTileAreas()->reset();

        $this->phaseState = new NullPhaseState();
    }

    function getGameData() {
        return $this->gameData;
    }

    function getWinAnalyzer() {
        return $this->winAnalyzer;
    }

    function getWinResult(Player $player) {
        // WinTarget will assert valid player
        return $this->getWinAnalyzer()->analyzeTarget(new WinTarget($player, $this));
    }

    function getRoundWindData() {
        return $this->roundWindData;
    }

    function getPlayerList() {
        return $this->playerList;
    }

    function getTileAreas() {
        return $this->tileAreas;
    }

    function getTurnManager() {
        return $this->turnManager;
    }

    /**
     * @return \Saki\RoundPhase\RoundPhaseState|\Saki\RoundPhase\OverPhaseState
     */
    function getPhaseState() {
        return $this->phaseState;
    }

    function toNextPhase(RoundPhaseState $customPhaseState = null) {
        if ($customPhaseState !== null) {
            $this->phaseState->setCustomNextState($customPhaseState);
        }

        $this->phaseState->leave($this);
        $this->phaseState = $this->phaseState->getNextState($this);
        $this->phaseState->enter($this);
    }

    // todo move judge logic into GameData
    function isGameOver() {
        if (!$this->getPhaseState()->getRoundPhase()->isOver()) {
            return false;
        }

        if ($this->getPlayerList()->hasMinusScorePlayer()) { // 有玩家被打飞，游戏结束
            return true;
        } elseif ($this->getRoundWindData()->isFinalRound()) { // 北入终局，游戏结束
            return true;
        } elseif (!$this->getRoundWindData()->isLastOrExtraRound()) { // 指定场数未达，游戏未结束
            return false;
        } else { // 达到指定场数
            $topPlayers = $this->getPlayerList()->getTopPlayers();
            if (count($topPlayers) != 1) {
                return false; // 并列第一，游戏未结束
            }

            $topPlayer = $topPlayers[0];
            $isTopPlayerEnoughScore = $topPlayer->getScore() >= 30000; // todo rule
            if (!$isTopPlayerEnoughScore) { // 若首位点数未达原点，游戏未结束
                return false;
            } else { // 首位点数达到原点，非连庄 或 连庄者达首位，游戏结束
                $result = $this->getPhaseState()->getRoundResult();
                $keepDealer = $result->isKeepDealer();
                $dealerIsTopPlayer = $this->getPlayerList()->getDealerPlayer() == $topPlayer;
                return (!$keepDealer || $dealerIsTopPlayer);
            }
        }
    }

    /**
     * @param bool $requireGameOver
     * @return \Saki\FinalScore\FinalScoreItem[]
     */
    function getFinalScoreItems($requireGameOver = true) {
        if ($requireGameOver && !$this->isGameOver()) {
            throw new \InvalidArgumentException('Game is not over.');
        }

        $target = new FinalScoreStrategyTarget($this->getPlayerList());
        return $this->getGameData()->getFinalScoreStrategy()->getFinalScoreItems($target);
    }

    function toNextRound() {
        if (!$this->getPhaseState()->getRoundPhase()->isOver()) {
            throw new \InvalidArgumentException('Not over phase.');
        }

        if ($this->isGameOver()) {
            throw new \InvalidArgumentException('Game is over.');
        }

        $keepDealer = $this->getPhaseState()->getRoundResult()->isKeepDealer();
        $this->reset($keepDealer);

//        $this->toInitPhase();
        $this->toNextPhase();
        $this->toNextPhase();
    }
}

