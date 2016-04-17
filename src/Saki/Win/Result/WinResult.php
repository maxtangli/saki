<?php
namespace Saki\Win\Result;

use Saki\Game\Player;
use Saki\Game\PlayerType;
use Saki\Game\SeatWind;
use Saki\Util\ArrayList;
use Saki\Win\WinReport;
use Saki\Win\WinState;

class WinResult extends Result {
    // note: createXXX()'s param check is not fully strict since it seems no harm

    /**
     * @param Player[] $players
     * @param Player $winPlayer
     * @param WinReport $winReport
     * @param int $accumulatedReachCount
     * @param int $seatWindTurn
     * @return WinResult
     */
    static function createTsumo(array $players, Player $winPlayer, WinReport $winReport, $accumulatedReachCount, $seatWindTurn) {
        if ($winReport->getWinState() != WinState::create(WinState::WIN_BY_SELF)) {
            throw new \InvalidArgumentException();
        }
        $losePlayers = array_values(array_filter($players, function (Player $player) use ($winPlayer) {
            return $player != $winPlayer;
        }));
        return new self($players, [$winPlayer], [$winReport], $losePlayers, $accumulatedReachCount, $seatWindTurn,
            ResultType::create(ResultType::WIN_BY_SELF));
    }

    /**
     * @param Player[] $players
     * @param Player $winPlayer
     * @param WinReport $winReport
     * @param Player $losePlayer
     * @param int $accumulatedReachCount
     * @param int $seatWindTurn
     * @return WinResult
     */
    static function createRon(array $players, Player $winPlayer, WinReport $winReport, Player $losePlayer, $accumulatedReachCount, $seatWindTurn) {
        if ($winReport->getWinState() != WinState::create(WinState::WIN_BY_OTHER)) {
            throw new \InvalidArgumentException();
        }
        return new self($players, [$winPlayer], [$winReport], [$losePlayer], $accumulatedReachCount, $seatWindTurn,
            ResultType::create(ResultType::WIN_BY_OTHER));
    }

    /**
     * @param Player[] $players
     * @param Player[] $winPlayers
     * @param WinResult[] $winReports
     * @param Player $losePlayer
     * @param int $accumulatedReachCount
     * @param int $seatWindTurn
     * @return WinResult
     */
    static function createMultiRon(array $players, array $winPlayers, array $winReports, Player $losePlayer, $accumulatedReachCount, $seatWindTurn) {
        foreach ($winReports as $winResult) {
            if ($winResult->getWinState() != WinState::create(WinState::WIN_BY_OTHER)) {
                throw new \InvalidArgumentException();
            }
        }

        $winPlayerCount = count($winPlayers);
        if (!in_array($winPlayerCount, [2, 3])) {
            throw new \InvalidArgumentException();
        }

        $winTypeValue = $winPlayerCount == 2 ? ResultType::DOUBLE_WIN_BY_OTHER : ResultType::TRIPLE_WIN_BY_OTHER;
        $winType = ResultType::create($winTypeValue);

        return new self($players, $winPlayers, $winReports, [$losePlayer], $accumulatedReachCount, $seatWindTurn, $winType);
    }

    private $players;
    private $winPlayers;
    private $winAnalyzerResults;
    private $losePlayers;
    private $accumulatedReachCount;
    private $seatWindTurn;

    /**
     * @param Player[] $players
     * @param Player[] $winPlayers
     * @param WinResult[] $winResults
     * @param Player[] $losePlayers
     * @param int $accumulatedReachCount
     * @param int $seatWindTurn
     * @param ResultType $winType
     */
    function __construct(array $players, array $winPlayers, array $winResults, array $losePlayers, $accumulatedReachCount, $seatWindTurn, ResultType $winType) {
        if (!$winType->isWin()) {
            throw new \InvalidArgumentException();
        }

        parent::__construct($players, $winType);
        $this->players = $players;
        $this->winPlayers = $winPlayers;
        $this->winAnalyzerResults = $winResults;
        $this->losePlayers = $losePlayers;
        $this->accumulatedReachCount = $accumulatedReachCount;
        $this->seatWindTurn = $seatWindTurn;
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

    function getSeatWindTurn() {
        return $this->seatWindTurn;
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
        $totalPoint = $this->getAccumulatedReachCount() * 1000;
        if ($this->isWinPlayer($player)) {
            return $totalPoint / $this->getWinPlayerCount();
        } else {
            return 0;
        }
    }

    function getSeatWindTurnDeltaInt(Player $player) {
        // each winPlayer get totalPoint, which was undertaken by each lostPlayer.
        $totalPoint = $this->getSeatWindTurn() * 300;
        if ($this->isWinPlayer($player)) {
            return $totalPoint;
        } elseif ($this->isLosePlayer($player)) {
            return -$totalPoint * $this->getWinPlayerCount() / $this->getLosePlayerCount();
        } else {
            return 0;
        }
    }

    function getTableItemDeltaInt(Player $player) {
        $isTsumo = $this->getResultType()->getValue() == ResultType::WIN_BY_SELF;
        if ($this->isWinPlayer($player)) {
            $pointItem = $this->getWinAnalyzerResult($player)->getPointItem();
            $receiverIsDealer = $player->getArea()->getSeatWind()->isDealer();
            return $pointItem->getReceivePoint($receiverIsDealer, $isTsumo);
        } elseif ($this->isLosePlayer($player)) {
            $totalPayPoint = 0;
            foreach ($this->getWinPlayers() as $winPlayer) {
                $pointItem = $this->getWinAnalyzerResult($winPlayer)->getPointItem();
                $receiverIsDealer = $winPlayer->getArea()->getSeatWind()->isDealer();
                $payerIsDealer = $player->getArea()->getSeatWind()->isDealer();
                $payPoint = -$pointItem->getPayPoint($receiverIsDealer, $isTsumo, $payerIsDealer);
                $totalPayPoint += $payPoint;
            }
            return $totalPayPoint;
        } else {
            return 0;
        }
    }

    /**
     * @param Player $player
     * @return PointDelta
     */
    function getPointDeltaInt(Player $player) {
        return $this->getTableItemDeltaInt($player)
        + $this->getReachDeltaInt($player)
        + $this->getSeatWindTurnDeltaInt($player);
    }

    /**
     * @return Player
     */
    function isKeepDealer() {
        $winPlayers = new ArrayList($this->getWinPlayers());
        return $winPlayers->any(function (Player $player) {
            return $player->getArea()->getSeatWind()->isDealer();
        });
    }
}