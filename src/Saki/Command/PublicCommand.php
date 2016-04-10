<?php
namespace Saki\Command;

abstract class PublicCommand extends PlayerCommand {
    function matchPhase() {
        $phaseState = $this->getContext()->getRound()->getPhaseState();
        if (!$phaseState->getPhase()->isPublic()) {
            return false;
        }

        if ($phaseState->isRobQuad()) {
            return $this->isWinByOther();
        }

        return true;
    }

    function matchActor() {
        // todo introduce PublicCommandRoller
        return !$this->isCurrentPlayer();
    }
}