<?php
namespace Saki\RoundResult;

use Saki\Game\Player;
use Saki\Util\Utils;
use Saki\Win\WinAnalyzerResult;

class WinRoundResult extends RoundResult {
    private $players;
    private $winPlayers;
    private $winAnalyzerResults;
    private $losePlayers;
    private $accumulatedReachCount;
    private $selfWindTurn;

    /**
     * @param Player[] $players
     * @param Player[] $winPlayers
     * @param WinAnalyzerResult[] $winAnalyzerResults
     * @param Player[] $losePlayers
     * @param int $accumulatedReachCount
     * @param int $selfWindTurn
     */
    function __construct(array $players, array $winPlayers, array $winAnalyzerResults, array $losePlayers, $accumulatedReachCount, $selfWindTurn) {
        parent::__construct($players, true);
        $this->players = $players;
        $this->winPlayers = $winPlayers;
        $this->winAnalyzerResults = $winAnalyzerResults;
        $this->losePlayers = $losePlayers;
        $this->accumulatedReachCount = $accumulatedReachCount;
        $this->selfWindTurn = $selfWindTurn;
    }

    function getPlayers() {
        return $this->players;
    }

    function getWinPlayers() {
        return $this->winPlayers;
    }

    function getWinAnalyzerResult(Player $player) {
        $k = array_search($player, $this->winPlayers);
        if ($k === false) {
            throw new \InvalidArgumentException();
        }
        return $this->winAnalyzerResults[$k];
    }

    function getLosePlayers() {
        return $this->losePlayers;
    }

    function getAccumulatedReachCount() {
        return $this->accumulatedReachCount;
    }

    function getSelfWindTurn() {
        return $this->selfWindTurn;
    }

    function getPlayerCount() {
        return count($this->getPlayers());
    }

    function getWinPlayerCount() {
        return count($this->getWinPlayers());
    }

    function getLosePlayerCount() {
        return count($this->getLosePlayers());
    }

    function isWinPlayer(Player $player) {
        return array_search($player, $this->getWinPlayers()) !== false;
    }

    function isLosePlayer(Player $player) {
        return array_search($player, $this->getLosePlayers()) !== false;
    }

    function getReachDeltaInt(Player $player) {
        $totalScore = $this->getAccumulatedReachCount() * 1000;
        if ($this->isWinPlayer($player)) {
            return $totalScore / $this->getWinPlayerCount();
        } else {
            return 0;
        }
    }

    function isWinBySelf() {
        return $this->getWinPlayerCount() == 1 && $this->getLosePlayerCount() == $this->getPlayerCount() - 1;
    }

    function getSelfWindTurnDeltaInt(Player $player) {
        $totalScore = $this->getSelfWindTurn() * 300;
        if ($this->isWinPlayer($player)) {
            return $totalScore / $this->getWinPlayerCount();
        } elseif ($this->isLosePlayer($player)) {
            return -$totalScore / $this->getLosePlayerCount(); // todo all or divide?
        } else {
            return 0;
        }
    }

    function getTableItemDeltaInt(Player $player) {
        $winBySelf = $this->isWinBySelf();
        if ($this->isWinPlayer($player)) {
            $scoreItem = $this->getWinAnalyzerResult($player)->getScoreItem();
            $receiverIsDealer = $player->isDealer();
            return $scoreItem->getReceiveScore($receiverIsDealer, $winBySelf);
        } elseif ($this->isLosePlayer($player)) {
            $totalPayScore = 0;
            foreach ($this->getWinPlayers() as $winPlayer) {
                $scoreItem = $this->getWinAnalyzerResult($winPlayer)->getScoreItem();
                $receiverIsDealer = $winPlayer->isDealer();
                $payerIsDealer = $player->isDealer();
                $payScore = -$scoreItem->getPayScore($receiverIsDealer, $winBySelf, $payerIsDealer);
                $totalPayScore += $payScore;
            }
            return $totalPayScore;
        } else {
            return 0;
        }
    }

    /**
     * @param Player $player
     * @return ScoreDelta
     */
    function getScoreDeltaInt(Player $player) {
        return $this->getTableItemDeltaInt($player)
        + $this->getReachDeltaInt($player)
        + $this->getSelfWindTurnDeltaInt($player);
    }

    /**
     * @return Player
     */
    function isKeepDealer() {
        return Utils::array_any($this->getWinPlayers(), function (Player $player) {
            return $player->isDealer();
        });
    }
}