<?php
namespace Saki\Game;

use Saki\Command\CommandFactory;

class Game {
    private $currentRound;

    function __construct($n, $initialScore) {
        $wall = new Wall(Wall::getStandardTileList());
        $wall->init();
        $playerList = new PlayerList(PlayerList::createPlayers($n, $initialScore));
        $dealerPlayer = $playerList[0];
        $this->currentRound = new Round($wall, $playerList, $dealerPlayer);
    }

    function getCurrentRound() {
        return $this->currentRound;
    }

    function getCommandFactory() {
        return new CommandFactory($this);
    }
}