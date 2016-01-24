<?php
namespace Saki\Command;

abstract class PublicCommand extends PlayerCommand {
    function matchRequiredPhases() {
        return $this->getContext()->getRound()->getRoundPhase()->isPublic();
    }

    function matchRequiredPlayer() {
        // todo introduce PublicCommandRoller
        return $this->getContext()->getRound()->getCurrentPlayer()->getSelfWind() != $this->getPlayerSelfWind();
    }
}