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
     * @var Player[]
     */
    private $players;

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
        $this->players = $players;
    }

    function hasMinusPointPlayer() {
        return $this->any(function (Player $player) {
            return $player->getArea()->getPoint() < 0;
        });
    }

    /**
     * @return Player[]
     */
    function getTopPlayers() {
        $topPlayers = [];
        $topPoint = 0;
        foreach ($this as $player) {
            if ($player->getArea()->getPoint() >= $topPoint) {
                if ($player->getArea()->getPoint() > $topPoint) {
                    $topPlayers = [$player];
                } else {
                    $topPlayers[] = $player;
                }
                $topPoint = $player->getArea()->getPoint();
            }
        }
        return $topPlayers;
    }

    /**
     * @return Player
     */
    function getDealerPlayer() {
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

    function getPlayer(SeatWind $seatWind) {
        return $this->getSeatWindTilePlayer($seatWind->getWindTile());
    }
    
    /** todo remove
     * @param Tile $seatWind
     * @return Player
     */
    function getSeatWindTilePlayer(Tile $seatWind) {
        $result = [];
        foreach ($this as $player) {
            /** @var Player $player */
            $player = $player;
            if ($player->getArea()->getSeatWind()->getWindTile() == $seatWind) {
                $result[] = $player;
            }
        }
        if (count($result) != 1) {
            throw new \LogicException('not one and only one seatWind.');
        }
        return $result[0];
    }
}