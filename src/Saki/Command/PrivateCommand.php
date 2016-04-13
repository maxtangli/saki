<?php
namespace Saki\Command;

abstract class PrivateCommand extends PlayerCommand {
    protected function matchPhase(CommandContext $context) {
        return $context->getPhase()->isPrivate();
    }

    protected function matchActor(CommandContext $context) {
        return $context->isActorCurrent();
    }
}