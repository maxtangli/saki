<?php

namespace Saki\Game;

class Round {
    private $wall;
    private $playerList;
    private $dealerPlayer;
    private $playerAreas;
    private $turnManager;

    function __construct(Wall $wall, PlayerList $playerList, $dealerPlayer) {
        $this->wall = $wall;
        $this->playerList = $playerList;
        $this->dealerPlayer = $dealerPlayer;

        $this->playerAreas = array_map(function ($v) {
            return new PlayerArea();
        }, range(1, count($playerList)));
        $this->turnManager = new TurnManager($playerList->toArray(), $dealerPlayer);

        $this->init();
    }

    function getWall() {
        return $this->wall;
    }

    function getPlayerList() {
        return $this->playerList;
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
        return $this->getPlayerAreas()[$this->getPlayerList()->toFirstIndex($player)];
    }

    function getTurnManager() {
        return $this->turnManager;
    }

    private function init() {
        $wall = $this->getWall();
        $playerCount = count($this->getPlayerList());
        $turnManager = $this->getTurnManager();

        for ($i = 0; $i < 4; ++$i) {
            for ($cnt = 0; $cnt < $playerCount; ++$cnt) {
                $currentPlayerArea = $this->getPlayerArea($turnManager->getCurrentPlayer());
                $currentPlayerArea->getOnHandTileOrderedList()->addMany($wall->popMany(4));
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

    function getCandidateCommand() {

    }
}

