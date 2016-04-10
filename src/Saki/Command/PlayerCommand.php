<?php
namespace Saki\Command;

use Saki\Game\SeatWind;

abstract class PlayerCommand extends Command {
    // todo constructor validate?
    function getPhase() {
        return $this->getContext()->getRound()->getPhaseState()->getPhase();
    }

    function getActor() {
        return new SeatWind($this->getParam(0));
    }

    /**
     * @return Tile
     */
    function getActSeatWindTile() { // todo remove
        return $this->getActor()->getWindTile();
    }

    function getActPlayer() { // todo remove
        return $this->getContext()->getRound()->getPlayerList()->getSeatWindTilePlayer($this->getActSeatWindTile());
    }

    function getCurrentPlayer() { // todo remove
        return $this->getContext()->getRound()->getAreas()->tempGetCurrentPlayer();
    }

    function isCurrentPlayer() { // todo remove
        return $this->getActPlayer() == $this->getCurrentPlayer();
    }

    //region override Command
    function getContext() {
        $context = parent::getContext();
        $context->bindActor($this->getActor());
        return $context;
    }

    function executable() {
        return $this->matchPhase() && $this->matchActor() && $this->matchOther();
    }

    function execute() {
        parent::execute();
        $this->getContext()->unbindActor();
    }
    //endregion

    //region subclass hooks
    abstract function matchPhase();

    abstract function matchActor();

    abstract function matchOther();
    //endregion
}