<?php
namespace Saki\Command;

abstract class PlayerCommand extends Command {
    // todo constructor validate?

    /**
     * @return Tile
     */
    function getActPlayerSelfWind() {
        return $this->getParam(0);
    }

    function getActPlayer() {
        return $this->getContext()->getRound()->getPlayerList()->getSelfWindPlayer($this->getActPlayerSelfWind());
    }

    function getCurrentPlayer() {
        return $this->getContext()->getRound()->getTurnManager()->getCurrentPlayer();
    }

    function isCurrentPlayer() {
        return $this->getActPlayer() == $this->getCurrentPlayer();
    }

    function getRoundPhase() {
        return $this->getContext()->getRound()->getPhaseState()->getRoundPhase();
    }

    function executable() {
        return $this->matchRequiredPhases() && $this->matchRequiredPlayer() && $this->matchOtherConditions();
    }

    abstract function matchRequiredPhases();

    abstract function matchRequiredPlayer();

    abstract function matchOtherConditions();
}