<?php
namespace Saki\Game;

use Saki\Util\ArrayLikeObject;

class PlayerList extends ArrayLikeObject {
    /**
     * @param int $n
     * @param int $initialScore
     * @return Player[]
     */
    static function createPlayers($n, $initialScore) {
        return array_map(function ($i) use ($initialScore) {
            return new Player($i, $initialScore);
        }, range(1, $n));
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