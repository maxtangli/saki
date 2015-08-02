<?php
namespace Saki\RoundResult;

use Saki\Game\Player;
use Saki\Win\WinAnalyzerResult;

class MultiWinByOtherRoundResult extends WinRoundResult {
    /**
     * @param array $players
     * @param Player[] $winPlayers
     * @param WinAnalyzerResult[] $winAnalyzerResults
     * @param Player $losePlayer
     * @param int $accumulatedReachCount
     * @param int $selfWindTurn
     */
    function __construct(array $players, array $winPlayers, array $winAnalyzerResults, Player $losePlayer, $accumulatedReachCount, $selfWindTurn) {
        parent::__construct($players, $winPlayers, $winAnalyzerResults, [$losePlayer], $accumulatedReachCount, $selfWindTurn);
    }
}