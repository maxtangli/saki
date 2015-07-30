<?php
namespace Saki\Game\Result;

use Saki\Game\Player;
use Saki\Win\WinAnalyzerResult;

class WinByOtherRoundResult extends WinRoundResult {
    /**
     * @param array $players
     * @param Player $winPlayer
     * @param WinAnalyzerResult $winAnalyzerResult
     * @param Player $losePlayer
     * @param int $accumulatedReachCount
     * @param int $selfWindTurn
     */
    function __construct(array $players, Player $winPlayer, WinAnalyzerResult $winAnalyzerResult, Player $losePlayer, $accumulatedReachCount, $selfWindTurn) {
        parent::__construct($players, [$winPlayer], [$winAnalyzerResult], [$losePlayer], $accumulatedReachCount, $selfWindTurn);
    }
}