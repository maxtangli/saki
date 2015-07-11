<?php
namespace Saki\Game\RoundResult;

use Saki\Game\Player;
use Saki\Win\WinAnalyzerResult;

class WinBySelfRoundResult extends RoundResult {
    private $winPlayer;
    private $winAnalyzerResult;

    function __construct(array $players, Player $winPlayer, WinAnalyzerResult $winAnalyzerResult) {
        parent::__construct($players);
        $this->winPlayer = $winPlayer;
        $this->winAnalyzerResult = $winAnalyzerResult;
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
    function getScoreDeltaInt(Player $player) {
        $scoreItem = $this->getWinAnalyzerResult()->getScoreItem();

        $receiverIsDealer = $this->getWinPlayer()->isDealer();
        $winBySelf = true;
        $payerIsDealer = $player->isDealer();
        $deltaInt = $player == $this->getWinPlayer() ? $scoreItem->getReceiveScore($receiverIsDealer, $winBySelf)
            : -$scoreItem->getPayScore($receiverIsDealer, $winBySelf, $payerIsDealer);
        return $deltaInt;
    }

    /**
     * @return Player
     */
    function getNextDealerPlayer() {
        return $this->getWinPlayer();
    }
}

