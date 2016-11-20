<?php
namespace Saki\Command;

use Saki\Game\Area;
use Saki\Game\Round;

/**
 * @package Saki\Command
 */
abstract class PublicCommand extends PlayerCommand {
    //region PlayerCommand impl
    //endregion

    //region PlayCommand override
    protected function matchProvider(Round $round, Area $actorArea) {
        $decider = $round->getPhaseState()->getPublicCommandDecider($round);
        return $decider->allowSubmit($this) || $decider->isDecidedCommand($this);
    }

    protected function executeImpl(Round $round) {
        return parent::executeImpl($round);

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