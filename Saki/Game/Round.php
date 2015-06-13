<?php

namespace Saki\Game;

use Saki\Tile;
use Saki\Util\Enum;
use Saki\Util\Utils;

class RoundPhase extends Enum {
    const INIT_PHASE = 1;
    const PRIVATE_PHASE = 2;
    const PUBLIC_PHASE = 3;
    const ROUND_OVER_PHASE = 4;

    static function getValue2StringMap() {
        return [
            self::INIT_PHASE => 'init phase',
            self::PRIVATE_PHASE => 'private phase',
            self::PUBLIC_PHASE => 'public phase',
            self::ROUND_OVER_PHASE => 'round-over phase',
        ];
    }
}

class Round {
    private $wall;
    private $playerList;
    private $dealerPlayer;
    private $playerAreas;
    private $turnManager;
    private $roundPhase;

    function __construct(Wall $wall, PlayerList $playerList, $dealerPlayer) {
        $this->wall = $wall;
        $this->playerList = $playerList;
        $this->dealerPlayer = $dealerPlayer;

        $this->playerAreas = array_map(function ($v) {
            return new PlayerArea();
        }, range(1, count($playerList)));
        $this->turnManager = new TurnManager($playerList->toArray(), $dealerPlayer);

        $this->roundPhase = RoundPhase::getInstance(RoundPhase::INIT_PHASE);
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

    protected function getTurnManager() {
        return $this->turnManager;
    }

    /**
     * @return Player
     */
    function getCurrentPlayer() {
        return $this->getTurnManager()->getCurrentPlayer();
    }

    function getCurrentTurn() {
        return $this->getTurnManager()->getCurrentTurn();
    }

    /**
     * @param $player
     * @return PlayerArea
     */
    function getPlayerArea($player) {
        return $this->getPlayerAreas()[$this->getPlayerList()->toFirstIndex($player)];
    }

    /**
     * @return RoundPhase
     */
    protected function getRoundPhase() {
        return $this->roundPhase;
    }

    protected function setRoundPhase($roundPhase) {
        $this->roundPhase = $roundPhase;
    }

    private function init() {
        $wall = $this->getWall();
        $playerCount = count($this->getPlayerList());
        $turnManager = $this->getTurnManager();

        // each player draw 4*4 tiles
        for ($i = 0; $i < 4; ++$i) {
            for ($cnt = 0; $cnt < $playerCount; ++$cnt) {
                $this->getPlayerArea($turnManager->getCurrentPlayer())->getOnHandTileSortedList()->addMany($this->getWall()->popMany(4));
                $turnManager->toNextPlayer(false);
            }
        }

        // dealer player draw 1 candidate tile
        $this->currentPlayerDraw();

        // go to dealer player's private phase
        $this->setRoundPhase(RoundPhase::getInstance(RoundPhase::PRIVATE_PHASE));
    }

    protected function currentPlayerDraw() {
        $this->getPlayerArea($this->getCurrentPlayer())->setCandidateTile($this->getWall()->pop());
    }

    /**
     * @return \Saki\Command\Command[]
     */
    function getCandidateCommands() {
        $candidateCommands = [];

        switch ($this->getRoundPhase()->getValue()) {
            case RoundPhase::PRIVATE_PHASE:
                $currentPlayer = $this->getCurrentPlayer();
                $currentPlayerArea = $this->getPlayerArea($currentPlayer);
                foreach ($currentPlayerArea->getOnHandTileSortedList() as $onHandTile) {
                    $candidateCommands[] = new \Saki\Command\DiscardCommand($this, $currentPlayer, $onHandTile);
                }
                if ($currentPlayerArea->hasCandidateTile()) {
                    $candidateCommands[] = new \Saki\Command\DiscardCommand($this, $currentPlayer, $currentPlayerArea->getCandidateTile());
                }
                $candidateCommands = array_unique($candidateCommands);
                break;
            case RoundPhase::PUBLIC_PHASE:
                break;
            case RoundPhase::ROUND_OVER_PHASE:
                break;
            default:
                throw new \LogicException();
        }

        return $candidateCommands;
    }

    function getCandidateCommand(Player $player) {
        return array_values(array_filter($this->getCandidateCommands(), function ($v) use ($player) {
            return $v->getPlayer() == $player;
        }));
    }

    function acceptCommand(\Saki\Command\Command $command) {
        $command->execute();
        switch ($this->getRoundPhase()->getValue()) {
            case RoundPhase::PRIVATE_PHASE:
                $this->getTurnManager()->toNextPlayer();
                $this->currentPlayerDraw();
                break;
            case RoundPhase::PUBLIC_PHASE:
                break;
            case RoundPhase::ROUND_OVER_PHASE:
            default:
                throw new \LogicException();
        }
    }
}
