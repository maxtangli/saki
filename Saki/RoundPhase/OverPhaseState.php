<?php
namespace Saki\RoundPhase;

use Saki\Game\Player;
use Saki\Game\RoundData;
use Saki\Game\RoundPhase;
use Saki\RoundResult\RoundResult;

class OverPhaseState extends RoundPhaseState {
    private $roundResult;

    function __construct(RoundResult $roundResult) {
        $this->roundResult = $roundResult;
    }

    function getRoundResult() {
        return $this->roundResult;
    }

    function getRoundPhase() {
        return RoundPhase::getOverInstance();
    }

    function getDefaultNextState(RoundData $roundData) {
        throw new \LogicException('No nextState exists in OverPhaseState.');
    }

    function enter(RoundData $roundData) {
        $result = $this->getRoundResult();
        // modify scores
        $roundData->getPlayerList()->walk(function (Player $player) use ($result) {
            $afterScore = $result->getScoreDelta($player)->getAfter();
            $player->setScore($afterScore);
        });
        // clear accumulatedReachCount if isWin
        if ($result->getRoundResultType()->isWin()) {
            $roundData->getTileAreas()->setAccumulatedReachCount(0);
        }
    }

    function leave(RoundData $roundData) {

    }
}