<?php
namespace Saki\RoundResult;

use Saki\Game\Player;
use Saki\Util\ArrayList;
use Saki\Win\WinResult;
use Saki\Win\WinState;

class WinRoundResult extends RoundResult {
    // note: createXXX()'s param check is not fully strict since it seems no harm

    /**
     * @param Player[] $players
     * @param Player $winPlayer
     * @param WinResult $winResult
     * @param int $accumulatedReachCount
     * @param int $selfWindTurn
     * @return WinRoundResult
     */
    static function createWinBySelf(array $players, Player $winPlayer, WinResult $winResult, $accumulatedReachCount, $selfWindTurn) {
        if ($winResult->getWinState() != WinState::create(WinState::WIN_BY_SELF)) {
            throw new \InvalidArgumentException();
        }
        $losePlayers = array_values(array_filter($players, function (Player $player) use ($winPlayer) {
            return $player != $winPlayer;
        }));
        return new self($players, [$winPlayer], [$winResult], $losePlayers, $accumulatedReachCount, $selfWindTurn,
            RoundResultType::create(RoundResultType::WIN_BY_SELF));
    }

    /**
     * @param Player[] $players
     * @param Player $winPlayer
     * @param WinResult $winResult
     * @param Player $losePlayer
     * @param int $accumulatedReachCount
     * @param int $selfWindTurn
     * @return WinRoundResult
     */
    static function createWinByOther(array $players, Player $winPlayer, WinResult $winResult, Player $losePlayer, $accumulatedReachCount, $selfWindTurn) {
        if ($winResult->getWinState() != WinState::create(WinState::WIN_BY_OTHER)) {
            throw new \InvalidArgumentException();
        }
        return new self($players, [$winPlayer], [$winResult], [$losePlayer], $accumulatedReachCount, $selfWindTurn,
            RoundResultType::create(RoundResultType::WIN_BY_OTHER));
    }

    /**
     * @param Player[] $players
     * @param Player[] $winPlayers
     * @param WinResult[] $winResults
     * @param Player $losePlayer
     * @param int $accumulatedReachCount
     * @param int $selfWindTurn
     * @return WinRoundResult
     */
    static function createMultiWinByOther(array $players, array $winPlayers, array $winResults, Player $losePlayer, $accumulatedReachCount, $selfWindTurn) {
        foreach ($winResults as $winResult) {
            if ($winResult->getWinState() != WinState::create(WinState::WIN_BY_OTHER)) {
                throw new \InvalidArgumentException();
            }
        }

        $winPlayerCount = count($winPlayers);
        if (!in_array($winPlayerCount, [2, 3])) {
            throw new \InvalidArgumentException();
        }

        $winTypeValue = $winPlayerCount == 2 ? RoundResultType::DOUBLE_WIN_BY_OTHER : RoundResultType::TRIPLE_WIN_BY_OTHER;
        $winType = RoundResultType::create($winTypeValue);

        return new self($players, $winPlayers, $winResults, [$losePlayer], $accumulatedReachCount, $selfWindTurn, $winType);
    }

    private $players;
    private $winPlayers;
    private $winAnalyzerResults;
    private $losePlayers;
    private $accumulatedReachCount;
    private $selfWindTurn;

    /**
     * @param Player[] $players
     * @param Player[] $winPlayers
     * @param WinResult[] $winResults
     * @param Player[] $losePlayers
     * @param int $accumulatedReachCount
     * @param int $selfWindTurn
     * @param RoundResultType $winType
     */
    function __construct(array $players, array $winPlayers, array $winResults, array $losePlayers, $accumulatedReachCount, $selfWindTurn, RoundResultType $winType) {
        if (!$winType->isWin()) {
            throw new \InvalidArgumentException();
        }

        parent::__construct($players, $winType);
        $this->players = $players;
        $this->winPlayers = $winPlayers;
        $this->winAnalyzerResults = $winResults;
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

    function getSelfWindTurnDeltaInt(Player $player) {
        // each winPlayer get totalScore, which was undertaken by each lostPlayer.
        $totalScore = $this->getSelfWindTurn() * 300;
        if ($this->isWinPlayer($player)) {
            return $totalScore;
        } elseif ($this->isLosePlayer($player)) {
            return -$totalScore * $this->getWinPlayerCount() / $this->getLosePlayerCount();
        } else {
            return 0;
        }
    }

    function getTableItemDeltaInt(Player $player) {
        $isWinBySelf = $this->getRoundResultType()->getValue() == RoundResultType::WIN_BY_SELF;
        if ($this->isWinPlayer($player)) {
            $scoreItem = $this->getWinAnalyzerResult($player)->getScoreItem();
            $receiverIsDealer = $player->getTileArea()->getPlayerWind()->isDealer();
            return $scoreItem->getReceiveScore($receiverIsDealer, $isWinBySelf);
        } elseif ($this->isLosePlayer($player)) {
            $totalPayScore = 0;
            foreach ($this->getWinPlayers() as $winPlayer) {
                $scoreItem = $this->getWinAnalyzerResult($winPlayer)->getScoreItem();
                $receiverIsDealer = $winPlayer->getTileArea()->getPlayerWind()->isDealer();
                $payerIsDealer = $player->getTileArea()->getPlayerWind()->isDealer();
                $payScore = -$scoreItem->getPayScore($receiverIsDealer, $isWinBySelf, $payerIsDealer);
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
        $winPlayers = new ArrayList($this->getWinPlayers());
        return $winPlayers->any(function (Player $player) {
            return $player->getTileArea()->getPlayerWind()->isDealer();
        });
    }
}