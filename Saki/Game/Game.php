<?php
namespace Saki\Game;

use Saki\Command\CommandFactory;
use Saki\Tile\TileSet;

class Game {
    private $currentRound;

    function __construct() {
        $this->currentRound = new Round();
    }

    function getCurrentRound() {
        return $this->currentRound;
    }

    function getCommandFactory() {
        return new CommandFactory($this);
    }
}