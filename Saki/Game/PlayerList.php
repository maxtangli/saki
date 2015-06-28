<?php
namespace Saki\Game;

use Saki\Tile;
use Saki\Util\ArrayLikeObject;

class PlayerList extends ArrayLikeObject {
    /**
     * @param int $n
     * @param int $initialScore
     * @return Player[]
     */
    static function createPlayers($n, $initialScore) {
        $data = [
            [1, $initialScore, Tile::fromString('E')],
            [2, $initialScore, Tile::fromString('S')],
            [3, $initialScore, Tile::fromString('W')],
            [4, $initialScore, Tile::fromString('N')],
        ];
        return array_map(function ($v) {
            return new Player($v[0], $v[1], $v[2]);
        },$data);
    }

    /**
     * @param Player[] $players
     */
    function __construct(array $players) {
        parent::__construct($players);
    }

    /**
     * @return Player[]
     */
    function toArray() {
        return parent::toArray();
    }

    /**
     * @param int $offset
     * @return Player
     */
    function offsetGet($offset) {
        return parent::offsetGet($offset);
    }
}