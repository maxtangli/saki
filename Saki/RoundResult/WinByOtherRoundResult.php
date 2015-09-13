<?php
namespace Saki\RoundResult;

use Saki\Game\Player;
use Saki\Win\WinResult;

class WinByOtherRoundResult extends WinRoundResult {
    /**
     * @param array $players
     * @param Player $winPlayer
     * @param WinResult $winAnalyzerResult
     * @param Player $losePlayer
     * @param int $accumulatedReachCount
     * @param int $selfWindTurn
     */
    function __construct(array $players, Player $winPlayer, WinResult $winAnalyzerResult, Player $losePlayer, $accumulatedReachCount, $selfWindTurn) {
        parent::__construct($players, [$winPlayer], [$winAnalyzerResult], [$losePlayer], $accumulatedReachCount, $selfWindTurn);
    }
}