<?php
namespace Saki\Command;

use Saki\Game\Area;
use Saki\Game\Round;

/**
 * @package Saki\Command
 */
abstract class PublicCommand extends PlayerCommand {
    //region PlayerCommand impl
    protected function matchPhase(Round $round, Area $actorArea) {
        $phaseState = $round->getPhaseState();
        if (!$phaseState->getPhase()->isPublic()) {
            return false;
        }

        if ($phaseState->isRonOnly()) {
            return $this->isRon();
        }

        return true;
    }

    protected function matchActor(Round $round, Area $actorArea) {
        // todo introduce PublicCommandRoller
        return !$actorArea->isCurrentSeatWind();
    }
    //endregion
}