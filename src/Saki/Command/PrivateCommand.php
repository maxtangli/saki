<?php
namespace Saki\Command;

use Saki\Game\Area;

/**
 * @package Saki\Command
 */
abstract class PrivateCommand extends PlayerCommand {
    //region PlayerCommand impl
    protected function matchPhase(CommandContext $context, Area $actorArea) {
        $phaseState = $context->getRound()->getAreas()->getPhaseState();
        return $phaseState->getPhase()->isPrivate();
    }

    protected function matchActor(CommandContext $context, Area $actorArea) {
        return $actorArea->isCurrentSeatWind();
    }
    //endregion
}