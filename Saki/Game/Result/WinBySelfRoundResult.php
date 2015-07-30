<?php
namespace Saki\Game\Result;

use Saki\Game\Player;
use Saki\Win\WinAnalyzerResult;

class WinBySelfRoundResult extends WinRoundResult {
    /**
     * @param array $players
     * @param Player $winPlayer
     * @param WinAnalyzerResult $winAnalyzerResult
     * @param int $accumulatedReachCount
     * @param int $selfWindTurn
     */
    function __construct(array $players, Player $winPlayer, WinAnalyzerResult $winAnalyzerResult, $accumulatedReachCount, $selfWindTurn) {
        $losePlayers = array_values(array_filter($players, function (Player $player) use ($winPlayer) {
            return $player != $winPlayer;
        }));
        parent::__construct($players, [$winPlayer], [$winAnalyzerResult], $losePlayers, $accumulatedReachCount, $selfWindTurn);
    }
}

