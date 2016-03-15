<?php
namespace Saki\Command;

abstract class PublicCommand extends PlayerCommand {
    function matchRequiredPhases() {
        $phaseState = $this->getContext()->getRoundData()->getPhaseState();
        if (!$phaseState->getRoundPhase()->isPublic()) {
            return false;
        }

        if ($phaseState->isRobQuad()) {
            return $this->isWinByOther();
        }

        return true;
    }

    function matchRequiredPlayer() {
        // todo introduce PublicCommandRoller
        return !$this->isCurrentPlayer();
    }
}