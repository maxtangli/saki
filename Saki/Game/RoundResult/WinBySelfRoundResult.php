<?php
namespace Saki\Game\RoundResult;

use Saki\Game\Player;
use Saki\Win\WinAnalyzerResult;

class WinBySelfRoundResult extends RoundResult {
    private $allPlayers;
    private $winPlayer;
    private $winAnalyzerResult;

    function __construct(array $allPlayers, Player $winPlayer, WinAnalyzerResult $winAnalyzerResult) {
        $this->allPlayers = $allPlayers;
        $this->winPlayer = $winPlayer;
        $this->winAnalyzerResult = $winAnalyzerResult;
    }

    function getAllPlayers() {
        return $this->allPlayers;
    }

    function getWinPlayer() {
        return $this->winPlayer;
    }

    function getWinAnalyzerResult() {
        return $this->winAnalyzerResult;
    }

    /**
     * @param Player $player
     * @return ScoreDelta
     */
    function getScoreDeltaInt(Player $player) { // todo
        $delta = $player == $this->getWinPlayer() ? 1200 : -400;
        return $delta;
    }
}