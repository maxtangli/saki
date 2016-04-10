<?php
namespace Saki\Command;

abstract class PrivateCommand extends PlayerCommand {
    function matchPhase() {
        return $this->getPhase()->isPrivate();
    }

    function matchActor() {
        return $this->isCurrentPlayer();
    }
}