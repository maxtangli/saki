<?php
namespace Saki\Command;

abstract class PublicCommand extends PlayerCommand {
    function matchRequiredPhases() {
        return $this->getRoundPhase()->isPublic();
    }

    function matchRequiredPlayer() {
        // todo introduce PublicCommandRoller
        return !$this->isCurrentPlayer();
    }
}