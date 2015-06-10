<?php
namespace Saki\Game;

class Game {
    private $currentRound;

    function __construct($n, $initialScore) {
        $playerList = new PlayerList(PlayerList::createPlayers($n, $initialScore));
        $wall = new Wall(Wall::getStandardTileList());
        $dealerPlayer = $playerList[0];
        $this->currentRound = new Round($wall, $playerList, $dealerPlayer);
    }

    function getCurrentRound() {
        return $this->currentRound;
    }
}