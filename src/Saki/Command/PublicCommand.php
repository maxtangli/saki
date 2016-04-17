<?php
namespace Saki\Command;

abstract class PublicCommand extends PlayerCommand {
    protected function matchPhase(CommandContext $context) {
        $phaseState = $this->getContext()->getRound()->getPhaseState();
        if (!$phaseState->getPhase()->isPublic()) {
            return false;
        }

        if ($phaseState->isRobQuad()) {
            return $this->isRon();
        }

        return true;
    }

    protected function matchActor(CommandContext $context) {
        // todo introduce PublicCommandRoller
        return !$context->isActorCurrent();
    }
}