<?php
namespace Saki\Command;

use Saki\Game\Area;
use Saki\Game\Round;

/**
 * @package Saki\Command
 */
abstract class PrivateCommand extends PlayerCommand {
    //region PlayerCommand impl
    protected static function matchPhase(Round $round, Area $actorArea) {
        return $round->getPhaseState()->getPhase()
            ->isPrivate();
    }

    protected static function matchActor(Round $round, Area $actorArea) {
        return $actorArea->isCurrentSeatWind();
    }
    //endregion
}