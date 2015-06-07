<?php

namespace Saki\Game;

class Round {
    private $wall;
    private $players;
    private $dealerPlayer;
    private $playerAreas;
    private $turnManager;

    function __construct(Wall $wall, array $players, $dealerPlayer) {
        if (array_search($dealerPlayer, $players, true) === false) {
            throw new \InvalidArgumentException("Invalid \$dealerPlayer[$dealerPlayer].");
        }
        $this->wall = $wall;
        $this->players = $players;
        $this->dealerPlayer = $dealerPlayer;

        $this->playerAreas = array_map(function ($v) {
            return new PlayerArea();
        }, $players);
        $this->turnManager = new TurnManager($players, $dealerPlayer);

        $this->init();
    }

    function getWall() {
        return $this->wall;
    }

    function getPlayers() {
        return $this->players;
    }

    function getPlayerCount() {
        return count($this->getPlayers());
    }

    protected function getPlayerIndex($player) {
        return array_search($player, $this->getPlayers(), true);
    }

    function getDealerPlayer() {
        return $this->dealerPlayer;
    }

    /**
     * @return PlayerArea[]
     */
    function getPlayerAreas() {
        return $this->playerAreas;
    }

    /**
     * @param $player
     * @return PlayerArea
     */
    function getPlayerArea($player) {
        return $this->getPlayerAreas()[$this->getPlayerIndex($player)];
    }

    function getTurnManager() {
        return $this->turnManager;
    }

    private function init() {
        $wall = $this->getWall();
        $playerCount = $this->getPlayerCount();
        $turnManager = $this->getTurnManager();

        for ($i = 0; $i < 4; ++$i) {
            for ($cnt = 0; $cnt < $playerCount; ++$cnt) {
                $this->getPlayerArea($turnManager->getCurrentPlayer())->getOnHandTileOrderedList()->addMany(
                    $wall->popMany(4)
                );
                $turnManager->toNextPlayer(false);
            }
        }

        if ($turnManager->getCurrentPlayer() !== $this->getDealerPlayer()) {
            throw new \LogicException();
        }
        $this->getPlayerArea($this->getDealerPlayer())->getOnHandTileOrderedList()->add(
            $wall->pop()
        );
    }
}

