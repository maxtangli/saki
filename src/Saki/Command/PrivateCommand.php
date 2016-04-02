<?php
namespace Saki\Command;

abstract class PrivateCommand extends PlayerCommand {
    function matchRequiredPhases() {
        return $this->getRoundPhase()->isPrivate();
    }

    function matchRequiredPlayer() {
        return $this->isCurrentPlayer();
    }
}