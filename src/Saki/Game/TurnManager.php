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

    function __construct(PlayerList $playerList) { // round only
        $windTiles = Tile::getWindList($playerList->count())->toArray(); // validate
        $this->playerList = $playerList;
        $this->seatWindRoller = new Roller($windTiles);

        $this->currentTurn = Turn::createFirst();
    }

    function reset() { // round only
        $this->seatWindRoller->reset(Tile::fromString('E'));

        $this->currentTurn = Turn::createFirst();
    }

    /**
     * @return Turn
     */
    function getCurrentTurn() { // round only
        $circleCount = $this->seatWindRoller->getCircleCount();
        $wind = $this->seatWindRoller->getCurrentTarget();
        return new Turn($circleCount, new SeatWind($wind));
    }

    function toSeatWind(SeatWind $seatWind) { // one usage only
        $this->seatWindRoller->toTarget($seatWind->getWindTile());

        $this->currentTurn = $this->currentTurn->toSeatWind($seatWind);
    }
}