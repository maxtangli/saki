<?php
namespace Saki\Command;

use Saki\Game\Area;
use Saki\Game\Round;

/**
 * @package Saki\Command
 */
abstract class PrivateCommand extends PlayerCommand {
    //region PlayerCommand impl
    protected function matchPhase(Round $round, Area $actorArea) {
        $phaseState = $round->getPhaseState();
        return $phaseState->getPhase()->isPrivate();
    }

    protected function matchActor(Round $round, Area $actorArea) {
        return $actorArea->isCurrentSeatWind();
    }
    //endregion
}