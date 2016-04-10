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
    private $seatWindRoller;
    private $currentTurn;

    function __construct(PlayerList $playerList) {
        $windTiles = Tile::getWindList($playerList->count())->toArray(); // validate
        $this->playerList = $playerList;
        $this->seatWindRoller = new Roller($windTiles);

        $this->currentTurn = Turn::createFirst();
    }

    function reset() {
        $this->seatWindRoller->reset(Tile::fromString('E'));

        $this->currentTurn = Turn::createFirst();
    }

    /**
     * @return Turn
     */
    function getCurrentTurn() {
        $circleCount = $this->seatWindRoller->getCircleCount();
        $wind = $this->seatWindRoller->getCurrentTarget();
        return new Turn($circleCount, new SeatWind($wind));
    }

    function toSeatWind(SeatWind $seatWind) {
        $this->seatWindRoller->toTarget($seatWind->getWindTile());

        $this->currentTurn = $this->currentTurn->toSeatWind($seatWind);
    }

    // delegate methods of Roller
    /**
     * @return Player
     */
    function getCurrentPlayer() { // todo
        $wind = $this->seatWindRoller->getCurrentTarget();
        return $this->seatWindToPlayer($wind);
    }

    /**
     * @param $offset
     * @param Player $basePlayer
     * @return Player
     */
    function getOffsetPlayer($offset, Player $basePlayer = null) {
        $baseSeatWind = $basePlayer ? $basePlayer->getArea()->getSeatWind()->getWindTile() : null;
        $wind = $this->seatWindRoller->getOffsetTarget($offset, $baseSeatWind);
        return $this->seatWindToPlayer($wind);
    }

    protected function seatWindToPlayer(Tile $seatWind) {
        return $this->playerList->getSeatWindTilePlayer($seatWind);
    }
}