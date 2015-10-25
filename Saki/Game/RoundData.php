<?php
namespace Saki\Game;

class RoundData {
    // immutable during game
    private $gameData;

    // immutable during round
    private $roundWindData;

    // variable during round
    private $playerList;
    private $turnManager;
    private $tileAreas;

    function __construct() {
        $gameData = new GameData();
        $this->gameData = $gameData;

        $this->roundWindData = new RoundWindData($gameData->getPlayerCount(), $gameData->getTotalRoundType());

        $this->playerList = new PlayerList($gameData->getPlayerCount(), $gameData->getInitialScore());
        $this->turnManager = new TurnManager($this->playerList);
        $wall = new Wall($gameData->getTileSet());
        $this->tileAreas = new TileAreas($wall, $this->playerList, function () {
            return $this->turnManager->getGlobalTurn();
        });
    }

    function reset($keepDealer) {
        if (!is_bool($keepDealer)) {
            throw new \InvalidArgumentException('bool expected.');
        }

        $this->getRoundWindData()->reset($keepDealer);

        $currentDealer = $this->getPlayerList()->getDealerPlayer();
        $nextDealer = $keepDealer ? $currentDealer : $this->getTurnManager()->getOffsetPlayer(1, $currentDealer);
        $this->getPlayerList()->reset($nextDealer);
        $this->getTurnManager()->reset();
        $this->getTileAreas()->reset();
    }

    function getGameData() {
        return $this->gameData;
    }

    function getRoundWindData() {
        return $this->roundWindData;
    }

    function getPlayerList() {
        return $this->playerList;
    }

    function getTileAreas() {
        return $this->tileAreas;
    }

    function getTurnManager() {
        return $this->turnManager;
    }
}