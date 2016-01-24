<?php
namespace Saki\Command;

use Saki\Command\ParamDeclaration\SelfWindParamDeclaration;

abstract class PlayerCommand extends Command {
    // todo constructor validate?

    /**
     * @return Tile
     */
    function getPlayerSelfWind() {
        return $this->getParam(0);
    }

    function executable() {
        return $this->matchRequiredPhases() && $this->matchRequiredPlayer() && $this->matchOtherConditions();
    }

    abstract function matchRequiredPhases();

    abstract function matchRequiredPlayer();

    abstract function matchOtherConditions();
}