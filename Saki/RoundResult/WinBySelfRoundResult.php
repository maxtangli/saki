<?php
namespace Saki\RoundResult;

use Saki\Game\Player;
use Saki\Win\WinResult;

class WinBySelfRoundResult extends WinRoundResult {
    /**
     * @param array $players
     * @param Player $winPlayer
     * @param WinResult $winAnalyzerResult
     * @param int $accumulatedReachCount
     * @param int $selfWindTurn
     */
    function __construct(array $players, Player $winPlayer, WinResult $winAnalyzerResult, $accumulatedReachCount, $selfWindTurn) {
        $losePlayers = array_values(array_filter($players, function (Player $player) use ($winPlayer) {
            return $player != $winPlayer;
        }));
        parent::__construct($players, [$winPlayer], [$winAnalyzerResult], $losePlayers, $accumulatedReachCount, $selfWindTurn);
    }
}

