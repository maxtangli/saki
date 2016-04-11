<?php
namespace Saki\Game;

use Saki\Tile\Tile;
use Saki\Util\ArrayList;
use Saki\Util\ReadonlyArrayList;
use Saki\Util\Utils;

class PlayerList extends ArrayList {
    use ReadonlyArrayList;

    static function createStandard() {
        return new PlayerList(4, 25000);
    }

    /**
     * @param int $n
     * @param int $initialPoint
     */
    function __construct(int $n, int $initialPoint) {
        if (!Utils::inRange($n, 1, 4)) {
            throw new \InvalidArgumentException();
        }

        $data = [
            [1, $initialPoint, SeatWind::fromString('E')],
            [2, $initialPoint, SeatWind::fromString('S')],
            [3, $initialPoint, SeatWind::fromString('W')],
            [4, $initialPoint, SeatWind::fromString('N')],
        ];
        $players = array_map(function ($v) {
            return new Player($v[0], $v[1], $v[2]);
        }, array_slice($data, 0, $n));

        parent::__construct($players);
    }

    /**
     * Used in: isGameOver.
     * @return bool
     */
    function hasMinusPointPlayer() { // todo move into PointFacade
        return $this->any(function (Player $player) {
            return $player->getArea()->getPoint() < 0;
        });
    }

    /**
     * Used in: isGameOver.
     * @return bool
     */
    function areTiledForTop() { // todo move into PointFacade
        return $this->getTopPlayerArrayList()->count() >= 2;
    }

    /**
     * Used in: isGameOver.
     * @return bool
     */
    function getSingleTopPlayer() {
        // todo safe way
        if ($this->areTiledForTop()) {
            throw new \InvalidArgumentException();
        }
        return $this->getTopPlayerArrayList()[0];
    }

    /**
     * @return ArrayList A not empty ArrayList of top Player.
     */
    protected function getTopPlayerArrayList() {
        $pointList = (new ArrayList())->fromSelect($this, function (Player $player) {
            return $player->getArea()->getPoint();
        });
        $maxPoint = $pointList->getMax();

        $maxPlayerArrayList = (new ArrayList())->fromSelect($this)
            ->where(function (Player $player) use ($maxPoint) {
                return $player->getArea()->getPoint() == $maxPoint;
            });
        if ($maxPlayerArrayList->isEmpty()) {
            throw new \LogicException();
        }
        return $maxPlayerArrayList;
    }

    /**
     * @return Player
     */
    function getEastPlayer() {
        return $this->getSeatWindTilePlayer(Tile::fromString('E'));
    }

    function getSouthPlayer() {
        return $this->getSeatWindTilePlayer(Tile::fromString('S'));
    }

    function getWestPlayer() {
        return $this->getSeatWindTilePlayer(Tile::fromString('W'));
    }

    function getNorthPlayer() {
        return $this->getSeatWindTilePlayer(Tile::fromString('N'));
    }

    function getPlayer(SeatWind $currentSeatWind) {
        return $this->getSeatWindTilePlayer($currentSeatWind->getWindTile());
    }

    /**
     * @param SeatWind $initialSeatWind
     * @return Player
     */
    function getPlayerByInitial(SeatWind $initialSeatWind) {
        return $this->getSingle(function (Player $player) use ($initialSeatWind) {
            return $player->getInitialSeatWind() == $initialSeatWind;
        });
    }

    /** todo remove
     * @param Tile $seatWindTile
     * @return Player
     */
    function getSeatWindTilePlayer(Tile $seatWindTile) {
        return $this->getSingle(function (Player $player) use ($seatWindTile) {
            return $player->getArea()->getSeatWind()->getWindTile() == $seatWindTile;
        });
    }
}