<?php
namespace Saki\Command;

abstract class PrivateCommand extends PlayerCommand {
    function matchRequiredPhases() {
        return $this->getContext()->getRound()->getRoundPhase()->isPrivate();
    }

    function matchRequiredPlayer() {
        return $this->getContext()->getRound()->getCurrentPlayer()->getSelfWind() == $this->getPlayerSelfWind();
    }
}