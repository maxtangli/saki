<?php
namespace Saki\Command;

use Saki\Game\Area;
use Saki\Game\Round;

/**
 * @package Saki\Command
 */
abstract class PublicCommand extends PlayerCommand {
    //region PlayerCommand impl
    protected static function matchPhase(Round $round, Area $actorArea) {
        return $round->getPhaseState()->getPhase()
            ->isPublic();
    }

    protected static function matchActor(Round $round, Area $actorArea) {
        // todo introduce PublicCommandRoller
        return !$actorArea->isCurrentSeatWind();
    }
    //endregion
}