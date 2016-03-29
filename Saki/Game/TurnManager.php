<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\Roller;

class TurnManager {
    /**
     * @var PlayerList immutable
     */
    private $playerList;

    /**
     * @var Roller
     */
    private $playerWindRoller;

    function __construct(PlayerList $playerList) {
        $windTiles = Tile::getWindList($playerList->count())->toArray(); // validate
        $this->playerList = $playerList;
        $this->playerWindRoller = new Roller($windTiles);
    }

    function reset() {
        $this->playerWindRoller->reset(Tile::fromString('E'));
    }

    function toPlayer(Player $player) {
        $this->playerWindRoller->toTarget($player->getSelfWind());
    }

    // delegate methods of Roller

    function getGlobalTurn() {
        return $this->playerWindRoller->getGlobalTurn();
    }

    /**
     * @return Player
     */
    function getCurrentPlayer() {
        $wind = $this->playerWindRoller->getCurrentTarget();
        return $this->selfWindToPlayer($wind);
    }

    /**
     * @return Tile
     */
    function getCurrentPlayerWind() {
        $wind = $this->playerWindRoller->getCurrentTarget();
        return $wind;
    }

    /**
     * @return RoundTurn
     */
    function getRoundTurn() {
        $globalTurn = $this->playerWindRoller->getGlobalTurn();
        $wind = $this->playerWindRoller->getCurrentTarget();
        return new RoundTurn($globalTurn, $wind);
    }

    /**
     * @param $offset
     * @param Player $basePlayer
     * @return Player
     */
    function getOffsetPlayer($offset, Player $basePlayer = null) {
        $basePlayerWind = $basePlayer ? $basePlayer->getSelfWind() : null;
        $wind = $this->playerWindRoller->getOffsetTarget($offset, $basePlayerWind);
        return $this->selfWindToPlayer($wind);
    }

    protected function selfWindToPlayer(Tile $selfWind) {
        return $this->playerList->getSelfWindPlayer($selfWind);
    }
}