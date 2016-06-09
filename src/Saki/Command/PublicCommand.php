<?php
namespace Saki\Command;

use Saki\Game\Area;

/**
 * @package Saki\Command
 */
abstract class PublicCommand extends PlayerCommand {
    //region PlayerCommand impl
    protected function matchPhase(CommandContext $context, Area $actorArea) {
        $phaseState = $context->getRound()->getAreas()->getPhaseState();
        if (!$phaseState->getPhase()->isPublic()) {
            return false;
        }

        if ($phaseState->isRonOnly()) {
            return $this->isRon();
        }

        return true;
    }

    protected function matchActor(CommandContext $context, Area $actorArea) {
        // todo introduce PublicCommandRoller
        return !$actorArea->isCurrentSeatWind();
    }
    //endregion
}