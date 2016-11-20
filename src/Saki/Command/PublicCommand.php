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
        return !$actorArea->isCurrentSeatWind();
    }
    //endregion

    //region PlayCommand override
    protected function matchProvider(Round $round, Area $actorArea) {
        $decider = $round->getPhaseState()->getPublicCommandDecider($round);
        return $decider->allowSubmit($this) || $decider->isDecidedCommand($this);
    }

    protected function executeImpl(Round $round) {
        $decider = $round->getPhaseState()->getPublicCommandDecider($round);

        if ($decider->allowSubmit($this)) {
            $decider->submit($this);
        }

        if ($decider->decided()) {
            if ($decider->isDecidedCommand($this)) {
                $decider->clear();
                return parent::executeImpl($round);
            } else {
                $decider->getDecided()->execute();
            }
        }
    }
    //endregion
}